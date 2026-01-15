#!/bin/bash
################################################################################
# Clean SQL File - Remove mysqldump warnings and errors
# 
# Removes warning messages that may have been written to SQL file
# Usage: ./clean_sql_file.sh [sql_file_path]
################################################################################

set -e

# Default file if not specified
SQL_FILE="${1:-backup/db/vtiger_prod.sql}"

# Colors
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m'

echo "=========================================="
echo "SQL File Cleaner"
echo "=========================================="
echo ""

# Check if file exists
if [ ! -f "$SQL_FILE" ]; then
    echo -e "${RED}ERROR: File not found: $SQL_FILE${NC}"
    exit 1
fi

echo "Input file: $SQL_FILE"
ORIGINAL_SIZE=$(du -h "$SQL_FILE" | cut -f1)
echo "Original size: $ORIGINAL_SIZE"
echo ""

# Create backup
BACKUP_FILE="${SQL_FILE}.backup_$(date +%Y%m%d_%H%M%S)"
cp "$SQL_FILE" "$BACKUP_FILE"
echo -e "${GREEN}✓ Backup created: $BACKUP_FILE${NC}"
echo ""

# Clean the file
echo "Cleaning SQL file..."

# Remove mysqldump warning lines
# Pattern: Lines starting with "mysqldump:" or containing "[Warning]"
sed -i.tmp \
    -e '/^mysqldump:/d' \
    -e '/\[Warning\]/d' \
    -e '/Using a password on the command line/d' \
    -e '/^-- MySQL dump/d' \
    -e '/^-- Host:/d' \
    -e '/^-- Server version/d' \
    "$SQL_FILE"

# Remove empty lines at the beginning
sed -i.tmp '/./,$!d' "$SQL_FILE"

# Ensure file starts with valid SQL (either comment or SQL statement)
# If file doesn't start with -- or valid SQL, find first valid line
FIRST_LINE=$(head -1 "$SQL_FILE")
if [[ ! "$FIRST_LINE" =~ ^(--|/\*|CREATE|INSERT|SET|USE|DROP) ]]; then
    echo -e "${YELLOW}⚠ First line doesn't look like SQL, finding first valid line...${NC}"
    # Find first line that looks like SQL
    FIRST_VALID=$(grep -n "^--\|^/\*\|^CREATE\|^INSERT\|^SET\|^USE\|^DROP" "$SQL_FILE" | head -1 | cut -d: -f1)
    if [ ! -z "$FIRST_VALID" ] && [ "$FIRST_VALID" -gt 1 ]; then
        sed -i.tmp "1,$((FIRST_VALID-1))d" "$SQL_FILE"
        echo -e "${GREEN}✓ Removed $((FIRST_VALID-1)) invalid lines from start${NC}"
    fi
fi

# Remove temporary file created by sed
rm -f "${SQL_FILE}.tmp"

# Verify file is not empty
if [ ! -s "$SQL_FILE" ]; then
    echo -e "${RED}✗ ERROR: File is empty after cleaning!${NC}"
    echo "Restoring from backup..."
    cp "$BACKUP_FILE" "$SQL_FILE"
    exit 1
fi

NEW_SIZE=$(du -h "$SQL_FILE" | cut -f1)
echo ""
echo -e "${GREEN}✓ Cleaning completed!${NC}"
echo "New size: $NEW_SIZE"
echo ""

# Verify file starts with valid SQL
FIRST_LINE=$(head -1 "$SQL_FILE")
echo "First line: $FIRST_LINE"
echo ""

if [[ "$FIRST_LINE" =~ ^(--|/\*|CREATE|INSERT|SET|USE|DROP) ]]; then
    echo -e "${GREEN}✓ File starts with valid SQL${NC}"
else
    echo -e "${YELLOW}⚠ Warning: First line may not be valid SQL${NC}"
fi

# Check for remaining warnings
WARNING_COUNT=$(grep -ci "warning\|mysqldump:" "$SQL_FILE" || echo "0")
if [ "$WARNING_COUNT" -gt 0 ]; then
    echo -e "${YELLOW}⚠ Found $WARNING_COUNT potential warning lines remaining${NC}"
    echo "You may need to manually review the file"
else
    echo -e "${GREEN}✓ No warning messages found${NC}"
fi

# Count SQL statements
CREATE_COUNT=$(grep -c "^CREATE TABLE" "$SQL_FILE" || echo "0")
INSERT_COUNT=$(grep -c "^INSERT INTO" "$SQL_FILE" || echo "0")

echo ""
echo "SQL Statistics:"
echo "  CREATE TABLE statements: $CREATE_COUNT"
echo "  INSERT INTO statements: $INSERT_COUNT"
echo ""

if [ "$CREATE_COUNT" -gt 0 ] && [ "$INSERT_COUNT" -gt 0 ]; then
    echo -e "${GREEN}✓ File appears to be valid SQL dump${NC}"
else
    echo -e "${RED}✗ WARNING: File may be corrupted or incomplete${NC}"
fi

echo ""
echo "=========================================="
echo -e "${GREEN}Cleanup completed!${NC}"
echo "=========================================="
echo ""
echo "Cleaned file: $SQL_FILE"
echo "Backup saved: $BACKUP_FILE"
echo ""
echo "You can now import this file via phpMyAdmin"


