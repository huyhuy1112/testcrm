# SQL File Cleaning Guide

## Problem

When exporting database using mysqldump, warning messages can be written to the SQL file:

```
mysqldump: [Warning] Using a password on the command line interface can be insecure.
```

This causes import errors in phpMyAdmin:
- "Gặp ký tự không cần" (Unexpected character)
- "Gặp phần đầu mệnh đề không cần" (Unexpected clause)
- SQL syntax errors

## Solution

### Option 1: Clean Existing SQL File (Quick Fix)

```bash
cd /Users/dangquochuy/Backup
./clean_sql_file.sh backup/db/TDB1_backup_20260107_1630.sql
```

**What it does:**
- Removes mysqldump warning lines
- Removes invalid header lines
- Ensures file starts with valid SQL
- Creates backup before cleaning
- Validates the cleaned file

### Option 2: Manual Clean (One-Line Command)

```bash
cd /Users/dangquochuy/Backup
sed -i.bak \
    -e '/^mysqldump:/d' \
    -e '/\[Warning\]/d' \
    -e '/Using a password on the command line/d' \
    backup/db/TDB1_backup_20260107_1630.sql && \
rm -f backup/db/TDB1_backup_20260107_1630.sql.bak
```

### Option 3: Re-export with Fixed Script

The `export_database.sh` script has been updated to prevent warnings:

```bash
cd /Users/dangquochuy/Backup
./export_database.sh
```

This will create a clean `backup/db/vtiger_prod.sql` file.

---

## Verification

After cleaning, verify the file:

```bash
# Check first line (should be valid SQL comment or statement)
head -1 backup/db/TDB1_backup_20260107_1630.sql

# Should show: -- MySQL dump... or /*!40101 SET... or CREATE TABLE...

# Check for warnings (should return 0)
grep -ci "mysqldump:\|\[Warning\]" backup/db/TDB1_backup_20260107_1630.sql

# Check SQL statements exist
grep -c "CREATE TABLE" backup/db/TDB1_backup_20260107_1630.sql
grep -c "INSERT INTO" backup/db/TDB1_backup_20260107_1630.sql
```

---

## Updated Export Command (Prevents Warnings)

**Fixed command (redirects stderr to prevent warnings in SQL file):**

```bash
docker exec vtiger_db mysqldump \
    -uroot -p132120 \
    --single-transaction \
    --routines \
    --triggers \
    --events \
    --quick \
    --lock-tables=false \
    --default-character-set=utf8mb4 \
    --skip-add-drop-database \
    --skip-add-locks \
    --skip-disable-keys \
    --skip-set-charset \
    --complete-insert \
    --extended-insert \
    TDB1 2>/dev/null > backup/db/vtiger_prod.sql
```

**Key change:** `2>/dev/null` redirects stderr (warnings) away from the SQL file.

---

## Files

- **clean_sql_file.sh** - Automated cleaning script
- **export_database.sh** - Updated export script (prevents warnings)
- **CLEAN_SQL_GUIDE.md** - This guide

---

**Status:** ✅ SQL file cleaning script ready  
**File to clean:** `backup/db/TDB1_backup_20260107_1630.sql`

