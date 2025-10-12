<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tạo báo cáo mới</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .stats-card {
            transition: all 0.3s ease;
        }
        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .customer-table th {
            background-color: #f8f9fa;
            font-weight: 600;
            font-size: 0.9rem;
        }
        .form-control-sm, .form-select-sm {
            font-size: 0.875rem;
        }
        .conversion-high { color: #198754 !important; }
        .conversion-medium { color: #fd7e14 !important; }
        .conversion-low { color: #dc3545 !important; }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 d-md-block bg-dark">
                <div class="position-sticky pt-3">
                    <div class="text-center text-white mb-4">
                        <h5>Quản lý trung tâm</h5>
                    </div>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link text-white" href="/Quan_ly_trung_tam/public/dashboard">
                                <i class="fas fa-tachometer-alt"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white active bg-primary" href="/Quan_ly_trung_tam/public/reports">
                                <i class="fas fa-chart-line"></i> Báo cáo đến trung tâm
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="/Quan_ly_trung_tam/public/revenue">
                                <i class="fas fa-money-bill-wave"></i> Báo cáo doanh thu
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="/Quan_ly_trung_tam/public/students">
                                <i class="fas fa-graduation-cap"></i> Học viên
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="/Quan_ly_trung_tam/public/certificates">
                                <i class="fas fa-certificate"></i> Chứng nhận
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Tạo báo cáo đến trung tâm</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <button type="button" class="btn btn-outline-primary me-2" onclick="goBack()">
                            <i class="fas fa-arrow-left"></i> Quay về trang chủ
                        </button>
                        <button type="button" class="btn btn-primary" onclick="saveReport()">
                            <i class="fas fa-save"></i> Lưu báo cáo
                        </button>
                    </div>
                </div>

                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <form method="POST" action="/Quan_ly_trung_tam/public/reports" id="reportForm">
                    <div class="row">
                        <!-- Left Column -->
                        <div class="col-md-6">
                            <!-- Date and Staff Section -->
                            <div class="row mb-3">
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
                                    <select class="form-select" id="staff_id" name="staff_id" required>
                                        <?php if (!empty($staff)): ?>
                                            <?php foreach ($staff as $s): ?>
                                                <option value="<?= $s['id'] ?>" 
                                                        <?= (isset($old_data['staff_id']) && $old_data['staff_id'] == $s['id']) ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($s['full_name']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <option value="1" selected>Nhân viên 1</option>
                                        <?php endif; ?>
                                    </select>
                                    <small class="text-muted">Tự động điền tên của bạn</small>
                                </div>
                            </div>

                            <!-- Visitor Count Section -->
                            <div class="mb-4">
                                <label class="form-label">
                                    <i class="fas fa-chart-line text-info"></i> Số liệu khách hàng
                                </label>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="card border-info stats-card">
                                            <div class="card-body text-center">
                                                <div class="d-flex align-items-center justify-content-center mb-2">
                                                    <i class="fas fa-users text-info me-2"></i>
                                                    <span>Số lượng đến</span>
                                                </div>
                                                <div class="d-flex align-items-center justify-content-center">
                                                    <button type="button" class="btn btn-outline-info btn-sm" onclick="changeVisitors(-1)">
                                                        <i class="fas fa-minus"></i>
                                                    </button>
                                                    <span class="mx-3 fs-4 fw-bold" id="visitors-count">1</span>
                                                    <span class="text-muted">người</span>
                                                    <button type="button" class="btn btn-info btn-sm ms-2" onclick="changeVisitors(1)">
                                                        <i class="fas fa-plus"></i>
                                                    </button>
                                                </div>
                                                <small class="text-muted">Tổng số khách hàng đến trong ngày</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="card border-success stats-card">
                                            <div class="card-body text-center">
                                                <div class="d-flex align-items-center justify-content-center mb-2">
                                                    <i class="fas fa-check text-success me-2"></i>
                                                    <span>Số lượng chốt</span>
                                                </div>
                                                <div class="d-flex align-items-center justify-content-center">
                                                    <i class="fas fa-check-circle text-success me-2"></i>
                                                    <span class="fs-3 fw-bold text-success" id="registered-count">0</span>
                                                    <small class="text-muted ms-2">Tổng khách chốt</small>
                                                </div>
                                                <small class="text-muted">Tự động tính khi khách hàng chốt</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Right Column - Customer Details -->
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <div class="row align-items-center">
                                        <div class="col">
                                            <h6 class="mb-0">
                                                <i class="fas fa-info-circle text-primary"></i>
                                                Nhập thông tin từng khách. <strong>Chi SĐT là bắt buộc.</strong> Họ tên có thể là <em>Họ tên/Zalo</em>. Hệ thống tự tính số chốt theo công tác từng dòng.
                                            </h6>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-sm mb-0 customer-table">
                                            <thead>
                                                <tr>
                                                    <th style="width: 25%">SĐT *</th>
                                                    <th style="width: 25%">Họ tên/Zalo</th>
                                                    <th style="width: 25%">Khóa tham khảo/ Ghi chú</th>
                                                    <th style="width: 10%">Chốt</th>
                                                    <th style="width: 15%">Hình thức đóng HP</th>
                                                </tr>
                                            </thead>
                                            <tbody id="customers-table">
                                                <!-- Customer rows will be added here -->
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="p-3 border-top bg-light">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="text-muted">% Tỷ lệ chốt đơn:</span>
                                            <span class="fs-4 fw-bold text-danger" id="conversion-rate">0.0%</span>
                                        </div>
                                    </div>
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
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let visitorCount = 1;
        let customerIndex = 0;
        
        // Initialize with first customer row
        document.addEventListener('DOMContentLoaded', function() {
            addCustomerRow();
            updateStats();
        });
        
        function changeVisitors(delta) {
            const newCount = visitorCount + delta;
            if (newCount >= 1) {
                visitorCount = newCount;
                document.getElementById('visitors-count').textContent = visitorCount;
                document.getElementById('total_visitors').value = visitorCount;
                
                // Add new customer row when increasing visitor count
                if (delta > 0) {
                    addCustomerRow();
                }
                
                updateStats();
            }
        }
        
        function addCustomerRow() {
            const tableBody = document.getElementById('customers-table');
            const row = document.createElement('tr');
            row.setAttribute('data-index', customerIndex);
            
            row.innerHTML = `
                <td>
                    <input type="text" class="form-control form-control-sm" 
                           name="customers[${customerIndex}][phone]" 
                           placeholder="VD: 0901234567" 
                           onchange="updateStats()" required>
                </td>
                <td>
                    <input type="text" class="form-control form-control-sm" 
                           name="customers[${customerIndex}][full_name]" 
                           placeholder="Tên khách hàng !">
                </td>
                <td>
                    <input type="text" class="form-control form-control-sm" 
                           name="customers[${customerIndex}][notes]" 
                           placeholder="VD: FE-2025 hoặc ghi chú">
                </td>
                <td class="text-center">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" 
                               name="customers[${customerIndex}][registered]" 
                               value="1" onchange="updateStats()">
                    </div>
                </td>
                <td>
                    <select class="form-select form-select-sm" 
                            name="customers[${customerIndex}][payment_method]" 
                            disabled>
                        <option value="">-- Chọn --</option>
                        <option value="cash">Tiền mặt</option>
                        <option value="transfer">Chuyển khoản</option>
                        <option value="installment">Trả góp</option>
                    </select>
                </td>
            `;
            
            tableBody.appendChild(row);
            
            // Enable payment method when customer is registered
            const checkbox = row.querySelector('input[type="checkbox"]');
            const paymentSelect = row.querySelector('select');
            
            checkbox.addEventListener('change', function() {
                paymentSelect.disabled = !this.checked;
                if (!this.checked) {
                    paymentSelect.value = '';
                }
                updateStats();
            });
            
            customerIndex++;
        }
        
        function updateStats() {
            const checkboxes = document.querySelectorAll('input[name*="[registered]"]:checked');
            const registeredCount = checkboxes.length;
            
            document.getElementById('registered-count').textContent = registeredCount;
            document.getElementById('total_registered').value = registeredCount;
            
            // Calculate conversion rate
            const conversionRate = visitorCount > 0 ? (registeredCount / visitorCount * 100).toFixed(1) : 0;
            document.getElementById('conversion-rate').textContent = conversionRate + '%';
            
            // Update conversion rate color
            const rateElement = document.getElementById('conversion-rate');
            rateElement.className = 'fs-4 fw-bold';
            if (conversionRate >= 50) {
                rateElement.classList.add('conversion-high');
            } else if (conversionRate >= 20) {
                rateElement.classList.add('conversion-medium');
            } else {
                rateElement.classList.add('conversion-low');
            }
        }
        
        function saveReport() {
            // Validate required fields
            const reportDate = document.getElementById('report_date').value;
            const staffId = document.getElementById('staff_id').value;
            
            if (!reportDate || !staffId) {
                alert('Vui lòng điền đầy đủ thông tin ngày báo cáo và nhân viên phụ trách!');
                return;
            }
            
            // Collect all customer data
            const customers = [];
            const rows = document.querySelectorAll('#customers-table tr');
            
            rows.forEach(row => {
                const phone = row.querySelector('input[name*="[phone]"]').value;
                const fullName = row.querySelector('input[name*="[full_name]"]').value;
                const notes = row.querySelector('input[name*="[notes]"]').value;
                const registered = row.querySelector('input[name*="[registered]"]').checked;
                const paymentMethod = row.querySelector('select[name*="[payment_method]"]').value;
                
                if (phone.trim()) { // Only add if phone is provided
                    customers.push({
                        phone: phone,
                        full_name: fullName,
                        notes: notes,
                        registered: registered ? 1 : 0,
                        registration_status: registered ? 'registered' : 'not_registered',
                        payment_method: paymentMethod || null,
                        status: 'new' // Default status
                    });
                }
            });
            
            if (customers.length === 0) {
                alert('Vui lòng nhập ít nhất một khách hàng với số điện thoại!');
                return;
            }
            
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
        
        // Auto-resize table on window resize
        window.addEventListener('resize', function() {
            // Optional: Add responsive behavior
        });
    </script>
</body>
</html>