# SocietyOS — Entity Relationship Diagram

Full schema: [`database/schema.sql`](../database/schema.sql) (41 module tables + 13 admin/security tables = 54 total).
Diagrams are split by domain for readability — cross-domain FKs (e.g. `flat_id` used everywhere) are noted under each diagram.

## Administration & RBAC

```mermaid
erDiagram
    SOCIETY ||--o{ USERS : employs
    SOCIETY ||--o{ FINANCIAL_YEARS : has
    SOCIETY ||--o{ BANK_DETAILS : has
    SOCIETY ||--o{ SETTINGS : has
    ROLES ||--o{ USERS : assigned
    ROLES ||--o{ ROLE_PERMISSIONS : grants
    PERMISSIONS ||--o{ ROLE_PERMISSIONS : granted_via
    USERS ||--o{ LOGIN_HISTORY : logs
    USERS ||--o{ USER_SESSIONS : has
    USERS ||--o{ ACTIVITY_LOGS : performs
    USERS ||--o{ PASSWORD_RESETS : requests
    USERS }o--o| MEMBERS : "linked resident"
```

## Society Structure → Residents

```mermaid
erDiagram
    SOCIETY ||--o{ WINGS : has
    WINGS ||--o{ FLOORS : has
    FLOORS ||--o{ FLATS : has
    FLATS ||--o{ FLAT_OWNERSHIP_HISTORY : has
    FLATS ||--o{ MEMBERS : houses
    MEMBERS ||--o{ FAMILY_MEMBERS : has
    MEMBERS ||--o{ EMERGENCY_CONTACTS : has
    MEMBERS ||--o{ DOCUMENTS : uploads
    FLATS ||--o{ TENANTS : "leased to"
    MEMBERS ||--o{ TENANTS : "is tenant / is owner"
```

## Vehicles & Parking

```mermaid
erDiagram
    MEMBERS ||--o{ VEHICLES : owns
    SOCIETY ||--o{ PARKING_SLOTS : has
    PARKING_SLOTS ||--o{ PARKING_ALLOCATIONS : allocated
    VEHICLES ||--o{ PARKING_ALLOCATIONS : parked_in
    FLATS ||--o{ PARKING_ALLOCATIONS : allocated_to
```

## Maintenance Billing

```mermaid
erDiagram
    SOCIETY ||--o{ MAINTENANCE_HEADS : defines
    FINANCIAL_YEARS ||--o{ MAINTENANCE_BILLS : issued_in
    FLATS ||--o{ MAINTENANCE_BILLS : billed
    MAINTENANCE_BILLS ||--o{ BILL_ITEMS : contains
    MAINTENANCE_HEADS ||--o{ BILL_ITEMS : "charge type"
    MAINTENANCE_BILLS ||--o{ PENALTIES : accrues
    MAINTENANCE_BILLS ||--o{ PAYMENTS : receives
    PAYMENTS ||--o| RECEIPTS : generates
```

## Accounting

```mermaid
erDiagram
    SOCIETY ||--o{ ACCOUNTS : has
    SOCIETY ||--o{ VENDORS : has
    ACCOUNTS ||--o{ INCOME : credited
    ACCOUNTS ||--o{ EXPENSES : debited
    VENDORS ||--o{ EXPENSES : billed_by
    ACCOUNTS ||--o{ LEDGER_ENTRIES : posted_to
```
*`ledger_entries.reference_id` polymorphically points to `income`, `expenses`, or `payments` per `reference_type` — no FK constraint, resolved in application code.*

## Visitors & Security

```mermaid
erDiagram
    FLATS ||--o{ VISITORS : receives
    FLATS ||--o{ VISITOR_PASSES : issues
    FLATS ||--o{ DELIVERIES : receives
    MEMBERS ||--o{ VISITORS : approves
```

## Complaints

```mermaid
erDiagram
    SOCIETY ||--o{ COMPLAINT_CATEGORIES : defines
    FLATS ||--o{ COMPLAINTS : raised_from
    MEMBERS ||--o{ COMPLAINTS : raises
    COMPLAINT_CATEGORIES ||--o{ COMPLAINTS : categorizes
    COMPLAINTS ||--o{ COMPLAINT_UPDATES : tracked_by
    USERS ||--o{ COMPLAINTS : assigned_to
```

## Communication

```mermaid
erDiagram
    SOCIETY ||--o{ NOTICES : publishes
    SOCIETY ||--o{ EVENTS : hosts
    SOCIETY ||--o{ POLLS : runs
    POLLS ||--o{ POLL_OPTIONS : has
    POLL_OPTIONS ||--o{ POLL_VOTES : receives
    MEMBERS ||--o{ POLL_VOTES : casts
```

## Staff

```mermaid
erDiagram
    SOCIETY ||--o{ STAFF : employs
    STAFF ||--o{ ATTENDANCE : marks
    STAFF ||--o{ LEAVE_REQUESTS : requests
    STAFF ||--o{ PAYROLL : paid_via
```

## Assets

```mermaid
erDiagram
    SOCIETY ||--o{ ASSET_CATEGORIES : defines
    ASSET_CATEGORIES ||--o{ ASSETS : categorizes
    ASSETS ||--o{ ASSET_AMC : covered_by
    ASSETS ||--o{ ASSET_SERVICES : serviced
    VENDORS ||--o{ ASSET_AMC : provides
```

## Cross-cutting notes

- Every module table carries `society_id` even though this is a single-society install — kept for referential integrity and to make a future multi-society migration additive, not a rewrite (not built now; YAGNI applies today).
- `flats.id` is the hub — members, vehicles (indirectly), bills, visitors, complaints, deliveries, and parking all key off it.
- All FKs use `ON DELETE CASCADE` for true child records, `ON DELETE SET NULL` for optional actor references (e.g. `logged_by`, `assigned_to`).
