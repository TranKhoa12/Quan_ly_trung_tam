<?php
$pageTitle = 'Thêm học viên hoàn thành khóa học';

// Compute base path
$scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
$scriptDir = str_replace('\\', '/', dirname($scriptName));
$studentBasePath = rtrim($scriptDir, '/');
if ($studentBasePath === '' || $studentBasePath === '.') {
    $studentBasePath = '';
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
                    <i class="fas fa-graduation-cap text-primary me-2"></i>
                    Thêm học viên hoàn thành khóa học
                </h2>
                <p class="welcome-subtitle">
                    Nhập thông tin học viên đã hoàn thành khóa học để quản lý và theo dõi
                </p>
            </div>
        </div>
        <div class="col-md-4 text-end">
            <a href="<?= $studentBasePath ?>/students" class="btn btn-outline-secondary">
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
                <h3 class="table-title">Thông tin học viên</h3>
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

                <form method="POST" action="<?= $studentBasePath ?>/students" enctype="multipart/form-data" id="studentForm">
                    <div class="row g-4">
                        <!-- Column 1 -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="full_name" class="form-label">
                                    <i class="fas fa-user text-primary me-1"></i>
                                    Họ tên học viên <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control form-control-lg" id="full_name" name="full_name" 
                                       value="<?= $old_data['full_name'] ?? '' ?>" required
                                       placeholder="Nhập họ tên đầy đủ">
                            </div>

                            <div class="mb-3">
                                <label for="phone" class="form-label">
                                    <i class="fas fa-phone text-success me-1"></i>
                                    Số điện thoại
                                </label>
                                <input type="text" class="form-control" id="phone" name="phone" 
                                       value="<?= $old_data['phone'] ?? '' ?>"
                                       placeholder="VD: 0901234567">
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label">
                                    <i class="fas fa-envelope text-info me-1"></i>
                                    Email
                                </label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?= $old_data['email'] ?? '' ?>"
                                       placeholder="email@example.com">
                            </div>

                            <div class="mb-3">
                                <label for="course_id" class="form-label">
                                    <i class="fas fa-book text-warning me-1"></i>
                                    Khóa học
                                </label>
                                <select class="form-select form-select-lg" id="course_id" name="course_id">
                                    <option value="">-- Chọn khóa học --</option>
                                    <?php if (!empty($courses)): ?>
                                        <?php foreach ($courses as $course): ?>
                                            <option value="<?= $course['id'] ?>" 
                                                    <?= (isset($old_data['course_id']) && $old_data['course_id'] == $course['id']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($course['course_name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>

                        <!-- Column 2 -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="instructor_id" class="form-label">
                                    <i class="fas fa-chalkboard-teacher text-secondary me-1"></i>
                                    Giảng viên phụ trách
                                </label>
                                <select class="form-select" id="instructor_id" name="instructor_id">
                                    <option value="">-- Chọn giảng viên --</option>
                                    <?php if (!empty($instructors)): ?>
                                        <?php foreach ($instructors as $instructor): ?>
                                            <option value="<?= $instructor['id'] ?>" 
                                                    <?= (isset($old_data['instructor_id']) && $old_data['instructor_id'] == $instructor['id']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($instructor['full_name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="enrollment_date" class="form-label">
                                    <i class="fas fa-calendar-plus text-primary me-1"></i>
                                    Ngày nhập học
                                </label>
                                <input type="date" class="form-control" id="enrollment_date" name="enrollment_date" 
                                       value="<?= $old_data['enrollment_date'] ?? '' ?>">
                            </div>

                            <div class="mb-3">
                                <label for="completion_date" class="form-label">
                                    <i class="fas fa-calendar-check text-success me-1"></i>
                                    Ngày hoàn thành <span class="text-danger">*</span>
                                </label>
                                <input type="date" class="form-control form-control-lg" id="completion_date" name="completion_date" 
                                       value="<?= $old_data['completion_date'] ?? date('Y-m-d') ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="status" class="form-label">
                                    <i class="fas fa-flag text-info me-1"></i>
                                    Trạng thái
                                </label>
                                <select class="form-select" id="status" name="status">
                                    <option value="studying" <?= (isset($old_data['status']) && $old_data['status'] == 'studying') ? 'selected' : '' ?>>
                                        📚 Đang học
                                    </option>
                                    <option value="completed" <?= (!isset($old_data['status']) || $old_data['status'] == 'completed') ? 'selected' : '' ?>>
                                        ✅ Hoàn thành
                                    </option>
                                    <option value="dropped" <?= (isset($old_data['status']) && $old_data['status'] == 'dropped') ? 'selected' : '' ?>>
                                        ❌ Bỏ học
                                    </option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="tracking_image" class="form-label">
                                    <i class="fas fa-image text-warning me-1"></i>
                                    Ảnh phiếu theo dõi học tập
                                </label>
                                <input type="file" class="form-control" id="tracking_image" name="tracking_image" 
                                       accept="image/*,.pdf">
                                <div class="form-text">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Chấp nhận file: JPG, PNG, PDF. Tối đa 5MB
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-info d-flex align-items-center mt-4" role="alert">
                        <i class="fas fa-lightbulb fs-4 me-3"></i>
                        <div>
                            <strong>Mẹo:</strong> Sau khi thêm học viên hoàn thành, bạn có thể tạo yêu cầu cấp chứng nhận cho học viên này tại phần "Chứng nhận".
                        </div>
                    </div>

                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-success btn-lg">
                            <i class="fas fa-save me-2"></i>Lưu thông tin
                        </button>
                        <a href="<?= $studentBasePath ?>/students" class="btn btn-secondary btn-lg">
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
const phoneInput = document.getElementById('phone');
if (phoneInput) {
    phoneInput.addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        if (value.length > 10) {
            value = value.slice(0, 10);
        }
        e.target.value = value;
    });
}

// Validate file size
const fileInput = document.getElementById('tracking_image');
if (fileInput) {
    fileInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file && file.size > 5 * 1024 * 1024) {
            alert('File quá lớn! Vui lòng chọn file nhỏ hơn 5MB.');
            e.target.value = '';
        }
    });
}

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
