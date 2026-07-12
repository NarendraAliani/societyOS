# SocietyOS — Sitemap / Route Map

Status legend: ✅ built this phase · ⬜ planned (routes not yet wired)

```
/login                          ✅ GET/POST  — auth
/logout                         ✅ POST
/                                ✅ GET redirect → /dashboard
/dashboard                      ✅ GET  — stat cards (flats, residents, visitors, complaints, dues, income, expenses)

/society                        ✅ GET/POST — Society Profile
/society/wings                  ✅ GET/POST — Wings (list/create/delete)
/society/wings/{id}             ✅ GET — Wing detail (floors)
/society/floors                 ✅ POST — create floor
/society/floors/{id}            ✅ GET — Floor detail (flats)
/society/floors/{id}/delete     ✅ POST
/society/flats                  ✅ POST — create flat
/society/flats/{id}/delete      ✅ POST
/society/parking                ⬜ Parking slots
/society/maintenance-heads      ✅ GET/POST — Maintenance heads (create/toggle/delete/edit name+type)
/society/maintenance-heads/{id} ✅ GET — Rate history + schedule a future/immediate rate change
/society/maintenance-heads/{id}/rates ✅ POST — Schedule a new effective-dated rate
/maintenance-head-rates/{id}/delete ✅ POST — Remove a scheduled (not-yet-effective) rate only

/members                        ✅ GET — Owners & Tenants list
/members/create                 ✅ GET/POST — Add resident
/members/{id}                   ✅ GET/POST — Detail/edit, POST .../delete
/members/{id}/family-members    ✅ POST — add family member (inline on detail page)
/members/tenants                ⬜ Dedicated tenants view (lease dates) — `tenants` table not yet wired
/members/documents               ⬜ Document upload (needs file-upload validation, not built yet)
/members/{id}/emergency-contacts ✅ POST — add emergency contact (inline on detail page)

/vehicles                       ✅ GET — Vehicles list
/vehicles/create                ✅ GET/POST — Add vehicle (duplicate registration blocked)
/vehicles/{id}/delete           ✅ POST
/vehicles/parking               ✅ GET/POST — Parking slots list/create
/vehicles/parking/{id}          ✅ GET — Slot detail: allocate/release, allocation history
/vehicles/parking/{id}/allocate ✅ POST
/parking-allocations/{id}/release ✅ POST
/vehicles/parking/rates         ✅ GET/POST — Effective-dated rate per slot type, history + scheduling
/parking-rates/{id}/delete      ✅ POST — Remove a scheduled (not-yet-effective) rate only

/billing                        ✅ GET — Bills list, filter by status
/billing/generate               ✅ GET/POST — Bulk bill generation (per flat, active heads, duplicate-period safe)
/billing/{id}                   ✅ GET — Bill detail (items, payment history)
/billing/{id}/payments          ✅ POST — Record payment (updates paid_amount/status)
/billing/payments/{id}/receipt  ✅ GET — Receipt PDF (DomPDF)
/billing/penalties               ✅ Persisted penalty/interest, recomputed on view (bill detail + defaulter report) — see DECISIONS.md
/billing/defaulters             ✅ GET — Defaulter report (outstanding + days overdue)

/accounting/accounts            ✅ GET/POST — Accounts (cash/bank), computed current balance
/accounting/income               ✅ GET/POST — Record income (auto-posts a credit ledger entry)
/accounting/expenses             ✅ GET/POST — Record expense (auto-posts a debit ledger entry)
/accounting/vendors              ✅ GET/POST — Vendors, POST .../delete
/accounting/ledger               ✅ GET — All entries, filterable by account (doubles as cash book / bank book via the filter)
/accounting/cash-book            ⬜ Dedicated view — covered today by `/accounting/ledger?account_id=`
/accounting/bank-book            ⬜ Dedicated view — covered today by `/accounting/ledger?account_id=`
/accounting/reports              ⬜ Trial balance, P&L, balance sheet — needs a chart-of-accounts model this schema doesn't have (see DECISIONS.md)

/visitors                       ✅ GET/POST — Gate register, filterable by date
/visitors/{id}/approve          ✅ POST
/visitors/{id}/reject           ✅ POST
/visitors/{id}/checkout         ✅ POST
/visitors/passes                ✅ GET/POST — Pre-authorized passes (text token, not a scannable QR image — see DECISIONS.md)
/visitors/passes/verify         ✅ POST — Verify token + auto check-in, single-use, time-window enforced
/visitors/deliveries            ✅ GET/POST — Delivery register, mark collected

/complaints                     ✅ GET — List, filter by status
/complaints/create              ✅ GET/POST — Register complaint
/complaints/{id}                ✅ GET — Detail + status timeline
/complaints/{id}/updates        ✅ POST — Update status (open→in_progress→resolved/closed)
/complaints/categories          ✅ GET/POST — Manage categories, POST .../delete

/notices                        ✅ GET/POST — Notice board (notice/circular), POST .../delete
/notices/events                 ✅ GET/POST — Events, POST .../delete
/notices/polls                  ✅ GET/POST — Polls with options
/notices/polls/{id}             ✅ GET — Results with vote %, cast/change vote
/notices/polls/{id}/vote        ✅ POST — Single active vote per member per poll (see DECISIONS.md)

/staff                          ✅ GET/POST — Employees (name, DOB, photo, ID proof), toggle status, delete
/staff/{id}                     ✅ GET — Detail: photo, DOB/age, ID proof, police verification, payroll history
/staff/{id}/police-verification ✅ POST — Update status (pending/verified/not_verified) + date + optional certificate
/staff/{id}/file/{type}         ✅ GET — Authenticated file serving (photo/id_proof/police_doc), never linked directly
/staff/attendance               ✅ GET/POST — Mark daily attendance (bulk form)
/staff/{id}/payroll             ✅ POST — Add payroll entry, mark paid
/staff/leave                    ✅ GET/POST — Log + approve/reject leave requests

/assets                         ✅ GET/POST — Asset register
/assets/{id}                    ✅ GET — Detail, status change
/assets/{id}/amc                ✅ POST — Add AMC record
/assets/{id}/service            ✅ POST — Log service, next-due tracking
/assets/categories              ✅ GET/POST

/reports                        ✅ GET — Report index
/reports/collection             ✅ Date-range payment collection + CSV
/reports/defaulters             ✅ Reuses billing defaulter report + CSV
/reports/income                 ✅ Date-range + CSV
/reports/expense                ✅ Date-range + CSV
/reports/visitors               ✅ Date-range + CSV
/reports/complaints             ✅ Summary by category/status + CSV
/reports/staff                  ✅ Roster + CSV
/reports/assets                 ✅ Register + CSV
/reports/occupancy              ✅ Flat occupancy + CSV
/reports/parking                ✅ Slot allocation + CSV
/reports/*?format=csv|pdf|xlsx   ✅ Every report above supports CSV, real PDF (Dompdf), and real Excel (PhpSpreadsheet) export — see DECISIONS.md

/admin/users                    ✅ GET/POST — Users, inline role/status edit, password reset
/admin/roles                    ✅ GET — Role list
/admin/roles/{id}               ✅ GET/POST — Edit a role's permission set (super_admin fixed/uneditable)
/admin/activity-logs            ✅ GET — Login history + module activity log, both populated (high-signal write actions instrumented across 10 controllers, see DECISIONS.md)
/admin/backup                   ⬜ Not built — deferred (see DECISIONS.md)

/profile                        ✅ GET — View own name/email/phone/role/status/last login
/profile/edit                   ✅ GET/POST — Edit own name/email/phone (email uniqueness enforced, role/status not self-editable)
/profile/password               ✅ GET/POST — Self-service password change (requires current password)
```

Full target hierarchy (module → sub-items) is documented in the original spec; this file tracks build status against it.

**Phase 2 note**: Society Setup and Residents are functionally complete (CRUD verified end-to-end against a live DB — see `docs/DECISIONS.md`), but forms are plain POST + page reload, not the AJAX/DataTables/SweetAlert2 pattern named in the tech stack. That polish is deferred; see decision log.

**Phases 6–10 note**: Complaints, Notices/Events/Polls, Staff, Assets, Reports, and Admin (Users/Roles/Activity Logs) are all built and verified end-to-end against a live DB through the real Apache vhost — see `docs/DECISIONS.md` for what's deferred in each (chart-of-accounts reports, scannable QR, backup/restore, self-service profile).
