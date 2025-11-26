<?php
// Enhanced OCR Handler with multiple free OCR providers

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Include the new OCR service
require_once '../app/helpers/BankingOCR.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $action = $_POST['action'] ?? '';
        
        if ($action === 'process_ocr') {
            $ocrService = new BankingOCRService();
            
            // Handle file upload
            $imagePath = null;
            $imageData = null;
            
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                // File uploaded via form
                $imagePath = $_FILES['image']['tmp_name'];
            } elseif (isset($_POST['imageData'])) {
                // Base64 image data
                $imageData = $_POST['imageData'];
            }
            
            // Process with OCR
            $ocrResult = $ocrService->processImageData($imagePath, $imageData);
            
            if ($ocrResult['success']) {
                // Parse banking information
                $bankingInfo = $ocrService->parseBankingInfo($ocrResult);
                
                echo json_encode([
                    'success' => true,
                    'raw_text' => $ocrResult['text'],
                    'document_type' => $ocrResult['type'],
                    'bank_detected' => $ocrResult['bank'],
                    'parsed_data' => $bankingInfo,
                    'confidence' => $bankingInfo['confidence'],
                    'provider' => 'Tesseract OCR'
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'error' => $ocrResult['error'] ?? 'Failed to process image'
                ]);
            }
            
        } else {
            throw new Exception('Invalid action');
        }
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'error' => 'Only POST method allowed'
    ]);
}
?>