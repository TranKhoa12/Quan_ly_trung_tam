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
    <div class="row">
        <div class="col-lg-8">
            <!-- Thông tin yêu cầu -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header gradient-header text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        Thông tin yêu cầu chuyển ca
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6 class="text-muted mb-3">Thông tin ca dạy</h6>
                            <table class="table table-sm">
                                <tr>
                                    <td class="fw-bold" style="width: 40%;">Ngày dạy:</td>
                                    <td><?= date('d/m/Y (l)', strtotime($transfer['shift_date'])) ?></td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Thời gian:</td>
                                    <td>
                                        <?= date('H:i', strtotime($transfer['custom_start'])) ?> - 
                                        <?= date('H:i', strtotime($transfer['custom_end'])) ?>
                                    </td>
                                </tr>
                                <?php if (!empty($transfer['shift_name'])): ?>
                                <tr>
                                    <td class="fw-bold">Ca:</td>
                                    <td><span class="badge bg-info"><?= htmlspecialchars($transfer['shift_name']) ?></span></td>
                                </tr>
                                <?php endif; ?>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted mb-3">Thông tin chuyển ca</h6>
                            <table class="table table-sm">
                                <tr>
                                    <td class="fw-bold" style="width: 40%;">Trạng thái:</td>
                                    <td>
                                        <?php
                                        $statusBadges = [
                                            'pending' => '<span class="badge bg-warning text-dark"><i class="fas fa-clock me-1"></i>Chờ duyệt</span>',
                                            'approved' => '<span class="badge bg-success"><i class="fas fa-check me-1"></i>Đã duyệt</span>',
                                            'rejected' => '<span class="badge bg-danger"><i class="fas fa-times me-1"></i>Từ chối</span>'
                                        ];
                                        echo $statusBadges[$transfer['status']] ?? $transfer['status'];
                                        ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Ngày tạo:</td>
                                    <td><?= date('d/m/Y H:i', strtotime($transfer['created_at'])) ?></td>
                                </tr>
                                <?php if ($transfer['processed_at']): ?>
                                <tr>
                                    <td class="fw-bold">Ngày xử lý:</td>
                                    <td><?= date('d/m/Y H:i', strtotime($transfer['processed_at'])) ?></td>
                                </tr>
                                <?php endif; ?>
                            </table>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="transfer-box from-box">
                                <div class="transfer-header">
                                    <i class="fas fa-user-minus me-2"></i>
                                    Người chuyển ca
                                </div>
                                <div class="transfer-body">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-user-circle fa-3x text-primary me-3"></i>
                                        <div>
                                            <div class="fw-bold"><?= htmlspecialchars($transfer['from_staff_name']) ?></div>
                                            <small class="text-muted"><?= htmlspecialchars($transfer['from_staff_email']) ?></small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="transfer-box to-box">
                                <div class="transfer-header">
                                    <i class="fas fa-user-plus me-2"></i>
                                    Người nhận ca
                                </div>
                                <div class="transfer-body">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-user-circle fa-3x text-success me-3"></i>
                                        <div>
                                            <div class="fw-bold"><?= htmlspecialchars($transfer['to_staff_name']) ?></div>
                                            <small class="text-muted"><?= htmlspecialchars($transfer['to_staff_email']) ?></small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <h6 class="text-muted mb-2">Lý do chuyển ca</h6>
                        <div class="alert alert-info border-0">
                            <i class="fas fa-comment-dots me-2"></i>
                            <?= nl2br(htmlspecialchars($transfer['reason'])) ?>
                        </div>
                    </div>

                    <?php if (!empty($transfer['admin_note'])): ?>
                    <div class="mb-4">
                        <h6 class="text-muted mb-2">Ghi chú của Admin</h6>
                        <div class="alert alert-secondary border-0">
                            <div class="d-flex align-items-start">
                                <i class="fas fa-user-shield fa-2x me-3 mt-1"></i>
                                <div>
                                    <div class="fw-bold"><?= htmlspecialchars($transfer['admin_name'] ?? 'Admin') ?></div>
                                    <div class="mt-2"><?= nl2br(htmlspecialchars($transfer['admin_note'])) ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if ($user['role'] === 'admin' && $transfer['status'] === 'pending'): ?>
                    <div class="d-flex gap-3 justify-content-end">
                        <button type="button" class="btn btn-success" onclick="showApproveModal(<?= $transfer['id'] ?>)">
                            <i class="fas fa-check me-2"></i>
                            Duyệt yêu cầu
                        </button>
                        <button type="button" class="btn btn-danger" onclick="showRejectModal(<?= $transfer['id'] ?>)">
                            <i class="fas fa-times me-2"></i>
                            Từ chối
                        </button>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Lịch sử log -->
            <div class="card shadow-sm border-0">
                <div class="card-header gradient-header text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-history me-2"></i>
                        Lịch sử thay đổi
                    </h6>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($logs)): ?>
                        <p class="text-center text-muted py-4 mb-0">Chưa có log nào</p>
                    <?php else: ?>
                        <div class="timeline">
                            <?php foreach ($logs as $index => $log): ?>
                                <div class="timeline-item <?= $index === count($logs) - 1 ? 'last' : '' ?>">
                                    <div class="timeline-marker">
                                        <?php
                                        $iconMap = [
                                            'created' => 'fa-plus-circle text-info',
                                            'approved' => 'fa-check-circle text-success',
                                            'rejected' => 'fa-times-circle text-danger'
                                        ];
                                        $icon = $iconMap[$log['action']] ?? 'fa-circle text-secondary';
                                        ?>
                                        <i class="fas <?= $icon ?>"></i>
                                    </div>
                                    <div class="timeline-content">
                                        <div class="fw-bold">
                                            <?php
                                            $actionMap = [
                                                'created' => 'Tạo yêu cầu',
                                                'approved' => 'Đã duyệt',
                                                'rejected' => 'Từ chối'
                                            ];
                                            echo $actionMap[$log['action']] ?? $log['action'];
                                            ?>
                                        </div>
                                        <small class="text-muted">
                                            <?= htmlspecialchars($log['actor_name']) ?>
                                        </small>
                                        <?php if (!empty($log['notes'])): ?>
                                            <div class="mt-2 small"><?= nl2br(htmlspecialchars($log['notes'])) ?></div>
                                        <?php endif; ?>
                                        <div class="mt-1 text-muted" style="font-size: 0.75rem;">
                                            <i class="far fa-clock me-1"></i>
                                            <?= date('d/m/Y H:i', strtotime($log['created_at'])) ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="mt-3">
                <a href="<?= $basePath ?>/teaching-shifts/transfers/<?= $user['role'] === 'admin' ? 'list' : 'my' ?>" 
                   class="btn btn-secondary w-100">
                    <i class="fas fa-arrow-left me-2"></i>
                    Quay lại
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Modal Duyệt -->
<div class="modal fade" id="approveModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="fas fa-check-circle me-2"></i>Duyệt yêu cầu</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="approveForm" method="POST">
                <div class="modal-body">
                    <p>Bạn có chắc chắn muốn duyệt yêu cầu này?</p>
                    <textarea name="admin_note" class="form-control" rows="3" placeholder="Ghi chú (tùy chọn)..."></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-success">Xác nhận</button>
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
                <h5 class="modal-title"><i class="fas fa-times-circle me-2"></i>Từ chối yêu cầu</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="rejectForm" method="POST">
                <div class="modal-body">
                    <p>Bạn có chắc chắn muốn từ chối yêu cầu này?</p>
                    <textarea name="admin_note" class="form-control" rows="3" required placeholder="Lý do từ chối..."></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-danger">Xác nhận</button>
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
useModernLayout('Chi tiết yêu cầu chuyển ca', $content);
?>
