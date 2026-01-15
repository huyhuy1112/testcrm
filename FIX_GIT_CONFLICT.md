# 🔧 Fix Git Conflict trên Hosting

## ❌ Lỗi
```
error: Your local changes to the following files would be overwritten by merge: 
config.inc.php
Please commit your changes or stash them before you merge.
```

## ✅ Giải Pháp

### **Cách 1: Backup và Pull (KHUYẾN NGHỊ)**

1. **Backup config.inc.php hiện tại:**
   ```bash
   cd /home/nhtdbus8/supertestcrm.tdbsolution.com
   cp config.inc.php config.inc.php.backup
   ```

2. **Stash thay đổi local:**
   ```bash
   git stash
   ```

3. **Pull code mới từ GitHub:**
   ```bash
   git pull origin main
   ```

4. **Restore database credentials:**
   - Mở file `config.inc.php`
   - Điền lại database credentials:
     ```php
     $dbconfig['db_username'] = 'nhtdbus8_supertestcrm';
     $dbconfig['db_password'] = '987456321852huy';
     $dbconfig['db_name'] = 'nhtdbus8_supertestcrm';
     ```

5. **Xong!**

---

### **Cách 2: Commit Local Changes Trước**

1. **Commit thay đổi local:**
   ```bash
   cd /home/nhtdbus8/supertestcrm.tdbsolution.com
   git add config.inc.php
   git commit -m "Keep production database config"
   ```

2. **Pull và merge:**
   ```bash
   git pull origin main
   ```

3. **Nếu có conflict, sửa file config.inc.php:**
   - Giữ lại database credentials của bạn
   - Xóa các dòng conflict markers (`<<<<<<<`, `=======`, `>>>>>>>`)

4. **Commit merge:**
   ```bash
   git add config.inc.php
   git commit -m "Merge: Keep production config"
   ```

---

### **Cách 3: Reset và Pull (Nếu không cần giữ thay đổi local)**

⚠️ **CẢNH BÁO:** Cách này sẽ XÓA thay đổi local trong config.inc.php!

1. **Backup config.inc.php:**
   ```bash
   cp config.inc.php config.inc.php.backup
   ```

2. **Reset về version trên GitHub:**
   ```bash
   git checkout -- config.inc.php
   ```

3. **Pull code mới:**
   ```bash
   git pull origin main
   ```

4. **Điền lại database credentials vào config.inc.php**

---

## 📋 Database Credentials Cần Điền

Sau khi pull về, mở `config.inc.php` và điền:

```php
$dbconfig['db_server'] = 'localhost';
$dbconfig['db_port'] = '';
$dbconfig['db_username'] = 'nhtdbus8_supertestcrm';
$dbconfig['db_password'] = '987456321852huy';
$dbconfig['db_name'] = 'nhtdbus8_supertestcrm';
```

---

## 🆘 Nếu Vẫn Bị Lỗi

1. **Kiểm tra git status:**
   ```bash
   git status
   ```

2. **Xem thay đổi:**
   ```bash
   git diff config.inc.php
   ```

3. **Force pull (CẨN THẬN - sẽ mất thay đổi local):**
   ```bash
   git fetch origin
   git reset --hard origin/main
   ```
   Sau đó điền lại database credentials.

---

## ✅ Sau Khi Fix Xong

1. **Kiểm tra website hoạt động:**
   - Truy cập: https://supertestcrm.tdbsolution.com
   - Đảm bảo không bị lỗi

2. **Xóa file backup (nếu không cần):**
   ```bash
   rm config.inc.php.backup
   ```

