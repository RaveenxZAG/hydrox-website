# Hydrox Website - SQLite Migration & Cutover Runbook

This guide provides step-by-step instructions for migrating the **Hydrox Public Website** (`hydrox.au`) to a dedicated, high-performance, persistent SQLite database on **HostPapa shared hosting** using only the **cPanel File Manager**, **cPanel Git Version Control**, and the **Web Browser** (no SSH / Terminal required).

---

## 1. Safety & Architecture Highlights

- **Complete Isolation**: The public website receives its own independent SQLite database located at:
  `/home/hydro851/persistent/hydrox-website/database/database.sqlite`
  The Hydrox Portal database is completely untouched and never shared.
- **Zero Live Data Loss**: The existing MySQL database (`hydro851_dev`) is **read-only** during migration. It is never truncated, altered, or deleted.
- **Instant Rollback**: You can revert to MySQL in under 30 seconds at any time simply by changing `DB_CONNECTION=mysql` in `.env` and clicking "Deploy HEAD Commit".
- **Clean Git Status**: All database files, SQLite journals, WAL files, and backups are stored outside the Git repository and strictly ignored by Git, ensuring the cPanel **"Deploy HEAD Commit"** button remains enabled permanently.

---

## 2. Directory Structure on HostPapa

The persistent directory layout outside the Git repository:

```
/home/hydro851/
├── persistent/
│   └── hydrox-website/
│       ├── .env                       <-- Persistent production environment file
│       ├── database/
│       │   └── database.sqlite        <-- Public website SQLite database (WAL mode)
│       ├── backups/                   <-- Daily gzipped backups (14-day retention)
│       └── storage/
│           └── app/
│               └── public/            <-- Customer uploads & booking photos
└── repositories/
    └── hydrox-website/                <-- Git repository checkout (.cpanel.yml deploys here)
        └── public/
            └── storage -> /home/hydro851/persistent/hydrox-website/storage/app/public
```

---

## 3. Step 1: Create Persistent Folders in cPanel File Manager

1. Log into your **cPanel** dashboard.
2. Open **File Manager**.
3. In your home directory (`/home/hydro851/`), check if the `persistent/hydrox-website` folder exists.
   - If not, click **+ Folder** and create:
     - `persistent/hydrox-website`
     - `persistent/hydrox-website/database`
     - `persistent/hydrox-website/backups`
4. Set folder permissions:
   - Right-click `database` -> **Change Permissions** -> Ensure `750` (`rwxr-x---`).
   - Right-click `backups` -> **Change Permissions** -> Ensure `750` (`rwxr-x---`).

---

## 4. Step 2: Configure Persistent `.env` in cPanel File Manager

1. In File Manager, navigate to `/home/hydro851/persistent/hydrox-website/`.
   - If a `.env` file already exists there, right-click and choose **Edit**.
   - If not, copy the existing `.env` from your repository checkout (`/home/hydro851/repositories/hydrox-website/.env`) into `/home/hydro851/persistent/hydrox-website/.env`.
2. Update or add the following lines in the `.env` file:

```dotenv
# ==============================================================================
# Database Configuration (SQLite WAL Mode)
# ==============================================================================
DB_CONNECTION=sqlite
DB_DATABASE=/home/hydro851/persistent/hydrox-website/database/database.sqlite
DB_FOREIGN_KEYS=true
DB_BUSY_TIMEOUT=5000
DB_JOURNAL_MODE=WAL
DB_SYNCHRONOUS=NORMAL

# ==============================================================================
# Migration Source (Existing MySQL database for data migration)
# ==============================================================================
SOURCE_DB_HOST=127.0.0.1
SOURCE_DB_PORT=3306
SOURCE_DB_DATABASE=hydro851_dev
SOURCE_DB_USERNAME=hydro851_dev
SOURCE_DB_PASSWORD=<YourExistingMySQLPassword>

# ==============================================================================
# Maintenance Endpoint (Enable temporarily for data migration)
# ==============================================================================
SQLITE_MIGRATION_ENABLED=true
INTERNAL_MAINTENANCE_TOKEN=HydroxSqliteMigrate2026SecureKey789
```

> **Security Note**: Replace `HydroxSqliteMigrate2026SecureKey789` with your own random secret string. Never share or commit this token.

3. Save the file.

---

## 5. Step 3: Deploy via cPanel Git Version Control

1. In cPanel, navigate to **Git™ Version Control**.
2. Click **Manage** next to the `hydrox-website` repository.
3. Switch to the **Pull or Deploy** tab.
4. Click **Update from Remote** to fetch the latest commits from `main`.
5. Click **Deploy HEAD Commit**.
   - The `.cpanel.yml` deployment script will automatically:
     - Symlink `/home/hydro851/persistent/hydrox-website/.env` to the checkout `.env`.
     - Initialize `/home/hydro851/persistent/hydrox-website/database/database.sqlite` if it does not already exist.
     - Run `php artisan migrate --force` to create all 39 SQLite tables.
     - Run `php artisan db:seed --force`.
     - Link persistent storage (`storage:link`).
     - Optimize route, config, and view caches.
6. Verify the deployment task reports success.

---

## 6. Step 4: Copy Data from MySQL to SQLite (Browser-Based)

Because HostPapa does not have SSH/Terminal access, use the secure internal maintenance endpoint:

### A. Run a Dry-Run First (Safe Simulation)
Open your web browser and visit:
```
https://hydrox.au/internal/maintenance/migrate-sqlite?token=HydroxSqliteMigrate2026SecureKey789&dry_run=1
```
*(Replace the token with your value from Step 2).*

**Expected Response**:
A JSON output showing `exit_code: 0`, `dry_run: true`, and a table of all tables with row counts in MySQL and SQLite.

### B. Execute the Live Data Migration
When ready to copy the data, visit:
```
https://hydrox.au/internal/maintenance/migrate-sqlite?token=HydroxSqliteMigrate2026SecureKey789
```

**Expected Response**:
```json
{
  "status": "success",
  "exit_code": 0,
  "dry_run": false,
  "duration_seconds": 1.42,
  "output": "Source connection: mysql_source\nTarget connection: sqlite\nDiscovered 39 tables for migration...\nPRAGMA foreign_key_check passed with 0 violations.\nMigration completed successfully."
}
```

### C. Disable the Maintenance Endpoint
Once migration is successful:
1. Open `/home/hydro851/persistent/hydrox-website/.env` in File Manager.
2. Change:
   ```dotenv
   SQLITE_MIGRATION_ENABLED=false
   ```
3. Save the file. (This locks down the endpoint with an immediate 404).

---

## 7. Step 5: Schedule Daily Automated Backups in cPanel

1. In cPanel, open **Cron Jobs**.
2. Under **Add New Cron Job**:
   - Common Settings: **Once Per Day (0 2 * * *)** (2:00 AM).
   - Command:
     ```bash
     /bin/bash /home/hydro851/repositories/hydrox-website/scripts/backup-sqlite.sh >/dev/null 2>&1
     ```
3. Click **Add New Cron Job**.
   - This script creates atomic online backups using SQLite `VACUUM INTO`, compresses them with `gzip` (`.sqlite.gz`), and retains the last 14 days of backups in `/home/hydro851/persistent/hydrox-website/backups/`.

---

## 8. Verification & Post-Migration Checklist

- [ ] **Public Booking Form**: Visit `https://hydrox.au/booking` and submit a test booking.
  - Verify booking confirmation page displays `HYD-2026...` reference.
  - Verify Google Ads conversion script `AW-18428986459/b0SgCPbm6-0cENuI0NNE` is rendered.
- [ ] **Email Notification**: Check that customer confirmation email and admin notification email arrive.
- [ ] **Hydrox Portal Sync**: Log into `https://portal.hydrox.au` and confirm the new lead appears under Bookings.
- [ ] **cPanel Git Cleanliness**: In cPanel Git Version Control, check repository status. "Deploy HEAD Commit" should remain cleanly enabled with no dirty file status.

---

## 9. Rollback Plan (If Needed)

If any unexpected issue occurs and you need to immediately revert to the MySQL database:

1. Open `/home/hydro851/persistent/hydrox-website/.env` in cPanel File Manager.
2. Change:
   ```dotenv
   DB_CONNECTION=mysql
   ```
3. Save the file.
4. In cPanel Git Version Control, click **Deploy HEAD Commit**.
5. The public website is immediately reverted to using the MySQL `hydro851_dev` database. Zero data was lost or changed in MySQL.
