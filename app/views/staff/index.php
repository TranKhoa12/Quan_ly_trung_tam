<?php
require_once __DIR__ . '/../layouts/main.php';

// Page content
ob_start();
?>

<?= pageHeader(
    'Quản lý nhân viên', 
    'Quản lý thông tin và theo dõi hiệu suất nhân viên',
    '<a href="/Quan_ly_trung_tam/public/staff/create" class="btn btn-primary">
        <i class="fas fa-plus me-2"></i>Thêm nhân viên mới
    </a>'
) ?>

<div class="p-3">
    <!-- Statistics Cards -->
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-6">
            <?= statsCard('fas fa-users', 'Tổng nhân viên', $stats['total'], 'Tất cả nhân viên', 'primary') ?>
        </div>
        <div class="col-xl-3 col-md-6">
            <?= statsCard('fas fa-user-check', 'Đang làm việc', $stats['active'], 'Nhân viên hoạt động', 'success') ?>
        </div>
        <div class="col-xl-3 col-md-6">
            <?= statsCard('fas fa-user-plus', 'Mới tháng này', $stats['new_this_month'], 'Nhân viên mới', 'info') ?>
        </div>
        <div class="col-xl-3 col-md-6">
            <?= statsCard('fas fa-building', 'Phòng ban', count($departments), 'Số phòng ban', 'warning') ?>
        </div>
    </div>
    
    <!-- Filters -->
    <div class="stats-card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">
                        <i class="fas fa-search"></i> Tìm kiếm
                    </label>
                    <input type="text" class="form-control" name="search" 
                           value="<?= htmlspecialchars($search) ?>" 
                           placeholder="Tên, email, số điện thoại...">
                </div>
                <div class="col-md-3">
                    <label class="form-label">
                        <i class="fas fa-building"></i> Phòng ban
                    </label>
                    <select class="form-select" name="department">
                        <option value="">Tất cả phòng ban</option>
                        <?php foreach ($departments as $dept): ?>
                            <option value="<?= htmlspecialchars($dept) ?>" <?= $department === $dept ? 'selected' : '' ?>>
                                <?= htmlspecialchars($dept) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">
                        <i class="fas fa-toggle-on"></i> Trạng thái
                    </label>
                    <select class="form-select" name="status">
                        <option value="">Tất cả trạng thái</option>
                        <option value="active" <?= $status === 'active' ? 'selected' : '' ?>>Đang làm việc</option>
                        <option value="inactive" <?= $status === 'inactive' ? 'selected' : '' ?>>Nghỉ việc</option>
                        <option value="suspended" <?= $status === 'suspended' ? 'selected' : '' ?>>Tạm dừng</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter"></i> Lọc
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Staff List -->
    <div class="stats-card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="mb-0">
                    <i class="fas fa-list me-2"></i>Danh sách nhân viên
                </h6>
                <span class="badge bg-light text-dark"><?= count($staffList) ?> nhân viên</span>
            </div>
            
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th class="border-0">Nhân viên</th>
                            <th class="border-0">Liên hệ</th>
                            <th class="border-0">Phòng ban</th>
                            <th class="border-0">Ngày vào làm</th>
                            <th class="border-0">Lương</th>
                            <th class="border-0">Trạng thái</th>
                            <th class="border-0">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($staffList)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <div class="empty-state">
                                        <i class="fas fa-users text-muted fa-4x mb-3 opacity-50"></i>
                                        <h6 class="text-muted mb-2">Không có nhân viên nào</h6>
                                        <p class="text-muted small mb-3">Thêm nhân viên đầu tiên để bắt đầu quản lý</p>
                                        <a href="/Quan_ly_trung_tam/public/staff/create" class="btn btn-primary">
                                            <i class="fas fa-plus me-2"></i>Thêm nhân viên đầu tiên
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($staffList as $staff): ?>
                                <?php 
                                $statusClass = $staff['status'] === 'active' ? 'success' : 
                                              ($staff['status'] === 'inactive' ? 'secondary' : 'warning');
                                $statusText = $staff['status'] === 'active' ? 'Đang làm việc' : 
                                             ($staff['status'] === 'inactive' ? 'Nghỉ việc' : 'Tạm dừng');
                                ?>
                                <tr class="staff-row">
                                    <td class="border-0">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-circle bg-primary text-white me-3">
                                                <?= strtoupper(substr($staff['full_name'], 0, 1)) ?>
                                            </div>
                                            <div>
                                                <div class="fw-semibold text-dark"><?= htmlspecialchars($staff['full_name']) ?></div>
                                                <small class="text-muted"><?= htmlspecialchars($staff['username']) ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="border-0">
                                        <div class="text-dark"><?= htmlspecialchars($staff['email']) ?></div>
                                        <small class="text-muted"><?= htmlspecialchars($staff['phone'] ?? 'Chưa cập nhật') ?></small>
                                    </td>
                                    <td class="border-0">
                                        <span class="badge bg-light text-dark"><?= htmlspecialchars($staff['department'] ?? 'Chưa phân công') ?></span>
                                    </td>
                                    <td class="border-0">
                                        <?= $staff['hire_date'] ? date('d/m/Y', strtotime($staff['hire_date'])) : 'Chưa cập nhật' ?>
                                    </td>
                                    <td class="border-0">
                                        <?= $staff['salary'] ? number_format($staff['salary'], 0, ',', '.') . ' ₫' : 'Chưa cập nhật' ?>
                                    </td>
                                    <td class="border-0">
                                        <span class="badge bg-<?= $statusClass ?>-subtle text-<?= $statusClass ?> px-3 py-2"><?= $statusText ?></span>
                                    </td>
                                    <td class="border-0">
                                        <div class="btn-group btn-group-sm">
                                            <a href="/Quan_ly_trung_tam/public/staff/<?= $staff['id'] ?>" 
                                               class="btn btn-outline-primary" title="Xem chi tiết">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="/Quan_ly_trung_tam/public/staff/<?= $staff['id'] ?>/edit" 
                                               class="btn btn-outline-success" title="Sửa">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button class="btn btn-outline-danger" 
                                                    onclick="deleteStaff(<?= $staff['id'] ?>)" title="Xóa">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();

// Custom CSS
$customCss = '
<style>
.avatar-circle {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 0.9rem;
}

.staff-row {
    transition: all 0.2s ease;
}

.staff-row:hover {
    background-color: rgba(var(--bs-primary-rgb), 0.04) !important;
    transform: scale(1.01);
}

.empty-state {
    transition: all 0.3s ease;
}

.empty-state:hover i {
    transform: scale(1.1);
    color: var(--bs-primary) !important;
}

.btn-group .btn {
    transition: all 0.2s ease;
}

.btn-group .btn:hover {
    transform: translateY(-1px);
}
</style>';

// Custom JavaScript
$customJs = '
<script>
function deleteStaff(id) {
    if (confirm("Bạn có chắc chắn muốn xóa nhân viên này?")) {
        fetch("/Quan_ly_trung_tam/public/staff/" + id, {
            method: "DELETE",
            headers: {
                "Content-Type": "application/json"
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert("Có lỗi xảy ra: " + data.message);
            }
        })
        .catch(error => {
            alert("Có lỗi xảy ra khi xóa nhân viên");
        });
    }
}

// Success/Error messages
const urlParams = new URLSearchParams(window.location.search);
if (urlParams.get("success") === "created") {
    alert("Đã thêm nhân viên thành công!");
}
if (urlParams.get("error")) {
    alert("Lỗi: " + decodeURIComponent(urlParams.get("error")));
}
</script>';

// Render layout
echo renderLayout('Quản lý nhân viên', $content, 'staff', $customCss, $customJs);
?>