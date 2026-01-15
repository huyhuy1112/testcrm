# Vtiger CRM Database Export Guide

**Purpose:** Export database from Docker to SQL file for cPanel import  
**Database:** TDB1  
**Output:** `backup/db/vtiger_prod.sql`

---

## Quick Export (Recommended)

### Option 1: Using Export Script (Easiest)

```bash
cd /Users/dangquochuy/Backup
./export_database.sh
```

**What it does:**
- Exports database with cPanel-compatible settings
- Removes DEFINER clauses automatically
- Validates the export
- Shows file size and statistics

---

### Option 2: Direct Docker Command (Manual)

**Standard Export (for databases < 200MB):**

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
    TDB1 > backup/db/vtiger_prod.sql
```

**Then remove DEFINER clauses:**

```bash
sed -i.bak \
    -e "s/DEFINER=[^ ]* //g" \
    -e "s/DEFINER=[^ ]*//g" \
    -e "s/SQL SECURITY DEFINER/SQL SECURITY INVOKER/g" \
    backup/db/vtiger_prod.sql

rm -f backup/db/vtiger_prod.sql.bak
```

---

### Option 3: Optimized for Large Databases (> 200MB)

**If database is large, use compression:**

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
    --compress \
    TDB1 | gzip > backup/db/vtiger_prod.sql.gz
```

**To decompress before import:**

```bash
gunzip backup/db/vtiger_prod.sql.gz
```

**Then remove DEFINER clauses (same as above).**

---

## Command Explanation

### Key Options for cPanel Compatibility

| Option | Purpose |
|--------|---------|
| `--single-transaction` | Creates consistent snapshot (no locks) |
| `--routines` | Includes stored procedures and functions |
| `--triggers` | Includes database triggers |
| `--events` | Includes scheduled events |
| `--quick` | Faster export for large tables |
| `--lock-tables=false` | No table locks (works with --single-transaction) |
| `--default-character-set=utf8mb4` | Ensures UTF-8 encoding |
| `--skip-add-drop-database` | Prevents DROP DATABASE (safer) |
| `--skip-add-locks` | No LOCK TABLES (cPanel compatible) |
| `--skip-disable-keys` | No ALTER TABLE DISABLE KEYS |
| `--complete-insert` | Full column names in INSERT (safer) |
| `--extended-insert` | Multiple rows per INSERT (smaller file) |

### Why Remove DEFINER?

- **cPanel shared hosting** doesn't allow SUPER privileges
- DEFINER clauses require SUPER privilege to import
- Removing them makes the dump compatible with shared hosting
- SQL SECURITY INVOKER uses the importing user's privileges

---

## Validation Checklist

After export, verify:

- [ ] **File exists:** `backup/db/vtiger_prod.sql` exists
- [ ] **File is not empty:** File size > 0 bytes
- [ ] **Contains CREATE TABLE:** `grep -q "CREATE TABLE" backup/db/vtiger_prod.sql`
- [ ] **Contains INSERT:** `grep -q "INSERT INTO" backup/db/vtiger_prod.sql`
- [ ] **No DEFINER clauses:** `grep -i "DEFINER" backup/db/vtiger_prod.sql` returns nothing
- [ ] **File size reasonable:** Should be 50-500MB for typical Vtiger database
- [ ] **Can be read:** `head -20 backup/db/vtiger_prod.sql` shows SQL statements

---

## Quick Validation Commands

```bash
# Check file exists and size
ls -lh backup/db/vtiger_prod.sql

# Check for CREATE TABLE statements
grep -c "CREATE TABLE" backup/db/vtiger_prod.sql

# Check for INSERT statements
grep -c "INSERT INTO" backup/db/vtiger_prod.sql

# Verify no DEFINER clauses (should return 0)
grep -ci "DEFINER" backup/db/vtiger_prod.sql

# Check first few lines
head -30 backup/db/vtiger_prod.sql
```

---

## Expected Output

**Successful export should show:**

```
==========================================
Vtiger CRM Database Export
==========================================

✓ Docker container 'vtiger_db' is running

Exporting database 'TDB1'...
Output file: backup/db/vtiger_prod.sql

Removing DEFINER clauses for cPanel compatibility...
✓ DEFINER clauses removed

Verifying SQL file...
✓ SQL file contains CREATE TABLE and INSERT statements

==========================================
Export completed successfully!
==========================================

File: backup/db/vtiger_prod.sql
Size: 150M (example)
Lines: 50000 (example)

Next steps:
1. Upload backup/db/vtiger_prod.sql to cPanel
2. Import via phpMyAdmin → Import tab
3. Select file and click 'Go'
```

---

## Import to cPanel

### Via phpMyAdmin

1. **Login to cPanel**
2. **Open phpMyAdmin**
3. **Select target database** (or create new one)
4. **Click Import tab**
5. **Choose file:** Select `vtiger_prod.sql`
6. **Click Go**
7. **Wait for import** (may take 5-15 minutes for large databases)

### Via Command Line (if SSH available)

```bash
mysql -uusername -ppassword database_name < vtiger_prod.sql
```

---

## Troubleshooting

### Export Fails

**Error: "Container not found"**
- Check container name: `docker ps | grep db`
- Update `DB_CONTAINER` in script if different

**Error: "Access denied"**
- Verify database password in `config.inc.php`
- Check database user has export privileges

**Error: "Out of disk space"**
- Check available disk space: `df -h`
- Use compression for large databases

### Import Fails in cPanel

**Error: "DEFINER" related**
- Re-run sed command to remove DEFINER clauses
- Ensure script removed all DEFINER references

**Error: "Max execution time exceeded"**
- Split import into smaller chunks
- Or increase PHP max_execution_time in phpMyAdmin

**Error: "File too large"**
- Use compression (gzip)
- Or split SQL file into multiple parts

---

## File Locations

- **Export Script:** `/Users/dangquochuy/Backup/export_database.sh`
- **Output File:** `/Users/dangquochuy/Backup/backup/db/vtiger_prod.sql`
- **This Guide:** `/Users/dangquochuy/Backup/EXPORT_DATABASE_GUIDE.md`

---

## Safety Notes

- ✅ **Read-only operation** - Does not modify source database
- ✅ **No data loss** - Original database remains intact
- ✅ **Reversible** - Can re-export if needed
- ✅ **Safe for production** - Uses consistent snapshot

---

**Last Updated:** January 10, 2025  
**Database:** TDB1  
**Vtiger Version:** 8.3.0


