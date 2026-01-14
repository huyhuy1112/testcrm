# FIX WHITE SCREEN - Hướng dẫn Production

## 🔴 LỖI

**Màn hình trắng (White Screen of Death)** khi truy cập website.

**Triệu chứng:**
- Trang web hiển thị màn hình trắng hoàn toàn
- Không có error message
- Không có content nào

---

## 📋 NGUYÊN NHÂN THƯỜNG GẶP

1. **PHP Errors không được hiển thị**
   - `display_errors = Off` trong production
   - Errors bị suppress

2. **Missing Files**
   - `config.inc.php` không tồn tại
   - `vendor/autoload.php` không tồn tại
   - `index.php` có lỗi

3. **Database Connection Failed**
   - Database credentials sai
   - Database không tồn tại
   - Database user không có quyền

4. **File Permissions**
   - Files không readable
   - Directories không writable

5. **PHP Syntax Error**
   - Lỗi syntax trong code
   - Missing semicolon, bracket, etc.

6. **Memory Limit**
   - PHP memory limit quá thấp
   - Script bị kill

---

## ✅ GIẢI PHÁP

### CÁCH 1: Dùng Debug File (Khuyến nghị)

#### Bước 1: Upload debug file

```bash
# Upload file debug_white_screen.php lên hosting
# Đặt vào thư mục root (cùng cấp với index.php)
```

#### Bước 2: Truy cập debug file

```
https://supertestcrm.tdbsolution.com/debug_white_screen.php
```

#### Bước 3: Xem kết quả

File sẽ hiển thị:
- PHP version
- File permissions
- Directory permissions
- Database connection status
- Missing files
- PHP errors

#### Bước 4: Fix theo kết quả

- Nếu thiếu file → Upload file đó
- Nếu permissions sai → Set permissions đúng
- Nếu database connection failed → Check credentials
- Nếu có PHP errors → Fix errors

#### Bước 5: Xóa debug file

```bash
# Sau khi fix xong, XÓA file này để bảo mật
rm debug_white_screen.php
```

---

### CÁCH 2: Enable Error Display (Tạm thời)

#### Bước 1: Sửa config.inc.php

Thêm vào đầu file (sau `<?php`):

```php
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
```

#### Bước 2: Refresh trang

- Truy cập lại website
- Xem error message
- Fix errors

#### Bước 3: Disable lại sau khi fix

```php
ini_set('display_errors', 'Off');
```

---

### CÁCH 3: Check Error Log

#### Bước 1: Vào cPanel

- Vào **Errors** hoặc **Error Log**
- Xem recent errors

#### Bước 2: Hoặc qua SSH

```bash
tail -50 /home/nhtdbus8/supertestcrm.tdbsolution.com/error_log
# hoặc
tail -50 ~/public_html/error_log
```

#### Bước 3: Fix errors

- Đọc error messages
- Fix theo hướng dẫn

---

### CÁCH 4: Check Common Issues

#### 1. Check file permissions

```bash
cd /home/nhtdbus8/supertestcrm.tdbsolution.com
chmod 644 index.php
chmod 644 config.inc.php
chmod 755 cache/
chmod 755 storage/
```

#### 2. Check missing files

```bash
# Check config.inc.php
ls -la config.inc.php

# Check vendor/autoload.php
ls -la vendor/autoload.php

# Check .htaccess
ls -la .htaccess
```

#### 3. Check database connection

Tạo file `test_db.php`:

```php
<?php
require_once 'config.inc.php';
$conn = new mysqli($dbconfig['db_server'], $dbconfig['db_username'], 
                   $dbconfig['db_password'], $dbconfig['db_name']);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
echo "Connected successfully";
$conn->close();
?>
```

Truy cập: `https://supertestcrm.tdbsolution.com/test_db.php`

#### 4. Clear cache

```bash
rm -rf cache/templates_c/*
rm -rf cache/images/*
mkdir -p cache/htmlpurifier/HTML
chmod -R 755 cache/
```

---

## 🔍 CHECKLIST DEBUG

- [ ] Upload `debug_white_screen.php` và truy cập
- [ ] Check PHP version
- [ ] Check file permissions
- [ ] Check directory permissions
- [ ] Check `config.inc.php` exists
- [ ] Check `vendor/autoload.php` exists
- [ ] Test database connection
- [ ] Check PHP error log
- [ ] Check memory limit
- [ ] Check `.htaccess` exists
- [ ] Clear cache

---

## 🆘 FIX CÁC LỖI CỤ THỂ

### Lỗi 1: "Fatal error: require(): Failed opening required 'vendor/autoload.php'"

**Nguyên nhân:** `vendor/` không tồn tại hoặc không được push

**Fix:**
```bash
# Pull code từ GitHub (nếu chưa có vendor/)
git pull origin main

# Hoặc chạy composer install (nếu có composer)
composer install
```

### Lỗi 2: "Fatal error: require(): Failed opening required 'config.inc.php'"

**Nguyên nhân:** `config.inc.php` không tồn tại

**Fix:**
```bash
# Copy từ PRODUCTION_CONFIG/config.inc.php
cp PRODUCTION_CONFIG/config.inc.php config.inc.php

# Hoặc upload file PRODUCTION_CONFIG/config.inc.php lên hosting
```

### Lỗi 3: "Database connection failed"

**Nguyên nhân:** Database credentials sai

**Fix:**
- Check `config.inc.php`
- Verify database credentials trong cPanel
- Test connection: `test_db.php`

### Lỗi 4: "Permission denied"

**Nguyên nhân:** File permissions sai

**Fix:**
```bash
find . -type f -exec chmod 644 {} \;
find . -type d -exec chmod 755 {} \;
chmod -R 755 cache/ storage/
```

### Lỗi 5: "Memory limit exceeded"

**Nguyên nhân:** PHP memory limit quá thấp

**Fix:**
Sửa `.htaccess`:
```apache
php_value memory_limit 512M
php_value max_execution_time 300
```

---

## ⚠️ LƯU Ý QUAN TRỌNG

1. **Xóa debug files sau khi fix**
   - `debug_white_screen.php`
   - `test_db.php`
   - Các file test khác

2. **Disable error display sau khi fix**
   - Set `display_errors = Off` trong production
   - Chỉ enable khi debug

3. **Backup trước khi sửa**
   - Backup `config.inc.php`
   - Backup database

---

## ✅ KẾT QUẢ SAU KHI FIX

- ✅ Website hiển thị bình thường
- ✅ Không còn màn hình trắng
- ✅ Errors được fix
- ✅ Database connection thành công

---

## 📂 FILES TRONG THƯ MỤC NÀY

- `debug_white_screen.php` - File debug để upload lên hosting
- `fix_white_screen_steps.txt` - Lệnh thủ công
- `README.md` - File này

