<?php
$pageTitle = 'Quản lý lịch dạy';

$scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
$scriptDir = str_replace('\\', '/', dirname($scriptName));
$basePath = rtrim($scriptDir, '/');
if ($basePath === '' || $basePath === '.') {
    $basePath = '';
}

ob_start();
?>

<style>
/* Calendar Container */
.shifts-calendar-container {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    overflow: hidden;
}

/* Calendar Header - Compact */
.calendar-header {
    background: linear-gradient(135deg, #4361ee 0%, #4cc9f0 100%);
    color: white;
    padding: 1rem 1.25rem;
}

.calendar-controls {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.75rem;
}

.calendar-nav-group {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.calendar-month-title {
    font-size: 1.25rem;
    font-weight: 700;
    margin: 0;
    min-width: 160px;
}

.nav-btn {
    background: rgba(255, 255, 255, 0.2);
    border: none;
    color: white;
    width: 32px;
    height: 32px;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.9rem;
}

.nav-btn:hover {
    background: rgba(255, 255, 255, 0.3);
}

/* Calendar Grid - Optimized height */
.calendar-grid-wrapper {
    height: calc(100vh - 250px);
    min-height: 800px;
    display: flex;
    flex-direction: column;
    overflow: hidden;
}

.calendar-grid {
    display: grid;
    grid-template-columns: repeat(6, 1fr);
    grid-template-rows: auto;
    background: white;
    flex: 1;
    overflow: hidden;
}

.calendar-day-header {
    background: #f8f9fa;
    padding: 0.65rem;
    text-align: center;
    font-weight: 600;
    font-size: 0.85rem;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    border-bottom: 1px solid #e0e0e0;
}

.calendar-days-grid {
    display: contents;
}

.calendar-day-cell {
    background: white;
    padding: 1rem;
    border: 1px solid #e0e0e0;
    position: relative;
    transition: all 0.15s ease;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    min-height: 200px;
    height: 200px;
}

.calendar-day-cell:not(.other-month) {
    cursor: pointer;
}

.calendar-day-cell:not(.other-month):hover {
    background: #f0f9ff;
    z-index: 1;
}

.calendar-day-cell.other-month {
    background: #f9fafb;
}

.calendar-day-cell.other-month .day-number {
    color: #9ca3af;
}

.calendar-day-cell.today {
    background: #e8f4f8;
}

.calendar-day-cell.today .day-number {
    background: #1a73e8;
    color: white;
    width: 32px;
    height: 32px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    font-weight: 600;
}

.day-number {
    font-size: 0.95rem;
    font-weight: 600;
    color: #3c4043;
    margin-bottom: 0.5rem;
    display: block;
    flex-shrink: 0;
}

.shifts-container {
    display: flex;
    flex-direction: column;
    gap: 4px;
    flex: 1;
    overflow-y: auto;
    margin-top: 0.5rem;
}

.shifts-container::-webkit-scrollbar {
    width: 4px;
}

.shifts-container::-webkit-scrollbar-thumb {
    background: #d1d5db;
    border-radius: 2px;
}

.shift-group {
    background: white;
    border-left: 4px solid #4361ee;
    border-radius: 5px;
    padding: 8px 10px;
    font-size: 0.8rem;
    cursor: pointer;
    transition: all 0.15s ease;
    flex-shrink: 0;
    line-height: 1.5;
    margin-bottom: 0;
}

.shift-group:hover {
    background: #f0f9ff;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.15);
}

.shift-group-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    margin-bottom: 4px;
}

.shift-group-time {
    font-weight: 600;
    color: #1e293b;
    font-size: 0.8rem;
}

.shift-group-count {
    background: #e0e7ff;
    color: #4338ca;
    padding: 3px 8px;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 600;
}

.shift-group-staff {
    color: #64748b;
    font-size: 0.75rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.shift-group.has-pending {
    border-left-color: #f59e0b;
}

.shift-group.has-pending .shift-group-count {
    background: #fef3c7;
    color: #d97706;
}

.shift-group.all-approved {
    border-left-color: #0d9488;
}

.shift-group.all-approved .shift-group-count {
    background: #ccfbf1;
    color: #0d9488;
}

.shift-group.has-rejected {
    border-left-color: #dc2626;
}

/* Legacy badge style for single staff view */
.shift-badge {
    background: #f59e0b;
    color: white;
    border-radius: 3px;
    padding: 2px 6px;
    font-size: 0.7rem;
    cursor: pointer;
    font-weight: 500;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    transition: all 0.15s ease;
    flex-shrink: 0;
    line-height: 1.4;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 4px;
}

.shift-badge:hover {
    filter: brightness(1.1);
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
}

.shift-badge.pending {
    background: #f59e0b;
}

.shift-badge.approved {
    background: #0d9488;
}

.shift-badge.rejected {
    background: #dc2626;
}

.shift-badge.cancelled {
    background: #6b7280;
}

.shift-time {
    font-weight: 500;
    flex: 1;
}

.shift-staff-initial {
    width: 16px;
    height: 16px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.3);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.6rem;
    font-weight: 600;
    flex-shrink: 0;
}

/* Quick Actions for Pending Shifts */
.shift-badge.pending,
.shift-group.has-pending {
    position: relative;
}

.shift-badge.pending:hover .shift-quick-actions,
.shift-group.has-pending:hover .shift-quick-actions {
    display: flex;
}

.shift-quick-actions {
    position: absolute;
    top: 50%;
    right: 4px;
    transform: translateY(-50%);
    display: none;
    gap: 2px;
    z-index: 10;
    background: white;
    padding: 2px;
    border-radius: 4px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
}

.quick-action-btn {
    width: 20px;
    height: 20px;
    border: none;
    border-radius: 3px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.7rem;
    transition: all 0.15s ease;
    padding: 0;
}

.quick-action-btn:hover {
    transform: scale(1.1);
}

.quick-action-btn.approve {
    background: #0d9488;
    color: white;
}

.quick-action-btn.approve:hover {
    background: #0f766e;
}

.quick-action-btn.reject {
    background: #dc2626;
    color: white;
}

.quick-action-btn.reject:hover {
    background: #b91c1c;
}

/* Modal */
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    backdrop-filter: blur(4px);
    z-index: 9998;
    display: none;
    animation: fadeIn 0.2s ease;
}

.modal-overlay.show {
    display: block;
}

.shift-detail-modal {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: white;
    border-radius: 16px;
    width: 90%;
    max-width: 600px;
    max-height: 85vh;
    overflow: hidden;
    z-index: 9999;
    display: none;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    animation: slideUp 0.3s ease;
}

.shift-detail-modal.show {
    display: flex;
    flex-direction: column;
}

.modal-header {
    background: linear-gradient(135deg, #4361ee 0%, #4cc9f0 100%);
    color: white;
    padding: 1.5rem;
    position: relative;
}

.modal-title {
    font-size: 1.25rem;
    font-weight: 700;
    margin: 0;
}

.modal-close {
    position: absolute;
    top: 1rem;
    right: 1rem;
    background: rgba(255, 255, 255, 0.2);
    border: none;
    color: white;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s ease;
}

.modal-close:hover {
    background: rgba(255, 255, 255, 0.3);
}

.modal-body {
    padding: 1.5rem;
    overflow-y: auto;
    flex: 1;
}

.info-row {
    display: flex;
    padding: 0.75rem 0;
    border-bottom: 1px solid #f0f0f0;
}

.info-label {
    font-weight: 600;
    color: #6c757d;
    min-width: 120px;
}

.info-value {
    color: #212529;
}

.modal-footer {
    padding: 1rem 1.5rem;
    background: #f8f9fa;
    border-top: 1px solid #e9ecef;
    display: flex;
    gap: 0.75rem;
}

/* Animations */
@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes slideUp {
    from {
        opacity: 0;
        transform: translate(-50%, -45%);
    }
    to {
        opacity: 1;
        transform: translate(-50%, -50%);
    }
}

/* Legend - Compact */
.calendar-legend {
    display: flex;
    gap: 1.5rem;
    padding: 0.5rem 0.75rem;
    background: #f8f9fa;
    border-radius: 6px;
    flex-wrap: wrap;
    align-items: center;
}

.legend-item {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    font-size: 0.8rem;
}

.legend-badge {
    width: 20px;
    height: 20px;
    border-radius: 4px;
}

.legend-badge.approved { background: #0d9488; }
.legend-badge.pending { background: #f59e0b; }
.legend-badge.rejected { background: #dc2626; }

/* Stats Summary - Compact */
.stats-summary {
    display: flex;
    gap: 0.75rem;
    margin-bottom: 1rem;
}

.stat-card {
    flex: 1;
    background: white;
    border-radius: 8px;
    padding: 0.75rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.stat-icon {
    width: 36px;
    height: 36px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.1rem;
    flex-shrink: 0;
}

.stat-icon.pending {
    background: #fef3c7;
    color: #f59e0b;
}

.stat-icon.approved {
    background: #d1fae5;
    color: #0d9488;
}

.stat-icon.total {
    background: #e0f2fe;
    color: #1a73e8;
}

.stat-content {
    flex: 1;
}

.stat-label {
    font-size: 0.75rem;
    color: #6c757d;
    margin-bottom: 0.15rem;
}

.stat-value {
    font-size: 1.4rem;
    font-weight: 700;
    color: #212529;
}
</style>

<!-- Compact Header -->
<div style="display: flex; gap: 0.75rem; align-items: center; margin-bottom: 1rem; flex-wrap: wrap;">
    <!-- Staff Selector -->
    <div style="display: flex; align-items: center; gap: 0.5rem;">
        <label style="font-weight: 600; font-size: 0.9rem; color: #1e293b; white-space: nowrap;">
            <i class="fas fa-users me-1"></i>Nhân viên:
        </label>
        <select class="form-select form-select-sm" id="staffSelector" onchange="selectStaff(this.value)" style="width: 200px;">
            <option value="all">Tất cả nhân viên</option>
            <?php foreach ($staffList as $staff): ?>
                <option value="<?= $staff['id'] ?>"><?= htmlspecialchars($staff['full_name']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <!-- Bulk Approve Buttons -->
    <div style="display: none; gap: 0.5rem;" id="bulkApproveButtons">
        <button type="button" class="btn btn-success btn-sm" onclick="quickApproveCurrentMonth()" id="bulkApproveMonthBtn">
            <i class="fas fa-calendar-check me-1"></i>Duyệt tất cả tháng này
        </button>
        <button type="button" class="btn btn-outline-success btn-sm" onclick="openBulkApproveModal()">
            <i class="fas fa-filter me-1"></i>Duyệt với bộ lọc
        </button>
    </div>
    
    <!-- Stats Summary -->
    <div class="stats-summary" style="margin-bottom: 0; flex: 1;">
        <div class="stat-card">
            <div class="stat-icon total">
                <i class="fas fa-calendar-alt"></i>
            </div>
            <div class="stat-content">
                <div class="stat-label">Tổng</div>
                <div class="stat-value" id="statTotal">0</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon pending">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-content">
                <div class="stat-label">Chờ</div>
                <div class="stat-value" id="statPending">0</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon approved">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-content">
                <div class="stat-label">Duyệt</div>
                <div class="stat-value" id="statApproved">0</div>
            </div>
        </div>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= $basePath ?>/teaching-shifts/admin/create" class="btn btn-success btn-sm">
            <i class="fas fa-plus me-1"></i>Đăng ký ca dạy
        </a>
        <a href="<?= $basePath ?>/teaching-shifts/admin?view=list" class="btn btn-outline-primary btn-sm">
            <i class="fas fa-list me-1"></i>Danh sách
        </a>
    </div>
</div>

<!-- Legend - Inline with calendar header -->
<div class="calendar-legend" style="margin-bottom: 0.75rem; padding: 0.5rem 0.75rem;">
    <strong style="color: #212529; font-size: 0.85rem;">Trạng thái:</strong>
    <div class="legend-item">
        <div class="legend-badge approved"></div>
        <span>Đã duyệt</span>
    </div>
    <div class="legend-item">
        <div class="legend-badge pending"></div>
        <span>Chờ duyệt</span>
    </div>
    <div class="legend-item">
        <div class="legend-badge rejected"></div>
        <span>Từ chối</span>
    </div>
</div>

<!-- Calendar Container -->
<div class="shifts-calendar-container">
    <!-- Calendar Header -->
    <div class="calendar-header">
        <div class="calendar-controls">
            <div class="calendar-nav-group">
                <button class="nav-btn" onclick="previousMonth()">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <h2 class="calendar-month-title" id="currentMonth">Tháng 11, 2025</h2>
                <button class="nav-btn" onclick="nextMonth()">
                    <i class="fas fa-chevron-right"></i>
                </button>
                <button class="nav-btn" onclick="goToday()" title="Hôm nay">
                    <i class="fas fa-calendar-day"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Month View -->
    <div id="monthView" class="calendar-grid-wrapper">
        <div class="calendar-grid" style="grid-template-columns: repeat(6, 1fr);">
            <!-- Day headers (Monday to Saturday only) -->
            <div class="calendar-day-header">T2</div>
            <div class="calendar-day-header">T3</div>
            <div class="calendar-day-header">T4</div>
            <div class="calendar-day-header">T5</div>
            <div class="calendar-day-header">T6</div>
            <div class="calendar-day-header">T7</div>

            <!-- Calendar days will be generated by JavaScript -->
            <div id="calendarDays" class="calendar-days-grid"></div>
        </div>
    </div>
</div>

<!-- Shift Detail Modal -->
<div class="modal-overlay" id="modalOverlay" onclick="closeShiftModal()"></div>
<div class="shift-detail-modal" id="shiftModal">
    <div class="modal-header">
        <h3 class="modal-title">
            <i class="fas fa-info-circle me-2"></i>
            Chi tiết ca dạy
        </h3>
        <button class="modal-close" onclick="closeShiftModal()">
            <i class="fas fa-times"></i>
        </button>
    </div>
    <div class="modal-body" id="modalBody">
        <!-- Content will be populated by JavaScript -->
    </div>
    <div class="modal-footer" id="modalFooter">
        <!-- Actions will be populated by JavaScript -->
    </div>
</div>

<!-- Bulk Approve Modal -->
<div class="modal-overlay" id="bulkApproveOverlay" onclick="closeBulkApproveModal()"></div>
<div class="shift-detail-modal" id="bulkApproveModal" style="max-width: 500px;">
    <div class="modal-header">
        <h3 class="modal-title">
            <i class="fas fa-check-double me-2"></i>
            Duyệt hàng loạt ca dạy
        </h3>
        <button class="modal-close" onclick="closeBulkApproveModal()">
            <i class="fas fa-times"></i>
        </button>
    </div>
    <div class="modal-body">
        <div class="mb-3">
            <label class="form-label fw-bold">Nhân viên</label>
            <div class="form-control-plaintext" id="bulkStaffName"></div>
        </div>
        <div class="row g-3 mb-3">
            <div class="col-md-6">
                <label class="form-label fw-bold">Từ ngày</label>
                <input type="date" class="form-control" id="bulkDateFrom">
            </div>
            <div class="col-md-6">
                <label class="form-label fw-bold">Đến ngày</label>
                <input type="date" class="form-control" id="bulkDateTo">
            </div>
        </div>
        <div class="mb-3">
            <label class="form-label fw-bold">Trạng thái ca</label>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="bulkIncludePending" checked>
                <label class="form-check-label" for="bulkIncludePending">
                    <span class="badge bg-warning">Chờ duyệt</span> - Chỉ duyệt các ca đang chờ
                </label>
            </div>
        </div>
        <div class="alert alert-info mb-0">
            <i class="fas fa-info-circle me-2"></i>
            <span id="bulkPreviewCount">Đang tính toán...</span>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" onclick="closeBulkApproveModal()">
            <i class="fas fa-times me-1"></i>Hủy
        </button>
        <button type="button" class="btn btn-success" onclick="executeBulkApprove()" id="bulkApproveExecuteBtn">
            <i class="fas fa-check-double me-1"></i>Duyệt tất cả
        </button>
    </div>
</div>

<script>
// Data from PHP
const shiftsData = <?= json_encode($registrations ?? []) ?>;
const staffData = <?= json_encode($staffList ?? []) ?>;
const today = new Date();
let currentDate = new Date();
let selectedStaffId = 'all';

// Initialize
function initCalendar() {
    updateStats();
    renderCalendar();
}

function selectStaff(staffId) {
    selectedStaffId = staffId;
    
    // Update select dropdown
    document.getElementById('staffSelector').value = staffId;
    
    // Show/hide bulk approve buttons
    const bulkButtons = document.getElementById('bulkApproveButtons');
    if (staffId !== 'all') {
        const year = currentDate.getFullYear();
        const month = currentDate.getMonth();
        const firstDay = formatDateForInput(new Date(year, month, 1));
        const lastDay = formatDateForInput(new Date(year, month + 1, 0));
        
        const staffPendingShifts = shiftsData.filter(s => 
            s.staff_id == staffId && 
            s.status === 'pending' &&
            s.shift_date >= firstDay &&
            s.shift_date <= lastDay
        );
        
        if (staffPendingShifts.length > 0) {
            bulkButtons.style.display = 'flex';
            // Update month button text with count
            const monthBtn = document.getElementById('bulkApproveMonthBtn');
            monthBtn.innerHTML = `<i class="fas fa-calendar-check me-1"></i>Duyệt tất cả tháng này (${staffPendingShifts.length})`;
        } else {
            bulkButtons.style.display = 'none';
        }
    } else {
        bulkButtons.style.display = 'none';
    }
    
    // Re-render calendar
    renderCalendar();
    updateStats();
}

function updateStats() {
    const filteredShifts = getFilteredShifts();
    const pending = filteredShifts.filter(s => s.status === 'pending').length;
    const approved = filteredShifts.filter(s => s.status === 'approved').length;
    
    document.getElementById('statTotal').textContent = filteredShifts.length;
    document.getElementById('statPending').textContent = pending;
    document.getElementById('statApproved').textContent = approved;
}

function getFilteredShifts() {
    if (selectedStaffId === 'all') {
        return shiftsData;
    }
    return shiftsData.filter(s => s.staff_id == selectedStaffId);
}

function renderCalendar() {
    const year = currentDate.getFullYear();
    const month = currentDate.getMonth();
    
    // Update month display
    const monthNames = ['Tháng 1', 'Tháng 2', 'Tháng 3', 'Tháng 4', 'Tháng 5', 'Tháng 6',
                       'Tháng 7', 'Tháng 8', 'Tháng 9', 'Tháng 10', 'Tháng 11', 'Tháng 12'];
    document.getElementById('currentMonth').textContent = `${monthNames[month]}, ${year}`;
    
    // Get first day of month and number of days
    const firstDay = new Date(year, month, 1);
    const lastDay = new Date(year, month + 1, 0);
    const daysInMonth = lastDay.getDate();
    const startingDayOfWeek = firstDay.getDay(); // 0 = Sunday, 1 = Monday, ...
    
    // Get previous month's last days
    const prevMonthLastDay = new Date(year, month, 0).getDate();
    
    const calendarDaysContainer = document.getElementById('calendarDays');
    calendarDaysContainer.innerHTML = '';
    
    // Adjust starting day (skip Sunday, start from Monday)
    // If month starts on Sunday (0), we need to show previous week starting from Monday
    let daysToShowFromPrevMonth = startingDayOfWeek === 0 ? 6 : startingDayOfWeek - 1;
    
    // Previous month's days (Monday to Saturday only)
    for (let i = daysToShowFromPrevMonth - 1; i >= 0; i--) {
        const day = prevMonthLastDay - i;
        const cell = createDayCell(day, true, null);
        calendarDaysContainer.appendChild(cell);
    }
    
    // Current month's days (skip Sundays)
    for (let day = 1; day <= daysInMonth; day++) {
        const date = new Date(year, month, day);
        const dayOfWeek = date.getDay();
        
        // Skip Sunday (0)
        if (dayOfWeek === 0) continue;
        
        const isToday = date.toDateString() === today.toDateString();
        const cell = createDayCell(day, false, date, isToday);
        calendarDaysContainer.appendChild(cell);
    }
    
    // Next month's days to fill the grid (6 columns x rows, skip Sundays)
    const totalCells = calendarDaysContainer.children.length;
    const remainingCells = Math.ceil(totalCells / 6) * 6 - totalCells;
    for (let day = 1; day <= remainingCells; day++) {
        const cell = createDayCell(day, true, null);
        calendarDaysContainer.appendChild(cell);
    }
}

function createDayCell(day, isOtherMonth, date, isToday = false) {
    const cell = document.createElement('div');
    cell.className = 'calendar-day-cell';
    if (isOtherMonth) cell.classList.add('other-month');
    if (isToday) cell.classList.add('today');
    
    const dayNumber = document.createElement('span');
    dayNumber.className = 'day-number';
    dayNumber.textContent = day;
    cell.appendChild(dayNumber);
    
    if (date && !isOtherMonth) {
        // Format date as YYYY-MM-DD in local time (not UTC)
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        const dateStr = `${year}-${month}-${day}`;
        
        // Get filtered shifts for this day
        const filteredShifts = getFilteredShifts();
        const dayShifts = filteredShifts.filter(shift => shift.shift_date === dateStr);
        
        if (dayShifts.length > 0) {
            const container = document.createElement('div');
            container.className = 'shifts-container';
            
            if (selectedStaffId === 'all') {
                // Group by time slot when viewing all staff
                const grouped = groupShiftsByTime(dayShifts);
                
                grouped.forEach(group => {
                    const shiftGroup = document.createElement('div');
                    
                    // Determine status class
                    const hasPending = group.shifts.some(s => s.status === 'pending');
                    const hasRejected = group.shifts.some(s => s.status === 'rejected');
                    const allApproved = group.shifts.every(s => s.status === 'approved');
                    
                    let statusClass = 'has-pending';
                    if (allApproved) statusClass = 'all-approved';
                    else if (hasRejected) statusClass = 'has-rejected';
                    
                    shiftGroup.className = `shift-group ${statusClass}`;
                    
                    // Get staff names (limit display)
                    const staffNames = group.shifts.map(s => {
                        const staff = staffData.find(st => st.id == s.staff_id);
                        return staff ? staff.full_name : 'N/A';
                    });
                    const displayNames = staffNames.slice(0, 3).join(', ') + (staffNames.length > 3 ? '...' : '');
                    
                    let quickActionsHtml = '';
                    if (hasPending) {
                        quickActionsHtml = `
                            <div class="shift-quick-actions">
                                <button class="quick-action-btn approve" title="Duyệt tất cả ca pending">
                                    <i class="fas fa-check"></i>
                                </button>
                                <button class="quick-action-btn reject" title="Từ chối tất cả ca pending">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        `;
                    }
                    
                    shiftGroup.innerHTML = `
                        <div class="shift-group-header">
                            <span class="shift-group-time">${group.time}</span>
                            <span class="shift-group-count">${group.shifts.length}</span>
                        </div>
                        <div class="shift-group-staff" title="${staffNames.join(', ')}">${displayNames}</div>
                        ${quickActionsHtml}
                    `;
                    
                    // Add click handler for quick actions
                    if (hasPending) {
                        const approveBtn = shiftGroup.querySelector('.quick-action-btn.approve');
                        const rejectBtn = shiftGroup.querySelector('.quick-action-btn.reject');
                        
                        if (approveBtn) {
                            approveBtn.onclick = (e) => {
                                e.stopPropagation();
                                const pendingShifts = group.shifts.filter(s => s.status === 'pending');
                                quickApproveShifts(pendingShifts);
                            };
                        }
                        
                        if (rejectBtn) {
                            rejectBtn.onclick = (e) => {
                                e.stopPropagation();
                                const pendingShifts = group.shifts.filter(s => s.status === 'pending');
                                quickRejectShifts(pendingShifts);
                            };
                        }
                    }
                    
                    shiftGroup.onclick = (e) => {
                        e.stopPropagation();
                        showShiftGroupModal(group, dateStr);
                    };
                    
                    container.appendChild(shiftGroup);
                });
            } else {
                // Single staff view - also show as groups (same style as all staff)
                const grouped = groupShiftsByTime(dayShifts);
                
                grouped.forEach(group => {
                    const shiftGroup = document.createElement('div');
                    
                    // Determine status class (only one shift per group in single staff view)
                    const shift = group.shifts[0];
                    let statusClass = 'has-pending';
                    if (shift.status === 'approved') statusClass = 'all-approved';
                    else if (shift.status === 'rejected') statusClass = 'has-rejected';
                    
                    shiftGroup.className = `shift-group ${statusClass}`;
                    
                    let quickActionsHtml = '';
                    if (shift.status === 'pending') {
                        quickActionsHtml = `
                            <div class="shift-quick-actions">
                                <button class="quick-action-btn approve" title="Duyệt ca">
                                    <i class="fas fa-check"></i>
                                </button>
                                <button class="quick-action-btn reject" title="Từ chối ca">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        `;
                    }
                    
                    shiftGroup.innerHTML = `
                        <div class="shift-group-header">
                            <span class="shift-group-time">${group.time}</span>
                            <span class="shift-group-hours">${shift.hours}h</span>
                        </div>
                        ${quickActionsHtml}
                    `;
                    
                    // Add click handler for quick actions
                    if (shift.status === 'pending') {
                        const approveBtn = shiftGroup.querySelector('.quick-action-btn.approve');
                        const rejectBtn = shiftGroup.querySelector('.quick-action-btn.reject');
                        
                        if (approveBtn) {
                            approveBtn.onclick = (e) => {
                                e.stopPropagation();
                                quickApproveShifts([shift]);
                            };
                        }
                        
                        if (rejectBtn) {
                            rejectBtn.onclick = (e) => {
                                e.stopPropagation();
                                quickRejectShifts([shift]);
                            };
                        }
                    }
                    
                    shiftGroup.onclick = (e) => {
                        e.stopPropagation();
                        showShiftDetail(shift);
                    };
                    
                    container.appendChild(shiftGroup);
                });
            }
            
            cell.appendChild(container);
        }
    }
    
    return cell;
}

function groupShiftsByTime(shifts) {
    const groups = {};
    
    shifts.forEach(shift => {
        const start = shift.custom_start || shift.preset_start;
        const end = shift.custom_end || shift.preset_end;
        const timeKey = `${start}-${end}`;
        const timeDisplay = start && end 
            ? `${start.substring(0, 5)} - ${end.substring(0, 5)}`
            : 'N/A';
        
        if (!groups[timeKey]) {
            groups[timeKey] = {
                time: timeDisplay,
                timeKey: timeKey,
                shifts: []
            };
        }
        groups[timeKey].shifts.push(shift);
    });
    
    // Convert to array and sort by start time
    return Object.values(groups).sort((a, b) => {
        return a.timeKey.localeCompare(b.timeKey);
    });
}

function showShiftGroupModal(group, dateStr) {
    const modalBody = document.getElementById('modalBody');
    const modalFooter = document.getElementById('modalFooter');
    
    // Build list of shifts
    let shiftsHtml = '<div style="max-height: 400px; overflow-y: auto;">';
    group.shifts.forEach(shift => {
        const staff = staffData.find(s => s.id == shift.staff_id);
        const statusLabels = {
            'pending': 'Chờ duyệt',
            'approved': 'Đã duyệt',
            'rejected': 'Từ chối',
            'cancelled': 'Đã hủy'
        };
        const statusColors = {
            'pending': 'warning',
            'approved': 'success',
            'rejected': 'danger',
            'cancelled': 'secondary'
        };
        
        shiftsHtml += `
            <div style="border: 1px solid #e9ecef; border-radius: 8px; padding: 1rem; margin-bottom: 0.75rem; background: #f8f9fa;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                    <strong style="color: #212529; font-size: 0.95rem;">
                        <i class="fas fa-user me-1"></i>${staff ? staff.full_name : 'N/A'}
                    </strong>
                    <span class="badge bg-${statusColors[shift.status]}">${statusLabels[shift.status]}</span>
                </div>
                <div style="font-size: 0.85rem; color: #6c757d;">
                    <div><i class="fas fa-clock me-1"></i>Số giờ: ${shift.hours}h</div>
                    ${shift.notes ? `<div style="margin-top: 0.25rem;"><i class="fas fa-comment me-1"></i>${shift.notes}</div>` : ''}
                </div>
                ${shift.status === 'pending' ? `
                    <div style="display: flex; gap: 0.5rem; margin-top: 0.75rem;">
                        <form method="POST" action="<?= $basePath ?>/teaching-shifts/${shift.id}/delete" style="margin: 0;">
                            <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Xóa ca này?')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                        <form method="POST" action="<?= $basePath ?>/teaching-shifts/${shift.id}/status" style="flex: 1; margin: 0;">
                            <input type="hidden" name="status" value="rejected">
                            <button type="submit" class="btn btn-sm btn-outline-danger w-100" onclick="return confirm('Từ chối ca này?')">
                                <i class="fas fa-times-circle me-1"></i>Từ chối
                            </button>
                        </form>
                        <form method="POST" action="<?= $basePath ?>/teaching-shifts/${shift.id}/status" style="flex: 1; margin: 0;">
                            <input type="hidden" name="status" value="approved">
                            <button type="submit" class="btn btn-sm btn-success w-100" onclick="return confirm('Duyệt ca này?')">
                                <i class="fas fa-check-circle me-1"></i>Duyệt
                            </button>
                        </form>
                    </div>
                ` : shift.status === 'approved' ? `
                    <div style="display: flex; gap: 0.5rem; margin-top: 0.75rem;">
                        <form method="POST" action="<?= $basePath ?>/teaching-shifts/${shift.id}/delete" style="margin: 0;">
                            <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Xóa ca này?')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                        <form method="POST" action="<?= $basePath ?>/teaching-shifts/${shift.id}/status" style="flex: 1; margin: 0;">
                            <input type="hidden" name="status" value="pending">
                            <button type="submit" class="btn btn-sm btn-warning w-100" onclick="return confirm('Chuyển về chờ duyệt?')">
                                <i class="fas fa-undo me-1"></i>Chờ duyệt
                            </button>
                        </form>
                    </div>
                ` : `
                    <div style="display: flex; gap: 0.5rem; margin-top: 0.75rem;">
                        <form method="POST" action="<?= $basePath ?>/teaching-shifts/${shift.id}/delete" style="flex: 1; margin: 0;">
                            <button type="submit" class="btn btn-sm btn-outline-danger w-100" onclick="return confirm('Xóa ca này?')">
                                <i class="fas fa-trash me-1"></i>Xóa
                            </button>
                        </form>
                    </div>
                `}
            </div>
        `;
    });
    shiftsHtml += '</div>';
    
    modalBody.innerHTML = `
        <div style="background: linear-gradient(135deg, #e0f2fe 0%, #dbeafe 100%); padding: 1rem; border-radius: 8px; margin-bottom: 1rem;">
            <div style="font-size: 1.1rem; font-weight: 600; color: #1e293b; margin-bottom: 0.25rem;">
                <i class="fas fa-calendar-day me-2"></i>${group.time}
            </div>
            <div style="font-size: 0.9rem; color: #64748b;">
                <i class="fas fa-calendar me-1"></i>${new Date(dateStr).toLocaleDateString('vi-VN')} • ${group.shifts.length} nhân viên
            </div>
        </div>
        ${shiftsHtml}
    `;
    
    modalFooter.innerHTML = `
        <button type="button" class="btn btn-secondary w-100" onclick="closeShiftModal()">
            <i class="fas fa-times me-1"></i>Đóng
        </button>
    `;
    
    document.getElementById('modalOverlay').classList.add('show');
    document.getElementById('shiftModal').classList.add('show');
    document.body.style.overflow = 'hidden';
}

function showShiftDetail(shift) {
    const staff = staffData.find(s => s.id == shift.staff_id);
    const start = shift.custom_start || shift.preset_start;
    const end = shift.custom_end || shift.preset_end;
    const timeDisplay = `${start ? start.substring(0, 5) : '--'} - ${end ? end.substring(0, 5) : '--'}`;
    
    const statusLabels = {
        'pending': 'Chờ duyệt',
        'approved': 'Đã duyệt',
        'rejected': 'Từ chối',
        'cancelled': 'Đã hủy'
    };
    
    const statusColors = {
        'pending': 'warning',
        'approved': 'success',
        'rejected': 'danger',
        'cancelled': 'secondary'
    };
    
    const modalBody = document.getElementById('modalBody');
    modalBody.innerHTML = `
        <div class="info-row">
            <div class="info-label">Nhân viên:</div>
            <div class="info-value"><strong>${staff ? staff.full_name : 'N/A'}</strong></div>
        </div>
        <div class="info-row">
            <div class="info-label">Ngày:</div>
            <div class="info-value">${new Date(shift.shift_date).toLocaleDateString('vi-VN')}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Khung giờ:</div>
            <div class="info-value"><strong>${timeDisplay}</strong></div>
        </div>
        <div class="info-row">
            <div class="info-label">Số giờ:</div>
            <div class="info-value">${shift.hours} giờ</div>
        </div>
        <div class="info-row">
            <div class="info-label">Trạng thái:</div>
            <div class="info-value">
                <span class="badge bg-${statusColors[shift.status]}">${statusLabels[shift.status]}</span>
            </div>
        </div>
        ${shift.notes ? `
        <div class="info-row">
            <div class="info-label">Ghi chú:</div>
            <div class="info-value">${shift.notes}</div>
        </div>
        ` : ''}
        ${shift.approved_by ? `
        <div class="info-row">
            <div class="info-label">Người duyệt:</div>
            <div class="info-value">${shift.approver_name || 'N/A'}</div>
        </div>
        ` : ''}
    `;
    
    const modalFooter = document.getElementById('modalFooter');
    
    // Pending status: Show approve/reject/delete buttons
    if (shift.status === 'pending') {
        modalFooter.innerHTML = `
            <button type="button" class="btn btn-secondary" onclick="closeShiftModal()">
                <i class="fas fa-times me-1"></i>Đóng
            </button>
            <form method="POST" action="<?= $basePath ?>/teaching-shifts/${shift.id}/delete" class="flex-fill" style="margin: 0;">
                <button type="submit" class="btn btn-outline-danger w-100" onclick="return confirm('Xóa ca này vĩnh viễn?')">
                    <i class="fas fa-trash me-1"></i>Xóa
                </button>
            </form>
            <form method="POST" action="<?= $basePath ?>/teaching-shifts/${shift.id}/status" class="flex-fill" style="margin: 0;">
                <input type="hidden" name="status" value="rejected">
                <button type="submit" class="btn btn-danger w-100" onclick="return confirm('Từ chối ca này?')">
                    <i class="fas fa-times-circle me-1"></i>Từ chối
                </button>
            </form>
            <form method="POST" action="<?= $basePath ?>/teaching-shifts/${shift.id}/status" class="flex-fill" style="margin: 0;">
                <input type="hidden" name="status" value="approved">
                <button type="submit" class="btn btn-success w-100" onclick="return confirm('Duyệt ca này?')">
                    <i class="fas fa-check-circle me-1"></i>Duyệt
                </button>
            </form>
        `;
    }
    // Approved status: Show revert to pending and delete buttons
    else if (shift.status === 'approved') {
        modalFooter.innerHTML = `
            <button type="button" class="btn btn-secondary" onclick="closeShiftModal()">
                <i class="fas fa-times me-1"></i>Đóng
            </button>
            <form method="POST" action="<?= $basePath ?>/teaching-shifts/${shift.id}/delete" class="flex-fill" style="margin: 0;">
                <button type="submit" class="btn btn-outline-danger w-100" onclick="return confirm('Xóa ca này vĩnh viễn?')">
                    <i class="fas fa-trash me-1"></i>Xóa
                </button>
            </form>
            <form method="POST" action="<?= $basePath ?>/teaching-shifts/${shift.id}/status" class="flex-fill" style="margin: 0;">
                <input type="hidden" name="status" value="pending">
                <button type="submit" class="btn btn-warning w-100" onclick="return confirm('Chuyển về chờ duyệt?')">
                    <i class="fas fa-undo me-1"></i>Chờ duyệt
                </button>
            </form>
        `;
    }
    // Other statuses: Show delete button only
    else {
        modalFooter.innerHTML = `
            <button type="button" class="btn btn-secondary flex-fill" onclick="closeShiftModal()">
                <i class="fas fa-times me-1"></i>Đóng
            </button>
            <form method="POST" action="<?= $basePath ?>/teaching-shifts/${shift.id}/delete" class="flex-fill" style="margin: 0;">
                <button type="submit" class="btn btn-outline-danger w-100" onclick="return confirm('Xóa ca này vĩnh viễn?')">
                    <i class="fas fa-trash me-1"></i>Xóa
                </button>
            </form>
        `;
    }
    
    document.getElementById('modalOverlay').classList.add('show');
    document.getElementById('shiftModal').classList.add('show');
    document.body.style.overflow = 'hidden';
}

function closeShiftModal() {
    document.getElementById('modalOverlay').classList.remove('show');
    document.getElementById('shiftModal').classList.remove('show');
    document.body.style.overflow = '';
}

function previousMonth() {
    currentDate.setMonth(currentDate.getMonth() - 1);
    renderCalendar();
}

function nextMonth() {
    currentDate.setMonth(currentDate.getMonth() + 1);
    renderCalendar();
}

function goToday() {
    currentDate = new Date();
    renderCalendar();
}

// Quick approve multiple shifts
function quickApproveShifts(shifts) {
    if (shifts.length === 0) return;
    
    const confirmMsg = shifts.length === 1 
        ? 'Duyệt ca này?' 
        : `Duyệt ${shifts.length} ca đang chờ?`;
    
    if (!confirm(confirmMsg)) return;
    
    const shiftIds = shifts.map(s => s.id);
    
    // Show loading
    const loadingDiv = document.createElement('div');
    loadingDiv.style.cssText = 'position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 20px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); z-index: 10000;';
    loadingDiv.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Đang xử lý...';
    document.body.appendChild(loadingDiv);
    
    fetch('<?= $basePath ?>/teaching-shifts/quick-approve', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ shift_ids: shiftIds })
    })
    .then(response => response.json())
    .then(data => {
        document.body.removeChild(loadingDiv);
        if (data.success) {
            alert(`Đã duyệt ${data.approved_count} ca thành công!`);
            location.reload();
        } else {
            alert('Có lỗi: ' + (data.message || 'Không thể duyệt ca'));
        }
    })
    .catch(error => {
        document.body.removeChild(loadingDiv);
        alert('Có lỗi xảy ra: ' + error.message);
    });
}

// Quick reject multiple shifts
function quickRejectShifts(shifts) {
    if (shifts.length === 0) return;
    
    const confirmMsg = shifts.length === 1 
        ? 'Từ chối ca này?' 
        : `Từ chối ${shifts.length} ca đang chờ?`;
    
    if (!confirm(confirmMsg)) return;
    
    const shiftIds = shifts.map(s => s.id);
    
    // Show loading
    const loadingDiv = document.createElement('div');
    loadingDiv.style.cssText = 'position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 20px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); z-index: 10000;';
    loadingDiv.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Đang xử lý...';
    document.body.appendChild(loadingDiv);
    
    fetch('<?= $basePath ?>/teaching-shifts/quick-reject', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ shift_ids: shiftIds })
    })
    .then(response => response.json())
    .then(data => {
        document.body.removeChild(loadingDiv);
        if (data.success) {
            alert(`Đã từ chối ${data.rejected_count} ca!`);
            location.reload();
        } else {
            alert('Có lỗi: ' + (data.message || 'Không thể từ chối ca'));
        }
    })
    .catch(error => {
        document.body.removeChild(loadingDiv);
        alert('Có lỗi xảy ra: ' + error.message);
    });
}

// Quick approve all pending shifts in current month
function quickApproveCurrentMonth() {
    if (selectedStaffId === 'all') {
        alert('Vui lòng chọn một nhân viên cụ thể');
        return;
    }
    
    const staff = staffData.find(s => s.id == selectedStaffId);
    const year = currentDate.getFullYear();
    const month = currentDate.getMonth();
    const monthName = currentDate.toLocaleDateString('vi-VN', { month: 'long', year: 'numeric' });
    
    const firstDay = formatDateForInput(new Date(year, month, 1));
    const lastDay = formatDateForInput(new Date(year, month + 1, 0));
    
    const pendingShifts = shiftsData.filter(s => 
        s.staff_id == selectedStaffId && 
        s.status === 'pending' &&
        s.shift_date >= firstDay &&
        s.shift_date <= lastDay
    );
    
    if (pendingShifts.length === 0) {
        alert('Không có ca nào cần duyệt trong tháng này');
        return;
    }
    
    const confirmMsg = `Duyệt tất cả ${pendingShifts.length} ca đang chờ của ${staff.full_name} trong ${monthName}?`;
    if (!confirm(confirmMsg)) return;
    
    const shiftIds = pendingShifts.map(s => s.id);
    
    const loadingDiv = document.createElement('div');
    loadingDiv.style.cssText = 'position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 20px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); z-index: 10000;';
    loadingDiv.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Đang duyệt...';
    document.body.appendChild(loadingDiv);
    
    fetch('<?= $basePath ?>/teaching-shifts/quick-approve', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ shift_ids: shiftIds })
    })
    .then(response => response.json())
    .then(data => {
        document.body.removeChild(loadingDiv);
        if (data.success) {
            alert(`Đã duyệt thành công ${data.approved_count} ca!`);
            location.reload();
        } else {
            alert('Có lỗi: ' + (data.message || 'Không thể duyệt ca'));
        }
    })
    .catch(error => {
        document.body.removeChild(loadingDiv);
        alert('Có lỗi xảy ra: ' + error.message);
    });
}

// Bulk Approve Modal Functions
function openBulkApproveModal() {
    if (selectedStaffId === 'all') {
        alert('Vui lòng chọn một nhân viên cụ thể');
        return;
    }
    
    // Get staff info
    const staff = staffData.find(s => s.id == selectedStaffId);
    document.getElementById('bulkStaffName').textContent = staff ? staff.full_name : 'N/A';
    
    // Set default date range (current month)
    const year = currentDate.getFullYear();
    const month = currentDate.getMonth();
    const firstDay = new Date(year, month, 1);
    const lastDay = new Date(year, month + 1, 0);
    
    document.getElementById('bulkDateFrom').value = formatDateForInput(firstDay);
    document.getElementById('bulkDateTo').value = formatDateForInput(lastDay);
    
    // Show modal
    document.getElementById('bulkApproveOverlay').style.display = 'block';
    document.getElementById('bulkApproveModal').style.display = 'block';
    
    // Update preview
    updateBulkPreview();
    
    // Add event listeners for preview update
    document.getElementById('bulkDateFrom').addEventListener('change', updateBulkPreview);
    document.getElementById('bulkDateTo').addEventListener('change', updateBulkPreview);
    document.getElementById('bulkIncludePending').addEventListener('change', updateBulkPreview);
}

function closeBulkApproveModal() {
    document.getElementById('bulkApproveOverlay').style.display = 'none';
    document.getElementById('bulkApproveModal').style.display = 'none';
}

function updateBulkPreview() {
    const dateFrom = document.getElementById('bulkDateFrom').value;
    const dateTo = document.getElementById('bulkDateTo').value;
    const includePending = document.getElementById('bulkIncludePending').checked;
    
    const shifts = getBulkShifts();
    const previewEl = document.getElementById('bulkPreviewCount');
    const executeBtn = document.getElementById('bulkApproveExecuteBtn');
    
    if (shifts.length === 0) {
        previewEl.innerHTML = '<strong>Không có ca nào</strong> phù hợp với điều kiện';
        executeBtn.disabled = true;
    } else {
        previewEl.innerHTML = `Sẽ duyệt <strong class="text-success">${shifts.length} ca</strong> từ ${formatDateDisplay(dateFrom)} đến ${formatDateDisplay(dateTo)}`;
        executeBtn.disabled = false;
    }
}

function getBulkShifts() {
    const dateFrom = document.getElementById('bulkDateFrom').value;
    const dateTo = document.getElementById('bulkDateTo').value;
    const includePending = document.getElementById('bulkIncludePending').checked;
    
    return shiftsData.filter(shift => {
        // Filter by staff
        if (shift.staff_id != selectedStaffId) return false;
        
        // Filter by status
        if (includePending && shift.status !== 'pending') return false;
        
        // Filter by date range
        if (dateFrom && shift.shift_date < dateFrom) return false;
        if (dateTo && shift.shift_date > dateTo) return false;
        
        return true;
    });
}

function executeBulkApprove() {
    const shifts = getBulkShifts();
    
    if (shifts.length === 0) {
        alert('Không có ca nào để duyệt');
        return;
    }
    
    const staff = staffData.find(s => s.id == selectedStaffId);
    const confirmMsg = `Bạn có chắc muốn duyệt ${shifts.length} ca của ${staff.full_name}?`;
    
    if (!confirm(confirmMsg)) return;
    
    closeBulkApproveModal();
    
    // Call API
    const shiftIds = shifts.map(s => s.id);
    
    const loadingDiv = document.createElement('div');
    loadingDiv.style.cssText = 'position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 20px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); z-index: 10000;';
    loadingDiv.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Đang duyệt...';
    document.body.appendChild(loadingDiv);
    
    fetch('<?= $basePath ?>/teaching-shifts/quick-approve', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ shift_ids: shiftIds })
    })
    .then(response => response.json())
    .then(data => {
        document.body.removeChild(loadingDiv);
        if (data.success) {
            alert(`Đã duyệt thành công ${data.approved_count} ca!`);
            location.reload();
        } else {
            alert('Có lỗi: ' + (data.message || 'Không thể duyệt ca'));
        }
    })
    .catch(error => {
        document.body.removeChild(loadingDiv);
        alert('Có lỗi xảy ra: ' + error.message);
    });
}

function formatDateForInput(date) {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
}

function formatDateDisplay(dateStr) {
    if (!dateStr) return '';
    const date = new Date(dateStr);
    return date.toLocaleDateString('vi-VN');
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', initCalendar);
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/modern.php';
?>
