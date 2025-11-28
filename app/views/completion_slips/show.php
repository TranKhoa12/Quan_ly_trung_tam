<?php
require_once __DIR__ . '/../layouts/main.php';

$scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
$scriptDir = str_replace('\\', '/', dirname($scriptName));
$basePath = rtrim($scriptDir, '/');
if ($basePath === '' || $basePath === '.') {
    $basePath = '';
}

$uploadsPath = $basePath . '/uploads/';

ob_start();

$buttonHtml = '<a href="' . $basePath . '/completion-slips" class="btn btn-outline-secondary me-2">
    <i class="fas fa-arrow-left me-1"></i>Danh sách
</a>';

if (($userRole ?? 'staff') === 'admin') {
    $buttonHtml .= '<a href="' . $basePath . '/completion-slips/' . $slip['id'] . '/edit" class="btn btn-primary">
        <i class="fas fa-edit me-1"></i>Chỉnh sửa
    </a>';
}

echo pageHeader(
    'Phiếu hoàn thành #' . $slip['id'],
    'Tạo lúc ' . (!empty($slip['created_at']) ? date('d/m/Y H:i', strtotime($slip['created_at'])) : 'Chưa xác định'),
    $buttonHtml
);
?>

<div class="p-3">
    <div class="row g-4">
        <div class="col-lg-6">
            <div class="stats-card h-100">
                <div class="card-body">
                    <h6 class="fw-bold mb-3"><i class="fas fa-user-graduate text-primary me-2"></i>Thông tin học viên</h6>
                    <div class="mb-3">
                        <div class="text-muted small">Họ tên</div>
                        <div class="fw-semibold fs-5"><?= htmlspecialchars($slip['student_name']) ?></div>
                    </div>
                    <div class="mb-3">
                        <div class="text-muted small">Giáo viên</div>
                        <div><?= htmlspecialchars($slip['teacher_name'] ?? '-') ?></div>
                    </div>
                    <div class="mb-3">
                        <div class="text-muted small">Khóa học</div>
                        <div class="fw-semibold"><?= htmlspecialchars($slip['course_name'] ?? 'Chưa cập nhật') ?></div>
                    </div>
                    <div class="mb-3">
                        <div class="text-muted small">Người tạo</div>
                        <div><?= htmlspecialchars($slip['created_by_name'] ?? 'Không rõ') ?></div>
                    </div>
                    <?php if (!empty($slip['notes'])): ?>
                        <div class="mb-0">
                            <div class="text-muted small">Ghi chú</div>
                            <div class="alert alert-light border">
                                <?= nl2br(htmlspecialchars($slip['notes'])) ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="stats-card h-100">
                <div class="card-body">
                    <h6 class="fw-bold mb-3"><i class="fas fa-images text-danger me-2"></i>Ảnh phiếu</h6>
                    <?php if (!empty($images)): ?>
                        <div class="row g-3">
                            <?php foreach ($images as $index => $image): ?>
                                <div class="col-6">
                                    <div class="border rounded p-2 h-100 d-flex flex-column justify-content-between">
                                        <div class="text-muted small mb-2">Ảnh #<?= $index + 1 ?></div>
                                        <?php if (preg_match('/\.pdf$/i', $image)): ?>
                                            <div class="text-center flex-grow-1 d-flex flex-column justify-content-center">
                                                <i class="fas fa-file-pdf text-danger fs-1 mb-2"></i>
                                                <a href="<?= htmlspecialchars($uploadsPath . $image) ?>" target="_blank" class="btn btn-sm btn-outline-primary w-100">
                                                    <i class="fas fa-download me-1"></i>Tải PDF
                                                </a>
                                            </div>
                                        <?php else: ?>
                                            <a href="<?= htmlspecialchars($uploadsPath . $image) ?>" target="_blank" class="d-block mb-2">
                                                <img src="<?= htmlspecialchars($uploadsPath . $image) ?>" class="img-fluid rounded" alt="Ảnh phiếu">
                                            </a>
                                            <a href="<?= htmlspecialchars($uploadsPath . $image) ?>" target="_blank" class="btn btn-sm btn-outline-primary w-100">
                                                <i class="fas fa-eye me-1"></i>Xem chi tiết
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-light border">
                            <i class="fas fa-info-circle me-1"></i>Chưa đính kèm ảnh nào.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
useModernLayout('Chi tiết phiếu hoàn thành', $content);
?>
