<?php
$pageTitle = 'Quản lý ca dạy';

$scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
$scriptDir = str_replace('\\', '/', dirname($scriptName));
$basePath = rtrim($scriptDir, '/');
if ($basePath === '' || $basePath === '.') {
    $basePath = '';
}

$statusOptions = [
    '' => 'Tất cả trạng thái',
    'pending' => 'Chờ duyệt',
    'approved' => 'Đã duyệt',
    'rejected' => 'Từ chối',
    'cancelled' => 'Đã hủy'
];

ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-1">
            <i class="fas fa-calendar-check me-2" style="color: #4361ee;"></i>
            Quản lý ca dạy nhân viên
        </h2>
        <p class="text-muted mb-0">Xem, lọc và duyệt ca đăng ký của toàn bộ nhân viên</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= $basePath ?>/teaching-shifts/manage" class="btn btn-outline-info">
            <i class="fas fa-layer-group me-2"></i>Quản lý loại ca
        </a>
        <a href="<?= $basePath ?>/teaching-shifts/admin/create" class="btn btn-success">
            <i class="fas fa-plus me-2"></i>Đăng ký ca dạy
        </a>
        <a href="<?= $basePath ?>/teaching-shifts/admin" class="btn btn-primary">
            <i class="fas fa-calendar-alt me-2"></i>Xem dạng lịch
        </a>
    </div>
</div>
?>

<div class="p-3">
    <div class="stats-card mb-4">
        <div class="card-body">
            <form class="row g-3" method="GET" action="<?= $basePath ?>/teaching-shifts/admin">
                <input type="hidden" name="view" value="list">
                <div class="col-md-3">
                    <label class="form-label">Trạng thái</label>
                    <select class="form-select" name="status" id="statusFilter">
                        <?php foreach ($statusOptions as $value => $label): ?>
                            <option value="<?= $value ?>" <?= ($filters['status'] ?? '') === $value ? 'selected' : '' ?>>
                                <?= $label ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Nhân viên</label>
                    <select class="form-select" name="staff_id">
                        <option value="">-- Tất cả --</option>
                        <?php foreach ($staffList as $staff): ?>
                            <option value="<?= $staff['id'] ?>" <?= ((int)($filters['staff_id'] ?? 0) === (int)$staff['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($staff['full_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Từ ngày</label>
                    <input type="date" class="form-control" name="date_from" value="<?= htmlspecialchars($filters['date_from'] ?? '') ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Đến ngày</label>
                    <input type="date" class="form-control" name="date_to" value="<?= htmlspecialchars($filters['date_to'] ?? '') ?>">
                </div>
                <div class="col-md-2 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary flex-fill">
                        <i class="fas fa-filter me-1"></i>Lọc
                    </button>
                    <a href="<?= $basePath ?>/teaching-shifts/admin?view=list" class="btn btn-outline-secondary flex-fill">
                        <i class="fas fa-undo me-1"></i>Đặt lại
                    </a>
                </div>
            </form>
            
            <!-- Quick Filter Buttons -->
            <div class="row g-2 mt-2">
                <div class="col-auto">
                    <button type="button" class="btn btn-sm <?= ($filters['status'] ?? '') === 'pending' ? 'btn-warning' : 'btn-outline-warning' ?>" onclick="quickFilter('pending')">
                        <i class="fas fa-clock me-1"></i>Chỉ ca chờ duyệt
                    </button>
                </div>
                <div class="col-auto">
                    <button type="button" class="btn btn-sm <?= ($filters['status'] ?? '') === 'approved' ? 'btn-success' : 'btn-outline-success' ?>" onclick="quickFilter('approved')">
                        <i class="fas fa-check me-1"></i>Chỉ ca đã duyệt
                    </button>
                </div>
                <div class="col-auto">
                    <button type="button" class="btn btn-sm <?= ($filters['status'] ?? '') === '' ? 'btn-primary' : 'btn-outline-primary' ?>" onclick="quickFilter('')">
                        <i class="fas fa-list me-1"></i>Tất cả
                    </button>
                </div>
            </div>
            
            <form class="d-none" method="GET" action="<?= $basePath ?>/teaching-shifts/admin" id="quickFilterForm">
                <input type="hidden" name="view" value="list">
                <input type="hidden" name="status" id="quickFilterStatus">
                <input type="hidden" name="staff_id" value="<?= htmlspecialchars($filters['staff_id'] ?? '') ?>">
                <input type="hidden" name="date_from" value="<?= htmlspecialchars($filters['date_from'] ?? '') ?>">
                <input type="hidden" name="date_to" value="<?= htmlspecialchars($filters['date_to'] ?? '') ?>">
            </form>
        </div>
    </div>

    <div class="stats-card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0"><i class="fas fa-users text-primary me-2"></i>Danh sách đăng ký</h5>
                <span class="badge bg-secondary">Tổng <?= $pagination['totalRecords'] ?? 0 ?> ca</span>
            </div>

            <?php if (!empty($registrations)): ?>
                <!-- Bulk Actions -->
                <div class="d-flex gap-2 mb-3">
                    <button type="button" class="btn btn-sm btn-success" onclick="bulkAction('approve')">
                        <i class="fas fa-check me-1"></i>Duyệt đã chọn
                    </button>
                    <button type="button" class="btn btn-sm btn-warning" onclick="bulkAction('reject')">
                        <i class="fas fa-times me-1"></i>Từ chối đã chọn
                    </button>
                    <button type="button" class="btn btn-sm btn-danger" onclick="bulkAction('delete')">
                        <i class="fas fa-trash me-1"></i>Xóa đã chọn
                    </button>
                    <span class="ms-auto text-muted small" id="selectedCount">Đã chọn: 0</span>
                </div>
            <?php endif; ?>

            <?php if (empty($registrations)): ?>
                <div class="alert alert-light border">Không có dữ liệu phù hợp.</div>
            <?php else: ?>
                <form id="bulkForm" method="POST" action="<?= $basePath ?>/teaching-shifts/bulk-action">
                    <input type="hidden" name="bulk_action" id="bulkActionInput">
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead>
                                <tr>
                                    <th style="width: 40px;">
                                        <input type="checkbox" id="selectAll" onchange="toggleSelectAll(this)">
                                    </th>
                                    <th>Ngày</th>
                                    <th>Nhân viên</th>
                                    <th>Ca / Khung giờ</th>
                                    <th>Số giờ</th>
                                    <th>Trạng thái</th>
                                    <th>Ghi chú</th>
                                    <th>Người duyệt</th>
                                    <th class="text-end">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($registrations as $registration): ?>
                                <?php
                                    $start = $registration['custom_start'] ?? $registration['preset_start'];
                                    $end = $registration['custom_end'] ?? $registration['preset_end'];
                                    $startDisplay = $start ? substr($start, 0, 5) : '--';
                                    $endDisplay = $end ? substr($end, 0, 5) : '--';
                                    $status = $registration['status'];
                                    $badgeClass = [
                                        'pending' => 'warning',
                                        'approved' => 'success',
                                        'rejected' => 'danger',
                                        'cancelled' => 'secondary'
                                    ][$status] ?? 'secondary';
                                ?>
                                <tr>
                                    <td>
                                        <input type="checkbox" name="selected_ids[]" value="<?= $registration['id'] ?>" class="row-checkbox" onchange="updateSelectedCount()">
                                    </td>
                                    <td><?= date('d/m/Y', strtotime($registration['shift_date'])) ?></td>
                                    <td><?= htmlspecialchars($registration['staff_name'] ?? 'Không rõ') ?></td>
                                    <td>
                                        <div class="fw-semibold">
                                            <?= $registration['shift_name'] ? htmlspecialchars($registration['shift_name']) : 'Ca chọn' ?>
                                        </div>
                                        <small class="text-muted"><?= $startDisplay ?> - <?= $endDisplay ?></small>
                                    </td>
                                    <td><?= number_format($registration['hours'], 2) ?>h</td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="badge bg-<?= $badgeClass ?> text-uppercase"><?= strtoupper($status) ?></span>
                                            <?php if ($status === 'pending'): ?>
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <button type="button" class="btn btn-sm btn-success" 
                                                            onclick="quickApproveOne(<?= $registration['id'] ?>)" 
                                                            title="Duyệt nhanh">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-danger" 
                                                            onclick="quickRejectOne(<?= $registration['id'] ?>)" 
                                                            title="Từ chối nhanh">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td><?= htmlspecialchars($registration['notes'] ?? '-') ?></td>
                                    <td>
                                        <?php if ($registration['approver_name']): ?>
                                            <div><?= htmlspecialchars($registration['approver_name']) ?></div>
                                            <small class="text-muted"><?= $registration['approved_at'] ? date('d/m H:i', strtotime($registration['approved_at'])) : '' ?></small>
                                        <?php else: ?>
                                            <span class="text-muted">--</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <?php if ($status === 'pending'): ?>
                                            <form method="POST" action="<?= $basePath ?>/teaching-shifts/<?= $registration['id'] ?>/status" class="d-inline">
                                                <input type="hidden" name="action" value="approve">
                                                <button type="submit" class="btn btn-sm btn-success" title="Duyệt">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            </form>
                                            <form method="POST" action="<?= $basePath ?>/teaching-shifts/<?= $registration['id'] ?>/status" class="d-inline ms-1">
                                                <input type="hidden" name="action" value="reject">
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Từ chối">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </form>
                                            <form method="POST" action="<?= $basePath ?>/teaching-shifts/<?= $registration['id'] ?>/delete" class="d-inline ms-1" onsubmit="return confirm('Xóa ca này vĩnh viễn?')">
                                                <button type="submit" class="btn btn-sm btn-outline-secondary" title="Xóa">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        <?php elseif ($status === 'approved'): ?>
                                            <form method="POST" action="<?= $basePath ?>/teaching-shifts/<?= $registration['id'] ?>/status" class="d-inline">
                                                <input type="hidden" name="status" value="pending">
                                                <button type="submit" class="btn btn-sm btn-warning" title="Chuyển về chờ duyệt" onclick="return confirm('Chuyển về chờ duyệt?')">
                                                    <i class="fas fa-undo"></i>
                                                </button>
                                            </form>
                                            <form method="POST" action="<?= $basePath ?>/teaching-shifts/<?= $registration['id'] ?>/delete" class="d-inline ms-1" onsubmit="return confirm('Xóa ca này vĩnh viễn?')">
                                                <button type="submit" class="btn btn-sm btn-outline-secondary" title="Xóa">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <form method="POST" action="<?= $basePath ?>/teaching-shifts/<?= $registration['id'] ?>/delete" class="d-inline" onsubmit="return confirm('Xóa ca này vĩnh viễn?')">
                                                <button type="submit" class="btn btn-sm btn-outline-secondary" title="Xóa">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                </form>

                <!-- Pagination -->
                <?php if ($pagination['totalPages'] > 1): ?>
                    <nav class="mt-4">
                        <ul class="pagination justify-content-center mb-0">
                            <?php
                            $currentPage = $pagination['currentPage'];
                            $totalPages = $pagination['totalPages'];
                            
                            // Build query string
                            $queryParams = $_GET;
                            unset($queryParams['page']);
                            $queryString = http_build_query($queryParams);
                            $queryString = $queryString ? '&' . $queryString : '';
                            ?>
                            
                            <!-- Previous -->
                            <li class="page-item <?= $currentPage <= 1 ? 'disabled' : '' ?>">
                                <a class="page-link" href="?page=<?= $currentPage - 1 ?><?= $queryString ?>">«</a>
                            </li>
                            
                            <!-- Pages -->
                            <?php
                            $start = max(1, $currentPage - 2);
                            $end = min($totalPages, $currentPage + 2);
                            
                            if ($start > 1): ?>
                                <li class="page-item"><a class="page-link" href="?page=1<?= $queryString ?>">1</a></li>
                                <?php if ($start > 2): ?>
                                    <li class="page-item disabled"><span class="page-link">...</span></li>
                                <?php endif; ?>
                            <?php endif; ?>
                            
                            <?php for ($i = $start; $i <= $end; $i++): ?>
                                <li class="page-item <?= $i === $currentPage ? 'active' : '' ?>">
                                    <a class="page-link" href="?page=<?= $i ?><?= $queryString ?>"><?= $i ?></a>
                                </li>
                            <?php endfor; ?>
                            
                            <?php if ($end < $totalPages): ?>
                                <?php if ($end < $totalPages - 1): ?>
                                    <li class="page-item disabled"><span class="page-link">...</span></li>
                                <?php endif; ?>
                                <li class="page-item"><a class="page-link" href="?page=<?= $totalPages ?><?= $queryString ?>"><?= $totalPages ?></a></li>
                            <?php endif; ?>
                            
                            <!-- Next -->
                            <li class="page-item <?= $currentPage >= $totalPages ? 'disabled' : '' ?>">
                                <a class="page-link" href="?page=<?= $currentPage + 1 ?><?= $queryString ?>">»</a>
                            </li>
                        </ul>
                        <div class="text-center text-muted small mt-2">
                            Trang <?= $currentPage ?>/<?= $totalPages ?> (<?= $pagination['totalRecords'] ?> ca)
                        </div>
                    </nav>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function toggleSelectAll(checkbox) {
    const checkboxes = document.querySelectorAll('.row-checkbox');
    checkboxes.forEach(cb => cb.checked = checkbox.checked);
    updateSelectedCount();
}

function updateSelectedCount() {
    const checked = document.querySelectorAll('.row-checkbox:checked').length;
    document.getElementById('selectedCount').textContent = `Đã chọn: ${checked}`;
    document.getElementById('selectAll').checked = checked === document.querySelectorAll('.row-checkbox').length && checked > 0;
}

function bulkAction(action) {
    const checked = document.querySelectorAll('.row-checkbox:checked');
    if (checked.length === 0) {
        alert('Vui lòng chọn ít nhất một ca dạy');
        return;
    }
    
    const actionText = {
        'approve': 'duyệt',
        'reject': 'từ chối',
        'delete': 'xóa'
    }[action];
    
    if (!confirm(`Bạn có chắc muốn ${actionText} ${checked.length} ca dạy đã chọn?`)) {
        return;
    }
    
    document.getElementById('bulkActionInput').value = action;
    document.getElementById('bulkForm').submit();
}

function quickFilter(status) {
    document.getElementById('quickFilterStatus').value = status;
    document.getElementById('quickFilterForm').submit();
}

function quickApproveOne(shiftId) {
    if (!confirm('Duyệt ca này?')) return;
    
    fetch('<?= $basePath ?>/teaching-shifts/quick-approve', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ shift_ids: [shiftId] })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Có lỗi: ' + (data.message || 'Không thể duyệt ca'));
        }
    })
    .catch(error => alert('Có lỗi xảy ra: ' + error.message));
}

function quickRejectOne(shiftId) {
    if (!confirm('Từ chối ca này?')) return;
    
    fetch('<?= $basePath ?>/teaching-shifts/quick-reject', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ shift_ids: [shiftId] })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Có lỗi: ' + (data.message || 'Không thể từ chối ca'));
        }
    })
    .catch(error => alert('Có lỗi xảy ra: ' + error.message));
}
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/modern.php';
?>
