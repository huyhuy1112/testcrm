# 🚀 Cách Bỏ Qua Installation Wizard - Sử Dụng Database Đã Có

## ❌ Vấn Đề
Vtiger đang hiển thị Installation Wizard và yêu cầu tạo database mới, mặc dù bạn đã có database sẵn.

## ✅ Giải Pháp

### **Cách 1: Điền Thông Tin Database Vào `config.inc.php` (KHUYẾN NGHỊ)**

1. **Mở file `config.inc.php`** trên hosting (qua cPanel File Manager hoặc FTP)

2. **Điền thông tin database của bạn:**
   ```php
   $dbconfig['db_username'] = 'nhtdbus8_supertestcrm';
   $dbconfig['db_password'] = '987456321852huy';
   $dbconfig['db_name'] = 'nhtdbus8_supertestcrm';
   ```

3. **Lưu file và truy cập website lại**

4. **Kết quả:** Vtiger sẽ tự động nhận database và **KHÔNG** vào Installation Wizard nữa!

---

### **Cách 2: Sử Dụng Database Đã Có Trong Installation Wizard**

Nếu bạn đang ở Installation Wizard:

1. **BỎ CHỌN** checkbox **"Create new database"** (không tích vào ô này)

2. **Điền thông tin database đã có:**
   - **Host Name**: `localhost`
   - **User Name**: `nhtdbus8_supertestcrm`
   - **Password**: `987456321852huy`
   - **Database Name**: `nhtdbus8_supertestcrm`

3. **Click "Next"** để tiếp tục

4. **Lưu ý:** Installation Wizard sẽ kiểm tra database và sử dụng database đã có thay vì tạo mới.

---

## 🔍 Tại Sao Lại Bị Vào Installation Wizard?

Vtiger kiểm tra `db_name` trong `config.inc.php`:
- Nếu `db_name` **TRỐNG** → Vtiger nghĩ chưa cài đặt → Redirect đến Installation Wizard
- Nếu `db_name` **CÓ GIÁ TRỊ** → Vtiger nhận database → Vào trang Login bình thường

---

## 📝 Checklist

- [ ] Đã điền `db_username` vào `config.inc.php`
- [ ] Đã điền `db_password` vào `config.inc.php`
- [ ] Đã điền `db_name` vào `config.inc.php`
- [ ] Đã lưu file `config.inc.php`
- [ ] Đã truy cập website và kiểm tra

---

## ⚠️ Lưu Ý

- **KHÔNG** tích vào "Create new database" nếu bạn đã có database
- Database phải đã được tạo sẵn trong cPanel MySQL Databases
- Đảm bảo user database có quyền truy cập vào database đó

