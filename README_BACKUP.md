# Database Backup & Restore System

## 📋 Overview

This backup system provides **automated, Docker-based database backups** for the Vtiger CRM setup. All backups are performed **inside the Docker container** to ensure consistency and avoid compatibility issues.

---

## 🎯 Why Docker-Based Backup?

### ❌ Problems with MySQL Workbench Export

1. **Version Mismatch**: Workbench may use different MySQL client version than Docker container
2. **Character Encoding**: Direct export can cause encoding issues (UTF-8, collation)
3. **Inconsistent State**: Database may be modified during export, causing inconsistent backups
4. **Missing Features**: Workbench export may miss stored procedures, triggers, or events
5. **No Transaction Safety**: Direct export doesn't use `--single-transaction` for InnoDB consistency

### ✅ Benefits of Docker-Based Backup

1. **Consistent Environment**: Uses same MySQL version as production
2. **Transaction Safety**: `--single-transaction` ensures consistent InnoDB backup
3. **Complete Backup**: Includes routines, triggers, and events
4. **Automated**: Can be scheduled via cron
5. **Source of Truth**: Docker container is the authoritative source

---

## 📁 Directory Structure

```
/backup
├── db/              # Backup SQL files
│   └── TDB1_backup_YYYYMMDD_HHMM.sql
└── logs/            # Backup operation logs
    └── backup.log
```

---

## 🚀 Usage

### Backup Database

**Command:**
```bash
./backup_db.sh
```

**What it does:**
1. Checks Docker daemon is running
2. Checks container `vtiger_db` is running
3. Checks database `TDB1` exists
4. Creates backup file: `backup/db/TDB1_backup_YYYYMMDD_HHMM.sql`
5. Logs operation to `backup/logs/backup.log`

**Example Output:**
```
INFO: Docker daemon is running
INFO: Container 'vtiger_db' is running
INFO: Database 'TDB1' exists
INFO: Starting backup of database 'TDB1'...
SUCCESS: Backup completed successfully
Backup completed: backup/db/TDB1_backup_20260107_1430.sql (15M)
```

### Restore Database

**Command:**
```bash
./restore_db.sh backup/db/TDB1_backup_20260107_1430.sql
```

**What it does:**
1. Validates backup file exists and is readable
2. Asks for confirmation (type `yes` to confirm)
3. Restores database `TDB1` from backup file
4. Logs operation to `backup/logs/backup.log`

**Example Output:**
```
INFO: Docker daemon is running
INFO: Container 'vtiger_db' is running
INFO: Backup file validated: backup/db/TDB1_backup_20260107_1430.sql
INFO: File size: 15M

========================================
WARNING: Database Restore
========================================

This will RESTORE database 'TDB1' from backup file:
  backup/db/TDB1_backup_20260107_1430.sql

WARNING: This will OVERWRITE all existing data in database 'TDB1'

Are you sure you want to continue? (yes/no): yes

INFO: Starting restore of database 'TDB1'...
SUCCESS: Database restore completed successfully
Restore completed successfully!
```

---

## ⚙️ Configuration

### Environment Variables

The scripts automatically detect MySQL root password from:
1. **Environment variable**: `MYSQL_ROOT_PASSWORD`
2. **docker-compose.yml**: Extracts from `MYSQL_ROOT_PASSWORD` in db service

**To set environment variable:**
```bash
export MYSQL_ROOT_PASSWORD=132120
./backup_db.sh
```

**Or inline:**
```bash
MYSQL_ROOT_PASSWORD=132120 ./backup_db.sh
```

### Script Configuration

Edit these variables at the top of `backup_db.sh` and `restore_db.sh`:

```bash
CONTAINER_NAME="vtiger_db"    # Docker container name
DATABASE_NAME="TDB1"          # Database name
MYSQL_USER="root"             # MySQL user
BACKUP_DIR="backup/db"        # Backup directory
LOG_DIR="backup/logs"         # Log directory
```

---

## 🔧 Automation (Cron)

### Schedule Daily Backups

**Add to crontab:**
```bash
crontab -e
```

**Add line (backup at 2 AM daily):**
```bash
0 2 * * * cd /path/to/Backup && ./backup_db.sh
```

**Or with environment variable:**
```bash
0 2 * * * cd /path/to/Backup && MYSQL_ROOT_PASSWORD=132120 ./backup_db.sh
```

### Schedule Weekly Backups

**Backup every Sunday at 3 AM:**
```bash
0 3 * * 0 cd /path/to/Backup && ./backup_db.sh
```

---

## 🐛 Common Failure Cases

### 1. Docker Daemon Not Running

**Error:**
```
ERROR: Docker daemon is not running. Please start Docker Desktop.
```

**Solution:**
```bash
# Start Docker Desktop (macOS/Windows)
# Or start Docker service (Linux)
sudo systemctl start docker
```

---

### 2. Container Not Running

**Error:**
```
ERROR: Container 'vtiger_db' is not running. Please start it with: docker compose up -d
```

**Solution:**
```bash
cd /path/to/Backup
docker compose up -d
```

**Verify:**
```bash
docker ps | grep vtiger_db
```

---

### 3. Database Not Found

**Error:**
```
ERROR: Database 'TDB1' does not exist in container 'vtiger_db'
```

**Solution:**
1. Check database name in `docker-compose.yml`
2. Verify database was created:
   ```bash
   docker exec -it vtiger_db mysql -uroot -p132120 -e "SHOW DATABASES;"
   ```
3. Create database if missing:
   ```bash
   docker exec -it vtiger_db mysql -uroot -p132120 -e "CREATE DATABASE TDB1;"
   ```

---

### 4. Wrong Container Name

**Error:**
```
ERROR: Container 'vtiger_db' is not running.
```

**Solution:**
1. Check actual container name:
   ```bash
   docker ps --format '{{.Names}}'
   ```
2. Update `CONTAINER_NAME` in script if different

---

### 5. Permission Denied

**Error:**
```
bash: ./backup_db.sh: Permission denied
```

**Solution:**
```bash
chmod +x backup_db.sh restore_db.sh
```

---

### 6. Backup File Empty

**Error:**
```
ERROR: Backup file is empty: backup/db/TDB1_backup_20260107_1430.sql
```

**Possible Causes:**
- Database is empty
- mysqldump failed silently
- Disk space full

**Solution:**
1. Check disk space: `df -h`
2. Check database has data:
   ```bash
   docker exec -it vtiger_db mysql -uroot -p132120 -e "USE TDB1; SHOW TABLES;"
   ```
3. Check logs: `tail -f backup/logs/backup.log`

---

### 7. MySQL Password Not Set

**Error:**
```
ERROR: MYSQL_ROOT_PASSWORD not set.
```

**Solution:**
1. Set environment variable:
   ```bash
   export MYSQL_ROOT_PASSWORD=132120
   ```
2. Or ensure `docker-compose.yml` has `MYSQL_ROOT_PASSWORD` set

---

## 📊 Backup File Format

**Filename:**
```
TDB1_backup_YYYYMMDD_HHMM.sql
```

**Example:**
```
TDB1_backup_20260107_1430.sql
```

**Contents:**
- Complete database structure
- All table data
- Stored procedures and functions
- Triggers
- Events
- DROP statements for clean restore

---

## 🔒 Safety Features

✅ **Transaction Safety**: Uses `--single-transaction` for consistent InnoDB backup  
✅ **Validation**: Checks Docker, container, and database before backup  
✅ **Confirmation**: Asks for confirmation before restore  
✅ **Logging**: All operations logged with timestamps  
✅ **Error Handling**: Fails fast with clear error messages  
✅ **Idempotent**: Safe to run multiple times  

---

## 📝 Log File

**Location:** `backup/logs/backup.log`

**Format:**
```
[YYYY-MM-DD HH:MM:SS] [LEVEL] Message
```

**Example:**
```
[2026-01-07 14:30:15] [INFO] Docker daemon is running
[2026-01-07 14:30:15] [INFO] Container 'vtiger_db' is running
[2026-01-07 14:30:15] [INFO] Database 'TDB1' exists
[2026-01-07 14:30:15] [INFO] Starting backup of database 'TDB1'...
[2026-01-07 14:30:45] [SUCCESS] Backup completed successfully
[2026-01-07 14:30:45] [INFO] Backup file: backup/db/TDB1_backup_20260107_1430.sql
[2026-01-07 14:30:45] [INFO] File size: 15M
```

---

## 🧹 Cleanup Old Backups

**Manual cleanup:**
```bash
# Remove backups older than 30 days
find backup/db -name "TDB1_backup_*.sql" -mtime +30 -delete
```

**Automated cleanup (add to crontab):**
```bash
# Cleanup old backups every Sunday at 4 AM
0 4 * * 0 cd /path/to/Backup && find backup/db -name "TDB1_backup_*.sql" -mtime +30 -delete
```

---

## ✅ Best Practices

1. **Regular Backups**: Schedule daily backups via cron
2. **Test Restores**: Periodically test restore to ensure backups work
3. **Monitor Logs**: Check `backup/logs/backup.log` regularly
4. **Keep Multiple Backups**: Don't delete all old backups immediately
5. **Offsite Backup**: Copy backups to external storage/cloud
6. **Before Major Changes**: Always backup before:
   - Database migrations
   - Vtiger upgrades
   - Major data imports
   - Schema changes

---

## 🔍 Verification

**Check backup file is valid:**
```bash
# View first few lines
head -n 20 backup/db/TDB1_backup_20260107_1430.sql

# Check file size
ls -lh backup/db/TDB1_backup_20260107_1430.sql

# Verify SQL syntax (basic check)
grep -i "CREATE TABLE\|INSERT INTO" backup/db/TDB1_backup_20260107_1430.sql | head -5
```

**Test restore on test database:**
```bash
# Create test database
docker exec -it vtiger_db mysql -uroot -p132120 -e "CREATE DATABASE TDB1_test;"

# Restore to test database (modify restore script temporarily)
# This verifies backup is valid without affecting production
```

---

## 📞 Support

**If backup fails:**
1. Check `backup/logs/backup.log` for detailed error messages
2. Verify Docker is running: `docker ps`
3. Verify container is running: `docker ps | grep vtiger_db`
4. Verify database exists: `docker exec -it vtiger_db mysql -uroot -p132120 -e "SHOW DATABASES;"`
5. Check disk space: `df -h`

---

**Last Updated:** 2026-01-07  
**Status:** ✅ Production Ready


