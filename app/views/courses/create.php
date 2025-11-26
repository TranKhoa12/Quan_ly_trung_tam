<?php
require_once __DIR__ . '/../layouts/main.php';

// Page content
ob_start();
?>

<?= pageHeader(
    'Thêm khóa học mới', 
    'Nhập thông tin khóa học mới',
    '<a href="/Quan_ly_trung_tam/public/courses" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-2"></i>Quay lại
    </a>'
) ?>

<div class="p-3">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="stats-card">
                <div class="card-body">
                    <?php if (isset($_SESSION['error_message'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            <?= htmlspecialchars($_SESSION['error_message']) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                        <?php unset($_SESSION['error_message']); ?>
                    <?php endif; ?>

                    <form method="POST" action="/Quan_ly_trung_tam/public/courses">
                        <div class="row g-3">
                            <!-- Mã khóa học -->
                            <div class="col-md-6">
                                <label class="form-label">
                                    <i class="fas fa-barcode text-primary me-1"></i>
                                    Mã khóa học <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control" name="course_code" required 
                                       placeholder="Ví dụ: ENG101">
                            </div>

                            <!-- Tên khóa học -->
                            <div class="col-md-6">
                                <label class="form-label">
                                    <i class="fas fa-book text-primary me-1"></i>
                                    Tên khóa học <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control" name="course_name" required 
                                       placeholder="Ví dụ: Tiếng Anh Giao Tiếp Cơ Bản">
                            </div>

                            <!-- Mô tả -->
                            <div class="col-12">
                                <label class="form-label">
                                    <i class="fas fa-align-left text-primary me-1"></i>
                                    Mô tả khóa học
                                </label>
                                <textarea class="form-control" name="description" rows="3" 
                                          placeholder="Mô tả chi tiết về khóa học..."></textarea>
                            </div>

                            <!-- Thời lượng -->
                            <div class="col-md-4">
                                <label class="form-label">
                                    <i class="fas fa-clock text-primary me-1"></i>
                                    Thời lượng (giờ)
                                </label>
                                <input type="number" class="form-control" name="duration" min="1" 
                                       placeholder="Ví dụ: 40">
                            </div>

                            <!-- Học phí -->
                            <div class="col-md-4">
                                <label class="form-label">
                                    <i class="fas fa-money-bill-wave text-primary me-1"></i>
                                    Học phí (VNĐ)
                                </label>
                                <input type="number" class="form-control" name="fee" min="0" 
                                       placeholder="Ví dụ: 5000000">
                            </div>

                            <!-- Số học viên tối đa -->
                            <div class="col-md-4">
                                <label class="form-label">
                                    <i class="fas fa-users text-primary me-1"></i>
                                    Số học viên tối đa
                                </label>
                                <input type="number" class="form-control" name="max_students" min="1" 
                                       placeholder="Ví dụ: 20">
                            </div>

                            <!-- Ngày bắt đầu -->
                            <div class="col-md-6">
                                <label class="form-label">
                                    <i class="fas fa-calendar-alt text-primary me-1"></i>
                                    Ngày bắt đầu
                                </label>
                                <input type="date" class="form-control" name="start_date">
                            </div>

                            <!-- Ngày kết thúc -->
                            <div class="col-md-6">
                                <label class="form-label">
                                    <i class="fas fa-calendar-check text-primary me-1"></i>
                                    Ngày kết thúc
                                </label>
                                <input type="date" class="form-control" name="end_date">
                            </div>

                            <!-- Trạng thái -->
                            <div class="col-md-12">
                                <label class="form-label">
                                    <i class="fas fa-toggle-on text-primary me-1"></i>
                                    Trạng thái
                                </label>
                                <select class="form-select" name="status">
                                    <option value="active">Đang hoạt động</option>
                                    <option value="inactive">Ngừng hoạt động</option>
                                    <option value="completed">Đã hoàn thành</option>
                                </select>
                            </div>

                            <!-- Buttons -->
                            <div class="col-12">
                                <hr class="my-4">
                                <div class="d-flex gap-2 justify-content-end">
                                    <a href="/Quan_ly_trung_tam/public/courses" class="btn btn-outline-secondary">
                                        <i class="fas fa-times me-2"></i>Hủy
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-2"></i>Lưu khóa học
                                    </button>
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

// Render layout
useModernLayout('Thêm khóa học mới', $content);
?>
