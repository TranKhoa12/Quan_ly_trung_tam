<?php
$pageTitle = 'Lịch dạy';

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

/* Quick Actions Bar */
.quick-actions-bar {
    display: flex;
    gap: 0.75rem;
    margin-bottom: 1.5rem;
    flex-wrap: wrap;
}

.quick-action-card {
    flex: 1;
    min-width: 200px;
    background: white;
    border-radius: 12px;
    padding: 1.25rem;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    cursor: pointer;
    transition: all 0.3s ease;
    border: 2px solid transparent;
}

.quick-action-card:hover {
    border-color: #4361ee;
    transform: translateY(-2px);
    box-shadow: 0 4px 16px rgba(67, 97, 238, 0.2);
}

.quick-action-card i {
    font-size: 1.5rem;
    margin-bottom: 0.5rem;
    color: #4361ee;
}

/* Legend */
.calendar-legend {
    display: flex;
    gap: 2rem;
    padding: 1rem;
    background: #f8f9fa;
    border-radius: 8px;
    margin-bottom: 1.5rem;
    flex-wrap: wrap;
    align-items: center;
}

.legend-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.9rem;
}

.legend-badge {
    width: 20px;
    height: 20px;
    border-radius: 4px;
}

.legend-badge.approved { background: #10b981; }
.legend-badge.pending { background: #f59e0b; }
.legend-badge.rejected { background: #ef4444; }
.legend-badge.available { 
    background: white;
    border: 2px dashed #6366f1;
}

/* Calendar Header */
.calendar-header {
    background: linear-gradient(135deg, #4361ee 0%, #4cc9f0 100%);
    color: white;
    padding: 1.5rem;
}

.calendar-controls {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 1rem;
}

.calendar-nav-group {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.calendar-month-title {
    font-size: 1.5rem;
    font-weight: 700;
    margin: 0;
    min-width: 200px;
}

.nav-btn {
    background: rgba(255, 255, 255, 0.2);
    border: none;
    color: white;
    width: 36px;
    height: 36px;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    justify-content: center;
}

.nav-btn:hover {
    background: rgba(255, 255, 255, 0.3);
}

.view-switcher {
    display: flex;
    gap: 0.5rem;
    background: rgba(255, 255, 255, 0.1);
    padding: 0.25rem;
    border-radius: 8px;
}

.view-btn {
    background: transparent;
    border: none;
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 6px;
    cursor: pointer;
    font-size: 0.9rem;
    transition: all 0.2s ease;
    white-space: nowrap;
}

.view-btn.active {
    background: white;
    color: #4361ee;
    font-weight: 600;
}

/* Calendar Grid */
.calendar-grid-wrapper {
    height: calc(100vh - 380px);
    min-height: 600px;
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
    padding: 0.75rem;
    text-align: center;
    font-weight: 600;
    font-size: 0.85rem;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    border-bottom: 2px solid #e0e0e0;
}

.calendar-days-grid {
    display: contents;
}

.calendar-day-cell {
    background: white;
    padding: 0.5rem;
    border: 1px solid #e0e0e0;
    position: relative;
    transition: all 0.15s ease;
    overflow: hidden;
    display: flex;
    flex-direction: column;
}

.calendar-day-cell:not(.other-month) {
    cursor: pointer;
}

.calendar-day-cell:not(.other-month):hover {
    background: #f0f9ff;
    z-index: 1;
}

.calendar-day-cell:not(.other-month):hover .day-add-btn {
    opacity: 1;
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
    width: 28px;
    height: 28px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    font-weight: 600;
}

.day-number {
    font-size: 0.75rem;
    font-weight: 500;
    color: #3c4043;
    margin-bottom: 0.25rem;
    display: block;
    flex-shrink: 0;
}

.shifts-container {
    display: flex;
    flex-direction: column;
    gap: 1px;
    flex: 1;
    overflow-y: auto;
    margin-top: 0.25rem;
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
    border-left: 3px solid #4361ee;
    border-radius: 4px;
    padding: 4px 8px;
    font-size: 0.75rem;
    cursor: pointer;
    transition: all 0.15s ease;
    flex-shrink: 0;
    line-height: 1.3;
    margin-bottom: 2px;
}

.shift-group:hover {
    background: #f0f9ff;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.15);
    transform: translateX(2px);
}

.shift-group-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 4px;
}

.shift-group-time {
    font-weight: 600;
    color: #1e293b;
    font-size: 0.75rem;
}

.shift-group-hours {
    background: #e0e7ff;
    color: #4338ca;
    padding: 1px 6px;
    border-radius: 10px;
    font-size: 0.65rem;
    font-weight: 600;
}

.shift-group.pending {
    border-left-color: #f59e0b;
}

.shift-group.pending .shift-group-hours {
    background: #fef3c7;
    color: #d97706;
}

.shift-group.approved {
    border-left-color: #0d9488;
}

.shift-group.approved .shift-group-hours {
    background: #ccfbf1;
    color: #0d9488;
}

.shift-group.rejected {
    border-left-color: #dc2626;
}

.shift-group.rejected .shift-group-hours {
    background: #fee2e2;
    color: #dc2626;
}

.shift-group.cancelled {
    border-left-color: #6b7280;
}

.shift-group.cancelled .shift-group-hours {
    background: #e5e7eb;
    color: #6b7280;
}

.shift-time {
    font-weight: 500;
}

.day-add-btn {
    position: absolute;
    bottom: 4px;
    right: 4px;
    width: 24px;
    height: 24px;
    background: #1a73e8;
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: all 0.15s ease;
    font-size: 0.9rem;
    cursor: pointer;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
    z-index: 10;
}

.day-add-btn:hover {
    background: #1557b0;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.4);
}

/* List View */
.shifts-list {
    padding: 1.5rem;
}

.shifts-list-group {
    margin-bottom: 2rem;
}

.shifts-list-date {
    font-size: 1.1rem;
    font-weight: 700;
    color: #212529;
    margin-bottom: 1rem;
    padding-bottom: 0.5rem;
    border-bottom: 2px solid #e9ecef;
}

.shift-list-item {
    background: white;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    padding: 1rem;
    margin-bottom: 0.75rem;
    transition: all 0.2s ease;
    cursor: pointer;
}

.shift-list-item:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    transform: translateY(-2px);
}

.shift-list-header {
    display: flex;
    justify-content: between;
    align-items: center;
    margin-bottom: 0.5rem;
}

.shift-list-time {
    font-size: 1.1rem;
    font-weight: 600;
    color: #212529;
}

.shift-list-status {
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 500;
}

.shift-list-status.approved {
    background: #d1fae5;
    color: #065f46;
}

.shift-list-status.pending {
    background: #fef3c7;
    color: #92400e;
}

.shift-list-status.rejected {
    background: #fee2e2;
    color: #991b1b;
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

.shift-modal {
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

.shift-modal.show {
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

.modal-subtitle {
    margin-top: 0.5rem;
    opacity: 0.9;
    font-size: 0.9rem;
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

.shift-option {
    background: #f8f9fa;
    border: 2px solid #e9ecef;
    border-radius: 12px;
    padding: 1rem;
    margin-bottom: 0.75rem;
    cursor: pointer;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    gap: 1rem;
}

.shift-option:hover {
    border-color: #4361ee;
    background: #eff6ff;
}

.shift-option.selected {
    border-color: #4361ee;
    background: #eff6ff;
    box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
}

.shift-option-icon {
    width: 48px;
    height: 48px;
    border-radius: 10px;
    background: linear-gradient(135deg, #4361ee 0%, #4cc9f0 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.25rem;
    flex-shrink: 0;
}

.shift-option-content {
    flex: 1;
}

.shift-option-name {
    font-weight: 600;
    font-size: 1rem;
    margin-bottom: 0.25rem;
    color: #212529;
}

.shift-option-time {
    color: #6c757d;
    font-size: 0.875rem;
}

.modal-footer {
    padding: 1rem 1.5rem;
    background: #f8f9fa;
    border-top: 1px solid #e9ecef;
    display: flex;
    gap: 0.75rem;
}

/* Day Checkbox */
.day-checkbox {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0.75rem 1rem;
    border: 2px solid #e9ecef;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s ease;
    user-select: none;
    min-width: 60px;
}

.day-checkbox input[type="checkbox"] {
    display: none;
}

.day-checkbox span {
    font-weight: 600;
    color: #6c757d;
}

.day-checkbox:hover {
    border-color: #4361ee;
    background: #f8f9ff;
}

.day-checkbox input[type="checkbox"]:checked + span {
    color: white;
}

.day-checkbox:has(input[type="checkbox"]:checked) {
    background: linear-gradient(135deg, #4361ee 0%, #4cc9f0 100%);
    border-color: #4361ee;
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

/* Responsive */
@media (max-width: 1024px) {
    .calendar-grid-wrapper {
        height: calc(100vh - 400px);
        min-height: 500px;
    }
    
    .shift-badge {
        font-size: 0.65rem;
        padding: 1px 4px;
    }
}

@media (max-width: 768px) {
    .calendar-controls {
        flex-direction: column;
        align-items: stretch;
    }
    
    .calendar-nav-group {
        justify-content: space-between;
    }
    
    .view-switcher {
        width: 100%;
        justify-content: space-between;
    }
    
    .quick-actions-bar {
        flex-direction: column;
    }
    
    .quick-action-card {
        min-width: 100%;
    }
    
    .calendar-grid-wrapper {
        height: calc(100vh - 450px);
        min-height: 400px;
    }
    
    .day-number {
        font-size: 0.7rem;
    }
    
    .shift-badge {
        font-size: 0.6rem;
        padding: 1px 3px;
    }
    
    .day-add-btn {
        width: 20px;
        height: 20px;
        font-size: 0.75rem;
    }
}
</style>

<!-- Quick Actions -->
<div class="quick-actions-bar">
    <div class="quick-action-card" onclick="openBulkRegistration()">
        <i class="fas fa-calendar-check"></i>
        <div style="font-weight: 600; margin-top: 0.5rem;">Đăng ký hàng loạt</div>
        <div style="font-size: 0.85rem; color: #6c757d;">Đăng ký nhiều ngày</div>
    </div>
    <div class="quick-action-card" onclick="showTodayShifts()">
        <i class="fas fa-calendar-day"></i>
        <div style="font-weight: 600; margin-top: 0.5rem;">Ca hôm nay</div>
        <div style="font-size: 0.85rem; color: #6c757d;">Xem ca dạy hôm nay</div>
    </div>
    <div class="quick-action-card" onclick="window.location.href='<?= $basePath ?>/teaching-shifts/transfers/my'">
        <i class="fas fa-exchange-alt"></i>
        <div style="font-weight: 600; margin-top: 0.5rem;">Yêu cầu chuyển ca</div>
        <div style="font-size: 0.85rem; color: #6c757d;">Xem yêu cầu của tôi</div>
    </div>
    <div class="quick-action-card" onclick="switchView('list')">
        <i class="fas fa-list-ul"></i>
        <div style="font-weight: 600; margin-top: 0.5rem;">Danh sách ca</div>
        <div style="font-size: 0.85rem; color: #6c757d;">Xem tất cả ca</div>
    </div>
    <div class="quick-action-card" onclick="exportSchedule()">
        <i class="fas fa-file-download"></i>
        <div style="font-weight: 600; margin-top: 0.5rem;">Xuất lịch</div>
        <div style="font-size: 0.85rem; color: #6c757d;">Tải về Excel</div>
    </div>
</div>

<!-- Legend -->
<div class="calendar-legend">
    <strong style="color: #212529;">Trạng thái:</strong>
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
    <div class="legend-item">
        <div class="legend-badge available"></div>
        <span>Có thể đăng ký</span>
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
            <div class="view-switcher">
                <button class="view-btn active" onclick="switchView('month', event)">
                    <i class="fas fa-calendar-alt"></i> Tháng
                </button>
                <button class="view-btn" onclick="switchView('list', event)">
                    <i class="fas fa-list"></i> Danh sách
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

    <!-- List View (hidden by default) -->
    <div id="listView" class="shifts-list" style="display: none;">
        <div id="listViewContent"></div>
    </div>
</div>

<!-- Shift Detail Modal -->
<div class="modal-overlay" id="detailModalOverlay" onclick="closeDetailModal()"></div>
<div class="shift-modal" id="detailModal">
    <div class="modal-header">
        <div>
            <h3 class="modal-title">
                <i class="fas fa-info-circle me-2"></i>
                Chi tiết ca dạy
            </h3>
        </div>
        <button class="modal-close" onclick="closeDetailModal()">
            <i class="fas fa-times"></i>
        </button>
    </div>
    <div class="modal-body" id="detailModalBody">
        <!-- Content will be filled by JavaScript -->
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary w-100" onclick="closeDetailModal()">
            <i class="fas fa-times me-1"></i>Đóng
        </button>
    </div>
</div>

<!-- Bulk Registration Modal -->
<div class="modal-overlay" id="bulkModalOverlay" onclick="closeBulkModal()"></div>
<div class="shift-modal" id="bulkModal" style="max-width: 700px;">
    <div class="modal-header">
        <div>
            <h3 class="modal-title">
                <i class="fas fa-calendar-check me-2"></i>
                Đăng ký ca dạy hàng loạt
            </h3>
            <div class="modal-subtitle">Chọn các ngày và ca dạy muốn đăng ký</div>
        </div>
        <button class="modal-close" onclick="closeBulkModal()">
            <i class="fas fa-times"></i>
        </button>
    </div>
    <div class="modal-body">
        <form id="bulkRegistrationForm">
            <!-- Date Range Selection -->
            <div class="mb-4">
                <h5 class="mb-3">
                    <i class="fas fa-calendar-alt me-2"></i>
                    Chọn khoảng thời gian
                </h5>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Từ ngày</label>
                        <input type="date" class="form-control form-control-lg" id="bulkDateFrom" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Đến ngày</label>
                        <input type="date" class="form-control form-control-lg" id="bulkDateTo" required>
                    </div>
                </div>
            </div>

            <!-- Days of Week Selection -->
            <div class="mb-4">
                <h5 class="mb-3">
                    <i class="fas fa-calendar-week me-2"></i>
                    Chọn các ngày trong tuần
                </h5>
                <div style="display: flex; flex-wrap: wrap; gap: 0.5rem;">
                    <label class="day-checkbox">
                        <input type="checkbox" name="weekdays[]" value="1" checked>
                        <span>T2</span>
                    </label>
                    <label class="day-checkbox">
                        <input type="checkbox" name="weekdays[]" value="2" checked>
                        <span>T3</span>
                    </label>
                    <label class="day-checkbox">
                        <input type="checkbox" name="weekdays[]" value="3" checked>
                        <span>T4</span>
                    </label>
                    <label class="day-checkbox">
                        <input type="checkbox" name="weekdays[]" value="4" checked>
                        <span>T5</span>
                    </label>
                    <label class="day-checkbox">
                        <input type="checkbox" name="weekdays[]" value="5" checked>
                        <span>T6</span>
                    </label>
                    <label class="day-checkbox">
                        <input type="checkbox" name="weekdays[]" value="6">
                        <span>T7</span>
                    </label>
                </div>
            </div>

            <!-- Shift Selection -->
            <div class="mb-4">
                <h5 class="mb-3">
                    <i class="fas fa-clock me-2"></i>
                    Chọn ca dạy (có thể chọn nhiều)
                </h5>
                <div id="bulkShiftOptions">
                    <?php foreach ($activeShifts as $shift): ?>
                        <label class="shift-option" style="cursor: pointer; margin-bottom: 0.5rem;">
                            <input type="checkbox" name="shift_ids[]" value="<?= $shift['id'] ?>" style="display: none;" onchange="toggleBulkShift(this)">
                            <div style="display: flex; align-items: center; gap: 1rem;">
                                <div class="shift-option-icon">
                                    <i class="fas fa-chalkboard-teacher"></i>
                                </div>
                                <div class="shift-option-content" style="flex: 1;">
                                    <div class="shift-option-name"><?= htmlspecialchars($shift['name']) ?></div>
                                    <div class="shift-option-time">
                                        <?= substr($shift['start_time'], 0, 5) ?> - <?= substr($shift['end_time'], 0, 5) ?>
                                        <span style="margin-left: 0.75rem;">
                                            <i class="fas fa-coins"></i>
                                            <?= number_format($shift['hourly_rate']) ?>đ/giờ
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </label>
                    <?php endforeach; ?>
                    
                    <label class="shift-option" style="cursor: pointer; margin-bottom: 0.5rem;">
                        <input type="checkbox" name="shift_ids[]" value="custom" style="display: none;" onchange="toggleBulkShift(this); toggleBulkCustomTime(this)">
                        <div style="display: flex; align-items: center; gap: 1rem;">
                            <div class="shift-option-icon" style="background: linear-gradient(135deg, #8b5cf6 0%, #ec4899 100%);">
                                <i class="fas fa-edit"></i>
                            </div>
                            <div class="shift-option-content" style="flex: 1;">
                                <div class="shift-option-name">Ca tự chọn</div>
                                <div class="shift-option-time">Tự nhập giờ bắt đầu và kết thúc</div>
                            </div>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Custom Time Fields for Bulk -->
            <div id="bulkCustomTimeFields" style="display: none;">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Ca tự chọn:</strong> Giờ này sẽ áp dụng cho tất cả các ngày đã chọn
                </div>
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label">
                            <i class="fas fa-clock me-1"></i>Giờ bắt đầu
                        </label>
                        <input type="time" class="form-control form-control-lg" id="bulkCustomStart">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">
                            <i class="fas fa-clock me-1"></i>Giờ kết thúc
                        </label>
                        <input type="time" class="form-control form-control-lg" id="bulkCustomEnd">
                    </div>
                </div>
            </div>

            <!-- Notes -->
            <div class="mb-3">
                <label class="form-label">
                    <i class="fas fa-comment me-1"></i>Ghi chú chung
                </label>
                <textarea class="form-control" id="bulkNotes" rows="2" placeholder="Ghi chú áp dụng cho tất cả ca đăng ký"></textarea>
            </div>

            <!-- Preview Summary -->
            <div id="bulkSummary" class="alert alert-info" style="display: none;">
                <i class="fas fa-info-circle me-2"></i>
                <span id="bulkSummaryText"></span>
            </div>
        </form>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary flex-fill" onclick="closeBulkModal()" id="bulkCancelBtn">
            <i class="fas fa-times me-1"></i>Hủy
        </button>
        <button type="button" class="btn btn-primary flex-fill" onclick="submitBulkRegistration()" id="bulkSubmitBtn">
            <i class="fas fa-paper-plane me-1"></i>
            <span id="bulkSubmitText">Đăng ký</span>
        </button>
    </div>
</div>

<!-- Shift Registration Modal -->
<div class="modal-overlay" id="modalOverlay" onclick="closeShiftModal()"></div>
<div class="shift-modal" id="shiftModal">
    <div class="modal-header">
        <div>
            <h3 class="modal-title">
                <i class="fas fa-calendar-plus me-2"></i>
                Đăng ký ca dạy
            </h3>
            <div class="modal-subtitle" id="selectedDate">Chọn ngày...</div>
        </div>
        <button class="modal-close" onclick="closeShiftModal()">
            <i class="fas fa-times"></i>
        </button>
    </div>
    <div class="modal-body">
        <form id="shiftRegistrationForm" method="POST" action="<?= $basePath ?>/teaching-shifts/register">
            <input type="hidden" name="shift_date" id="shiftDateInput">
            
            <div class="mb-4">
                <h5 class="mb-3">
                    <i class="fas fa-clock me-2"></i>
                    Chọn ca dạy
                </h5>
                <div id="shiftOptions">
                    <?php foreach ($activeShifts as $shift): ?>
                        <div class="shift-option" data-shift-id="<?= $shift['id'] ?>" onclick="selectShift(<?= $shift['id'] ?>, '<?= htmlspecialchars($shift['name']) ?>', event)">
                            <div class="shift-option-icon">
                                <i class="fas fa-chalkboard-teacher"></i>
                            </div>
                            <div class="shift-option-content">
                                <div class="shift-option-name"><?= htmlspecialchars($shift['name']) ?></div>
                                <div class="shift-option-time">
                                    <?= substr($shift['start_time'], 0, 5) ?> - <?= substr($shift['end_time'], 0, 5) ?>
                                    <span style="margin-left: 0.75rem;">
                                        <i class="fas fa-coins"></i>
                                        <?= number_format($shift['hourly_rate']) ?>đ/giờ
                                    </span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    
                    <div class="shift-option" data-shift-id="custom" onclick="selectShift('custom', 'Ca tự chọn', event)">
                        <div class="shift-option-icon" style="background: linear-gradient(135deg, #8b5cf6 0%, #ec4899 100%);">
                            <i class="fas fa-edit"></i>
                        </div>
                        <div class="shift-option-content">
                            <div class="shift-option-name">Ca tự chọn</div>
                            <div class="shift-option-time">Tự nhập giờ bắt đầu và kết thúc</div>
                        </div>
                    </div>
                </div>
                <input type="hidden" name="shift_id" id="selectedShiftId">
            </div>

            <div id="customTimeFields" style="display: none;">
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label">
                            <i class="fas fa-clock me-1"></i>Giờ bắt đầu
                        </label>
                        <input type="time" class="form-control form-control-lg" name="custom_start" id="customStart">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">
                            <i class="fas fa-clock me-1"></i>Giờ kết thúc
                        </label>
                        <input type="time" class="form-control form-control-lg" name="custom_end" id="customEnd">
                    </div>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label">
                    <i class="fas fa-comment me-1"></i>Ghi chú
                </label>
                <textarea class="form-control" name="notes" rows="3" placeholder="Ví dụ: Thay ca cho đồng nghiệp, lớp phát sinh đột xuất..."></textarea>
            </div>

        </form>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary flex-fill" onclick="closeShiftModal()">
            <i class="fas fa-times me-1"></i>Hủy
        </button>
        <button type="submit" form="shiftRegistrationForm" class="btn btn-primary flex-fill">
            <i class="fas fa-paper-plane me-1"></i>Gửi đăng ký
        </button>
    </div>
</div>

<script>
// Sample data - replace with actual data from PHP
const shiftsData = <?= json_encode($registrations ?? []) ?>;
const basePath = '<?= $basePath ?>';
const today = new Date();
let currentDate = new Date();
let currentView = 'month';
let selectedShiftId = null;

console.log('=== SHIFTS DATA ===');
console.log('Total shifts:', shiftsData.length);
// Group by day of week
const byDayOfWeek = {};
shiftsData.forEach(shift => {
    const date = new Date(shift.shift_date);
    const dow = date.getDay();
    if (!byDayOfWeek[dow]) byDayOfWeek[dow] = 0;
    byDayOfWeek[dow]++;
});
console.log('Shifts by day of week:', byDayOfWeek);
console.log('(0=Sun, 1=Mon, 2=Tue, 3=Wed, 4=Thu, 5=Fri, 6=Sat)');

// Initialize calendar
function initCalendar() {
    renderCalendar();
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
    
    // Our calendar starts from Monday (not Sunday)
    // Grid headers: T2(Mon), T3(Tue), T4(Wed), T5(Thu), T6(Fri), T7(Sat)
    // JavaScript getDay(): 0=Sun, 1=Mon, 2=Tue, 3=Wed, 4=Thu, 5=Fri, 6=Sat
    
    // Calculate how many days from previous month to show
    // If month starts on Sunday (0), show 6 days (Mon-Sat from prev month)
    // If month starts on Monday (1), show 0 days
    // If month starts on Tuesday (2), show 1 day (Monday from prev month)
    // etc.
    let daysToShowFromPrevMonth = startingDayOfWeek === 0 ? 6 : startingDayOfWeek - 1;
    
    // Add previous month's days
    for (let i = daysToShowFromPrevMonth; i > 0; i--) {
        const day = prevMonthLastDay - i + 1;
        const cell = createDayCell(day, true, null);
        calendarDaysContainer.appendChild(cell);
    }
    
    // Add current month's days (skip Sundays)
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
        
        // Add shifts for this day
        const dayShifts = shiftsData.filter(shift => shift.shift_date === dateStr);
        
        if (dayShifts.length > 0) {
            const container = document.createElement('div');
            container.className = 'shifts-container';
            
            dayShifts.forEach(shift => {
                const start = shift.custom_start || shift.preset_start;
                const end = shift.custom_end || shift.preset_end;
                const timeDisplay = start && end 
                    ? `${start.substring(0, 5)} - ${end.substring(0, 5)}`
                    : (start ? start.substring(0, 5) : '--');
                
                const shiftGroup = document.createElement('div');
                shiftGroup.className = `shift-group ${shift.status}`;
                shiftGroup.innerHTML = `
                    <div class="shift-group-header">
                        <span class="shift-group-time">${timeDisplay}</span>
                        <span class="shift-group-hours">${shift.hours}h</span>
                    </div>
                `;
                shiftGroup.title = `Click để xem chi tiết`;
                shiftGroup.onclick = (e) => {
                    e.stopPropagation();
                    viewShiftDetail(shift);
                };
                container.appendChild(shiftGroup);
            });
            
            cell.appendChild(container);
        }
        
        // Add button for registering new shift
        const addBtn = document.createElement('div');
        addBtn.className = 'day-add-btn';
        addBtn.innerHTML = '<i class="fas fa-plus"></i>';
        addBtn.title = 'Đăng ký ca dạy';
        addBtn.onclick = (e) => {
            e.stopPropagation();
            openShiftModal(dateStr);
        };
        cell.appendChild(addBtn);
        
        // Add click handler to cell
        cell.onclick = () => openShiftModal(dateStr);
    }
    
    return cell;
}

function openShiftModal(dateStr) {
    const date = new Date(dateStr);
    const dateDisplay = date.toLocaleDateString('vi-VN', { 
        weekday: 'long', 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric' 
    });
    
    document.getElementById('selectedDate').textContent = dateDisplay;
    document.getElementById('shiftDateInput').value = dateStr;
    document.getElementById('modalOverlay').classList.add('show');
    document.getElementById('shiftModal').classList.add('show');
    document.body.style.overflow = 'hidden';
}

function closeShiftModal() {
    document.getElementById('modalOverlay').classList.remove('show');
    document.getElementById('shiftModal').classList.remove('show');
    document.body.style.overflow = '';
    
    // Reset form
    document.querySelectorAll('.shift-option').forEach(opt => opt.classList.remove('selected'));
    document.getElementById('selectedShiftId').value = '';
    document.getElementById('customTimeFields').style.display = 'none';
    selectedShiftId = null;
}

function selectShift(shiftId, shiftName, event) {
    selectedShiftId = shiftId;
    
    // Update visual selection
    document.querySelectorAll('.shift-option').forEach(opt => opt.classList.remove('selected'));
    event.currentTarget.classList.add('selected');
    
    // Update form
    document.getElementById('selectedShiftId').value = shiftId === 'custom' ? '' : shiftId;
    
    // Show/hide custom time fields
    const customFields = document.getElementById('customTimeFields');
    customFields.style.display = shiftId === 'custom' ? 'block' : 'none';
}

function previousMonth() {
    currentDate.setMonth(currentDate.getMonth() - 1);
    renderCalendar();
}

function nextMonth() {
    currentDate.setMonth(currentDate.getMonth() + 1);
    renderCalendar();
}

function switchView(view, event) {
    currentView = view;
    
    // Update button states
    if (event) {
        document.querySelectorAll('.view-btn').forEach(btn => btn.classList.remove('active'));
        event.currentTarget.classList.add('active');
    }
    
    // Show/hide views
    document.getElementById('monthView').style.display = view === 'month' ? 'block' : 'none';
    document.getElementById('listView').style.display = view === 'list' ? 'block' : 'none';
    
    if (view === 'list') renderListView();
}

function goToday() {
    currentDate = new Date();
    renderCalendar();
}

function renderListView() {
    const container = document.getElementById('listViewContent');
    
    if (shiftsData.length === 0) {
        container.innerHTML = `
            <div style="text-align: center; padding: 3rem; color: #6c757d;">
                <i class="fas fa-calendar-times" style="font-size: 3rem; margin-bottom: 1rem;"></i>
                <p>Chưa có ca dạy nào được đăng ký.</p>
            </div>
        `;
        return;
    }
    
    // Group by date
    const groupedShifts = {};
    shiftsData.forEach(shift => {
        if (!groupedShifts[shift.shift_date]) {
            groupedShifts[shift.shift_date] = [];
        }
        groupedShifts[shift.shift_date].push(shift);
    });
    
    let html = '';
    Object.keys(groupedShifts).sort().reverse().forEach(date => {
        const dateObj = new Date(date);
        const dateDisplay = dateObj.toLocaleDateString('vi-VN', { 
            weekday: 'long', 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric' 
        });
        
        html += `<div class="shifts-list-group">`;
        html += `<div class="shifts-list-date">${dateDisplay}</div>`;
        
        groupedShifts[date].forEach(shift => {
            const start = shift.custom_start || shift.preset_start;
            const end = shift.custom_end || shift.preset_end;
            const time = start ? `${start.substring(0, 5)} - ${end.substring(0, 5)}` : '--';
            const statusText = {
                pending: 'Chờ duyệt',
                approved: 'Đã duyệt',
                rejected: 'Từ chối',
                cancelled: 'Đã hủy'
            }[shift.status];
            
            html += `
                <div class="shift-list-item">
                    <div class="shift-list-header">
                        <div class="shift-list-time">${time}</div>
                        <span class="shift-list-status ${shift.status}">${statusText}</span>
                    </div>
                    <div style="color: #6c757d; font-size: 0.9rem;">
                        <i class="fas fa-clock me-1"></i>${shift.hours} giờ
                    </div>
                    ${shift.notes ? `<div style="margin-top: 0.5rem; color: #6c757d; font-size: 0.875rem;"><i class="fas fa-sticky-note me-1"></i>${shift.notes}</div>` : ''}
                </div>
            `;
        });
        
        html += `</div>`;
    });
    
    container.innerHTML = html;
}

function viewShiftDetail(shift) {
    const statusLabels = {
        pending: 'Chờ duyệt',
        approved: 'Đã duyệt',
        rejected: 'Từ chối',
        cancelled: 'Đã hủy'
    };
    
    const statusColors = {
        pending: '#f59e0b',
        approved: '#10b981',
        rejected: '#ef4444',
        cancelled: '#6b7280'
    };
    
    const start = shift.custom_start || shift.preset_start;
    const end = shift.custom_end || shift.preset_end;
    const time = start ? `${start.substring(0, 5)} - ${end.substring(0, 5)}` : '--';
    const date = new Date(shift.shift_date);
    const dateDisplay = date.toLocaleDateString('vi-VN', { 
        weekday: 'long', 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric' 
    });
    
    const modalBody = document.getElementById('detailModalBody');
    modalBody.innerHTML = `
        <div style="display: flex; flex-direction: column; gap: 1rem;">
            <div class="detail-row">
                <div style="display: flex; align-items: center; gap: 0.5rem; color: #6b7280; margin-bottom: 0.25rem;">
                    <i class="fas fa-calendar"></i>
                    <strong>Ngày</strong>
                </div>
                <div style="font-size: 1rem;">${dateDisplay}</div>
            </div>
            
            <div class="detail-row">
                <div style="display: flex; align-items: center; gap: 0.5rem; color: #6b7280; margin-bottom: 0.25rem;">
                    <i class="fas fa-clock"></i>
                    <strong>Thời gian</strong>
                </div>
                <div style="font-size: 1rem;">${time} (${shift.hours || '--'} giờ)</div>
            </div>
            
            <div class="detail-row">
                <div style="display: flex; align-items: center; gap: 0.5rem; color: #6b7280; margin-bottom: 0.25rem;">
                    <i class="fas fa-info-circle"></i>
                    <strong>Trạng thái</strong>
                </div>
                <div>
                    <span style="display: inline-block; padding: 0.375rem 0.75rem; border-radius: 6px; background: ${statusColors[shift.status]}; color: white; font-weight: 500;">
                        ${statusLabels[shift.status]}
                    </span>
                </div>
            </div>
            
            ${shift.hourly_rate ? `
                <div class="detail-row">
                    <div style="display: flex; align-items: center; gap: 0.5rem; color: #6b7280; margin-bottom: 0.25rem;">
                        <i class="fas fa-coins"></i>
                        <strong>Lương/giờ</strong>
                    </div>
                    <div style="font-size: 1rem;">${Number(shift.hourly_rate).toLocaleString()}đ</div>
                </div>
            ` : ''}
            
            ${shift.notes ? `
                <div class="detail-row">
                    <div style="display: flex; align-items: center; gap: 0.5rem; color: #6b7280; margin-bottom: 0.25rem;">
                        <i class="fas fa-comment"></i>
                        <strong>Ghi chú</strong>
                    </div>
                    <div style="font-size: 0.95rem; color: #6b7280;">${shift.notes}</div>
                </div>
            ` : ''}
            
            ${shift.approved_by ? `
                <div class="detail-row">
                    <div style="display: flex; align-items: center; gap: 0.5rem; color: #6b7280; margin-bottom: 0.25rem;">
                        <i class="fas fa-user-check"></i>
                        <strong>Người duyệt</strong>
                    </div>
                    <div style="font-size: 0.95rem;">${shift.approver_name || 'N/A'}</div>
                </div>
            ` : ''}
        </div>
        
        ${shift.status === 'approved' ? `
            <div style="margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid #e5e7eb;">
                <a href="${basePath}/teaching-shifts/transfer/${shift.id}" 
                   class="btn btn-warning w-100"
                   style="display: flex; align-items: center; justify-content: center; gap: 0.5rem; padding: 0.75rem; border-radius: 8px; text-decoration: none; font-weight: 500;">
                    <i class="fas fa-exchange-alt"></i>
                    Yêu cầu chuyển ca
                </a>
                <small class="text-muted d-block text-center mt-2">
                    <i class="fas fa-info-circle me-1"></i>
                    Chỉ có thể chuyển ca đã được duyệt
                </small>
            </div>
        ` : ''}
    `;
    
    document.getElementById('detailModalOverlay').classList.add('show');
    document.getElementById('detailModal').classList.add('show');
    document.body.style.overflow = 'hidden';
}

function closeDetailModal() {
    document.getElementById('detailModalOverlay').classList.remove('show');
    document.getElementById('detailModal').classList.remove('show');
    document.body.style.overflow = '';
}

function showTodayShifts() {
    currentDate = new Date();
    renderCalendar();
    const todayStr = today.toISOString().split('T')[0];
    const todayShifts = shiftsData.filter(s => s.shift_date === todayStr);
    
    if (todayShifts.length > 0) {
        window.showToast(`Bạn có ${todayShifts.length} ca dạy hôm nay`, 'info');
    } else {
        window.showToast('Bạn chưa có ca dạy nào hôm nay', 'info');
    }
}

function exportSchedule() {
    window.showToast('Tính năng xuất lịch đang được phát triển', 'info');
}

// Bulk Registration Functions
function openBulkRegistration() {
    // Set default date range (next 7 days)
    const tomorrow = new Date();
    tomorrow.setDate(tomorrow.getDate() + 1);
    const nextWeek = new Date();
    nextWeek.setDate(nextWeek.getDate() + 7);
    
    document.getElementById('bulkDateFrom').value = tomorrow.toISOString().split('T')[0];
    document.getElementById('bulkDateTo').value = nextWeek.toISOString().split('T')[0];
    document.getElementById('bulkDateFrom').min = tomorrow.toISOString().split('T')[0];
    
    document.getElementById('bulkModalOverlay').classList.add('show');
    document.getElementById('bulkModal').classList.add('show');
    document.body.style.overflow = 'hidden';
    
    updateBulkSummary();
}

function closeBulkModal() {
    document.getElementById('bulkModalOverlay').classList.remove('show');
    document.getElementById('bulkModal').classList.remove('show');
    document.body.style.overflow = '';
    
    // Reset form
    document.querySelectorAll('#bulkShiftOptions input[type="checkbox"]').forEach(cb => {
        cb.checked = false;
        cb.parentElement.classList.remove('selected');
    });
    document.getElementById('bulkNotes').value = '';
    document.getElementById('bulkCustomTimeFields').style.display = 'none';
    document.getElementById('bulkCustomStart').value = '';
    document.getElementById('bulkCustomEnd').value = '';
    document.getElementById('bulkSummary').style.display = 'none';
}

function toggleBulkShift(checkbox) {
    if (checkbox.checked) {
        checkbox.parentElement.classList.add('selected');
    } else {
        checkbox.parentElement.classList.remove('selected');
    }
    updateBulkSummary();
}

function toggleBulkCustomTime(checkbox) {
    const customFields = document.getElementById('bulkCustomTimeFields');
    if (checkbox.checked) {
        customFields.style.display = 'block';
    } else {
        customFields.style.display = 'none';
        document.getElementById('bulkCustomStart').value = '';
        document.getElementById('bulkCustomEnd').value = '';
    }
}

function updateBulkSummary() {
    const dateFrom = document.getElementById('bulkDateFrom').value;
    const dateTo = document.getElementById('bulkDateTo').value;
    const selectedDays = Array.from(document.querySelectorAll('input[name="weekdays[]"]:checked')).map(cb => parseInt(cb.value));
    const selectedShifts = Array.from(document.querySelectorAll('input[name="shift_ids[]"]:checked'));
    
    if (!dateFrom || !dateTo || selectedDays.length === 0 || selectedShifts.length === 0) {
        document.getElementById('bulkSummary').style.display = 'none';
        return;
    }
    
    // Calculate number of days
    const dates = [];
    const start = new Date(dateFrom);
    const end = new Date(dateTo);
    
    for (let d = new Date(start); d <= end; d.setDate(d.getDate() + 1)) {
        const dayOfWeek = d.getDay();
        // Skip Sunday
        if (dayOfWeek === 0) continue;
        
        if (selectedDays.includes(dayOfWeek)) {
            dates.push(new Date(d));
        }
    }
    
    const summary = document.getElementById('bulkSummary');
    const summaryText = document.getElementById('bulkSummaryText');
    
    const totalRegistrations = dates.length * selectedShifts.length;
    
    if (dates.length > 0 && selectedShifts.length > 0) {
        summaryText.innerHTML = `<strong>Sẽ đăng ký ${totalRegistrations} ca dạy</strong> (${dates.length} ngày × ${selectedShifts.length} ca) từ ${start.toLocaleDateString('vi-VN')} đến ${end.toLocaleDateString('vi-VN')}`;
        summary.style.display = 'block';
    } else {
        summaryText.innerHTML = 'Không có ngày nào phù hợp với điều kiện đã chọn';
        summary.style.display = 'block';
    }
}

// Update summary when date or weekdays change
document.addEventListener('DOMContentLoaded', function() {
    const bulkForm = document.getElementById('bulkRegistrationForm');
    if (bulkForm) {
        bulkForm.addEventListener('change', updateBulkSummary);
    }
});

async function submitBulkRegistration() {
    const selectedShifts = Array.from(document.querySelectorAll('input[name="shift_ids[]"]:checked'));
    
    if (selectedShifts.length === 0) {
        window.showToast('Vui lòng chọn ít nhất một ca dạy', 'error');
        return;
    }
    
    // Check if custom shift is selected and validate time
    const hasCustomShift = selectedShifts.some(cb => cb.value === 'custom');
    const bulkCustomStart = document.getElementById('bulkCustomStart').value;
    const bulkCustomEnd = document.getElementById('bulkCustomEnd').value;
    
    if (hasCustomShift) {
        if (!bulkCustomStart || !bulkCustomEnd) {
            window.showToast('Vui lòng nhập giờ bắt đầu và kết thúc cho ca tự chọn', 'error');
            return;
        }
        if (bulkCustomStart >= bulkCustomEnd) {
            window.showToast('Giờ kết thúc phải lớn hơn giờ bắt đầu', 'error');
            return;
        }
    }
    
    const dateFrom = document.getElementById('bulkDateFrom').value;
    const dateTo = document.getElementById('bulkDateTo').value;
    const selectedDays = Array.from(document.querySelectorAll('input[name="weekdays[]"]:checked')).map(cb => parseInt(cb.value));
    const notes = document.getElementById('bulkNotes').value;
    
    if (!dateFrom || !dateTo) {
        window.showToast('Vui lòng chọn khoảng thời gian', 'error');
        return;
    }
    
    if (selectedDays.length === 0) {
        window.showToast('Vui lòng chọn ít nhất một ngày trong tuần', 'error');
        return;
    }
    
    // Calculate dates
    const dates = [];
    const start = new Date(dateFrom);
    const end = new Date(dateTo);
    
    console.log('Selected days (checkbox values):', selectedDays);
    
    for (let d = new Date(start); d <= end; d.setDate(d.getDate() + 1)) {
        const dayOfWeek = d.getDay(); // 0=Sunday, 1=Monday, 2=Tuesday, ..., 6=Saturday
        // Skip Sunday (dayOfWeek = 0) since we don't have checkbox for it
        if (dayOfWeek === 0) continue;
        
        if (selectedDays.includes(dayOfWeek)) {
            const dateStr = d.toISOString().split('T')[0];
            dates.push(dateStr);
            console.log(`Adding date: ${dateStr}, dayOfWeek: ${dayOfWeek}`);
        }
    }
    
    console.log('Total dates to register:', dates.length);
    
    if (dates.length === 0) {
        window.showToast('Không có ngày nào phù hợp', 'error');
        return;
    }
    
    // Show loading state
    const submitBtn = document.getElementById('bulkSubmitBtn');
    const cancelBtn = document.getElementById('bulkCancelBtn');
    const submitText = document.getElementById('bulkSubmitText');
    const originalText = submitText.textContent;
    
    submitBtn.disabled = true;
    cancelBtn.disabled = true;
    submitText.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Đang đăng ký...';
    
    // Submit each date and shift combination
    let successCount = 0;
    let errorCount = 0;
    const totalRegistrations = dates.length * selectedShifts.length;
    let processedCount = 0;
    
    for (const date of dates) {
        for (const shiftCheckbox of selectedShifts) {
            const formData = new FormData();
            
            // Handle custom shift
            if (shiftCheckbox.value === 'custom') {
                formData.append('shift_id', 'custom');
                formData.append('custom_start', bulkCustomStart);
                formData.append('custom_end', bulkCustomEnd);
            } else {
                formData.append('shift_id', shiftCheckbox.value);
            }
            
            formData.append('shift_date', date);
            formData.append('notes', notes);
            
            try {
                const response = await fetch(basePath + '/teaching-shifts/register', {
                    method: 'POST',
                    body: formData
                });
                
                processedCount++;
                submitText.innerHTML = `<i class="fas fa-spinner fa-spin me-1"></i>Đang đăng ký... (${processedCount}/${totalRegistrations})`;
                
                if (response.ok) {
                    successCount++;
                } else {
                    errorCount++;
                    console.error('Registration failed for date:', date, 'shift:', shiftCheckbox.value);
                }
            } catch (error) {
                errorCount++;
                processedCount++;
                console.error('Error registering shift:', error);
            }
        }
    }
    
    // Reset button state
    submitBtn.disabled = false;
    cancelBtn.disabled = false;
    submitText.textContent = originalText;
    
    closeBulkModal();
    
    if (successCount > 0) {
        window.showToast(`Đã đăng ký thành công ${successCount}/${totalRegistrations} ca dạy${errorCount > 0 ? ` (${errorCount} ca thất bại)` : ''}`, successCount === totalRegistrations ? 'success' : 'warning');
        setTimeout(() => window.location.reload(), 1500);
    } else {
        window.showToast('Đăng ký thất bại. Vui lòng thử lại', 'error');
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', initCalendar);
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/modern.php';
?>
