# HƯỚNG DẪN PUSH CODE LÊN GITHUB - PRODUCTION READY

## 🎯 MỤC TIÊU

Push code lên GitHub một cách **chọn lọc** để:
- ✅ Có thể deploy lên production qua cPanel
- ✅ Không có lỗi như trước (Docker configs, permissions, etc.)
- ✅ Code sạch, không có test files, backup files

---

## 📋 CHUẨN BỊ

### 1. Kiểm tra Git Remote

```bash
git remote -v
```

Nếu chưa có remote `supertestcrm`:
```bash
git remote add supertestcrm https://github.com/huyhuy1112/supertestcrm.git
```

### 2. Kiểm tra .gitignore

Đảm bảo `.gitignore` đã được cập nhật để loại bỏ:
- `config.inc.php` (sẽ tạo trên production)
- Test files (`test_*.php`, `*_test.php`)
- Fix directories (`CONNECTOR_DISPLAY_FIX/`, etc.)
- Backup files (`.zip`, `.sql`, `.bak`)
- Cache và logs

---

## 🚀 CÁC BƯỚC PUSH CODE

### Bước 1: Kiểm tra files sẽ được push

```bash
git status
```

### Bước 2: Add files chọn lọc

**KHÔNG add:**
- `config.inc.php` (đã có trong .gitignore)
- Test files
- Fix directories
- Backup files
- `.zip` files

**ADD:**
- Source code chính
- `vtiger_upload_fix/` (đã có config production-ready)
- `.gitignore`
- `DEPLOY_PRODUCTION.md`
- `htaccess.txt`
- Các file cần thiết khác

```bash
# Add .gitignore và deploy guide
git add .gitignore DEPLOY_PRODUCTION.md PUSH_TO_GITHUB.md

# Add vtiger_upload_fix (đã có config production-ready)
git add vtiger_upload_fix/

# Add source code (loại trừ files trong .gitignore)
git add .

# Kiểm tra lại
git status
```

### Bước 3: Commit

```bash
git commit -m "Prepare code for production deployment via cPanel

- Updated .gitignore to exclude test files, fix directories, and backups
- Added vtiger_upload_fix/ with production-ready config.inc.php
- Added DEPLOY_PRODUCTION.md with deployment instructions
- Removed Docker-specific configurations
- Ready for cPanel shared hosting deployment"
```

### Bước 4: Push lên GitHub

```bash
# Push lên supertestcrm repository
git push supertestcrm main

# Hoặc nếu branch khác:
git push supertestcrm main:main
```

---

## ✅ KIỂM TRA SAU KHI PUSH

### 1. Kiểm tra trên GitHub

Truy cập: https://github.com/huyhuy1112/supertestcrm

Kiểm tra:
- ✅ Code đã được push
- ✅ Không có `config.inc.php` với credentials
- ✅ Không có test files
- ✅ Không có fix directories
- ✅ Có `vtiger_upload_fix/` với config production-ready

### 2. Test clone

```bash
# Test clone repository
cd /tmp
git clone https://github.com/huyhuy1112/supertestcrm.git test-clone
cd test-clone

# Kiểm tra
ls -la
# Không thấy config.inc.php
# Không thấy test files
# Có vtiger_upload_fix/
```

---

## 🔧 FIX CÁC VẤN ĐỀ

### Vấn đề 1: Push nhầm config.inc.php

**Fix:**
```bash
# Xóa khỏi git (nhưng giữ file local)
git rm --cached config.inc.php

# Commit
git commit -m "Remove config.inc.php from repository"

# Push
git push supertestcrm main
```

### Vấn đề 2: Push nhầm test files

**Fix:**
```bash
# Xóa khỏi git
git rm --cached test_*.php
git rm --cached *_test.php

# Commit và push
git commit -m "Remove test files"
git push supertestcrm main
```

### Vấn đề 3: .gitignore không hoạt động

**Fix:**
```bash
# Xóa cache git
git rm -r --cached .

# Add lại tất cả (sẽ respect .gitignore)
git add .

# Commit và push
git commit -m "Fix .gitignore"
git push supertestcrm main
```

---

## 📝 CHECKLIST TRƯỚC KHI PUSH

- [ ] Đã cập nhật `.gitignore`
- [ ] Đã kiểm tra `git status`
- [ ] Không có `config.inc.php` với credentials
- [ ] Không có test files
- [ ] Không có fix directories
- [ ] Không có backup files (`.zip`, `.sql`)
- [ ] Có `vtiger_upload_fix/` với config production-ready
- [ ] Có `DEPLOY_PRODUCTION.md`
- [ ] Đã test commit message
- [ ] Sẵn sàng push

---

## 🎯 KẾT QUẢ MONG ĐỢI

Sau khi push:
- ✅ Repository sạch, chỉ có code cần thiết
- ✅ Có thể clone và deploy lên production
- ✅ Không có lỗi Docker configs
- ✅ Config production-ready trong `vtiger_upload_fix/`
- ✅ Có hướng dẫn deploy chi tiết

---

## 📂 CẤU TRÚC REPOSITORY SAU KHI PUSH

```
supertestcrm/
├── .gitignore
├── DEPLOY_PRODUCTION.md
├── PUSH_TO_GITHUB.md
├── htaccess.txt
├── index.php
├── modules/
├── includes/
├── layouts/
├── vtiger_upload_fix/
│   ├── config.inc.php (production-ready template)
│   ├── .htaccess
│   └── ... (source code)
└── ... (các files khác)
```

**KHÔNG có:**
- `config.inc.php` (với credentials)
- `test_*.php`
- `CONNECTOR_DISPLAY_FIX/`
- `*.zip`, `*.sql`

