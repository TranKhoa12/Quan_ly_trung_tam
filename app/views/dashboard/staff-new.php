<?php
// Prepare data for layout
$title = 'Dashboard Nhân Viên';

// Start capturing content
ob_start();
?>

<div class="dashboard-wrapper">
    <!-- Hero Section -->
    <div class="hero-section">
        <div class="hero-content">
            <div class="hero-text">
                <h1 class="hero-title">
                    <i class="fas fa-sun text-warning me-2"></i>
                    Chào mừng trở lại, <span class="text-primary"><?= htmlspecialchars($user['full_name']) ?>!</span>
                </h1>
                <p class="hero-subtitle">
                    Hôm nay là <?= date('l, d/m/Y') ?> - Hãy bắt đầu một ngày làm việc hiệu quả
                </p>
            </div>
            <div class="hero-badge">
                <div class="status-pill online">
                    <span class="status-dot"></span>
                    <span>Đang hoạt động</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Overview -->
    <div class="stats-section">
        <div class="section-title">
            <h2><i class="fas fa-chart-bar me-2"></i>Thống kê hôm nay</h2>
            <p>Tổng quan về công việc và kết quả của bạn</p>
        </div>
        
        <div class="stats-grid">
            <!-- Báo cáo hôm nay -->
            <div class="stat-card blue-gradient">
                <div class="stat-icon">
                    <i class="fas fa-clipboard-list"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-number"><?= $stats['my_reports_today'] ?></div>
                    <div class="stat-label">Báo cáo hôm nay</div>
                    <div class="stat-badge <?= $stats['my_reports_today'] > 0 ? 'success' : 'neutral' ?>">
                        <i class="fas fa-<?= $stats['my_reports_today'] > 0 ? 'check-circle' : 'clock' ?> me-1"></i>
                        <?= $stats['my_reports_today'] > 0 ? 'Đã hoàn thành' : 'Chưa báo cáo' ?>
                    </div>
                </div>
            </div>

            <!-- Doanh thu hôm nay -->
            <div class="stat-card green-gradient">
                <div class="stat-icon">
                    <i class="fas fa-money-bill-trend-up"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-number"><?= number_format($stats['my_revenue_today']) ?>đ</div>
                    <div class="stat-label">Doanh thu hôm nay</div>
                    <div class="stat-badge <?= $stats['my_revenue_today'] > 0 ? 'success' : 'neutral' ?>">
                        <i class="fas fa-<?= $stats['my_revenue_today'] > 0 ? 'trending-up' : 'minus' ?> me-1"></i>
                        <?= $stats['my_revenue_today'] > 0 ? 'Có doanh thu' : 'Chưa có giao dịch' ?>
                    </div>
                </div>
            </div>

            <!-- Khách tư vấn -->
            <div class="stat-card purple-gradient">
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-number"><?= $stats['my_visitors_today'] ?></div>
                    <div class="stat-label">Khách tư vấn</div>
                    <div class="stat-badge info">
                        <i class="fas fa-handshake me-1"></i>
                        Tư vấn hôm nay
                    </div>
                </div>
            </div>

            <!-- Chứng nhận chờ -->
            <div class="stat-card orange-gradient">
                <div class="stat-icon">
                    <i class="fas fa-certificate"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-number"><?= $stats['my_certificates_pending'] ?></div>
                    <div class="stat-label">Chứng nhận chờ</div>
                    <div class="stat-badge <?= $stats['my_certificates_pending'] > 0 ? 'warning' : 'success' ?>">
                        <i class="fas fa-<?= $stats['my_certificates_pending'] > 0 ? 'hourglass-half' : 'check' ?> me-1"></i>
                        <?= $stats['my_certificates_pending'] > 0 ? 'Chờ duyệt' : 'Hoàn thành' ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="actions-section">
        <div class="section-title">
            <h2><i class="fas fa-bolt me-2"></i>Thao tác nhanh</h2>
            <p>Ba công việc chính bạn cần thực hiện hàng ngày</p>
        </div>

        <div class="action-grid">
            <!-- Tạo báo cáo -->
            <div class="action-card primary" onclick="navigateToReports()">
                <div class="action-header">
                    <div class="action-icon">
                        <i class="fas fa-clipboard-check"></i>
                    </div>
                    <div class="action-priority high">
                        <i class="fas fa-star"></i>
                    </div>
                </div>
                <div class="action-body">
                    <h3 class="action-title">Báo cáo học viên</h3>
                    <p class="action-description">
                        Ghi nhận số lượng khách đến tham quan và tư vấn trong ngày
                    </p>
                    <div class="action-tags">
                        <span class="tag primary">Hàng ngày</span>
                        <span class="tag info">5 phút</span>
                    </div>
                </div>
                <div class="action-footer">
                    <div class="shortcut-hint">
                        <i class="fas fa-keyboard me-1"></i>Alt + R
                    </div>
                </div>
            </div>

            <!-- Báo cáo doanh thu -->
            <div class="action-card success" onclick="navigateToRevenue()">
                <div class="action-header">
                    <div class="action-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="action-priority high">
                        <i class="fas fa-star"></i>
                    </div>
                </div>
                <div class="action-body">
                    <h3 class="action-title">Báo cáo doanh thu</h3>
                    <p class="action-description">
                        Ghi nhận học phí và doanh thu từ học viên đăng ký
                    </p>
                    <div class="action-tags">
                        <span class="tag success">Quan trọng</span>
                        <span class="tag info">3 phút</span>
                    </div>
                </div>
                <div class="action-footer">
                    <div class="shortcut-hint">
                        <i class="fas fa-keyboard me-1"></i>Ctrl + R
                    </div>
                </div>
            </div>

            <!-- Cấp chứng nhận -->
            <div class="action-card warning" onclick="navigateToCertificates()">
                <div class="action-header">
                    <div class="action-icon">
                        <i class="fas fa-award"></i>
                    </div>
                    <div class="action-priority medium">
                        <i class="fas fa-exclamation"></i>
                    </div>
                </div>
                <div class="action-body">
                    <h3 class="action-title">Cấp chứng nhận</h3>
                    <p class="action-description">
                        Tạo yêu cầu cấp chứng nhận cho học viên hoàn thành
                    </p>
                    <div class="action-tags">
                        <span class="tag warning">Theo yêu cầu</span>
                        <span class="tag info">2 phút</span>
                    </div>
                </div>
                <div class="action-footer">
                    <div class="shortcut-hint">
                        <i class="fas fa-keyboard me-1"></i>Alt + C
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="activity-section">
        <div class="section-title">
            <h2><i class="fas fa-history me-2"></i>Hoạt động gần đây</h2>
            <p>Lịch sử báo cáo và công việc của bạn</p>
        </div>

        <div class="activity-card">
            <?php if (!empty($my_recent_reports)): ?>
                <div class="activity-table">
                    <table class="modern-data-table">
                        <thead>
                            <tr>
                                <th>
                                    <i class="fas fa-calendar me-1"></i>
                                    Ngày tháng
                                </th>
                                <th>
                                    <i class="fas fa-clock me-1"></i>
                                    Thời gian
                                </th>
                                <th>
                                    <i class="fas fa-users me-1"></i>
                                    Khách đến
                                </th>
                                <th>
                                    <i class="fas fa-user-plus me-1"></i>
                                    Đăng ký
                                </th>
                                <th>
                                    <i class="fas fa-dollar-sign me-1"></i>
                                    Doanh thu
                                </th>
                                <th>
                                    <i class="fas fa-eye me-1"></i>
                                    Chi tiết
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($my_recent_reports as $index => $report): ?>
                                <tr class="table-row" style="animation-delay: <?= $index * 0.1 ?>s;">
                                    <td>
                                        <div class="date-cell">
                                            <div class="date-main"><?= date('d/m/Y', strtotime($report['report_date'])) ?></div>
                                            <div class="date-sub"><?= date('l', strtotime($report['report_date'])) ?></div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="time-badge">
                                            <?= date('H:i', strtotime($report['report_time'])) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="metric-cell visitors">
                                            <span class="metric-number"><?= $report['total_visitors'] ?></span>
                                            <span class="metric-label">người</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="metric-cell registered">
                                            <span class="metric-number"><?= $report['total_registered'] ?></span>
                                            <span class="metric-label">người</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="revenue-cell">
                                            <span class="currency">₫</span>
                                            <span class="amount"><?= number_format($report['revenue_amount']) ?></span>
                                        </div>
                                    </td>
                                    <td>
                                        <button class="view-btn" onclick="viewReportDetail('<?= $report['report_date'] ?>')" title="Xem chi tiết">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty-activity">
                    <div class="empty-icon">
                        <i class="fas fa-clipboard"></i>
                    </div>
                    <h3>Chưa có hoạt động nào</h3>
                    <p>Bạn chưa có báo cáo nào. Hãy tạo báo cáo đầu tiên!</p>
                    <button class="create-first-btn" onclick="navigateToReports()">
                        <i class="fas fa-plus me-2"></i>
                        Tạo báo cáo đầu tiên
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
/* Modern Staff Dashboard Styles */
.dashboard-wrapper {
    padding: 2rem;
    max-width: 1400px;
    margin: 0 auto;
}

/* Hero Section */
.hero-section {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 20px;
    padding: 2rem;
    margin-bottom: 2rem;
    color: white;
    position: relative;
    overflow: hidden;
}

.hero-section::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -50%;
    width: 200%;
    height: 200%;
    background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
    animation: float 6s ease-in-out infinite;
}

.hero-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
    position: relative;
    z-index: 2;
}

.hero-title {
    font-size: 2.5rem;
    font-weight: 800;
    margin-bottom: 0.5rem;
}

.hero-subtitle {
    font-size: 1.2rem;
    opacity: 0.9;
    margin: 0;
}

.status-pill {
    display: flex;
    align-items: center;
    background: rgba(255,255,255,0.2);
    padding: 0.75rem 1.5rem;
    border-radius: 50px;
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255,255,255,0.3);
}

.status-dot {
    width: 12px;
    height: 12px;
    background: #10b981;
    border-radius: 50%;
    margin-right: 0.75rem;
    animation: pulse 2s infinite;
}

/* Section Titles */
.section-title {
    text-align: center;
    margin-bottom: 2rem;
}

.section-title h2 {
    font-size: 2rem;
    font-weight: 700;
    color: var(--gray-900);
    margin-bottom: 0.5rem;
}

.section-title p {
    color: var(--gray-600);
    font-size: 1.1rem;
    margin: 0;
}

/* Stats Grid */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 1.5rem;
    margin-bottom: 3rem;
}

.stat-card {
    background: white;
    border-radius: 20px;
    padding: 2rem;
    position: relative;
    overflow: hidden;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.stat-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 20px 40px rgba(0,0,0,0.15);
}

.stat-card.blue-gradient { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; }
.stat-card.green-gradient { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; }
.stat-card.purple-gradient { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white; }
.stat-card.orange-gradient { background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); color: white; }

.stat-icon {
    width: 80px;
    height: 80px;
    background: rgba(255,255,255,0.2);
    border-radius: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    margin-bottom: 1rem;
    backdrop-filter: blur(10px);
}

.stat-number {
    font-size: 3rem;
    font-weight: 900;
    margin-bottom: 0.5rem;
    line-height: 1;
}

.stat-label {
    font-size: 1.1rem;
    font-weight: 600;
    opacity: 0.9;
    margin-bottom: 1rem;
}

.stat-badge {
    display: inline-flex;
    align-items: center;
    padding: 0.5rem 1rem;
    border-radius: 50px;
    font-size: 0.9rem;
    font-weight: 500;
    background: rgba(255,255,255,0.2);
    backdrop-filter: blur(10px);
}

/* Action Grid */
.action-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    gap: 2rem;
    margin-bottom: 3rem;
}

.action-card {
    background: white;
    border-radius: 20px;
    padding: 2rem;
    cursor: pointer;
    transition: all 0.3s ease;
    border: 2px solid transparent;
    position: relative;
    overflow: hidden;
}

.action-card:hover {
    transform: translateY(-8px) scale(1.02);
    box-shadow: 0 25px 50px rgba(0,0,0,0.15);
}

.action-card.primary:hover { border-color: #667eea; }
.action-card.success:hover { border-color: #10b981; }
.action-card.warning:hover { border-color: #f59e0b; }

.action-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
}

.action-icon {
    width: 70px;
    height: 70px;
    border-radius: 18px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    color: white;
    background: linear-gradient(135deg, #667eea, #764ba2);
}

.action-card.success .action-icon { background: linear-gradient(135deg, #10b981, #059669); }
.action-card.warning .action-icon { background: linear-gradient(135deg, #f59e0b, #d97706); }

.action-priority {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
}

.action-priority.high { background: #ef4444; color: white; }
.action-priority.medium { background: #f59e0b; color: white; }

.action-title {
    font-size: 1.5rem;
    font-weight: 700;
    margin-bottom: 1rem;
    color: var(--gray-900);
}

.action-description {
    color: var(--gray-600);
    line-height: 1.6;
    margin-bottom: 1.5rem;
}

.action-tags {
    display: flex;
    gap: 0.5rem;
    margin-bottom: 1rem;
}

.tag {
    padding: 0.4rem 0.8rem;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
}

.tag.primary { background: #dbeafe; color: #1d4ed8; }
.tag.success { background: #d1fae5; color: #047857; }
.tag.warning { background: #fef3c7; color: #92400e; }
.tag.info { background: #e0f2fe; color: #0369a1; }

.shortcut-hint {
    font-size: 0.9rem;
    color: var(--gray-500);
    font-family: 'JetBrains Mono', monospace;
}

/* Activity Section */
.activity-card {
    background: white;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
}

.modern-data-table {
    width: 100%;
    border-collapse: collapse;
}

.modern-data-table th {
    background: var(--gray-50);
    padding: 1.5rem;
    text-align: left;
    font-weight: 600;
    color: var(--gray-700);
    border-bottom: 1px solid var(--gray-200);
}

.modern-data-table td {
    padding: 1.5rem;
    border-bottom: 1px solid var(--gray-100);
}

.table-row {
    transition: background-color 0.2s ease;
    animation: slideInUp 0.5s ease forwards;
    opacity: 0;
}

.table-row:hover {
    background: var(--gray-50);
}

.date-cell {
    display: flex;
    flex-direction: column;
}

.date-main {
    font-weight: 600;
    color: var(--gray-900);
}

.date-sub {
    font-size: 0.8rem;
    color: var(--gray-500);
}

.time-badge {
    background: var(--primary-100);
    color: var(--primary-700);
    padding: 0.4rem 0.8rem;
    border-radius: 12px;
    font-size: 0.9rem;
    font-weight: 500;
}

.metric-cell {
    display: flex;
    align-items: baseline;
    gap: 0.25rem;
}

.metric-number {
    font-weight: 700;
    font-size: 1.1rem;
}

.metric-label {
    font-size: 0.8rem;
    color: var(--gray-500);
}

.metric-cell.visitors .metric-number { color: #3b82f6; }
.metric-cell.registered .metric-number { color: #10b981; }

.revenue-cell {
    display: flex;
    align-items: baseline;
    font-weight: 700;
    color: #059669;
}

.currency {
    font-size: 0.9rem;
    margin-right: 0.25rem;
}

.view-btn {
    background: var(--primary-100);
    color: var(--primary-600);
    border: none;
    width: 40px;
    height: 40px;
    border-radius: 12px;
    cursor: pointer;
    transition: all 0.2s ease;
}

.view-btn:hover {
    background: var(--primary-600);
    color: white;
    transform: scale(1.1);
}

/* Empty State */
.empty-activity {
    text-align: center;
    padding: 4rem 2rem;
}

.empty-icon {
    font-size: 4rem;
    color: var(--gray-300);
    margin-bottom: 1.5rem;
}

.empty-activity h3 {
    font-size: 1.5rem;
    font-weight: 600;
    color: var(--gray-700);
    margin-bottom: 0.5rem;
}

.empty-activity p {
    color: var(--gray-500);
    margin-bottom: 2rem;
}

.create-first-btn {
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    border: none;
    padding: 1rem 2rem;
    border-radius: 50px;
    font-size: 1.1rem;
    font-weight: 600;
    cursor: pointer;
    transition: transform 0.2s ease;
}

.create-first-btn:hover {
    transform: translateY(-2px);
}

/* Animations */
@keyframes float {
    0%, 100% { transform: translateY(0px) rotate(0deg); }
    50% { transform: translateY(-20px) rotate(180deg); }
}

@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.5; }
}

@keyframes slideInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Responsive Design */
@media (max-width: 768px) {
    .dashboard-wrapper {
        padding: 1rem;
    }
    
    .hero-content {
        flex-direction: column;
        text-align: center;
        gap: 1.5rem;
    }
    
    .hero-title {
        font-size: 1.8rem;
    }
    
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
    .action-grid {
        grid-template-columns: 1fr;
    }
    
    .action-card {
        padding: 1.5rem;
    }
    
    .modern-data-table {
        font-size: 0.9rem;
    }
    
    .modern-data-table th,
    .modern-data-table td {
        padding: 1rem;
    }
}

/* Dark theme adjustments */
[data-theme="dark"] .stat-card {
    background: var(--gray-800);
}

[data-theme="dark"] .action-card {
    background: var(--gray-800);
    border-color: var(--gray-700);
}

[data-theme="dark"] .activity-card {
    background: var(--gray-800);
}
</style>

<script>
// Navigation functions
function navigateToReports() {
    showLoadingState('Đang chuyển đến tạo báo cáo...');
    setTimeout(() => window.location.href = '/reports/create', 500);
}

function navigateToRevenue() {
    showLoadingState('Đang chuyển đến báo cáo doanh thu...');
    setTimeout(() => window.location.href = '/revenue/create', 500);
}

function navigateToCertificates() {
    showLoadingState('Đang chuyển đến cấp chứng nhận...');
    setTimeout(() => window.location.href = '/certificates/create', 500);
}

function viewReportDetail(reportDate) {
    showLoadingState('Đang tải chi tiết báo cáo...');
    setTimeout(() => window.location.href = '/reports/view/' + reportDate, 500);
}

function showLoadingState(message) {
    const overlay = document.createElement('div');
    overlay.className = 'loading-overlay';
    overlay.innerHTML = `
        <div class="loading-content">
            <div class="spinner"></div>
            <p>${message}</p>
        </div>
    `;
    document.body.appendChild(overlay);
}

// Animate table rows on load
document.addEventListener('DOMContentLoaded', function() {
    const rows = document.querySelectorAll('.table-row');
    rows.forEach((row, index) => {
        setTimeout(() => {
            row.style.opacity = '1';
        }, index * 100);
    });
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>