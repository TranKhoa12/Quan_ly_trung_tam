<?php
$pageTitle = 'Yêu cầu cấp chứng nhận';

// Compute base path
$scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
$scriptDir = str_replace('\\', '/', dirname($scriptName));
$certBasePath = rtrim($scriptDir, '/');
if ($certBasePath === '' || $certBasePath === '.') {
    $certBasePath = '';
}

// Start content buffering
ob_start();
?>

<!-- Page Header -->
<div class="welcome-section fade-in">
    <div class="row align-items-center mb-4">
        <div class="col-md-8">
            <div class="welcome-text">
                <h2 class="welcome-title">
                    <i class="fas fa-certificate text-warning me-2"></i>
                    Yêu cầu cấp chứng nhận
                </h2>
                <p class="welcome-subtitle">
                    Gửi yêu cầu cấp chứng nhận cho học viên hoàn thành khóa học
                </p>
            </div>
        </div>
        <div class="col-md-4 text-end">
            <a href="<?= $certBasePath ?>/certificates" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i>Quay lại
            </a>
        </div>
    </div>
</div>

<!-- Form Content -->
<div class="row slide-up">
    <div class="col-12">
        <div class="data-table">
            <div class="table-header">
                <h3 class="table-title">Thông tin yêu cầu chứng nhận</h3>
                <p class="table-subtitle">Điền đầy đủ thông tin học viên và khóa học đã hoàn thành</p>
            </div>
            
            <div class="table-content p-4">
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <?= htmlspecialchars($error) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <form method="POST" action="<?= $certBasePath ?>/certificates" id="certificateForm">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="student_name" class="form-label">
                                    <i class="fas fa-user text-primary me-1"></i>
                                    Tên học viên <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control form-control-lg" id="student_name" name="student_name" 
                                       value="<?= $old_data['student_name'] ?? '' ?>" required 
                                       placeholder="Nhập họ tên học viên">
                            </div>

                            <div class="mb-3">
                                <label for="username" class="form-label">
                                    <i class="fas fa-user-circle text-info me-1"></i>
                                    Tài khoản đăng nhập
                                </label>
                                <input type="text" class="form-control" id="username" name="username" 
                                       value="<?= $old_data['username'] ?? '' ?>" 
                                       placeholder="Nhập tài khoản đăng nhập">
                            </div>

                            <div class="mb-3">
                                <label for="phone" class="form-label">
                                    <i class="fas fa-phone text-success me-1"></i>
                                    Số điện thoại <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control" id="phone" name="phone" 
                                       value="<?= $old_data['phone'] ?? '' ?>" required 
                                       placeholder="Nhập số điện thoại">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="subject" class="form-label">
                                    <i class="fas fa-book text-warning me-1"></i>
                                    Bộ môn học <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control form-control-lg" id="subject" name="subject" 
                                       value="<?= $old_data['subject'] ?? '' ?>" required 
                                       placeholder="Nhập tên bộ môn học">
                            </div>

                            <div class="mb-3">
                                <label for="receive_status" class="form-label">
                                    <i class="fas fa-check-circle text-info me-1"></i>
                                    Tình trạng học viên nhận
                                </label>
                                <select class="form-select" id="receive_status" name="receive_status">
                                    <option value="not_received" <?= (!isset($old_data['receive_status']) || $old_data['receive_status'] == 'not_received') ? 'selected' : '' ?>>
                                        Chưa nhận
                                    </option>
                                    <option value="received" <?= (isset($old_data['receive_status']) && $old_data['receive_status'] == 'received') ? 'selected' : '' ?>>
                                        Đã nhận
                                    </option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="notes" class="form-label">
                                    <i class="fas fa-sticky-note text-secondary me-1"></i>
                                    Ghi chú
                                </label>
                                <textarea class="form-control" id="notes" name="notes" rows="4" 
                                          placeholder="Nhập ghi chú (nếu có)"><?= $old_data['notes'] ?? '' ?></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-info d-flex align-items-center mt-4" role="alert">
                        <i class="fas fa-info-circle fs-4 me-3"></i>
                        <div>
                            <strong>Lưu ý:</strong> Yêu cầu sẽ được gửi với trạng thái "Đang đợi" và cần được admin phê duyệt.
                        </div>
                    </div>

                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-success btn-lg">
                            <i class="fas fa-paper-plane me-2"></i>Gửi yêu cầu
                        </button>
                        <a href="<?= $certBasePath ?>/certificates" class="btn btn-secondary btn-lg">
                            <i class="fas fa-times me-2"></i>Hủy
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Auto-format phone number
document.getElementById('phone').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    if (value.length > 10) {
        value = value.slice(0, 10);
    }
    e.target.value = value;
});

// Validate form before submit
document.getElementById('certificateForm').addEventListener('submit', function(e) {
    const studentName = document.getElementById('student_name').value.trim();
    const phone = document.getElementById('phone').value.trim();
    const subject = document.getElementById('subject').value.trim();

    if (!studentName || !phone || !subject) {
        e.preventDefault();
        alert('Vui lòng điền đầy đủ thông tin bắt buộc (*)');
        return false;
    }

    if (phone.length < 10) {
        e.preventDefault();
        alert('Số điện thoại phải có ít nhất 10 chữ số');
        return false;
    }
});

// Add smooth transitions
document.querySelectorAll('.form-control, .form-select').forEach(el => {
    el.addEventListener('focus', function() {
        this.style.transform = 'scale(1.01)';
        this.style.transition = 'all 0.2s ease';
    });
    el.addEventListener('blur', function() {
        this.style.transform = 'scale(1)';
    });
});
</script>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/main.php';
useModernLayout($pageTitle, $content);
?>
