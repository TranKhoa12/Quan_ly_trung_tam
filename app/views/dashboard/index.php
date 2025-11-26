<?php
require_once __DIR__ . '/../layouts/main.php';

// Page content
ob_start();
?>

<!-- Include Dashboard CSS and Chart.js -->
<link rel="stylesheet" href="/assets/css/dashboard.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="dashboard-container">
    <div class="page-header">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="mb-1">
                        <i class="fas fa-tachometer-alt me-2"></i>
                        Dashboard Quản Trị
                    </h1>
                    <p class="subtitle mb-0">
                        Chào mừng bạn đến với hệ thống quản lý trung tâm - Toàn cảnh hoạt động hôm nay
                    </p>
                </div>
                <div class="col-md-4 text-md-end">
                    <div class="d-flex align-items-center justify-content-md-end gap-2 mt-3 mt-md-0">
                        <span class="badge badge-modern bg-success badge-pulse">
                            <i class="fas fa-circle me-1" style="font-size: 0.5rem;"></i>Hệ thống hoạt động
                        </span>
                        <span class="badge badge-modern bg-light text-dark">
                            <i class="fas fa-clock me-1"></i><?= date('d/m/Y H:i') ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid p-3">
    <!-- Statistics Cards with Animation -->
    <div class="row g-3 mb-4">
        <div class="col-md-3 col-sm-6">
            <div class="stats-card dashboard-card" data-aos="fade-up" data-aos-delay="100">
                <?= statsCard(
                    'fas fa-chart-line', 
                    'Báo cáo hôm nay', 
                    $stats['total_reports_today'], 
                    'Số báo cáo đã tạo trong ngày',
                    'primary',
                    $stats['total_reports_today'] > 0 ? '+' . $stats['total_reports_today'] . ' báo cáo' : 'Chưa có báo cáo'
                ) ?>
            </div>
        </div>
        
        <div class="col-md-3 col-sm-6">
            <div class="stats-card dashboard-card" data-aos="fade-up" data-aos-delay="200">
                <?= statsCard(
                    'fas fa-money-bill-wave', 
                    'Doanh thu hôm nay', 
                    number_format($stats['total_revenue_today']) . ' đ', 
                    'Tổng doanh thu trong ngày',
                    'success',
                    $stats['total_revenue_today'] > 0 ? 'Đã có giao dịch' : 'Chưa có giao dịch'
                ) ?>
            </div>
        </div>
        
        <div class="col-md-3 col-sm-6">
            <div class="stats-card dashboard-card" data-aos="fade-up" data-aos-delay="300">
                <?= statsCard(
                    'fas fa-graduation-cap', 
                    'Học viên đang học', 
                    $stats['total_students'], 
                    'Số học viên hiện tại',
                    'info',
                    $stats['total_students'] . ' học viên'
                ) ?>
            </div>
        </div>
        
        <div class="col-md-3 col-sm-6">
            <div class="stats-card dashboard-card" data-aos="fade-up" data-aos-delay="400">
                <?= statsCard(
                    'fas fa-certificate', 
                    'Chứng nhận chờ duyệt', 
                    $stats['pending_certificates'], 
                    'Yêu cầu cấp chứng nhận',
                    'warning',
                    $stats['pending_certificates'] > 0 ? 'Cần xử lý' : 'Đã xử lý hết'
                ) ?>
            </div>
        </div>
    </div>

    <!-- Charts and Recent Activities -->
    <div class="row g-3">
        <!-- Recent Reports with Enhanced Design -->
        <div class="col-lg-8">
            <div class="stats-card dashboard-card" data-aos="fade-right" data-aos-delay="500">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h6 class="card-title mb-0">
                            <i class="fas fa-clock text-primary me-2"></i>
                            Báo cáo gần đây
                        </h6>
                        <span class="badge bg-light text-dark">
                            <?= count($recent_reports ?? []) ?> báo cáo
                        </span>
                    </div>
                    
                    <?php if (!empty($recent_reports)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover dashboard-table">
                                <thead class="table-light">
                                    <tr>
                                        <th class="border-0">Ngày</th>
                                        <th class="border-0">Nhân viên</th>
                                        <th class="border-0">Số lượng đến</th>
                                        <th class="border-0">Số lượng chốt</th>
                                        <th class="border-0">Tỷ lệ chốt</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_reports as $report): ?>
                                        <tr class="dashboard-table-row">
                                            <td class="border-0">
                                                <div class="d-flex flex-column">
                                                    <span class="fw-semibold text-dark"><?= date('d/m/Y', strtotime($report['report_date'])) ?></span>
                                                    <small class="text-muted"><?= date('H:i', strtotime($report['report_time'])) ?></small>
                                                </div>
                                            </td>
                                            <td class="border-0">
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-sm bg-primary-subtle rounded-circle d-flex align-items-center justify-content-center me-2">
                                                        <i class="fas fa-user text-primary"></i>
                                                    </div>
                                                    <span class="fw-medium"><?= htmlspecialchars($report['staff_name']) ?></span>
                                                </div>
                                            </td>
                                            <td class="border-0">
                                                <span class="badge bg-info-subtle text-info px-3 py-2"><?= $report['total_visitors'] ?></span>
                                            </td>
                                            <td class="border-0">
                                                <span class="badge bg-success-subtle text-success px-3 py-2"><?= $report['total_registered'] ?></span>
                                            </td>
                                            <td class="border-0">
                                                <?php 
                                                $rate = $report['total_visitors'] > 0 ? 
                                                    ($report['total_registered'] / $report['total_visitors'] * 100) : 0;
                                                $color = $rate >= 50 ? 'success' : ($rate >= 20 ? 'warning' : 'danger');
                                                ?>
                                                <span class="badge bg-<?= $color ?>-subtle text-<?= $color ?> px-3 py-2 fw-semibold"><?= number_format($rate, 1) ?>%</span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="text-center mt-3">
                            <a href="/Quan_ly_trung_tam/public/reports" class="btn btn-outline-primary btn-sm px-4">
                                <i class="fas fa-eye me-2"></i>Xem tất cả báo cáo
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <div class="empty-state">
                                <i class="fas fa-chart-line text-muted fa-4x mb-3 opacity-50"></i>
                                <h6 class="text-muted mb-2">Chưa có báo cáo nào</h6>
                                <p class="text-muted small mb-3">Tạo báo cáo đầu tiên để bắt đầu theo dõi hiệu suất</p>
                                <a href="/Quan_ly_trung_tam/public/reports/create" class="btn btn-primary">
                                    <i class="fas fa-plus me-2"></i>Tạo báo cáo đầu tiên
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Enhanced Quick Actions & System Status -->
        <div class="col-lg-4">
            <!-- Quick Actions with better design -->
            <div class="stats-card dashboard-card" data-aos="fade-left" data-aos-delay="600">
                <div class="card-body">
                    <h6 class="card-title mb-3">
                        <i class="fas fa-bolt text-warning me-2"></i>
                        Hành động nhanh
                    </h6>
                    
                    <div class="d-grid gap-2">
                        <a href="/Quan_ly_trung_tam/public/reports/create" class="btn btn-primary quick-action-btn position-relative overflow-hidden">
                            <i class="fas fa-plus me-2"></i>Tạo báo cáo mới
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                +
                            </span>
                        </a>
                        <a href="/Quan_ly_trung_tam/public/revenue/create" class="btn btn-success quick-action-btn">
                            <i class="fas fa-money-bill me-2"></i>Nhập doanh thu
                        </a>
                        <a href="/Quan_ly_trung_tam/public/students/create" class="btn btn-info quick-action-btn">
                            <i class="fas fa-user-plus me-2"></i>Thêm học viên
                        </a>
                        <a href="/Quan_ly_trung_tam/public/certificates/create" class="btn btn-warning quick-action-btn">
                            <i class="fas fa-certificate me-2"></i>Yêu cầu chứng nhận
                        </a>
                    </div>
                </div>
            </div>

            <!-- Enhanced System Status -->
            <div class="stats-card dashboard-card mt-3" data-aos="fade-left" data-aos-delay="700">
                <div class="card-body">
                    <h6 class="card-title mb-3">
                        <i class="fas fa-server text-info me-2"></i>
                        Trạng thái hệ thống
                    </h6>
                    
                    <div class="system-status">
                        <div class="d-flex justify-content-between align-items-center mb-3 p-2 rounded bg-light">
                            <div class="d-flex align-items-center">
                                <div class="status-indicator bg-success me-2"></div>
                                <span class="fw-medium">Database</span>
                            </div>
                            <span class="badge bg-success-subtle text-success">
                                <i class="fas fa-check me-1"></i>Hoạt động
                            </span>
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center mb-3 p-2 rounded bg-light">
                            <div class="d-flex align-items-center">
                                <div class="status-indicator bg-success me-2"></div>
                                <span class="fw-medium">Server</span>
                            </div>
                            <span class="badge bg-success-subtle text-success">
                                <i class="fas fa-check me-1"></i>Ổn định
                            </span>
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center p-2 rounded bg-light">
                            <div class="d-flex align-items-center">
                                <div class="status-indicator bg-warning me-2"></div>
                                <span class="fw-medium">Backup</span>
                            </div>
                            <span class="badge bg-warning-subtle text-warning">
                                <i class="fas fa-clock me-1"></i>Tự động
                            </span>
                        </div>
                    </div>
                    
                    <!-- System Info -->
                    <div class="mt-3 pt-3 border-top">
                        <small class="text-muted d-block">Uptime: 15 ngày, 4 giờ</small>
                        <small class="text-muted d-block">Phiên bản: v2.1.0</small>
                        <small class="text-muted">Cập nhật cuối: <?= date('d/m/Y') ?></small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Thông tin tổng quan cho Admin -->
    <div class="row g-3 mt-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-light">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="card-title mb-0">
                            <i class="fas fa-chart-pie me-2"></i>
                            Tổng quan hệ thống
                        </h6>
                        <span class="badge bg-primary">Admin Dashboard</span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="text-center p-3 border rounded">
                                <i class="fas fa-users-cog fa-2x text-primary mb-2"></i>
                                <h6>Quản lý toàn diện</h6>
                                <p class="text-muted small mb-0">Xem và điều chỉnh tất cả hoạt động của trung tâm</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center p-3 border rounded">
                                <i class="fas fa-chart-line fa-2x text-success mb-2"></i>
                                <h6>Báo cáo chi tiết</h6>
                                <p class="text-muted small mb-0">Truy cập báo cáo từ tất cả nhân viên</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center p-3 border rounded">
                                <i class="fas fa-cogs fa-2x text-warning mb-2"></i>
                                <h6>Cấu hình hệ thống</h6>
                                <p class="text-muted small mb-0">Điều chỉnh cài đặt và phân quyền</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();

// Custom CSS for enhanced dashboard
$customCss = '
/* Dashboard Card Animations */
.dashboard-card {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    border: 1px solid rgba(0,0,0,0.08);
    box-shadow: 0 2px 4px rgba(0,0,0,0.04);
}

.dashboard-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.12);
    border-color: rgba(0,0,0,0.12);
}

/* Enhanced Table Styling */
.dashboard-table-row {
    transition: all 0.2s ease;
}

.dashboard-table-row:hover {
    background-color: rgba(var(--bs-primary-rgb), 0.04) !important;
    transform: scale(1.01);
}

/* Avatar styling */
.avatar-sm {
    width: 32px;
    height: 32px;
}

/* Quick Action Buttons */
.quick-action-btn {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    border-radius: 0.5rem;
    font-weight: 500;
    position: relative;
    overflow: hidden;
}

.quick-action-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(0,0,0,0.15);
}

.quick-action-btn:active {
    transform: translateY(0);
}

/* System Status Indicators */
.status-indicator {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% { opacity: 1; }
    50% { opacity: 0.5; }
    100% { opacity: 1; }
}

.system-status .badge {
    font-size: 0.75rem;
    padding: 0.375rem 0.75rem;
}

/* Performance Metrics */
.performance-metric {
    transition: all 0.3s ease;
    border: 1px solid rgba(0,0,0,0.06);
}

.performance-metric:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
}

.performance-icon {
    transition: transform 0.3s ease;
}

.performance-metric:hover .performance-icon {
    transform: scale(1.1);
}

/* Mini Chart */
.mini-chart {
    padding: 0 0.5rem;
}

.chart-bar {
    border-radius: 2px 2px 0 0;
    transition: all 0.3s ease;
    opacity: 0.8;
}

.chart-bar:hover {
    opacity: 1;
    transform: scaleY(1.1);
}

/* Empty State */
.empty-state {
    transition: all 0.3s ease;
}

.empty-state:hover i {
    transform: scale(1.1);
    color: var(--bs-primary) !important;
}

/* Badge enhancements */
.badge {
    font-weight: 500;
    letter-spacing: 0.025em;
}

/* Counter Animation */
@keyframes countUp {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.performance-metric [data-counter] {
    animation: countUp 0.6s ease-out;
}

/* Progress bars */
.progress {
    background-color: rgba(0,0,0,0.05);
}

.progress-bar {
    transition: width 1s ease-in-out;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .dashboard-card {
        margin-bottom: 1rem;
    }
    
    .performance-metric {
        margin-bottom: 1rem;
    }
    
    .mini-chart {
        height: 60px !important;
    }
}

/* Loading states */
.dashboard-card.loading {
    pointer-events: none;
    opacity: 0.7;
}

.dashboard-card.loading::after {
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
    animation: shimmer 1.5s infinite;
}

@keyframes shimmer {
    0% { transform: translateX(-100%); }
    100% { transform: translateX(100%); }
}
';

// Add JavaScript for enhanced interactions
$customJs = '
<script>
document.addEventListener("DOMContentLoaded", function() {
    // Counter animation
    const counters = document.querySelectorAll("[data-counter]");
    counters.forEach(counter => {
        const target = parseFloat(counter.getAttribute("data-counter"));
        let current = 0;
        const increment = target / 30;
        
        const updateCounter = () => {
            if (current < target) {
                current += increment;
                if (target > 1000) {
                    counter.textContent = Math.floor(current).toLocaleString();
                } else {
                    counter.textContent = current.toFixed(1);
                }
                requestAnimationFrame(updateCounter);
            } else {
                if (target > 1000) {
                    counter.textContent = target.toLocaleString();
                } else {
                    counter.textContent = target;
                }
            }
        };
        
        // Start animation when element is visible
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    updateCounter();
                    observer.unobserve(entry.target);
                }
            });
        });
        
        observer.observe(counter);
    });
    
    // Progress bar animation
    const progressBars = document.querySelectorAll(".progress-bar");
    progressBars.forEach(bar => {
        const width = bar.style.width;
        bar.style.width = "0%";
        setTimeout(() => {
            bar.style.width = width;
        }, 500);
    });
    
    // Quick action button effects
    const quickButtons = document.querySelectorAll(".quick-action-btn");
    quickButtons.forEach(btn => {
        btn.addEventListener("mouseenter", function() {
            this.style.transform = "translateY(-2px) scale(1.02)";
        });
        
        btn.addEventListener("mouseleave", function() {
            this.style.transform = "translateY(0) scale(1)";
        });
    });
    
    // Auto refresh indicators (optional)
    setInterval(() => {
        const timeSpans = document.querySelectorAll(".badge:contains(\"Cập nhật:\")");
        timeSpans.forEach(span => {
            if (span.textContent.includes("Cập nhật:")) {
                span.innerHTML = "<i class=\"fas fa-circle me-1\" style=\"font-size: 0.5rem;\"></i>Trực tuyến";
            }
        });
    }, 30000);
});
</script>
';

// Render layout with enhanced features
echo renderLayout('Dashboard', $content, 'dashboard', $customCss, $customJs);
?>