#!/bin/bash
# Fix Git Conflict on Hosting - Manual Steps
# Chạy script này trên hosting để fix conflict

echo "🔧 Fix Git Conflict on Hosting"
echo "==============================="
echo ""

CONFIG_FILE="config.inc.php"
BACKUP_FILE="config.inc.php.backup.$(date +%Y%m%d%H%M%S)"

# 1. Backup current config
if [ -f "$CONFIG_FILE" ]; then
    cp "$CONFIG_FILE" "$BACKUP_FILE"
    echo "✅ Backed up: $BACKUP_FILE"
else
    echo "⚠️ $CONFIG_FILE not found"
fi

# 2. Stash local changes
echo ""
echo "📦 Stashing local changes..."
git stash

# 3. Pull latest code
echo ""
echo "⬇️ Pulling latest code..."
git pull origin main

# 4. Check if pull was successful
if [ $? -eq 0 ]; then
    echo ""
    echo "✅ Pull successful!"
    echo ""
    echo "💡 Next steps:"
    echo "1. Check config.inc.php has correct production database settings"
    echo "2. If not, restore from backup: cp $BACKUP_FILE $CONFIG_FILE"
    echo "3. Update database credentials in config.inc.php if needed"
else
    echo ""
    echo "❌ Pull failed. Check error above."
    echo "💡 You can restore backup: cp $BACKUP_FILE $CONFIG_FILE"
fi

