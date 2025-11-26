<?php
require_once __DIR__ . '/../layouts/main.php';

// Page content
ob_start();
?>

<?= pageHeader(
    'Chỉnh sửa yêu cầu chứng nhận', 
    'Cập nhật thông tin yêu cầu cấp chứng nhận', 
    '<a href="/Quan_ly_trung_tam/public/certificates" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-2"></i>Quay lại
    </a>'
) ?>

<div class="p-3">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="stats-card">
                <div class="card-body">
                    <h5 class="card-title mb-4">
                        <i class="fas fa-edit text-primary me-2"></i>
                        Chỉnh sửa thông tin yêu cầu
                    </h5>

                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <?= htmlspecialchars($_SESSION['error']) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                        <?php unset($_SESSION['error']); ?>
                    <?php endif; ?>

                    <form method="POST" action="/Quan_ly_trung_tam/public/certificates/<?= $certificate['id'] ?>/update">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="student_name" class="form-label">
                                    <i class="fas fa-user text-primary"></i> Tên học viên *
                                </label>
                                <input type="text" class="form-control" id="student_name" name="student_name" 
                                       value="<?= htmlspecialchars($certificate['student_name']) ?>" required>
                            </div>

                            <div class="col-md-6">
                                <label for="username" class="form-label">
                                    <i class="fas fa-user-circle text-info"></i> Tên đăng nhập
                                </label>
                                <input type="text" class="form-control" id="username" name="username" 
                                       value="<?= htmlspecialchars($certificate['username'] ?? '') ?>">
                            </div>

                            <div class="col-md-6">
                                <label for="phone" class="form-label">
                                    <i class="fas fa-phone text-success"></i> Số điện thoại
                                </label>
                                <input type="text" class="form-control" id="phone" name="phone" 
                                       value="<?= htmlspecialchars($certificate['phone'] ?? '') ?>">
                            </div>

                            <div class="col-md-6">
                                <label for="subject" class="form-label">
                                    <i class="fas fa-book text-warning"></i> Môn học *
                                </label>
                                <input type="text" class="form-control" id="subject" name="subject" 
                                       value="<?= htmlspecialchars($certificate['subject']) ?>" required>
                            </div>

                            <div class="col-12">
                                <label for="notes" class="form-label">
                                    <i class="fas fa-sticky-note text-secondary"></i> Ghi chú
                                </label>
                                <textarea class="form-control" id="notes" name="notes" rows="4"><?= htmlspecialchars($certificate['notes'] ?? '') ?></textarea>
                            </div>
                        </div>

                        <hr class="my-4">

                        <div class="d-flex gap-2 justify-content-end">
                            <a href="/Quan_ly_trung_tam/public/certificates" class="btn btn-secondary">
                                <i class="fas fa-times me-2"></i>Hủy
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Lưu thay đổi
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
useModernLayout('Chỉnh sửa yêu cầu chứng nhận', $content);
?>
