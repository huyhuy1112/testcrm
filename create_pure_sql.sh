#!/bin/bash
################################################################################
# Create Pure SQL File - Only CREATE TABLE and INSERT INTO
# 
# Removes ALL comments, SET statements, and keeps only pure SQL
################################################################################

set -e

INPUT_FILE="${1:-backup/db/vtiger_prod_clean.sql}"
OUTPUT_FILE="backup/db/vtiger_prod_pure.sql"

# Colors
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m'

echo "=========================================="
echo "Create Pure SQL File"
echo "=========================================="
echo ""

if [ ! -f "$INPUT_FILE" ]; then
    echo -e "${RED}ERROR: Input file not found: $INPUT_FILE${NC}"
    exit 1
fi

echo "Input: $INPUT_FILE"
ORIGINAL_SIZE=$(du -h "$INPUT_FILE" | cut -f1)
echo "Original size: $ORIGINAL_SIZE"
echo ""

# Create backup
BACKUP_FILE="${INPUT_FILE}.backup_$(date +%Y%m%d_%H%M%S)"
cp "$INPUT_FILE" "$BACKUP_FILE"
echo -e "${GREEN}✓ Backup created${NC}"
echo ""

echo "Creating pure SQL file..."

# Step 1: Remove all SET statements and conditional comments
sed \
    -e '/^\/\*!40101 SET @saved_cs_client/d' \
    -e '/^\/\*!50503 SET character_set_client/d' \
    -e '/^\/\*!40101 SET character_set_client = @saved_cs_client/d' \
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
    -e '/^\/\*!40000 DROP DATABASE/d' \
    -e '/^CREATE DATABASE/d' \
    -e '/^USE `/d' \
    -e '/^-- Current Database:/d' \
    -e '/^--$/d' \
    -e '/^-- Table structure/d' \
    -e '/^-- Dumping data/d' \
    -e '/saved_cs_client/d' \
    -e '/character_set_client/d' \
    "$INPUT_FILE" > "$OUTPUT_FILE.tmp"

# Step 2: Remove empty lines
sed -i.tmp '/^$/d' "$OUTPUT_FILE.tmp"

# Step 3: Remove any remaining comment lines
sed -i.tmp '/^--/d' "$OUTPUT_FILE.tmp"

# Step 4: Remove any remaining conditional comment blocks
sed -i.tmp '/^\/\*!/d' "$OUTPUT_FILE.tmp"

# Step 5: Remove lines that are just semicolons
sed -i.tmp '/^;$/d' "$OUTPUT_FILE.tmp"

# Step 6: Remove temporary file
rm -f "$OUTPUT_FILE.tmp.tmp"

# Step 7: Ensure file starts with CREATE, INSERT, DROP, or ALTER
FIRST_LINE=$(head -1 "$OUTPUT_FILE.tmp")
if [[ ! "$FIRST_LINE" =~ ^(CREATE|INSERT|DROP|ALTER) ]]; then
    echo -e "${YELLOW}⚠ Finding first valid SQL statement...${NC}"
    FIRST_VALID=$(grep -n "^CREATE\|^INSERT\|^DROP\|^ALTER" "$OUTPUT_FILE.tmp" | head -1 | cut -d: -f1)
    if [ ! -z "$FIRST_VALID" ] && [ "$FIRST_VALID" -gt 1 ]; then
        sed -i.tmp "1,$((FIRST_VALID-1))d" "$OUTPUT_FILE.tmp"
        echo -e "${GREEN}✓ Removed $((FIRST_VALID-1)) invalid lines from start${NC}"
    fi
fi

# Move to final location
mv "$OUTPUT_FILE.tmp" "$OUTPUT_FILE"

# Verify
if [ ! -s "$OUTPUT_FILE" ]; then
    echo -e "${RED}✗ ERROR: Output file is empty!${NC}"
    exit 1
fi

NEW_SIZE=$(du -h "$OUTPUT_FILE" | cut -f1)
LINE_COUNT=$(wc -l < "$OUTPUT_FILE")

echo -e "${GREEN}✓ Pure SQL file created!${NC}"
echo ""
echo "Output: $OUTPUT_FILE"
echo "Size: $NEW_SIZE"
echo "Lines: $LINE_COUNT"
echo ""

# Verify
CREATE_COUNT=$(grep -c "^CREATE TABLE" "$OUTPUT_FILE" 2>/dev/null || echo "0")
INSERT_COUNT=$(grep -c "^INSERT INTO" "$OUTPUT_FILE" 2>/dev/null || echo "0")
DROP_COUNT=$(grep -c "^DROP TABLE" "$OUTPUT_FILE" 2>/dev/null || echo "0")
SET_COUNT=$(grep -c "SET @" "$OUTPUT_FILE" 2>/dev/null || echo "0")
COMMENT_COUNT=$(grep -c "^--\|^/\*" "$OUTPUT_FILE" 2>/dev/null || echo "0")

echo "Verification:"
echo "  CREATE TABLE: $CREATE_COUNT"
echo "  INSERT INTO: $INSERT_COUNT"
echo "  DROP TABLE: $DROP_COUNT"
echo "  SET statements: $SET_COUNT"
echo "  Comments: $COMMENT_COUNT"
echo ""

if [ "$CREATE_COUNT" -gt 0 ] && [ "$INSERT_COUNT" -gt 0 ] && [ "$SET_COUNT" -eq 0 ]; then
    echo -e "${GREEN}✓ File is pure SQL - ready for import!${NC}"
    echo ""
    echo "First 5 lines:"
    head -5 "$OUTPUT_FILE"
    echo ""
    echo "Last 3 lines:"
    tail -3 "$OUTPUT_FILE"
    echo ""
else
    echo -e "${YELLOW}⚠ File may need additional cleaning${NC}"
    if [ "$SET_COUNT" -gt 0 ]; then
        echo "  Still contains $SET_COUNT SET statements"
    fi
    if [ "$COMMENT_COUNT" -gt 0 ]; then
        echo "  Still contains $COMMENT_COUNT comment lines"
    fi
fi

echo ""
echo "=========================================="
echo -e "${GREEN}Complete!${NC}"
echo "=========================================="
echo ""
echo "Pure SQL file: $OUTPUT_FILE"
echo "Ready for phpMyAdmin import!"
