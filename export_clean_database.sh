#!/bin/bash
################################################################################
# Export Clean Database - Pure SQL Only
# 
# Exports database with minimal comments, pure SQL statements only
# Compatible with phpMyAdmin import
################################################################################

set -e

# Configuration
DB_CONTAINER="vtiger_db"
DB_NAME="TDB1"
DB_USER="root"
DB_PASSWORD="132120"
OUTPUT_FILE="vtiger_prod_clean.sql"
BACKUP_DIR="backup/db"

# Colors
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m'

echo "=========================================="
echo "Export Clean Database (Pure SQL)"
echo "=========================================="
echo ""

# Check if Docker container is running
if ! docker ps | grep -q "$DB_CONTAINER"; then
    echo -e "${RED}ERROR: Docker container '$DB_CONTAINER' is not running${NC}"
    exit 1
fi

echo -e "${GREEN}✓ Docker container '$DB_CONTAINER' is running${NC}"

# Create backup directory
mkdir -p "$BACKUP_DIR"
OUTPUT_PATH="$BACKUP_DIR/$OUTPUT_FILE"

echo ""
echo "Exporting database '$DB_NAME' (pure SQL mode)..."
echo "Output file: $OUTPUT_PATH"
echo ""

# Export with minimal comments, pure SQL
docker exec "$DB_CONTAINER" mysqldump \
    -u"$DB_USER" \
    -p"$DB_PASSWORD" \
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
    --skip-comments \
    --skip-tz-utc \
    --no-create-info=false \
    --complete-insert \
    --extended-insert \
    --compact \
    "$DB_NAME" 2>/dev/null > "$OUTPUT_PATH.tmp"

# Check if export was successful
if [ $? -ne 0 ] || [ ! -f "$OUTPUT_PATH.tmp" ]; then
    echo -e "${RED}✗ ERROR: Database export failed!${NC}"
    exit 1
fi

echo -e "${GREEN}✓ Initial export completed${NC}"
echo ""

# Clean the SQL file - remove all non-essential content
echo "Cleaning SQL file (removing comments and metadata)..."

# Step 1: Remove mysqldump version comments and headers
sed -i.tmp \
    -e '/^-- MySQL dump/d' \
    -e '/^-- Host:/d' \
    -e '/^-- Server version/d' \
    -e '/^-- Dump completed on/d' \
    -e '/^-- --------------------------------------------------------/d' \
    -e '/^--$/d' \
    -e '/^mysqldump:/d' \
    -e '/\[Warning\]/d' \
    -e '/Using a password/d' \
    "$OUTPUT_PATH.tmp"

# Step 2: Remove SET statements that may cause issues
sed -i.tmp \
    -e '/^\/\*!40101 SET @OLD_CHARACTER_SET_CLIENT/d' \
    -e '/^\/\*!40101 SET @OLD_CHARACTER_SET_RESULTS/d' \
    -e '/^\/\*!40101 SET @OLD_COLLATION_CONNECTION/d' \
    -e '/^\/\*!50503 SET NAMES/d' \
    -e '/^\/\*!40103 SET @OLD_TIME_ZONE/d' \
    -e '/^\/\*!40103 SET TIME_ZONE/d' \
    -e '/^\/\*!40014 SET @OLD_UNIQUE_CHECKS/d' \
    -e '/^\/\*!40014 SET @OLD_FOREIGN_KEY_CHECKS/d' \
    -e '/^\/\*!40101 SET @OLD_SQL_MODE/d' \
    -e '/^\/\*!40111 SET @OLD_SQL_NOTES/d' \
    -e '/^\/\*!40101 SET CHARACTER_SET_CLIENT/d' \
    -e '/^\/\*!40101 SET CHARACTER_SET_RESULTS/d' \
    -e '/^\/\*!40101 SET COLLATION_CONNECTION/d' \
    -e '/^\/\*!40103 SET TIME_ZONE/d' \
    -e '/^\/\*!40014 SET UNIQUE_CHECKS/d' \
    -e '/^\/\*!40014 SET FOREIGN_KEY_CHECKS/d' \
    -e '/^\/\*!40101 SET SQL_MODE/d' \
    -e '/^\/\*!40111 SET SQL_NOTES/d' \
    "$OUTPUT_PATH.tmp"

# Step 3: Remove empty lines at the beginning
sed -i.tmp '/./,$!d' "$OUTPUT_PATH.tmp"

# Step 4: Remove DEFINER clauses from routines and views
sed -i.tmp \
    -e 's/DEFINER=[^ ]* //g' \
    -e 's/DEFINER=[^ ]*//g' \
    -e 's/SQL SECURITY DEFINER/SQL SECURITY INVOKER/g' \
    "$OUTPUT_PATH.tmp"

# Step 5: Remove USE database statement (not needed, import to selected database)
sed -i.tmp '/^USE `/d' "$OUTPUT_PATH.tmp"

# Step 6: Remove "Current Database" comments
sed -i.tmp '/^-- Current Database:/d' "$OUTPUT_PATH.tmp"

# Step 7: Remove temporary file
rm -f "$OUTPUT_PATH.tmp.tmp"

# Step 8: Ensure file starts with CREATE or INSERT
FIRST_LINE=$(head -1 "$OUTPUT_PATH.tmp")
if [[ ! "$FIRST_LINE" =~ ^(CREATE|INSERT|DROP|ALTER|/\*) ]]; then
    echo -e "${YELLOW}⚠ Finding first valid SQL statement...${NC}"
    # Find first line with CREATE, INSERT, DROP, or ALTER
    FIRST_VALID=$(grep -n "^CREATE\|^INSERT\|^DROP\|^ALTER\|^/\*" "$OUTPUT_PATH.tmp" | head -1 | cut -d: -f1)
    if [ ! -z "$FIRST_VALID" ] && [ "$FIRST_VALID" -gt 1 ]; then
        sed -i.tmp "1,$((FIRST_VALID-1))d" "$OUTPUT_PATH.tmp"
        echo -e "${GREEN}✓ Removed $((FIRST_VALID-1)) invalid lines from start${NC}"
    fi
fi

# Move to final location
mv "$OUTPUT_PATH.tmp" "$OUTPUT_PATH"

# Verify file is not empty
if [ ! -s "$OUTPUT_PATH" ]; then
    echo -e "${RED}✗ ERROR: SQL file is empty after cleaning!${NC}"
    exit 1
fi

FILE_SIZE=$(du -h "$OUTPUT_PATH" | cut -f1)
LINE_COUNT=$(wc -l < "$OUTPUT_PATH")

echo -e "${GREEN}✓ Cleaning completed!${NC}"
echo ""
echo "File: $OUTPUT_PATH"
echo "Size: $FILE_SIZE"
echo "Lines: $LINE_COUNT"
echo ""

# Verify SQL file
echo "Verifying SQL file..."

# Check for SQL statements
CREATE_COUNT=$(grep -c "^CREATE TABLE" "$OUTPUT_PATH" 2>/dev/null || echo "0")
INSERT_COUNT=$(grep -c "^INSERT INTO" "$OUTPUT_PATH" 2>/dev/null || echo "0")
WARNING_COUNT=$(grep -ci "warning\|mysqldump:" "$OUTPUT_PATH" 2>/dev/null || echo "0")
COMMENT_COUNT=$(grep -c "^--" "$OUTPUT_PATH" 2>/dev/null || echo "0")

echo ""
echo "SQL Statistics:"
echo "  CREATE TABLE: $CREATE_COUNT"
echo "  INSERT INTO: $INSERT_COUNT"
echo "  Warnings: $WARNING_COUNT"
echo "  Comments: $COMMENT_COUNT"
echo ""

if [ "$CREATE_COUNT" -gt 0 ] && [ "$INSERT_COUNT" -gt 0 ] && [ "$WARNING_COUNT" -eq 0 ]; then
    echo -e "${GREEN}✓ File is clean and ready for import!${NC}"
    
    # Show first few lines
    echo ""
    echo "First 5 lines of SQL file:"
    head -5 "$OUTPUT_PATH"
    echo ""
else
    echo -e "${YELLOW}⚠ Warning: File may need manual review${NC}"
fi

echo ""
echo "=========================================="
echo -e "${GREEN}Export completed!${NC}"
echo "=========================================="
echo ""
echo "Clean SQL file: $OUTPUT_PATH"
echo ""
echo "This file contains pure SQL statements only:"
echo "  - CREATE TABLE statements"
echo "  - INSERT INTO statements"
echo "  - No comments"
echo "  - No SET statements"
echo "  - No warnings"
echo ""
echo "Ready for phpMyAdmin import!"

