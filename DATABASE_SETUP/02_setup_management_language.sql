-- ==========================================
-- Setup Management Menu Language
-- ==========================================
-- 
-- File: 02_setup_management_language.sql
-- Mục đích: Cập nhật language để PROJECTS app hiển thị là "Management"
--
-- LƯU Ý: 
-- - File này chỉ cập nhật database, không sửa file PHP
-- - Nếu cần sửa file PHP, xem file: languages/en_us/Vtiger.php
-- ==========================================

-- Kiểm tra xem có bảng vtiger_language_strings không
-- (Một số version Vtiger lưu language trong database)

-- Nếu bảng tồn tại, cập nhật
UPDATE vtiger_language_strings 
SET value = 'Management' 
WHERE name = 'LBL_PROJECT' 
  AND language = 'en_us'
  AND value != 'Management';

-- Nếu chưa có, insert mới
INSERT INTO vtiger_language_strings (name, value, language, module)
SELECT 'LBL_PROJECT', 'Management', 'en_us', 'Vtiger'
WHERE NOT EXISTS (
    SELECT 1 FROM vtiger_language_strings 
    WHERE name = 'LBL_PROJECT' AND language = 'en_us'
);

-- Kiểm tra kết quả
SELECT name, value, language 
FROM vtiger_language_strings 
WHERE name = 'LBL_PROJECT' AND language = 'en_us';

-- LƯU Ý QUAN TRỌNG:
-- Nếu bảng vtiger_language_strings không tồn tại, 
-- bạn cần sửa file: languages/en_us/Vtiger.php
-- Tìm dòng: 'LBL_PROJECT' => '...'
-- Sửa thành: 'LBL_PROJECT' => 'Management'

