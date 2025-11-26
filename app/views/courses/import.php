<?php
require_once __DIR__ . '/../layouts/main.php';

// Page content
ob_start();
?>

<?= pageHeader(
    'Import khóa học từ Excel', 
    'Tải lên file Excel để import hàng loạt khóa học',
    '<a href="/Quan_ly_trung_tam/public/courses" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-2"></i>Quay lại
    </a>'
) ?>

<div class="p-3">
    <!-- Success/Error Messages -->
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle me-2"></i>
            <?= htmlspecialchars($_SESSION['success_message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-circle me-2"></i>
            <?= htmlspecialchars($_SESSION['error_message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Import Instructions -->
            <div class="stats-card mb-4">
                <div class="card-body">
                    <h6 class="mb-3">
                        <i class="fas fa-info-circle text-primary me-2"></i>Hướng dẫn import file Excel
                    </h6>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-muted">Định dạng file Excel:</h6>
                            <ul class="list-unstyled">
                                <li><i class="fas fa-check text-success me-2"></i>File .xlsx hoặc .xls</li>
                                <li><i class="fas fa-check text-success me-2"></i>Dòng đầu tiên là tiêu đề cột</li>
                                <li><i class="fas fa-check text-success me-2"></i>Tối đa 1000 dòng</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted">Các cột bắt buộc:</h6>
                            <ul class="list-unstyled">
                                <li><i class="fas fa-asterisk text-danger me-2" style="font-size: 8px;"></i><strong>course_code</strong> - Mã khóa học</li>
                                <li><i class="fas fa-asterisk text-danger me-2" style="font-size: 8px;"></i><strong>course_name</strong> - Tên khóa học</li>
                            </ul>
                            
                            <h6 class="text-muted mt-3">Các cột tùy chọn:</h6>
                            <ul class="list-unstyled small text-muted">
                                <li>• description - Mô tả</li>
                                <li>• duration_hours - Thời lượng (giờ)</li>
                                <li>• price - Học phí</li>
                                <li>• start_date - Ngày bắt đầu (YYYY-MM-DD)</li>
                                <li>• end_date - Ngày kết thúc (YYYY-MM-DD)</li>
                                <li>• max_students - Số học viên tối đa</li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="mt-3">
                        <a href="/Quan_ly_trung_tam/public/courses/download-template" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-download me-2"></i>Tải file mẫu Excel
                        </a>
                    </div>
                </div>
            </div>

            <!-- Upload Form -->
            <div class="stats-card">
                <div class="card-body">
                    <h6 class="mb-3">
                        <i class="fas fa-upload text-primary me-2"></i>Tải lên file Excel
                    </h6>
                    
                    <form method="POST" action="/Quan_ly_trung_tam/public/courses/process-import" enctype="multipart/form-data">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">
                                    <i class="fas fa-file-excel text-primary me-1"></i>
                                    Chọn file Excel <span class="text-danger">*</span>
                                </label>
                                <input type="file" class="form-control" name="excel_file" required 
                                       accept=".xlsx,.xls">
                                <div class="form-text">
                                    Chỉ chấp nhận file .xlsx hoặc .xls, tối đa 10MB
                                </div>
                            </div>
                            
                            <div class="col-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="skip_duplicates" id="skip_duplicates" checked>
                                    <label class="form-check-label" for="skip_duplicates">
                                        Bỏ qua các khóa học đã tồn tại (trùng mã khóa học)
                                    </label>
                                </div>
                            </div>
                            
                            <div class="col-12">
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-success">
                                        <i class="fas fa-upload me-2"></i>Bắt đầu import
                                    </button>
                                    <a href="/Quan_ly_trung_tam/public/courses" class="btn btn-outline-secondary">
                                        <i class="fas fa-times me-2"></i>Hủy
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

<style>
.stats-card {
    border: none;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    background: white;
}
</style>

<?php
$content = ob_get_clean();

// Render layout
useModernLayout('Import khóa học từ Excel', $content);
?>