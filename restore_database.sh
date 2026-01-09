#!/bin/bash

# Script to restore Vtiger database from SQL backup file
# Usage: ./restore_database.sh [backup_file.sql]

DB_CONTAINER="vtiger_db"
DB_NAME="TDB1"
DB_USER="root"
DB_PASS="132120"

echo "🔍 Vtiger Database Restore Script"
echo "===================================="

# Check if backup file is provided
if [ -z "$1" ]; then
    echo ""
    echo "❌ No backup file provided!"
    echo ""
    echo "📋 Usage:"
    echo "   ./restore_database.sh /path/to/backup.sql"
    echo ""
    echo "💡 To find backup files, run:"
    echo "   find . -name '*.sql' -o -name '*.dump'"
    echo ""
    echo "📝 Alternative: If you don't have a backup file, you can:"
    echo "   1. Access Vtiger installation wizard: http://localhost:8080"
    echo "   2. Re-run the installation to create database structure"
    echo "   3. Or manually create tables from schema/DatabaseSchema.xml"
    echo ""
    exit 1
fi

BACKUP_FILE="$1"

# Check if file exists
if [ ! -f "$BACKUP_FILE" ]; then
    echo "❌ Backup file not found: $BACKUP_FILE"
    exit 1
fi

echo "📄 Backup file: $BACKUP_FILE"
echo "🗄️  Database: $DB_NAME"
echo ""

# Check if database exists
echo "🔍 Checking database..."
docker exec $DB_CONTAINER mysql -u$DB_USER -p$DB_PASS -e "USE $DB_NAME;" 2>/dev/null
if [ $? -ne 0 ]; then
    echo "❌ Database $DB_NAME does not exist or connection failed"
    exit 1
fi

# Check current table count
TABLE_COUNT=$(docker exec $DB_CONTAINER mysql -u$DB_USER -p$DB_PASS -e "SELECT COUNT(*) as cnt FROM information_schema.tables WHERE table_schema = '$DB_NAME';" 2>/dev/null | tail -1)
echo "📊 Current tables in database: $TABLE_COUNT"

if [ "$TABLE_COUNT" -gt 0 ]; then
    echo "⚠️  Warning: Database already has $TABLE_COUNT tables!"
    read -p "Do you want to continue? This may overwrite existing data. (y/N): " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        echo "❌ Restore cancelled"
        exit 1
    fi
fi

echo ""
echo "🚀 Starting database restore..."
echo "⏳ This may take a few minutes..."

# Restore database
docker exec -i $DB_CONTAINER mysql -u$DB_USER -p$DB_PASS $DB_NAME < "$BACKUP_FILE"

if [ $? -eq 0 ]; then
    echo ""
    echo "✅ Database restored successfully!"
    
    # Check new table count
    NEW_TABLE_COUNT=$(docker exec $DB_CONTAINER mysql -u$DB_USER -p$DB_PASS -e "SELECT COUNT(*) as cnt FROM information_schema.tables WHERE table_schema = '$DB_NAME';" 2>/dev/null | tail -1)
    echo "📊 Tables after restore: $NEW_TABLE_COUNT"
    echo ""
    echo "🎉 Restore completed! You can now access Vtiger at http://localhost:8080"
else
    echo ""
    echo "❌ Restore failed! Please check the error messages above."
    exit 1
fi

