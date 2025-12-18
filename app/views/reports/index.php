<?php
require_once __DIR__ . '/../layouts/main.php';

// Page content
ob_start();
?>

<?php
$headerButton = '';
$headerTitle = 'Báo cáo đến trung tâm';
$headerDesc = 'Quản lý và theo dõi số lượng khách hàng đến trung tâm';

if (isset($userRole)) {
    if ($userRole === 'staff') {
        $headerTitle = 'Báo cáo công việc của tôi';
        $headerDesc = 'Xem lại các báo cáo bạn đã thực hiện trong ngày';
        $headerButton = '
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-outline-warning" onclick="createEmptyReport()">
                    <i class="fas fa-file-alt me-2"></i>Báo cáo rỗng
                </button>
                <a href="/Quan_ly_trung_tam/public/reports/create" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Tạo báo cáo mới
                </a>
            </div>';
    } elseif ($userRole === 'admin') {
        $headerButton = '
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-outline-warning" onclick="createEmptyReport()">
                    <i class="fas fa-file-alt me-2"></i>Báo cáo rỗng
                </button>
                <a href="/Quan_ly_trung_tam/public/reports/create" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Tạo báo cáo mới
                </a>
            </div>';
    }
}
?>

<?= pageHeader(
    $headerTitle, 
    $headerDesc, 
    $headerButton
) ?>

<div class="p-3">
    <!-- Filter and Search - Chỉ hiển thị cho Admin -->
    <?php if (isset($userRole) && $userRole === 'admin'): ?>
    <div class="stats-card mb-4">
        <div class="card-body">
            <form method="GET" action="/Quan_ly_trung_tam/public/reports">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label">
                            <i class="fas fa-calendar text-primary"></i> Từ ngày
                        </label>
                        <input type="date" class="form-control" name="from_date" 
                               value="<?= $_GET['from_date'] ?? '' ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">
                            <i class="fas fa-calendar text-primary"></i> Đến ngày
                        </label>
                        <input type="date" class="form-control" name="to_date" 
                               value="<?= $_GET['to_date'] ?? '' ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">
                            <i class="fas fa-user text-primary"></i> Nhân viên
                        </label>
                        <select class="form-select" name="staff_id">
                            <option value="">Tất cả nhân viên</option>
                            <?php if (!empty($staff)): ?>
                                <?php foreach ($staff as $s): ?>
                                    <option value="<?= $s['id'] ?>" 
                                            <?= (isset($_GET['staff_id']) && $_GET['staff_id'] == $s['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($s['full_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search me-2"></i>Tìm kiếm
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <!-- Summary Statistics - Chỉ hiển thị cho Admin -->
    <?php if (isset($userRole) && $userRole === 'admin'): ?>
    <div class="row g-3 mb-4">
        <div class="col-md-3 col-sm-6">
            <?= statsCard(
                'fas fa-calendar-day', 
                'Tổng báo cáo', 
                count($reports), 
                'Số báo cáo trong khoảng thời gian',
                'primary'
            ) ?>
        </div>
        
        <div class="col-md-3 col-sm-6">
            <?php 
            $total_visitors = array_sum(array_column($reports, 'total_visitors'));
            ?>
            <?= statsCard(
                'fas fa-users', 
                'Tổng khách đến', 
                $total_visitors, 
                'Tổng số lượng khách hàng',
                'info'
            ) ?>
        </div>
        
        <div class="col-md-3 col-sm-6">
            <?php 
            $total_registered = array_sum(array_column($reports, 'total_registered'));
            ?>
            <?= statsCard(
                'fas fa-check-circle', 
                'Tổng khách chốt', 
                $total_registered, 
                'Số khách hàng đã chốt',
                'success'
            ) ?>
        </div>
        
        <div class="col-md-3 col-sm-6">
            <?php 
            $conversion_rate = $total_visitors > 0 ? ($total_registered / $total_visitors * 100) : 0;
            $rate_color = $conversion_rate >= 50 ? 'success' : ($conversion_rate >= 20 ? 'warning' : 'danger');
            ?>
            <?= statsCard(
                'fas fa-percentage', 
                'Tỷ lệ chốt', 
                number_format($conversion_rate, 1) . '%', 
                'Tỷ lệ chốt trung bình',
                $rate_color
            ) ?>
        </div>
    </div>
    <?php else: ?>
    <!-- Thông tin cho Nhân viên -->
    <div class="stats-card mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="card-title mb-0">
                    <i class="fas fa-calendar-day text-primary me-2"></i>
                    Báo cáo của bạn hôm nay (<?= date('d/m/Y') ?>)
                </h6>
                <span class="badge bg-info"><?= count($reports) ?> báo cáo</span>
            </div>
            
            <?php if (count($reports) > 0): ?>
                <div class="row g-3">
                    <div class="col-md-4">
                        <?php 
                        $total_visitors = array_sum(array_column($reports, 'total_visitors'));
                        ?>
                        <?= statsCard(
                            'fas fa-users', 
                            'Khách đến', 
                            $total_visitors, 
                            'Tổng khách bạn đã báo cáo',
                            'info'
                        ) ?>
                    </div>
                    <div class="col-md-4">
                        <?php 
                        $total_registered = array_sum(array_column($reports, 'total_registered'));
                        ?>
                        <?= statsCard(
                            'fas fa-check-circle', 
                            'Khách chốt', 
                            $total_registered, 
                            'Tổng khách đã chốt',
                            'success'
                        ) ?>
                    </div>
                    <div class="col-md-4">
                        <?php 
                        $conversion_rate = $total_visitors > 0 ? ($total_registered / $total_visitors * 100) : 0;
                        $rate_color = $conversion_rate >= 50 ? 'success' : ($conversion_rate >= 20 ? 'warning' : 'danger');
                        ?>
                        <?= statsCard(
                            'fas fa-percentage', 
                            'Tỷ lệ chốt của bạn', 
                            number_format($conversion_rate, 1) . '%', 
                            'Hiệu quả làm việc hôm nay',
                            $rate_color
                        ) ?>
                    </div>
                </div>
            <?php else: ?>
                <div class="text-center py-4">
                    <i class="fas fa-calendar-check text-muted fa-3x mb-3"></i>
                    <h6 class="text-muted">Bạn chưa có báo cáo nào hôm nay</h6>
                    <p class="text-muted mb-3">Tạo báo cáo đầu tiên để ghi nhận công việc của bạn</p>
                    <a href="/Quan_ly_trung_tam/public/reports/create" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Tạo báo cáo ngay
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Reports Table -->
    <div class="stats-card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="card-title mb-0">
                    <i class="fas fa-list text-primary me-2"></i>
                    <?php if (isset($userRole) && $userRole === 'staff'): ?>
                        Chi tiết báo cáo hôm nay
                    <?php else: ?>
                        Danh sách báo cáo
                    <?php endif; ?>
                </h6>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-primary"><?= count($reports) ?> báo cáo</span>
                    <?php if (isset($userRole) && $userRole === 'admin' && !empty($reports)): ?>
                    <button type="button" class="btn btn-sm btn-danger" id="deleteSelectedBtn" style="display: none;" onclick="deleteSelected()">
                        <i class="fas fa-trash me-1"></i>Xóa đã chọn (<span id="selectedCount">0</span>)
                    </button>
                    <?php endif; ?>
                </div>
            </div>
            
            <?php if (!empty($reports)): ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <?php if (isset($userRole) && $userRole === 'admin'): ?>
                                <th width="50">
                                    <input type="checkbox" class="form-check-input" id="selectAll" onchange="toggleSelectAll(this)">
                                </th>
                                <?php endif; ?>
                                <th>Ngày báo cáo</th>
                                <?php if (isset($userRole) && $userRole === 'admin'): ?>
                                <th>Nhân viên</th>
                                <?php endif; ?>
                                <th>Số lượng đến</th>
                                <th>Số lượng chốt</th>
                                <?php if (isset($userRole) && $userRole === 'admin'): ?>
                                <th>Tỷ lệ chốt</th>
                                <?php endif; ?>
                                <th>Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($reports as $report): ?>
                                <tr>
                                    <?php if (isset($userRole) && $userRole === 'admin'): ?>
                                    <td>
                                        <input type="checkbox" class="form-check-input report-checkbox" value="<?= $report['id'] ?>" onchange="updateSelectedCount()">
                                    </td>
                                    <?php endif; ?>
                                    <td>
                                        <div>
                                            <span class="fw-semibold"><?= date('d/m/Y', strtotime($report['report_date'])) ?></span>
                                            <br><small class="text-muted"><?= date('H:i', strtotime($report['report_time'])) ?></small>
                                        </div>
                                    </td>
                                    <?php if (isset($userRole) && $userRole === 'admin'): ?>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-user-circle text-muted me-2"></i>
                                            <?= htmlspecialchars($report['staff_name'] ?? 'N/A') ?>
                                        </div>
                                    </td>
                                    <?php endif; ?>
                                    <td>
                                        <span class="badge bg-info fs-6"><?= $report['total_visitors'] ?></span>
                                    </td>
                                    <td>
                                        <span class="badge bg-success fs-6"><?= $report['total_registered'] ?></span>
                                    </td>
                                    <?php if (isset($userRole) && $userRole === 'admin'): ?>
                                    <td>
                                        <?php 
                                        $rate = $report['total_visitors'] > 0 ? 
                                            ($report['total_registered'] / $report['total_visitors'] * 100) : 0;
                                        $color = $rate >= 50 ? 'success' : ($rate >= 20 ? 'warning' : 'danger');
                                        ?>
                                        <span class="badge bg-<?= $color ?> fs-6"><?= number_format($rate, 1) ?>%</span>
                                    </td>
                                    <?php endif; ?>
                                    <td>
                                        <?php if (isset($userRole) && $userRole === 'admin'): ?>
                                        <div class="btn-group" role="group">
                                            <button type="button" class="btn btn-sm btn-outline-primary" 
                                                    onclick="viewReportDetails(<?= $report['id'] ?>)" title="Xem chi tiết">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <a href="/Quan_ly_trung_tam/public/reports/<?= $report['id'] ?>/edit" 
                                               class="btn btn-sm btn-outline-warning" title="Chỉnh sửa">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-outline-danger" 
                                                    onclick="deleteReport(<?= $report['id'] ?>)" title="Xóa">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                        <?php else: ?>
                                        <!-- Nhân viên chỉ xem chi tiết -->
                                        <button type="button" class="btn btn-sm btn-outline-primary" 
                                                onclick="viewReportDetails(<?= $report['id'] ?>)" title="Xem chi tiết">
                                            <i class="fas fa-eye me-1"></i>Xem
                                        </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <?php if (isset($totalPages) && $totalPages > 1): ?>
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
                
                <?php if (count($reports) === 0): ?>
                    <div class="text-center py-4">
                        <i class="fas fa-search text-muted fa-3x mb-3"></i>
                        <p class="text-muted">Không tìm thấy báo cáo nào phù hợp với điều kiện tìm kiếm</p>
                        <a href="/Quan_ly_trung_tam/public/reports" class="btn btn-outline-primary">
                            <i class="fas fa-refresh me-2"></i>Xem tất cả
                        </a>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-chart-line text-muted fa-4x mb-3"></i>
                    <h5 class="text-muted">Chưa có báo cáo nào</h5>
                    <p class="text-muted">Bắt đầu tạo báo cáo đầu tiên để theo dõi số lượng khách hàng đến trung tâm</p>
                    <a href="/Quan_ly_trung_tam/public/reports/create" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Tạo báo cáo đầu tiên
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal chi tiết báo cáo -->
<div class="modal fade" id="reportDetailModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-list-alt text-primary me-2"></i>
                    Chi tiết báo cáo
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="reportDetailContent">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Đang tải...</span>
                    </div>
                    <p class="mt-2 text-muted">Đang tải thông tin...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Đóng
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Toast Container -->
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 11000;">
    <div id="successToast" class="toast align-items-center text-white bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body">
                <i class="fas fa-check-circle me-2"></i>
                <span id="successToastMessage"></span>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
    <div id="errorToast" class="toast align-items-center text-white bg-danger border-0" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body">
                <i class="fas fa-exclamation-circle me-2"></i>
                <span id="errorToastMessage"></span>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
</div>

<!-- Modal xác nhận báo cáo rỗng -->
<div class="modal fade" id="emptyReportModal" tabindex="-1" aria-labelledby="emptyReportModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title" id="emptyReportModalLabel">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Xác nhận báo cáo rỗng
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-3">
                    <i class="fas fa-file-alt text-warning" style="font-size: 3rem;"></i>
                </div>
                <p class="text-center mb-3">
                    Bạn có chắc chắn muốn tạo <strong>báo cáo rỗng</strong> cho ngày hôm nay?
                </p>
                <div class="alert alert-info">
                    <h6><i class="fas fa-info-circle me-2"></i>Thông tin báo cáo sẽ được tạo:</h6>
                    <ul class="mb-0">
                        <li><strong>Ngày báo cáo:</strong> <?= date('d/m/Y') ?></li>
                        <li><strong>Số lượng đến:</strong> 0 người</li>
                        <li><strong>Số lượng chốt:</strong> 0 khách hàng</li>
                        <li><strong>Chi tiết khách hàng:</strong> Không có</li>
                        <li><strong>Ghi chú:</strong> Báo cáo rỗng - Không có khách hàng đến</li>
                    </ul>
                </div>
                <p class="text-muted small">
                    <i class="fas fa-lightbulb me-1"></i>
                    Báo cáo này sẽ được lưu vào cơ sở dữ liệu để đảm bảo tính liên tục của dữ liệu.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Hủy bỏ
                </button>
                <button type="button" class="btn btn-warning" onclick="confirmEmptyReport()" data-bs-dismiss="modal">
                    <i class="fas fa-check me-2"></i>Xác nhận tạo báo cáo rỗng
                </button>
            </div>
        </div>
    </div>
</div>

<script>
<?php
// Lấy dữ liệu user để truyền vào JavaScript
$jsUserRole = isset($userRole) ? htmlspecialchars($userRole, ENT_QUOTES) : 'staff';
$jsUserId = isset($user) ? htmlspecialchars($user["id"], ENT_QUOTES) : '1';

// Xử lý thông báo
?>

// Toast notifications are handled by modern.php layout

function createEmptyReport() {
    const modal = new bootstrap.Modal(document.getElementById('emptyReportModal'));
    modal.show();
}

function confirmEmptyReport() {
    const userRole = '<?php echo $jsUserRole; ?>';
    const userId = '<?php echo $jsUserId; ?>';
    const staffName = '<?php echo isset($user["full_name"]) ? addslashes($user["full_name"]) : "Nhân viên"; ?>';
    const today = '<?php echo date('Y-m-d'); ?>';
    const todayFormatted = '<?php echo date('d/m/Y'); ?>'; // Định dạng dd/mm/yyyy
    const currentTime = '<?php echo date('H:i:s'); ?>';
    
    const noteText = `Báo cáo rỗng - ${todayFormatted} Không có khách hàng đến trung tâm trong ca dạy của nhân viên ${staffName}.`;
    
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '/Quan_ly_trung_tam/public/reports';
    
    const inputs = [
        {name: 'report_date', value: today},
        {name: 'report_time', value: currentTime},
        {name: 'staff_id', value: userId},
        {name: 'total_visitors', value: '0'},
        {name: 'total_registered', value: '0'},
        {name: 'empty_report', value: '1'},
        {name: 'notes', value: noteText}
    ];
    
    inputs.forEach(input => {
        const hiddenInput = document.createElement('input');
        hiddenInput.type = 'hidden';
        hiddenInput.name = input.name;
        hiddenInput.value = input.value;
        form.appendChild(hiddenInput);
    });
    
    console.log('Submitting empty report with data:', inputs);
    
    document.body.appendChild(form);
    form.submit();
}

function deleteReport(reportId) {
    if (confirm('Bạn có chắc chắn muốn xóa báo cáo này?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '/Quan_ly_trung_tam/public/reports/' + reportId + '/delete';
        
        document.body.appendChild(form);
        form.submit();
    }
}

function viewReportDetails(reportId) {
    const modal = new bootstrap.Modal(document.getElementById('reportDetailModal'));
    modal.show();
    
    document.getElementById('reportDetailContent').innerHTML = `
        <div class="text-center py-4">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Đang tải...</span>
            </div>
            <p class="mt-2 text-muted">Đang tải thông tin...</p>
        </div>
    `;
    
    fetch(`/Quan_ly_trung_tam/public/api/reports/${reportId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                renderReportDetails(data.data.report, data.data.customers);
            } else {
                showError(data.message || 'Không thể tải thông tin báo cáo');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showError('Đã xảy ra lỗi khi tải thông tin');
        });
}

function renderReportDetails(report, customers) {
    const detailContent = `
        <div class="row g-3">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h6 class="card-subtitle mb-3 text-muted">
                            <i class="fas fa-info-circle me-2"></i>Thông tin chung
                        </h6>
                        <table class="table table-sm">
                            <tr>
                                <th width="40%">Mã báo cáo:</th>
                                <td>${report.id}</td>
                            </tr>
                            <tr>
                                <th>Ngày báo cáo:</th>
                                <td>${formatDate(report.report_date)}</td>
                            </tr>
                            <tr>
                                <th>Giờ báo cáo:</th>
                                <td>${formatTime(report.report_time)}</td>
                            </tr>
                            <tr>
                                <th>Nhân viên:</th>
                                <td>${report.staff_name || 'Không xác định'}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h6 class="card-subtitle mb-3 text-muted">
                            <i class="fas fa-chart-bar me-2"></i>Thống kê
                        </h6>
                        <table class="table table-sm">
                            <tr>
                                <th width="40%">Tổng khách đến:</th>
                                <td><span class="badge bg-primary">${report.total_visitors}</span></td>
                            </tr>
                            <tr>
                                <th>Đã đăng ký:</th>
                                <td><span class="badge bg-success">${report.total_registered}</span></td>
                            </tr>
                            <tr>
                                <th>Chưa đăng ký:</th>
                                <td><span class="badge bg-warning">${report.total_visitors - report.total_registered}</span></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
            ${
                report.notes ? `
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h6 class="card-subtitle mb-3 text-muted">
                                <i class="fas fa-sticky-note me-2"></i>Ghi chú
                            </h6>
                            <p class="mb-0">${report.notes}</p>
                        </div>
                    </div>
                </div>
                ` : ''
            }
            ${
                customers && customers.length > 0 ? `
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h6 class="card-subtitle mb-3 text-muted">
                                <i class="fas fa-users me-2"></i>Danh sách khách hàng (${customers.length})
                            </h6>
                            <div class="table-responsive">
                                <table class="table table-sm table-hover">
                                    <thead>
                                        <tr>
                                            <th width="5%">STT</th>
                                            <th width="20%">Tên khách hàng</th>
                                            <th width="15%">Số điện thoại</th>
                                            <th width="25%">Khóa học</th>
                                            <th width="15%">Hình thức thanh toán</th>
                                            <th width="20%">Ghi chú</th>
                                            <th width="15%">Trạng thái</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        ${customers.map((customer, index) => {
                                            // Thu gọn tên khóa học nếu quá dài
                                            let courseName = customer.course_name || 'Chưa chọn';
                                            let courseTitle = courseName;
                                            if (courseName.length > 30) {
                                                courseName = courseName.substring(0, 30) + '...';
                                            }
                                            
                                            // Định dạng hình thức thanh toán
                                            let paymentMethod = 'N/A';
                                            let paymentBadge = 'secondary';
                                            if (customer.payment_method) {
                                                switch(customer.payment_method) {
                                                    case 'cash':
                                                        paymentMethod = 'Tiền mặt';
                                                        paymentBadge = 'success';
                                                        break;
                                                    case 'transfer':
                                                        paymentMethod = 'Chuyển khoản';
                                                        paymentBadge = 'primary';
                                                        break;
                                                    case 'card':
                                                        paymentMethod = 'Thẻ';
                                                        paymentBadge = 'info';
                                                        break;
                                                    default:
                                                        paymentMethod = customer.payment_method;
                                                }
                                            }
                                            
                                            return `
                                            <tr>
                                                <td>${index + 1}</td>
                                                <td>${customer.customer_name || 'N/A'}</td>
                                                <td>${customer.customer_phone || 'N/A'}</td>
                                                <td>
                                                    <span class="text-truncate d-inline-block" 
                                                          style="max-width: 250px;" 
                                                          title="${courseTitle}">
                                                        ${courseName}
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge bg-${paymentBadge}">${paymentMethod}</span>
                                                </td>
                                                <td>
                                                    ${(() => {
                                                        const note = (customer.notes || '').trim();
                                                        if (!note) {
                                                            return '<span class="text-muted">-</span>';
                                                        }
                                                        const truncated = note.length > 60 ? note.substring(0, 60) + '...' : note;
                                                        return `<span class="text-truncate d-inline-block" style="max-width: 240px;" title="${escapeHtml(note, true)}">${escapeHtml(truncated)}</span>`;
                                                    })()}
                                                </td>
                                                <td>
                                                    ${customer.registered === 'registered' || customer.registration_status === 'registered' ? 
                                                        '<span class="badge bg-success"><i class="fas fa-check me-1"></i>Đã chốt</span>' : 
                                                        '<span class="badge bg-secondary"><i class="fas fa-clock me-1"></i>Chưa chốt</span>'}
                                                </td>
                                            </tr>
                                        `;
                                        }).join('')}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                ` : ''
            }
        </div>
    `;
    
    document.getElementById('reportDetailContent').innerHTML = detailContent;
}

function showError(message) {
    document.getElementById('reportDetailContent').innerHTML = `
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-triangle me-2"></i>${message}
        </div>
    `;
}

// Chọn tất cả checkbox
function toggleSelectAll(checkbox) {
    const checkboxes = document.querySelectorAll('.report-checkbox');
    checkboxes.forEach(cb => {
        cb.checked = checkbox.checked;
    });
    updateSelectedCount();
}

// Cập nhật số lượng đã chọn
function updateSelectedCount() {
    const checkboxes = document.querySelectorAll('.report-checkbox:checked');
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
    const allCheckboxes = document.querySelectorAll('.report-checkbox');
    if (selectAllCheckbox && allCheckboxes.length > 0) {
        selectAllCheckbox.checked = checkboxes.length === allCheckboxes.length;
    }
}

// Xóa các báo cáo đã chọn
function deleteSelected() {
    const checkboxes = document.querySelectorAll('.report-checkbox:checked');
    
    if (checkboxes.length === 0) {
        alert('Vui lòng chọn ít nhất một báo cáo để xóa');
        return;
    }
    
    const ids = Array.from(checkboxes).map(cb => cb.value);
    const count = ids.length;
    
    if (confirm(`Bạn có chắc chắn muốn xóa ${count} báo cáo đã chọn?\n\nHành động này không thể hoàn tác!`)) {
        // Hiển thị loading
        const deleteBtn = document.getElementById('deleteSelectedBtn');
        const originalHtml = deleteBtn.innerHTML;
        deleteBtn.disabled = true;
        deleteBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Đang xóa...';
        
        fetch('/Quan_ly_trung_tam/public/reports/delete-multiple', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ ids: ids })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message || `Đã xóa thành công ${count} báo cáo`);
                location.reload();
            } else {
                alert('Lỗi: ' + (data.message || 'Không thể xóa báo cáo'));
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

function escapeHtml(text, forAttribute = false) {
    if (text === undefined || text === null) {
        return '';
    }
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#39;'
    };
    let escaped = String(text).replace(/[&<>"']/g, (char) => map[char]);
    if (forAttribute) {
        escaped = escaped.replace(/\r?\n/g, '&#10;');
    }
    return escaped;
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('vi-VN');
}

function formatTime(timeString) {
    return timeString.substring(0, 5);
}
</script>

<?php
$content = ob_get_clean();

// Render layout with modern UI
useModernLayout('Báo cáo đến trung tâm', $content);
?>
