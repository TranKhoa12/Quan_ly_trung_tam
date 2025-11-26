<?php
require_once __DIR__ . '/../layouts/main.php';

// Ensure revenue_reports is always an array
$revenue_reports = $revenue_reports ?? [];

// Page content
ob_start();
?>

<?= pageHeader(
    'Báo cáo doanh thu', 
    'Theo dõi và quản lý doanh thu từ học phí và các khoản thu khác', 
    '<a href="/Quan_ly_trung_tam/public/revenue/create" class="btn btn-primary">
        <i class="fas fa-plus me-2"></i>Thêm doanh thu mới
    </a>'
) ?>

<div class="p-3">
    <!-- Statistics - Chỉ Admin mới thấy -->
    <?php if ($userRole === 'admin'): ?>
        <div class="row g-3 mb-4">
            <div class="col-md-3 col-sm-6">
                <?php 
                $total_amount = 0;
                if (!empty($revenue_reports) && is_array($revenue_reports)) {
                    $amounts = array_column($revenue_reports, 'amount');
                    $total_amount = array_sum(array_filter($amounts, 'is_numeric'));
                }
                ?>
                <?= statsCard(
                    'fas fa-money-bill-wave', 
                    'Tổng doanh thu', 
                    number_format((float)$total_amount) . ' đ', 
                    'Tổng doanh thu',
                    'success'
                ) ?>
            </div>
            
            <div class="col-md-3 col-sm-6">
                <?php $report_count = !empty($revenue_reports) && is_array($revenue_reports) ? count($revenue_reports) : 0; ?>
                <?= statsCard(
                    'fas fa-receipt', 
                    'Số giao dịch', 
                    $report_count, 
                    'Tổng số giao dịch',
                    'primary'
                ) ?>
            </div>
            
            <div class="col-md-3 col-sm-6">
                <?php 
                $cash_amount = 0;
                if (!empty($revenue_reports) && is_array($revenue_reports)) {
                    $cash_payments = array_filter($revenue_reports, function($r) { 
                        return isset($r['transfer_type']) && $r['transfer_type'] === 'cash'; 
                    });
                    if (!empty($cash_payments)) {
                        $amounts = array_column($cash_payments, 'amount');
                        $cash_amount = array_sum(array_filter($amounts, 'is_numeric'));
                    }
                }
                ?>
                <?= statsCard(
                    'fas fa-coins', 
                    'Tiền mặt', 
                    number_format((float)$cash_amount) . ' đ', 
                    'Doanh thu từ tiền mặt',
                    'warning'
                ) ?>
            </div>
            
            <div class="col-md-3 col-sm-6">
                <?php 
                $transfer_amount = 0;
                if (!empty($revenue_reports) && is_array($revenue_reports)) {
                    $transfer_payments = array_filter($revenue_reports, function($r) { 
                        return isset($r['transfer_type']) && in_array($r['transfer_type'], ['account_co_nhi', 'account_thay_hien', 'account_company']); 
                    });
                    if (!empty($transfer_payments)) {
                        $amounts = array_column($transfer_payments, 'amount');
                        $transfer_amount = array_sum(array_filter($amounts, 'is_numeric'));
                    }
                }
                ?>
                <?= statsCard(
                    'fas fa-credit-card', 
                    'Chuyển khoản', 
                    number_format((float)$transfer_amount) . ' đ', 
                    'Doanh thu từ chuyển khoản',
                    'info'
                ) ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Filter and Search -->
    <div class="stats-card mb-4">
        <div class="card-body">
            <form method="GET" action="/Quan_ly_trung_tam/public/revenue">
                <div class="row g-3 align-items-end">
                    <?php if ($userRole === 'admin'): ?>
                    <div class="col-md-2">
                        <label class="form-label">
                            <i class="fas fa-calendar-alt text-primary"></i> Từ ngày
                        </label>
                        <input type="date" class="form-control" name="from_date" 
                               value="<?= $_GET['from_date'] ?? '' ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">
                            <i class="fas fa-calendar-alt text-primary"></i> Đến ngày
                        </label>
                        <input type="date" class="form-control" name="to_date" 
                               value="<?= $_GET['to_date'] ?? '' ?>">
                    </div>
                    <?php endif; ?>
                    <div class="col-md-2">
                        <label class="form-label">
                            <i class="fas fa-exchange-alt text-primary"></i> Hình thức
                        </label>
                        <select class="form-select" name="transfer_type">
                            <option value="">Tất cả hình thức</option>
                            <option value="cash" <?= (isset($_GET['transfer_type']) && $_GET['transfer_type'] === 'cash') ? 'selected' : '' ?>>Tiền mặt</option>
                            <option value="account_co_nhi" <?= (isset($_GET['transfer_type']) && $_GET['transfer_type'] === 'account_co_nhi') ? 'selected' : '' ?>>TK Cô Nhi</option>
                            <option value="account_thay_hien" <?= (isset($_GET['transfer_type']) && $_GET['transfer_type'] === 'account_thay_hien') ? 'selected' : '' ?>>TK Thầy Hiền</option>
                            <option value="account_company" <?= (isset($_GET['transfer_type']) && $_GET['transfer_type'] === 'account_company') ? 'selected' : '' ?>>TK Công ty</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">
                            <i class="fas fa-tags text-primary"></i> Loại thanh toán
                        </label>
                        <select class="form-select" name="payment_content">
                            <option value="">Tất cả loại</option>
                            <option value="full_payment" <?= (isset($_GET['payment_content']) && $_GET['payment_content'] === 'full_payment') ? 'selected' : '' ?>>Thanh toán đủ</option>
                            <option value="deposit" <?= (isset($_GET['payment_content']) && $_GET['payment_content'] === 'deposit') ? 'selected' : '' ?>>Cọc học phí</option>
                            <option value="full_payment_after_deposit" <?= (isset($_GET['payment_content']) && $_GET['payment_content'] === 'full_payment_after_deposit') ? 'selected' : '' ?>>Thanh toán đủ (đã cọc)</option>
                            <option value="accounting_deposit" <?= (isset($_GET['payment_content']) && $_GET['payment_content'] === 'accounting_deposit') ? 'selected' : '' ?>>Cọc học phí (kế toán)</option>
                            <option value="l1_payment" <?= (isset($_GET['payment_content']) && $_GET['payment_content'] === 'l1_payment') ? 'selected' : '' ?>>Thanh toán L1</option>
                            <option value="l2_payment" <?= (isset($_GET['payment_content']) && $_GET['payment_content'] === 'l2_payment') ? 'selected' : '' ?>>Thanh toán L2</option>
                            <option value="l3_payment" <?= (isset($_GET['payment_content']) && $_GET['payment_content'] === 'l3_payment') ? 'selected' : '' ?>>Thanh toán L3</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">
                            <i class="fas fa-search text-primary"></i> Tìm kiếm
                        </label>
                        <input type="text" class="form-control" name="search" 
                               placeholder="Tên học viên..." 
                               value="<?= $_GET['search'] ?? '' ?>">
                    </div>
                    <div class="col-md-1">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                    <div class="col-md-1">
                        <a href="/Quan_ly_trung_tam/public/revenue" class="btn btn-outline-secondary w-100" title="Xóa bộ lọc">
                            <i class="fas fa-redo"></i>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Revenue Table -->
    <div class="stats-card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="card-title mb-0">
                    <i class="fas fa-list text-primary me-2"></i>
                    Danh sách doanh thu
                </h6>
                <span class="badge bg-primary"><?= count($revenue_reports) ?> giao dịch</span>
            </div>
            
            <?php if (!empty($revenue_reports)): ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Ngày thanh toán</th>
                                <th>Học viên</th>
                                <th>Khóa học</th>
                                <th>Số tiền</th>
                                <th>Hình thức</th>
                                <th>Loại thanh toán</th>
                                <th>Mã biên lai</th>
                                <th>Nhân viên</th>
                                <th>Hình ảnh</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($revenue_reports as $report): ?>
                                <tr>
                                    <td>
                                        <div>
                                            <span class="fw-semibold"><?= date('d/m/Y', strtotime($report['payment_date'])) ?></span>
                                            <br><small class="text-muted"><?= date('H:i', strtotime($report['created_at'])) ?></small>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-circle bg-success text-white me-2 d-flex align-items-center justify-content-center" 
                                                 style="width: 32px; height: 32px; border-radius: 50%; font-weight: 600; font-size: 0.8rem;">
                                                <?= strtoupper(substr($report['student_name'], 0, 1)) ?>
                                            </div>
                                            <?= htmlspecialchars($report['student_name']) ?>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if (!empty($report['course_name'])): ?>
                                            <?php 
                                            $courseName = $report['course_name'];
                                            $displayName = strlen($courseName) > 30 ? substr($courseName, 0, 27) . '...' : $courseName;
                                            ?>
                                            <span class="badge bg-info" 
                                                  title="<?= htmlspecialchars($courseName) ?>"
                                                  style="cursor: help; max-width: 200px; white-space: normal; text-align: left;">
                                                <?= htmlspecialchars($displayName) ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted">Chưa có</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="fw-bold text-success"><?= number_format($report['amount']) ?> đ</span>
                                    </td>
                                    <td>
                                        <?php
                                        $transferTypes = [
                                            'cash' => ['label' => 'Tiền mặt', 'color' => 'warning', 'icon' => 'fas fa-coins'],
                                            'account_co_nhi' => ['label' => 'TK Cô Nhi', 'color' => 'primary', 'icon' => 'fas fa-university'],
                                            'account_thay_hien' => ['label' => 'TK Thầy Hiền', 'color' => 'info', 'icon' => 'fas fa-university'],
                                            'account_company' => ['label' => 'TK Công ty', 'color' => 'dark', 'icon' => 'fas fa-building']
                                        ];
                                        $type = $transferTypes[$report['transfer_type']] ?? ['label' => $report['transfer_type'], 'color' => 'secondary', 'icon' => 'fas fa-question'];
                                        ?>
                                        <span class="badge bg-<?= $type['color'] ?>">
                                            <i class="<?= $type['icon'] ?> me-1"></i><?= $type['label'] ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php
                                        $paymentTypes = [
                                            'full_payment' => ['label' => 'Thanh toán đầy đủ', 'color' => 'success'],
                                            'deposit' => ['label' => 'Đặt cọc', 'color' => 'warning'],
                                            'full_payment_after_deposit' => ['label' => 'Thanh toán sau cọc', 'color' => 'info'],
                                            'accounting_deposit' => ['label' => 'Cọc kế toán', 'color' => 'primary'],
                                            'l1_payment' => ['label' => 'Thanh toán L1', 'color' => 'secondary'],
                                            'l2_payment' => ['label' => 'Thanh toán L2', 'color' => 'secondary'],
                                            'l3_payment' => ['label' => 'Thanh toán L3', 'color' => 'secondary']
                                        ];
                                        $payment = $paymentTypes[$report['payment_content']] ?? ['label' => $report['payment_content'], 'color' => 'light'];
                                        ?>
                                        <span class="badge bg-<?= $payment['color'] ?>"><?= $payment['label'] ?></span>
                                    </td>
                                    <td>
                                        <?php if (!empty($report['receipt_code'])): ?>
                                            <code><?= htmlspecialchars($report['receipt_code']) ?></code>
                                        <?php else: ?>
                                            <span class="text-muted">Chưa có</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($report['staff_name'])): ?>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-circle bg-primary text-white me-2 d-flex align-items-center justify-content-center" 
                                                     style="width: 28px; height: 28px; border-radius: 50%; font-weight: 600; font-size: 0.75rem;">
                                                    <?= strtoupper(substr($report['staff_name'], 0, 1)) ?>
                                                </div>
                                                <span><?= htmlspecialchars($report['staff_name']) ?></span>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-muted">Chưa rõ</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($report['confirmation_image'])): ?>
                                            <button type="button" 
                                                    class="btn btn-sm btn-outline-info" 
                                                    onclick="showImageModal('/Quan_ly_trung_tam/public/uploads/<?= htmlspecialchars($report['confirmation_image']) ?>')"
                                                    title="Xem ảnh">
                                                <i class="fas fa-image"></i>
                                            </button>
                                        <?php else: ?>
                                            <span class="text-muted">Không có</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Total Summary - Chỉ Admin mới thấy -->
                <?php if ($userRole === 'admin'): ?>
                    <div class="border-top pt-3 mt-3">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="text-center">
                                    <div class="text-success fs-5 fw-bold"><?= number_format($total_amount) ?> đ</div>
                                    <small class="text-muted">Tổng doanh thu</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="text-center">
                                    <div class="text-warning fs-5 fw-bold"><?= number_format($cash_amount) ?> đ</div>
                                    <small class="text-muted">Tiền mặt</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="text-center">
                                    <div class="text-info fs-5 fw-bold"><?= number_format($transfer_amount) ?> đ</div>
                                    <small class="text-muted">Chuyển khoản</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="text-center">
                                    <div class="text-primary fs-5 fw-bold"><?= count($revenue_reports) ?></div>
                                    <small class="text-muted">Giao dịch</small>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
                
                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                    <div class="mt-4">
                        <nav aria-label="Pagination">
                            <ul class="pagination justify-content-center">
                                <!-- Previous Button -->
                                <li class="page-item <?= $currentPage <= 1 ? 'disabled' : '' ?>">
                                    <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $currentPage - 1])) ?>">
                                        <i class="fas fa-chevron-left"></i> Trước
                                    </a>
                                </li>
                                
                                <?php
                                // Calculate page range
                                $startPage = max(1, $currentPage - 2);
                                $endPage = min($totalPages, $currentPage + 2);
                                
                                // First page
                                if ($startPage > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => 1])) ?>">1</a>
                                    </li>
                                    <?php if ($startPage > 2): ?>
                                        <li class="page-item disabled"><span class="page-link">...</span></li>
                                    <?php endif; ?>
                                <?php endif; ?>
                                
                                <!-- Page numbers -->
                                <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                                    <li class="page-item <?= $i === $currentPage ? 'active' : '' ?>">
                                        <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"><?= $i ?></a>
                                    </li>
                                <?php endfor; ?>
                                
                                <!-- Last page -->
                                <?php if ($endPage < $totalPages): ?>
                                    <?php if ($endPage < $totalPages - 1): ?>
                                        <li class="page-item disabled"><span class="page-link">...</span></li>
                                    <?php endif; ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $totalPages])) ?>"><?= $totalPages ?></a>
                                    </li>
                                <?php endif; ?>
                                
                                <!-- Next Button -->
                                <li class="page-item <?= $currentPage >= $totalPages ? 'disabled' : '' ?>">
                                    <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $currentPage + 1])) ?>">
                                        Sau <i class="fas fa-chevron-right"></i>
                                    </a>
                                </li>
                            </ul>
                        </nav>
                        
                        <!-- Pagination Info -->
                        <div class="text-center text-muted mt-3">
                            <small>
                                Hiển thị <?= min(($currentPage - 1) * $perPage + 1, $totalRecords) ?> - 
                                <?= min($currentPage * $perPage, $totalRecords) ?> 
                                trong tổng số <?= $totalRecords ?> bản ghi
                            </small>
                        </div>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-money-bill-wave text-muted fa-4x mb-3"></i>
                    <h5 class="text-muted">Chưa có doanh thu nào</h5>
                    <p class="text-muted">Bắt đầu thêm giao dịch doanh thu đầu tiên</p>
                    <a href="/Quan_ly_trung_tam/public/revenue/create" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Thêm doanh thu đầu tiên
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Image Preview Modal -->
<div class="modal fade" id="imageModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-image text-primary me-2"></i>Ảnh xác nhận chuyển khoản
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center p-0">
                <img id="modalImage" src="" alt="Confirmation Image" class="img-fluid" style="max-height: 70vh; width: auto;">
            </div>
            <div class="modal-footer">
                <a id="downloadImageBtn" href="" download class="btn btn-primary">
                    <i class="fas fa-download me-2"></i>Tải xuống
                </a>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Đóng
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function showImageModal(imagePath) {
    const modal = new bootstrap.Modal(document.getElementById('imageModal'));
    const modalImage = document.getElementById('modalImage');
    const downloadBtn = document.getElementById('downloadImageBtn');
    
    modalImage.src = imagePath;
    downloadBtn.href = imagePath;
    
    modal.show();
}

function deleteRevenue(revenueId) {
    if (confirm('Bạn có chắc chắn muốn xóa giao dịch doanh thu này?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '/Quan_ly_trung_tam/public/revenue/' + revenueId + '/delete';
        
        document.body.appendChild(form);
        form.submit();
    }
}
</script>

<?php
$content = ob_get_clean();

// Render layout
useModernLayout('Báo cáo doanh thu', $content);
?>