<?php
require_once __DIR__ . '/../layouts/main.php';

// Page content
ob_start();

$headerTitle = 'Chỉnh sửa báo cáo';
$headerDesc = 'Cập nhật thông tin báo cáo đến trung tâm';
$headerButton = '<a href="/Quan_ly_trung_tam/public/reports/' . ($report['id'] ?? '') . '" class="btn btn-outline-secondary">
    <i class="fas fa-arrow-left me-2"></i>Quay lại
</a>';
?>

<?= pageHeader($headerTitle, $headerDesc, $headerButton) ?>

<style>
    /* Course Combo Box Styling */
    .course-combo-wrapper {
        position: relative;
    }

    .course-combo-input {
        font-size: 0.8rem !important;
        padding: 4px 8px !important;
    }

    .course-dropdown {
        border: 1px solid #ced4da !important;
        border-radius: 0.375rem !important;
        background: white !important;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075) !important;
        max-height: 200px;
        overflow-y: auto;
        z-index: 1050;
    }

    .dropdown-option {
        font-size: 0.8rem;
        border-bottom: 1px solid #f1f1f1;
        transition: background-color 0.15s ease-in-out;
        padding: 8px 12px;
        cursor: pointer;
    }

    .dropdown-option:last-child {
        border-bottom: none;
    }

    .dropdown-option:hover {
        background-color: #f8f9fa;
    }

    .customer-table {
        table-layout: fixed;
        width: 100%;
    }

    .customer-table th {
        font-size: 0.85rem;
        font-weight: 600;
        padding: 0.5rem 0.375rem;
        vertical-align: middle;
    }

    .customer-table td {
        padding: 0.375rem;
        vertical-align: middle;
    }

    .course-notes-cell {
        position: relative;
        padding: 0.375rem !important;
    }

    .customer-phone-input,
    .customer-name-input,
    .customer-notes-input,
    .customer-payment-select {
        font-size: 0.8rem;
    }
</style>

<div class="p-3">
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle me-2"></i>
            <?= htmlspecialchars($_SESSION['error']) ?>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <form id="editReportForm" method="POST" action="/Quan_ly_trung_tam/public/reports/<?= $report['id'] ?>/update">
        <div class="row">
            <div class="col-md-8">
                <!-- Report Information -->
                <div class="stats-card mb-4">
                    <div class="card-body">
                        <h6 class="card-title mb-4">
                            <i class="fas fa-info-circle text-primary me-2"></i>
                            Thông tin báo cáo
                        </h6>
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="report_date" class="form-label">Ngày báo cáo *</label>
                                <input type="date" id="report_date" name="report_date" 
                                       class="form-control" value="<?= $report['report_date'] ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="total_visitors" class="form-label">Tổng số khách đến</label>
                                <input type="number" id="total_visitors" name="total_visitors" 
                                       class="form-control" value="<?= $report['total_visitors'] ?>" min="0">
                            </div>
                        </div>
                        
                        <div class="mt-3">
                            <label for="notes" class="form-label">Ghi chú</label>
                            <textarea id="notes" name="notes" class="form-control" rows="3"
                                      placeholder="Ghi chú về báo cáo..."><?= htmlspecialchars($report['notes'] ?? '') ?></textarea>
                        </div>
                    </div>
                </div>

                <!-- Customer Details -->
                <div class="stats-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="card-title mb-0">
                                <i class="fas fa-users text-primary me-2"></i>
                                Chi tiết khách hàng
                                <span class="badge bg-primary" id="customer-count"><?= count($customers) ?> khách hàng</span>
                            </h6>
                            <div class="btn-group">
                                <button type="button" class="btn btn-sm btn-success" onclick="addCustomerRow()">
                                    <i class="fas fa-plus me-1"></i>Thêm khách hàng
                                </button>
                                <button type="button" class="btn btn-sm btn-warning" onclick="removeLastCustomerRow()">
                                    <i class="fas fa-minus me-1"></i>Xóa cuối
                                </button>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table customer-table">
                                <thead>
                                    <tr>
                                        <th style="width: 110px">SĐT *</th>
                                        <th style="width: 120px">Họ tên/Zalo</th>
                                        <th style="width: 280px">Khóa học quan tâm & Ghi chú</th>
                                        <th style="width: 50px; text-align: center">Chốt</th>
                                        <th style="width: 120px">Hình thức đóng HP</th>
                                        <th style="width: 50px">Xóa</th>
                                    </tr>
                                </thead>
                                <tbody id="customers-table">
                                    <!-- Existing customers will be loaded here -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <!-- Statistics -->
                <div class="stats-card mb-4">
                    <div class="card-body text-center">
                        <h6 class="card-title mb-3">
                            <i class="fas fa-chart-pie text-primary me-2"></i>
                            Thống kê
                        </h6>
                        
                        <div class="row g-3">
                            <div class="col-6">
                                <div class="p-3 bg-light rounded">
                                    <div class="h5 mb-1 text-primary" id="visitors-count"><?= $report['total_visitors'] ?></div>
                                    <small class="text-muted">Khách đến</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-3 bg-light rounded">
                                    <div class="h5 mb-1 text-success" id="registered-count"><?= $report['total_registered'] ?></div>
                                    <small class="text-muted">Khách chốt</small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-3 p-3 bg-light rounded">
                            <div class="h3 mb-1 text-warning" id="conversion-rate">
                                <?= $report['total_visitors'] > 0 ? round(($report['total_registered'] / $report['total_visitors']) * 100, 1) : 0 ?>%
                            </div>
                            <small class="text-muted">Tỷ lệ chốt</small>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-save me-2"></i>Cập nhật báo cáo
                    </button>
                </div>
            </div>
        </div>

        <!-- Hidden input for customers data -->
        <input type="hidden" id="customers_data" name="customers_data" value="">
        <input type="hidden" id="total_registered" name="total_registered" value="<?= $report['total_registered'] ?>">
    </form>
</div>

<script>
    let customerIndex = 0;
    let courses = <?= json_encode($courses ?? []) ?>;
    
    // Load existing customers on page load
    document.addEventListener('DOMContentLoaded', function() {
        const existingCustomers = <?= json_encode($customers ?? []) ?>;
        existingCustomers.forEach(customer => {
            addCustomerRowWithData(customer);
        });
        updateStats();
    });

    function addCustomerRowWithData(customerData) {
        const tableBody = document.getElementById('customers-table');
        const row = document.createElement('tr');
        row.setAttribute('data-index', customerIndex);
        
        row.innerHTML = `
            <td>
                <input type="text" class="form-control form-control-sm customer-phone-input" 
                       name="customers[${customerIndex}][phone]" 
                       placeholder="0901234567" 
                       value="${customerData.phone || ''}"
                       onchange="updateStats()" required>
            </td>
            <td>
                <input type="text" class="form-control form-control-sm customer-name-input" 
                       name="customers[${customerIndex}][full_name]" 
                       placeholder="Tên khách hàng"
                       value="${customerData.full_name || ''}">
            </td>
            <td class="course-notes-cell">
                <div class="course-combo-wrapper position-relative mb-1">
                    <input type="text" 
                           class="form-control form-control-sm course-combo-input" 
                           name="customers[${customerIndex}][course_display]" 
                           placeholder="Gõ để tìm hoặc chọn khóa học..."
                           value="${customerData.course_name || ''}"
                           autocomplete="off"
                           data-index="${customerIndex}">
                    <input type="hidden" 
                           name="customers[${customerIndex}][course_id]" 
                           class="course-id-input"
                           value="${customerData.course_id || ''}">
                    <div class="course-dropdown position-absolute w-100" 
                         style="display: none; z-index: 1000; max-height: 200px; overflow-y: auto; 
                                background: white; border: 1px solid #ced4da; border-radius: 0.375rem;
                                box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);">
                    </div>
                </div>
                <input type="text" class="form-control form-control-sm customer-notes-input" 
                       name="customers[${customerIndex}][notes]" 
                       placeholder="Ghi chú thêm"
                       value="${customerData.notes || ''}">
            </td>
            <td class="text-center">
                <div class="form-check d-flex justify-content-center">
                    <input type="checkbox" class="form-check-input" 
                           name="customers[${customerIndex}][registered]" 
                           value="1" onchange="updateStats()"
                           ${customerData.registration_status === 'registered' ? 'checked' : ''}>
                </div>
            </td>
            <td>
                <select class="form-select form-select-sm bg-light customer-payment-select" 
                        name="customers[${customerIndex}][payment_method]"
                        ${customerData.registration_status !== 'registered' ? 'disabled' : ''}>
                    <option value="">-- Chọn --</option>
                    <option value="transfer" ${customerData.payment_method === 'transfer' ? 'selected' : ''}>Chuyển khoản</option>
                    <option value="cash" ${customerData.payment_method === 'cash' ? 'selected' : ''}>Tiền mặt</option>
                </select>
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeCustomerRow(this)">
                    <i class="fas fa-times"></i>
                </button>
            </td>
        `;
        
        tableBody.appendChild(row);
        
        // Initialize course combo
        const courseWrapper = row.querySelector('.course-combo-wrapper');
        if (courseWrapper) {
            initializeCourseCombo(courseWrapper);
        }
        
        // Setup payment method logic
        const checkbox = row.querySelector('input[name*="[registered]"]');
        const paymentSelect = row.querySelector('select[name*="[payment_method]"]');
        
        checkbox.addEventListener('change', function() {
            paymentSelect.disabled = !this.checked;
            if (!this.checked) {
                paymentSelect.value = '';
                paymentSelect.classList.add('bg-light');
            } else {
                paymentSelect.classList.remove('bg-light');
            }
            updateStats();
        });
        
        customerIndex++;
    }

    // Include the same functions from create.php for combo box and customer management
    <?php include __DIR__ . '/create_js_functions.php'; ?>
    
    function removeCustomerRow(button) {
        const row = button.closest('tr');
        row.remove();
        updateCustomerCount();
        updateStats();
    }
    
    function updateCustomerCount() {
        const rows = document.querySelectorAll('#customers-table tr');
        document.getElementById('customer-count').textContent = rows.length + ' khách hàng';
    }

    // Form submission
    document.getElementById('editReportForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Collect customer data
        const customers = [];
        const rows = document.querySelectorAll('#customers-table tr');
        
        rows.forEach(row => {
            const phone = row.querySelector('input[name*="[phone]"]').value;
            const fullName = row.querySelector('input[name*="[full_name]"]').value;
            const notes = row.querySelector('input[name*="[notes]"]').value;
            const registered = row.querySelector('input[name*="[registered]"]').checked;
            const paymentMethod = row.querySelector('select[name*="[payment_method]"]').value;
            const courseIdInput = row.querySelector('input[name*="[course_id]"]');
            const courseId = courseIdInput ? courseIdInput.value : '';
            
            if (phone.trim()) {
                customers.push({
                    phone: phone,
                    full_name: fullName,
                    notes: notes,
                    registered: registered ? 1 : 0,
                    registration_status: registered ? 'registered' : 'not_registered',
                    payment_method: paymentMethod || null,
                    course_id: courseId || null,
                    status: 'new'
                });
            }
        });
        
        // Set customers data
        document.getElementById('customers_data').value = JSON.stringify(customers);
        
        // Submit form
        this.submit();
    });
</script>

<?php
$content = ob_get_clean();
useModernLayout('Chỉnh sửa báo cáo', $content);
?>