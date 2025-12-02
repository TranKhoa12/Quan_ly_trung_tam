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
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-exchange-alt me-2"></i>
                    Yêu cầu chuyển ca của tôi
                </h5>
                <a href="<?= $basePath ?>/teaching-shifts" class="btn btn-light btn-sm">
                    <i class="fas fa-arrow-left me-2"></i>
                    Về lịch dạy
                </a>
            </div>
        </div>
        <div class="card-body">
            <?php if (empty($transfers)): ?>
                <div class="empty-state">
                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                    <p class="text-muted">Chưa có yêu cầu chuyển ca nào.</p>
                    <a href="<?= $basePath ?>/teaching-shifts" class="btn btn-primary">
                        <i class="fas fa-calendar me-2"></i>
                        Xem lịch dạy
                    </a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle transfer-table">
                        <thead class="table-light">
                            <tr>
                                <th>Ngày dạy</th>
                                <th>Giờ</th>
                                <th>Ca</th>
                                <th>Vai trò</th>
                                <th>Người liên quan</th>
                                <th>Lý do</th>
                                <th>Trạng thái</th>
                                <th>Ngày tạo</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($transfers as $transfer): ?>
                                <tr>
                                    <td>
                                        <strong><?= date('d/m/Y', strtotime($transfer['shift_date'])) ?></strong>
                                    </td>
                                    <td>
                                        <?= date('H:i', strtotime($transfer['custom_start'])) ?> - 
                                        <?= date('H:i', strtotime($transfer['custom_end'])) ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($transfer['shift_name'])): ?>
                                            <span class="badge bg-info"><?= htmlspecialchars($transfer['shift_name']) ?></span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Ca tùy chỉnh</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($transfer['role'] === 'sender'): ?>
                                            <span class="badge bg-warning">
                                                <i class="fas fa-paper-plane me-1"></i>
                                                Người gửi
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-success">
                                                <i class="fas fa-hand-holding me-1"></i>
                                                Người nhận
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($transfer['role'] === 'sender'): ?>
                                            <i class="fas fa-arrow-right text-primary me-2"></i>
                                            <?= htmlspecialchars($transfer['to_staff_name']) ?>
                                        <?php else: ?>
                                            <i class="fas fa-arrow-left text-success me-2"></i>
                                            <?= htmlspecialchars($transfer['from_staff_name']) ?>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <small class="text-muted" title="<?= htmlspecialchars($transfer['reason']) ?>">
                                            <?= mb_substr(htmlspecialchars($transfer['reason']), 0, 30) ?>
                                            <?= mb_strlen($transfer['reason']) > 30 ? '...' : '' ?>
                                        </small>
                                    </td>
                                    <td>
                                        <?php
                                        $statusBadges = [
                                            'pending' => '<span class="badge bg-warning"><i class="fas fa-clock me-1"></i>Chờ duyệt</span>',
                                            'approved' => '<span class="badge bg-success"><i class="fas fa-check me-1"></i>Đã duyệt</span>',
                                            'rejected' => '<span class="badge bg-danger"><i class="fas fa-times me-1"></i>Từ chối</span>'
                                        ];
                                        echo $statusBadges[$transfer['status']] ?? $transfer['status'];
                                        ?>
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            <?= date('d/m/Y H:i', strtotime($transfer['created_at'])) ?>
                                        </small>
                                    </td>
                                    <td>
                                        <a href="<?= $basePath ?>/teaching-shifts/transfer/detail/<?= $transfer['id'] ?>" 
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php require_once __DIR__ . '/partials/transfer_styles.php'; ?>

<?php
$content = ob_get_clean();
useModernLayout('Yêu cầu chuyển ca của tôi', $content);
?>
