# Database Export Commands - Quick Reference

## 1) Docker Command (Copy-Paste Ready)

### Standard Export (Recommended)

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
    TDB1 > backup/db/vtiger_prod.sql && \
sed -i.bak \
    -e "s/DEFINER=[^ ]* //g" \
    -e "s/DEFINER=[^ ]*//g" \
    -e "s/SQL SECURITY DEFINER/SQL SECURITY INVOKER/g" \
    backup/db/vtiger_prod.sql && \
rm -f backup/db/vtiger_prod.sql.bak && \
echo "✓ Export completed: backup/db/vtiger_prod.sql"
```

### Optimized for Large Databases (> 200MB)

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
    TDB1 | gzip > backup/db/vtiger_prod.sql.gz && \
echo "✓ Compressed export completed: backup/db/vtiger_prod.sql.gz"
```

**To decompress:**
```bash
gunzip backup/db/vtiger_prod.sql.gz
sed -i.bak \
    -e "s/DEFINER=[^ ]* //g" \
    -e "s/DEFINER=[^ ]*//g" \
    -e "s/SQL SECURITY DEFINER/SQL SECURITY INVOKER/g" \
    backup/db/vtiger_prod.sql && \
rm -f backup/db/vtiger_prod.sql.bak
```

---

## 2) Explanation

### Why These Options?

**`--single-transaction`**
- Creates a consistent snapshot without locking tables
- Essential for InnoDB tables (Vtiger uses InnoDB)
- Allows reads during export

**`--routines --triggers --events`**
- Includes stored procedures, functions, triggers, and events
- Ensures complete database export

**`--quick --lock-tables=false`**
- Faster export for large tables
- No table locks (works with --single-transaction)

**`--default-character-set=utf8mb4`**
- Ensures proper UTF-8 encoding
- Supports emojis and special characters

**`--skip-add-drop-database`**
- Prevents DROP DATABASE statement
- Safer for import (won't accidentally drop existing database)

**`--skip-add-locks --skip-disable-keys`**
- Removes LOCK TABLES and ALTER TABLE statements
- cPanel shared hosting doesn't allow these operations

**`--complete-insert --extended-insert`**
- Full column names in INSERT statements (safer)
- Multiple rows per INSERT (smaller file size)

**Removing DEFINER clauses:**
- cPanel shared hosting doesn't allow SUPER privileges
- DEFINER requires SUPER privilege to import
- Removing them makes dump compatible with shared hosting

---

## 3) Validation Checklist

After running export, verify:

```bash
# 1. File exists and has content
ls -lh backup/db/vtiger_prod.sql

# 2. Contains CREATE TABLE statements
grep -c "CREATE TABLE" backup/db/vtiger_prod.sql
# Expected: 500+ tables

# 3. Contains INSERT statements
grep -c "INSERT INTO" backup/db/vtiger_prod.sql
# Expected: 1000+ inserts

# 4. No DEFINER clauses (should return 0)
grep -ci "DEFINER" backup/db/vtiger_prod.sql
# Expected: 0

# 5. File size reasonable
du -h backup/db/vtiger_prod.sql
# Expected: 50-500MB for typical Vtiger database

# 6. Can read first lines
head -30 backup/db/vtiger_prod.sql
# Should show SQL statements, not binary data
```

**Quick validation (all-in-one):**

```bash
echo "=== Export Validation ===" && \
echo "File size: $(du -h backup/db/vtiger_prod.sql | cut -f1)" && \
echo "CREATE TABLE count: $(grep -c 'CREATE TABLE' backup/db/vtiger_prod.sql)" && \
echo "INSERT count: $(grep -c 'INSERT INTO' backup/db/vtiger_prod.sql)" && \
echo "DEFINER count: $(grep -ci 'DEFINER' backup/db/vtiger_prod.sql)" && \
echo "Status: $([ $(grep -ci 'DEFINER' backup/db/vtiger_prod.sql) -eq 0 ] && echo '✓ READY' || echo '✗ NEEDS FIX')"
```

---

## Alternative: Use Export Script

**Easiest method:**

```bash
cd /Users/dangquochuy/Backup
./export_database.sh
```

The script automatically:
- ✅ Exports database
- ✅ Removes DEFINER clauses
- ✅ Validates export
- ✅ Shows statistics

---

## Expected Results

**Successful export should show:**

```
File: backup/db/vtiger_prod.sql
Size: 150M (example, depends on data)
CREATE TABLE count: 526
INSERT count: 5000+ (depends on data)
DEFINER count: 0
Status: ✓ READY
```

---

## Import to cPanel

1. **Upload** `backup/db/vtiger_prod.sql` to cPanel File Manager
2. **Open phpMyAdmin** in cPanel
3. **Select database** (or create new)
4. **Click Import tab**
5. **Choose file:** Select `vtiger_prod.sql`
6. **Click Go**
7. **Wait** (5-15 minutes for large databases)

---

**Container Name:** `vtiger_db`  
**Database:** `TDB1`  
**User:** `root`  
**Password:** `132120`  
**Output:** `backup/db/vtiger_prod.sql`

