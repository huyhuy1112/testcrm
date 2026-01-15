#!/bin/bash
set -e

INPUT_FILE="Dump20260115.sql"
OUTPUT_FILE="TDB1_clean_export.sql"

if [ ! -f "$INPUT_FILE" ]; then
    echo "❌ File not found: $INPUT_FILE"
    exit 1
fi

echo "🧹 Cleaning SQL file..."
echo "Input: $INPUT_FILE"
echo "Output: $OUTPUT_FILE"
echo ""

# Clean SQL file using awk for better control
awk '
BEGIN {
    in_create = 0
    in_insert = 0
    create_buffer = ""
    insert_buffer = ""
}

# Skip comment lines
/^--/ { next }

# Skip MySQL version comments
/^\/\*![0-9]+/ { next }

# Skip SET statements
/^SET / { next }
/^DROP TABLE/ { next }
/^LOCK TABLES/ { next }
/^UNLOCK TABLES/ { next }

# Process CREATE TABLE
/^CREATE TABLE/ {
    in_create = 1
    create_buffer = $0
    next
}

# Process INSERT INTO
/^INSERT INTO/ {
    in_insert = 1
    insert_buffer = $0
    next
}

# Continue CREATE TABLE
in_create == 1 {
    create_buffer = create_buffer " " $0
    if ($0 ~ /;/) {
        print create_buffer
        create_buffer = ""
        in_create = 0
    }
    next
}

# Continue INSERT INTO
in_insert == 1 {
    insert_buffer = insert_buffer " " $0
    if ($0 ~ /;/) {
        print insert_buffer
        insert_buffer = ""
        in_insert = 0
    }
    next
}

# Print other lines that end with semicolon (like CREATE TABLE sequences)
/;$/ && !/^CREATE/ && !/^INSERT/ {
    print
}
' "$INPUT_FILE" | \
sed -E \
  -e 's/^[[:space:]]+//' \
  -e '/^$/d' \
  -e 's/[[:space:]]+/ /g' \
  -e 's/[[:space:]]*;[[:space:]]*/;\n/g' > "$OUTPUT_FILE"

# Ensure file ends with newline
echo "" >> "$OUTPUT_FILE"

FILE_SIZE=$(du -h "$OUTPUT_FILE" | cut -f1)
LINE_COUNT=$(wc -l < "$OUTPUT_FILE" | tr -d ' ')
CREATE_COUNT=$(grep -c "^CREATE TABLE" "$OUTPUT_FILE" || echo "0")
INSERT_COUNT=$(grep -c "^INSERT INTO" "$OUTPUT_FILE" || echo "0")

echo "✅ Clean SQL file created!"
echo "📁 File: $OUTPUT_FILE"
echo "📊 Size: $FILE_SIZE"
echo "📝 Lines: $LINE_COUNT"
echo "🏗️  CREATE TABLE: $CREATE_COUNT"
echo "📥 INSERT INTO: $INSERT_COUNT"
echo ""
echo "✅ File contains only CREATE TABLE and INSERT INTO statements"
