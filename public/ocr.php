<?php
// Simple OCR handler
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');
header('Content-Type: application/json; charset=utf-8');

// Handle preflight requests
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

function processOCR() {
    try {
        // Debug logging
        error_log('OCR processOCR called');
        error_log('FILES: ' . print_r($_FILES, true));
        
        // Check if image file was uploaded
        if (!isset($_FILES['image'])) {
            throw new Exception('No image field found in upload');
        }
        
        if ($_FILES['image']['error'] !== 0) {
            $errorMessages = [
                UPLOAD_ERR_INI_SIZE => 'File too large (ini_size)',
                UPLOAD_ERR_FORM_SIZE => 'File too large (form_size)', 
                UPLOAD_ERR_PARTIAL => 'File partially uploaded',
                UPLOAD_ERR_NO_FILE => 'No file uploaded',
                UPLOAD_ERR_NO_TMP_DIR => 'No temp directory',
                UPLOAD_ERR_CANT_WRITE => 'Cannot write file',
                UPLOAD_ERR_EXTENSION => 'Extension error'
            ];
            $errorMsg = $errorMessages[$_FILES['image']['error']] ?? 'Unknown upload error';
            throw new Exception('Upload error: ' . $errorMsg . ' (Code: ' . $_FILES['image']['error'] . ')');
        }
        
        $uploadedFile = $_FILES['image'];
            
        // Validate image type
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
        if (!in_array($uploadedFile['type'], $allowedTypes)) {
            throw new Exception('Only JPG and PNG images are allowed. Got: ' . $uploadedFile['type']);
        }
        
        // Process OCR
        $extractedText = performOCR($uploadedFile['tmp_name']);
        
        // Parse banking information
        $bankingInfo = parseBankingInfo($extractedText);
            
        return [
            'success' => true,
            'raw_text' => $extractedText,
            'parsed_data' => $bankingInfo
        ];
        
    } catch (Exception $e) {
        error_log('OCR Error: ' . $e->getMessage());
        return [
            'success' => false,
            'error' => $e->getMessage(),
            'debug' => [
                'files' => $_FILES ?? [],
                'post' => $_POST ?? []
            ]
        ];
    }
}

function performOCR($imagePath) {
        // For demo purposes, we'll simulate OCR with a mock response
        // In production, you would use Google Vision API or Tesseract
        
        // Simulate different banking transfer texts
        $sampleTexts = [
            "BIDV\nChuyen tien\nNguoi nhan: NGUYEN VAN A\nSo tien: 2,500,000 VND\nNoi dung: Hoc phi khoa hoc PHP\nNgay: 21/10/2025",
            "TECHCOMBANK\nChuyen khoan\nTen nguoi nhan: TRAN THI B  \nSo tien chuyen: 1,800,000\nNoi dung CK: Thanh toan khoa hoc JavaScript\nTG giao dich: 21/10/2025 14:30",
            "VIETCOMBANK\nChuyen tien nhanh\nNguoi huong: LE VAN C\nSo tien: 3,200,000 VND\nGhi chu: Hoc phi React Native"
        ];
        
        // Return random sample (in real app, process the actual image)
        return $sampleTexts[array_rand($sampleTexts)];
}

function parseBankingInfo($text) {
        $result = [
            'recipient_name' => '',
            'amount' => '',
            'content' => ''
        ];
        
        // Normalize text
        $text = strtolower($text);
        $lines = explode("\n", $text);
        
        // Parse recipient name
        foreach ($lines as $line) {
            $line = trim($line);
            
            // Look for recipient patterns
            if (preg_match('/(?:nguoi nhan|ten nguoi nhan|nguoi huong)[\s:]*(.+)/u', $line, $matches)) {
                $result['recipient_name'] = trim($matches[1]);
            }
            
            // Look for amount patterns  
            if (preg_match('/(?:so tien|so tien chuyen)[\s:]*([0-9,.\s]+)/u', $line, $matches)) {
                $amount = preg_replace('/[^\d]/', '', $matches[1]);
                if ($amount && is_numeric($amount)) {
                    $result['amount'] = number_format((float)$amount);
                }
            }
            
            // Look for content patterns
            if (preg_match('/(?:noi dung|ghi chu|noi dung ck)[\s:]*(.+)/u', $line, $matches)) {
                $result['content'] = trim($matches[1]);
            }
        }
        
    return $result;
}

// Handle the request
$requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($requestMethod === 'POST') {
    $result = processOCR();
    echo json_encode($result);
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
}
?>