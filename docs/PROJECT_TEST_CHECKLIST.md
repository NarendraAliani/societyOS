# SocietyOS — Project Verification Checklist

Use this checklist after each production deployment. Test with Super Admin, Society Admin/back-office, Resident, and a second-society user where available. Check desktop and mobile widths.

| # | Area | Test | Expected result | Status |
|---:|---|---|---|---|
| 1 | Authentication | Valid login | Correct dashboard/role opens | ☐ |
| 2 | Authentication | Invalid password | Login rejected safely | ☐ |
| 3 | Authentication | Logout | Session ends; protected pages redirect | ☐ |
| 4 | Authentication | Forced password change | User must change password before normal access | ☐ |
| 5 | Roles | Multiple roles | Only assigned roles are available | ☐ |
| 6 | Roles | Duplicate role assignment | Same role cannot be assigned twice | ☐ |
| 7 | Roles | Same flat + same role | Flat unavailable to another user for that role | ☐ |
| 8 | Roles | Same flat + different role | Allowed where business rules permit | ☐ |
| 9 | Roles | Linked-home dropdown | Already-linked candidate is excluded | ☐ |
| 10 | Roles | Change role | Candidate dropdown refreshes correctly | ☐ |
| 11 | Navigation | All sidebar links | No 404/500 | ☐ |
| 12 | Navigation | Long content scroll | Right content scrolls independently | ☐ |
| 13 | Navigation | Long content scroll | Left sidebar remains independently scrollable/fixed | ☐ |
| 14 | Navigation | Mobile menu | Sidebar drawer opens/closes correctly | ☐ |
| 15 | Navigation | Deep URL refresh | Page stays styled and functional | ☐ |
| 16 | Society Setup | Society details | Save/edit persists | ☐ |
| 17 | Society Setup | Wings | Add/edit/delete works and is scoped | ☐ |
| 18 | Society Setup | Floors | Add/edit/delete works for selected wing | ☐ |
| 19 | Society Setup | Flats | Add/edit/delete works for selected floor | ☐ |
| 20 | Residents | Resident list | Search/filter/list data is correct | ☐ |
| 21 | Residents | Create resident | Correct member/flat is created | ☐ |
| 22 | Residents | Edit resident | Changes persist | ☐ |
| 23 | Residents | Member detail | Shared dashboard layout renders correctly | ☐ |
| 24 | Residents | Family members | Add/edit/remove works | ☐ |
| 25 | Residents | Emergency contacts | Add/edit/remove works | ☐ |
| 26 | Residents | Documents | Upload/view/remove works | ☐ |
| 27 | Residents | Tenant lease | Create/edit/agreement view works | ☐ |
| 28 | Residents | Ownership | Changing member ID cannot access another society | ☐ |
| 29 | Vehicles | Vehicle list | Correct records appear | ☐ |
| 30 | Vehicles | Add vehicle | Correct member/flat linkage | ☐ |
| 31 | Vehicles | Edit vehicle | Changes persist | ☐ |
| 32 | Vehicles | Delete vehicle | Authorized deletion only | ☐ |
| 33 | Parking | Parking slots | Add/edit/delete works | ☐ |
| 34 | Parking | Allocation | Only valid society flat/vehicle can be allocated | ☐ |
| 35 | Parking | Release | Correct allocation is released | ☐ |
| 36 | Parking | Rates | Add/delete works for current society | ☐ |
| 37 | Maintenance | Heads | Add/edit/configuration works | ☐ |
| 38 | Maintenance | Rates | Effective-date rates calculate correctly | ☐ |
| 39 | Billing | Generate bills | Correct flats/heads are billed | ☐ |
| 40 | Billing | Parking billing | Chargeable parking is included correctly | ☐ |
| 41 | Billing | Duplicate generation | Same flat/period is not billed twice | ☐ |
| 42 | Billing | Bill detail | Only current-society bill can be opened | ☐ |
| 43 | Billing | Payment | Payment updates balance/status and creates receipt | ☐ |
| 44 | Billing | Overpayment | Amount above outstanding balance is rejected | ☐ |
| 45 | Billing | Receipt PDF | Correct receipt and society data | ☐ |
| 46 | Billing | Partial payment | Partially-paid status/balance is correct | ☐ |
| 47 | Billing | Defaulters | Correct overdue bills/penalties appear | ☐ |
| 48 | Accounts | Transactions | Add/edit/list behavior is correct | ☐ |
| 49 | Accounts | Financial reports | Totals reconcile with transactions | ☐ |
| 50 | Visitors | Visitor register | Create/approve/reject/check-out works | ☐ |
| 51 | Visitors | Visitor pass | Create/verify/consume works | ☐ |
| 52 | Visitors | Deliveries | Log/collect works | ☐ |
| 53 | Complaints | Create complaint | Correct resident/flat linkage | ☐ |
| 54 | Complaints | Status workflow | Open/in-progress/resolved/closed works | ☐ |
| 55 | Notices | Notices | Create/delete works | ☐ |
| 56 | Notices | Events | Create/delete works | ☐ |
| 57 | Notices | Polls | Create/vote/results work | ☐ |
| 58 | Staff | Staff records | Add/edit/view works | ☐ |
| 59 | Staff | Payroll | Records are correctly scoped | ☐ |
| 60 | Staff | Leave | Leave workflow works | ☐ |
| 61 | Assets | Asset CRUD | Add/edit/status works | ☐ |
| 62 | Assets | AMC/service | AMC/service records work | ☐ |
| 63 | Assets | Society isolation | Other-society IDs are rejected | ☐ |
| 64 | Reports | Filters | Date/status filters return expected records | ☐ |
| 65 | Administration | User management | Create/edit/deactivate/roles work | ☐ |
| 66 | Administration | Permissions | Unauthorized module/action is blocked | ☐ |
| 67 | Security | CSRF | Missing/tampered POST token is rejected | ☐ |
| 68 | Security | Direct IDs | Changing numeric IDs cannot cross society boundary | ☐ |
| 69 | Security | File access | Documents/agreements require authorization | ☐ |
| 70 | Security | Session | Protected URLs fail after logout | ☐ |
| 71 | UI | Shared shell | Sidebar/topbar/theme/font controls consistent | ☐ |
| 72 | UI | Responsive | Forms/cards/tables work on mobile | ☐ |
| 73 | UI | Modals | Add/Edit modals open, validate and close correctly | ☐ |
| 74 | UI | Empty states | Empty lists show useful messaging | ☐ |
| 75 | UI | Validation | Required/invalid input gets clear feedback | ☐ |
| 76 | UI | Flash messages | Success/error feedback appears | ☐ |
| 77 | Data | Refresh after mutation | New data survives refresh | ☐ |
| 78 | Data | Delete confirmation | Destructive actions confirm appropriately | ☐ |
| 79 | Regression | GitHub Actions | All automated checks pass | ☐ |
| 80 | Production | Hard refresh | Latest deployment is visible | ☐ |

## Cross-society isolation test

With a second society available:
1. Take a valid record ID from Society A.
2. Log in as an authorized user of Society B.
3. Replace a URL/action ID with Society A's ID.
4. Expected: **404, rejection, or safe redirect — never disclosure or mutation.**
5. Repeat for residents, flats, vehicles, parking, bills, payments, receipts, assets, staff, documents and leases.

## Final production smoke test

- [ ] Login
- [ ] Dashboard
- [ ] Sidebar navigation
- [ ] One create
- [ ] One edit
- [ ] One delete
- [ ] One document view
- [ ] One modal
- [ ] One report
- [ ] One resident self-service page
- [ ] Mobile-width check
- [ ] Independent sidebar/content scrolling
- [ ] Logout

Record deployment date, commit SHA, tester, environment, and failed test IDs for sign-off.


## Quality hardening verification

| Area | Automated gate | Manual verification |
|---|---|---|
| Multi-society isolation | Tenant-scoped lookup regression | Create two societies and attempt cross-society URLs |
| RBAC | Route/middleware regression | Log in as each role and verify permitted/forbidden modules |
| CSRF | Controller security contracts | Submit a state-changing form without a valid token |
| Authentication | CAPTCHA + reset regressions | Login, logout, password reset and session expiry |
| Visitor QR | QR regression | Open a pass's QR action and scan it with a phone |
| File uploads | Upload helper contract | Test valid files, wrong MIME, oversize and unauthorized access |
| Audit logs | Activity-log contract | Perform a state-changing action and inspect Activity Logs |
| Responsive UI | Layout regression | Check phone/tablet widths and wide data tables |
| Destructive actions | Confirmation contract | Verify delete/restore prompts and server-side authorization |
| Production smoke | Deployment workflow | Open login and platform login after deployment |
