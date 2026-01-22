<?php
require_once __DIR__ . '/../layouts/main.php';

// Page content
ob_start();
?>

<?= pageHeader(
    'Thêm nhân viên mới', 
    'Nhập thông tin để thêm nhân viên vào hệ thống',
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
                        <i class="fas fa-user-plus text-primary me-2"></i>
                        Thông tin nhân viên
                    </h5>

                    <form method="POST" action="/Quan_ly_trung_tam/public/staff" id="staffForm">
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
                                       value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>"
                                       placeholder="Nhập họ và tên đầy đủ">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">
                                    <i class="fas fa-at text-primary"></i> Tên đăng nhập *
                                </label>
                                <input type="text" class="form-control" name="username" required
                                       value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                                       placeholder="Tên đăng nhập (không dấu, không khoảng trắng)">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">
                                    <i class="fas fa-envelope text-primary"></i> Email *
                                </label>
                                <input type="email" class="form-control" name="email" required
                                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                                       placeholder="email@example.com">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">
                                    <i class="fas fa-phone text-primary"></i> Số điện thoại
                                </label>
                                <input type="tel" class="form-control" name="phone"
                                       value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>"
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
                                                <?= (($_POST['department'] ?? '') === $dept) ? 'selected' : '' ?>>
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
                                       value="<?= $_POST['hire_date'] ?? date('Y-m-d') ?>">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">
                                    <i class="fas fa-money-bill-wave text-primary"></i> Lương (VNĐ)
                                </label>
                                <input type="number" class="form-control" name="salary" min="0"
                                       value="<?= htmlspecialchars($_POST['salary'] ?? '') ?>"
                                       placeholder="15000000">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">
                                    <i class="fas fa-toggle-on text-primary"></i> Trạng thái
                                </label>
                                <select class="form-select" name="status">
                                    <option value="active" <?= (($_POST['status'] ?? 'active') === 'active') ? 'selected' : '' ?>>
                                        Đang làm việc
                                    </option>
                                    <option value="inactive" <?= (($_POST['status'] ?? '') === 'inactive') ? 'selected' : '' ?>>
                                        Nghỉ việc
                                    </option>
                                    <option value="on_leave" <?= (($_POST['status'] ?? '') === 'on_leave') ? 'selected' : '' ?>>
                                        Tạm dừng
                                    </option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">
                                    <i class="fas fa-user-shield text-primary"></i> Phân quyền
                                </label>
                                <select class="form-select" name="role">
                                    <option value="staff" <?= (($_POST['role'] ?? 'staff') === 'staff') ? 'selected' : '' ?>>Nhân viên</option>
                                    <option value="admin" <?= (($_POST['role'] ?? '') === 'admin') ? 'selected' : '' ?>>Quản trị viên</option>
                                </select>
                            </div>

                            <div class="col-12">
                                <label class="form-label">
                                    <i class="fas fa-map-marker-alt text-primary"></i> Địa chỉ
                                </label>
                                <textarea class="form-control" name="address" rows="3"
                                          placeholder="Nhập địa chỉ chi tiết"><?= htmlspecialchars($_POST['address'] ?? '') ?></textarea>
                            </div>

                            <!-- Bảo mật -->
                            <div class="col-12 mt-4">
                                <h6 class="border-bottom pb-2 mb-3">
                                    <i class="fas fa-lock text-warning me-2"></i>
                                    Thông tin bảo mật
                                </h6>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">
                                    <i class="fas fa-key text-primary"></i> Mật khẩu *
                                </label>
                                <input type="password" class="form-control" name="password" required
                                       placeholder="Nhập mật khẩu (tối thiểu 6 ký tự)">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">
                                    <i class="fas fa-key text-primary"></i> Xác nhận mật khẩu *
                                </label>
                                <input type="password" class="form-control" name="password_confirm" required
                                       placeholder="Nhập lại mật khẩu">
                            </div>

                            <div class="col-12">
                                <label class="form-label">
                                    <i class="fas fa-sticky-note text-primary"></i> Ghi chú
                                </label>
                                <textarea class="form-control" name="notes" rows="3"
                                          placeholder="Ghi chú thêm về nhân viên"><?= htmlspecialchars($_POST['notes'] ?? '') ?></textarea>
                            </div>

                            <!-- Buttons -->
                            <div class="col-12 mt-4">
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-2"></i>Thêm nhân viên
                                    </button>
                                    <a href="/Quan_ly_trung_tam/public/staff" class="btn btn-outline-secondary">
                                        <i class="fas fa-times me-2"></i>Hủy bỏ
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
<script>
document.getElementById("staffForm").addEventListener("submit", function(e) {
    const password = document.querySelector("input[name=\"password\"]").value;
    const passwordConfirm = document.querySelector("input[name=\"password_confirm\"]").value;
    const hireDate = document.querySelector("input[name=\"hire_date\"]").value;
    const salary = document.querySelector("input[name=\"salary\"]").value;
    
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
});

// Auto-generate username from full name
document.querySelector("input[name=\"full_name\"]").addEventListener("input", function() {
    const fullName = this.value;
    const username = fullName
        .toLowerCase()
        .normalize("NFD")
        .replace(/[\u0300-\u036f]/g, "")
        .replace(/[^a-z0-9]/g, "")
        .substring(0, 20);
    
    if (username && !document.querySelector("input[name=\"username\"]").value) {
        document.querySelector("input[name=\"username\"]").value = username;
    }
});
</script>';

// Render layout
useModernLayout('Thêm nhân viên mới', $content);
?>