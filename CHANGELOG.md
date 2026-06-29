# PSM (Procurement & Supplier Management) - Change History

## Server Info
- **Domain**: psm.artmotion.net
- **Server IP**: 84.22.40.222
- **OS**: Ubuntu with Nginx 1.24, PHP 8.3-FPM
- **Database**: MySQL (database: coops)
- **Backend**: Laravel (PHP) at `/home/coops/coops-app`
- **Frontend**: Vue.js 3 SPA at `/home/coops/coops-ui`
- **Email SMTP**: mail.artmotion.info:587 (TLS), from pmk@artmotion.info

---

## 2026-06-26: Initial Setup & Migration

### Infrastructure
- Configured Nginx with SSL (self-signed cert) for Cloudflare Full SSL mode
- Set up PHP 8.3-FPM with Laravel
- Deployed Vue.js frontend to Laravel public directory

### Database Migration (from old PMK system)
- Migrated all data from old `menaxhimi_pmk` database
- Fixed `suppliers.bussines_no` column (changed to VARCHAR for large/text values)
- Added `bill_files.deleted_at` column (model uses SoftDeletes)
- Fixed department mapping to match new system IDs
- Migrated bill files and contract files from old `/home/migration/app/` paths

### Bill Visibility Fix
- Fixed `BillRepository` so Director Department role sees only bills from their department
- Added `getAllByResponsiblePersonId()` method with department + creator scope
- Removed hardcoded Prokurimi/Financa exception for Director Department role

### Supplier Contract Dependency
- Updated all existing suppliers to have `no_contract_needed = 1`

---

## 2026-06-26: Search Functionality

### Suppliers Page
- Added search button toggle with text input (searches by name or business number)
- Backend: `SupplierRepository::getAll()` now supports `search_text` query parameter

### Users (All Users) Page
- Added search button toggle with text input (searches by name or email)
- Backend: `UserRepository::getAll()` now supports `search_text` query parameter

### Vuex Store Updates
- Updated `Supplier` and `User` store actions to pass `search_text` URL parameter

---

## 2026-06-26: Email Notifications

### Email Templates Created
- `email/layout.blade.php` - Shared email layout (centered card, header, footer)
- `email/bill_assigned.blade.php` - New bill notification
- `email/bill_approved.blade.php` - Bill approval notification
- `email/bill_canceled.blade.php` - Bill cancellation notification
- `email/contract_assigned.blade.php` - Contract assignment notification
- `email/contract_approved.blade.php` - Contract approval notification
- `email/contract_canceled.blade.php` - Contract cancellation notification

### Mailable Classes Fixed
- All 6 Mailable classes (`BillAssigned`, `BillApproved`, `BillCanceled`, `ContractAssigned`, `ContractApproved`, `ContractCanceled`) updated to use `view()` with proper blade templates

---

## 2026-06-26: Workflow Engine & Step 0 (Creation Notifications)

### Dynamic Notification System
- Added `notify_department` column to `workflow_steps` table
- Made `role_id` nullable in `workflow_steps` (Step 0 has no role)
- Each workflow template now has a **Step 0** for creation notifications
- `WorkflowEngine::getCreationNotifications()` reads Step 0 config

### Bill Creation Refactored (`BillMediator::createBill`)
- Removed old hardcoded Prokurimi-specific branching logic
- Unified into single code path using Step 0 workflow configuration
- Notifications determined dynamically from workflow template
- All email sending wrapped in try/catch (bad emails don't crash requests)
- Handles both `departments` and `department_id` request fields

### Contract Creation Updated (`ContractMediator::requestContract`)
- Now uses Step 0 workflow configuration for creation notifications
- Removed hardcoded CEO + responsible person notification
- First step query excludes Step 0 (`where step_order > 0`)

### Frontend: Workflow Management Page
- Added "Creation Process (Step 0)" section in Add/Edit workflow modals
- Step 0 shows: "Notify Assigned Department" toggle + role checkboxes
- Step 0 name/role are non-editable (fixed as "Creation")
- Flow preview and info modal properly separate Step 0 from approval steps
- Table step count excludes Step 0

### Backend: Workflow Template CRUD
- `WorkflowTemplateMediator::create()` now creates Step 0 automatically
- `WorkflowTemplateMediator::update()` preserves Step 0 (only deletes approval steps on update)
- Accepts `step0` payload with `notify_roles` and `notify_department`

---

## 2026-06-26: Bill Creation & Validation Fixes

### Fixed `created_by` Field Error
- Bill creation was failing with "Field 'created_by' doesn't have a default value"
- Fixed by including `created_by` and `updated_by` in the initial `store()` call

### Fixed File Upload Validation
- `BillStoreRequest` had broken syntax in validation rules (nested array)
- Changed `files.*` validation from `'sometimes', 'required'` to `'nullable'`
- Fixed same issue in `BillRequestRequest`

### Fixed File Upload (axios Content-Type)
- Root cause: axios instance had hardcoded `Content-type: application/json`
- This prevented FormData (file uploads) from being sent as multipart
- Fix: Added request interceptor that removes Content-type when data is FormData

### Fixed Missing `requestBill` Method
- `BillController@request` was calling non-existent `requestBill()` mediator method
- Fixed by pointing it to `createBill()` (same logic)

### Fixed Department Field Mismatch
- "Request" form sends `department_id`, "Add" form sends `departments`
- `createBill()` now handles both field names

---

## 2026-06-29: Approval Notification Fix

### Fixed Wrong Notification Recipients on Approval
- `WorkflowEngine::advanceStep()` was reading the **next step's** `notify_roles` instead of the **current step's**
- When step 1 was approved, it notified step 2's roles (wrong) instead of step 1's roles (correct)
- Now correctly uses current step's `notify_roles` to determine who gets notified
- Also sends notifications on final step approval (previously sent empty array)

### Fixed Duplicate Creator Notifications
- Bill creator was receiving duplicate emails when they were also in a notified role
- Added deduplication: creator notification is skipped if already in the notifications list
- Same fix applied to contract approval notifications

### Added Error Handling to Approval Emails
- All email sending in `approveBill()` and contract approval wrapped in try/catch
- Invalid email addresses are logged as warnings instead of crashing the request

---

## Workflow Configuration (Current)

### Bill Workflow (Template ID: 1)
| Step | Name | Required Role | Notify Roles |
|------|------|--------------|-------------|
| 0 | Creation | — | Notify assigned department users |
| 1 | Department Director Approval | Director Department | Executive Director, Procurement Officer |
| 2 | CEO Approval | Executive Director | Procurement Officer |

### Contract Workflow (Template ID: 2)
| Step | Name | Required Role | Notify Roles |
|------|------|--------------|-------------|
| 0 | Creation | — | Notify assigned department users |
| 1 | CEO Approval | Executive Director | Legal Office |
| 2 | Legal Approval | Legal Office | Director Department |
| 3 | Creator Approval | Director Department | Legal Office |
| 4 | Legal Office | Legal Office | — |

---

## ARBK Scraper
- Docker container on `127.0.0.1:8181`
- Requires CapSolver API key with server IP whitelisted
- Status: IP whitelist pending (user must do via CapSolver dashboard)
