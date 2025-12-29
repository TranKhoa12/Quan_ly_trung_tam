<?php
require_once __DIR__ . '/../layouts/main.php';

// Compute base path
$scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
$scriptDir = str_replace('\\', '/', dirname($scriptName));
$basePath = rtrim($scriptDir, '/');
if ($basePath === '' || $basePath === '.') {
    $basePath = '';
}

// Page content
ob_start();
?>

<?= pageHeader(
    'Bảng lương ca dạy', 
    'Tổng hợp số giờ đã duyệt và tính tiền lương cho từng nhân viên',
    '<div class="d-flex gap-2">
        <a href="' . $basePath . '/teaching-shifts/payroll/report" class="btn btn-outline-info">
            <i class="fas fa-chart-bar me-2"></i>Báo cáo thống kê
        </a>
        <a href="' . $basePath . '/teaching-shifts/tax-report" class="btn btn-outline-warning">
            <i class="fas fa-file-invoice-dollar me-2"></i>Báo cáo thuế 10%
        </a>
        <a href="' . $basePath . '/teaching-shifts/admin" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Quay lại
        </a>
    </div>'
) ?>

<style>
/* Custom styles for payroll page */
.filter-section-payroll {
    background: white;
    padding: 1.5rem;
    border-radius: 8px;
    margin-bottom: 1.5rem;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

.filter-section-payroll .form-label {
    color: #495057;
    font-weight: 600;
    margin-bottom: 0.5rem;
}

.filter-section-payroll .form-control {
    border: 1px solid #dee2e6;
    background: white;
    padding: 0.75rem 1rem;
}

.filter-section-payroll .btn-primary {
    background: #0d6efd;
    color: white;
    border: none;
    padding: 0.75rem 2rem;
    font-weight: 600;
}

.filter-section-payroll .btn-primary:hover {
    background: #0b5ed7;
}

.period-info-banner {
    background: #f8f9fa;
    padding: 1.25rem;
    border-radius: 8px;
    border-left: 4px solid #0d6efd;
    margin-bottom: 1.5rem;
}

.staff-avatar-payroll {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #0d6efd;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 600;
    font-size: 0.875rem;
    flex-shrink: 0;
}

.shifts-badge-payroll {
    background: #dbeafe;
    color: #1e40af;
    padding: 0.375rem 0.75rem;
    border-radius: 6px;
    font-weight: 600;
    font-size: 0.875rem;
}

.hours-display-payroll {
    font-weight: 600;
    color: #4361ee;
    font-size: 1rem;
}

.amount-display-payroll {
    font-size: 1.125rem;
    font-weight: 700;
    color: #059669;
}

.empty-state-payroll {
    text-align: center;
    padding: 4rem 2rem;
    color: #9ca3af;
}

.empty-state-payroll i {
    font-size: 4rem;
    color: #d1d5db;
    margin-bottom: 1rem;
}

/* Custom Vietnamese Month Picker */
.vietnam-month-picker {
    position: relative;
}

.vietnam-month-input {
    cursor: pointer;
    background: white;
}

.vietnam-month-dropdown {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: white;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    z-index: 1000;
    display: none;
    max-height: 400px;
    overflow-y: auto;
    margin-top: 4px;
}

.vietnam-month-dropdown.show {
    display: block;
}

.month-year-header {
    padding: 12px 16px;
    background: #0d6efd;
    color: white;
    font-weight: 600;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-radius: 8px 8px 0 0;
}

.year-nav-btn {
    background: rgba(255,255,255,0.2);
    border: none;
    color: white;
    width: 32px;
    height: 32px;
    border-radius: 6px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
}

.year-nav-btn:hover {
    background: rgba(255,255,255,0.3);
}

.months-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 8px;
    padding: 16px;
}

.month-item {
    padding: 12px;
    text-align: center;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.2s;
    border: 1px solid #e5e7eb;
    background: white;
    font-weight: 500;
}

.month-item:hover {
    background: #f0f9ff;
    border-color: #667eea;
    color: #667eea;
}

.month-item.selected {
    background: #0d6efd;
    color: white;
    border-color: #0d6efd;
}

.month-item.current {
    border-color: #10b981;
    color: #10b981;
    font-weight: 600;
}
</style>

<div class="p-3">
    <!-- Filter Section -->
    <div class="filter-section-payroll">
        <form class="row g-3 align-items-end" method="GET" action="<?= $basePath ?>/teaching-shifts/payroll" id="payrollForm">
            <div class="col-md-4">
                <label class="form-label">
                    <i class="fas fa-calendar-alt me-2"></i>Chọn tháng tính lương
                </label>
                <div class="vietnam-month-picker">
                    <input type="text" class="form-control vietnam-month-input" id="monthDisplay" 
                           placeholder="Chọn tháng..." readonly>
                    <input type="hidden" name="month" id="monthValue" value="<?= htmlspecialchars($month) ?>" required>
                    
                    <div class="vietnam-month-dropdown" id="monthDropdown">
                        <div class="month-year-header">
                            <button type="button" class="year-nav-btn" id="prevYear">
                                <i class="fas fa-chevron-left"></i>
                            </button>
                            <span id="currentYear">2025</span>
                            <button type="button" class="year-nav-btn" id="nextYear">
                                <i class="fas fa-chevron-right"></i>
                            </button>
                        </div>
                        <div class="months-grid" id="monthsGrid"></div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-search me-2"></i>Xem bảng lương
                </button>
            </div>
        </form>
    </div>

    <!-- Statistics Cards -->
    <?php if (!empty($report)): ?>
        <?php
            $totalStaff = count($report);
            $totalHoursSum = array_sum(array_column($report, 'total_hours'));
            $totalAmountSum = array_sum(array_column($report, 'total_amount'));
            $totalTaxSum = isset($totalTax) ? $totalTax : array_sum(array_column($report, 'tax_amount'));
            $totalNetSum = isset($totalNet) ? $totalNet : array_sum(array_column($report, 'net_amount'));
            $totalShiftsSum = array_sum(array_map(function($r) { return (int)($r['total_shifts'] ?? 0); }, $report));
        ?>
        <div class="row g-3 mb-4">
            <div class="col-xl-3 col-md-6">
                <?= statsCard('fas fa-users', 'Tổng nhân viên', $totalStaff, 'Nhân viên có ca dạy', 'primary') ?>
            </div>
            <div class="col-xl-3 col-md-6">
                <?= statsCard('fas fa-calendar-check', 'Tổng ca dạy', $totalShiftsSum, 'Ca đã được duyệt', 'warning') ?>
            </div>
            <div class="col-xl-3 col-md-6">
                <?= statsCard('fas fa-clock', 'Tổng giờ dạy', number_format($totalHoursSum, 1) . 'h', 'Tổng số giờ', 'info') ?>
            </div>
            <div class="col-xl-3 col-md-6">
                <?= statsCard('fas fa-money-bill-wave', 'Tổng chi phí (gross)', number_format($totalAmountSum, 0, ',', '.') . ' đ', 'Trước thuế', 'success') ?>
            </div>
            <div class="col-xl-3 col-md-6">
                <?= statsCard('fas fa-percent', 'Thuế tạm khấu trừ (10%)', number_format($totalTaxSum, 0, ',', '.') . ' đ', 'Áp dụng cho part-time', 'secondary') ?>
            </div>
            <div class="col-xl-3 col-md-6">
                <?= statsCard('fas fa-hand-holding-usd', 'Thực nhận (net)', number_format($totalNetSum, 0, ',', '.') . ' đ', 'Sau thuế', 'primary') ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Period Info -->
    <?php if (!empty($report)): ?>
        <div class="period-info-banner">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="mb-1">
                        <i class="fas fa-calendar-alt me-2"></i>
                        Kỳ lương: <?= date('d/m/Y', strtotime($periodStart)) ?> - <?= date('d/m/Y', strtotime($periodEnd)) ?>
                    </h6>
                    <p class="mb-0 small text-muted">
                        <i class="fas fa-info-circle me-1"></i>
                        Chỉ bao gồm các ca đã được duyệt. Tính thuế tạm khấu trừ 10% cho nhân sự part-time.
                    </p>
                </div>
                <div class="d-flex gap-2">
                    <?php if (!empty($storedPayroll)): ?>
                        <a href="<?= $basePath ?>/teaching-shifts/payroll/print?month=<?= urlencode($month) ?>" 
                           target="_blank" class="btn btn-info">
                            <i class="fas fa-print me-2"></i>In tất cả phiếu lương
                        </a>
                        <form method="POST" action="<?= $basePath ?>/teaching-shifts/payroll/cancel" onsubmit="return confirm('Xác nhận HỦY bảng lương tháng này? Bảng lương sẽ chuyển sang trạng thái đã hủy và bạn có thể lưu lại bảng mới!')">
                            <input type="hidden" name="month" value="<?= htmlspecialchars($month) ?>">
                            <button type="submit" class="btn btn-warning">
                                <i class="fas fa-ban me-2"></i>Hủy bảng lương
                            </button>
                        </form>
                    <?php endif; ?>
                    <form method="POST" action="<?= $basePath ?>/teaching-shifts/payroll/finalize" onsubmit="return confirm('Xác nhận lưu bảng lương tháng này?')">
                        <input type="hidden" name="month" value="<?= htmlspecialchars($month) ?>">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save me-2"></i>Lưu bảng lương
                        </button>
                    </form>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Payroll Table -->
    <div class="stats-card">
        <div class="card-body">
            <?php if (empty($report)): ?>
                <div class="empty-state-payroll">
                    <i class="fas fa-inbox"></i>
                    <h5 class="mt-3 mb-2">Không có ca đã duyệt trong tháng này</h5>
                    <p>Hãy chọn tháng khác hoặc duyệt các ca dạy trước</p>
                </div>
            <?php else: ?>
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="mb-0">
                        <i class="fas fa-list me-2"></i>Chi tiết bảng lương
                    </h6>
                    <span class="badge bg-light text-dark"><?= $totalStaff ?> nhân viên</span>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th class="border-0" style="width: 50px;">#</th>
                                <th class="border-0">Nhân viên</th>
                                <th class="border-0 text-center">Số ca</th>
                                <th class="border-0 text-center">Số giờ</th>
                                <th class="border-0 text-end">Thành tiền (gross)</th>
                                <th class="border-0 text-end">Thuế 10%</th>
                                <th class="border-0 text-end">Thực nhận</th>
                                <th class="border-0 text-center">Trạng thái</th>
                                <th class="border-0 text-center" style="width: 120px;">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $index = 1; ?>
                            <?php foreach ($report as $row): ?>
                                <?php
                                    $stored = $storedPayroll[$row['staff_id']] ?? null;
                                    $totalHours = (float)$row['total_hours'];
                                    $totalAmount = (float)$row['total_amount'];
                                    $taxAmount = (float)($row['tax_amount'] ?? 0);
                                    $netAmount = (float)($row['net_amount'] ?? ($totalAmount - $taxAmount));
                                    $totalShifts = (int)($row['total_shifts'] ?? 0);
                                    
                                    // Get initials for avatar
                                    $fullName = $row['full_name'] ?? 'N/A';
                                    $nameParts = explode(' ', $fullName);
                                    $initials = '';
                                    if (count($nameParts) >= 2) {
                                        $initials = mb_substr($nameParts[count($nameParts) - 2], 0, 1) . mb_substr($nameParts[count($nameParts) - 1], 0, 1);
                                    } else {
                                        $initials = mb_substr($fullName, 0, 2);
                                    }
                                    $initials = mb_strtoupper($initials);
                                ?>
                                <tr>
                                    <td class="text-muted fw-semibold"><?= $index++ ?></td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="staff-avatar-payroll"><?= $initials ?></div>
                                            <span class="fw-semibold"><?= htmlspecialchars($fullName) ?></span>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span class="shifts-badge-payroll"><?= $totalShifts ?> ca</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="hours-display-payroll"><?= number_format($totalHours, 1) ?>h</span>
                                    </td>
                                    <td class="text-end">
                                        <span class="amount-display-payroll"><?= number_format($totalAmount, 0, ',', '.') ?> ₫</span>
                                    </td>
                                    <td class="text-end text-danger">
                                        <?= number_format($taxAmount, 0, ',', '.') ?> ₫
                                    </td>
                                    <td class="text-end text-success fw-semibold">
                                        <?= number_format($netAmount, 0, ',', '.') ?> ₫
                                    </td>
                                    <td class="text-center">
                                        <?php if ($stored && $stored['status'] === 'active'): ?>
                                            <span class="badge bg-success">
                                                <i class="fas fa-check-circle me-1"></i>
                                                Đã lưu
                                            </span>
                                        <?php elseif ($stored && $stored['status'] === 'cancelled'): ?>
                                            <span class="badge bg-secondary">
                                                <i class="fas fa-ban me-1"></i>
                                                Đã hủy
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-warning text-dark">
                                                <i class="fas fa-clock me-1"></i>
                                                Chưa lưu
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($stored && $stored['status'] === 'active'): ?>
                                            <div class="d-flex gap-1 justify-content-center">
                                                <a href="<?= $basePath ?>/teaching-shifts/payroll/print?month=<?= urlencode($month) ?>&staff_id=<?= $row['staff_id'] ?>" 
                                                   target="_blank" class="btn btn-sm btn-info" title="In phiếu lương">
                                                    <i class="fas fa-print"></i>
                                                </a>
                                                <form method="POST" action="<?= $basePath ?>/teaching-shifts/payroll/cancel-staff" style="display: inline-block;" onsubmit="return confirm('Hủy bảng lương cho <?= htmlspecialchars($fullName) ?>?')">
                                                    <input type="hidden" name="month" value="<?= htmlspecialchars($month) ?>">
                                                    <input type="hidden" name="staff_id" value="<?= $row['staff_id'] ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-warning" title="Hủy">
                                                        <i class="fas fa-ban"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        <?php elseif ($stored && $stored['status'] === 'cancelled'): ?>
                                            <form method="POST" action="<?= $basePath ?>/teaching-shifts/payroll/save-staff" style="display: inline-block;" onsubmit="return confirm('Lưu bảng lương cho <?= htmlspecialchars($fullName) ?>?')">
                                                <input type="hidden" name="month" value="<?= htmlspecialchars($month) ?>">
                                                <input type="hidden" name="staff_id" value="<?= $row['staff_id'] ?>">
                                                <input type="hidden" name="total_hours" value="<?= $totalHours ?>">
                                                <input type="hidden" name="total_amount" value="<?= $totalAmount ?>">
                                                <button type="submit" class="btn btn-sm btn-success" title="Lưu">
                                                    <i class="fas fa-save me-1"></i>Lưu
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <form method="POST" action="<?= $basePath ?>/teaching-shifts/payroll/save-staff" style="display: inline-block;" onsubmit="return confirm('Lưu bảng lương cho <?= htmlspecialchars($fullName) ?>?')">
                                                <input type="hidden" name="month" value="<?= htmlspecialchars($month) ?>">
                                                <input type="hidden" name="staff_id" value="<?= $row['staff_id'] ?>">
                                                <input type="hidden" name="total_hours" value="<?= $totalHours ?>">
                                                <input type="hidden" name="total_amount" value="<?= $totalAmount ?>">
                                                <button type="submit" class="btn btn-sm btn-success" title="Lưu">
                                                    <i class="fas fa-save me-1"></i>Lưu
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot class="table-light">
                            <tr class="fw-bold">
                                <td colspan="2" class="text-end">TỔNG CỘNG:</td>
                                <td class="text-center">
                                    <span class="badge bg-primary"><?= $totalShiftsSum ?> ca</span>
                                </td>
                                <td class="text-center text-primary"><?= number_format($totalHoursSum, 1) ?>h</td>
                                <td class="text-end text-success fs-6 fw-semibold">
                                    <?= number_format($totalAmountSum, 0, ',', '.') ?> ₫
                                </td>
                                <td class="text-end text-danger fw-semibold">
                                    <?= number_format($totalTaxSum, 0, ',', '.') ?> ₫
                                </td>
                                <td class="text-end text-success fs-5 fw-bold">
                                    <?= number_format($totalNetSum, 0, ',', '.') ?> ₫
                                </td>
                                <td></td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
// Custom Vietnamese Month Picker
document.addEventListener('DOMContentLoaded', function() {
    const monthDisplay = document.getElementById('monthDisplay');
    const monthValue = document.getElementById('monthValue');
    const monthDropdown = document.getElementById('monthDropdown');
    const monthsGrid = document.getElementById('monthsGrid');
    const currentYearSpan = document.getElementById('currentYear');
    const prevYearBtn = document.getElementById('prevYear');
    const nextYearBtn = document.getElementById('nextYear');
    
    // Vietnamese month names
    const vietnameseMonths = [
        'Tháng 1', 'Tháng 2', 'Tháng 3', 'Tháng 4',
        'Tháng 5', 'Tháng 6', 'Tháng 7', 'Tháng 8',
        'Tháng 9', 'Tháng 10', 'Tháng 11', 'Tháng 12'
    ];
    
    let selectedYear = new Date().getFullYear();
    let selectedMonth = new Date().getMonth() + 1;
    
    // Initialize with current value if exists
    if (monthValue.value) {
        const [year, month] = monthValue.value.split('-');
        selectedYear = parseInt(year);
        selectedMonth = parseInt(month);
        updateDisplay();
    }
    
    // Function to update display
    function updateDisplay() {
        monthDisplay.value = vietnameseMonths[selectedMonth - 1] + ' ' + selectedYear;
        monthValue.value = selectedYear + '-' + String(selectedMonth).padStart(2, '0');
    }
    
    // Function to render months grid
    function renderMonths() {
        currentYearSpan.textContent = selectedYear;
        monthsGrid.innerHTML = '';
        
        const currentDate = new Date();
        const currentYear = currentDate.getFullYear();
        const currentMonth = currentDate.getMonth() + 1;
        
        vietnameseMonths.forEach((monthName, index) => {
            const monthNum = index + 1;
            const monthItem = document.createElement('div');
            monthItem.className = 'month-item';
            monthItem.textContent = monthName;
            
            // Highlight selected month
            if (monthNum === selectedMonth && selectedYear === parseInt(monthValue.value.split('-')[0])) {
                monthItem.classList.add('selected');
            }
            
            // Highlight current month
            if (monthNum === currentMonth && selectedYear === currentYear) {
                monthItem.classList.add('current');
            }
            
            monthItem.addEventListener('click', function() {
                selectedMonth = monthNum;
                updateDisplay();
                monthDropdown.classList.remove('show');
                renderMonths();
            });
            
            monthsGrid.appendChild(monthItem);
        });
    }
    
    // Show/hide dropdown
    monthDisplay.addEventListener('click', function(e) {
        e.stopPropagation();
        monthDropdown.classList.toggle('show');
        if (monthDropdown.classList.contains('show')) {
            renderMonths();
        }
    });
    
    // Year navigation
    prevYearBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        selectedYear--;
        renderMonths();
    });
    
    nextYearBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        selectedYear++;
        renderMonths();
    });
    
    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (!monthDropdown.contains(e.target) && e.target !== monthDisplay) {
            monthDropdown.classList.remove('show');
        }
    });
    
    // Prevent dropdown close when clicking inside
    monthDropdown.addEventListener('click', function(e) {
        e.stopPropagation();
    });
    
    // Initialize display
    renderMonths();
});
</script>

<?php
$content = ob_get_clean();
useModernLayout('Bảng lương ca dạy', $content);
?>
