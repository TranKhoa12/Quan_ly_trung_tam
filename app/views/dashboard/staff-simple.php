<?php
// Capture content first
ob_start();
?>

<div class="page-header">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center">
        <div>
            <h1 class="h2 mb-2">Trang chủ</h1>
            <p class="text-muted mb-0">Chào mừng <?= htmlspecialchars($user['full_name']) ?> - Hôm nay là <?= date('d/m/Y') ?></p>
        </div>
        <div class="btn-toolbar mb-2 mb-md-0">
            <span class="badge bg-success"><i class="fas fa-circle me-1"></i>Đang hoạt động</span>
        </div>
    </div>
</div>

<div class="p-3">
    <!-- Quick stats -->
    <div class="row g-3 mb-4">
        <div class="col-md-3 col-sm-6">
            <div class="stats-card border-primary">
                <div class="card-body text-center">
                    <div class="d-flex align-items-center justify-content-center mb-3">
                        <i class="fas fa-file-alt text-primary me-2 fs-4"></i>
                        <span class="fw-semibold">Báo cáo hôm nay</span>
                    </div>
                    <div class="mb-2">
                        <span class="fs-2 fw-bold text-primary d-block"><?= $stats['my_reports_today'] ?? 0 ?></span>
                    </div>
                    <small class="text-muted">Số báo cáo bạn đã tạo</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="stats-card border-success">
                <div class="card-body text-center">
                    <div class="d-flex align-items-center justify-content-center mb-3">
                        <i class="fas fa-dollar-sign text-success me-2 fs-4"></i>
                        <span class="fw-semibold">Doanh thu hôm nay</span>
                    </div>
                    <div class="mb-2">
                        <span class="fs-2 fw-bold text-success d-block"><?= number_format($stats['my_revenue_today'] ?? 0) ?> VNĐ</span>
                    </div>
                    <small class="text-muted">Doanh thu từ báo cáo của bạn</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="stats-card border-info">
                <div class="card-body text-center">
                    <div class="d-flex align-items-center justify-content-center mb-3">
                        <i class="fas fa-eye text-info me-2 fs-4"></i>
                        <span class="fw-semibold">Lượt truy cập</span>
                    </div>
                    <div class="mb-2">
                        <span class="fs-2 fw-bold text-info d-block"><?= $stats['my_visitors_today'] ?? 0 ?></span>
                    </div>
                    <small class="text-muted">Lượt truy cập hôm nay</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="stats-card border-warning">
                <div class="card-body text-center">
                    <div class="d-flex align-items-center justify-content-center mb-3">
                        <i class="fas fa-certificate text-warning me-2 fs-4"></i>
                        <span class="fw-semibold">Chứng nhận chờ duyệt</span>
                    </div>
                    <div class="mb-2">
                        <span class="fs-2 fw-bold text-warning d-block"><?= $stats['my_certificates_pending'] ?? 0 ?></span>
                    </div>
                    <small class="text-muted">Cần xử lý</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Main features -->
    <div class="row g-3 mb-4">
        <div class="col-12">
            <h5 class="mb-3"><i class="fas fa-th-large me-2"></i>Chức năng chính</h5>
        </div>
        
        <!-- Quản lý học viên -->
        <div class="col-md-6 col-lg-4">
            <div class="card h-100 shadow-sm border-0" style="transition: all 0.3s ease;">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <div class="d-inline-flex align-items-center justify-content-center rounded-circle" 
                             style="width: 60px; height: 60px; background: linear-gradient(135deg, #4f46e5, #4f46e5dd);">
                            <i class="fas fa-users text-white fs-4"></i>
                        </div>
                    </div>
                    <h5 class="card-title fw-bold mb-2">Quản lý Học viên</h5>
                    <p class="card-text text-muted small mb-3">Thêm, sửa, xóa thông tin học viên và theo dõi tiến độ</p>
                    <div class="d-flex gap-2 justify-content-center flex-wrap">
                        <a href="/Quan_ly_trung_tam/public/students" class="btn btn-primary btn-sm">
                            <i class="fas fa-list me-1"></i>Danh sách
                        </a>
                        <a href="/Quan_ly_trung_tam/public/students/create" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus me-1"></i>Thêm mới
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quản lý khóa học -->
        <div class="col-md-6 col-lg-4">
            <div class="card h-100 shadow-sm border-0" style="transition: all 0.3s ease;">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <div class="d-inline-flex align-items-center justify-content-center rounded-circle" 
                             style="width: 60px; height: 60px; background: linear-gradient(135deg, #10b981, #10b981dd);">
                            <i class="fas fa-graduation-cap text-white fs-4"></i>
                        </div>
                    </div>
                    <h5 class="card-title fw-bold mb-2">Quản lý Khóa học</h5>
                    <p class="card-text text-muted small mb-3">Tạo và quản lý các khóa học, môn học</p>
                    <div class="d-flex gap-2 justify-content-center flex-wrap">
                        <a href="/Quan_ly_trung_tam/public/courses" class="btn btn-success btn-sm">
                            <i class="fas fa-list me-1"></i>Danh sách
                        </a>
                        <a href="/Quan_ly_trung_tam/public/courses/create" class="btn btn-success btn-sm">
                            <i class="fas fa-plus me-1"></i>Tạo mới
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Báo cáo học tập -->
        <div class="col-md-6 col-lg-4">
            <div class="card h-100 shadow-sm border-0" style="transition: all 0.3s ease;">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <div class="d-inline-flex align-items-center justify-content-center rounded-circle" 
                             style="width: 60px; height: 60px; background: linear-gradient(135deg, #06b6d4, #06b6d4dd);">
                            <i class="fas fa-chart-bar text-white fs-4"></i>
                        </div>
                    </div>
                    <h5 class="card-title fw-bold mb-2">Báo cáo Học tập</h5>
                    <p class="card-text text-muted small mb-3">Tạo và xem báo cáo tiến độ học viên</p>
                    <div class="d-flex gap-2 justify-content-center flex-wrap">
                        <a href="/Quan_ly_trung_tam/public/reports" class="btn btn-info btn-sm">
                            <i class="fas fa-eye me-1"></i>Xem báo cáo
                        </a>
                        <a href="/Quan_ly_trung_tam/public/reports/create" class="btn btn-info btn-sm">
                            <i class="fas fa-plus me-1"></i>Tạo mới
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Chứng nhận -->
        <div class="col-md-6 col-lg-4">
            <div class="card h-100 shadow-sm border-0" style="transition: all 0.3s ease;">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <div class="d-inline-flex align-items-center justify-content-center rounded-circle" 
                             style="width: 60px; height: 60px; background: linear-gradient(135deg, #f59e0b, #f59e0bdd);">
                            <i class="fas fa-certificate text-white fs-4"></i>
                        </div>
                    </div>
                    <h5 class="card-title fw-bold mb-2">Chứng nhận</h5>
                    <p class="card-text text-muted small mb-3">Tạo và quản lý chứng nhận hoàn thành</p>
                    <div class="d-flex gap-2 justify-content-center flex-wrap">
                        <a href="/Quan_ly_trung_tam/public/certificates" class="btn btn-warning btn-sm">
                            <i class="fas fa-list me-1"></i>Danh sách
                        </a>
                        <a href="/Quan_ly_trung_tam/public/certificates/create" class="btn btn-warning btn-sm">
                            <i class="fas fa-plus me-1"></i>Tạo mới
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Báo cáo doanh thu -->
        <div class="col-md-6 col-lg-4">
            <div class="card h-100 shadow-sm border-0" style="transition: all 0.3s ease;">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <div class="d-inline-flex align-items-center justify-content-center rounded-circle" 
                             style="width: 60px; height: 60px; background: linear-gradient(135deg, #10b981, #10b981dd);">
                            <i class="fas fa-dollar-sign text-white fs-4"></i>
                        </div>
                    </div>
                    <h5 class="card-title fw-bold mb-2">Báo cáo Doanh thu</h5>
                    <p class="card-text text-muted small mb-3">Xem báo cáo doanh thu từ khóa học</p>
                    <div class="d-flex gap-2 justify-content-center flex-wrap">
                        <a href="/Quan_ly_trung_tam/public/revenue" class="btn btn-success btn-sm">
                            <i class="fas fa-chart-line me-1"></i>Xem doanh thu
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Nhập liệu OCR -->
        <div class="col-md-6 col-lg-4">
            <div class="card h-100 shadow-sm border-0" style="transition: all 0.3s ease;">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <div class="d-inline-flex align-items-center justify-content-center rounded-circle" 
                             style="width: 60px; height: 60px; background: linear-gradient(135deg, #6b7280, #6b7280dd);">
                            <i class="fas fa-camera text-white fs-4"></i>
                        </div>
                    </div>
                    <h5 class="card-title fw-bold mb-2">Nhập liệu OCR</h5>
                    <p class="card-text text-muted small mb-3">Sử dụng OCR để nhập dữ liệu từ ảnh</p>
                    <div class="d-flex gap-2 justify-content-center flex-wrap">
                        <a href="/Quan_ly_trung_tam/public/ocr" class="btn btn-secondary btn-sm">
                            <i class="fas fa-upload me-1"></i>Nhập dữ liệu
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.15) !important;
}
</style>


<?php
$content = ob_get_clean();

// Load layout helper
require_once __DIR__ . '/../layouts/main.php';

// Render with layout
renderLayout('Trang chủ', $content, 'dashboard');
?>