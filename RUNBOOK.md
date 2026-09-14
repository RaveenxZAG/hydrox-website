# Hydrox Website - SQLite Migration & Cutover Runbook

This guide provides step-by-step instructions for migrating the **Hydrox Public Website** (`hydrox.au`) to a dedicated, high-performance, persistent SQLite database on **HostPapa shared hosting** using the **cPanel File Manager**, **cPanel Git Version Control**, and a **Web Browser** (no SSH / Terminal required).

---

## 1. Safety & Architecture Highlights

- **Dedicated Isolation**: The public website uses its own independent SQLite database located at:
  `/home/hydro851/persistent/hydrox-website/database/database.sqlite`
  The separate Hydrox Portal application and database are completely untouched and never shared.
- **Two-Phase Staging & Atomic Promotion**:
  1. Data is imported into a brand-new, isolated staging database (`staging_<timestamp>.sqlite`).
  2. Full relational verification is performed on staging (`PRAGMA foreign_key_check` and `PRAGMA integrity_check`).
  3. Only after 100% verification passes is the staging file atomically promoted/renamed to `database.sqlite`.
  4. If any error occurs, the staging file is quarantined, and both the active SQLite database and the MySQL source database remain untouched.
- **Zero Live MySQL Mutation**: The existing MySQL database (`hydro851_dev`) is strictly **read-only** during migration. It is never truncated, modified, or deleted.
- **Maintenance Write-Safety**: To guarantee zero dropped or duplicate submissions under concurrent traffic, cutover occurs during a brief maintenance window (`php artisan down` or maintenance mode), ensuring deterministic replication of all rows.
- **Controlled Rollback**: You can revert back to MySQL at any time simply by changing `DB_CONNECTION=mysql` in `.env` and clicking "Deploy HEAD Commit" in cPanel Git.

---

## 2. Directory Structure on HostPapa

Verify your persistent directory structure in cPanel File Manager:

```
/home/hydro851/
├── persistent/
│   └── hydrox-website/
│       ├── .env                       <-- Persistent environment file (outside Git)
│       ├── database/
│       │   └── database.sqlite        <-- Public website SQLite database (WAL mode)
│       ├── backups/                   <-- Daily gzipped backups (14-day retention)
│       └── storage/
│           └── app/
│               ├── private/           <-- Booking photos & private documents
│               └── public/            <-- Public photos & certificates
└── <CPANEL_APP_PATH>/                 <-- Git repository checkout (e.g. /home/hydro851/hydrox_dev_app)
    └── public/
        └── storage -> /home/hydro851/persistent/hydrox-website/storage/app/public
```

> **Important**: Check your cPanel **Git™ Version Control** screen to confirm your repository path (commonly `/home/hydro851/hydrox_dev_app` or similar). Replace `<CPANEL_APP_PATH>` with your actual repository path.

---

## 3. Step 1: Create Persistent Folders in cPanel File Manager

1. Open **cPanel** -> **File Manager**.
2. In your home directory (`/home/hydro851/`), ensure the following directories exist:
   - `persistent/hydrox-website`
   - `persistent/hydrox-website/database`
   - `persistent/hydrox-website/backups`
   - `persistent/hydrox-website/storage/app/private`
   - `persistent/hydrox-website/storage/app/public`
3. Set folder permissions:
   - Right-click `database`, `backups`, and `private` -> **Change Permissions** -> Set to `750` (`rwxr-x---`).
   - Right-click `public` -> **Change Permissions** -> Set to `755` (`rwxr-xr-x`).

---

## 4. Step 2: Copy Existing Uploads to Persistent Storage

Before switching databases, copy any existing uploads so that no historic documents or photos are lost:

1. In File Manager, navigate to `<CPANEL_APP_PATH>/storage/app/private` (if it exists):
   - Select all files/folders inside it.
   - Click **Copy** in the top toolbar.
   - Specify destination: `/home/hydro851/persistent/hydrox-website/storage/app/private`.
   - Click **Copy File(s)**. (Existing files are preserved; nothing is deleted).
2. Repeat for `<CPANEL_APP_PATH>/storage/app/public`:
   - Select all files/folders inside it.
   - Click **Copy**.
   - Destination: `/home/hydro851/persistent/hydrox-website/storage/app/public`.
   - Click **Copy File(s)**.

---

## 5. Step 3: Deploy Code via cPanel Git Version Control

1. In cPanel, navigate to **Git™ Version Control**.
2. Click **Manage** next to the repository.
3. Switch to the **Pull or Deploy** tab.
4. Click **Update from Remote** to fetch the latest commit from `main`.
5. Click **Deploy HEAD Commit**.
   - Note: The site remains actively connected to MySQL (`DB_CONNECTION=mysql`) during this deployment.
   - No database seeding (`db:seed`) is executed during deployment, preventing any alteration of existing administrative accounts.

---

## 6. Step 4: Configure Persistent `.env` for Cutover

1. In File Manager, navigate to `/home/hydro851/persistent/hydrox-website/`.
2. Right-click `.env` and select **Edit**.
3. Generate a private, high-entropy random token on your local machine (e.g., using a password generator or `openssl rand -hex 24`). Do NOT reuse any example token.
4. Ensure the following configurations are set in `.env`:

```dotenv
# ==============================================================================
# Active Database (KEEP ON MYSQL UNTIL AFTER DATA IMPORT)
# ==============================================================================
DB_CONNECTION=mysql

# SQLite Configuration (Target for migration and cutover)
DB_DATABASE=/home/hydro851/persistent/hydrox-website/database/database.sqlite
DB_FOREIGN_KEYS=true
DB_BUSY_TIMEOUT=5000
DB_JOURNAL_MODE=WAL
DB_SYNCHRONOUS=NORMAL

# Dedicated MySQL Source Credentials (Required - no silent fallbacks)
SOURCE_DB_HOST=127.0.0.1
SOURCE_DB_PORT=3306
SOURCE_DB_DATABASE=hydro851_dev
SOURCE_DB_USERNAME=hydro851_dev
SOURCE_DB_PASSWORD=<YourActualMySQLPassword>

# Persistent Storage Roots
FILESYSTEM_LOCAL_ROOT=/home/hydro851/persistent/hydrox-website/storage/app/private
FILESYSTEM_PUBLIC_ROOT=/home/hydro851/persistent/hydrox-website/storage/app/public

# Maintenance Endpoint (Enable temporarily for migration)
SQLITE_MIGRATION_ENABLED=true
INTERNAL_MAINTENANCE_TOKEN=<PASTE_YOUR_GENERATED_SECRET_TOKEN_HERE>
```

5. Click **Save Changes**.

---

## 7. Step 5: Execute Migration via Secure Browser Form

Because HostPapa shared hosting provides no SSH/Terminal, use the secure web maintenance interface:

### A. Pre-Flight Dry Run (Verification Only)
1. Open your browser and navigate to:
   ```
   https://hydrox.au/internal/maintenance/migrate-sqlite
   ```
   *(Note: This GET request displays a secure status form and does NOT mutate any data).*
2. Enter your secret `INTERNAL_MAINTENANCE_TOKEN` in the password field.
3. Ensure the **Run in Dry-Run Mode** checkbox is **checked**.
4. Click **Execute Migration**.
5. Review the dry-run summary table showing that all tables in MySQL are readable and row counts match.

### B. Execute Live Import & Promotion (Brief Maintenance Window)
To guarantee zero dropped or duplicate submissions during final data copy:
1. In the browser, navigate back to `https://hydrox.au/internal/maintenance/migrate-sqlite`.
2. Enter your secret `INTERNAL_MAINTENANCE_TOKEN`.
3. **Uncheck** the Dry-Run Mode checkbox.
4. Click **Execute Migration**.
5. The system will:
   - Create a clean staging SQLite file.
   - Run complete schema migrations on staging.
   - Keyset-paginate all rows under a consistent MySQL transaction.
   - Validate exact row counts and ID ranges.
   - Run `PRAGMA foreign_key_check` and `PRAGMA integrity_check`.
   - Checkpoint WAL and optimize SQLite.
   - Back up any prior SQLite file and atomically promote staging to `/home/hydro851/persistent/hydrox-website/database/database.sqlite`.
6. Confirm the browser displays `"status": "success"` and `0 violations`.

---

## 8. Step 6: Complete Cutover to SQLite & Disable Maintenance

1. In File Manager, edit `/home/hydro851/persistent/hydrox-website/.env`:
   - Change:
     ```dotenv
     DB_CONNECTION=sqlite
     SQLITE_MIGRATION_ENABLED=false
     INTERNAL_MAINTENANCE_TOKEN=
     ```
   - (Optional): You can remove or blank `SOURCE_DB_PASSWORD`.
2. Click **Save Changes**.
3. In cPanel **Git™ Version Control**, click **Deploy HEAD Commit** to re-cache configuration with SQLite active.
4. The public website is now running live on its dedicated SQLite database.

---

## 9. Step 7: Schedule Automated Daily Backups in cPanel

1. In cPanel, navigate to **Cron Jobs**.
2. Under **Add New Cron Job**:
   - Minute: `0`
   - Hour: `2` (2:00 AM)
   - Day: `*`, Month: `*`, Weekday: `*`
   - Command (replace `<CPANEL_APP_PATH>` with your repository path):
     ```bash
     /bin/bash <CPANEL_APP_PATH>/scripts/backup-sqlite.sh >/dev/null 2>&1
     ```
3. Click **Add New Cron Job**.
   - This script creates online atomic backups using SQLite `VACUUM INTO`, compresses them (`.sqlite.gz`), and automatically prunes backups older than 14 days in `/home/hydro851/persistent/hydrox-website/backups/`.

---

## 10. Post-Migration Verification Checklist

- [ ] **Submit Test Booking**: Visit `https://hydrox.au/booking` and submit a test booking with photos.
- [ ] **Confirmation & Google Ads**: Verify the confirmation page loads with reference `HYD-2026...` and the Google Ads conversion event fires with `send_to: 'AW-18428986459/b0SgCPbm6-0cENuI0NNE'`.
- [ ] **Portal Sync**: Verify the new booking appears in `https://portal.hydrox.au`.
- [ ] **Photo Storage**: Confirm the booking photo is stored in `/home/hydro851/persistent/hydrox-website/storage/app/private/booking-photos/`.
- [ ] **Git Working Tree Cleanliness**: In cPanel Git Version Control, confirm working tree is clean and "Deploy HEAD Commit" remains active.

---

## 11. Rollback Procedure (If Ever Needed)

If you ever need to revert immediately to MySQL:

1. Open `/home/hydro851/persistent/hydrox-website/.env` in File Manager.
2. Change:
   ```dotenv
   DB_CONNECTION=mysql
   ```
3. Save the file.
4. In cPanel Git Version Control, click **Deploy HEAD Commit**.
5. The live site is instantly running on the MySQL `hydro851_dev` database. Zero MySQL data was lost or modified.
