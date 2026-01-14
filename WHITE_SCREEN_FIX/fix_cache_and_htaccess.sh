#!/bin/bash
# ============================================================================
# FIX WHITE SCREEN - Create cache directories and .htaccess
# ============================================================================
# 
# Vấn đề từ debug:
# 1. cache/ directory NOT FOUND
# 2. .htaccess NOT FOUND
# 
# SỬ DỤNG:
#   1. Upload script này lên hosting
#   2. Chạy: bash fix_cache_and_htaccess.sh
#   3. Hoặc chạy từng lệnh thủ công
#
# ============================================================================

# Đường dẫn root của Vtiger (thay đổi nếu khác)
VTIGER_ROOT="/home/nhtdbus8/supertestcrm.tdbsolution.com"

echo "=== FIX WHITE SCREEN - CREATE CACHE AND .HTACCESS ==="
echo ""

# Di chuyển đến thư mục root
cd "$VTIGER_ROOT" || exit 1

# ============================================================================
# 1. TẠO CACHE DIRECTORY
# ============================================================================
echo "1. Tạo thư mục cache/..."

if [ ! -d "cache" ]; then
    echo "   → Tạo cache/..."
    mkdir -p cache
    chmod 755 cache
    echo "   ✓ cache/ đã được tạo"
else
    echo "   ✓ cache/ đã tồn tại"
fi

# Tạo các subdirectories trong cache
echo "2. Tạo các subdirectories trong cache/..."

# cache/templates_c/
if [ ! -d "cache/templates_c" ]; then
    mkdir -p cache/templates_c
    chmod 755 cache/templates_c
    echo "   ✓ cache/templates_c/ đã được tạo"
fi

# cache/images/
if [ ! -d "cache/images" ]; then
    mkdir -p cache/images
    chmod 755 cache/images
    echo "   ✓ cache/images/ đã được tạo"
fi

# cache/import/
if [ ! -d "cache/import" ]; then
    mkdir -p cache/import
    chmod 755 cache/import
    echo "   ✓ cache/import/ đã được tạo"
fi

# cache/upload/
if [ ! -d "cache/upload" ]; then
    mkdir -p cache/upload
    chmod 755 cache/upload
    echo "   ✓ cache/upload/ đã được tạo"
fi

# cache/htmlpurifier/HTML
if [ ! -d "cache/htmlpurifier/HTML" ]; then
    mkdir -p cache/htmlpurifier/HTML
    chmod -R 755 cache/htmlpurifier
    echo "   ✓ cache/htmlpurifier/HTML/ đã được tạo"
fi

# Set permissions cho toàn bộ cache/
chmod -R 755 cache/
echo "   ✓ Permissions đã được set cho cache/"

# ============================================================================
# 2. TẠO .HTACCESS
# ============================================================================
echo ""
echo "3. Tạo .htaccess..."

if [ ! -f ".htaccess" ]; then
    if [ -f "htaccess.txt" ]; then
        echo "   → Copy htaccess.txt → .htaccess..."
        cp htaccess.txt .htaccess
        chmod 644 .htaccess
        echo "   ✓ .htaccess đã được tạo từ htaccess.txt"
    else
        echo "   → Tạo .htaccess mới..."
        cat > .htaccess << 'EOF'
Options -Indexes

# Enable output buffering
php_flag output_buffering On
php_value output_buffering 4096

# Disable display errors (production)
php_flag display_errors Off

# Memory and execution time
php_value memory_limit 512M
php_value max_execution_time 300
EOF
        chmod 644 .htaccess
        echo "   ✓ .htaccess đã được tạo"
    fi
else
    echo "   ✓ .htaccess đã tồn tại"
fi

# ============================================================================
# 3. CLEAR CACHE (nếu có)
# ============================================================================
echo ""
echo "4. Clear cache (nếu có)..."
if [ -d "cache/templates_c" ]; then
    rm -rf cache/templates_c/*
    echo "   ✓ cache/templates_c/ đã được clear"
fi
if [ -d "cache/images" ]; then
    rm -rf cache/images/*
    echo "   ✓ cache/images/ đã được clear"
fi

# ============================================================================
# 4. VERIFY
# ============================================================================
echo ""
echo "=== VERIFY ==="
echo ""

# Check cache/
if [ -d "cache" ]; then
    echo "✓ cache/ tồn tại"
    echo "  Permissions: $(stat -c '%a' cache 2>/dev/null || stat -f '%A' cache)"
else
    echo "✗ cache/ KHÔNG tồn tại"
fi

# Check .htaccess
if [ -f ".htaccess" ]; then
    echo "✓ .htaccess tồn tại"
    echo "  Permissions: $(stat -c '%a' .htaccess 2>/dev/null || stat -f '%A' .htaccess)"
else
    echo "✗ .htaccess KHÔNG tồn tại"
fi

# Check subdirectories
echo ""
echo "Cache subdirectories:"
for dir in cache/templates_c cache/images cache/import cache/upload cache/htmlpurifier/HTML; do
    if [ -d "$dir" ]; then
        echo "  ✓ $dir"
    else
        echo "  ✗ $dir"
    fi
done

echo ""
echo "=== HOÀN TẤT ==="
echo ""
echo "Bây giờ thử truy cập website lại:"
echo "https://supertestcrm.tdbsolution.com"
echo ""
echo "Nếu vẫn còn white screen, check PHP error log trong cPanel."

