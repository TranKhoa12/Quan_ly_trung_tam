<?php
// Start output buffering
ob_start();
?>

<style>
.counter-btn {
    width: 45px;
    height: 45px;
    border-radius: 12px;
    border: 2px solid;
    background: white;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
    cursor: pointer;
    font-size: 18px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.counter-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.counter-btn:active {
    transform: translateY(0);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.counter-btn.btn-outline-info {
    border-color: #0dcaf0;
    color: #0dcaf0;
}

.counter-btn.btn-outline-info:hover {
    background: #0dcaf0;
    color: white;
}

.counter-btn.btn-info {
    border-color: #0dcaf0;
    background: #0dcaf0;
    color: white;
}

.counter-btn.btn-info:hover {
    background: #0bb4d6;
    border-color: #0bb4d6;
}

.counter-btn i {
    font-size: 16px;
}
</style>

<!-- Page Header -->
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-4 border-bottom">
    <div>
        <h1 class="h2 mb-2">
            <i class="fas fa-plus-circle text-primary me-2"></i>
            Tạo báo cáo đến trung tâm
        </h1>
        <p class="text-muted mb-0">Nhập thông tin khách hàng đến trung tâm trong ngày</p>
    </div>
    <div class="btn-toolbar mb-2 mb-md-0">
        <button type="button" class="btn btn-outline-secondary me-2" onclick="goBack()">
            <i class="fas fa-arrow-left me-2"></i>Quay về
        </button>
        <button type="button" class="btn btn-primary" onclick="saveReport()">
            <i class="fas fa-save me-2"></i>Lưu báo cáo
        </button>
    </div>
</div>

<!-- Alert Messages -->
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <?= htmlspecialchars($error) ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="/Quan_ly_trung_tam/public/reports" id="reportForm" enctype="multipart/form-data">
                            <!-- Row 1: Basic Info (Left) + Statistics Cards (Right) -->
                            <div class="row g-3 mb-4">
                                <!-- Left Column - Basic Info -->
                                <div class="col-lg-6">
                                    <div class="stats-card h-100">
                                        <div class="card-body d-flex flex-column">
                                            <h6 class="card-title mb-3">
                                                <i class="fas fa-info-circle text-primary me-2"></i>
                                                Thông tin cơ bản
                                            </h6>
                                            
                                            <div class="row g-3 flex-grow-1">
                                                <div class="col-md-6">
                                                    <label class="form-label">
                                                        <i class="fas fa-calendar text-primary"></i> Ngày báo cáo *
                                                    </label>
                                                    <input type="date" class="form-control" id="report_date" name="report_date" 
                                                           value="<?= $old_data['report_date'] ?? date('Y-m-d') ?>" required>
                                                    <small class="text-muted">Ngày thực hiện công việc</small>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">
                                                        <i class="fas fa-user text-primary"></i> Nhân viên phụ trách *
                                                    </label>
                                                    <?php if (isset($userRole) && $userRole === 'admin'): ?>
                                                        <select class="form-select" id="staff_id" name="staff_id" required>
                                                            <?php if (!empty($staff)): ?>
                                                                <?php foreach ($staff as $s): ?>
                                                                    <option value="<?= $s['id'] ?>" 
                                                                            <?= (isset($old_data['staff_id']) && $old_data['staff_id'] == $s['id']) ? 'selected' : '' ?>>
                                                                        <?= htmlspecialchars($s['full_name']) ?>
                                                                    </option>
                                                                <?php endforeach; ?>
                                                            <?php else: ?>
                                                                <option value="1" selected>Admin</option>
                                                            <?php endif; ?>
                                                        </select>
                                                        <small class="text-muted">Chọn nhân viên phụ trách</small>
                                                    <?php else: ?>
                                                        <input type="text" class="form-control" 
                                                               value="<?= htmlspecialchars($staff[0]['full_name'] ?? 'Bạn') ?>" readonly>
                                                        <input type="hidden" name="staff_id" value="<?= $staff[0]['id'] ?? '' ?>">
                                                        <small class="text-muted">Báo cáo của bạn</small>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Right Column - Statistics Cards -->
                                <div class="col-lg-6">
                                    <div class="row g-3">
                                        <div class="col-sm-6">
                                            <div class="stats-card border-info h-100">
                                                <div class="card-body text-center d-flex flex-column justify-content-between">
                                                    <div>
                                                        <div class="d-flex align-items-center justify-content-center mb-3">
                                                            <i class="fas fa-users text-info me-2 fs-5"></i>
                                                            <span class="fw-semibold">Số lượng đến</span>
                                                        </div>
                                                        <div class="d-flex align-items-center justify-content-center mb-3">
                                                            <button type="button" class="counter-btn btn-outline-info me-3" onclick="changeVisitors(-1)">
                                                                <i class="fas fa-minus"></i>
                                                            </button>
                                                            <div class="text-center">
                                                                <span class="fs-1 fw-bold text-info d-block" id="visitors-count">1</span>
                                                                <small class="text-muted">người</small>
                                                            </div>
                                                            <button type="button" class="counter-btn btn-info ms-3" onclick="changeVisitors(1)">
                                                                <i class="fas fa-plus text-white"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                    <div class="mt-auto pt-2">
                                                        <small class="text-muted">Tổng số KH đến trong ngày</small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="col-sm-6">
                                            <div class="stats-card border-success h-100">
                                                <div class="card-body text-center d-flex flex-column justify-content-between">
                                                    <div>
                                                        <div class="d-flex align-items-center justify-content-center mb-3">
                                                            <i class="fas fa-check-circle text-success me-2 fs-5"></i>
                                                            <span class="fw-semibold">Số lượng chốt</span>
                                                        </div>
                                                        <div class="d-flex align-items-center justify-content-center mb-3">
                                                            <div class="text-center">
                                                                <span class="fs-1 fw-bold text-success d-block" id="registered-count">0</span>
                                                                <small class="text-muted">khách hàng</small>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="mt-auto pt-2">
                                                        <small class="text-muted">Tự động tính khi KH chốt</small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Row 2: Customer Details (Full Width) -->
                            <div class="row">
                                <div class="col-12">
                                    <div class="customer-table-container">
                                        <div class="customer-table-header">
                                            <h6 class="mb-0">
                                                <i class="fas fa-users"></i>
                                                Chi tiết khách hàng
                                            </h6>
                                            <span class="badge bg-white text-primary" id="customer-count">1 khách hàng</span>
                                            <small class="w-100">
                                                SĐT bắt buộc; họ tên có thể ghi dạng <em>Họ tên/Zalo</em>. Hệ thống tự đếm số khách chốt theo từng dòng.
                                            </small>
                                        </div>

                                        <div class="table-responsive">
                                            <table class="table customer-table">
                                                <thead>
                                                    <tr>
                                                        <th style="width: 110px">SĐT *</th>
                                                        <th style="width: 120px">Họ tên/Zalo</th>
                                                        <th style="width: 280px">Khóa học quan tâm & Ghi chú</th>
                                                        <th style="width: 50px; text-align: center">Chốt</th>
                                                        <th style="width: 150px; text-align: center">Nhập doanh thu</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="customers-table">
                                                    <!-- Customer rows will be added here -->
                                                </tbody>
                                            </table>
                                        </div>

                                    </div>
                                </div>
                            </div>
                            
                            <!-- Hidden inputs -->
                            <input type="hidden" id="total_visitors" name="total_visitors" value="1">
                            <input type="hidden" id="total_registered" name="total_registered" value="0">
                            <input type="hidden" name="notes" id="notes" value="">
                            <input type="hidden" name="report_time" value="<?= date('H:i') ?>">
                        </form>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Modal Nhập Doanh Thu -->
    <div class="modal fade" id="revenueModal" tabindex="-1" aria-labelledby="revenueModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="revenueModalLabel">
                        <i class="fas fa-dollar-sign text-success me-2"></i>
                        Nhập thông tin doanh thu
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="revenueForm">
                        <input type="hidden" id="revenue_customer_index" value="">
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="revenue_student_name" class="form-label">
                                    <i class="fas fa-user text-primary"></i> Tên học viên *
                                </label>
                                <input type="text" class="form-control" id="revenue_student_name" readonly>
                            </div>

                            <div class="col-md-6">
                                <label for="revenue_payment_date" class="form-label">
                                    <i class="fas fa-calendar text-primary"></i> Ngày thanh toán *
                                </label>
                                <input type="date" class="form-control" id="revenue_payment_date" required 
                                       max="<?= date('Y-m-d') ?>">
                            </div>

                            <div class="col-md-6">
                                <label for="revenue_amount" class="form-label">
                                    <i class="fas fa-money-bill text-success"></i> Số tiền *
                                </label>
                                <input type="text" class="form-control" id="revenue_amount" 
                                       placeholder="VD: 2,500,000" inputmode="numeric" required>
                                <small class="text-muted">Số tiền sẽ tự động định dạng</small>
                            </div>

                            <div class="col-md-6">
                                <label for="revenue_receipt_code" class="form-label">
                                    <i class="fas fa-receipt text-info"></i> Mã phiếu thu *
                                </label>
                                <input type="text" class="form-control" id="revenue_receipt_code" 
                                       placeholder="VD: BT20250001" required autocomplete="off">
                                <small class="text-muted receipt-check-message"></small>
                            </div>

                            <div class="col-md-6">
                                <label for="revenue_transfer_type" class="form-label">
                                    <i class="fas fa-exchange-alt text-warning"></i> Hình thức chuyển *
                                </label>
                                <select class="form-select" id="revenue_transfer_type" required>
                                    <option value="">-- Chọn hình thức --</option>
                                    <option value="cash">Tiền mặt</option>
                                    <option value="account_co_nhi">TK Cô Nhi</option>
                                    <option value="account_thay_hien">TK Thầy Hiến</option>
                                    <option value="account_company">TK Công ty</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label for="revenue_payment_content" class="form-label">
                                    <i class="fas fa-tags text-secondary"></i> Loại thanh toán *
                                </label>
                                <select class="form-select" id="revenue_payment_content" required>
                                    <option value="">-- Chọn loại --</option>
                                    <option value="full_payment">Thanh toán đủ</option>
                                    <option value="deposit">Cọc học phí</option>
                                    <option value="full_payment_after_deposit">Thanh toán đủ (đã cọc)</option>
                                    <option value="accounting_deposit">Cọc học phí (kế toán)</option>
                                    <option value="l1_payment">Thanh toán L1</option>
                                    <option value="l2_payment">Thanh toán L2</option>
                                    <option value="l3_payment">Thanh toán L3</option>
                                </select>
                            </div>

                            <div class="col-12">
                                <label for="revenue_confirmation_image" class="form-label">
                                    <i class="fas fa-image text-info"></i> Ảnh xác nhận
                                </label>
                                <input type="file" class="form-control" id="revenue_confirmation_image" 
                                       accept="image/*">
                                <small class="text-muted">Tải lên ảnh xác nhận chuyển khoản (nếu có)</small>
                            </div>

                            <div class="col-12">
                                <label for="revenue_notes" class="form-label">
                                    <i class="fas fa-sticky-note text-secondary"></i> Ghi chú
                                </label>
                                <textarea class="form-control" id="revenue_notes" rows="2" 
                                          placeholder="Ghi chú thêm về giao dịch..."></textarea>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Hủy
                    </button>
                    <button type="button" class="btn btn-success" onclick="saveRevenueData()">
                        <i class="fas fa-save me-1"></i>Lưu doanh thu
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        let visitorCount = 1;
        let customerIndex = 0;
        
        // Danh sách khóa học từ PHP
        const courses = <?= json_encode($courses ?? []) ?>;
        console.log('Available courses:', courses);
        
        // Initialize with first customer row
        document.addEventListener('DOMContentLoaded', function() {
            addCustomerRow();
            updateStats();
            updateCustomerCount();
        });
        
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebar-overlay');
            
            if (sidebar.classList.contains('show')) {
                sidebar.classList.remove('show');
                overlay.style.display = 'none';
            } else {
                sidebar.classList.add('show');
                overlay.style.display = 'block';
            }
        }
        
        function changeVisitors(delta) {
            const newCount = visitorCount + delta;
            if (newCount >= 0) {
                const oldCount = visitorCount;
                visitorCount = newCount;
                document.getElementById('visitors-count').textContent = visitorCount;
                document.getElementById('total_visitors').value = visitorCount;
                
                // Add new customer row when increasing visitor count
                if (delta > 0) {
                    // If going from 0 to any number, remove empty state and add rows
                    if (oldCount === 0) {
                        removeEmptyCustomerState();
                        for (let i = 0; i < visitorCount; i++) {
                            addCustomerRow();
                        }
                    } else {
                        addCustomerRow();
                    }
                }
                // Remove last customer row when decreasing
                else if (delta < 0 && customerIndex > 0) {
                    removeLastCustomerRow();
                }
                // If visitor count becomes 0, clear all customer rows
                if (visitorCount === 0) {
                    clearAllCustomerRows();
                }
                
                updateStats();
                updateCustomerCount();
                
                // Add animation effect
                const countElement = document.getElementById('visitors-count');
                countElement.style.transform = 'scale(1.2)';
                setTimeout(() => {
                    countElement.style.transform = 'scale(1)';
                }, 200);
            }
        }
        
        function addCustomerRow() {
            const tableBody = document.getElementById('customers-table');
            const row = document.createElement('tr');
            row.setAttribute('data-index', customerIndex);
            row.style.opacity = '0';
            
            row.innerHTML = `
                <td>
                    <input type="text" class="form-control form-control-sm customer-phone-input" 
                           name="customers[${customerIndex}][phone]" 
                           placeholder="0901234567" 
                           onchange="updateStats()" required>
                </td>
                <td>
                    <input type="text" class="form-control form-control-sm customer-name-input" 
                           name="customers[${customerIndex}][full_name]" 
                           placeholder="Tên khách hàng">
                </td>
                <td class="course-notes-cell">
                    <div class="course-combo-wrapper position-relative mb-1">
                        <input type="text" 
                               class="form-control form-control-sm course-combo-input" 
                               name="customers[${customerIndex}][course_display]" 
                               placeholder="Gõ để tìm hoặc chọn khóa học..."
                               autocomplete="off"
                               data-index="${customerIndex}">
                        <input type="hidden" 
                               name="customers[${customerIndex}][course_id]" 
                               class="course-id-input">
                        <div class="course-dropdown position-absolute w-100" 
                             style="display: none; z-index: 1000; max-height: 200px; overflow-y: auto; 
                                    background: white; border: 1px solid #ced4da; border-radius: 0.375rem;
                                    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);">
                        </div>
                    </div>
                    <input type="text" class="form-control form-control-sm customer-notes-input" 
                           name="customers[${customerIndex}][notes]" 
                           placeholder="Ghi chú thêm">
                </td>
                <td class="text-center">
                    <div class="form-check d-flex justify-content-center">
                        <input type="checkbox" class="form-check-input" 
                               name="customers[${customerIndex}][registered]" 
                               value="1" onchange="updateStats()">
                    </div>
                </td>
                <td class="text-center">
                    <button type="button" 
                            class="btn btn-sm btn-outline-primary revenue-btn" 
                            data-customer-index="${customerIndex}"
                            onclick="openRevenueModal(${customerIndex})"
                            disabled>
                        <i class="fas fa-dollar-sign me-1"></i>Nhập DT
                    </button>
                    <input type="hidden" name="customers[${customerIndex}][has_revenue]" class="has-revenue-input" value="0">
                    <input type="hidden" name="customers[${customerIndex}][revenue_data]" class="revenue-data-input" value="">
                </td>
            `;
            
            tableBody.appendChild(row);
            
            // Fade in animation
            setTimeout(() => {
                row.style.transition = 'opacity 0.3s ease';
                row.style.opacity = '1';
            }, 50);
            
            // Enable revenue button when customer is registered
            const checkbox = row.querySelector('input[name*="[registered]"]');
            const revenueBtn = row.querySelector('.revenue-btn');
            const phoneInput = row.querySelector('input[name*="[phone]"]');
            const nameInput = row.querySelector('input[name*="[full_name]"]');
            
            function checkRevenueButtonState() {
                const chk = row.querySelector('input[name*="[registered]"]');
                const phone = row.querySelector('input[name*="[phone]"]');
                const name = row.querySelector('input[name*="[full_name]"]');
                const btn = row.querySelector('.revenue-btn');
                
                console.log('Checking elements:', {
                    hasCheckbox: !!chk,
                    hasPhone: !!phone,
                    hasName: !!name,
                    hasBtn: !!btn
                });
                
                if (!chk || !phone || !name || !btn) {
                    console.error('Missing elements for revenue button state check', {
                        chk: !!chk,
                        phone: !!phone,
                        name: !!name,
                        btn: !!btn
                    });
                    return;
                }
                
                const isChecked = chk.checked;
                const hasPhone = phone.value.trim().length > 0;
                const hasName = name.value.trim().length > 0;
                
                console.log('Revenue button state check:', {
                    isChecked,
                    hasPhone,
                    hasName,
                    phoneValue: phone.value,
                    nameValue: name.value
                });
                
                if (isChecked && hasPhone && hasName) {
                    btn.disabled = false;
                    console.log('✓ Revenue button ENABLED');
                } else {
                    btn.disabled = true;
                    console.log('✗ Revenue button DISABLED - reasons:', {
                        needsChecked: !isChecked,
                        needsPhone: !hasPhone,
                        needsName: !hasName
                    });
                }
            }
            
            console.log('Setting up event listeners for row:', customerIndex);
            
            checkbox.addEventListener('change', function() {
                console.log('Checkbox changed:', this.checked);
                if (!this.checked) {
                    // Clear revenue data if unchecked
                    row.querySelector('.has-revenue-input').value = '0';
                    row.querySelector('.revenue-data-input').value = '';
                    revenueBtn.innerHTML = '<i class="fas fa-dollar-sign me-1"></i>Nhập DT';
                    revenueBtn.classList.remove('btn-success');
                    revenueBtn.classList.add('btn-outline-primary');
                }
                checkRevenueButtonState();
                updateStats();
            });
            
            // Check button state when phone/name changes
            phoneInput.addEventListener('input', function() {
                console.log('Phone input changed:', this.value);
                checkRevenueButtonState();
            });
            nameInput.addEventListener('input', function() {
                console.log('Name input changed:', this.value);
                checkRevenueButtonState();
            });
            
            console.log('Event listeners attached successfully for row:', customerIndex);
            
            // Initialize course dropdown for this row
            const courseWrapper = row.querySelector('.course-combo-wrapper');
            if (courseWrapper) {
                initializeCourseCombo(courseWrapper);
            }
            
            // Initialize course combo box after DOM is ready
            setTimeout(() => {
                const courseWrapper = row.querySelector('.course-combo-wrapper');
                if (courseWrapper) {
                    initializeCourseCombo(courseWrapper);
                }
            }, 100);
            
            customerIndex++;
        }
        
        function removeLastCustomerRow() {
            const tableBody = document.getElementById('customers-table');
            const rows = tableBody.querySelectorAll('tr');
            if (rows.length > 0) {
                const lastRow = rows[rows.length - 1];
                lastRow.style.transition = 'opacity 0.3s ease';
                lastRow.style.opacity = '0';
                setTimeout(() => {
                    lastRow.remove();
                }, 300);
                customerIndex--;
            }
        }
        
        function clearAllCustomerRows() {
            const tableBody = document.getElementById('customers-table');
            const rows = tableBody.querySelectorAll('tr');
            
            // Remove all rows with animation
            rows.forEach((row, index) => {
                setTimeout(() => {
                    row.style.transition = 'opacity 0.3s ease';
                    row.style.opacity = '0';
                    setTimeout(() => {
                        row.remove();
                    }, 300);
                }, index * 100);
            });
            
            // Reset customer index
            customerIndex = 0;
            
            // Add empty state message after all rows are removed
            setTimeout(() => {
                if (visitorCount === 0) {
                    showEmptyCustomerState();
                }
            }, rows.length * 100 + 400);
        }
        
        function showEmptyCustomerState() {
            const tableBody = document.getElementById('customers-table');
            const emptyRow = document.createElement('tr');
            emptyRow.id = 'empty-customer-row';
            emptyRow.innerHTML = `
                <td colspan="5" class="text-center py-5 text-muted">
                    <i class="fas fa-users fa-3x mb-3 opacity-50"></i>
                    <h6>Không có khách hàng</h6>
                    <p class="mb-0">Tăng số lượng đến để bắt đầu nhập thông tin khách hàng</p>
                </td>
            `;
            tableBody.appendChild(emptyRow);
        }
        
        function removeEmptyCustomerState() {
            const emptyRow = document.getElementById('empty-customer-row');
            if (emptyRow) {
                emptyRow.remove();
            }
        }
        
        function clearLastCustomerRow() {
            const tableBody = document.getElementById('customers-table');
            const lastRow = tableBody.querySelector('tr:last-child');
            if (lastRow) {
                // Clear all input values in the last row
                const inputs = lastRow.querySelectorAll('input, select');
                inputs.forEach(input => {
                    if (input.type === 'checkbox') {
                        input.checked = false;
                    } else {
                        input.value = '';
                    }
                });
                
                // Reset placeholder text
                const phoneInput = lastRow.querySelector('input[name*="[phone]"]');
                const nameInput = lastRow.querySelector('input[name*="[full_name]"]');
                const courseInput = lastRow.querySelector('.course-combo-input');
                const revenueBtn = lastRow.querySelector('.revenue-btn');
                
                if (phoneInput) phoneInput.placeholder = 'VD: 0901234567';
                if (nameInput) nameInput.placeholder = 'Tên khách hàng';
                if (courseInput) courseInput.value = '';
                if (revenueBtn) {
                    revenueBtn.disabled = true;
                    revenueBtn.innerHTML = '<i class="fas fa-dollar-sign me-1"></i>Nhập DT';
                }
            }
        }
        
        function updateStats() {
            const checkboxes = document.querySelectorAll('input[name*="[registered]"]:checked');
            const registeredCount = checkboxes.length;
            
            document.getElementById('registered-count').textContent = registeredCount;
            document.getElementById('total_registered').value = registeredCount;
            
            // Get current visitor count
            const visitorCount = parseInt(document.getElementById('visitors-count').textContent);
            
            // Calculate conversion rate (if widget present)
            const rateElement = document.getElementById('conversion-rate');
            if (rateElement) {
                const conversionRate = visitorCount > 0 ? (registeredCount / visitorCount * 100).toFixed(1) : 0;
                rateElement.textContent = conversionRate + '%';
                rateElement.className = 'fs-2 fw-bold';
                if (conversionRate >= 50) {
                    rateElement.classList.add('conversion-high');
                } else if (conversionRate >= 20) {
                    rateElement.classList.add('conversion-medium');
                } else {
                    rateElement.classList.add('conversion-low');
                }
            }
            
            // Add animation to registered count
            const registeredElement = document.getElementById('registered-count');
            registeredElement.style.transform = 'scale(1.1)';
            setTimeout(() => {
                registeredElement.style.transform = 'scale(1)';
            }, 200);
            
        }
        
        // Initialize course combo box with pure HTML/JS
        function initializeCourseCombo(wrapper) {
            if (!wrapper) {
                console.error('Wrapper is null or undefined');
                return;
            }
            
            const input = wrapper.querySelector('.course-combo-input');
            const hiddenInput = wrapper.querySelector('.course-id-input');
            const dropdown = wrapper.querySelector('.course-dropdown');
            
            if (!input || !hiddenInput || !dropdown) {
                console.error('Course combo elements not found:', {input, hiddenInput, dropdown});
                return;
            }
            
            console.log('Initializing course combo with', courses.length, 'courses');
            console.log('Input element:', input);
            console.log('Dropdown element:', dropdown);
            
            // Filter and display courses based on input
            function filterCourses(searchTerm) {
                const filtered = courses.filter(course => {
                    const fullText = `${course.course_code} - ${course.course_name}`;
                    return fullText.toLowerCase().includes(searchTerm.toLowerCase());
                });
                
                displayDropdown(filtered, searchTerm);
            }
            
            // Display dropdown with options
            function displayDropdown(filteredCourses, searchTerm) {
                dropdown.innerHTML = '';
                
                // Show existing courses
                filteredCourses.forEach(course => {
                    const option = document.createElement('div');
                    option.className = 'dropdown-option p-2 border-bottom';
                    option.style.cursor = 'pointer';
                    option.innerHTML = `
                        <div style="font-weight: 500; font-size: 0.8rem;">${course.course_code}</div>
                        <div style="font-size: 0.75rem; color: #6c757d;">${course.course_name}</div>
                    `;
                    
                    option.addEventListener('click', () => {
                        input.value = `${course.course_code} - ${course.course_name}`;
                        hiddenInput.value = course.id;
                        dropdown.style.display = 'none';
                    });
                    
                    option.addEventListener('mouseenter', () => {
                        option.style.backgroundColor = '#f8f9fa';
                    });
                    
                    option.addEventListener('mouseleave', () => {
                        option.style.backgroundColor = 'white';
                    });
                    
                    dropdown.appendChild(option);
                });
                
                // Add "Create new" option if search term doesn't match exactly
                if (searchTerm && !filteredCourses.some(course => 
                    `${course.course_code} - ${course.course_name}`.toLowerCase() === searchTerm.toLowerCase())) {
                    const newOption = document.createElement('div');
                    newOption.className = 'dropdown-option p-2 text-success';
                    newOption.style.cursor = 'pointer';
                    newOption.innerHTML = `
                        <div style="font-weight: 600; font-size: 0.8rem;">
                            <i class="fas fa-plus-circle me-1"></i>Thêm khóa học: "${searchTerm}"
                        </div>
                    `;
                    
                    newOption.addEventListener('click', () => {
                        input.value = searchTerm;
                        hiddenInput.value = searchTerm; // Use text as ID for new courses
                        dropdown.style.display = 'none';
                    });
                    
                    newOption.addEventListener('mouseenter', () => {
                        newOption.style.backgroundColor = '#d1edff';
                    });
                    
                    newOption.addEventListener('mouseleave', () => {
                        newOption.style.backgroundColor = 'white';
                    });
                    
                    dropdown.appendChild(newOption);
                }
                
                // Show "No results" message
                if (dropdown.children.length === 0) {
                    const noResult = document.createElement('div');
                    noResult.className = 'p-2 text-muted';
                    noResult.style.fontSize = '0.8rem';
                    noResult.textContent = 'Không tìm thấy khóa học nào';
                    dropdown.appendChild(noResult);
                }
                
                dropdown.style.display = 'block';
            }
            
            // Input event listeners
            input.addEventListener('input', (e) => {
                const value = e.target.value;
                if (value.length > 0) {
                    filterCourses(value);
                } else {
                    hiddenInput.value = '';
                    dropdown.style.display = 'none';
                }
            });
            
            input.addEventListener('focus', () => {
                console.log('Input focused, current value:', input.value);
                console.log('Total courses available:', courses.length);
                if (input.value.length > 0) {
                    filterCourses(input.value);
                } else {
                    displayDropdown(courses, '');
                }
            });
            
            input.addEventListener('blur', (e) => {
                // Delay hiding to allow click on dropdown
                setTimeout(() => {
                    if (!dropdown.matches(':hover')) {
                        dropdown.style.display = 'none';
                    }
                }, 150);
            });
            
            // Click outside to close
            document.addEventListener('click', (e) => {
                if (!wrapper.contains(e.target)) {
                    dropdown.style.display = 'none';
                }
            });
        }
        
        function updateCustomerCount() {
            const countElement = document.getElementById('customer-count');
            const visitorCount = parseInt(document.getElementById('visitors-count').textContent);
            if (visitorCount === 0) {
                countElement.textContent = 'Không có khách hàng';
            } else {
                countElement.textContent = `${visitorCount} khách hàng`;
            }
        }
        
        function saveReport() {
            // Show loading state
            const saveBtn = document.querySelector('button[onclick="saveReport()"]');
            const originalText = saveBtn.innerHTML;
            saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Đang lưu...';
            saveBtn.disabled = true;
            
            // Validate required fields
            const reportDate = document.getElementById('report_date').value;
            const staffIdElement = document.getElementById('staff_id') || document.querySelector('input[name="staff_id"]');
            const staffId = staffIdElement ? staffIdElement.value : '';
            
            if (!reportDate || !staffId) {
                alert('Vui lòng điền đầy đủ thông tin ngày báo cáo và nhân viên phụ trách!');
                saveBtn.innerHTML = originalText;
                saveBtn.disabled = false;
                return;
            }
            
            // Collect all customer data
            const customers = [];
            const rows = document.querySelectorAll('#customers-table tr');
            
            console.log('Total rows found:', rows.length);
            
            rows.forEach(row => {
                const phone = row.querySelector('input[name*="[phone]"]').value;
                const fullName = row.querySelector('input[name*="[full_name]"]').value;
                const notes = row.querySelector('input[name*="[notes]"]').value;
                const registered = row.querySelector('input[name*="[registered]"]').checked;
                const hasRevenue = row.querySelector('.has-revenue-input').value;
                const revenueData = row.querySelector('.revenue-data-input').value;
                const courseIdInput = row.querySelector('input[name*="[course_id]"]');
                const courseId = courseIdInput ? courseIdInput.value : '';
                
                if (phone.trim()) { // Only add if phone is provided
                    const customerData = {
                        phone: phone,
                        full_name: fullName,
                        notes: notes,
                        registered: registered ? 1 : 0,
                        registration_status: registered ? 'registered' : 'not_registered',
                        has_revenue: hasRevenue || '0',
                        revenue_data: revenueData || '',
                        course_id: courseId || null,
                        status: 'new' // Default status
                    };
                    
                    console.log('Customer data:', customerData);
                    customers.push(customerData);
                }
            });
            
            if (customers.length === 0) {
                alert('Vui lòng nhập ít nhất một khách hàng với số điện thoại!');
                saveBtn.innerHTML = originalText;
                saveBtn.disabled = false;
                return;
            }
            
            // Validate: Check if any customer is registered (chốt) but has no revenue data
            const customersWithoutRevenue = [];
            customers.forEach((customer, index) => {
                if (customer.registered == 1 && customer.has_revenue != '1') {
                    customersWithoutRevenue.push({
                        index: index + 1,
                        name: customer.full_name || customer.phone
                    });
                }
            });
            
            if (customersWithoutRevenue.length > 0) {
                const customerList = customersWithoutRevenue.map(c => `• Khách hàng ${c.index}: ${c.name}`).join('\n');
                alert(
                    `❌ KHÔNG THỂ LƯU: Có ${customersWithoutRevenue.length} khách hàng đã CHỐT nhưng chưa nhập doanh thu!\n\n${customerList}\n\n` +
                    `Vui lòng nhấn nút "💲 Nhập DT" để nhập thông tin doanh thu cho các khách hàng đã chốt.`
                );
                saveBtn.innerHTML = originalText;
                saveBtn.disabled = false;
                return;
            }
            
            // Update visitor count and registered count
            const visitorCount = parseInt(document.getElementById('visitors-count').textContent);
            const registeredCheckboxes = document.querySelectorAll('input[name*="[registered]"]:checked');
            const registeredCount = registeredCheckboxes.length;
            
            document.getElementById('total_visitors').value = visitorCount;
            document.getElementById('total_registered').value = registeredCount;
            
            // Debug: Log the values being submitted
            console.log('Final data before submit:');
            console.log('Visitor count:', visitorCount);
            console.log('Registered count:', registeredCount);
            console.log('Customers:', customers);
            
            // Add customers data to form
            const form = document.getElementById('reportForm');
            const customersInput = document.createElement('input');
            customersInput.type = 'hidden';
            customersInput.name = 'customers_data';
            customersInput.value = JSON.stringify(customers);
            form.appendChild(customersInput);
            
            // Submit form
            form.submit();
        }
        
        function goBack() {
            window.location.href = '/Quan_ly_trung_tam/public/reports';
        }

        // Revenue Modal Functions
        let currentCustomerIndex = null;
        let receiptCheckTimeout = null;

        function openRevenueModal(customerIndex) {
            currentCustomerIndex = customerIndex;
            const rows = document.querySelectorAll('#customers-table tr');
            let row = null;
            
            // Find the row by checking the button's data attribute
            rows.forEach(r => {
                const btn = r.querySelector(`.revenue-btn[data-customer-index="${customerIndex}"]`);
                if (btn) {
                    row = r;
                }
            });
            
            if (!row) {
                console.error('Row not found for customer index:', customerIndex);
                return;
            }
            
            const studentName = row.querySelector('input[name*="[full_name]"]').value;
            const courseInput = row.querySelector('.course-combo-input');
            const courseId = row.querySelector('.course-id-input').value;
            
            // Get saved revenue data if exists
            const revenueDataInput = row.querySelector('.revenue-data-input');
            let existingData = null;
            
            if (revenueDataInput && revenueDataInput.value) {
                try {
                    existingData = JSON.parse(revenueDataInput.value);
                } catch (e) {
                    console.error('Error parsing revenue data:', e);
                }
            }
            
            // Set student name
            document.getElementById('revenue_student_name').value = studentName;
            document.getElementById('revenue_customer_index').value = customerIndex;
            
            // Set default date to today
            document.getElementById('revenue_payment_date').value = '<?= date('Y-m-d') ?>';
            
            // If existing data, populate form
            if (existingData) {
                document.getElementById('revenue_payment_date').value = existingData.payment_date || '';
                document.getElementById('revenue_amount').value = formatCurrency(existingData.amount || '');
                document.getElementById('revenue_receipt_code').value = existingData.receipt_code || '';
                document.getElementById('revenue_transfer_type').value = existingData.transfer_type || '';
                document.getElementById('revenue_payment_content').value = existingData.payment_content || '';
                document.getElementById('revenue_notes').value = existingData.notes || '';
                
                // Show file info if exists
                if (existingData.has_image && existingData.image_name) {
                    const fileInput = document.getElementById('revenue_confirmation_image');
                    const fileLabel = fileInput.nextElementSibling;
                    if (fileLabel && fileLabel.classList.contains('text-muted')) {
                        fileLabel.innerHTML = `<span class="text-success"><i class="fas fa-check-circle"></i> ${existingData.image_name}</span>`;
                    }
                }
            } else {
                // Clear form
                document.getElementById('revenueForm').reset();
                document.getElementById('revenue_student_name').value = studentName;
                document.getElementById('revenue_payment_date').value = '<?= date('Y-m-d') ?>';
                
                // Reset file input label
                const fileInput = document.getElementById('revenue_confirmation_image');
                const fileLabel = fileInput.nextElementSibling;
                if (fileLabel && fileLabel.classList.contains('text-success')) {
                    fileLabel.innerHTML = 'Tải lên ảnh xác nhận chuyển khoản (nếu có)';
                    fileLabel.classList.remove('text-success');
                    fileLabel.classList.add('text-muted');
                }
            }
            
            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('revenueModal'));
            modal.show();
        }

        function saveRevenueData() {
            // Validate form
            const form = document.getElementById('revenueForm');
            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }
            
            const amountInput = document.getElementById('revenue_amount');
            const amount = amountInput.value.replace(/[,\.]/g, '');
            
            if (!amount || isNaN(amount) || parseInt(amount) < 1000) {
                alert('Số tiền phải lớn hơn hoặc bằng 1,000 đ');
                return;
            }
            
            // Collect revenue data
            const revenueData = {
                student_name: document.getElementById('revenue_student_name').value,
                payment_date: document.getElementById('revenue_payment_date').value,
                amount: amount,
                receipt_code: document.getElementById('revenue_receipt_code').value,
                transfer_type: document.getElementById('revenue_transfer_type').value,
                payment_content: document.getElementById('revenue_payment_content').value,
                notes: document.getElementById('revenue_notes').value,
                has_image: false
            };
            
            // Handle file input
            const fileInput = document.getElementById('revenue_confirmation_image');
            if (fileInput.files.length > 0) {
                revenueData.has_image = true;
                revenueData.image_name = fileInput.files[0].name;
                
                // Create a hidden file input in main form with dynamic name
                const mainForm = document.getElementById('reportForm');
                const hiddenFileInput = document.createElement('input');
                hiddenFileInput.type = 'file';
                hiddenFileInput.name = `revenue_image_${currentCustomerIndex}`;
                hiddenFileInput.style.display = 'none';
                
                // Transfer the file
                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(fileInput.files[0]);
                hiddenFileInput.files = dataTransfer.files;
                
                mainForm.appendChild(hiddenFileInput);
            }
            
            // Get the customer row
            const row = document.querySelector(`tr:has([data-customer-index="${currentCustomerIndex}"])`);
            if (row) {
                // Store revenue data
                row.querySelector('.has-revenue-input').value = '1';
                row.querySelector('.revenue-data-input').value = JSON.stringify(revenueData);
                
                // Update button text
                const revenueBtn = row.querySelector('.revenue-btn');
                revenueBtn.innerHTML = '<i class="fas fa-check-circle me-1"></i>Đã nhập';
                revenueBtn.classList.remove('btn-outline-primary');
                revenueBtn.classList.add('btn-success');
            }
            
            // Close modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('revenueModal'));
            modal.hide();
        }

        // Format currency input
        document.getElementById('revenue_amount').addEventListener('input', function(e) {
            let value = e.target.value.replace(/[^\d]/g, '');
            if (value) {
                e.target.value = formatCurrency(value);
            }
        });

        function formatCurrency(value) {
            if (!value) return '';
            const num = parseInt(value.toString().replace(/[^\d]/g, ''));
            return num.toLocaleString('vi-VN');
        }

        // Check receipt code duplicate
        document.getElementById('revenue_receipt_code').addEventListener('input', function(e) {
            clearTimeout(receiptCheckTimeout);
            const receiptCode = e.target.value.trim();
            const messageEl = document.querySelector('.receipt-check-message');
            
            if (receiptCode.length < 3) {
                messageEl.textContent = '';
                e.target.classList.remove('is-invalid', 'is-valid');
                return;
            }
            
            messageEl.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang kiểm tra...';
            
            receiptCheckTimeout = setTimeout(() => {
                fetch(`/Quan_ly_trung_tam/public/revenue/check-receipt-code?receipt_code=${encodeURIComponent(receiptCode)}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.exists) {
                            messageEl.innerHTML = '<span class="text-danger"><i class="fas fa-times-circle"></i> Mã phiếu thu đã tồn tại</span>';
                            e.target.classList.add('is-invalid');
                            e.target.classList.remove('is-valid');
                        } else {
                            messageEl.innerHTML = '<span class="text-success"><i class="fas fa-check-circle"></i> Mã phiếu thu hợp lệ</span>';
                            e.target.classList.add('is-valid');
                            e.target.classList.remove('is-invalid');
                        }
                    })
                    .catch(error => {
                        console.error('Error checking receipt code:', error);
                        messageEl.textContent = '';
                    });
            }, 500);
        });

        // Date validation - không cho chọn ngày trong tương lai
        document.getElementById('revenue_payment_date').addEventListener('change', function(e) {
            const selectedDate = new Date(e.target.value);
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            
            if (selectedDate > today) {
                alert('Ngày thanh toán không được lớn hơn ngày hôm nay!');
                e.target.value = '<?= date('Y-m-d') ?>';
            }
        });
        
        // File input change handler - show filename
        document.getElementById('revenue_confirmation_image').addEventListener('change', function(e) {
            const fileLabel = this.nextElementSibling;
            if (this.files.length > 0) {
                const fileName = this.files[0].name;
                const fileSize = (this.files[0].size / 1024).toFixed(2); // KB
                if (fileLabel) {
                    fileLabel.innerHTML = `<span class="text-success"><i class="fas fa-check-circle"></i> ${fileName} (${fileSize} KB)</span>`;
                    fileLabel.classList.remove('text-muted');
                    fileLabel.classList.add('text-success');
                }
            } else {
                if (fileLabel) {
                    fileLabel.innerHTML = 'Tải lên ảnh xác nhận chuyển khoản (nếu có)';
                    fileLabel.classList.remove('text-success');
                    fileLabel.classList.add('text-muted');
                }
            }
        });
    </script>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/main.php';
useModernLayout('Tạo báo cáo đến trung tâm', $content);
?>