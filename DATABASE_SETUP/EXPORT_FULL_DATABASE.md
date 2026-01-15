# 📦 Hướng Dẫn Export Full Database Vtiger CRM

## 🎯 Mục Đích
Export toàn bộ database (structure + data) để có thể import lại từ đầu.

---

## 📋 CÁCH 1: Export Từ phpMyAdmin (KHUYẾN NGHỊ)

### **Bước 1: Vào phpMyAdmin**
1. Vào **cPanel → phpMyAdmin**
2. Chọn database: `nhtdbus8_supertestcrm`

### **Bước 2: Export Database**
1. Click tab **"Export"**
2. Chọn phương thức: **"Quick"** hoặc **"Custom"**
3. Nếu chọn **"Custom"**:
   - ✅ Chọn **"Structure"** (cấu trúc bảng)
   - ✅ Chọn **"Data"** (dữ liệu)
   - ✅ Format: **SQL**
   - ✅ Compression: **gzip** (để file nhỏ hơn)
4. Click **"Go"**
5. File sẽ được download về máy

### **Bước 3: Lưu File**
- Tên file: `vtiger_full_database.sql` hoặc `vtiger_full_database.sql.gz`
- Lưu vào folder `DATABASE_SETUP/`

---

## 📋 CÁCH 2: Export Từ Command Line (SSH)

Nếu có SSH access:

```bash
# Export full database (structure + data)
mysqldump -u nhtdbus8_supertestcrm -p nhtdbus8_supertestcrm > vtiger_full_database.sql

# Hoặc với compression
mysqldump -u nhtdbus8_supertestcrm -p nhtdbus8_supertestcrm | gzip > vtiger_full_database.sql.gz
```

---

## 📋 CÁCH 3: Export Chỉ Structure (Không Có Data)

Nếu chỉ muốn cấu trúc database:

```bash
mysqldump -u nhtdbus8_supertestcrm -p --no-data nhtdbus8_supertestcrm > vtiger_structure_only.sql
```

---

## 📋 CÁCH 4: Export Từ Localhost Docker

Nếu muốn export từ localhost:

```bash
# Vào container
docker exec -it <container_name> bash

# Export database
mysqldump -u root -p132120 TDB1 > /var/www/html/vtiger_full_database.sql

# Copy ra ngoài
docker cp <container_name>:/var/www/html/vtiger_full_database.sql ./
```

---

## 📥 CÁCH IMPORT DATABASE

### **Bước 1: Xóa Database Cũ**
1. Vào phpMyAdmin
2. Chọn database: `nhtdbus8_supertestcrm`
3. Click tab **"Operations"**
4. Click **"Drop the database"** → Xác nhận

### **Bước 2: Tạo Database Mới**
1. Tạo database mới: `nhtdbus8_supertestcrm`
2. Chọn charset: `utf8mb4` hoặc `utf8`

### **Bước 3: Import File SQL**
1. Chọn database mới
2. Click tab **"Import"**
3. Click **"Choose File"** → Chọn file `.sql` hoặc `.sql.gz`
4. Click **"Go"**
5. Đợi import hoàn tất

### **Bước 4: Setup Menu Management**
Sau khi import xong, chạy file `FULL_DATABASE_SETUP.sql` để setup menu Management.

---

## ⚠️ LƯU Ý

1. **File SQL có thể rất lớn** (từ vài MB đến hàng trăm MB)
2. **Thời gian import** có thể mất vài phút đến vài giờ (tùy kích thước)
3. **Nên backup trước** khi import
4. **Kiểm tra file** trước khi import (đảm bảo không bị corrupt)

---

## 📊 Kích Thước File Thường Gặp

- **Structure only**: ~1-5 MB
- **Structure + Data (nhỏ)**: ~10-50 MB
- **Structure + Data (lớn)**: ~100-500 MB
- **Với compression (gzip)**: Giảm 70-90% kích thước

---

## 🔧 Troubleshooting

### **Lỗi: "File too large"**
- Dùng compression (gzip)
- Hoặc tăng `upload_max_filesize` trong PHP
- Hoặc import qua command line

### **Lỗi: "Timeout"**
- Tăng `max_execution_time` trong PHP
- Hoặc import qua command line
- Hoặc chia nhỏ file SQL

### **Lỗi: "Memory limit"**
- Tăng `memory_limit` trong PHP
- Hoặc import qua command line

---

## ✅ Checklist

- [ ] Đã export database từ phpMyAdmin
- [ ] File SQL đã được lưu
- [ ] Đã test import trên database test
- [ ] Đã backup database production trước khi import
- [ ] Đã setup menu Management sau khi import

