<?php
require_once __DIR__ . '/../layouts/main.php';

// Page content
ob_start();
?>

<?= pageHeader(
    'Chi tiết nhân viên', 
    'Thông tin chi tiết của nhân viên: ' . htmlspecialchars($staff['full_name']),
    '<div class="d-flex gap-2">
        <a href="/Quan_ly_trung_tam/public/staff" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Quay lại
        </a>
        <a href="/Quan_ly_trung_tam/public/staff/<?= $staff["id"] ?>/edit" class="btn btn-primary">
            <i class="fas fa-edit me-2"></i>Sửa
        </a>
    </div>'
) ?>

<div class="p-3">
    <div class="row">
        <!-- Thông tin cơ bản -->
        <div class="col-md-8">
            <div class="stats-card mb-4">
                <div class="card-body">
                    <h5 class="card-title mb-4">
                        <i class="fas fa-user text-primary me-2"></i>
                        Thông tin cơ bản
                    </h5>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted">Họ và tên</label>
                            <div class="fw-bold fs-5 text-dark">
                                <i class="fas fa-user text-primary me-2"></i>
                                <?= htmlspecialchars($staff['full_name']) ?>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label text-muted">Tên đăng nhập</label>
                            <div class="fw-bold">
                                <i class="fas fa-at text-primary me-2"></i>
                                <?= htmlspecialchars($staff['username']) ?>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label text-muted">Email</label>
                            <div class="fw-bold">
                                <i class="fas fa-envelope text-primary me-2"></i>
                                <a href="mailto:<?= htmlspecialchars($staff['email']) ?>" class="text-decoration-none">
                                    <?= htmlspecialchars($staff['email']) ?>
                                </a>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label text-muted">Số điện thoại</label>
                            <div class="fw-bold">
                                <i class="fas fa-phone text-primary me-2"></i>
                                <?php if ($staff['phone']): ?>
                                    <a href="tel:<?= htmlspecialchars($staff['phone']) ?>" class="text-decoration-none">
                                        <?= htmlspecialchars($staff['phone']) ?>
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted">Chưa cập nhật</span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <?php if ($staff['address']): ?>
                        <div class="col-12">
                            <label class="form-label text-muted">Địa chỉ</label>
                            <div class="fw-bold">
                                <i class="fas fa-map-marker-alt text-primary me-2"></i>
                                <?= htmlspecialchars($staff['address']) ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Thông tin công việc -->
            <div class="stats-card mb-4">
                <div class="card-body">
                    <h5 class="card-title mb-4">
                        <i class="fas fa-briefcase text-success me-2"></i>
                        Thông tin công việc
                    </h5>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted">Phòng ban</label>
                            <div class="fw-bold">
                                <i class="fas fa-building text-primary me-2"></i>
                                <?= $staff['department'] ? htmlspecialchars($staff['department']) : '<span class="text-muted">Chưa phân công</span>' ?>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label text-muted">Trạng thái</label>
                            <div class="fw-bold">
                                <?php
                                $statusInfo = match($staff['status']) {
                                    'active' => ['text-success', 'fas fa-check-circle', 'Đang làm việc'],
                                    'inactive' => ['text-danger', 'fas fa-times-circle', 'Nghỉ việc'],
                                    'on_leave' => ['text-warning', 'fas fa-pause-circle', 'Tạm dừng'],
                                    default => ['text-muted', 'fas fa-question-circle', 'Không xác định']
                                };
                                ?>
                                <span class="<?= $statusInfo[0] ?>">
                                    <i class="<?= $statusInfo[1] ?> me-2"></i>
                                    <?= $statusInfo[2] ?>
                                </span>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label text-muted">Ngày vào làm</label>
                            <div class="fw-bold">
                                <i class="fas fa-calendar text-primary me-2"></i>
                                <?= $staff['hire_date'] ? date('d/m/Y', strtotime($staff['hire_date'])) : '<span class="text-muted">Chưa cập nhật</span>' ?>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label text-muted">Lương</label>
                            <div class="fw-bold">
                                <i class="fas fa-money-bill-wave text-primary me-2"></i>
                                <?= $staff['salary'] ? number_format($staff['salary']) . ' VNĐ' : '<span class="text-muted">Chưa cập nhật</span>' ?>
                            </div>
                        </div>

                        <?php if ($staff['hire_date']): ?>
                        <div class="col-md-6">
                            <label class="form-label text-muted">Thời gian làm việc</label>
                            <div class="fw-bold">
                                <i class="fas fa-clock text-primary me-2"></i>
                                <?php
                                $start = new DateTime($staff['hire_date']);
                                $now = new DateTime();
                                $diff = $start->diff($now);
                                echo $diff->y . ' năm ' . $diff->m . ' tháng ' . $diff->d . ' ngày';
                                ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Ghi chú -->
            <?php if ($staff['notes']): ?>
            <div class="stats-card mb-4">
                <div class="card-body">
                    <h5 class="card-title mb-3">
                        <i class="fas fa-sticky-note text-warning me-2"></i>
                        Ghi chú
                    </h5>
                    <div class="bg-light p-3 rounded">
                        <?= nl2br(htmlspecialchars($staff['notes'])) ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Sidebar -->
        <div class="col-md-4">
            <!-- Thống kê nhanh -->
            <div class="stats-card mb-4">
                <div class="card-body">
                    <h5 class="card-title mb-3">
                        <i class="fas fa-chart-pie text-info me-2"></i>
                        Thống kê
                    </h5>

                    <div class="row g-2">
                        <div class="col-12">
                            <div class="info-card bg-primary text-white">
                                <div class="p-3 text-center">
                                    <i class="fas fa-hashtag fs-4"></i>
                                    <h6 class="mt-2 mb-1">ID Nhân viên</h6>
                                    <strong class="fs-5">#<?= $staff['id'] ?></strong>
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="info-card bg-success text-white">
                                <div class="p-3 text-center">
                                    <i class="fas fa-user-shield fs-4"></i>
                                    <h6 class="mt-2 mb-1">Quyền hạn</h6>
                                    <strong><?= ucfirst($staff['role']) ?></strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Thông tin hệ thống -->
            <div class="stats-card mb-4">
                <div class="card-body">
                    <h5 class="card-title mb-3">
                        <i class="fas fa-cog text-secondary me-2"></i>
                        Thông tin hệ thống
                    </h5>

                    <div class="timeline">
                        <div class="timeline-item">
                            <div class="timeline-marker bg-primary"></div>
                            <div class="timeline-content">
                                <h6 class="mb-1">Ngày tạo tài khoản</h6>
                                <small class="text-muted">
                                    <i class="fas fa-calendar-plus me-1"></i>
                                    <?= date('d/m/Y H:i', strtotime($staff['created_at'])) ?>
                                </small>
                            </div>
                        </div>

                        <div class="timeline-item">
                            <div class="timeline-marker bg-warning"></div>
                            <div class="timeline-content">
                                <h6 class="mb-1">Cập nhật lần cuối</h6>
                                <small class="text-muted">
                                    <i class="fas fa-edit me-1"></i>
                                    <?= date('d/m/Y H:i', strtotime($staff['updated_at'])) ?>
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Hành động -->
            <div class="stats-card">
                <div class="card-body">
                    <h5 class="card-title mb-3">
                        <i class="fas fa-tools text-dark me-2"></i>
                        Hành động
                    </h5>

                    <div class="d-grid gap-2">
                        <a href="/Quan_ly_trung_tam/public/staff/<?= $staff['id'] ?>/edit" class="btn btn-primary">
                            <i class="fas fa-edit me-2"></i>Sửa thông tin
                        </a>
                        
                        <?php if ($staff['status'] === 'active'): ?>
                            <button class="btn btn-warning" onclick="changeStatus('<?= $staff['id'] ?>', 'on_leave')">
                                <i class="fas fa-pause me-2"></i>Tạm dừng
                            </button>
                        <?php elseif ($staff['status'] === 'on_leave'): ?>
                            <button class="btn btn-success" onclick="changeStatus('<?= $staff['id'] ?>', 'active')">
                                <i class="fas fa-play me-2"></i>Kích hoạt
                            </button>
                        <?php endif; ?>

                        <button class="btn btn-danger" onclick="if(confirm('Bạn có chắc muốn xóa nhân viên &quot;<?= htmlspecialchars($staff['full_name']) ?>&quot;?\n\nHành động này không thể hoàn tác!')) { 
                            if(confirm('Bạn THỰC SỰ chắc chắn muốn xóa? Điều này sẽ xóa vĩnh viễn tất cả dữ liệu liên quan!')) {
                                fetch('/Quan_ly_trung_tam/public/staff/<?= $staff['id'] ?>/delete', {
                                    method: 'POST',
                                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                                    body: '_method=DELETE'
                                }).then(r => {
                                    if(r.ok) { window.location.href = '/Quan_ly_trung_tam/public/staff'; }
                                    else { alert('Có lỗi xảy ra khi xóa nhân viên!'); }
                                }).catch(e => alert('Có lỗi xảy ra: ' + e.message));
                            }
                        }">
                            <i class="fas fa-trash me-2"></i>Xóa nhân viên
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();

// Custom CSS
$customCss = '
<style>
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline-item {
    position: relative;
    margin-bottom: 20px;
}

.timeline-item:last-child {
    margin-bottom: 0;
}

.timeline-marker {
    position: absolute;
    left: -35px;
    top: 2px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    border: 2px solid #fff;
    box-shadow: 0 0 0 2px #dee2e6;
}

.timeline::before {
    content: "";
    position: absolute;
    left: -30px;
    top: 8px;
    bottom: 0;
    width: 2px;
    background: #dee2e6;
}

.info-card {
    border-radius: 10px;
    transition: transform 0.2s;
}

.info-card:hover {
    transform: translateY(-2px);
}
</style>';

// Custom JavaScript
$customJs = '
<script>
// Define functions in global scope
window.changeStatus = function(staffId, newStatus) {
    const statusText = {
        "active": "kích hoạt",
        "on_leave": "tạm dừng",
        "inactive": "vô hiệu hóa"
    };
    
    if (confirm(`Bạn có chắc muốn ${statusText[newStatus]} nhân viên này?`)) {
        fetch(`/Quan_ly_trung_tam/public/staff/${staffId}/update`, {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded",
            },
            body: `_method=PUT&status=${newStatus}`
        })
        .then(response => {
            if (response.ok) {
                location.reload();
            } else {
                alert("Có lỗi xảy ra khi cập nhật trạng thái!");
            }
        })
        .catch(error => {
        });
    }
};
</script>';

// Render layout
useModernLayout('Chi tiết nhân viên', $content);
?>