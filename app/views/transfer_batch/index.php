<?php
require_once __DIR__ . '/../layouts/main.php';

$data = $data ?? ['details' => [], 'totals' => []];
$dateFrom = $dateFrom ?? date('Y-m-d');
$dateTo = $dateTo ?? date('Y-m-d');

// Page content
ob_start();
?>

<?= pageHeader(
    'Quản lý đợt chuyển tiền', 
    'Báo cáo tổng hợp doanh thu theo khoảng thời gian', 
    '<a href="/Quan_ly_trung_tam/public/revenue" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-2"></i>Quay lại doanh thu
    </a>'
) ?>

<div class="p-3">
    <!-- Filter Date Range -->
    <div class="stats-card mb-4">
        <div class="card-body">
            <form method="GET" action="/Quan_ly_trung_tam/public/transfer-batch" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label for="from_date" class="form-label fw-bold">
                        <i class="fas fa-calendar-alt me-1"></i>Từ ngày
                    </label>
                    <input type="date" 
                           class="form-control form-control-lg" 
                           id="from_date" 
                           name="from_date" 
                           value="<?= htmlspecialchars($dateFrom) ?>">
                </div>
                <div class="col-md-3">
                    <label for="to_date" class="form-label fw-bold">
                        <i class="fas fa-calendar-alt me-1"></i>Đến ngày
                    </label>
                    <input type="date" 
                           class="form-control form-control-lg" 
                           id="to_date" 
                           name="to_date" 
                           value="<?= htmlspecialchars($dateTo) ?>">
                </div>
                <div class="col-md-6">
                    <button type="submit" class="btn btn-primary btn-lg me-2">
                        <i class="fas fa-search me-2"></i>Xem báo cáo
                    </button>
                    <button type="button" class="btn btn-outline-secondary btn-lg me-2" onclick="setThisWeek()">
                        Tuần này
                    </button>
                    <button type="button" class="btn btn-outline-secondary btn-lg" onclick="setThisMonth()">
                        Tháng này
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Report Table -->
    <div class="stats-card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-hover mb-0" style="font-size: 14px;">
                    <thead style="background: #f8f9fa;">
                        <tr>
                            <th class="text-center" style="width: 120px; background: #e3f2fd;">Từ ngày</th>
                            <th class="text-center" style="width: 120px; background: #e3f2fd;">Đến ngày</th>
                            <th class="text-center" style="width: 180px; background: #e3f2fd;">
                                Tổng thực thu<br><small>(Doanh thu)</small>
                            </th>
                            <th class="text-center" style="width: 150px; background: #ffebcc;">
                                Chuyển khoản<br>TK CTY
                            </th>
                            <th class="text-center" style="width: 150px; background: #ffebcc;">
                                Chuyển khoản<br>TK Th Hiến
                            </th>
                            <th class="text-center" style="width: 150px; background: #ffebcc;">
                                Chi nhánh CK<br>cho Th Hiến
                            </th>
                            <th class="text-center" style="width: 120px; background: #e3f2fd;">Chênh lệch</th>
                            <th class="text-center" style="width: 150px; background: #d4edda;">Tạo mã QR</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- TỔNG CỘNG Row -->
                        <tr style="background: #fff3cd; font-weight: bold;">
                            <td colspan="2" class="text-center" style="vertical-align: middle;">
                                <strong style="font-size: 16px;">TỔNG CỘNG</strong>
                            </td>
                            <td class="text-end" style="color: #0066cc;">
                                <?= number_format($data['total_revenue'], 0, ',', '.') ?>
                            </td>
                            <td class="text-end" style="background: #ffff99;">
                                <?= number_format($data['transfer_company'], 0, ',', '.') ?>
                            </td>
                            <td class="text-end" style="background: #ffff99;">
                                <?= number_format($data['transfer_thien'], 0, ',', '.') ?>
                            </td>
                            <td class="text-end" style="background: #ffff99;">
                                <?= number_format($data['cash_thien'], 0, ',', '.') ?>
                            </td>
                            <td class="text-end" style="color: <?= $data['difference'] != 0 ? '#ff0000' : '#000' ?>;">
                                <?= number_format($data['difference'], 0, ',', '.') ?>
                            </td>
                            <td class="text-center"></td>
                        </tr>

                        <!-- Data Row (Theo khoảng thời gian đã chọn) -->
                        <tr>
                            <td class="text-center"><?= date('d/m/Y', strtotime($data['date_from'])) ?></td>
                            <td class="text-center"><?= date('d/m/Y', strtotime($data['date_to'])) ?></td>
                            <td class="text-end"><?= number_format($data['total_revenue'], 0, ',', '.') ?></td>
                            <td class="text-end" style="background: #ffff99;"><?= number_format($data['transfer_company'], 0, ',', '.') ?></td>
                            <td class="text-end" style="background: #ffff99;"><?= number_format($data['transfer_thien'], 0, ',', '.') ?></td>
                            <td class="text-end" style="background: #ffff99;"><?= number_format($data['cash_thien'], 0, ',', '.') ?></td>
                            <td class="text-end" style="color: <?= $data['difference'] != 0 ? '#ff0000' : '#000' ?>;">
                                <?= number_format($data['difference'], 0, ',', '.') ?>
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn btn-success btn-sm" onclick="showQRModal(<?= $data['cash_thien'] ?>)">
                                    <i class="fas fa-qrcode me-1"></i>Tạo QR
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Legend -->
    <div class="mt-3">
        <small class="text-muted">
            <i class="fas fa-info-circle me-1"></i>
            <strong>Chú thích:</strong>
            <span class="ms-2">Tổng thực thu = Tổng tất cả doanh thu</span>
            <span class="ms-2">|</span>
            <span class="ms-2">Chuyển khoản TK CTY = Tài khoản Công ty</span>
            <span class="ms-2">|</span>
            <span class="ms-2">Chuyển khoản TK Th Hiến = Tài khoản Thầy Hiến</span>
            <span class="ms-2">|</span>
            <span class="ms-2">Chi nhánh CK cho Th Hiến = Cô Nhi + Tiền mặt</span>
        </small>
    </div>
</div>

<!-- QR Code Modal -->
<div class="modal fade" id="qrModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="fas fa-qrcode me-2"></i>Tạo mã QR chuyển khoản
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Step 1: Input Form -->
                <div id="qrInputForm">
                    <div class="alert alert-info mb-3">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Thông tin người nhận:</strong><br>
                        Tên TK: <strong>BACH XUAN HIEN</strong><br>
                        Số TK: <strong>0901456055</strong><br>
                        Ngân hàng: <strong>SHB</strong>
                    </div>
                    
                    <div class="mb-3">
                        <label for="qrAmount" class="form-label fw-bold">Số tiền chuyển:</label>
                        <input type="text" class="form-control form-control-lg" id="qrAmount" readonly>
                    </div>
                    
                    <div class="mb-3">
                        <label for="qrContent" class="form-label fw-bold">Nội dung chuyển khoản:</label>
                        <input type="text" 
                               class="form-control form-control-lg" 
                               id="qrContent" 
                               placeholder="VD: Chuyen tien thang 12/2024"
                               maxlength="50">
                        <small class="text-muted">Tối đa 50 ký tự</small>
                    </div>
                </div>
                
                <!-- Step 2: QR Display -->
                <div id="qrDisplay" class="d-none">
                    <div class="text-center mb-3">
                        <img id="qrImage" src="" alt="QR Code" class="img-fluid" style="max-width: 300px; border: 2px solid #ddd; border-radius: 8px; padding: 10px;">
                    </div>
                    <div class="alert alert-success">
                        <p class="mb-1"><strong>Số tiền:</strong> <span id="qrAmountDisplay"></span></p>
                        <p class="mb-0"><strong>Nội dung:</strong> <span id="qrContentDisplay"></span></p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <!-- Input Form Buttons -->
                <div id="qrInputButtons">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="button" class="btn btn-success" onclick="generateQR()">
                        <i class="fas fa-qrcode me-2"></i>Tạo mã QR
                    </button>
                </div>
                
                <!-- Display Buttons -->
                <div id="qrDisplayButtons" class="d-none">
                    <button type="button" class="btn btn-secondary" onclick="resetQRForm()">
                        <i class="fas fa-redo me-2"></i>Tạo lại
                    </button>
                    <button type="button" class="btn btn-primary" onclick="downloadQR()">
                        <i class="fas fa-download me-2"></i>Tải xuống
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let currentQRAmount = 0;

function setThisWeek() {
    const today = new Date();
    const dayOfWeek = today.getDay();
    const monday = new Date(today);
    monday.setDate(today.getDate() - (dayOfWeek === 0 ? 6 : dayOfWeek - 1));
    const sunday = new Date(monday);
    sunday.setDate(monday.getDate() + 6);
    
    document.getElementById('from_date').value = formatDate(monday);
    document.getElementById('to_date').value = formatDate(sunday);
}

function setThisMonth() {
    const today = new Date();
    const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
    const lastDay = new Date(today.getFullYear(), today.getMonth() + 1, 0);
    
    document.getElementById('from_date').value = formatDate(firstDay);
    document.getElementById('to_date').value = formatDate(lastDay);
}

function formatDate(date) {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
}

// QR Code Functions
function showQRModal(amount) {
    try {
        currentQRAmount = amount;
        document.getElementById('qrAmount').value = formatMoney(amount) + ' đ';
        document.getElementById('qrContent').value = '';
        
        // Reset form
        document.getElementById('qrInputForm').classList.remove('d-none');
        document.getElementById('qrDisplay').classList.add('d-none');
        document.getElementById('qrInputButtons').classList.remove('d-none');
        document.getElementById('qrDisplayButtons').classList.add('d-none');
        
        // Show modal
        const modal = new bootstrap.Modal(document.getElementById('qrModal'));
        modal.show();
    } catch (error) {
        console.error('Error showing modal:', error);
        alert('Không thể mở modal. Vui lòng thử lại.');
    }
}

function generateQR() {
    const content = document.getElementById('qrContent').value.trim();
    
    if (!content) {
        alert('Vui lòng nhập nội dung chuyển khoản!');
        return;
    }
    
    // Bank info
    const bankBin = '970443'; // SHB
    const accountNo = '0901456055';
    const accountName = 'BACH XUAN HIEN';
    const amount = Math.round(currentQRAmount);
    
    // Generate VietQR URL
    const qrUrl = `https://img.vietqr.io/image/${bankBin}-${accountNo}-compact2.jpg?amount=${amount}&addInfo=${encodeURIComponent(content)}&accountName=${encodeURIComponent(accountName)}`;
    
    // Display QR
    document.getElementById('qrImage').src = qrUrl;
    document.getElementById('qrAmountDisplay').textContent = formatMoney(amount) + ' đ';
    document.getElementById('qrContentDisplay').textContent = content;
    
    // Switch views
    document.getElementById('qrInputForm').classList.add('d-none');
    document.getElementById('qrDisplay').classList.remove('d-none');
    document.getElementById('qrInputButtons').classList.add('d-none');
    document.getElementById('qrDisplayButtons').classList.remove('d-none');
}

function resetQRForm() {
    document.getElementById('qrInputForm').classList.remove('d-none');
    document.getElementById('qrDisplay').classList.add('d-none');
    document.getElementById('qrInputButtons').classList.remove('d-none');
    document.getElementById('qrDisplayButtons').classList.add('d-none');
    document.getElementById('qrContent').value = '';
    document.getElementById('qrContent').focus();
}

function downloadQR() {
    const qrImage = document.getElementById('qrImage');
    const link = document.createElement('a');
    link.href = qrImage.src;
    link.download = 'QR_ChuyenKhoan_' + Date.now() + '.jpg';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

function formatMoney(amount) {
    return new Intl.NumberFormat('vi-VN').format(amount);
}
</script>

<style>
.stats-card {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    border: none;
}

.table {
    margin-bottom: 0 !important;
}

.table thead th {
    font-weight: 600;
    padding: 12px 8px;
    border: 1px solid #dee2e6;
}

.table tbody td {
    padding: 10px 8px;
    border: 1px solid #dee2e6;
    vertical-align: middle;
}

.table-bordered {
    border: 1px solid #dee2e6;
}

.form-control-lg {
    height: 46px;
}

.btn-lg {
    padding: 12px 24px;
}
</style>

<?php
$content = ob_get_clean();
useModernLayout('Quản lý đợt chuyển tiền', $content);
