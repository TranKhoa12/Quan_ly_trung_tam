<?php
require_once __DIR__ . '/../layouts/main.php';

// Ensure revenue_reports is always an array
$revenue_reports = $revenue_reports ?? [];

// Page content
ob_start();
?>

<?php
$headerButtons = '<div class="btn-group">';
// Chỉ admin mới thấy nút Quản lý đợt chuyển tiền
if ($userRole === 'admin') {
    $headerButtons .= '<a href="/Quan_ly_trung_tam/public/transfer-batch" class="btn btn-success">
            <i class="fas fa-money-check-alt me-2"></i>Quản lý đợt chuyển tiền
        </a>';
}
$headerButtons .= '<a href="/Quan_ly_trung_tam/public/revenue/create" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Thêm doanh thu mới
        </a>
    </div>';
?>

<?= pageHeader(
    'Báo cáo doanh thu', 
    'Theo dõi và quản lý doanh thu từ học phí và các khoản thu khác', 
    $headerButtons
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
                        <div class="custom-multi-select">
                            <button type="button" class="form-control text-start dropdown-toggle-custom" id="transferTypeBtn" onclick="toggleTransferDropdown(event)">
                                <span id="transferTypeLabel">Tất cả hình thức</span>
                                <i class="fas fa-chevron-down float-end mt-1"></i>
                            </button>
                            <div class="dropdown-menu-custom" id="transferTypeDropdown">
                                <?php
                                $selectedTypes = isset($_GET['transfer_type']) ? (is_array($_GET['transfer_type']) ? $_GET['transfer_type'] : [$_GET['transfer_type']]) : [];
                                $transferOptions = [
                                    'cash' => 'Tiền mặt',
                                    'account_co_nhi' => 'TK Cô Nhi',
                                    'account_thay_hien' => 'TK Thầy Hiến',
                                    'account_company' => 'TK Công ty'
                                ];
                                foreach ($transferOptions as $value => $label):
                                ?>
                                <label class="dropdown-item-custom">
                                    <input type="checkbox" name="transfer_type[]" value="<?= $value ?>" 
                                           <?= in_array($value, $selectedTypes) ? 'checked' : '' ?>
                                           onchange="updateTransferLabel()">
                                    <span><?= $label ?></span>
                                </label>
                                <?php endforeach; ?>
                                <div class="dropdown-divider"></div>
                                <button type="button" class="btn btn-sm btn-link text-decoration-none w-100" onclick="clearTransferSelection()">
                                    <i class="fas fa-times-circle me-1"></i>Xóa tất cả
                                </button>
                            </div>
                        </div>
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
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-primary"><?= count($revenue_reports) ?> giao dịch</span>
                    <?php if ($userRole === 'admin' && !empty($revenue_reports)): ?>
                    <button type="button" class="btn btn-sm btn-danger" id="deleteSelectedBtn" style="display: none;" onclick="deleteSelected()">
                        <i class="fas fa-trash me-1"></i>Xóa đã chọn (<span id="selectedCount">0</span>)
                    </button>
                    <?php endif; ?>
                </div>
            </div>
            
            <?php if (!empty($revenue_reports)): ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <?php if ($userRole === 'admin'): ?>
                                <th width="50">
                                    <input type="checkbox" class="form-check-input" id="selectAll" onchange="toggleSelectAll(this)">
                                </th>
                                <?php endif; ?>
                                <th>Ngày thanh toán</th>
                                <th>Học viên</th>
                                <th>Khóa học</th>
                                <th>Số tiền</th>
                                <th>Hình thức</th>
                                <th>Loại thanh toán</th>
                                <th>Mã biên lai</th>
                                <th>Nhân viên</th>
                                <th>Hình ảnh</th>
                                <?php if ($userRole === 'admin'): ?>
                                <th class="text-end">Hành động</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($revenue_reports as $report): ?>
                                <tr>
                                    <?php if ($userRole === 'admin'): ?>
                                    <td>
                                        <input type="checkbox" class="form-check-input revenue-checkbox" value="<?= $report['id'] ?>" onchange="updateSelectedCount()">
                                    </td>
                                    <?php endif; ?>
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
                                            <button type="button" 
                                                    class="badge bg-info border-0" 
                                                    data-fullname="<?= htmlspecialchars($courseName) ?>"
                                                    onclick="showCourseName(this.dataset.fullname)"
                                                    style="cursor: pointer; max-width: 220px; white-space: normal; text-align: left;">
                                                <?= htmlspecialchars($displayName) ?>
                                            </button>
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
                                            'account_thay_hien' => ['label' => 'TK Thầy Hiến', 'color' => 'info', 'icon' => 'fas fa-university'],
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
                                        <?php 
                                        // Get all images from confirmation_images JSON column
                                        $allImages = [];
                                        if (!empty($report['confirmation_images'])) {
                                            $decoded = json_decode($report['confirmation_images'], true);
                                            if (is_array($decoded)) {
                                                $allImages = array_values(array_filter($decoded));
                                            }
                                        }
                                        // Fallback to old single image column
                                        if (empty($allImages) && !empty($report['confirmation_image'])) {
                                            $allImages[] = $report['confirmation_image'];
                                        }
                                        // Legacy support: parse additional filenames stored in notes
                                        if (!empty($report['notes']) && stripos($report['notes'], 'Additional images:') !== false) {
                                            if (preg_match('/Additional images:\s*(.+?)(?:\||$)/i', $report['notes'], $matches)) {
                                                $extraImages = array_map('trim', explode(',', $matches[1]));
                                                foreach ($extraImages as $imageName) {
                                                    if ($imageName !== '' && !in_array($imageName, $allImages, true)) {
                                                        $allImages[] = $imageName;
                                                    }
                                                }
                                            }
                                        }
                                        
                                        if (!empty($allImages)): 
                                            $imageCount = count($allImages);
                                            $imageData = json_encode(array_map(function($img) {
                                                return '/Quan_ly_trung_tam/public/uploads/' . $img;
                                            }, $allImages));
                                        ?>
                                            <button type="button" 
                                                    class="btn btn-sm btn-outline-info position-relative" 
                                                    onclick='showMultipleImages(<?= $imageData ?>)'
                                                    title="Xem <?= $imageCount ?> ảnh">
                                                <i class="fas fa-images"></i>
                                                <?php if ($imageCount > 1): ?>
                                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" 
                                                          style="font-size: 0.65rem; padding: 0.2rem 0.4rem;">
                                                        <?= $imageCount ?>
                                                    </span>
                                                <?php endif; ?>
                                            </button>
                                        <?php else: ?>
                                            <span class="text-muted">Không có</span>
                                        <?php endif; ?>
                                    </td>
                                    <?php if ($userRole === 'admin'): ?>
                                    <td class="text-end">
                                        <div class="d-inline-flex align-items-center justify-content-end gap-2" style="white-space: nowrap;">
                                            <a href="/Quan_ly_trung_tam/public/revenue/<?= $report['id'] ?>/edit" class="btn btn-sm btn-outline-primary" title="Chỉnh sửa">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-outline-danger" title="Xóa" onclick="deleteRevenue(<?= $report['id'] ?>)">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                    <?php endif; ?>
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

<!-- Multiple Images Preview Modal -->
<div class="modal fade" id="imageModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-images text-primary me-2"></i>Ảnh xác nhận chuyển khoản
                    <span id="imageCounter" class="badge bg-primary ms-2"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center p-0 position-relative" style="min-height: 400px;">
                <!-- Navigation Buttons -->
                <button type="button" id="prevImageBtn" 
                        class="btn btn-dark position-absolute top-50 start-0 translate-middle-y ms-3" 
                        style="z-index: 10; opacity: 0.7;"
                        onclick="navigateImage(-1)">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <button type="button" id="nextImageBtn" 
                        class="btn btn-dark position-absolute top-50 end-0 translate-middle-y me-3" 
                        style="z-index: 10; opacity: 0.7;"
                        onclick="navigateImage(1)">
                    <i class="fas fa-chevron-right"></i>
                </button>
                
                <!-- Main Image -->
                <img id="modalImage" src="" alt="Confirmation Image" 
                     class="img-fluid" style="max-height: 70vh; width: auto;">
                
                <!-- Thumbnails -->
                <div id="thumbnailsContainer" class="d-flex justify-content-center gap-2 p-3 bg-light" 
                     style="overflow-x: auto;"></div>
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

<!-- Course Name Modal -->
<div class="modal fade" id="courseNameModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-book-open text-primary me-2"></i>Tên khóa học</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="courseNameFull" class="fs-6"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="fas fa-times me-2"></i>Đóng</button>
            </div>
        </div>
    </div>
    </div>

<script>
let currentImages = [];
let currentImageIndex = 0;

function showMultipleImages(images) {
    currentImages = Array.isArray(images) ? images : [images];
    currentImageIndex = 0;
    
    const modal = new bootstrap.Modal(document.getElementById('imageModal'));
    
    // Update counter
    const counter = document.getElementById('imageCounter');
    if (currentImages.length > 1) {
        counter.textContent = `1/${currentImages.length}`;
    } else {
        counter.textContent = '';
    }
    
    // Create thumbnails
    createThumbnails();
    
    // Show first image
    displayImage(0);
    
    // Show/hide navigation buttons
    updateNavigationButtons();
    
    modal.show();
}

function createThumbnails() {
    const container = document.getElementById('thumbnailsContainer');
    container.innerHTML = '';
    
    if (currentImages.length <= 1) {
        container.style.display = 'none';
        return;
    }
    
    container.style.display = 'flex';
    
    currentImages.forEach((imagePath, index) => {
        const thumb = document.createElement('img');
        thumb.src = imagePath;
        thumb.className = 'border rounded';
        thumb.style.cssText = 'width: 80px; height: 80px; object-fit: cover; cursor: pointer; transition: all 0.2s;';
        thumb.onclick = () => displayImage(index);
        thumb.dataset.index = index;
        
        if (index === 0) {
            thumb.classList.add('border-primary');
            thumb.style.borderWidth = '3px';
        }
        
        container.appendChild(thumb);
    });
}

function displayImage(index) {
    if (index < 0 || index >= currentImages.length) return;
    
    currentImageIndex = index;
    const modalImage = document.getElementById('modalImage');
    const downloadBtn = document.getElementById('downloadImageBtn');
    const counter = document.getElementById('imageCounter');
    
    // Update main image
    modalImage.src = currentImages[index];
    downloadBtn.href = currentImages[index];
    
    // Update counter
    if (currentImages.length > 1) {
        counter.textContent = `${index + 1}/${currentImages.length}`;
    }
    
    // Update thumbnails
    const thumbnails = document.querySelectorAll('#thumbnailsContainer img');
    thumbnails.forEach((thumb, i) => {
        if (i === index) {
            thumb.classList.add('border-primary');
            thumb.style.borderWidth = '3px';
        } else {
            thumb.classList.remove('border-primary');
            thumb.style.borderWidth = '1px';
        }
    });
    
    updateNavigationButtons();
}

function navigateImage(direction) {
    const newIndex = currentImageIndex + direction;
    if (newIndex >= 0 && newIndex < currentImages.length) {
        displayImage(newIndex);
    }
}

function updateNavigationButtons() {
    const prevBtn = document.getElementById('prevImageBtn');
    const nextBtn = document.getElementById('nextImageBtn');
    
    if (currentImages.length <= 1) {
        prevBtn.style.display = 'none';
        nextBtn.style.display = 'none';
        return;
    }
    
    prevBtn.style.display = currentImageIndex > 0 ? 'block' : 'none';
    nextBtn.style.display = currentImageIndex < currentImages.length - 1 ? 'block' : 'none';
}

// Keyboard navigation
document.addEventListener('keydown', function(e) {
    const modal = document.getElementById('imageModal');
    if (modal.classList.contains('show')) {
        if (e.key === 'ArrowLeft') {
            navigateImage(-1);
        } else if (e.key === 'ArrowRight') {
            navigateImage(1);
        }
    }
});

// Legacy function for single image (backward compatibility)
function showImageModal(imagePath) {
    showMultipleImages([imagePath]);
}

function showCourseName(fullName) {
    const nameEl = document.getElementById('courseNameFull');
    if (nameEl) {
        nameEl.textContent = fullName;
    }
    const modal = new bootstrap.Modal(document.getElementById('courseNameModal'));
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

// Chọn tất cả checkbox
function toggleSelectAll(checkbox) {
    const checkboxes = document.querySelectorAll('.revenue-checkbox');
    checkboxes.forEach(cb => {
        cb.checked = checkbox.checked;
    });
    updateSelectedCount();
}

// Cập nhật số lượng đã chọn
function updateSelectedCount() {
    const checkboxes = document.querySelectorAll('.revenue-checkbox:checked');
    const count = checkboxes.length;
    const deleteBtn = document.getElementById('deleteSelectedBtn');
    const selectedCountSpan = document.getElementById('selectedCount');
    
    if (selectedCountSpan) {
        selectedCountSpan.textContent = count;
    }
    
    if (deleteBtn) {
        if (count > 0) {
            deleteBtn.style.display = 'inline-block';
        } else {
            deleteBtn.style.display = 'none';
        }
    }
    
    // Cập nhật trạng thái checkbox "Chọn tất cả"
    const selectAllCheckbox = document.getElementById('selectAll');
    const allCheckboxes = document.querySelectorAll('.revenue-checkbox');
    if (selectAllCheckbox && allCheckboxes.length > 0) {
        selectAllCheckbox.checked = checkboxes.length === allCheckboxes.length;
    }
}

// Xóa các giao dịch đã chọn
function deleteSelected() {
    const checkboxes = document.querySelectorAll('.revenue-checkbox:checked');
    
    if (checkboxes.length === 0) {
        alert('Vui lòng chọn ít nhất một giao dịch để xóa');
        return;
    }
    
    const ids = Array.from(checkboxes).map(cb => cb.value);
    const count = ids.length;
    
    if (confirm(`Bạn có chắc chắn muốn xóa ${count} giao dịch đã chọn?\n\nHành động này không thể hoàn tác!`)) {
        // Hiển thị loading
        const deleteBtn = document.getElementById('deleteSelectedBtn');
        const originalHtml = deleteBtn.innerHTML;
        deleteBtn.disabled = true;
        deleteBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Đang xóa...';
        
        fetch('/Quan_ly_trung_tam/public/revenue/delete-multiple', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ ids: ids })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message || `Đã xóa thành công ${count} giao dịch`);
                location.reload();
            } else {
                alert('Lỗi: ' + (data.message || 'Không thể xóa giao dịch'));
                deleteBtn.disabled = false;
                deleteBtn.innerHTML = originalHtml;
            }
        })
        .catch(error => {
            alert('Lỗi kết nối: ' + error.message);
            deleteBtn.disabled = false;
            deleteBtn.innerHTML = originalHtml;
        });
    }
}
</script>

<?php
$content = ob_get_clean();

// Render layout
useModernLayout('Báo cáo doanh thu', $content);
?>