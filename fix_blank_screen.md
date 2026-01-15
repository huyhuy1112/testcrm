# Hướng dẫn khắc phục màn hình trắng Vtiger

## Vấn đề đã được khắc phục:
✅ mysqli extension đã được cài đặt và enable
✅ config.inc.php đã được tạo với database TDB1
✅ Database connection OK

## Vấn đề còn lại:
❌ Database TDB1 trống (0 tables) - Vtiger cần có cấu trúc database

## Giải pháp:

### Phương án 1: Restore từ backup SQL (Nếu có)
```bash
cd /Users/dangquochuy/Backup
./restore_database.sh /path/to/TDB1_backup.sql
```

### Phương án 2: Chạy lại Installation Wizard (Khuyến nghị)
1. Truy cập: http://localhost:8080
2. Chọn "Install" hoặc "Re-install"
3. Điền thông tin:
   - Database Server: `db`
   - Database Port: `3306`
   - Database Username: `root`
   - Database Password: `132120`
   - Database Name: `TDB1`
4. Hệ thống sẽ tự động tạo lại cấu trúc database từ `schema/DatabaseSchema.xml`

### Phương án 3: Tạo database structure thủ công (Nâng cao)
Cần sử dụng Vtiger's installation framework để parse DatabaseSchema.xml

## Lưu ý:
- mysqli extension đã được cài và enable
- Apache đã được restart
- Database TDB1 đã được tạo và có thể kết nối
- Chỉ cần có cấu trúc database (tables) là Vtiger sẽ chạy được


