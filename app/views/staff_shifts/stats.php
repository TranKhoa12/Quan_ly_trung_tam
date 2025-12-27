<?php
// Use modern layout
$pageTitle = 'Thống kê ca dạy - ' . htmlspecialchars($staffName);

// Setup buildUrl function
$scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
$scriptDir = str_replace('\\', '/', dirname($scriptName));
$appBasePath = rtrim($scriptDir, '/');
if ($appBasePath === '' || $appBasePath === '.') {
    $appBasePath = '';
}

$buildUrl = function (string $path = '') use ($appBasePath): string {
    $normalized = '/' . ltrim($path, '/');
    return ($appBasePath ? $appBasePath : '') . $normalized;
};

ob_start();
?>

<?php
$monthName = ['', 'Tháng 1', 'Tháng 2', 'Tháng 3', 'Tháng 4', 'Tháng 5', 'Tháng 6', 
              'Tháng 7', 'Tháng 8', 'Tháng 9', 'Tháng 10', 'Tháng 11', 'Tháng 12'];
?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h2><i class="fas fa-chart-line text-primary me-2"></i>Thống Kê Ca Dạy</h2>
            <p class="text-muted">Nhân viên: <strong><?= htmlspecialchars($staffName) ?></strong></p>
        </div>
        <div class="col-md-4 text-end">
            <a href="<?= $buildUrl('staff/shift-stats/export') ?>?month=<?= $month ?>&year=<?= $year ?>" 
               class="btn btn-success">
                <i class="fas fa-file-excel me-1"></i>Xuất Excel
            </a>
        </div>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Bộ lọc tháng/năm -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Tháng</label>
                    <select name="month" class="form-select">
                        <?php for ($m = 1; $m <= 12; $m++): ?>
                            <option value="<?= $m ?>" <?= $m == $month ? 'selected' : '' ?>>
                                <?= $monthName[$m] ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Năm</label>
                    <select name="year" class="form-select">
                        <?php for ($y = date('Y'); $y >= 2020; $y--): ?>
                            <option value="<?= $y ?>" <?= $y == $year ? 'selected' : '' ?>><?= $y ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-search me-1"></i>Xem
                    </button>
                    <a href="<?= $buildUrl('staff/shift-stats') ?>" class="btn btn-secondary">
                        <i class="fas fa-redo me-1"></i>Tháng này
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Thống kê tổng quan -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">Tổng ca đã dạy</p>
                            <h3 class="mb-0"><?= number_format($stats['total_shifts']) ?></h3>
                            <small class="text-muted">Đến hôm nay</small>
                        </div>
                        <div class="rounded-circle bg-primary bg-opacity-10 p-3">
                            <i class="fas fa-calendar-check fa-2x text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">Tổng giờ dạy</p>
                            <h3 class="mb-0"><?= number_format($stats['total_hours'], 1) ?></h3>
                            <small class="text-muted">giờ</small>
                        </div>
                        <div class="rounded-circle bg-info bg-opacity-10 p-3">
                            <i class="fas fa-clock fa-2x text-info"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">Ca sắp tới</p>
                            <h3 class="mb-0 text-info"><?= number_format($stats['upcoming_shifts']) ?></h3>
                            <small class="text-muted">Chưa dạy</small>
                        </div>
                        <div class="rounded-circle bg-info bg-opacity-10 p-3">
                            <i class="fas fa-calendar-alt fa-2x text-info"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">Ca chờ duyệt</p>
                            <h3 class="mb-0 text-warning"><?= number_format($stats['pending_shifts']) ?></h3>
                            <small class="text-muted">Pending</small>
                        </div>
                        <div class="rounded-circle bg-warning bg-opacity-10 p-3">
                            <i class="fas fa-hourglass-half fa-2x text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Biểu đồ thống kê theo trạng thái -->
    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-chart-pie text-primary me-2"></i>Phân bố theo trạng thái</h5>
                </div>
                <div class="card-body">
                    <canvas id="statusChart" height="200"></canvas>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-chart-bar text-info me-2"></i>Giờ dạy theo ngày</h5>
                </div>
                <div class="card-body">
                    <canvas id="dailyChart" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Bảng chi tiết ca dạy -->
    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <h5 class="mb-0"><i class="fas fa-list text-primary me-2"></i>Chi Tiết Ca Dạy</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Ngày</th>
                            <th>Ca học</th>
                            <th>Giờ</th>
                            <th class="text-end">Số giờ</th>
                            <th class="text-center">Trạng thái</th>
                            <th>Người duyệt</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($shifts)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">
                                    <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                                    Không có ca dạy nào trong kỳ này
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($shifts as $shift): ?>
                                <tr <?= $shift['is_upcoming'] ? 'class="table-info bg-opacity-25"' : '' ?>>
                                    <td>
                                        <strong><?= date('d/m/Y', strtotime($shift['shift_date'])) ?></strong>
                                        <br><small class="text-muted"><?= date('l', strtotime($shift['shift_date'])) ?></small>
                                        <?php if ($shift['is_upcoming']): ?>
                                            <br><span class="badge bg-info badge-sm">Sắp tới</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($shift['shift_name']): ?>
                                            <span class="badge bg-primary"><?= htmlspecialchars($shift['shift_name']) ?></span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Ca tự chọn</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php 
                                        $start = $shift['custom_start'] ?: $shift['start_time'];
                                        $end = $shift['custom_end'] ?: $shift['end_time'];
                                        echo htmlspecialchars(substr($start, 0, 5)) . ' - ' . htmlspecialchars(substr($end, 0, 5));
                                        ?>
                                    </td>
                                    <td class="text-end">
                                        <strong><?= number_format($shift['hours'], 1) ?></strong> giờ
                                    </td>
                                    <td class="text-center">
                                        <?php
                                        $statusBadge = [
                                            'pending' => 'warning',
                                            'approved' => 'success',
                                            'rejected' => 'danger',
                                            'cancelled' => 'secondary'
                                        ];
                                        $statusText = [
                                            'pending' => 'Chờ duyệt',
                                            'approved' => 'Đã duyệt',
                                            'rejected' => 'Từ chối',
                                            'cancelled' => 'Đã hủy'
                                        ];
                                        ?>
                                        <span class="badge bg-<?= $statusBadge[$shift['status']] ?>">
                                            <?= $statusText[$shift['status']] ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($shift['approver_name']): ?>
                                            <small><?= htmlspecialchars($shift['approver_name']) ?></small>
                                            <?php if ($shift['approved_at']): ?>
                                                <br><small class="text-muted"><?= date('d/m H:i', strtotime($shift['approved_at'])) ?></small>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                    <?php if (!empty($shifts)): ?>
                        <tfoot class="table-light">
                            <tr>
                                <th colspan="3" class="text-end">TỔNG CỘNG:</th>
                                <th class="text-end"><?= number_format($stats['total_hours'], 1) ?> giờ</th>
                                <th colspan="2"></th>
                            </tr>
                        </tfoot>
                    <?php endif; ?>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
// Biểu đồ trạng thái
const statusData = <?= json_encode($statusStats) ?>;
const statusLabels = statusData.map(item => {
    const map = {
        'pending': 'Chờ duyệt',
        'approved': 'Đã duyệt',
        'rejected': 'Từ chối',
        'cancelled': 'Đã hủy'
    };
    return map[item.status] || item.status;
});
const statusCounts = statusData.map(item => item.count);
const statusColors = statusData.map(item => {
    const colors = {
        'pending': '#ffc107',
        'approved': '#198754',
        'rejected': '#dc3545',
        'cancelled': '#6c757d'
    };
    return colors[item.status] || '#6c757d';
});

new Chart(document.getElementById('statusChart'), {
    type: 'doughnut',
    data: {
        labels: statusLabels,
        datasets: [{
            data: statusCounts,
            backgroundColor: statusColors,
            borderWidth: 2,
            borderColor: '#fff'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});

// Biểu đồ theo ngày
const dailyData = <?= json_encode($dailyStats) ?>;
const dailyLabels = dailyData.map(item => {
    const date = new Date(item.date);
    return date.getDate() + '/' + (date.getMonth() + 1);
});
const dailyHours = dailyData.map(item => parseFloat(item.total_hours));

new Chart(document.getElementById('dailyChart'), {
    type: 'bar',
    data: {
        labels: dailyLabels,
        datasets: [{
            label: 'Số giờ dạy',
            data: dailyHours,
            backgroundColor: 'rgba(13, 110, 253, 0.8)',
            borderColor: 'rgb(13, 110, 253)',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return value + 'h';
                    }
                }
            }
        }
    }
});
</script>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/modern.php';
?>