# DEBUG REQUEST HANGING - Khi request processing dừng lại

## 🔴 TÌNH HUỐNG

Bạn đã test và thấy:
- ✅ Composer loaded
- ✅ config.php loaded
- ✅ config.inc.php loaded
- ✅ WebUI.php loaded
- ✅ WebUI created
- ✅ Request created
- ⚠️ **Processing request...** → Dừng lại ở đây!

---

## 📋 NGUYÊN NHÂN CÓ THỂ

1. **Request processing bị timeout**
   - Query database quá chậm
   - Infinite loop
   - Deadlock

2. **Lỗi trong quá trình process nhưng không được hiển thị**
   - Fatal error bị suppress
   - Exception không được catch

3. **Redirect hoặc output headers**
   - Redirect đến login page
   - Headers đã được send
   - Output buffer bị flush

4. **Missing dependencies**
   - Class/function không tồn tại
   - File include failed

---

## ✅ GIẢI PHÁP

### CÁCH 1: Dùng test_index_simple.php (Khuyến nghị)

#### Bước 1: Pull code

```bash
cd /home/nhtdbus8/supertestcrm.tdbsolution.com
git pull origin main
```

#### Bước 2: Truy cập test file

```
https://supertestcrm.tdbsolution.com/WHITE_SCREEN_FIX/test_index_simple.php
```

File này là **exact copy của index.php** nhưng có:
- Error display enabled
- Try-catch để bắt errors
- Output buffering để capture output
- Header checking

#### Bước 3: Xem kết quả

File sẽ hiển thị:
- **Nếu có error:** Error message, file, line, stack trace
- **Nếu có output:** Output được capture
- **Nếu redirect:** Headers đã được send

---

### CÁCH 2: Dùng test_process_request.php (Chi tiết hơn)

#### Bước 1: Truy cập

```
https://supertestcrm.tdbsolution.com/WHITE_SCREEN_FIX/test_process_request.php
```

#### Bước 2: Xem từng bước

File sẽ test:
1. Loading required files
2. Creating WebUI instance
3. Creating Request
4. Testing Database connection
5. Checking Current User
6. **Processing Request** (với timeout protection)
7. Checking Headers
8. Recent PHP Errors

#### Bước 3: Xem kết quả

- Nếu dừng ở bước nào → Lỗi ở bước đó
- Nếu process quá lâu → Timeout issue
- Nếu có output → Xem output để biết lỗi

---

### CÁCH 3: Sửa index.php tạm thời

#### Bước 1: Backup index.php

```bash
cp index.php index.php.backup
```

#### Bước 2: Sửa index.php

Thêm vào đầu file (sau `<?php`):

```php
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
ini_set('log_errors', '1');
ini_set('max_execution_time', '60');
```

Và wrap process trong try-catch:

```php
try {
    $webUI = new Vtiger_WebUI();
    $webUI->process(new Vtiger_Request($_REQUEST, $_REQUEST));
} catch (Error $e) {
    echo "<h1>FATAL ERROR</h1>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "<p>File: " . $e->getFile() . "</p>";
    echo "<p>Line: " . $e->getLine() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
} catch (Exception $e) {
    echo "<h1>EXCEPTION</h1>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "<p>File: " . $e->getFile() . "</p>";
    echo "<p>Line: " . $e->getLine() . "</p>";
}
```

#### Bước 3: Refresh trang

- Truy cập website
- Xem error message
- Fix errors

#### Bước 4: Restore sau khi fix

```bash
cp index.php.backup index.php
```

---

### CÁCH 4: Check Error Log trong cPanel

#### Bước 1: Vào cPanel

- Vào **Errors** hoặc **Error Log**
- Xem recent errors

#### Bước 2: Hoặc qua SSH

```bash
tail -50 /home/nhtdbus8/supertestcrm.tdbsolution.com/error_log
# hoặc
tail -f ~/public_html/error_log
```

#### Bước 3: Fix errors

- Đọc error messages
- Fix theo hướng dẫn

---

## 🔍 CÁC LỖI THƯỜNG GẶP

### Lỗi 1: "Maximum execution time exceeded"

**Nguyên nhân:** Query database quá chậm hoặc infinite loop

**Fix:**
- Check database indexes
- Check for infinite loops
- Increase `max_execution_time`

### Lỗi 2: "Call to undefined method"

**Nguyên nhân:** Method không tồn tại trong class

**Fix:** Check class definition và method name

### Lỗi 3: "Headers already sent"

**Nguyên nhân:** Output trước khi send headers

**Fix:** Check có output nào trước `header()` không

### Lỗi 4: "Fatal error: Uncaught Error"

**Nguyên nhân:** PHP 8.x compatibility issue

**Fix:** Check code compatibility với PHP 8.2

### Lỗi 5: Redirect loop

**Nguyên nhân:** Redirect đến chính nó

**Fix:** Check redirect logic trong WebUI

---

## 📝 CHECKLIST

- [ ] Pull code từ GitHub
- [ ] Upload test file (`test_index_simple.php` hoặc `test_process_request.php`)
- [ ] Truy cập test file
- [ ] Xem error message hoặc output
- [ ] Fix errors
- [ ] Test website → không còn white screen
- [ ] Disable error display lại
- [ ] Xóa test files

---

## ⚠️ LƯU Ý

1. **Chỉ enable errors tạm thời**
   - Disable lại sau khi fix
   - Không để `display_errors = On` trong production

2. **Xóa test files**
   - `test_index_simple.php`
   - `test_process_request.php`
   - `index.php.backup` (nếu có)

3. **Check error log**
   - Luôn check error log trong cPanel
   - Có thể có errors không hiển thị trên browser

---

## ✅ KẾT QUẢ

Sau khi fix:
- ✅ Error được identify và fix
- ✅ Request processing hoàn thành
- ✅ Website không còn white screen
- ✅ Errors được disable lại
- ✅ Test files đã được xóa

