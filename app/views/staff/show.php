<?php
require_once __DIR__ . '/../layouts/main.php';

// Page content
ob_start();

// Get status info
$statusConfig = [
    'active' => ['class' => 'success', 'icon' => 'fa-check-circle', 'text' => 'Đang làm việc'],
    'inactive' => ['class' => 'danger', 'icon' => 'fa-times-circle', 'text' => 'Nghỉ việc'],
    'on_leave' => ['class' => 'warning', 'icon' => 'fa-pause-circle', 'text' => 'Tạm dừng']
];
$currentStatus = $statusConfig[$staff['status']] ?? ['class' => 'secondary', 'icon' => 'fa-question-circle', 'text' => 'Không xác định'];
?>

<style>
.profile-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 20px;
    padding: 40px;
    color: white;
    margin-bottom: 30px;
    box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
}

.profile-avatar {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    background: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 48px;
    font-weight: bold;
    color: #667eea;
    box-shadow: 0 8px 20px rgba(0,0,0,0.15);
    margin: 0 auto 20px;
}

.info-card {
    background: white;
    border-radius: 15px;
    padding: 25px;
    margin-bottom: 20px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    transition: all 0.3s ease;
}

.info-card:hover {
    box-shadow: 0 5px 20px rgba(0,0,0,0.12);
    transform: translateY(-2px);
}

.info-card-header {
    display: flex;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 2px solid #f0f0f0;
}

.info-card-header i {
    font-size: 24px;
    margin-right: 12px;
}

.info-card-header h5 {
    margin: 0;
    font-weight: 600;
    color: #2d3748;
}

.info-row {
    display: flex;
    padding: 15px 0;
    border-bottom: 1px solid #f7fafc;
}

.info-row:last-child {
    border-bottom: none;
}

.info-label {
    flex: 0 0 140px;
    color: #718096;
    font-size: 14px;
    font-weight: 500;
}

.info-value {
    flex: 1;
    color: #2d3748;
    font-weight: 600;
}

.info-value i {
    margin-right: 8px;
    color: #667eea;
}

.stat-box {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 15px;
    padding: 25px;
    color: white;
    text-align: center;
    margin-bottom: 20px;
    box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
}

.stat-box.green {
    background: linear-gradient(135deg, #56ab2f 0%, #a8e063 100%);
    box-shadow: 0 5px 15px rgba(86, 171, 47, 0.3);
}

.stat-box.orange {
    background: linear-gradient(135deg, #f46b45 0%, #eea849 100%);
    box-shadow: 0 5px 15px rgba(244, 107, 69, 0.3);
}

.stat-box-icon {
    font-size: 36px;
    margin-bottom: 10px;
    opacity: 0.9;
}

.stat-box-label {
    font-size: 14px;
    opacity: 0.9;
    margin-bottom: 5px;
}

.stat-box-value {
    font-size: 28px;
    font-weight: bold;
}

.action-btn {
    width: 100%;
    padding: 12px;
    border-radius: 10px;
    font-weight: 600;
    margin-bottom: 10px;
    border: none;
    transition: all 0.3s ease;
}

.action-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
}

.status-badge {
    display: inline-flex;
    align-items: center;
    padding: 8px 16px;
    border-radius: 20px;
    font-weight: 600;
    font-size: 14px;
}

.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 6px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: linear-gradient(180deg, #667eea 0%, #764ba2 100%);
}

.timeline-item {
    position: relative;
    padding-bottom: 25px;
}

.timeline-item:last-child {
    padding-bottom: 0;
}

.timeline-dot {
    position: absolute;
    left: -24px;
    top: 4px;
    width: 14px;
    height: 14px;
    border-radius: 50%;
    background: white;
    border: 3px solid #667eea;
}

.timeline-content {
    background: #f7fafc;
    padding: 12px 15px;
    border-radius: 10px;
}

.timeline-content h6 {
    margin: 0 0 5px 0;
    color: #2d3748;
    font-size: 14px;
    font-weight: 600;
}

.timeline-content small {
    color: #718096;
    font-size: 13px;
}
</style>

<div class="p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <a href="/Quan_ly_trung_tam/public/staff" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Quay lại
        </a>
    </div>

    <!-- Profile Header -->
    <div class="card mb-3">
        <div class="card-body p-3">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-1"><?= htmlspecialchars($staff['full_name']) ?></h5>
                    <small class="text-muted">
                        <i class="fas fa-at me-1"></i><?= htmlspecialchars($staff['username']) ?> | 
                        <i class="fas fa-envelope me-1"></i><?= htmlspecialchars($staff['email']) ?>
                    </small>
                </div>
                <span class="badge bg-<?= $currentStatus['class'] ?>">
                    <i class="fas <?= $currentStatus['icon'] ?> me-1"></i>
                    <?= $currentStatus['text'] ?>
                </span>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="row">
        <!-- Left Column - Main Info -->
        <div class="col-lg-8">
            <!-- Thông tin cơ bản -->
            <div class="info-card">
                <div class="info-card-header">
                    <i class="fas fa-user me-1"></i>Thông tin cơ bản
                </div>
                
                <div class="info-row">
                    <div class="info-label">Họ và tên</div>
                    <div class="info-value">
                        <i class="fas fa-user"></i>
                        <?= htmlspecialchars($staff['full_name']) ?>
                    </div>
                </div>
                
                <div class="info-row">
                    <div class="info-label">Tên đăng nhập</div>
                    <div class="info-value">
                        <i class="fas fa-at"></i>
                        <?= htmlspecialchars($staff['username']) ?>
                    </div>
                </div>
                
                <div class="info-row">
                    <div class="info-label">Email</div>
                    <div class="info-value">
                        <i class="fas fa-envelope"></i>
                        <a href="mailto:<?= htmlspecialchars($staff['email']) ?>" class="text-decoration-none text-primary">
                            <?= htmlspecialchars($staff['email']) ?>
                        </a>
                    </div>
                </div>
                
                <div class="info-row">
                    <div class="info-label">Số điện thoại</div>
                    <div class="info-value">
                        <i class="fas fa-phone"></i>
                        <?php if ($staff['phone']): ?>
                            <a href="tel:<?= htmlspecialchars($staff['phone']) ?>" class="text-decoration-none text-primary">
                                <?= htmlspecialchars($staff['phone']) ?>
                            </a>
                        <?php else: ?>
                            <span class="text-muted">Chưa cập nhật</span>
                        <?php endif; ?>
                    </div>
                </div>
                
                <?php if ($staff['address']): ?>
                <div class="info-row">
                    <div class="info-label">Địa chỉ</div>
                    <div class="info-value">
                        <i class="fas fa-map-marker-alt"></i>
                        <?= htmlspecialchars($staff['address']) ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Thông tin công việc -->
            <div class="info-card">
                <div class="info-card-header">
                    <i class="fas fa-briefcase me-1"></i>Thông tin công việc
                </div>
                
                <div class="info-row">
                    <div class="info-label">Phòng ban</div>
                    <div class="info-value">
                        <i class="fas fa-building"></i>
                        <?= $staff['department'] ? htmlspecialchars($staff['department']) : '<span class="text-muted">Chưa phân công</span>' ?>
                    </div>
                </div>
                
                <div class="info-row">
                    <div class="info-label">Ngày vào làm</div>
                    <div class="info-value">
                        <i class="fas fa-calendar"></i>
                        <?= $staff['hire_date'] ? date('d/m/Y', strtotime($staff['hire_date'])) : '<span class="text-muted">Chưa cập nhật</span>' ?>
                    </div>
                </div>
                
                <div class="info-row">
                    <div class="info-label">Lương</div>
                    <div class="info-value">
                        <i class="fas fa-money-bill-wave"></i>
                        <?= $staff['salary'] ? number_format($staff['salary']) . ' VNĐ' : '<span class="text-muted">Chưa cập nhật</span>' ?>
                    </div>
                </div>
                
                <?php if ($staff['hire_date']): ?>
                <div class="info-row">
                    <div class="info-label">Thời gian làm việc</div>
                    <div class="info-value">
                        <i class="fas fa-clock"></i>
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

            <!-- Ghi chú -->
            <?php if ($staff['notes']): ?>
            <div class="info-card">
                <div class="info-card-header">
                    <i class="fas fa-sticky-note me-1"></i>Ghi chú
                </div>
                <div class="p-2 small">
                    <?= nl2br(htmlspecialchars($staff['notes'])) ?>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Thống kê nhanh -->
            <div class="info-card">
                <div class="info-card-header">Thông tin khác</div>
                <div class="info-row">
                    <div class="info-label">ID Nhân viên</div>
                    <div class="info-value">#<?= $staff['id'] ?></div>
                </div>
                <div class="info-row">
                    <div class="info-label">Quyền hạn</div>
                    <div class="info-value"><?= ucfirst($staff['role']) ?></div>
                </div>
            </div>

            <!-- Thông tin hệ thống -->
            <div class="info-card">
                <div class="info-card-header">
                    <i class="fas fa-clock me-1"></i>Lịch sử
                </div>
                <div class="info-row">
                    <div class="info-label">Ngày tạo</div>
                    <div class="info-value"><?= date('d/m/Y H:i', strtotime($staff['created_at'])) ?></div>
                </div>
                <div class="info-row">
                    <div class="info-label">Cập nhật</div>
                    <div class="info-value"><?= date('d/m/Y H:i', strtotime($staff['updated_at'])) ?></div>
                </div>
            </div>

            <!-- Hành động -->
            <div class="info-card">
                <div class="info-card-header">
                    <i class="fas fa-cog me-1"></i>Hành động
                </div>
                <div class="d-grid gap-2 p-2">
                    <a href="/Quan_ly_trung_tam/public/staff/<?= $staff['id'] ?>/edit" class="btn btn-primary">
                        <i class="fas fa-edit me-2"></i>Chỉnh sửa
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

<?php
$content = ob_get_clean();

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
            alert("Có lỗi xảy ra: " + error.message);
        });
    }
};
</script>';

// Render layout
useModernLayout('Chi tiết nhân viên', $content);
?>