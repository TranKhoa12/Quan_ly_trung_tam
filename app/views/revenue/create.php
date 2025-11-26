<?php
require_once __DIR__ . '/../layouts/main.php';
require_once __DIR__ . '/../../helpers/BankingOCR.php';

// Page content
ob_start();
?>

<!-- Page Header -->
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-4 border-bottom">
    <div>
        <h1 class="h2 mb-2">
            <i class="fas fa-dollar-sign text-primary me-2"></i>
            Tạo báo cáo doanh thu
        </h1>
        <p class="text-muted mb-0">Nhập thông tin doanh thu và chi phí của trung tâm</p>
    </div>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="/Quan_ly_trung_tam/public/revenue" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Quay lại
        </a>
    </div>
</div>

<div class="p-3">
    <?php if (isset($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>
            <?= htmlspecialchars($error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="stats-card">
        <div class="card-body">
            <form method="POST" action="/Quan_ly_trung_tam/public/revenue" enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">Thông tin thanh toán</h5>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label for="payment_date" class="form-label">Ngày đóng học phí *</label>
                                        <input type="date" class="form-control" id="payment_date" name="payment_date" 
                                               value="<?= $old_data['payment_date'] ?? date('Y-m-d') ?>" required>
                                    </div>

                                    <div class="mb-3">
                                        <label for="transfer_type" class="form-label">Loại chuyển khoản *</label>
                                        <select class="form-select" id="transfer_type" name="transfer_type" required>
                                            <option value="">Chọn loại chuyển khoản</option>
                                            <option value="cash" <?= (isset($old_data['transfer_type']) && $old_data['transfer_type'] == 'cash') ? 'selected' : '' ?>>
                                                Tiền mặt
                                            </option>
                                            <option value="account_co_nhi" <?= (isset($old_data['transfer_type']) && $old_data['transfer_type'] == 'account_co_nhi') ? 'selected' : '' ?>>
                                                Tài khoản Cô Nhi
                                            </option>
                                            <option value="account_thay_hien" <?= (isset($old_data['transfer_type']) && $old_data['transfer_type'] == 'account_thay_hien') ? 'selected' : '' ?>>
                                                Tài khoản Thầy Hiến
                                            </option>
                                            <option value="account_company" <?= (isset($old_data['transfer_type']) && $old_data['transfer_type'] == 'account_company') ? 'selected' : '' ?>>
                                                Tài khoản Công ty
                                            </option>
                                        </select>
                                    </div>

                    <div class="mb-3">
                        <label for="confirmation_image" class="form-label">Ảnh xác nhận chuyển khoản/phiếu thu</label>
                        <input type="file" class="form-control" id="confirmation_image" name="confirmation_image" 
                               accept="image/*">
                        <div class="form-text">Chấp nhận file: JPG, PNG. Tối đa 10MB.  </div>
                        
                        <!-- Image Preview - Thumbnail -->
                        <div id="image_preview" class="mt-3" style="display: none;">
                            <div class="d-flex align-items-start gap-3">
                                <img id="preview_img" class="rounded border" style="width: 150px; height: 150px; object-fit: cover; cursor: pointer;" onclick="openImageFullView()" title="Click để xem ảnh đầy đủ">
                                <div>
                                    <p class="mb-2 text-muted small">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Click vào ảnh để xem kích thước đầy đủ
                                    </p>
                                    <button type="button" class="btn btn-outline-danger btn-sm" id="clear_image_btn">
                                        <i class="fas fa-times me-2"></i>Xóa ảnh
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Modal for Full Image View -->
                        <div class="modal fade" id="imageFullViewModal" tabindex="-1">
                            <div class="modal-dialog modal-lg modal-dialog-centered">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Xem ảnh xác nhận</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body text-center">
                                        <img id="modal_full_img" class="img-fluid" style="max-width: 100%;">
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- OCR Confirmation Modal -->
                        <div class="modal fade" id="ocrConfirmationModal" tabindex="-1">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <div class="modal-header bg-primary text-white">
                                        <h5 class="modal-title">
                                            <i class="fas fa-robot me-2"></i>Kết quả trích xuất OCR
                                        </h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="alert alert-info mb-3">
                                            <i class="fas fa-info-circle me-2"></i>
                                            Hệ thống đã trích xuất được thông tin từ ảnh. Vui lòng xác nhận để điền vào form.
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">
                                                <i class="fas fa-user me-2 text-primary"></i>Tên người nhận:
                                            </label>
                                            <div class="form-control bg-light" id="ocr_recipient_name">-</div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">
                                                <i class="fas fa-money-bill-wave me-2 text-success"></i>Số tiền:
                                            </label>
                                            <div class="form-control bg-light" id="ocr_amount">-</div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">
                                                <i class="fas fa-file-alt me-2 text-info"></i>Nội dung chuyển khoản:
                                            </label>
                                            <div class="form-control bg-light" id="ocr_content">-</div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">
                                                <i class="fas fa-barcode me-2 text-warning"></i>Mã phiếu thu:
                                            </label>
                                            <div class="form-control bg-light" id="ocr_receipt_code">-</div>
                                        </div>
                                        
                                        <div class="text-muted small">
                                            <i class="fas fa-microchip me-1"></i>
                                            Nguồn: <span id="ocr_provider">-</span>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                            <i class="fas fa-times me-2"></i>Hủy
                                        </button>
                                        <button type="button" class="btn btn-primary" id="confirmOCRBtn">
                                            <i class="fas fa-check me-2"></i>Xác nhận & Điền vào form
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- OCR Processing Status -->
                        <div id="ocr_status" class="mt-2" style="display: none;">
                            <div class="alert alert-info">
                                <i class="fas fa-spinner fa-spin me-2"></i>Đang phân tích ảnh...
                            </div>
                        </div>
                    </div>                                    <div class="mb-3">
                                        <label for="receipt_code" class="form-label">Mã phiếu thu</label>
                                        <input type="text" class="form-control" id="receipt_code" name="receipt_code" 
                                               value="<?= $old_data['receipt_code'] ?? '' ?>" 
                                               placeholder="Nhập mã phiếu thu" autocomplete="off">
                                        <small id="receiptCodeFeedback" class="form-text"></small>
                                    </div>

                                    <div class="mb-3">
                                        <label for="amount" class="form-label">Số tiền *</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control" id="amount" name="amount" 
                                                   value="<?= $old_data['amount'] ?? '' ?>" required 
                                                   placeholder="Nhập số tiền" inputmode="numeric">
                                            <span class="input-group-text">VNĐ</span>
                                        </div>
                                        <div class="form-text">Chỉ nhập số, không nhập ký tự đặc biệt</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">Thông tin học viên</h5>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label for="student_name" class="form-label">Họ tên học viên *</label>
                                        <input type="text" class="form-control" id="student_name" name="student_name" 
                                               value="<?= $old_data['student_name'] ?? '' ?>" required 
                                               placeholder="Nhập họ tên học viên">
                                    </div>

                                    <div class="mb-3">
                                        <label for="course_search" class="form-label">Khóa học</label>
                                        <div class="course-combo-wrapper position-relative">
                                            <input type="text" 
                                                   class="form-control course-combo-input" 
                                                   id="course_search"
                                                   placeholder="Gõ để tìm hoặc chọn khóa học..."
                                                   autocomplete="off"
                                                   value="<?= isset($old_data['course_id']) ? htmlspecialchars($courses[array_search($old_data['course_id'], array_column($courses, 'id'))]['course_name'] ?? '') : '' ?>">
                                            <input type="hidden" 
                                                   id="course_id"
                                                   name="course_id" 
                                                   class="course-id-input"
                                                   value="<?= $old_data['course_id'] ?? '' ?>">
                                            <div class="course-dropdown position-absolute w-100" 
                                                 style="display: none; z-index: 1000; max-height: 300px; overflow-y: auto; 
                                                        background: white; border: 1px solid #ced4da; border-radius: 0.375rem;
                                                        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="payment_content" class="form-label">Nội dung chuyển khoản *</label>
                                        <select class="form-select" id="payment_content" name="payment_content" required>
                                            <option value="">Chọn nội dung thanh toán</option>
                                            <option value="full_payment" <?= (isset($old_data['payment_content']) && $old_data['payment_content'] == 'full_payment') ? 'selected' : '' ?>>
                                                Thanh toán đủ
                                            </option>
                                            <option value="deposit" <?= (isset($old_data['payment_content']) && $old_data['payment_content'] == 'deposit') ? 'selected' : '' ?>>
                                                Cọc học phí
                                            </option>
                                            <option value="full_payment_after_deposit" <?= (isset($old_data['payment_content']) && $old_data['payment_content'] == 'full_payment_after_deposit') ? 'selected' : '' ?>>
                                                Thanh toán đủ (đã cọc)
                                            </option>
                                            <option value="accounting_deposit" <?= (isset($old_data['payment_content']) && $old_data['payment_content'] == 'accounting_deposit') ? 'selected' : '' ?>>
                                                Cọc học phí (kế toán)
                                            </option>
                                            <option value="l1_payment" <?= (isset($old_data['payment_content']) && $old_data['payment_content'] == 'l1_payment') ? 'selected' : '' ?>>
                                                Thanh toán L1
                                            </option>
                                            <option value="l2_payment" <?= (isset($old_data['payment_content']) && $old_data['payment_content'] == 'l2_payment') ? 'selected' : '' ?>>
                                                Thanh toán L2
                                            </option>
                                            <option value="l3_payment" <?= (isset($old_data['payment_content']) && $old_data['payment_content'] == 'l3_payment') ? 'selected' : '' ?>>
                                                Thanh toán L3
                                            </option>
                                        </select>
                                    </div>

                                    <!-- Hidden staff ID - lấy từ user đăng nhập -->
                                    <input type="hidden" id="staff_id" name="staff_id" value="<?= $_SESSION['user_id'] ?? 1 ?>">
                                    
                                    <!-- Hidden notes field -->
                                    <input type="hidden" id="notes" name="notes" value="">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-12 d-flex gap-2">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save me-2"></i>Lưu báo cáo doanh thu
                            </button>
                            <a href="/revenue" class="btn btn-secondary">
                                <i class="fas fa-times me-2"></i>Hủy
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript -->
<script>
// Format number input with thousand separators
document.getElementById('amount').addEventListener('input', function(e) {
    // Remove all non-digit characters
    let value = e.target.value.replace(/\D/g, '');
    
    // Format with thousand separators
    if (value) {
        value = parseInt(value).toLocaleString('en-US');
    }
    
    e.target.value = value;
});

// Validate payment date <= today
document.getElementById('payment_date').addEventListener('change', function(e) {
    const selectedDate = new Date(e.target.value);
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    
    if (selectedDate > today) {
        alert('Ngày đóng học phí không được vượt quá ngày hôm nay!');
        e.target.value = new Date().toISOString().split('T')[0];
    }
});

// Validate form before submit
document.querySelector('form').addEventListener('submit', function(e) {
    const requiredFields = ['payment_date', 'transfer_type', 'amount', 'student_name', 'payment_content'];
    let isValid = true;

    requiredFields.forEach(function(fieldName) {
        const field = document.getElementById(fieldName);
        if (!field.value.trim()) {
            isValid = false;
            field.classList.add('is-invalid');
        } else {
            field.classList.remove('is-invalid');
        }
    });

    if (!isValid) {
        e.preventDefault();
        alert('Vui lòng điền đầy đủ thông tin bắt buộc (*)');
        return false;
    }

    // Validate amount is a valid number
    const amountInput = document.getElementById('amount');
    const amountValue = amountInput.value.replace(/,/g, ''); // Remove commas
    
    if (!amountValue || isNaN(amountValue) || parseFloat(amountValue) <= 0) {
        e.preventDefault();
        amountInput.classList.add('is-invalid');
        alert('Số tiền phải là số hợp lệ và lớn hơn 0');
        return false;
    }
});

// Image upload and OCR functionality
const confirmationImageInput = document.getElementById('confirmation_image');
const imagePreview = document.getElementById('image_preview');
const previewImg = document.getElementById('preview_img');
const clearImageBtn = document.getElementById('clear_image_btn');
const ocrStatus = document.getElementById('ocr_status');

// Handle image selection with auto OCR
confirmationImageInput.addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        // Validate file type
        if (!file.type.match('image.*')) {
            alert('Vui lòng chọn file ảnh (JPG, PNG)');
            e.target.value = '';
            return;
        }
        
        // Validate file size (10MB for better OCR quality)
        if (file.size > 10 * 1024 * 1024) {
            alert('File ảnh quá lớn. Vui lòng chọn file nhỏ hơn 10MB');
            e.target.value = '';
            return;
        }
        
        // Show preview
        const reader = new FileReader();
        reader.onload = function(e) {
            previewImg.src = e.target.result;
            imagePreview.style.display = 'block';
            
            // OCR functionality temporarily disabled
            // setTimeout(() => {
            //     processImageWithOCR(file);
            // }, 500);
        };
        reader.readAsDataURL(file);
    }
});

// Handle clear image
clearImageBtn.addEventListener('click', function() {
    confirmationImageInput.value = '';
    imagePreview.style.display = 'none';
    ocrStatus.style.display = 'none';
});

// Open full image view in modal
function openImageFullView() {
    const fullImg = document.getElementById('modal_full_img');
    const previewSrc = document.getElementById('preview_img').src;
    fullImg.src = previewSrc;
    
    const modal = new bootstrap.Modal(document.getElementById('imageFullViewModal'));
    modal.show();
}

// Auto OCR processing function with multiple OCR providers
async function processImageWithOCR(file) {
    // Show processing status
    ocrStatus.style.display = 'block';
    ocrStatus.innerHTML = '<i class=\"fas fa-spinner fa-spin me-2\"></i>Đang phân tích ảnh tự động...';
    
    try {
        // Method 1: Try OCR.space API first (best accuracy for Vietnamese)
        ocrStatus.innerHTML = '<i class=\"fas fa-spinner fa-spin me-2\"></i>Đang phân tích với OCR.space...';
        const ocrSpaceResult = await tryOCRSpace(file);
        if (ocrSpaceResult.success && ocrSpaceResult.parsed_data) {
            // Validate the result has meaningful data
            if (ocrSpaceResult.parsed_data.amount || ocrSpaceResult.parsed_data.receipt_code) {
                processOCRResult(ocrSpaceResult);
                return;
            }
        }
        
        // Method 2: Server-side OCR processing
        ocrStatus.innerHTML = '<i class=\"fas fa-spinner fa-spin me-2\"></i>Đang phân tích trên server...';
        const serverResult = await tryServerOCR(file);
        if (serverResult.success && serverResult.parsed_data) {
            if (serverResult.parsed_data.amount || serverResult.parsed_data.receipt_code) {
                processOCRResult(serverResult);
                return;
            }
        }
        
        // Method 3: Fallback to Tesseract.js (client-side)
        ocrStatus.innerHTML = '<i class=\"fas fa-spinner fa-spin me-2\"></i>Đang phân tích với Tesseract.js...';
        const tesseractResult = await tryTesseractJS(file);
        if (tesseractResult.success && tesseractResult.parsed_data) {
            if (tesseractResult.parsed_data.amount || tesseractResult.parsed_data.receipt_code) {
                processOCRResult(tesseractResult);
                return;
            }
        }
        
        // If all methods fail
        throw new Error('Không thể trích xuất thông tin từ ảnh');
        
    } catch (error) {
        console.error('OCR Error:', error);
        ocrStatus.style.display = 'none';
        
        // Show error alert
        const alertDiv = document.createElement('div');
        alertDiv.className = 'alert alert-warning alert-dismissible fade show mt-3';
        alertDiv.innerHTML = `
            <i class="fas fa-exclamation-triangle me-2"></i>
            <strong>Không thể trích xuất thông tin từ ảnh.</strong> Vui lòng nhập thủ công.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        imagePreview.parentNode.insertBefore(alertDiv, imagePreview.nextSibling);
        
        setTimeout(() => {
            if (alertDiv.parentNode) {
                alertDiv.remove();
            }
        }, 5000);
    }
}

// OCR.space API integration (free tier) - Optimized for Vietnamese banking
async function tryOCRSpace(file) {
    try {
        const formData = new FormData();
        formData.append('file', file);
        formData.append('apikey', 'helloworld'); // Free demo key
        formData.append('language', 'vie'); // Vietnamese language
        formData.append('isOverlayRequired', 'false');
        formData.append('detectOrientation', 'true');
        formData.append('scale', 'true');
        formData.append('OCREngine', '2'); // Engine 2 is better for Vietnamese
        
        const response = await fetch('https://api.ocr.space/parse/image', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        console.log('OCR.space Raw Response:', result);
        
        if (result.ParsedResults && result.ParsedResults.length > 0) {
            const extractedText = result.ParsedResults[0].ParsedText;
            console.log('OCR.space Extracted Text:', extractedText);
            
            const parsedData = parseVietnameseBankingText(extractedText);
            
            return {
                success: true,
                text: extractedText,
                provider: 'OCR.space',
                parsed_data: parseVietnameseBankingText(extractedText)
            };
        }
    } catch (error) {
        console.log('OCR.space failed:', error);
    }
    
    return { success: false };
}

// Tesseract.js client-side OCR (completely free)
async function tryTesseractJS(file) {
    try {
        // Load Tesseract.js if not already loaded
        if (typeof Tesseract === 'undefined') {
            await loadTesseractJS();
        }
        
        ocrStatus.innerHTML = '<i class=\"fas fa-spinner fa-spin me-2\"></i>Phân tích ảnh với Tesseract.js...';
        
        const { data: { text } } = await Tesseract.recognize(file, 'vie+eng', {
            logger: m => {
                if (m.status === 'recognizing text') {
                    const progress = Math.round(m.progress * 100);
                    ocrStatus.innerHTML = `<i class=\"fas fa-spinner fa-spin me-2\"></i>Phân tích ảnh: ${progress}%`;
                }
            }
        });
        
        return {
            success: true,
            text: text,
            provider: 'Tesseract.js',
            parsed_data: parseVietnameseBankingText(text)
        };
        
    } catch (error) {
        console.log('Tesseract.js failed:', error);
        return { success: false };
    }
}

// Load Tesseract.js dynamically
function loadTesseractJS() {
    return new Promise((resolve, reject) => {
        const script = document.createElement('script');
        script.src = 'https://cdn.jsdelivr.net/npm/tesseract.js@4/dist/tesseract.min.js';
        script.onload = resolve;
        script.onerror = reject;
        document.head.appendChild(script);
    });
}

// Server-side OCR fallback
async function tryServerOCR(file) {
    const formData = new FormData();
    formData.append('action', 'process_ocr');
    formData.append('image', file);
    
    const response = await fetch('/Quan_ly_trung_tam/public/ocr_handler.php', {
        method: 'POST',
        body: formData
    });
    
    return await response.json();
}

// Enhanced Vietnamese banking text parser for VCB Digibank and other banks
function parseVietnameseBankingText(text) {
    const result = {
        recipient_name: '',
        amount: '',
        content: '',
        receipt_code: '',
        account_number: '',
        transaction_time: '',
        confidence: 'medium'
    };
    
    console.log('OCR Raw Text:', text); // Debug log
    
    const lines = text.split(/[\n\r]+/).map(line => line.trim()).filter(line => line.length > 0);
    
    // Parse amount - Multiple patterns for different formats
    const amountPatterns = [
        // VCB format: "3,000,000 VND" or "3.000.000 VND"
        /([0-9]{1,3}(?:[,\.]\d{3})*)\s*(?:VND|vnđ|đ|dong)/i,
        // Standard format with label
        /(?:số tiền|amount|số tiền gd|số tiền chuyển)[\s:]*([0-9,.\s]+)/i,
        // Just number with commas/dots
        /^([0-9]{1,3}(?:[,\.]\d{3})+)$/,
    ];
    
    // Parse recipient name - More flexible patterns
    const namePatterns = [
        // VCB format: "Tên người nhận" label on one line, name on next line
        /(?:tên người nhận|người nhận|bên nhận)/i,
        // Direct company name pattern - must have CONG TY or CO LTD
        /(CONG\s*TY\s*TNHH[\s\w]+)/i,
        /(?:đến|tk nhận|họ tên)[\s:]*([A-ZÀÁẠẢÃÂẦẤẬẨẪĂẰẮẶẲẴÈÉẸẺẼÊỀẾỆỂỄÌÍỊỈĨÒÓỌỎÕÔỒỐỘỔỖƠỜỚỢỞỠÙÚỤỦŨƯỪỨỰỬỮỲÝỴỶỸĐ\s]{10,})/i,
    ];
    
    // Parse content/memo - Look for BT20 codes specifically (BT20 + 5 digits = 9 characters total)
    const contentPatterns = [
        // Direct BT20 code (must start with BT20)
        /(?:nội dung|content|memo|lý do|diễn giải|ghi chú)[\s:]*\n*(BT20\d{5})/i,
        /(?:nội dung|content|memo)[\s:]*\n*([^\n]+)/i,
        // Any line with BT20 followed by 5 digits
        /(BT\s*20\s*\d{5})/i,
    ];
    
    // Parse receipt code - Must start with BT20 followed by 5 digits (format: BT20XXXXX)
    const codePatterns = [
        // BT20 with spaces or without spaces
        /BT\s*20\s*(\d{5})/i,
        // Direct match BT20XXXXX
        /(BT20\d{5})/i,
        // With label
        /(?:mã phiếu|mã|code)[\s:]*([A-Z]*20\d{5})/i,
    ];
    
    // Process each line
    lines.forEach((line, index) => {
        const cleanLine = line.trim();
        
        // Extract amount
        if (!result.amount) {
            amountPatterns.forEach(pattern => {
                const match = cleanLine.match(pattern);
                if (match) {
                    const amount = match[1].replace(/[^\d]/g, '');
                    if (amount && parseInt(amount) >= 1000) { // Minimum 1,000 VND
                        result.amount = amount;
                    }
                }
            });
        }
        
        // Extract recipient name - VCB style (label on one line, name on next)
        if (!result.recipient_name) {
            // Check if current line is the label
            if (cleanLine.match(/tên người nhận/i) || cleanLine.match(/người nhận/i)) {
                // Get next line as the name
                if (index + 1 < lines.length) {
                    const nextLine = lines[index + 1].trim();
                    // Check if next line looks like a company name (all caps, no numbers at start)
                    if (nextLine.match(/^[A-ZÀÁẠẢÃÂẦẤẬẨẪĂẰẮẶẲẴÈÉẸẺẼÊỀẾỆỂỄÌÍỊỈĨÒÓỌỎÕÔỒỐỘỔỖƠỜỚỢỞỠÙÚỤỦŨƯỪỨỰỬỮỲÝỴỶỸĐ]/)) {
                        let fullName = nextLine;
                        // Check if name continues on next line
                        if (index + 2 < lines.length) {
                            const nextNextLine = lines[index + 2].trim();
                            if (nextNextLine.match(/^[A-ZÀÁẠẢÃÂẦẤẬẨẪĂẰẮẶẲẴÈÉẸẺẼÊỀẾỆỂỄÌÍỊỈĨÒÓỌỎÕÔỒỐỘỔỖƠỜỚỢỞỠÙÚỤỦŨƯỪỨỰỬỮỲÝỴỶỸĐ\s]+$/) && 
                                nextNextLine.length < 50 && 
                                !nextNextLine.match(/ngân hàng|bank|số tiền|amount/i)) {
                                fullName += ' ' + nextNextLine;
                            }
                        }
                        result.recipient_name = fullName.replace(/\s+/g, ' ').toUpperCase();
                    }
                }
            }
            
            // Direct match for company name
            if (!result.recipient_name) {
                namePatterns.forEach(pattern => {
                    const match = cleanLine.match(pattern);
                    if (match && match[1]) {
                        let name = match[1].trim();
                        // Clean up the name
                        name = name.replace(/\s+/g, ' ').toUpperCase();
                        // Check if it looks like a company name
                        if (name.length > 10 && !/^\d+$/.test(name) && !name.match(/TESSDATA|DIRECTORY/i)) {
                            result.recipient_name = name;
                        }
                    }
                });
            }
        }
        
        // Extract content/BT20 code
        if (!result.content || !result.receipt_code) {
            contentPatterns.forEach(pattern => {
                const match = cleanLine.match(pattern);
                if (match) {
                    const extractedContent = match[1].trim();
                    
                    // Check if it's a BT20 code (must start with BT20)
                    const bt20Match = extractedContent.match(/BT\s*20\s*(\d{5})/i);
                    if (bt20Match) {
                        result.receipt_code = 'BT20' + bt20Match[1];
                        result.content = result.receipt_code;
                    } else if (!result.content && extractedContent.length > 0) {
                        result.content = extractedContent;
                    }
                }
            });
        }
        
        // Direct search for BT20 code in any line
        if (!result.receipt_code) {
            codePatterns.forEach(pattern => {
                const match = cleanLine.match(pattern);
                if (match) {
                    let code = '';
                    if (match[1]) {
                        // Check if it's BT20XXXXX format
                        if (match[1].match(/BT20\d{5}/i)) {
                            code = match[1].replace(/\s/g, '').toUpperCase();
                        } else {
                            // Extract digits and check if it starts with 20
                            const digits = match[1].replace(/\D/g, '');
                            if (digits.length === 7 && digits.startsWith('20')) {
                                code = 'BT' + digits;
                            } else if (digits.length === 5) {
                                // Assume it's the 5 digits after BT20
                                code = 'BT20' + digits;
                            }
                        }
                    }
                    if (code && code.match(/^BT20\d{5}$/)) {
                        result.receipt_code = code;
                        if (!result.content) {
                            result.content = code;
                        }
                    }
                }
            });
        }
    });
    
    console.log('Parsed OCR Result:', result); // Debug log
    
    return result;
}

// Show OCR confirmation modal
function showOCRConfirmationModal(data, provider) {
    // Store data temporarily for confirmation
    window.ocrExtractedData = data;
    
    // Populate modal fields
    document.getElementById('ocr_recipient_name').textContent = data.recipient_name || 'Không trích xuất được';
    document.getElementById('ocr_amount').textContent = data.amount ? parseInt(data.amount).toLocaleString('vi-VN') + ' VNĐ' : 'Không trích xuất được';
    document.getElementById('ocr_content').textContent = data.content || 'Không trích xuất được';
    document.getElementById('ocr_receipt_code').textContent = data.receipt_code || 'Không trích xuất được';
    document.getElementById('ocr_provider').textContent = provider;
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('ocrConfirmationModal'));
    modal.show();
}

// Handle OCR confirmation button
document.addEventListener('DOMContentLoaded', function() {
    const confirmBtn = document.getElementById('confirmOCRBtn');
    if (confirmBtn) {
        confirmBtn.addEventListener('click', function() {
            const data = window.ocrExtractedData;
            if (data) {
                // Fill form fields
                if (data.recipient_name) {
                    document.getElementById('student_name').value = data.recipient_name;
                }
                if (data.amount) {
                    // Format amount with thousand separators
                    const formattedAmount = parseInt(data.amount).toLocaleString('en-US');
                    document.getElementById('amount').value = formattedAmount;
                }
                if (data.receipt_code) {
                    document.getElementById('receipt_code').value = data.receipt_code;
                }
                
                // Close modal
                bootstrap.Modal.getInstance(document.getElementById('ocrConfirmationModal')).hide();
                
                // Show success message
                const alertDiv = document.createElement('div');
                alertDiv.className = 'alert alert-success alert-dismissible fade show mt-3';
                alertDiv.innerHTML = `
                    <i class="fas fa-check-circle me-2"></i>
                    <strong>Đã điền thông tin!</strong> Vui lòng kiểm tra và chỉnh sửa nếu cần.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                `;
                
                imagePreview.parentNode.insertBefore(alertDiv, imagePreview.nextSibling);
                
                setTimeout(() => {
                    if (alertDiv.parentNode) {
                        alertDiv.remove();
                    }
                }, 5000);
            }
        });
    }
});

// Process OCR result and show confirmation popup
function processOCRResult(result) {
    ocrStatus.style.display = 'none';
    
    if (result.success && result.parsed_data) {
        const data = result.parsed_data;
        
        // Show confirmation popup with extracted data
        showOCRConfirmationModal(data, result.provider || 'Server OCR');
        
    } else {
        // Show error message
        const alertDiv = document.createElement('div');
        alertDiv.className = 'alert alert-warning alert-dismissible fade show mt-3';
        alertDiv.innerHTML = `
            <i class=\"fas fa-exclamation-triangle me-2\"></i>
            <strong>Không thể trích xuất thông tin</strong> từ ảnh này. Vui lòng nhập thủ công.
            <button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"alert\"></button>
        `;
        
        imagePreview.parentNode.insertBefore(alertDiv, imagePreview.nextSibling);
        
        setTimeout(() => {
            if (alertDiv.parentNode) {
                alertDiv.remove();
            }
        }, 4000);
    }
}
</script>

<style>
/* Course Combo Box Styling */
.course-combo-wrapper {
    position: relative;
}

.course-combo-input {
    width: 100%;
}

.course-dropdown {
    position: absolute;
    top: 100%;
    left: 0;
    width: 100%;
    max-height: 300px;
    overflow-y: auto;
    background: white;
    border: 1px solid #ced4da;
    border-radius: 0.375rem;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    z-index: 1000;
    margin-top: 2px;
}

.course-dropdown .dropdown-option {
    padding: 0.5rem;
    cursor: pointer;
    border-bottom: 1px solid #f0f0f0;
    transition: background-color 0.2s;
}

.course-dropdown .dropdown-option:hover {
    background-color: #f8f9fa;
}

.course-dropdown .dropdown-option:last-child {
    border-bottom: none;
}

.course-dropdown::-webkit-scrollbar {
    width: 8px;
}

.course-dropdown::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 4px;
}

.course-dropdown::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 4px;
}

.course-dropdown::-webkit-scrollbar-thumb:hover {
    background: #555;
}
</style>

<script>
// Course data from PHP
const courses = <?= json_encode($courses ?? []) ?>;

// Initialize Course Combo Box
document.addEventListener('DOMContentLoaded', function() {
    const wrapper = document.querySelector('.course-combo-wrapper');
    if (wrapper) {
        initializeCourseCombo(wrapper);
    }
});

function initializeCourseCombo(wrapper) {
    const input = wrapper.querySelector('.course-combo-input');
    const hiddenInput = wrapper.querySelector('.course-id-input');
    const dropdown = wrapper.querySelector('.course-dropdown');
    const amountInput = document.getElementById('amount');
    
    // Filter and display courses based on input
    function filterCourses(searchTerm) {
        const filtered = courses.filter(course => {
            const fullText = `${course.course_name}`;
            return fullText.toLowerCase().includes(searchTerm.toLowerCase());
        });
        
        displayDropdown(filtered);
    }
    
    // Display dropdown with options
    function displayDropdown(filteredCourses) {
        dropdown.innerHTML = '';
        
        if (filteredCourses.length === 0) {
            const noResult = document.createElement('div');
            noResult.className = 'p-2 text-muted';
            noResult.style.fontSize = '0.9rem';
            noResult.textContent = 'Không tìm thấy khóa học nào';
            dropdown.appendChild(noResult);
        } else {
            filteredCourses.forEach(course => {
                const option = document.createElement('div');
                option.className = 'dropdown-option';
                option.innerHTML = `
                    <div style="font-weight: 500; font-size: 0.9rem;">${course.course_name}</div>
                `;
                
                option.addEventListener('click', () => {
                    input.value = course.course_name;
                    hiddenInput.value = course.id;
                    dropdown.style.display = 'none';
                    
                    // Auto-fill amount if empty
                    if (!amountInput.value || amountInput.value === '0') {
                        amountInput.value = course.price;
                    }
                });
                
                dropdown.appendChild(option);
            });
        }
        
        dropdown.style.display = 'block';
    }
    
    // Input event listeners
    input.addEventListener('input', (e) => {
        const value = e.target.value;
        if (value.length > 0) {
            filterCourses(value);
        } else {
            hiddenInput.value = '';
            dropdown.style.display = 'none';
        }
    });
    
    input.addEventListener('focus', () => {
        if (input.value.length > 0) {
            filterCourses(input.value);
        } else {
            displayDropdown(courses);
        }
    });
    
    // Hide dropdown when clicking outside
    document.addEventListener('click', (e) => {
        if (!wrapper.contains(e.target)) {
            dropdown.style.display = 'none';
        }
    });
    
    // Allow clearing the field
    input.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            dropdown.style.display = 'none';
        }
    });
}

// Check receipt code duplicate on input with debounce
let receiptCodeTimeout;
const receiptCodeInput = document.getElementById('receipt_code');
const receiptCodeFeedback = document.getElementById('receiptCodeFeedback');

if (receiptCodeInput && receiptCodeFeedback) {
    receiptCodeInput.addEventListener('input', function(e) {
        const receiptCode = e.target.value.trim();
        
        // Clear previous timeout
        clearTimeout(receiptCodeTimeout);
        
        // Reset feedback
        receiptCodeFeedback.textContent = '';
        receiptCodeFeedback.className = 'form-text';
        receiptCodeInput.classList.remove('is-invalid', 'is-valid');
        
        if (!receiptCode) {
            return;
        }
        
        // Show checking message
        receiptCodeFeedback.textContent = 'Đang kiểm tra...';
        receiptCodeFeedback.className = 'form-text text-muted';
        
        // Debounce 500ms
        receiptCodeTimeout = setTimeout(() => {
            checkReceiptCodeDuplicate(receiptCode);
        }, 500);
    });
}

function checkReceiptCodeDuplicate(receiptCode) {
    // Check in database
    fetch('/Quan_ly_trung_tam/public/revenue/check-receipt-code?receipt_code=' + encodeURIComponent(receiptCode))
        .then(response => response.json())
        .then(data => {
            if (data.exists) {
                receiptCodeInput.classList.add('is-invalid');
                receiptCodeInput.classList.remove('is-valid');
                receiptCodeFeedback.textContent = '⚠️ Mã phiếu thu đã tồn tại trong hệ thống!';
                receiptCodeFeedback.className = 'form-text text-danger';
            } else {
                receiptCodeInput.classList.add('is-valid');
                receiptCodeInput.classList.remove('is-invalid');
                receiptCodeFeedback.textContent = '✓ Mã phiếu thu hợp lệ';
                receiptCodeFeedback.className = 'form-text text-success';
            }
        })
        .catch(error => {
            console.error('Error checking receipt code:', error);
            receiptCodeFeedback.textContent = '';
            receiptCodeFeedback.className = 'form-text';
            receiptCodeInput.classList.remove('is-invalid', 'is-valid');
        });
}

// Validate form before submit
document.querySelector('form').addEventListener('submit', function(e) {
    const receiptCodeInput = document.getElementById('receipt_code');
    
    // Check if receipt code has validation error
    if (receiptCodeInput && receiptCodeInput.value.trim() && receiptCodeInput.classList.contains('is-invalid')) {
        e.preventDefault();
        Swal.fire({
            icon: 'error',
            title: 'Mã phiếu thu trùng lặp!',
            text: 'Vui lòng sửa mã phiếu thu trước khi lưu.'
        });
        receiptCodeInput.focus();
        return false;
    }
});
</script>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/main.php';
useModernLayout('Tạo báo cáo doanh thu', $content);
?>