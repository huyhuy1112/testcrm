# 🔧 Khôi Phục Menu Management (Team, Report, Schedule)

## ❌ Vấn Đề
Khi vào Management không thấy các mục: **Team, Report, Schedule, Document**

## ✅ Nguyên Nhân
Các script cấu hình menu đã có trong code nhưng **chưa được chạy trên hosting**, nên database chưa có cấu hình menu Management.

## 🚀 Giải Pháp

### **Cách 1: Chạy Script Tự Động (KHUYẾN NGHỊ)**

1. **Truy cập script trên hosting:**
   ```
   https://supertestcrm.tdbsolution.com/configure_management_menu.php
   ```

2. **Script sẽ tự động:**
   - Tìm các module: Calendar, Reports, Documents, Users
   - Gán chúng vào app "PROJECTS" (hiển thị là "Management")
   - Cập nhật labels: Schedule, Report, Document, Team
   - Xác minh kết quả

3. **Sau khi chạy xong:**
   - Xóa file `configure_management_menu.php` để bảo mật
   - Clear cache: `Settings → Configuration → Clear Cache`
   - Logout và login lại
   - Kiểm tra menu Management

---

### **Cách 2: Chạy SQL Script**

1. **Truy cập cPanel → phpMyAdmin**

2. **Chọn database:** `nhtdbus8_supertestcrm`

3. **Chạy file SQL:** `configure_management_menu.sql`

   Hoặc copy nội dung từ file `configure_management_menu.sql` và chạy trong phpMyAdmin

4. **Clear cache và logout/login lại**

---

### **Cách 3: Chạy Script PHP Qua Terminal (nếu có SSH)**

```bash
cd /home/nhtdbus8/supertestcrm.tdbsolution.com
php configure_management_menu.php
```

---

## 📋 Các Module Sẽ Được Thêm Vào Management

1. **Calendar** → Hiển thị là **"Schedule"**
2. **Reports** → Hiển thị là **"Report"**
3. **Documents** → Hiển thị là **"Document"**
4. **Users** → Hiển thị là **"Team"**

---

## ⚠️ Lưu Ý

- Script **an toàn** và có thể chạy nhiều lần (idempotent)
- Script sẽ **không xóa** dữ liệu hiện có
- Script chỉ **thêm/cập nhật** cấu hình menu
- Sau khi chạy, **xóa file script** để bảo mật

---

## 🔍 Kiểm Tra Sau Khi Chạy

1. **Vào Vtiger CRM**
2. **Kiểm tra sidebar menu** → Phải có "Management"
3. **Click vào "Management"** → Phải thấy:
   - Schedule
   - Report
   - Document
   - Team

---

## 🆘 Nếu Vẫn Không Thấy

1. **Clear cache:**
   ```bash
   rm -rf cache/*
   rm -rf storage/cache/*
   rm -rf templates_c/*
   ```

2. **Kiểm tra database:**
   ```sql
   SELECT t.name, t.tablabel, a.appname, a.sequence 
   FROM vtiger_app2tab a 
   INNER JOIN vtiger_tab t ON t.tabid = a.tabid 
   WHERE a.appname = 'PROJECTS' 
   ORDER BY a.sequence;
   ```

3. **Chạy lại script:** `fix_projects_to_project_menu.php` (nếu cần migrate từ PROJECTS sang PROJECT)

