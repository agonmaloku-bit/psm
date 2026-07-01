# PSM Migration Guide

## Migration: Old `menaxhimi_pmk` → New `coops` Database

### 1. Database Backup
- Always back up the existing coops database before any changes
- Example: `mysqldump -u coops -p coops > backup_coops_before_reimport.sql`

### 2. Data Wipe & Reimport
- Wipe the coops database tables
- Import data from old `menaxhimi_pmk` MySQL dump into `coops` database
- Expected data: ~16,099 bills, 81 contracts, 28 users, 12 departments, 1,903 suppliers

### 3. Schema Fixes During Import
- **Contracts table**: Make `payment_date`, `total_price`, `unit_price`, `payment_terms` nullable (old data has NULLs)
- **OAuth/Passport**: Skip importing OAuth tables — old system used Passport v10, new uses v12. Create fresh personal access client after import: `php artisan passport:client --personal`

### 4. Post-Import Data Fixes
- **Workflow templates** (⚠️ CRITICAL - DO NOT DELETE FROM DB):
  - All bills: `UPDATE bills SET workflow_template_id = 1 WHERE workflow_template_id IS NULL;`
  - All contracts: `UPDATE contracts SET workflow_template_id = 2 WHERE workflow_template_id IS NULL;`
- **Suppliers**: All imported suppliers have `no_contract_needed = 1`

### 5. Workflow Template Configuration (⚠️ DO NOT DELETE)
These workflow templates MUST exist in the database. Deleting them breaks all bill/contract approval flows.

- **Bill workflow (ID 1)**: 2 approval steps
  - Step 0: Creation → notify department
  - Step 1: role_id=5 (Director Department) → notify [6, 4]
  - Step 2: role_id=6 (Executive Director) → notify [4]

- **Contract workflow (ID 2)**: 4 approval steps

### 6. Role IDs (from imported data)
| ID | Role |
|----|------|
| 1 | Super Admin |
| 2 | Admin |
| 3 | Responsible Person |
| 4 | Procurement Officer |
| 5 | Director Department |
| 6 | Executive Director |
| 7 | Legal Office |
| 8 | Transport |
| 9 | Sigurimi/Security |

### 7. Post-Migration Verification
- Clear all Laravel caches: `php artisan cache:clear && php artisan config:clear && php artisan route:clear`
- Create fresh Passport client: `php artisan passport:client --personal`
- Verify users can log in
- Confirm bill/contract counts match expectations
- Test workflow approval flows

### 8. Server Details
- Server: Linux (hostname pmkv2), IP 84.22.40.222, Ubuntu with Nginx 1.24, PHP 8.3-FPM
- Laravel App: `/home/coops/coops-app`
- Vue.js SPA: `/home/coops/coops-ui`
- Database: MySQL, database=coops, user=coops
- Domain: psm.artmotion.net (Cloudflare proxy, Full SSL)
- Git: github.com/agonmaloku-bit/psm (main branch)
