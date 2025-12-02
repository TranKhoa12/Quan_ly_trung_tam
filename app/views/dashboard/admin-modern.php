<?php
$pageTitle = 'Dashboard Quản Trị';

$scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
$scriptDir = str_replace('\\', '/', dirname($scriptName));
$adminBasePath = rtrim($scriptDir, '/');
if ($adminBasePath === '' || $adminBasePath === '.') {
    $adminBasePath = '';
}

// Start content buffering
ob_start();
?>

<!-- Admin Welcome Section -->
<div class="welcome-section fade-in">
    <div class="row align-items-center mb-4">
        <div class="col-md-8">
            <div class="welcome-text">
                <h2 class="welcome-title">
                    <i class="fas fa-crown text-warning me-2"></i>
                    Chào mừng Admin, <span class="text-primary"><?= htmlspecialchars($user['full_name']) ?>!</span>
                </h2>
                <p class="welcome-subtitle">
                    Tổng quan hệ thống và hoạt động của trung tâm hôm nay - <?= date('l, d/m/Y', strtotime('today')) ?>
                </p>
            </div>
        </div>
        </div>
</div>

<!-- Admin Stats Grid -->
<div class="stats-grid slide-up">
    <div class="stat-card primary">
        <div class="stat-header">
            <span class="stat-title">Tổng báo cáo hôm nay</span>
            <div class="stat-icon primary">
                <i class="fas fa-chart-bar"></i>
            </div>
        </div>
        <div class="stat-value"><?= $stats['total_reports_today'] ?></div>
        <div class="stat-change <?= $stats['total_reports_today'] > 0 ? 'positive' : 'neutral' ?>">
            <i class="fas fa-<?= $stats['total_reports_today'] > 0 ? 'arrow-up' : 'minus' ?> me-1"></i>
            <?= $stats['total_reports_today'] > 0 ? '+' . $stats['total_reports_today'] . ' báo cáo mới' : 'Chưa có báo cáo' ?>
        </div>
    </div>

    <div class="stat-card success">
        <div class="stat-header">
            <span class="stat-title">Tổng doanh thu hôm nay</span>
            <div class="stat-icon success">
                <i class="fas fa-money-bill-trend-up"></i>
            </div>
        </div>
        <div class="stat-value"><?= number_format($stats['total_revenue_today']) ?>đ</div>
        <div class="stat-change <?= $stats['total_revenue_today'] > 0 ? 'positive' : 'neutral' ?>">
            <i class="fas fa-<?= $stats['total_revenue_today'] > 0 ? 'trending-up' : 'minus' ?> me-1"></i>
            <?= $stats['total_revenue_today'] > 0 ? 'Tăng trưởng tốt' : 'Chưa có giao dịch' ?>
        </div>
    </div>

    <div class="stat-card info">
        <div class="stat-header">
            <span class="stat-title">Tổng học viên đang học</span>
            <div class="stat-icon info">
                <i class="fas fa-graduation-cap"></i>
            </div>
        </div>
        <div class="stat-value"><?= $stats['total_students'] ?></div>
        <div class="stat-change positive">
            <i class="fas fa-users me-1"></i>
            <?= $stats['total_students'] ?> học viên đang hoạt động
        </div>
    </div>

    <div class="stat-card warning">
        <div class="stat-header">
            <span class="stat-title">Chứng nhận chờ duyệt</span>
            <div class="stat-icon warning">
                <i class="fas fa-certificate"></i>
            </div>
        </div>
        <div class="stat-value"><?= $stats['pending_certificates'] ?></div>
        <div class="stat-change <?= $stats['pending_certificates'] > 0 ? 'negative' : 'positive' ?>">
            <i class="fas fa-<?= $stats['pending_certificates'] > 0 ? 'exclamation-triangle' : 'check-circle' ?> me-1"></i>
            <?= $stats['pending_certificates'] > 0 ? 'Cần xử lý' : 'Đã xử lý hết' ?>
        </div>
    </div>
</div>

<!-- Admin Action Grid -->
<div class="action-section slide-up" style="animation-delay: 0.2s;">
    <div class="section-header mb-4">
        <h3 class="section-title">
            <i class="fas fa-tools me-2"></i>
            Công cụ quản trị
        </h3>
        <p class="section-subtitle">
            Các chức năng quản lý và giám sát hệ thống trung tâm
        </p>
    </div>

    <div class="action-grid admin-grid">
        <!-- Quản lý học viên -->
    <div class="action-card" onclick="window.location.href='<?= $adminBasePath ?>/students'">
            <div class="action-icon primary">
                <i class="fas fa-user-graduate"></i>
            </div>
            <h4 class="action-title">Quản lý học viên</h4>
            <p class="action-description">
                Xem, thêm, sửa, xóa thông tin học viên và theo dõi tiến độ học tập
            </p>
            <div class="action-meta">
                <span class="action-badge modern-badge primary">Quan trọng</span>
                <span class="action-badge modern-badge info"><?= $stats['total_students'] ?> học viên</span>
            </div>
        </div>

        <!-- Quản lý khóa học -->
    <div class="action-card" onclick="window.location.href='<?= $adminBasePath ?>/courses'">
            <div class="action-icon info">
                <i class="fas fa-book-open"></i>
            </div>
            <h4 class="action-title">Quản lý khóa học</h4>
            <p class="action-description">
                Tạo và quản lý các khóa học, chương trình đào tạo của trung tâm
            </p>
            <div class="action-meta">
                <span class="action-badge modern-badge info">Hệ thống</span>
                <span class="action-badge modern-badge primary">Cập nhật</span>
            </div>
        </div>

        <!-- Quản lý nhân viên -->
    <div class="action-card" onclick="window.location.href='<?= $adminBasePath ?>/staff'">
            <div class="action-icon warning">
                <i class="fas fa-users-cog"></i>
            </div>
            <h4 class="action-title">Quản lý nhân viên</h4>
            <p class="action-description">
                Quản lý thông tin nhân viên, phân quyền và theo dõi hiệu suất làm việc
            </p>
            <div class="action-meta">
                <span class="action-badge modern-badge warning">Phân quyền</span>
                <span class="action-badge modern-badge success">Hoạt động</span>
            </div>
        </div>

        <!-- Báo cáo tổng hợp -->
    <div class="action-card" onclick="window.location.href='<?= $adminBasePath ?>/reports/admin'">
            <div class="action-icon success">
                <i class="fas fa-chart-pie"></i>
            </div>
            <h4 class="action-title">Báo cáo tổng hợp</h4>
            <p class="action-description">
                Xem báo cáo chi tiết, thống kê và phân tích dữ liệu của trung tâm
            </p>
            <div class="action-meta">
                <span class="action-badge modern-badge success">Thống kê</span>
                <span class="action-badge modern-badge info">Realtime</span>
            </div>
        </div>

        <!-- Quản lý doanh thu -->
    <div class="action-card" onclick="window.location.href='<?= $adminBasePath ?>/revenue'">
            <div class="action-icon success">
                <i class="fas fa-chart-line"></i>
            </div>
            <h4 class="action-title">Quản lý doanh thu</h4>
            <p class="action-description">
                Theo dõi và phân tích doanh thu, lợi nhuận của trung tâm
            </p>
            <div class="action-meta">
                <span class="action-badge modern-badge success">Tài chính</span>
                <span class="action-badge modern-badge warning">Quan trọng</span>
            </div>
        </div>

        <!-- Quản lý chứng nhận -->
    <div class="action-card" onclick="window.location.href='<?= $adminBasePath ?>/certificates'">
            <div class="action-icon info">
                <i class="fas fa-certificate"></i>
            </div>
            <h4 class="action-title">Quản lý chứng nhận</h4>
            <p class="action-description">
                Cấp phát và theo dõi chứng nhận hoàn thành khóa học
            </p>
            <div class="action-meta">
                <span class="action-badge modern-badge info">Đào tạo</span>
                <span class="action-badge modern-badge warning"><?= $stats['pending_certificates'] ?> chờ duyệt</span>
            </div>
        </div>
    </div>
</div>

<!-- Admin Dashboard Content -->
<div class="row g-4 slide-up" style="animation-delay: 0.4s;">
    <!-- Recent Reports Table -->
    <div class="col-lg-8">
        <div class="data-table">
            <div class="table-header">
                <h3 class="table-title">Báo cáo gần đây của nhân viên</h3>
                <p class="table-subtitle">Tổng quan hoạt động báo cáo từ tất cả nhân viên</p>
            </div>
            
            <div class="table-content">
                <?php if (!empty($recent_reports)): ?>
                    <table class="modern-table">
                        <thead>
                            <tr>
                                <th><i class="fas fa-calendar me-1"></i>Ngày</th>
                                <th><i class="fas fa-user me-1"></i>Nhân viên</th>
                                <th><i class="fas fa-users me-1"></i>Khách đến</th>
                                <th><i class="fas fa-user-plus me-1"></i>Đăng ký</th>
                                <th><i class="fas fa-eye me-1"></i>Chi tiết</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_reports as $index => $report): ?>
                                <tr class="fade-in" style="animation-delay: <?= $index * 0.1 ?>s;">
                                    <td>
                                        <span class="fw-semibold">
                                            <?= date('d/m/Y', strtotime($report['report_date'])) ?>
                                        </span>
                                        <div class="small text-muted">
                                            <?= date('H:i', strtotime($report['report_time'])) ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="staff-avatar me-2">
                                                <?= strtoupper(substr($report['staff_name'], 0, 2)) ?>
                                            </div>
                                            <span class="fw-medium"><?= htmlspecialchars($report['staff_name']) ?></span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="modern-badge primary">
                                            <i class="fas fa-users me-1"></i>
                                            <?= $report['total_visitors'] ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="modern-badge success">
                                            <i class="fas fa-user-check me-1"></i>
                                            <?= $report['total_registered'] ?>
                                        </span>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary" 
                                                onclick="viewDetailedReport('<?= $report['report_date'] ?>')">
                                            <i class="fas fa-search me-1"></i>Xem
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="empty-state">
                        <div class="empty-icon">
                            <i class="fas fa-chart-bar"></i>
                        </div>
                        <h4 class="empty-title">Chưa có báo cáo nào</h4>
                        <p class="empty-description">
                            Chưa có nhân viên nào tạo báo cáo hôm nay
                        </p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Quick Access & Statistics -->
    <div class="col-lg-4">
        <div class="row g-4">
            <!-- Monthly Statistics -->
            <div class="col-12">
                <div class="data-table">
                    <div class="table-header">
                        <h4 class="table-title">Thống kê tháng này</h4>
                    </div>
                    <div class="system-health p-3">
                        <div class="health-item">
                            <i class="fas fa-users text-primary me-2"></i>
                            <span>Khách đến tư vấn</span>
                            <span class="status-text ms-auto fw-bold"><?= $stats['total_visitors_month'] ?? 0 ?></span>
                        </div>
                        <div class="health-item">
                            <i class="fas fa-user-plus text-success me-2"></i>
                            <span>Học viên đăng ký</span>
                            <span class="status-text ms-auto fw-bold"><?= $stats['total_registered_month'] ?? 0 ?></span>
                        </div>
                        <div class="health-item">
                            <i class="fas fa-chart-line text-info me-2"></i>
                            <span>Doanh thu tháng</span>
                            <span class="status-text ms-auto fw-bold"><?= number_format($stats['total_revenue_month'] ?? 0) ?>đ</span>
                        </div>
                        <div class="health-item">
                            <i class="fas fa-graduation-cap text-warning me-2"></i>
                            <span>Học viên đang học</span>
                            <span class="status-text ms-auto fw-bold"><?= $stats['total_students'] ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Links -->
            <div class="col-12">
                <div class="data-table">
                    <div class="table-header">
                        <h4 class="table-title">Truy cập nhanh</h4>
                    </div>
                    <div class="quick-actions p-3">
                        <a href="<?= $adminBasePath ?>/reports" class="quick-action-btn primary text-decoration-none">
                            <i class="fas fa-chart-bar me-2"></i>Xem tất cả báo cáo
                        </a>
                        <a href="<?= $adminBasePath ?>/revenue" class="quick-action-btn success text-decoration-none">
                            <i class="fas fa-money-bill-wave me-2"></i>Xem doanh thu
                        </a>
                        <a href="<?= $adminBasePath ?>/certificates" class="quick-action-btn info text-decoration-none">
                            <i class="fas fa-certificate me-2"></i>Duyệt chứng nhận
                        </a>
                        <a href="<?= $adminBasePath ?>/teaching-shifts/admin" class="quick-action-btn warning text-decoration-none">
                            <i class="fas fa-calendar-alt me-2"></i>Lịch ca dạy
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Admin Dashboard Functions
function viewDetailedReport(reportDate) {
    window.location.href = '<?= $adminBasePath ?>/reports';
}
</script>

<!-- Admin Dashboard Styles -->
<style>
.admin-grid {
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1.5rem;
}

.staff-avatar {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--primary-500), var(--primary-600));
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 0.75rem;
    font-weight: 600;
}

.system-health {
    background: var(--gray-50);
}

.health-item {
    display: flex;
    align-items: center;
    padding: 0.75rem 0;
    border-bottom: 1px solid var(--gray-200);
}

.health-item:last-child {
    border-bottom: none;
}



.status-text {
    font-size: 0.875rem;
    color: var(--gray-600);
}

.quick-actions {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.quick-action-btn {
    display: block;
    width: 100%;
    padding: 0.75rem 1rem;
    border: 1px solid var(--gray-300);
    border-radius: var(--radius-base);
    background: white;
    color: var(--gray-700);
    font-size: 0.875rem;
    font-weight: 500;
    cursor: pointer;
    transition: all var(--transition-base);
    text-align: left;
}

.quick-action-btn:hover {
    transform: translateY(-1px);
    box-shadow: var(--shadow-md);
    text-decoration: none;
}

.quick-action-btn.primary:hover { border-color: var(--primary-300); background: var(--primary-50); color: var(--primary-700); }
.quick-action-btn.success:hover { border-color: var(--success-300); background: var(--success-50); color: var(--success-700); }
.quick-action-btn.info:hover { border-color: var(--info-300); background: var(--info-50); color: var(--info-700); }
.quick-action-btn.warning:hover { border-color: var(--warning-300); background: var(--warning-50); color: var(--warning-700); }

@media (max-width: 768px) {
    .admin-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<?php
// Get the content and include in layout
$content = ob_get_clean();
include __DIR__ . '/../layouts/modern.php';
?>