<?php
require_once __DIR__ . '/../layouts/main.php';

$scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
$scriptDir = str_replace('\\', '/', dirname($scriptName));
$basePath = rtrim($scriptDir, '/');
if ($basePath === '' || $basePath === '.') {
    $basePath = '';
}

// Ensure variables exist
$statistics = $statistics ?? [];
$topStaff = $topStaff ?? [];
$currentMonth = $currentMonth ?? date('m/Y');

ob_start();
?>

<?= pageHeader(
    'Báo cáo thống kê bảng lương', 
    'Phân tích xu hướng và so sánh bảng lương qua các tháng',
    '<a href="' . $basePath . '/teaching-shifts/payroll" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-2"></i>Quay lại
    </a>'
) ?>

<style>
.stats-summary-card {
    background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%);
    color: white;
    border-radius: 16px;
    padding: 2rem;
    margin-bottom: 2rem;
    box-shadow: 0 10px 30px rgba(79, 70, 229, 0.3);
}

.stat-box {
    text-align: center;
    padding: 1rem;
}

.stat-value {
    font-size: 2.5rem;
    font-weight: 700;
    display: block;
}

.stat-label {
    font-size: 0.875rem;
    opacity: 0.9;
    margin-top: 0.5rem;
}

.chart-card {
    background: white;
    border-radius: 16px;
    padding: 2rem;
    box-shadow: 0 2px 12px rgba(0,0,0,0.08);
    margin-bottom: 2rem;
}

.trend-up {
    color: #10b981;
}

.trend-down {
    color: #ef4444;
}

.rank-badge {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 1.2rem;
}

.rank-1 { background: linear-gradient(135deg, #fbbf24, #f59e0b); color: white; }
.rank-2 { background: linear-gradient(135deg, #94a3b8, #64748b); color: white; }
.rank-3 { background: linear-gradient(135deg, #d97706, #92400e); color: white; }
.rank-other { background: #e2e8f0; color: #64748b; }

.progress-bar-custom {
    height: 8px;
    border-radius: 4px;
    background: #e9ecef;
    overflow: hidden;
}

.progress-fill {
    height: 100%;
    background: linear-gradient(90deg, #4f46e5, #6366f1);
    border-radius: 4px;
    transition: width 0.3s ease;
}
</style>

<div class="p-3">
    <!-- Summary Statistics -->
    <div class="stats-summary-card">
        <div class="row">
            <div class="col-md-3">
                <div class="stat-box">
                    <span class="stat-value"><?= count($statistics) ?></span>
                    <div class="stat-label">Số tháng theo dõi</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-box">
                    <?php
                    $totalAllMonths = array_sum(array_column($statistics, 'total_amount'));
                    ?>
                    <span class="stat-value"><?= number_format($totalAllMonths / 1000000, 1) ?>M</span>
                    <div class="stat-label">Tổng chi trả (12 tháng)</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-box">
                    <?php
                    $avgMonthly = count($statistics) > 0 ? $totalAllMonths / count($statistics) : 0;
                    ?>
                    <span class="stat-value"><?= number_format($avgMonthly / 1000000, 1) ?>M</span>
                    <div class="stat-label">Trung bình mỗi tháng</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-box">
                    <?php
                    $currentMonthData = !empty($statistics) ? end($statistics) : ['total_staff' => 0];
                    reset($statistics);
                    ?>
                    <span class="stat-value"><?= $currentMonthData['total_staff'] ?? 0 ?></span>
                    <div class="stat-label">Nhân viên tháng <?= $currentMonth ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Monthly Trend Chart -->
        <div class="col-lg-8">
            <div class="chart-card">
                <h5 class="mb-4">
                    <i class="fas fa-chart-line me-2 text-primary"></i>
                    Xu hướng bảng lương 12 tháng
                </h5>
                <canvas id="payrollTrendChart" height="80"></canvas>
            </div>

            <!-- Monthly Table -->
            <div class="chart-card">
                <h5 class="mb-3">
                    <i class="fas fa-table me-2 text-primary"></i>
                    Chi tiết theo tháng
                </h5>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Tháng</th>
                                <th class="text-center">Số NV</th>
                                <th class="text-center">Tổng giờ</th>
                                <th class="text-end">Tổng tiền</th>
                                <th class="text-end">TB/người</th>
                                <th class="text-center">So sánh</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $prevAmount = 0;
                            foreach ($statistics as $stat): 
                                $change = 0;
                                if ($prevAmount > 0) {
                                    $change = (($stat['total_amount'] - $prevAmount) / $prevAmount) * 100;
                                }
                                $prevAmount = $stat['total_amount'];
                            ?>
                            <tr>
                                <td>
                                    <strong><?= $stat['month'] ?></strong>
                                    <?php if ($stat['month_key'] === date('Y-m')): ?>
                                        <span class="badge bg-primary ms-2">Hiện tại</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center"><?= $stat['total_staff'] ?></td>
                                <td class="text-center"><?= number_format($stat['total_hours'], 1) ?>h</td>
                                <td class="text-end fw-bold"><?= number_format($stat['total_amount'], 0, ',', '.') ?> ₫</td>
                                <td class="text-end"><?= number_format($stat['avg_amount'], 0, ',', '.') ?> ₫</td>
                                <td class="text-center">
                                    <?php if ($change > 0): ?>
                                        <span class="trend-up">
                                            <i class="fas fa-arrow-up"></i> <?= number_format(abs($change), 1) ?>%
                                        </span>
                                    <?php elseif ($change < 0): ?>
                                        <span class="trend-down">
                                            <i class="fas fa-arrow-down"></i> <?= number_format(abs($change), 1) ?>%
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Top Staff Sidebar -->
        <div class="col-lg-4">
            <div class="chart-card">
                <h5 class="mb-3">
                    <i class="fas fa-trophy me-2 text-warning"></i>
                    Top 10 NV tháng <?= $currentMonth ?>
                </h5>
                <div class="list-group list-group-flush">
                    <?php 
                    $maxAmount = !empty($topStaff) ? $topStaff[0]['total_amount'] : 1;
                    foreach ($topStaff as $index => $staff): 
                        $rankClass = 'rank-other';
                        if ($index === 0) $rankClass = 'rank-1';
                        elseif ($index === 1) $rankClass = 'rank-2';
                        elseif ($index === 2) $rankClass = 'rank-3';
                        
                        $percentage = ($staff['total_amount'] / $maxAmount) * 100;
                    ?>
                    <div class="list-group-item border-0 px-0">
                        <div class="d-flex align-items-center gap-3 mb-2">
                            <div class="rank-badge <?= $rankClass ?>">
                                <?= $index + 1 ?>
                            </div>
                            <div class="flex-grow-1">
                                <div class="fw-bold"><?= htmlspecialchars($staff['full_name']) ?></div>
                                <small class="text-muted"><?= number_format($staff['total_hours'], 1) ?> giờ</small>
                            </div>
                            <div class="text-end">
                                <div class="fw-bold text-success"><?= number_format($staff['total_amount'], 0, ',', '.') ?> ₫</div>
                            </div>
                        </div>
                        <div class="progress-bar-custom">
                            <div class="progress-fill" style="width: <?= $percentage ?>%"></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php if (empty($topStaff)): ?>
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-inbox fa-2x mb-2"></i>
                            <p>Chưa có dữ liệu</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
// Prepare data for chart
const monthLabels = <?= json_encode(array_column($statistics, 'month')) ?>;
const amountData = <?= json_encode(array_column($statistics, 'total_amount')) ?>;
const hoursData = <?= json_encode(array_column($statistics, 'total_hours')) ?>;

// Create trend chart
const ctx = document.getElementById('payrollTrendChart').getContext('2d');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: monthLabels,
        datasets: [{
            label: 'Tổng tiền (₫)',
            data: amountData,
            borderColor: '#4f46e5',
            backgroundColor: 'rgba(79, 70, 229, 0.1)',
            tension: 0.4,
            fill: true,
            yAxisID: 'y'
        }, {
            label: 'Tổng giờ',
            data: hoursData,
            borderColor: '#10b981',
            backgroundColor: 'rgba(16, 185, 129, 0.1)',
            tension: 0.4,
            fill: true,
            yAxisID: 'y1'
        }]
    },
    options: {
        responsive: true,
        interaction: {
            mode: 'index',
            intersect: false,
        },
        plugins: {
            legend: {
                display: true,
                position: 'top',
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        let label = context.dataset.label || '';
                        if (label) {
                            label += ': ';
                        }
                        if (context.parsed.y !== null) {
                            if (context.datasetIndex === 0) {
                                label += new Intl.NumberFormat('vi-VN').format(context.parsed.y) + ' ₫';
                            } else {
                                label += context.parsed.y.toFixed(1) + ' giờ';
                            }
                        }
                        return label;
                    }
                }
            }
        },
        scales: {
            y: {
                type: 'linear',
                display: true,
                position: 'left',
                ticks: {
                    callback: function(value) {
                        return (value / 1000000).toFixed(1) + 'M';
                    }
                }
            },
            y1: {
                type: 'linear',
                display: true,
                position: 'right',
                grid: {
                    drawOnChartArea: false,
                },
                ticks: {
                    callback: function(value) {
                        return value.toFixed(0) + 'h';
                    }
                }
            },
        }
    }
});
</script>

<?php
$content = ob_get_clean();
renderLayout($content, [
    'title' => 'Báo cáo thống kê bảng lương',
    'activePage' => 'teaching-shifts'
]);
?>
