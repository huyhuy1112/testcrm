# 📦 DATABASE_SETUP - Cấu Hình Menu Management

## 📋 Mô Tả

Folder này chứa các file SQL để cấu hình menu **Management** trong Vtiger CRM với các module:
- **Calendar** → Hiển thị là **"Schedule"**
- **Reports** → Hiển thị là **"Report"**
- **Documents** → Hiển thị là **"Document"**
- **Users** → Hiển thị là **"Team"**

---

## 📁 Các File SQL

### 1. `01_setup_management_menu.sql` ⭐ **QUAN TRỌNG NHẤT**
- **Mục đích:** Cấu hình menu Management với app "PROJECTS"
- **Khi nào dùng:** Sau khi đã cài đặt Vtiger CRM (database đã có sẵn)
- **Chức năng:**
  - Gán các module vào app "PROJECTS" (hiển thị là "Management")
  - Cập nhật display labels
  - An toàn, có thể chạy nhiều lần

### 2. `02_setup_management_language.sql`
- **Mục đích:** Cập nhật language để "PROJECTS" hiển thị là "Management"
- **Khi nào dùng:** Nếu language được lưu trong database
- **Lưu ý:** Nếu không có bảng `vtiger_language_strings`, cần sửa file `languages/en_us/Vtiger.php`

### 3. `03_fix_projects_to_project.sql`
- **Mục đích:** Di chuyển modules từ app "PROJECTS" sang app "PROJECT"
- **Khi nào dùng:** 
  - Khi muốn dùng app "PROJECT" thay vì "PROJECTS"
  - Hoặc khi có lỗi với app "PROJECTS"
- **Lưu ý:** File này sẽ XÓA app "PROJECTS" và chuyển sang "PROJECT"

---

## 🚀 Cách Sử Dụng

### **Bước 1: Tạo Database trên cPanel**

1. Vào **cPanel → MySQL Databases**
2. Tạo database mới: `nhtdbus8_supertestcrm`
3. Tạo user mới: `nhtdbus8_supertestcrm`
4. Gán quyền cho user vào database
5. Ghi nhớ thông tin:
   - Database name: `nhtdbus8_supertestcrm`
   - Username: `nhtdbus8_supertestcrm`
   - Password: `987456321852huy`

### **Bước 2: Cài Đặt Vtiger CRM**

1. Upload code Vtiger lên hosting
2. Truy cập website và chạy **Installation Wizard**
3. Điền thông tin database:
   - Host: `localhost`
   - Username: `nhtdbus8_supertestcrm`
   - Password: `987456321852huy`
   - Database: `nhtdbus8_supertestcrm`
4. Hoàn tất installation

### **Bước 3: Import SQL để Cấu Hình Menu**

1. Vào **cPanel → phpMyAdmin**
2. Chọn database: `nhtdbus8_supertestcrm`
3. Click tab **"SQL"**
4. Upload hoặc copy nội dung file `01_setup_management_menu.sql`
5. Click **"Go"** để chạy
6. Kiểm tra kết quả (phải thấy 4 dòng: Calendar, Reports, Documents, Users)

### **Bước 4: Cập Nhật Language (Tùy Chọn)**

1. Nếu language được lưu trong database:
   - Chạy file `02_setup_management_language.sql`
2. Nếu không có bảng `vtiger_language_strings`:
   - Sửa file: `languages/en_us/Vtiger.php`
   - Tìm: `'LBL_PROJECT' => '...'`
   - Sửa thành: `'LBL_PROJECT' => 'Management'`

### **Bước 5: Clear Cache**

1. Vào Vtiger CRM
2. **Settings → Configuration → Clear Cache**
3. Hoặc chạy lệnh:
   ```bash
   rm -rf cache/*
   rm -rf storage/cache/*
   rm -rf templates_c/*
   ```

### **Bước 6: Kiểm Tra**

1. **Logout** và **Login** lại Vtiger CRM
2. Kiểm tra sidebar menu → Phải có **"Management"**
3. Click vào **"Management"** → Phải thấy:
   - Schedule
   - Report
   - Document
   - Team

---

## ⚠️ Lưu Ý Quan Trọng

1. **Chỉ chạy SQL SAU KHI đã cài đặt Vtiger CRM**
   - Database phải đã có các bảng: `vtiger_tab`, `vtiger_app2tab`

2. **Backup database trước khi chạy SQL**
   - Vào phpMyAdmin → Export database

3. **Các file SQL đều an toàn (idempotent)**
   - Có thể chạy nhiều lần mà không bị lỗi

4. **Nếu dùng app "PROJECT" thay vì "PROJECTS"**
   - Chạy `01_setup_management_menu.sql` trước
   - Sau đó chạy `03_fix_projects_to_project.sql`

---

## 🔍 Kiểm Tra Sau Khi Import

### **Query kiểm tra menu Management:**

```sql
SELECT 
    t.name AS module_name,
    t.tablabel AS display_label,
    a.appname,
    a.sequence,
    a.visible
FROM vtiger_app2tab a
INNER JOIN vtiger_tab t ON t.tabid = a.tabid
WHERE a.appname = 'PROJECTS'
  AND t.name IN ('Calendar', 'Reports', 'Documents', 'Users')
ORDER BY a.sequence;
```

### **Kết quả mong đợi:**

| module_name | display_label | appname | sequence | visible |
|-------------|---------------|---------|----------|---------|
| Calendar    | Schedule      | PROJECTS| 1        | 1       |
| Reports     | Report        | PROJECTS| 2        | 1       |
| Documents   | Document      | PROJECTS| 3        | 1       |
| Users       | Team          | PROJECTS| 4        | 1       |

---

## 🆘 Troubleshooting

### **Vấn đề: Không thấy menu Management**

1. **Kiểm tra database:**
   ```sql
   SELECT COUNT(*) FROM vtiger_app2tab WHERE appname = 'PROJECTS';
   ```
   - Phải có ít nhất 4 records

2. **Clear cache:**
   - Settings → Configuration → Clear Cache
   - Hoặc xóa thủ công: `cache/`, `storage/cache/`, `templates_c/`

3. **Logout và login lại**

### **Vấn đề: Menu hiển thị sai tên**

1. **Kiểm tra language file:**
   - File: `languages/en_us/Vtiger.php`
   - Phải có: `'LBL_PROJECT' => 'Management'`

2. **Clear cache và reload**

---

## 📞 Hỗ Trợ

Nếu gặp vấn đề, kiểm tra:
1. Database đã có các bảng `vtiger_tab`, `vtiger_app2tab` chưa?
2. SQL đã chạy thành công chưa? (không có lỗi)
3. Cache đã clear chưa?
4. Đã logout/login lại chưa?

