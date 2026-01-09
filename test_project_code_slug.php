<?php
/**
 * Test Project Code slug generation with Vietnamese characters
 */

// Test cases
$testCases = array(
    "chế lá cà" => "che-la-ca",
    "Dự án Xây dựng" => "du-an-xay-dung",
    "Thiết kế Website" => "thiet-ke-website",
    "Quản lý Dự án" => "quan-ly-du-an",
    "Hệ thống ERP" => "he-thong-erp",
    "Đào tạo Nhân viên" => "dao-tao-nhan-vien",
    "normal text 123" => "normal-text-123",
    "Mixed CASE Text" => "mixed-case-text",
);

// Vietnamese character mapping function (same as in ProjectCodeHandler)
function normalizeVietnameseToAscii($text) {
    $vietnameseChars = array(
        'à','á','ạ','ả','ã','â','ầ','ấ','ậ','ẩ','ẫ','ă','ằ','ắ','ặ','ẳ','ẵ',
        'è','é','ẹ','ẻ','ẽ','ê','ề','ế','ệ','ể','ễ',
        'ì','í','ị','ỉ','ĩ',
        'ò','ó','ọ','ỏ','õ','ô','ồ','ố','ộ','ổ','ỗ','ơ','ờ','ớ','ợ','ở','ỡ',
        'ù','ú','ụ','ủ','ũ','ư','ừ','ứ','ự','ử','ữ',
        'ỳ','ý','ỵ','ỷ','ỹ',
        'đ',
        'À','Á','Ạ','Ả','Ã','Â','Ầ','Ấ','Ậ','Ẩ','Ẫ','Ă','Ằ','Ắ','Ặ','Ẳ','Ẵ',
        'È','É','Ẹ','Ẻ','Ẽ','Ê','Ề','Ế','Ệ','Ể','Ễ',
        'Ì','Í','Ị','Ỉ','Ĩ',
        'Ò','Ó','Ọ','Ỏ','Õ','Ô','Ồ','Ố','Ộ','Ổ','Ỗ','Ơ','Ờ','Ớ','Ợ','Ở','Ỡ',
        'Ù','Ú','Ụ','Ủ','Ũ','Ư','Ừ','Ứ','Ự','Ử','Ữ',
        'Ỳ','Ý','Ỵ','Ỷ','Ỹ',
        'Đ'
    );
    
    $asciiReplacements = array(
        'a','a','a','a','a','a','a','a','a','a','a','a','a','a','a','a','a',
        'e','e','e','e','e','e','e','e','e','e','e',
        'i','i','i','i','i',
        'o','o','o','o','o','o','o','o','o','o','o','o','o','o','o','o','o',
        'u','u','u','u','u','u','u','u','u','u','u',
        'y','y','y','y','y',
        'd',
        'A','A','A','A','A','A','A','A','A','A','A','A','A','A','A','A','A',
        'E','E','E','E','E','E','E','E','E','E','E',
        'I','I','I','I','I',
        'O','O','O','O','O','O','O','O','O','O','O','O','O','O','O','O','O',
        'U','U','U','U','U','U','U','U','U','U','U',
        'Y','Y','Y','Y','Y',
        'D'
    );
    
    // Replace Vietnamese characters with ASCII equivalents
    $text = str_replace($vietnameseChars, $asciiReplacements, $text);
    
    // Lowercase
    $text = strtolower($text);
    
    // Replace non-alphanumeric with dash
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    
    // Trim dashes from start and end
    $text = trim($text, '-');
    
    // Remove duplicate dashes
    $text = preg_replace('/-+/', '-', $text);
    
    return $text;
}

echo "Testing Vietnamese character normalization:\n";
echo str_repeat("=", 60) . "\n";

$passed = 0;
$failed = 0;

foreach ($testCases as $input => $expected) {
    $result = normalizeVietnameseToAscii($input);
    $status = ($result === $expected) ? "✅ PASS" : "❌ FAIL";
    
    if ($result === $expected) {
        $passed++;
    } else {
        $failed++;
    }
    
    echo sprintf("%-30s => %-25s (expected: %-25s) %s\n", 
        $input, 
        $result, 
        $expected,
        $status
    );
}

echo str_repeat("=", 60) . "\n";
echo "Passed: $passed / " . count($testCases) . "\n";
echo "Failed: $failed / " . count($testCases) . "\n";

if ($failed == 0) {
    echo "\n✅ All tests passed!\n";
} else {
    echo "\n❌ Some tests failed!\n";
    exit(1);
}

