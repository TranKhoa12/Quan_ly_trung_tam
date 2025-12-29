<?php
require_once __DIR__ . '/../layouts/main.php';

$scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
$scriptDir = str_replace('\\', '/', dirname($scriptName));
$basePath = rtrim($scriptDir, '/');
if ($basePath === '' || $basePath === '.') {
    $basePath = '';
}

ob_start();
?>

<?= pageHeader(
    'Báo cáo thuế khấu trừ 10%',
    'Tổng hợp gross / thuế / net theo tháng và chi tiết theo nhân viên',
    '<a href="' . $basePath . '/teaching-shifts/payroll" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-2"></i>Quay lại bảng lương
     </a>'
) ?>

<style>
.tax-report-card { box-shadow: 0 2px 8px rgba(0,0,0,0.06); }
.table-hover tbody tr:hover { background: #f8fafc; }
.badge-month { background: #eef2ff; color: #4338ca; }
</style>

<div class="p-3">
    <div class="card tax-report-card mb-3">
        <div class="card-body">
            <form class="row g-3 align-items-end" method="GET" action="<?= $basePath ?>/teaching-shifts/tax-report">
                <div class="col-md-3">
                    <label class="form-label">Năm</label>
                    <input type="number" name="year" class="form-control" value="<?= htmlspecialchars($year) ?>" min="2000" max="2100">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Tháng (tùy chọn, định dạng YYYY-MM)</label>
                    <input type="text" name="month" class="form-control" value="<?= htmlspecialchars($month) ?>" placeholder="<?= date('Y-m') ?>">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search me-2"></i>Xem báo cáo
                    </button>
                    <a class="btn btn-outline-success ms-2" href="<?= $basePath ?>/teaching-shifts/tax-report/export?year=<?= urlencode($year) ?><?= $month ? '&month=' . urlencode($month) : '' ?>">
                        <i class="fas fa-file-csv me-2"></i>Xuất CSV
                    </a>
                </div>
            </form>
        </div>
    </div>

    <?php if (empty($month)): ?>
        <?php
            $grossYear = $yearTotals['gross_sum'] ?? 0;
            $taxYear = $yearTotals['tax_sum'] ?? 0;
            $netYear = $yearTotals['net_sum'] ?? 0;
            $staffYear = $yearTotals['staff_count'] ?? 0;
            $periodsYear = $yearTotals['periods'] ?? 0;
        ?>
        <div class="row g-3 mb-3">
            <div class="col-md-3">
                <?= statsCard('fas fa-coins', 'Tổng gross năm', number_format($grossYear, 0, ',', '.') . ' ₫', '', 'primary') ?>
            </div>
            <div class="col-md-3">
                <?= statsCard('fas fa-percent', 'Tổng thuế khấu trừ', number_format($taxYear, 0, ',', '.') . ' ₫', '', 'warning') ?>
            </div>
            <div class="col-md-3">
                <?= statsCard('fas fa-hand-holding-usd', 'Tổng thực nhận', number_format($netYear, 0, ',', '.') . ' ₫', '', 'success') ?>
            </div>
            <div class="col-md-3">
                <?= statsCard('fas fa-users', 'Số NV / kỳ', $staffYear . ' NV / ' . $periodsYear . ' kỳ', '', 'info') ?>
            </div>
        </div>

        <div class="card tax-report-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0"><i class="fas fa-table me-2 text-primary"></i>Tổng hợp theo tháng (<?= $year ?>)</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Tháng</th>
                                <th class="text-end">Gross</th>
                                <th class="text-end">Thuế</th>
                                <th class="text-end">Net</th>
                                <th class="text-center">Số NV</th>
                                <th class="text-center">Chi tiết</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($monthlySummary)): ?>
                                <tr><td colspan="6" class="text-center text-muted">Chưa có dữ liệu</td></tr>
                            <?php else: ?>
                                <?php foreach ($monthlySummary as $row): ?>
                                    <tr>
                                        <td><span class="badge badge-month"><?= htmlspecialchars($row['month_key']) ?></span></td>
                                        <td class="text-end"><?= number_format($row['gross_sum'], 0, ',', '.') ?> ₫</td>
                                        <td class="text-end text-danger"><?= number_format($row['tax_sum'], 0, ',', '.') ?> ₫</td>
                                        <td class="text-end text-success fw-semibold"><?= number_format($row['net_sum'], 0, ',', '.') ?> ₫</td>
                                        <td class="text-center"><?= (int)$row['staff_count'] ?></td>
                                        <td class="text-center">
                                            <a class="btn btn-sm btn-outline-primary" href="<?= $basePath ?>/teaching-shifts/tax-report?year=<?= urlencode($year) ?>&month=<?= urlencode($row['month_key']) ?>">
                                                Xem chi tiết
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php else: ?>
        <?php
            $mGross = $monthTotals['gross_sum'] ?? 0;
            $mTax = $monthTotals['tax_sum'] ?? 0;
            $mNet = $monthTotals['net_sum'] ?? 0;
            $mStaff = $monthTotals['staff_count'] ?? 0;
        ?>
        <div class="row g-3 mb-3">
            <div class="col-md-3">
                <?= statsCard('fas fa-calendar', 'Kỳ lương', htmlspecialchars($month), 'Đã lọc', 'secondary') ?>
            </div>
            <div class="col-md-3">
                <?= statsCard('fas fa-coins', 'Gross', number_format($mGross, 0, ',', '.') . ' ₫', '', 'primary') ?>
            </div>
            <div class="col-md-3">
                <?= statsCard('fas fa-percent', 'Thuế', number_format($mTax, 0, ',', '.') . ' ₫', '', 'warning') ?>
            </div>
            <div class="col-md-3">
                <?= statsCard('fas fa-hand-holding-usd', 'Thực nhận', number_format($mNet, 0, ',', '.') . ' ₫', '', 'success') ?>
            </div>
        </div>

        <div class="card tax-report-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0"><i class="fas fa-users me-2 text-primary"></i>Chi tiết nhân viên (<?= htmlspecialchars($month) ?>)</h5>
                    <a class="btn btn-outline-secondary" href="<?= $basePath ?>/teaching-shifts/tax-report?year=<?= urlencode($year) ?>">
                        <i class="fas fa-arrow-left me-2"></i>Về tổng hợp năm
                    </a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Nhân viên</th>
                                <th class="text-end">Gross</th>
                                <th class="text-end">Thuế</th>
                                <th class="text-end">Net</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($monthDetails)): ?>
                                <tr><td colspan="4" class="text-center text-muted">Chưa có dữ liệu</td></tr>
                            <?php else: ?>
                                <?php foreach ($monthDetails as $row): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($row['full_name'] ?? ('NV #' . $row['staff_id'])) ?></td>
                                        <td class="text-end"><?= number_format($row['gross_sum'], 0, ',', '.') ?> ₫</td>
                                        <td class="text-end text-danger"><?= number_format($row['tax_sum'], 0, ',', '.') ?> ₫</td>
                                        <td class="text-end text-success fw-semibold"><?= number_format($row['net_sum'], 0, ',', '.') ?> ₫</td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
useModernLayout('Báo cáo thuế khấu trừ', $content);
?>
