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
    'Đăng ký ca dạy cho nhân viên', 
    'Admin tạo lịch dạy và phân công ca cho nhân viên',
    '<a href="' . $basePath . '/teaching-shifts/admin" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-2"></i>Quay lại
    </a>'
) ?>

<style>
.custom-shift-fields {
    display: none;
    animation: slideDown 0.3s ease;
}

.custom-shift-fields.show {
    display: block;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.shift-preview {
    background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%);
    color: white;
    padding: 1.5rem;
    border-radius: 16px;
    margin-top: 1.5rem;
    box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
    animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: scale(0.95);
    }
    to {
        opacity: 1;
        transform: scale(1);
    }
}

.shift-preview-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem 0;
    border-bottom: 1px solid rgba(255,255,255,0.2);
}

.shift-preview-item:last-child {
    border-bottom: none;
}

.shift-preview-label {
    opacity: 0.9;
    font-size: 0.9rem;
}

.shift-preview-value {
    font-weight: 600;
    font-size: 1.1rem;
}

.multi-select-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.4rem 0.9rem;
    background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%);
    color: white;
    border-radius: 20px;
    margin: 0.25rem;
    font-size: 0.875rem;
    box-shadow: 0 2px 8px rgba(102, 126, 234, 0.3);
    transition: all 0.2s ease;
    animation: bounceIn 0.3s ease;
}

.multi-select-badge:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

@keyframes bounceIn {
    0% {
        opacity: 0;
        transform: scale(0.3);
    }
    50% {
        opacity: 1;
        transform: scale(1.05);
    }
    100% {
        transform: scale(1);
    }
}

.multi-select-badge .remove-badge {
    cursor: pointer;
    font-weight: bold;
    width: 18px;
    height: 18px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(255,255,255,0.3);
    border-radius: 50%;
    transition: all 0.2s ease;
}

.multi-select-badge .remove-badge:hover {
    background: rgba(255,255,255,0.5);
    transform: rotate(90deg);
}

.multi-select-container {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    min-height: 60px;
    padding: 0.75rem;
    border: 2px dashed #dee2e6;
    border-radius: 12px;
    background: linear-gradient(to bottom, #f8f9fa, #ffffff);
    transition: all 0.3s ease;
}

.multi-select-container:empty::before {
    content: "Chưa có mục nào được chọn...";
    color: #adb5bd;
    font-style: italic;
}

.multi-select-container:has(.multi-select-badge) {
    border-color: #4f46e5;
    background: linear-gradient(to bottom, #f8f9ff, #ffffff);
}

.date-range-option {
    display: none;
    animation: slideDown 0.3s ease;
}

.date-range-option.show {
    display: block;
}

.nav-tabs {
    border-bottom: 2px solid #e9ecef;
    margin-bottom: 2rem;
}

.nav-tabs .nav-link {
    color: #6c757d;
    border: none;
    border-bottom: 3px solid transparent;
    padding: 1rem 2rem;
    font-weight: 500;
    transition: all 0.3s ease;
    position: relative;
}

.nav-tabs .nav-link:hover {
    color: #4f46e5;
    border-color: transparent;
    background: rgba(102, 126, 234, 0.05);
}

.nav-tabs .nav-link.active {
    color: #4f46e5;
    border-bottom-color: #4f46e5;
    background: transparent;
    font-weight: 600;
}

.nav-tabs .nav-link i {
    margin-right: 0.5rem;
}

/* Custom date input styling */
.date-input-wrapper {
    position: relative;
}

.date-input-wrapper input[type="date"] {
    position: relative;
    width: 100%;
    padding-right: 40px;
}

.date-input-wrapper input[type="date"]::-webkit-calendar-picker-indicator {
    position: absolute;
    right: 12px;
    cursor: pointer;
    opacity: 0.6;
    transition: opacity 0.2s ease;
}

.date-input-wrapper input[type="date"]::-webkit-calendar-picker-indicator:hover {
    opacity: 1;
}

.date-display {
    position: absolute;
    top: 0;
    left: 0;
    width: calc(100% - 40px);
    height: 100%;
    padding: 0.375rem 0.75rem;
    pointer-events: none;
    display: flex;
    align-items: center;
    background: white;
    color: #212529;
    font-weight: 500;
    z-index: 1;
}

.date-input-wrapper input[type="date"] {
    color: transparent;
}

.date-input-wrapper input[type="date"]:focus {
    color: transparent;
    border-color: #4f46e5;
    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
}

/* Form controls enhancement */
.form-control, .form-select {
    border-radius: 8px;
    border: 1px solid #dee2e6;
    padding: 0.6rem 0.9rem;
    transition: all 0.3s ease;
}

.form-control:focus, .form-select:focus {
    border-color: #4f46e5;
    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
}

.form-label {
    font-weight: 600;
    color: #495057;
    margin-bottom: 0.5rem;
}

.form-label i {
    color: #4f46e5;
    width: 20px;
}

/* Button styling */
.btn {
    border-radius: 8px;
    padding: 0.6rem 1.5rem;
    font-weight: 500;
    transition: all 0.3s ease;
}

.btn-success {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    border: none;
    box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
}

.btn-success:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(40, 167, 69, 0.4);
}

.btn-outline-secondary {
    border: 2px solid #dee2e6;
}

.btn-outline-secondary:hover {
    background: #f8f9fa;
    border-color: #adb5bd;
    transform: translateY(-2px);
}

.btn-outline-primary {
    border: 2px solid #4f46e5;
    color: #4f46e5;
}

.btn-outline-primary:hover {
    background: #4f46e5;
    border-color: #4f46e5;
    color: white;
}

.btn-check:checked + .btn-outline-primary {
    background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%);
    border-color: #4f46e5;
    color: white;
}

/* Card styling */
.stats-card {
    border-radius: 16px;
    border: none;
    box-shadow: 0 2px 12px rgba(0,0,0,0.08);
    transition: all 0.3s ease;
}

.stats-card:hover {
    box-shadow: 0 8px 24px rgba(0,0,0,0.12);
    transform: translateY(-2px);
}

.stats-card .card-body {
    padding: 2rem;
}

/* Checkbox styling */
.form-check-input:checked {
    background-color: #4f46e5;
    border-color: #4f46e5;
}

.form-check-input:focus {
    border-color: #4f46e5;
    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
}

.form-check {
    padding: 0.5rem;
    border-radius: 6px;
    transition: background 0.2s ease;
}

.form-check:hover {
    background: rgba(102, 126, 234, 0.05);
}

/* Select multi-size styling */
select[size] {
    border-radius: 12px;
    padding: 0.5rem;
}

select[size] option {
    padding: 0.5rem;
    border-radius: 6px;
    margin: 2px 0;
}

select[size] option:hover {
    background: rgba(102, 126, 234, 0.1);
}

/* Summary box */
#multiSummary {
    background: linear-gradient(135deg, #f8f9ff 0%, #ffffff 100%);
    border: 2px solid #4f46e5;
    border-radius: 12px;
    padding: 1.5rem;
    margin-top: 1.5rem;
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.15);
}

#multiSummary .badge {
    font-size: 1rem;
    padding: 0.5rem 1rem;
}

/* Responsive improvements */
@media (max-width: 768px) {
    .stats-card .card-body {
        padding: 1.5rem;
    }
    
    .nav-tabs .nav-link {
        padding: 0.75rem 1rem;
        font-size: 0.9rem;
    }
}
</style>

<div class="p-3">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <!-- Tab Navigation -->
            <ul class="nav nav-tabs mb-4" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="single-tab" data-bs-toggle="tab" data-bs-target="#single-shift" type="button" role="tab">
                        <i class="fas fa-user me-2"></i>Đăng ký đơn lẻ
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="multiple-tab" data-bs-toggle="tab" data-bs-target="#multiple-shifts" type="button" role="tab">
                        <i class="fas fa-users me-2"></i>Đăng ký hàng loạt
                    </button>
                </li>
            </ul>

            <!-- Tab Content -->
            <div class="tab-content">
                <!-- Single Shift Registration -->
                <div class="tab-pane fade show active" id="single-shift" role="tabpanel">
                    <div class="stats-card">
                        <div class="card-body">
                            <form method="POST" action="<?= $basePath ?>/teaching-shifts/admin/create" id="singleShiftForm">
                        <!-- Staff Selection -->
                        <div class="mb-4">
                            <label class="form-label">
                                <i class="fas fa-user me-2"></i>Chọn nhân viên
                            </label>
                            <select class="form-select" name="staff_id" id="staffSelect" required>
                                <option value="">-- Chọn nhân viên --</option>
                                <?php foreach ($staffList as $staff): ?>
                                    <option value="<?= $staff['id'] ?>" <?= (isset($_POST['staff_id']) && $_POST['staff_id'] == $staff['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($staff['full_name']) ?> - <?= htmlspecialchars($staff['email']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Shift Date -->
                        <div class="mb-4">
                            <label class="form-label">
                                <i class="fas fa-calendar me-2"></i>Ngày dạy
                            </label>
                            <div class="date-input-wrapper">
                                <span class="date-display" id="shiftDateDisplay"></span>
                                <input type="date" class="form-control" name="shift_date" id="shiftDate" 
                                       value="<?= date('Y-m-d') ?>" required>
                            </div>
                            <small class="text-muted"><i class="fas fa-info-circle me-1"></i>Admin có thể chọn bất kỳ ngày nào (kể cả ngày đã qua)</small>
                        </div>

                        <!-- Shift Type -->
                        <div class="mb-4">
                            <label class="form-label">
                                <i class="fas fa-clock me-2"></i>Chọn ca dạy
                            </label>
                            <select class="form-select" name="shift_id" id="shiftSelect" required>
                                <option value="">-- Chọn ca dạy --</option>
                                <?php foreach ($activeShifts as $shift): ?>
                                    <option value="<?= $shift['id'] ?>" 
                                            data-start="<?= htmlspecialchars($shift['start_time']) ?>"
                                            data-end="<?= htmlspecialchars($shift['end_time']) ?>"
                                            data-rate="<?= htmlspecialchars($shift['hourly_rate']) ?>">
                                        <?= htmlspecialchars($shift['name']) ?> 
                                        (<?= date('H:i', strtotime($shift['start_time'])) ?> - <?= date('H:i', strtotime($shift['end_time'])) ?>)
                                        - <?= number_format($shift['hourly_rate']) ?>đ/giờ
                                    </option>
                                <?php endforeach; ?>
                                <option value="custom">Ca tùy chỉnh (Nhập thời gian khác)</option>
                            </select>
                        </div>

                        <!-- Custom Time Fields -->
                        <div class="custom-shift-fields" id="customShiftFields">
                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label class="form-label">
                                        <i class="fas fa-clock me-2"></i>Giờ bắt đầu
                                    </label>
                                    <input type="time" class="form-control" name="custom_start" id="customStart">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">
                                        <i class="fas fa-clock me-2"></i>Giờ kết thúc
                                    </label>
                                    <input type="time" class="form-control" name="custom_end" id="customEnd">
                                </div>
                            </div>
                        </div>

                        <!-- Notes -->
                        <div class="mb-4">
                            <label class="form-label">
                                <i class="fas fa-sticky-note me-2"></i>Ghi chú (Tùy chọn)
                            </label>
                            <textarea class="form-control" name="notes" rows="3" 
                                      placeholder="Thông tin thêm về ca dạy..."><?= htmlspecialchars($_POST['notes'] ?? '') ?></textarea>
                        </div>

                        <!-- Auto Approve -->
                        <div class="mb-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="auto_approve" value="1" 
                                       id="autoApprove" <?= (isset($_POST['auto_approve']) && $_POST['auto_approve'] === '1') ? 'checked' : '' ?>>
                                <label class="form-check-label" for="autoApprove">
                                    <i class="fas fa-check-circle text-success me-2"></i>
                                    Tự động duyệt ca dạy này (Không cần nhân viên xác nhận)
                                </label>
                            </div>
                            <small class="text-muted">
                                Nếu chọn, ca dạy sẽ được duyệt ngay lập tức. Nếu không, nhân viên cần xác nhận.
                            </small>
                        </div>

                        <!-- Preview -->
                        <div class="shift-preview" id="shiftPreview" style="display: none;">
                            <h6 class="mb-3"><i class="fas fa-eye me-2"></i>Xem trước thông tin ca dạy</h6>
                            <div class="shift-preview-item">
                                <span class="shift-preview-label">Nhân viên:</span>
                                <span class="shift-preview-value" id="previewStaff">-</span>
                            </div>
                            <div class="shift-preview-item">
                                <span class="shift-preview-label">Ngày:</span>
                                <span class="shift-preview-value" id="previewDate">-</span>
                            </div>
                            <div class="shift-preview-item">
                                <span class="shift-preview-label">Thời gian:</span>
                                <span class="shift-preview-value" id="previewTime">-</span>
                            </div>
                            <div class="shift-preview-item">
                                <span class="shift-preview-label">Số giờ:</span>
                                <span class="shift-preview-value" id="previewHours">-</span>
                            </div>
                            <div class="shift-preview-item">
                                <span class="shift-preview-label">Lương/giờ:</span>
                                <span class="shift-preview-value" id="previewRate">-</span>
                            </div>
                            <div class="shift-preview-item">
                                <span class="shift-preview-label">Tổng lương dự kiến:</span>
                                <span class="shift-preview-value" id="previewTotal">-</span>
                            </div>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="d-flex gap-2 mt-4">
                            <button type="submit" class="btn btn-primary flex-fill">
                                <i class="fas fa-save me-2"></i>Đăng ký ca dạy
                            </button>
                            <a href="<?= $basePath ?>/teaching-shifts/admin" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-2"></i>Hủy
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Multiple Shifts Registration -->
        <div class="tab-pane fade" id="multiple-shifts" role="tabpanel">
            <div class="stats-card">
                <div class="card-body">
                    <form method="POST" action="<?= $basePath ?>/teaching-shifts/admin/create-multiple" id="multipleShiftForm">
                        <input type="hidden" name="multiple" value="1">
                        
                        <!-- Staff Selection (Multiple) -->
                        <div class="mb-4">
                            <label class="form-label">
                                <i class="fas fa-users me-2"></i>Chọn nhân viên (Có thể chọn nhiều)
                            </label>
                            <select class="form-select" id="multiStaffSelect" size="8">
                                <option value="">-- Chọn để thêm nhân viên --</option>
                                <?php foreach ($staffList as $staff): ?>
                                    <option value="<?= $staff['id'] ?>" data-name="<?= htmlspecialchars($staff['full_name']) ?>">
                                        <?= htmlspecialchars($staff['full_name']) ?> - <?= htmlspecialchars($staff['email']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div id="selectedStaffContainer" class="multi-select-container mt-2"></div>
                        </div>

                        <!-- Date Range Option -->
                        <div class="mb-4">
                            <label class="form-label">
                                <i class="fas fa-calendar me-2"></i>Chọn ngày dạy
                            </label>
                            <div class="btn-group w-100 mb-3" role="group">
                                <input type="radio" class="btn-check" name="date_mode" id="singleDate" value="single" checked>
                                <label class="btn btn-outline-primary" for="singleDate">Một ngày</label>
                                
                                <input type="radio" class="btn-check" name="date_mode" id="dateRange" value="range">
                                <label class="btn btn-outline-primary" for="dateRange">Khoảng thời gian</label>
                            </div>
                            
                            <div id="singleDateOption">
                                <div class="date-input-wrapper">
                                    <span class="date-display" id="multiShiftDateDisplay"></span>
                                    <input type="date" class="form-control" name="multi_shift_date" id="multiShiftDate" 
                                           value="<?= date('Y-m-d') ?>">
                                </div>
                            </div>
                            
                            <div id="dateRangeOption" class="date-range-option">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label small">Từ ngày</label>
                                        <div class="date-input-wrapper">
                                            <span class="date-display" id="dateFromDisplay"></span>
                                            <input type="date" class="form-control" name="date_from" id="dateFrom" 
                                                   value="<?= date('Y-m-d') ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small">Đến ngày</label>
                                        <div class="date-input-wrapper">
                                            <span class="date-display" id="dateToDisplay"></span>
                                            <input type="date" class="form-control" name="date_to" id="dateTo" 
                                                   value="<?= date('Y-m-d', strtotime('+7 days')) ?>">
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-2">
                                    <label class="form-label small">Chọn các ngày trong tuần</label>
                                    <div class="d-flex gap-2 flex-wrap">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="weekdays[]" value="1" id="mon">
                                            <label class="form-check-label" for="mon">T2</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="weekdays[]" value="2" id="tue">
                                            <label class="form-check-label" for="tue">T3</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="weekdays[]" value="3" id="wed">
                                            <label class="form-check-label" for="wed">T4</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="weekdays[]" value="4" id="thu">
                                            <label class="form-check-label" for="thu">T5</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="weekdays[]" value="5" id="fri">
                                            <label class="form-check-label" for="fri">T6</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="weekdays[]" value="6" id="sat" checked>
                                            <label class="form-check-label" for="sat">T7</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Shift Selection (Multiple) -->
                        <div class="mb-4">
                            <label class="form-label">
                                <i class="fas fa-clock me-2"></i>Chọn ca dạy (Có thể chọn nhiều ca)
                            </label>
                            <select class="form-select" id="multiShiftSelect" size="6">
                                <option value="">-- Chọn để thêm ca dạy --</option>
                                <?php foreach ($activeShifts as $shift): ?>
                                    <option value="<?= $shift['id'] ?>" 
                                            data-name="<?= htmlspecialchars($shift['name']) ?>"
                                            data-start="<?= htmlspecialchars($shift['start_time']) ?>"
                                            data-end="<?= htmlspecialchars($shift['end_time']) ?>"
                                            data-rate="<?= htmlspecialchars($shift['hourly_rate']) ?>">
                                        <?= htmlspecialchars($shift['name']) ?> 
                                        (<?= date('H:i', strtotime($shift['start_time'])) ?> - <?= date('H:i', strtotime($shift['end_time'])) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div id="selectedShiftsContainer" class="multi-select-container mt-2"></div>
                        </div>

                        <!-- Notes -->
                        <div class="mb-4">
                            <label class="form-label">
                                <i class="fas fa-sticky-note me-2"></i>Ghi chú (Tùy chọn)
                            </label>
                            <textarea class="form-control" name="notes" rows="2" 
                                      placeholder="Ghi chú chung cho tất cả các ca..."></textarea>
                        </div>

                        <!-- Auto Approve -->
                        <div class="mb-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="auto_approve" value="1" 
                                       id="multiAutoApprove" checked>
                                <label class="form-check-label" for="multiAutoApprove">
                                    <i class="fas fa-check-circle text-success me-2"></i>
                                    Tự động duyệt tất cả ca dạy
                                </label>
                            </div>
                        </div>

                        <!-- Summary Preview -->
                        <div class="alert alert-info" id="multiSummary" style="display: none;">
                            <h6 class="alert-heading"><i class="fas fa-info-circle me-2"></i>Tóm tắt đăng ký</h6>
                            <div id="summaryContent"></div>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-success flex-fill" id="submitMultiple">
                                <i class="fas fa-calendar-plus me-2"></i>Đăng ký hàng loạt
                            </button>
                            <a href="<?= $basePath ?>/teaching-shifts/admin" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-2"></i>Hủy
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
</div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // ========== DATE FORMAT HELPER ==========
    function formatDateToDisplay(dateStr) {
        if (!dateStr) return '';
        const parts = dateStr.split('-'); // yyyy-mm-dd
        if (parts.length === 3) {
            return `${parts[2]}/${parts[1]}/${parts[0]}`; // dd/mm/yyyy
        }
        return dateStr;
    }

    function setupDateDisplay(inputId, displayId) {
        const input = document.getElementById(inputId);
        const display = document.getElementById(displayId);
        
        if (!input || !display) return;

        // Set initial display
        if (input.value) {
            display.textContent = formatDateToDisplay(input.value);
        }

        // Update display when date changes
        input.addEventListener('change', function() {
            display.textContent = formatDateToDisplay(this.value);
        });

        // Also update on input (for some browsers)
        input.addEventListener('input', function() {
            display.textContent = formatDateToDisplay(this.value);
        });
    }

    // Setup all date displays
    setupDateDisplay('shiftDate', 'shiftDateDisplay');
    setupDateDisplay('multiShiftDate', 'multiShiftDateDisplay');
    setupDateDisplay('dateFrom', 'dateFromDisplay');
    setupDateDisplay('dateTo', 'dateToDisplay');

    // ========== SINGLE SHIFT FORM ==========
    const shiftSelect = document.getElementById('shiftSelect');
    const customFields = document.getElementById('customShiftFields');
    const customStart = document.getElementById('customStart');
    const customEnd = document.getElementById('customEnd');
    const staffSelect = document.getElementById('staffSelect');
    const shiftDate = document.getElementById('shiftDate');
    const preview = document.getElementById('shiftPreview');

    // Show/hide custom time fields
    if (shiftSelect) {
        shiftSelect.addEventListener('change', function() {
            if (this.value === 'custom') {
                customFields.classList.add('show');
                customStart.required = true;
                customEnd.required = true;
            } else {
                customFields.classList.remove('show');
                customStart.required = false;
                customEnd.required = false;
            }
            updatePreview();
        });
    }

    // Update preview when any field changes
    [staffSelect, shiftDate, shiftSelect, customStart, customEnd].forEach(element => {
        if (element) element.addEventListener('change', updatePreview);
    });

    function updatePreview() {
        if (!staffSelect || !shiftSelect || !shiftDate || !preview) return;
        
        const staff = staffSelect.options[staffSelect.selectedIndex];
        const shift = shiftSelect.options[shiftSelect.selectedIndex];
        const date = shiftDate.value;

        if (!staff.value || !shift.value || !date) {
            preview.style.display = 'none';
            return;
        }

        preview.style.display = 'block';
        document.getElementById('previewStaff').textContent = staff.text.split(' - ')[0];

        // Format date to dd/mm/yyyy for display
        const dateObj = new Date(date + 'T00:00:00');
        const day = String(dateObj.getDate()).padStart(2, '0');
        const month = String(dateObj.getMonth() + 1).padStart(2, '0');
        const year = dateObj.getFullYear();
        document.getElementById('previewDate').textContent = `${day}/${month}/${year}`;

        let startTime, endTime, rate = 50000;

        if (shift.value === 'custom') {
            startTime = customStart.value;
            endTime = customEnd.value;
        } else {
            startTime = shift.dataset.start;
            endTime = shift.dataset.end;
            rate = parseFloat(shift.dataset.rate) || 50000;
        }

        if (startTime && endTime) {
            document.getElementById('previewTime').textContent = `${startTime} - ${endTime}`;

            const start = new Date(`2000-01-01 ${startTime}`);
            const end = new Date(`2000-01-01 ${endTime}`);
            const hours = (end - start) / (1000 * 60 * 60);

            if (hours > 0) {
                document.getElementById('previewHours').textContent = hours.toFixed(1) + ' giờ';
                document.getElementById('previewRate').textContent = rate.toLocaleString('vi-VN') + 'đ';
                
                const total = hours * rate;
                document.getElementById('previewTotal').textContent = total.toLocaleString('vi-VN') + 'đ';
            }
        }
    }

    // Validation before submit
    const singleForm = document.getElementById('singleShiftForm');
    if (singleForm) {
        singleForm.addEventListener('submit', function(e) {
            const shiftValue = shiftSelect.value;
            
            if (shiftValue === 'custom') {
                if (!customStart.value || !customEnd.value) {
                    e.preventDefault();
                    alert('Vui lòng nhập giờ bắt đầu và kết thúc cho ca tùy chỉnh!');
                    return false;
                }

                const start = new Date(`2000-01-01 ${customStart.value}`);
                const end = new Date(`2000-01-01 ${customEnd.value}`);
                
                if (end <= start) {
                    e.preventDefault();
                    alert('Giờ kết thúc phải lớn hơn giờ bắt đầu!');
                    return false;
                }
            }
        });
    }

    // ========== MULTIPLE SHIFTS FORM ==========
    const multiStaffSelect = document.getElementById('multiStaffSelect');
    const selectedStaffContainer = document.getElementById('selectedStaffContainer');
    const multiShiftSelect = document.getElementById('multiShiftSelect');
    const selectedShiftsContainer = document.getElementById('selectedShiftsContainer');
    const multiSummary = document.getElementById('multiSummary');
    const summaryContent = document.getElementById('summaryContent');
    const multipleForm = document.getElementById('multipleShiftForm');
    
    let selectedStaff = [];
    let selectedShifts = [];

    // Date mode toggle
    document.querySelectorAll('input[name="date_mode"]').forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.value === 'single') {
                document.getElementById('singleDateOption').style.display = 'block';
                document.getElementById('dateRangeOption').classList.remove('show');
            } else {
                document.getElementById('singleDateOption').style.display = 'none';
                document.getElementById('dateRangeOption').classList.add('show');
            }
            updateMultiSummary();
        });
    });

    // Staff multi-select
    if (multiStaffSelect) {
        multiStaffSelect.addEventListener('change', function() {
            const option = this.options[this.selectedIndex];
            if (!option.value) return;
            
            const staffId = option.value;
            const staffName = option.dataset.name;
            
            if (!selectedStaff.find(s => s.id === staffId)) {
                selectedStaff.push({id: staffId, name: staffName});
                renderSelectedStaff();
                updateMultiSummary();
            }
            this.selectedIndex = 0;
        });
    }

    function renderSelectedStaff() {
        selectedStaffContainer.innerHTML = selectedStaff.map(staff => `
            <div class="multi-select-badge">
                <span>${staff.name}</span>
                <span class="remove-badge" onclick="removeStaff('${staff.id}')">&times;</span>
                <input type="hidden" name="staff_ids[]" value="${staff.id}">
            </div>
        `).join('');
    }

    window.removeStaff = function(staffId) {
        selectedStaff = selectedStaff.filter(s => s.id !== staffId);
        renderSelectedStaff();
        updateMultiSummary();
    };

    // Shift multi-select
    if (multiShiftSelect) {
        multiShiftSelect.addEventListener('change', function() {
            const option = this.options[this.selectedIndex];
            if (!option.value) return;
            
            const shiftId = option.value;
            const shiftName = option.dataset.name;
            const start = option.dataset.start;
            const end = option.dataset.end;
            
            if (!selectedShifts.find(s => s.id === shiftId)) {
                selectedShifts.push({
                    id: shiftId, 
                    name: shiftName,
                    start: start,
                    end: end
                });
                renderSelectedShifts();
                updateMultiSummary();
            }
            this.selectedIndex = 0;
        });
    }

    function renderSelectedShifts() {
        selectedShiftsContainer.innerHTML = selectedShifts.map(shift => `
            <div class="multi-select-badge">
                <span>${shift.name}</span>
                <span class="remove-badge" onclick="removeShift('${shift.id}')">&times;</span>
                <input type="hidden" name="shift_ids[]" value="${shift.id}">
            </div>
        `).join('');
    }

    window.removeShift = function(shiftId) {
        selectedShifts = selectedShifts.filter(s => s.id !== shiftId);
        renderSelectedShifts();
        updateMultiSummary();
    };

    function updateMultiSummary() {
        if (!multiSummary || !summaryContent) return;
        
        if (selectedStaff.length === 0 || selectedShifts.length === 0) {
            multiSummary.style.display = 'none';
            return;
        }

        const dateMode = document.querySelector('input[name="date_mode"]:checked').value;
        let totalShifts = 0;
        let dateText = '';

        if (dateMode === 'single') {
            const dateInput = document.getElementById('multiShiftDate');
            if (dateInput && dateInput.value) {
                totalShifts = selectedStaff.length * selectedShifts.length;
                const dateObj = new Date(dateInput.value + 'T00:00:00');
                dateText = dateObj.toLocaleDateString('vi-VN');
            }
        } else {
            const dateFromInput = document.getElementById('dateFrom');
            const dateToInput = document.getElementById('dateTo');
            const weekdays = document.querySelectorAll('input[name="weekdays[]"]:checked');
            
            if (dateFromInput && dateToInput && dateFromInput.value && dateToInput.value && weekdays.length > 0) {
                const days = countDaysInRange(dateFromInput.value, dateToInput.value, Array.from(weekdays).map(w => parseInt(w.value)));
                totalShifts = selectedStaff.length * selectedShifts.length * days;
                const fromDate = new Date(dateFromInput.value + 'T00:00:00');
                const toDate = new Date(dateToInput.value + 'T00:00:00');
                dateText = `${fromDate.toLocaleDateString('vi-VN')} - ${toDate.toLocaleDateString('vi-VN')} (${days} ngày)`;
            }
        }

        summaryContent.innerHTML = `
            <p class="mb-1"><strong>Nhân viên:</strong> ${selectedStaff.length} người</p>
            <p class="mb-1"><strong>Ca dạy:</strong> ${selectedShifts.length} ca</p>
            <p class="mb-1"><strong>Ngày:</strong> ${dateText}</p>
            <p class="mb-0"><strong>Tổng cộng:</strong> <span class="badge bg-success">${totalShifts} ca dạy</span></p>
        `;
        multiSummary.style.display = 'block';
    }

    function countDaysInRange(start, end, weekdays) {
        const startDate = new Date(start);
        const endDate = new Date(end);
        let count = 0;
        
        for (let d = new Date(startDate); d <= endDate; d.setDate(d.getDate() + 1)) {
            const dayOfWeek = d.getDay();
            // Luôn loại trừ Chủ nhật (0)
            if (dayOfWeek !== 0 && weekdays.includes(dayOfWeek)) {
                count++;
            }
        }
        return count;
    }

    // Validate multiple form
    if (multipleForm) {
        multipleForm.addEventListener('submit', function(e) {
            if (selectedStaff.length === 0) {
                e.preventDefault();
                alert('Vui lòng chọn ít nhất một nhân viên!');
                return false;
            }
            
            if (selectedShifts.length === 0) {
                e.preventDefault();
                alert('Vui lòng chọn ít nhất một ca dạy!');
                return false;
            }

            const dateMode = document.querySelector('input[name="date_mode"]:checked').value;
            if (dateMode === 'range') {
                const weekdays = document.querySelectorAll('input[name="weekdays[]"]:checked');
                if (weekdays.length === 0) {
                    e.preventDefault();
                    alert('Vui lòng chọn ít nhất một ngày trong tuần!');
                    return false;
                }
            }

            if (!confirm(`Bạn có chắc muốn đăng ký ${selectedStaff.length} nhân viên với ${selectedShifts.length} ca dạy?`)) {
                e.preventDefault();
                return false;
            }
        });
    }

    // Update summary on checkbox or date change
    document.querySelectorAll('input[name="weekdays[]"]').forEach(checkbox => {
        checkbox.addEventListener('change', updateMultiSummary);
    });

    document.getElementById('dateFrom')?.addEventListener('change', updateMultiSummary);
    document.getElementById('dateTo')?.addEventListener('change', updateMultiSummary);
    document.getElementById('multiShiftDate')?.addEventListener('change', updateMultiSummary);
});
</script>

<?php
$content = ob_get_clean();
useModernLayout('Đăng ký ca dạy cho nhân viên', $content);
?>
