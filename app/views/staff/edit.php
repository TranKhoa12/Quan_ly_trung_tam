<?php
require_once __DIR__ . '/../layouts/main.php';

// Page content
ob_start();
?>

<?= pageHeader(
    'Sửa thông tin nhân viên', 
    'Cập nhật thông tin cho nhân viên: ' . htmlspecialchars($staff['full_name']),
    '<a href="/Quan_ly_trung_tam/public/staff" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-2"></i>Quay lại
    </a>'
) ?>

<div class="p-3">
    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <?= htmlspecialchars($_GET['error']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="stats-card">
                <div class="card-body">
                    <h5 class="card-title mb-4">
                        <i class="fas fa-user-edit text-primary me-2"></i>
                        Thông tin nhân viên #<?= $staff['id'] ?>
                    </h5>

                    <form method="POST" action="/Quan_ly_trung_tam/public/staff/<?= $staff['id'] ?>/update" id="staffForm">
                        <input type="hidden" name="_method" value="PUT">
                        
                        <div class="row g-3">
                            <!-- Thông tin cơ bản -->
                            <div class="col-12">
                                <h6 class="border-bottom pb-2 mb-3">
                                    <i class="fas fa-info-circle text-info me-2"></i>
                                    Thông tin cơ bản
                                </h6>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">
                                    <i class="fas fa-user text-primary"></i> Họ và tên *
                                </label>
                                <input type="text" class="form-control" name="full_name" required
                                       value="<?= htmlspecialchars($staff['full_name']) ?>"
                                       placeholder="Nhập họ và tên đầy đủ">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">
                                    <i class="fas fa-at text-primary"></i> Tên đăng nhập *
                                </label>
                                <input type="text" class="form-control" name="username" required
                                       value="<?= htmlspecialchars($staff['username']) ?>"
                                       placeholder="Tên đăng nhập (không dấu, không khoảng trắng)">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">
                                    <i class="fas fa-envelope text-primary"></i> Email *
                                </label>
                                <input type="email" class="form-control" name="email" required
                                       value="<?= htmlspecialchars($staff['email']) ?>"
                                       placeholder="email@example.com">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">
                                    <i class="fas fa-phone text-primary"></i> Số điện thoại
                                </label>
                                <input type="tel" class="form-control" name="phone"
                                       value="<?= htmlspecialchars($staff['phone'] ?? '') ?>"
                                       placeholder="0912345678">
                            </div>

                            <!-- Thông tin công việc -->
                            <div class="col-12 mt-4">
                                <h6 class="border-bottom pb-2 mb-3">
                                    <i class="fas fa-briefcase text-success me-2"></i>
                                    Thông tin công việc
                                </h6>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">
                                    <i class="fas fa-building text-primary"></i> Phòng ban
                                </label>
                                <select class="form-select" name="department">
                                    <option value="">Chọn phòng ban</option>
                                    <?php foreach ($departments as $dept): ?>
                                        <option value="<?= htmlspecialchars($dept) ?>"
                                                <?= ($staff['department'] === $dept) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($dept) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">
                                    <i class="fas fa-calendar text-primary"></i> Ngày vào làm
                                </label>
                                <input type="date" class="form-control" name="hire_date"
                                       value="<?= $staff['hire_date'] ?>">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">
                                    <i class="fas fa-money-bill-wave text-primary"></i> Lương (VNĐ)
                                </label>
                                <input type="number" class="form-control" name="salary" min="0"
                                       value="<?= htmlspecialchars($staff['salary'] ?? '') ?>"
                                       placeholder="15000000">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">
                                    <i class="fas fa-toggle-on text-primary"></i> Trạng thái
                                </label>
                                <select class="form-select" name="status">
                                    <option value="active" <?= ($staff['status'] === 'active') ? 'selected' : '' ?>>
                                        Đang làm việc
                                    </option>
                                    <option value="inactive" <?= ($staff['status'] === 'inactive') ? 'selected' : '' ?>>
                                        Nghỉ việc
                                    </option>
                                    <option value="on_leave" <?= ($staff['status'] === 'on_leave') ? 'selected' : '' ?>>
                                        Tạm dừng
                                    </option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">
                                    <i class="fas fa-user-shield text-primary"></i> Phân quyền
                                </label>
                                <select class="form-select" name="role">
                                    <option value="staff" <?= ($staff['role'] === 'staff') ? 'selected' : '' ?>>Nhân viên</option>
                                    <option value="admin" <?= ($staff['role'] === 'admin') ? 'selected' : '' ?>>Quản trị viên</option>
                                </select>
                            </div>

                            <div class="col-12">
                                <label class="form-label">
                                    <i class="fas fa-map-marker-alt text-primary"></i> Địa chỉ
                                </label>
                                <textarea class="form-control" name="address" rows="3"
                                          placeholder="Nhập địa chỉ chi tiết"><?= htmlspecialchars($staff['address'] ?? '') ?></textarea>
                            </div>

                            <!-- Thông tin thống kê -->
                            <div class="col-12 mt-4">
                                <h6 class="border-bottom pb-2 mb-3">
                                    <i class="fas fa-chart-line text-info me-2"></i>
                                    Thông tin thống kê
                                </h6>
                            </div>

                            <div class="col-md-4">
                                <div class="info-card bg-light">
                                    <div class="p-3 text-center">
                                        <i class="fas fa-calendar-plus text-primary fs-4"></i>
                                        <h6 class="mt-2 mb-1">Ngày tạo</h6>
                                        <small class="text-muted">
                                            <?= date('d/m/Y H:i', strtotime($staff['created_at'])) ?>
                                        </small>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="info-card bg-light">
                                    <div class="p-3 text-center">
                                        <i class="fas fa-edit text-warning fs-4"></i>
                                        <h6 class="mt-2 mb-1">Cập nhật lần cuối</h6>
                                        <small class="text-muted">
                                            <?= date('d/m/Y H:i', strtotime($staff['updated_at'])) ?>
                                        </small>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="info-card bg-light">
                                    <div class="p-3 text-center">
                                        <i class="fas fa-clock text-success fs-4"></i>
                                        <h6 class="mt-2 mb-1">Thời gian làm việc</h6>
                                        <small class="text-muted">
                                            <?php
                                            if ($staff['hire_date']) {
                                                $start = new DateTime($staff['hire_date']);
                                                $now = new DateTime();
                                                $diff = $start->diff($now);
                                                echo $diff->y . ' năm ' . $diff->m . ' tháng';
                                            } else {
                                                echo 'Chưa cập nhật';
                                            }
                                            ?>
                                        </small>
                                    </div>
                                </div>
                            </div>

                            <!-- Đổi mật khẩu -->
                            <div class="col-12 mt-4">
                                <h6 class="border-bottom pb-2 mb-3">
                                    <i class="fas fa-lock text-warning me-2"></i>
                                    Đổi mật khẩu <small class="text-muted">(để trống nếu không đổi)</small>
                                </h6>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">
                                    <i class="fas fa-key text-primary"></i> Mật khẩu mới
                                </label>
                                <input type="password" class="form-control" name="password"
                                       placeholder="Nhập mật khẩu mới (tối thiểu 6 ký tự)">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">
                                    <i class="fas fa-key text-primary"></i> Xác nhận mật khẩu mới
                                </label>
                                <input type="password" class="form-control" name="password_confirm"
                                       placeholder="Nhập lại mật khẩu mới">
                            </div>

                            <div class="col-12">
                                <label class="form-label">
                                    <i class="fas fa-sticky-note text-primary"></i> Ghi chú
                                </label>
                                <textarea class="form-control" name="notes" rows="3"
                                          placeholder="Ghi chú thêm về nhân viên"><?= htmlspecialchars($staff['notes'] ?? '') ?></textarea>
                            </div>

                            <!-- Buttons -->
                            <div class="col-12 mt-4">
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-2"></i>Cập nhật
                                    </button>
                                    <a href="/Quan_ly_trung_tam/public/staff" class="btn btn-outline-secondary">
                                        <i class="fas fa-times me-2"></i>Hủy bỏ
                                    </a>
                                    <a href="/Quan_ly_trung_tam/public/staff/<?= $staff['id'] ?>" class="btn btn-outline-info">
                                        <i class="fas fa-eye me-2"></i>Xem chi tiết
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();

// Custom JavaScript
$customJs = '
document.getElementById("staffForm").addEventListener("submit", function(e) {
    console.log("Form submit triggered");
    console.log("Form action:", this.action);
    console.log("Form method:", this.method);
    
    const password = document.querySelector("input[name=\"password\"]").value;
    const passwordConfirm = document.querySelector("input[name=\"password_confirm\"]").value;
    const hireDate = document.querySelector("input[name=\"hire_date\"]").value;
    const salary = document.querySelector("input[name=\"salary\"]").value;
    
    // Validate password if provided
    if (password || passwordConfirm) {
        if (password !== passwordConfirm) {
            e.preventDefault();
            alert("Mật khẩu xác nhận không khớp!");
            return false;
        }
        
        if (password.length < 6) {
            e.preventDefault();
            alert("Mật khẩu phải có ít nhất 6 ký tự!");
            return false;
        }
    }
    
    // Validate hire date format if provided
    if (hireDate && hireDate.trim() !== "") {
        const datePattern = /^\d{4}-\d{2}-\d{2}$/;
        if (!datePattern.test(hireDate)) {
            e.preventDefault();
            alert("Ngày vào làm không đúng định dạng!");
            return false;
        }
    }
    
    // Validate salary if provided
    if (salary && salary.trim() !== "" && isNaN(salary)) {
        e.preventDefault();
        alert("Lương phải là số!");
        return false;
    }
    
    console.log("Form validation passed, submitting...");
});';

// Render layout
useModernLayout('Sửa nhân viên', $content);
?>