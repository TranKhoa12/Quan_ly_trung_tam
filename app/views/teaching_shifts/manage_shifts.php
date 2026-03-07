<?php
$pageTitle = 'Quản lý loại ca dạy';

$scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
$scriptDir = str_replace('\\', '/', dirname($scriptName));
$basePath = rtrim($scriptDir, '/');
if ($basePath === '' || $basePath === '.') {
    $basePath = '';
}

ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-1">
            <i class="fas fa-layer-group me-2" style="color: #4361ee;"></i>
            Quản lý loại ca dạy
        </h2>
        <p class="text-muted mb-0">Tạo, chỉnh sửa và quản lý các khung giờ ca dạy</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= $basePath ?>/teaching-shifts/admin" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Quay lại
        </a>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addShiftModal">
            <i class="fas fa-plus me-2"></i>Thêm ca dạy mới
        </button>
    </div>
</div>

<!-- Thông báo -->
<?php if (!empty($_SESSION['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($_SESSION['success']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['success']); ?>
<?php endif; ?>
<?php if (!empty($_SESSION['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($_SESSION['error']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>

<!-- Thông báo ảnh hưởng -->
<div class="alert alert-info border-0 mb-4">
    <div class="d-flex gap-2 align-items-start">
        <i class="fas fa-info-circle mt-1 text-primary"></i>
        <div>
            <strong>Lưu ý an toàn:</strong>
            Khi sửa ca dạy <strong>đã có đăng ký</strong>, giờ của ca sẽ thay đổi nhưng các <em>đăng ký cũ vẫn giữ nguyên số giờ đã tính</em>.
            Chỉ các đăng ký <strong>mới</strong> sẽ dùng giờ mới. Vô hiệu hoá ca sẽ ẩn khỏi form đăng ký nhưng <strong>không ảnh hưởng báo cáo & bảng lương.</strong>
        </div>
    </div>
</div>

<!-- Danh sách ca dạy -->
<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th>Tên ca</th>
                        <th>Giờ bắt đầu</th>
                        <th>Giờ kết thúc</th>
                        <th>Thời lượng</th>
                        <th>Đơn giá/giờ</th>
                        <th>Số đăng ký</th>
                        <th>Trạng thái</th>
                        <th class="text-end">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($shifts)): ?>
                    <tr>
                        <td colspan="9" class="text-center text-muted py-4">Chưa có ca dạy nào.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($shifts as $i => $shift):
                        $start = $shift['start_time'] ?? null;
                        $end   = $shift['end_time'] ?? null;
                        $duration = '';
                        if ($start && $end) {
                            $mins = (strtotime($end) - strtotime($start)) / 60;
                            $h = floor($mins / 60);
                            $m = $mins % 60;
                            $duration = $h > 0 ? "{$h}h" : '';
                            $duration .= $m > 0 ? "{$m}p" : '';
                        }
                        $isActive = (int)$shift['is_active'] === 1;
                        $regCount = (int)($shift['registration_count'] ?? 0);
                    ?>
                    <tr class="<?= $isActive ? '' : 'table-secondary text-muted' ?>">
                        <td><?= $i + 1 ?></td>
                        <td>
                            <span class="fw-semibold"><?= htmlspecialchars($shift['name']) ?></span>
                            <?php if (!$isActive): ?>
                                <span class="badge bg-secondary ms-1">Ẩn</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border">
                                <i class="fas fa-clock me-1 text-primary"></i>
                                <?= $start ? substr($start, 0, 5) : '--' ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border">
                                <i class="fas fa-clock me-1 text-danger"></i>
                                <?= $end ? substr($end, 0, 5) : '--' ?>
                            </span>
                        </td>
                        <td><span class="text-muted"><?= $duration ?: '--' ?></span></td>
                        <td><?= number_format((float)$shift['hourly_rate'], 0, ',', '.') ?> đ/giờ</td>
                        <td>
                            <?php if ($regCount > 0): ?>
                                <span class="badge bg-primary"><?= $regCount ?> ca</span>
                            <?php else: ?>
                                <span class="text-muted">0 ca</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($isActive): ?>
                                <span class="badge bg-success"><i class="fas fa-check me-1"></i>Đang hoạt động</span>
                            <?php else: ?>
                                <span class="badge bg-secondary"><i class="fas fa-ban me-1"></i>Vô hiệu hoá</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end">
                            <button class="btn btn-sm btn-outline-primary me-1"
                                    onclick="openEditModal(<?= $shift['id'] ?>, <?= htmlspecialchars(json_encode($shift)) ?>)"
                                    title="Sửa">
                                <i class="fas fa-edit"></i>
                            </button>
                            <form method="POST" action="<?= $basePath ?>/teaching-shifts/manage/<?= $shift['id'] ?>/toggle"
                                  class="d-inline"
                                  onsubmit="return confirm('<?= $isActive ? 'Vô hiệu hoá' : 'Kích hoạt lại' ?> ca dạy này?')">
                                <button type="submit" class="btn btn-sm <?= $isActive ? 'btn-outline-warning' : 'btn-outline-success' ?>"
                                        title="<?= $isActive ? 'Vô hiệu hoá' : 'Kích hoạt' ?>">
                                    <i class="fas <?= $isActive ? 'fa-eye-slash' : 'fa-eye' ?>"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Thêm ca dạy -->
<div class="modal fade" id="addShiftModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-plus-circle me-2 text-primary"></i>Thêm ca dạy mới</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="<?= $basePath ?>/teaching-shifts/manage/store">
                <div class="modal-body">
                    <!-- Nút điền nhanh 5 ca mẫu -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Điền nhanh ca mẫu:</label>
                        <div class="d-flex flex-wrap gap-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="fillPreset('Ca 1', '08:30', '10:00', 50000)">Ca 1 (8h30-10h)</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="fillPreset('Ca 2', '10:00', '11:30', 50000)">Ca 2 (10h-11h30)</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="fillPreset('Ca 3', '14:00', '15:30', 50000)">Ca 3 (14h-15h30)</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="fillPreset('Ca 4', '17:00', '18:30', 50000)">Ca 4 (17h-18h30)</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="fillPreset('Ca 5', '19:00', '20:30', 50000)">Ca 5 (19h-20h30)</button>
                        </div>
                    </div>
                    <hr>
                    <div class="mb-3">
                        <label class="form-label">Tên ca <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name" id="addName" placeholder="VD: Ca 1" required>
                    </div>
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label">Giờ bắt đầu <span class="text-danger">*</span></label>
                            <input type="time" class="form-control" name="start_time" id="addStart" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Giờ kết thúc <span class="text-danger">*</span></label>
                            <input type="time" class="form-control" name="end_time" id="addEnd" required>
                        </div>
                    </div>
                    <div class="mt-3">
                        <label class="form-label">Đơn giá/giờ (đồng)</label>
                        <input type="number" class="form-control" name="hourly_rate" id="addRate" value="50000" min="0" step="1000">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Lưu</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Sửa ca dạy -->
<div class="modal fade" id="editShiftModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-edit me-2 text-warning"></i>Sửa ca dạy</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="editForm" action="">
                <div class="modal-body">
                    <div class="alert alert-warning border-0 py-2 mb-3" id="editWarning" style="display:none">
                        <i class="fas fa-exclamation-triangle me-1"></i>
                        Ca này đang có <strong id="editRegCount"></strong> đăng ký.
                        Thay đổi giờ <strong>không ảnh hưởng</strong> đến các đăng ký cũ.
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tên ca <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name" id="editName" required>
                    </div>
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label">Giờ bắt đầu <span class="text-danger">*</span></label>
                            <input type="time" class="form-control" name="start_time" id="editStart" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Giờ kết thúc <span class="text-danger">*</span></label>
                            <input type="time" class="form-control" name="end_time" id="editEnd" required>
                        </div>
                    </div>
                    <div class="mt-3">
                        <label class="form-label">Đơn giá/giờ (đồng)</label>
                        <input type="number" class="form-control" name="hourly_rate" id="editRate" min="0" step="1000">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-warning"><i class="fas fa-save me-1"></i>Cập nhật</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function fillPreset(name, start, end, rate) {
    document.getElementById('addName').value  = name;
    document.getElementById('addStart').value = start;
    document.getElementById('addEnd').value   = end;
    document.getElementById('addRate').value  = rate;
}

function openEditModal(id, shift) {
    document.getElementById('editForm').action =
        '<?= $basePath ?>/teaching-shifts/manage/' + id + '/update';
    document.getElementById('editName').value  = shift.name;
    document.getElementById('editStart').value = shift.start_time ? shift.start_time.substring(0, 5) : '';
    document.getElementById('editEnd').value   = shift.end_time   ? shift.end_time.substring(0, 5)   : '';
    document.getElementById('editRate').value  = shift.hourly_rate;

    const regCount = parseInt(shift.registration_count) || 0;
    const warning  = document.getElementById('editWarning');
    if (regCount > 0) {
        document.getElementById('editRegCount').textContent = regCount + ' ca';
        warning.style.display = 'block';
    } else {
        warning.style.display = 'none';
    }

    new bootstrap.Modal(document.getElementById('editShiftModal')).show();
}
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/modern.php';
?>
