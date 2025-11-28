<?php
require_once __DIR__ . '/../layouts/main.php';

// Compute base path
$scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
$scriptDir = str_replace('\\', '/', dirname($scriptName));
$certBasePath = rtrim($scriptDir, '/');
if ($certBasePath === '' || $certBasePath === '.') {
    $certBasePath = '';
}

// Start content buffering
ob_start();

// Page header
$headerTitle = 'Yêu cầu cấp chứng nhận';
$headerDesc = 'Gửi yêu cầu cấp chứng nhận cho học viên hoàn thành khóa học';
$headerButton = '<a href="' . $certBasePath . '/certificates" class="btn btn-outline-secondary">
    <i class="fas fa-arrow-left me-2"></i>Quay lại
</a>';

echo pageHeader($headerTitle, $headerDesc, $headerButton);
?>

<div class="p-3">

    <div class="stats-card">
        <div class="card-body">
            <h6 class="card-title mb-4">
                <i class="fas fa-edit text-primary me-2"></i>
                Thông tin yêu cầu chứng nhận
            </h6>

            <form method="POST" action="<?= $certBasePath ?>/certificates" id="certificateForm">
                <div class="row g-4">
                    <!-- Row 1: Tên học viên và Tài khoản -->
                    <div class="col-md-6">
                        <label for="student_name" class="form-label">
                            <i class="fas fa-user text-primary me-1"></i>
                            Tên học viên <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control" id="student_name" name="student_name" 
                               value="<?= $old_data['student_name'] ?? '' ?>" required 
                               placeholder="Nhập họ tên học viên">
                    </div>

                    <div class="col-md-6">
                        <label for="username" class="form-label">
                            <i class="fas fa-user-circle text-info me-1"></i>
                            Tài khoản đăng nhập (Mặc định là SĐT) <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control" id="username" name="username" 
                               value="<?= $old_data['username'] ?? '' ?>" required
                               placeholder="Mặc định là số điện thoại">
                    </div>

                    <!-- Row 2: Số điện thoại và Bộ môn -->
                    <div class="col-md-6">
                        <label for="phone" class="form-label">
                            <i class="fas fa-phone text-success me-1"></i>
                            Số điện thoại <span class="text-danger">*</span>
                        </label>
                        <input type="tel" class="form-control" id="phone" name="phone" 
                               value="<?= $old_data['phone'] ?? '' ?>" required 
                               placeholder="Nhập số điện thoại" maxlength="11" pattern="[0-9]{10,11}">
                        <small class="text-muted">Nhập 10-11 chữ số</small>
                    </div>

                    <div class="col-md-6">
                        <label for="subject" class="form-label">
                            <i class="fas fa-book text-warning me-1"></i>
                            Bộ môn <span class="text-danger">*</span>
                        </label>
                        <select class="form-select" id="subject" name="subject" required>
                            <option value="">-- Chọn bộ môn --</option>
                            <option value="Tin học văn phòng" <?= (isset($old_data['subject']) && $old_data['subject'] == 'Tin học văn phòng') ? 'selected' : '' ?>>Tin học văn phòng</option>
                            <option value="Trí tuệ nhân tạo (AI)" <?= (isset($old_data['subject']) && $old_data['subject'] == 'Trí tuệ nhân tạo (AI)') ? 'selected' : '' ?>>Trí tuệ nhân tạo (AI)</option>
                            <option value="Kế toán" <?= (isset($old_data['subject']) && $old_data['subject'] == 'Kế toán') ? 'selected' : '' ?>>Kế toán</option>
                            <option value="Thiết kế đồ họa" <?= (isset($old_data['subject']) && $old_data['subject'] == 'Thiết kế đồ họa') ? 'selected' : '' ?>>Thiết kế đồ họa</option>
                            <option value="Vẽ kỹ thuật" <?= (isset($old_data['subject']) && $old_data['subject'] == 'Vẽ kỹ thuật') ? 'selected' : '' ?>>Vẽ kỹ thuật</option>
                            <option value="Tin học trẻ em" <?= (isset($old_data['subject']) && $old_data['subject'] == 'Tin học trẻ em') ? 'selected' : '' ?>>Tin học trẻ em</option>
                            <option value="Lập trình" <?= (isset($old_data['subject']) && $old_data['subject'] == 'Lập trình') ? 'selected' : '' ?>>Lập trình</option>
                            <option value="MOS" <?= (isset($old_data['subject']) && $old_data['subject'] == 'MOS') ? 'selected' : '' ?>>MOS</option>
                            <option value="IC3" <?= (isset($old_data['subject']) && $old_data['subject'] == 'IC3') ? 'selected' : '' ?>>IC3</option>
                            <option value="Quảng cáo" <?= (isset($old_data['subject']) && $old_data['subject'] == 'Quảng cáo') ? 'selected' : '' ?>>Quảng cáo</option>
                        </select>
                    </div>

                    <!-- Row 3: Email và Ghi chú -->
                    <div class="col-md-6">
                        <label for="email" class="form-label">
                            <i class="fas fa-envelope text-danger me-1"></i>
                            Email <span class="text-danger">*</span>
                        </label>
                        <input type="email" class="form-control" id="email" name="email" 
                               value="<?= $old_data['email'] ?? '' ?>" required
                               placeholder="Nhập địa chỉ email">
                        <small class="text-muted">
                            <i class="fas fa-info-circle me-1"></i>
                            Sẽ có gmail gửi về khi chứng nhận được cấp.
                        </small>
                    </div>

                    <div class="col-md-6">
                        <label for="notes" class="form-label">
                            <i class="fas fa-sticky-note text-secondary me-1"></i>
                            Ghi chú
                        </label>
                        <textarea class="form-control" id="notes" name="notes" rows="3" 
                                  placeholder="Nhập ghi chú (nếu có)"><?= $old_data['notes'] ?? '' ?></textarea>
                    </div>
                </div>

                <!-- Alert box -->
                <div class="alert alert-info d-flex align-items-start mt-4 mb-4" role="alert">
                    <i class="fas fa-info-circle fs-5 me-3 mt-1"></i>
                    <div>
                        <strong>Lưu ý:</strong> Yêu cầu sẽ được gửi với trạng thái "Chờ duyệt" và cần được admin phê duyệt.
                    </div>
                </div>

                <!-- Action buttons -->
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-paper-plane me-2"></i>Gửi yêu cầu
                    </button>
                    <a href="<?= $certBasePath ?>/certificates" class="btn btn-secondary">
                        <i class="fas fa-times me-2"></i>Hủy
                    </a>
                </div>
            </form>
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
    const username = document.getElementById('username').value.trim();
    const phone = document.getElementById('phone').value.trim();
    const subject = document.getElementById('subject').value.trim();
    const email = document.getElementById('email').value.trim();

    if (!studentName || !username || !phone || !subject || !email) {
        e.preventDefault();
        alert('Vui lòng điền đầy đủ thông tin bắt buộc (*)');
        return false;
    }

    if (phone.length < 10) {
        e.preventDefault();
        alert('Số điện thoại phải có ít nhất 10 chữ số');
        return false;
    }

    // Validate email format
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
        e.preventDefault();
        alert('Vui lòng nhập địa chỉ email hợp lệ');
        return false;
    }
});
<?php if (isset($error)): ?>
<?php $_SESSION['error'] = $error; ?>
<?php endif; ?>
</script>

<?php
$content = ob_get_clean();
useModernLayout('Yêu cầu cấp chứng nhận', $content);
?>
