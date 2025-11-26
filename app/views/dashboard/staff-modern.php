<?php
// Define page data
$pageTitle = 'Dashboard Nhân Viên';
$content = ob_start();
?>

<!-- Modern Staff Dashboard Content -->
<div class="dashboard-container">
    <!-- Welcome Section -->
    <div class="welcome-hero"
    <div class="row align-items-center mb-4">
        <div class="col-md-8">
            <div class="welcome-text">
                <h2 class="welcome-title">
                    Chào mừng trở lại, <span class="text-primary"><?= htmlspecialchars($user['full_name']) ?>!</span>
                </h2>
                <p class="welcome-subtitle">
                    Đây là tổng quan công việc của bạn hôm nay - <?= date('l, d/m/Y', strtotime('today')) ?>
                </p>
            </div>
        </div>
        <div class="col-md-4 text-end">
            <div class="welcome-badge">
                <div class="status-indicator">
                    <span class="status-dot"></span>
                    <span class="status-text">Đang hoạt động</span>
                </div>
                <div class="current-time">
                    <i class="fas fa-clock me-1"></i>
                    <span class="live-clock"><?= date('H:i - d/m/Y') ?></span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Stats Cards Grid -->
<div class="stats-grid slide-up">
    <div class="stat-card primary">
        <div class="stat-header">
            <span class="stat-title">Báo cáo hôm nay</span>
            <div class="stat-icon primary">
                <i class="fas fa-clipboard-list"></i>
            </div>
        </div>
        <div class="stat-value"><?= $stats['my_reports_today'] ?></div>
        <div class="stat-change <?= $stats['my_reports_today'] > 0 ? 'positive' : 'neutral' ?>">
            <i class="fas fa-<?= $stats['my_reports_today'] > 0 ? 'arrow-up' : 'minus' ?> me-1"></i>
            <?= $stats['my_reports_today'] > 0 ? 'Đã hoàn thành ' . $stats['my_reports_today'] . ' báo cáo' : 'Chưa có báo cáo nào' ?>
        </div>
    </div>

    <div class="stat-card success">
        <div class="stat-header">
            <span class="stat-title">Doanh thu hôm nay</span>
            <div class="stat-icon success">
                <i class="fas fa-money-bill-wave"></i>
            </div>
        </div>
        <div class="stat-value"><?= number_format($stats['my_revenue_today']) ?>đ</div>
        <div class="stat-change <?= $stats['my_revenue_today'] > 0 ? 'positive' : 'neutral' ?>">
            <i class="fas fa-<?= $stats['my_revenue_today'] > 0 ? 'trending-up' : 'minus' ?> me-1"></i>
            <?= $stats['my_revenue_today'] > 0 ? 'Tuyệt vời! Đã có doanh thu' : 'Chưa có giao dịch' ?>
        </div>
    </div>

    <div class="stat-card info">
        <div class="stat-header">
            <span class="stat-title">Khách tư vấn</span>
            <div class="stat-icon info">
                <i class="fas fa-users"></i>
            </div>
        </div>
        <div class="stat-value"><?= $stats['my_visitors_today'] ?></div>
        <div class="stat-change positive">
            <i class="fas fa-user-plus me-1"></i>
            Tổng <?= $stats['my_visitors_today'] ?> lượt tư vấn
        </div>
    </div>

    <div class="stat-card warning">
        <div class="stat-header">
            <span class="stat-title">Chứng nhận chờ</span>
            <div class="stat-icon warning">
                <i class="fas fa-certificate"></i>
            </div>
        </div>
        <div class="stat-value"><?= $stats['my_certificates_pending'] ?></div>
        <div class="stat-change <?= $stats['my_certificates_pending'] > 0 ? 'negative' : 'positive' ?>">
            <i class="fas fa-<?= $stats['my_certificates_pending'] > 0 ? 'clock' : 'check' ?> me-1"></i>
            <?= $stats['my_certificates_pending'] > 0 ? 'Có ' . $stats['my_certificates_pending'] . ' chờ duyệt' : 'Không có yêu cầu' ?>
        </div>
    </div>
</div>

<!-- Quick Actions Section -->
<div class="action-section slide-up" style="animation-delay: 0.2s;">
    <div class="section-header mb-4">
        <h3 class="section-title">
            <i class="fas fa-bolt me-2"></i>
            Công việc chính của bạn
        </h3>
        <p class="section-subtitle">
            Ba nhiệm vụ quan trọng nhất trong ngày - hãy hoàn thành để đạt hiệu suất tốt nhất
        </p>
    </div>

    <div class="action-grid">
        <!-- Báo cáo học viên -->
        <div class="action-card" onclick="navigateToReports()">
            <div class="action-icon primary">
                <i class="fas fa-clipboard-check"></i>
            </div>
            <h4 class="action-title">Báo cáo học viên</h4>
            <p class="action-description">
                Ghi nhận số lượng khách đến tham quan và tư vấn trong ngày hôm nay
            </p>
            <div class="action-meta">
                <span class="action-badge modern-badge primary">Hàng ngày</span>
                <span class="action-badge modern-badge info">5-10 phút</span>
            </div>
            <div class="action-shortcut">
                <small class="text-muted">
                    <i class="fas fa-keyboard me-1"></i>Alt + R
                </small>
            </div>
        </div>

        <!-- Báo cáo doanh thu -->
        <div class="action-card" onclick="navigateToRevenue()">
            <div class="action-icon success">
                <i class="fas fa-chart-line"></i>
            </div>
            <h4 class="action-title">Báo cáo doanh thu</h4>
            <p class="action-description">
                Ghi nhận học phí và doanh thu từ các học viên đăng ký khóa học
            </p>
            <div class="action-meta">
                <span class="action-badge modern-badge success">Quan trọng</span>
                <span class="action-badge modern-badge info">3-5 phút</span>
            </div>
            <div class="action-shortcut">
                <small class="text-muted">
                    <i class="fas fa-keyboard me-1"></i>Ctrl + R
                </small>
            </div>
        </div>

        <!-- Cấp chứng nhận -->
        <div class="action-card" onclick="navigateToCertificates()">
            <div class="action-icon warning">
                <i class="fas fa-award"></i>
            </div>
            <h4 class="action-title">Cấp chứng nhận</h4>
            <p class="action-description">
                Tạo yêu cầu cấp chứng nhận cho học viên đã hoàn thành khóa học
            </p>
            <div class="action-meta">
                <span class="action-badge modern-badge warning">Theo yêu cầu</span>
                <span class="action-badge modern-badge info">2-3 phút</span>
            </div>
            <div class="action-shortcut">
                <small class="text-muted">
                    <i class="fas fa-keyboard me-1"></i>Alt + C
                </small>
            </div>
        </div>
    </div>
</div>

<!-- Recent Reports Section -->
<div class="reports-section slide-up" style="animation-delay: 0.4s;">
    <div class="data-table">
        <div class="table-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="table-title">Báo cáo gần đây của bạn</h3>
                    <p class="table-subtitle">Lịch sử hoạt động và báo cáo trong tuần vừa qua</p>
                </div>
                <button class="btn btn-outline-primary" onclick="window.location.href='/reports'">
                    <i class="fas fa-external-link-alt me-1"></i>
                    Xem tất cả
                </button>
            </div>
        </div>
        
        <div class="table-content">
            <?php if (!empty($my_recent_reports)): ?>
                <table class="modern-table">
                    <thead>
                        <tr>
                            <th><i class="fas fa-calendar-day me-1"></i>Ngày</th>
                            <th><i class="fas fa-clock me-1"></i>Thời gian</th>
                            <th><i class="fas fa-users me-1"></i>Khách đến</th>
                            <th><i class="fas fa-user-plus me-1"></i>Đăng ký</th>
                            <th><i class="fas fa-money-bill-wave me-1"></i>Doanh thu</th>
                            <th><i class="fas fa-cogs me-1"></i>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($my_recent_reports as $index => $report): ?>
                            <tr class="fade-in" style="animation-delay: <?= $index * 0.1 ?>s;">
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="date-indicator me-2">
                                            <span class="day"><?= date('d', strtotime($report['report_date'])) ?></span>
                                            <span class="month"><?= date('M', strtotime($report['report_date'])) ?></span>
                                        </div>
                                        <span class="fw-semibold">
                                            <?= date('d/m/Y', strtotime($report['report_date'])) ?>
                                        </span>
                                    </div>
                                </td>
                                <td>
                                    <span class="modern-badge info">
                                        <i class="fas fa-clock me-1"></i>
                                        <?= date('H:i', strtotime($report['report_time'])) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="modern-badge primary">
                                        <i class="fas fa-users me-1"></i>
                                        <?= $report['total_visitors'] ?> người
                                    </span>
                                </td>
                                <td>
                                    <span class="modern-badge success">
                                        <i class="fas fa-user-check me-1"></i>
                                        <?= $report['total_registered'] ?> người
                                    </span>
                                </td>
                                <td>
                                    <div class="revenue-display">
                                        <i class="fas fa-dollar-sign text-success me-1"></i>
                                        <strong class="text-success">
                                            <?= number_format($report['revenue_amount']) ?>đ
                                        </strong>
                                    </div>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary" 
                                            onclick="viewReportDetail('<?= $report['report_date'] ?>')">
                                        <i class="fas fa-eye me-1"></i>Xem chi tiết
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-icon">
                        <i class="fas fa-inbox"></i>
                    </div>
                    <h4 class="empty-title">Chưa có báo cáo nào</h4>
                    <p class="empty-description">
                        Bạn chưa tạo báo cáo nào. Hãy bắt đầu với báo cáo đầu tiên của bạn!
                    </p>
                    <button class="btn btn-primary btn-lg" onclick="navigateToReports()">
                        <i class="fas fa-plus me-2"></i>Tạo báo cáo đầu tiên
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- JavaScript for Interactions -->
<script>
// Navigation Functions
function navigateToReports() {
    showLoadingAndRedirect('/reports/create', 'Đang chuyển đến tạo báo cáo...');
}

function navigateToRevenue() {
    showLoadingAndRedirect('/revenue/create', 'Đang chuyển đến báo cáo doanh thu...');
}

function navigateToCertificates() {
    showLoadingAndRedirect('/certificates/create', 'Đang chuyển đến cấp chứng nhận...');
}

function viewReportDetail(reportDate) {
    showLoadingAndRedirect('/reports/view/' + reportDate, 'Đang tải chi tiết báo cáo...');
}

function showLoadingAndRedirect(url, message) {
    // Create loading overlay
    const overlay = document.createElement('div');
    overlay.className = 'loading-overlay';
    overlay.innerHTML = `
        <div class="loading-content">
            <div class="spinner-border text-primary mb-3" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="text-muted">${message}</p>
        </div>
    `;
    document.body.appendChild(overlay);
    
    // Redirect after short delay
    setTimeout(() => {
        window.location.href = url;
    }, 800);
}

// Enhanced Keyboard Shortcuts
document.addEventListener('keydown', function(e) {
    // Alt + R for Reports
    if (e.altKey && e.key === 'r') {
        e.preventDefault();
        navigateToReports();
    }
    
    // Ctrl + R for Revenue (prevent default browser refresh)
    if (e.ctrlKey && e.key === 'r') {
        e.preventDefault();
        navigateToRevenue();
    }
    
    // Alt + C for Certificates
    if (e.altKey && e.key === 'c') {
        e.preventDefault();
        navigateToCertificates();
    }
});

// Initialize dashboard
document.addEventListener('DOMContentLoaded', function() {
    // Add hover effects to action cards
    const actionCards = document.querySelectorAll('.action-card');
    actionCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-8px) scale(1.02)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = '';
        });
    });
    
    // Auto-refresh stats every 5 minutes
    setInterval(refreshStats, 300000);
});

async function refreshStats() {
    try {
        const response = await fetch('/api/dashboard/stats');
        const data = await response.json();
        
        // Update stat values with animation
        document.querySelectorAll('.stat-value').forEach((el, index) => {
            el.style.transform = 'scale(1.1)';
            setTimeout(() => {
                el.style.transform = '';
            }, 200);
        });
        
    } catch (error) {
        console.error('Failed to refresh stats:', error);
    }
}
</script>

<!-- Custom Styles for Staff Dashboard -->
<style>
.welcome-section {
    margin-bottom: 2rem;
}

.welcome-title {
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
    color: var(--gray-900);
}

.welcome-subtitle {
    color: var(--gray-600);
    margin: 0;
}

.welcome-badge {
    text-align: right;
}

.status-indicator {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    margin-bottom: 0.5rem;
}

.status-dot {
    width: 8px;
    height: 8px;
    background: var(--success-500);
    border-radius: 50%;
    margin-right: 0.5rem;
    animation: pulse 2s infinite;
}

.status-text {
    font-size: 0.875rem;
    font-weight: 500;
    color: var(--success-600);
}

.current-time {
    font-size: 0.875rem;
    color: var(--gray-600);
}

.section-header {
    text-align: center;
    margin-bottom: 2rem;
}

.section-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--gray-900);
    margin-bottom: 0.5rem;
}

.section-subtitle {
    color: var(--gray-600);
    margin: 0;
}

.action-shortcut {
    margin-top: 1rem;
    text-align: center;
}

.date-indicator {
    background: var(--primary-50);
    border-radius: var(--radius-base);
    padding: 0.25rem 0.5rem;
    text-align: center;
    min-width: 48px;
}

.date-indicator .day {
    font-size: 0.875rem;
    font-weight: 700;
    color: var(--primary-600);
    display: block;
    line-height: 1;
}

.date-indicator .month {
    font-size: 0.75rem;
    color: var(--primary-500);
    text-transform: uppercase;
    display: block;
    line-height: 1;
}

.revenue-display {
    display: flex;
    align-items: center;
}

.empty-state {
    text-align: center;
    padding: 3rem 2rem;
}

.empty-icon {
    font-size: 4rem;
    color: var(--gray-300);
    margin-bottom: 1rem;
}

.empty-title {
    font-size: 1.25rem;
    font-weight: 600;
    color: var(--gray-700);
    margin-bottom: 0.5rem;
}

.empty-description {
    color: var(--gray-500);
    margin-bottom: 2rem;
}

.loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(255, 255, 255, 0.9);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
    backdrop-filter: blur(2px);
}

.loading-content {
    text-align: center;
    padding: 2rem;
    background: white;
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-lg);
}

@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.5; }
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .welcome-title {
        font-size: 1.5rem;
    }
    
    .welcome-badge {
        text-align: left;
        margin-top: 1rem;
    }
    
    .status-indicator {
        justify-content: flex-start;
    }
    
    .action-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<?php
// Get the content and include in layout
$content = ob_get_clean();
include __DIR__ . '/../layouts/modern.php';
?>