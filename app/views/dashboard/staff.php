<?php
$pageTitle = 'Dashboard Nhân Viên';

// Compute base path
$scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
$scriptDir = str_replace('\\', '/', dirname($scriptName));
$staffBasePath = rtrim($scriptDir, '/');
if ($staffBasePath === '' || $staffBasePath === '.') {
    $staffBasePath = '';
}

// Start content buffering
ob_start();

// Define data for compact rendering
$statCards = [
    [
        'icon' => 'fas fa-file-alt',
        'color' => 'primary',
        'value' => $stats['my_reports_today'] ?? 0,
        'label' => 'Báo cáo hôm nay'
    ],
    [
        'icon' => 'fas fa-dollar-sign',
        'color' => 'success',
        'value' => number_format($stats['my_revenue_today'] ?? 0),
        'label' => 'Doanh thu (VNĐ)'
    ],
    [
        'icon' => 'fas fa-users',
        'color' => 'info',
        'value' => $stats['my_visitors_today'] ?? 0,
        'label' => 'Khách hàng'
    ],
    [
        'icon' => 'fas fa-certificate',
        'color' => 'warning',
        'value' => $stats['my_certificates_pending'] ?? 0,
        'label' => 'Chứng nhận chờ'
    ]
];

$primaryActions = [
    [
        'icon' => 'fas fa-chart-bar',
        'color' => 'info',
        'title' => 'Tạo báo cáo học viên',
        'description' => 'Ghi nhận lượt học viên đến trung tâm trong ngày.',
        'actions' => [
            ['href' => $staffBasePath . '/reports/create', 'label' => 'Tạo báo cáo', 'class' => 'btn-info text-white', 'icon' => 'fas fa-plus'],
            ['href' => $staffBasePath . '/reports', 'label' => 'Danh sách', 'class' => 'btn-outline-info', 'icon' => 'fas fa-list']
        ]
    ],
    [
        'icon' => 'fas fa-dollar-sign',
        'color' => 'success',
        'title' => 'Tạo báo cáo doanh thu',
        'description' => 'Cập nhật các khoản thu theo khóa học và học viên.',
        'actions' => [
            ['href' => $staffBasePath . '/revenue/create', 'label' => 'Tạo doanh thu', 'class' => 'btn-success text-white', 'icon' => 'fas fa-plus'],
            ['href' => $staffBasePath . '/revenue', 'label' => 'Lịch sử', 'class' => 'btn-outline-success', 'icon' => 'fas fa-clock-rotate-left']
        ]
    ],
    [
        'icon' => 'fas fa-award',
        'color' => 'warning',
        'title' => 'Yêu cầu cấp chứng nhận',
        'description' => 'Gửi yêu cầu chứng nhận hoàn thành cho học viên.',
        'actions' => [
            ['href' => $staffBasePath . '/certificates/create', 'label' => 'Tạo yêu cầu', 'class' => 'btn-warning text-white', 'icon' => 'fas fa-paper-plane'],
            ['href' => $staffBasePath . '/certificates', 'label' => 'Danh sách chờ', 'class' => 'btn-outline-warning', 'icon' => 'fas fa-inbox']
        ]
    ]
];
?>

<!-- Staff Welcome Section -->
<div class="welcome-section fade-in">
    <div class="row align-items-center mb-4">
        <div class="col-md-8">
            <div class="welcome-text">
                <h2 class="welcome-title">
                    <i class="fas fa-user-tie text-primary me-2"></i>
                    Chào mừng, <span class="text-primary"><?= htmlspecialchars($user['full_name']) ?>!</span>
                </h2>
                <p class="welcome-subtitle">
                    Tổng quan công việc của bạn hôm nay - <?php 
                        $days = ['Chủ Nhật', 'Thứ Hai', 'Thứ Ba', 'Thứ Tư', 'Thứ Năm', 'Thứ Sáu', 'Thứ Bảy'];
                        echo $days[date('w')] . ', ' . date('d/m/Y'); 
                    ?>
                </p>
            </div>
        </div>
        <div class="col-md-4 text-end">
            <div class="staff-controls">
                <button class="btn btn-outline-primary" onclick="window.location.href='<?= $staffBasePath ?>/reports/create'">
                    <i class="fas fa-plus me-1"></i>Tạo báo cáo
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Staff Stats Grid - Hidden -->
<!-- <div class="stats-grid slide-up">
    <?php foreach ($statCards as $card): ?>
    <div class="stat-card <?= $card['color'] ?>">
        <div class="stat-header">
            <span class="stat-title"><?= htmlspecialchars($card['label']) ?></span>
            <div class="stat-icon <?= $card['color'] ?>">
                <i class="<?= $card['icon'] ?>"></i>
            </div>
        </div>
        <div class="stat-value"><?= $card['value'] ?></div>
        <div class="stat-change neutral">
            <i class="fas fa-chart-line me-1"></i>
            Theo dõi hôm nay
        </div>
    </div>
    <?php endforeach; ?>
</div> -->

<!-- Staff Action Section -->
<div class="action-section slide-up" style="animation-delay: 0.2s;">
    <div class="section-header mb-4">
        <h3 class="section-title">
            <i class="fas fa-tasks me-2"></i>
            Công việc hàng ngày
        </h3>
        <p class="section-subtitle">
            Các tác vụ chính của nhân viên trong hệ thống
        </p>
    </div>

    <div class="action-grid">
        <?php foreach ($primaryActions as $action): ?>
        <div class="action-card">
            <div class="action-icon <?= $action['color'] ?>">
                <i class="<?= $action['icon'] ?>"></i>
            </div>
            <h4 class="action-title"><?= htmlspecialchars($action['title']) ?></h4>
            <p class="action-description">
                <?= htmlspecialchars($action['description']) ?>
            </p>
            <div class="action-meta">
                <?php foreach ($action['actions'] as $link): ?>
                    <a href="<?= htmlspecialchars($link['href']) ?>" class="btn btn-sm btn-<?= $action['color'] ?> text-white">
                        <i class="<?= $link['icon'] ?> me-1"></i><?= htmlspecialchars($link['label']) ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/modern.php';
?>
