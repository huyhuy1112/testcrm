#!/bin/bash
################################################################################
# Vtiger CRM Database Export Script
# 
# Exports TDB1 database from Docker to SQL file compatible with cPanel
# 
# Usage: ./export_database.sh
################################################################################

set -e  # Exit on error

# Configuration
DB_CONTAINER="vtiger_db"
DB_NAME="TDB1"
DB_USER="root"
DB_PASSWORD="132120"
OUTPUT_FILE="vtiger_prod.sql"
BACKUP_DIR="backup/db"

# Colors for output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

echo "=========================================="
echo "Vtiger CRM Database Export"
echo "=========================================="
echo ""

# Check if Docker container is running
if ! docker ps | grep -q "$DB_CONTAINER"; then
    echo -e "${RED}ERROR: Docker container '$DB_CONTAINER' is not running${NC}"
    exit 1
fi

echo -e "${GREEN}✓ Docker container '$DB_CONTAINER' is running${NC}"

# Create backup directory if it doesn't exist
mkdir -p "$BACKUP_DIR"

# Full path to output file
OUTPUT_PATH="$BACKUP_DIR/$OUTPUT_FILE"

echo ""
echo "Exporting database '$DB_NAME'..."
echo "Output file: $OUTPUT_PATH"
echo ""

# Export database with cPanel-compatible settings
# Redirect stderr to /dev/null to prevent warnings from being written to SQL file
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
    --no-create-info=false \
    --complete-insert \
    --extended-insert \
    "$DB_NAME" 2>/dev/null > "$OUTPUT_PATH"

# Check if export was successful
if [ $? -eq 0 ] && [ -f "$OUTPUT_PATH" ]; then
    FILE_SIZE=$(du -h "$OUTPUT_PATH" | cut -f1)
    LINE_COUNT=$(wc -l < "$OUTPUT_PATH")
    
    echo ""
    echo -e "${GREEN}✓ Database export completed successfully!${NC}"
    echo ""
    echo "File: $OUTPUT_PATH"
    echo "Size: $FILE_SIZE"
    echo "Lines: $LINE_COUNT"
    echo ""
    
    # Post-process to remove DEFINER clauses (cPanel compatibility)
    echo "Removing DEFINER clauses for cPanel compatibility..."
    
    # Create temporary file
    TEMP_FILE="${OUTPUT_PATH}.tmp"
    
    # Remove DEFINER from CREATE statements
    sed -i.bak \
        -e "s/DEFINER=[^ ]* //g" \
        -e "s/DEFINER=[^ ]*//g" \
        -e "s/SQL SECURITY DEFINER/SQL SECURITY INVOKER/g" \
        "$OUTPUT_PATH"
    
    # Remove backup file created by sed
    rm -f "${OUTPUT_PATH}.bak"
    
    echo -e "${GREEN}✓ DEFINER clauses removed${NC}"
    echo ""
    
    # Verify SQL file
    echo "Verifying SQL file..."
    
    # Check for common SQL statements
    if grep -q "CREATE TABLE" "$OUTPUT_PATH" && \
       grep -q "INSERT INTO" "$OUTPUT_PATH"; then
        echo -e "${GREEN}✓ SQL file contains CREATE TABLE and INSERT statements${NC}"
    else
        echo -e "${YELLOW}⚠ Warning: SQL file may be incomplete${NC}"
    fi
    
    # Check file is not empty
    if [ ! -s "$OUTPUT_PATH" ]; then
        echo -e "${RED}✗ ERROR: SQL file is empty!${NC}"
        exit 1
    fi
    
    echo ""
    echo "=========================================="
    echo -e "${GREEN}Export completed successfully!${NC}"
    echo "=========================================="
    echo ""
    echo "Next steps:"
    echo "1. Upload $OUTPUT_PATH to cPanel"
    echo "2. Import via phpMyAdmin → Import tab"
    echo "3. Select file and click 'Go'"
    echo ""
    
else
    echo -e "${RED}✗ ERROR: Database export failed!${NC}"
    exit 1
fi

