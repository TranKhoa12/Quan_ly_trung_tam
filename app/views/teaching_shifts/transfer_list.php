<?php
require_once __DIR__ . '/../layouts/main.php';

$scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
$scriptDir = str_replace('\\', '/', dirname($scriptName));
$basePath = rtrim($scriptDir, '/');
if ($basePath === '' || $basePath === '.') {
    $basePath = '';
}

ob_start();
?>

<div class="container-fluid py-4 transfer-page">
    <div class="card shadow-sm border-0">
        <div class="card-header gradient-header text-white">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <h5 class="mb-0">
                    <i class="fas fa-exchange-alt me-2"></i>
                    Quản lý yêu cầu chuyển ca
                </h5>
                <div class="btn-group" role="group">
                    <a href="<?= $basePath ?>/teaching-shifts/transfers/list?status=pending" 
                       class="btn btn-sm <?= $currentStatus === 'pending' ? 'btn-light' : 'btn-outline-light' ?>">
                        <i class="fas fa-clock me-1"></i>
                        Chờ duyệt
                    </a>
                    <a href="<?= $basePath ?>/teaching-shifts/transfers/list?status=approved" 
                       class="btn btn-sm <?= $currentStatus === 'approved' ? 'btn-light' : 'btn-outline-light' ?>">
                        <i class="fas fa-check me-1"></i>
                        Đã duyệt
                    </a>
                    <a href="<?= $basePath ?>/teaching-shifts/transfers/list?status=rejected" 
                       class="btn btn-sm <?= $currentStatus === 'rejected' ? 'btn-light' : 'btn-outline-light' ?>">
                        <i class="fas fa-times me-1"></i>
                        Từ chối
                    </a>
                    <a href="<?= $basePath ?>/teaching-shifts/transfers/list?status=all" 
                       class="btn btn-sm <?= $currentStatus === 'all' ? 'btn-light' : 'btn-outline-light' ?>">
                        <i class="fas fa-list me-1"></i>
                        Tất cả
                    </a>
                </div>
            </div>
        </div>
        <div class="card-body">
            <?php if (empty($transfers)): ?>
                <div class="empty-state">
                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                    <p class="text-muted">Không có yêu cầu nào.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle transfer-table">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 5%;">#</th>
                                <th style="width: 10%;">Ngày dạy</th>
                                <th style="width: 10%;">Giờ</th>
                                <th style="width: 10%;">Ca</th>
                                <th style="width: 15%;">Từ nhân viên</th>
                                <th style="width: 15%;">Đến nhân viên</th>
                                <th style="width: 15%;">Lý do</th>
                                <th style="width: 10%;">Trạng thái</th>
                                <th style="width: 10%;">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($transfers as $index => $transfer): ?>
                                <tr>
                                    <td><?= $index + 1 ?></td>
                                    <td>
                                        <strong><?= date('d/m/Y', strtotime($transfer['shift_date'])) ?></strong>
                                    </td>
                                    <td>
                                        <small>
                                            <?= date('H:i', strtotime($transfer['custom_start'])) ?><br>
                                            <?= date('H:i', strtotime($transfer['custom_end'])) ?>
                                        </small>
                                    </td>
                                    <td>
                                        <?php if (!empty($transfer['shift_name'])): ?>
                                            <span class="badge bg-info"><?= htmlspecialchars($transfer['shift_name']) ?></span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Tùy chỉnh</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-user-circle text-primary me-2"></i>
                                            <div>
                                                <div class="fw-bold"><?= htmlspecialchars($transfer['from_staff_name']) ?></div>
                                                <small class="text-muted"><?= htmlspecialchars($transfer['from_staff_email']) ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-user-circle text-success me-2"></i>
                                            <div>
                                                <div class="fw-bold"><?= htmlspecialchars($transfer['to_staff_name']) ?></div>
                                                <small class="text-muted"><?= htmlspecialchars($transfer['to_staff_email']) ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <small class="text-muted" title="<?= htmlspecialchars($transfer['reason']) ?>">
                                            <?= mb_substr(htmlspecialchars($transfer['reason']), 0, 40) ?>
                                            <?= mb_strlen($transfer['reason']) > 40 ? '...' : '' ?>
                                        </small>
                                    </td>
                                    <td>
                                        <?php
                                        $statusBadges = [
                                            'pending' => '<span class="badge bg-warning text-dark"><i class="fas fa-clock me-1"></i>Chờ duyệt</span>',
                                            'approved' => '<span class="badge bg-success"><i class="fas fa-check me-1"></i>Đã duyệt</span>',
                                            'rejected' => '<span class="badge bg-danger"><i class="fas fa-times me-1"></i>Từ chối</span>'
                                        ];
                                        echo $statusBadges[$transfer['status']] ?? $transfer['status'];
                                        ?>
                                        <?php if ($transfer['status'] !== 'pending'): ?>
                                            <div class="mt-1">
                                                <small class="text-muted">
                                                    <?= date('d/m H:i', strtotime($transfer['processed_at'])) ?>
                                                </small>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group-vertical gap-1">
                                            <a href="<?= $basePath ?>/teaching-shifts/transfer/detail/<?= $transfer['id'] ?>" 
                                               class="btn btn-sm btn-outline-info" title="Chi tiết">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <?php if ($transfer['status'] === 'pending'): ?>
                                                <button type="button" class="btn btn-sm btn-outline-success" 
                                                        onclick="showApproveModal(<?= $transfer['id'] ?>)" title="Duyệt">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-danger" 
                                                        onclick="showRejectModal(<?= $transfer['id'] ?>)" title="Từ chối">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            <?php endif; ?>
                                            <form method="POST" action="<?= $basePath ?>/teaching-shifts/transfer/delete/<?= $transfer['id'] ?>" 
                                                  onsubmit="return confirm('Xóa yêu cầu chuyển ca này vĩnh viễn?')" class="d-inline">
                                                <button type="submit" class="btn btn-sm btn-outline-secondary w-100" title="Xóa">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal Duyệt -->
<div class="modal fade" id="approveModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="fas fa-check-circle me-2"></i>
                    Duyệt yêu cầu chuyển ca
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="approveForm" method="POST">
                <div class="modal-body">
                    <p>Bạn có chắc chắn muốn <strong>duyệt</strong> yêu cầu chuyển ca này?</p>
                    <div class="mb-3">
                        <label class="form-label">Ghi chú (tùy chọn)</label>
                        <textarea name="admin_note" class="form-control" rows="3" 
                            placeholder="Nhập ghi chú nếu có..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check me-2"></i>
                        Xác nhận duyệt
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Từ chối -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="fas fa-times-circle me-2"></i>
                    Từ chối yêu cầu chuyển ca
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="rejectForm" method="POST">
                <div class="modal-body">
                    <p>Bạn có chắc chắn muốn <strong>từ chối</strong> yêu cầu chuyển ca này?</p>
                    <div class="mb-3">
                        <label class="form-label">Lý do từ chối <span class="text-danger">*</span></label>
                        <textarea name="admin_note" class="form-control" rows="3" required
                            placeholder="Nhập lý do từ chối..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-times me-2"></i>
                        Xác nhận từ chối
                    </button>
                </div>
            </form>
        </div>
    </div>
<?php require_once __DIR__ . '/partials/transfer_styles.php'; ?>

<script>
function showApproveModal(transferId) {
    const form = document.getElementById('approveForm');
    form.action = '<?= $basePath ?>/teaching-shifts/transfer/approve/' + transferId;
    new bootstrap.Modal(document.getElementById('approveModal')).show();
}

function showRejectModal(transferId) {
    const form = document.getElementById('rejectForm');
    form.action = '<?= $basePath ?>/teaching-shifts/transfer/reject/' + transferId;
    new bootstrap.Modal(document.getElementById('rejectModal')).show();
}
</script>

<?php
$content = ob_get_clean();
useModernLayout('Quản lý yêu cầu chuyển ca', $content);
?>
