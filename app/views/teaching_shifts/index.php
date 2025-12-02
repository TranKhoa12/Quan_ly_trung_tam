<?php
require_once __DIR__ . '/../layouts/main.php';

$scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
$scriptDir = str_replace('\\', '/', dirname($scriptName));
$basePath = rtrim($scriptDir, '/');
if ($basePath === '' || $basePath === '.') {
    $basePath = '';
}

ob_start();

echo pageHeader(
    'Lịch dạy & đăng ký ca',
    'Đăng ký ca dạy và theo dõi trạng thái duyệt của bạn'
);
?>

<div class="p-3">
    <div class="stats-card mb-4">
        <div class="card-body">
            <h5 class="fw-semibold mb-3">
                <i class="fas fa-plus-circle text-success me-2"></i>Đăng ký ca mới
            </h5>
            <form class="row g-3" method="POST" action="<?= $basePath ?>/teaching-shifts/register">
                <div class="col-md-3">
                    <label class="form-label">Ngày dạy <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" name="shift_date" required min="<?= date('Y-m-d') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Ca dạy <span class="text-danger">*</span></label>
                    <select class="form-select" name="shift_id" id="shiftSelect" required>
                        <option value="">-- Chọn ca cố định --</option>
                        <?php foreach ($activeShifts as $shift): ?>
                            <option value="<?= $shift['id'] ?>">
                                <?= htmlspecialchars($shift['name']) ?>
                                (<?= substr($shift['start_time'], 0, 5) ?> - <?= substr($shift['end_time'], 0, 5) ?>)
                            </option>
                        <?php endforeach; ?>
                        <option value="custom">Ca chọn (tự nhập giờ)</option>
                    </select>
                </div>
                <div class="col-md-2 custom-shift-field" style="display:none;">
                    <label class="form-label">Giờ vào</label>
                    <input type="time" class="form-control" name="custom_start" id="customStart">
                </div>
                <div class="col-md-2 custom-shift-field" style="display:none;">
                    <label class="form-label">Giờ ra</label>
                    <input type="time" class="form-control" name="custom_end" id="customEnd">
                </div>
                <div class="col-12">
                    <label class="form-label">Ghi chú</label>
                    <textarea class="form-control" name="notes" rows="2" placeholder="Ví dụ: thay ca giúp bạn, lớp phát sinh..."></textarea>
                </div>
                <div class="col-12 text-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>Gửi đăng ký
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="stats-card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-semibold mb-0"><i class="fas fa-calendar-week text-primary me-2"></i>Lịch ca của tôi</h5>
                <span class="badge bg-secondary"><?= count($registrations) ?> ca gần đây</span>
            </div>
            <?php if (empty($registrations)): ?>
                <div class="alert alert-light border">Chưa có đăng ký nào. Hãy tạo ca đầu tiên!</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th>Ngày</th>
                                <th>Khung giờ</th>
                                <th>Số giờ</th>
                                <th>Trạng thái</th>
                                <th>Ghi chú</th>
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
                                    <td class="fw-semibold"><?= date('d/m/Y', strtotime($registration['shift_date'])) ?></td>
                                    <td>
                                        <div class="fw-semibold">
                                            <?= $registration['shift_name'] ? htmlspecialchars($registration['shift_name']) : 'Ca chọn' ?>
                                        </div>
                                        <small class="text-muted">
                                            <?= $startDisplay ?> - <?= $endDisplay ?>
                                        </small>
                                    </td>
                                    <td><?= number_format($registration['hours'], 2) ?>h</td>
                                    <td>
                                        <span class="badge bg-<?= $badgeClass ?> text-uppercase">
                                            <?= strtoupper($status) ?>
                                        </span>
                                    </td>
                                    <td><?= htmlspecialchars($registration['notes'] ?? '-') ?></td>
                                    <td class="text-end">
                                        <?php 
                                        $shiftDateTime = $registration['shift_date'] . ' ' . ($registration['custom_end'] ?? $registration['preset_end'] ?? '23:59:59');
                                        $isPastShift = strtotime($shiftDateTime) < time();
                                        ?>
                                        <?php if ($status === 'approved' && !$isPastShift): ?>
                                            <a href="<?= $basePath ?>/teaching-shifts/transfer/<?= $registration['id'] ?>" 
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-exchange-alt me-1"></i>Chuyển ca
                                            </a>
                                        <?php elseif ($status === 'pending'): ?>
                                            <form method="POST" action="<?= $basePath ?>/teaching-shifts/<?= $registration['id'] ?>/cancel" class="d-inline">
                                                <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Hủy đăng ký ca này?');">
                                                    <i class="fas fa-times me-1"></i>Hủy
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <span class="text-muted">--</span>
                                        <?php endif; ?>
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

<script>
const shiftSelect = document.getElementById('shiftSelect');
const customFields = document.querySelectorAll('.custom-shift-field');
if (shiftSelect) {
    shiftSelect.addEventListener('change', () => {
        if (shiftSelect.value === 'custom') {
            customFields.forEach(field => field.style.display = 'block');
            customFields.forEach(field => field.querySelector('input').setAttribute('required', 'required'));
        } else {
            customFields.forEach(field => {
                field.style.display = 'none';
                const input = field.querySelector('input');
                input.removeAttribute('required');
                input.value = '';
            });
        }
    });
}
</script>

<?php
$content = ob_get_clean();
echo renderLayout('Lịch dạy của tôi', $content, 'teaching_shifts');
?>
