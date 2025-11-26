<?php
// Start output buffering
ob_start();
?>
    <style>
        :root {
            --primary-color: #4f46e5;
            --secondary-color: #6366f1;
            --success-color: #10b981;
            --warning-color: #f59e0b;
            --danger-color: #ef4444;
            --info-color: #06b6d4;
            --dark-color: #1f2937;
            --light-color: #f8fafc;
            --border-color: #e5e7eb;
            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1);
            --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1);
            --border-radius: 0.75rem;
        }

        * {
            font-family: 'Inter', sans-serif;
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }

        .sidebar {
            background: linear-gradient(180deg, var(--dark-color) 0%, #374151 100%);
            min-height: 100vh;
            box-shadow: var(--shadow-lg);
            border-radius: 0 var(--border-radius) var(--border-radius) 0;
        }

        .sidebar .nav-link {
            color: #d1d5db;
            padding: 0.75rem 1.25rem;
            margin: 0.25rem 0;
            border-radius: var(--border-radius);
            transition: all 0.3s ease;
        }

        .sidebar .nav-link:hover {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            transform: translateX(5px);
        }

        .sidebar .nav-link.active {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            box-shadow: var(--shadow-md);
        }

        .main-content {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-lg);
            margin: 1rem;
            overflow: hidden;
        }

        .page-header {
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
            border-bottom: 1px solid var(--border-color);
            padding: 2rem;
        }

        .stats-card {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-md);
            transition: all 0.3s ease;
            border: 1px solid var(--border-color);
            overflow: hidden;
        }

        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }

        .stats-card .card-body {
            padding: 1.5rem;
        }

        .counter-btn {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid;
            transition: all 0.3s ease;
        }

        .counter-btn:hover {
            transform: scale(1.1);
        }

        .customer-table-container {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-md);
            overflow: hidden;
        }

        .customer-table-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 0.75rem;
            padding: 1rem 1.5rem;
            border-bottom: 1px solid var(--border-color);
            background-color: #f8fafc;
        }

        .customer-table-header h6 {
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--dark-color);
        }

        .customer-table-header small {
            color: #64748b;
        }

        .customer-table {
            margin: 0;
        }

        .customer-table th {
            background: var(--light-color);
            border: none;
            padding: 1rem 0.75rem;
            font-weight: 600;
            font-size: 0.875rem;
            color: var(--dark-color);
        }

        .customer-table td {
            padding: 0.75rem;
            border: none;
            border-bottom: 1px solid #f1f5f9;
        }

        .customer-table tr:hover {
            background: #f8fafc;
        }

        .form-control, .form-select {
            border: 1px solid var(--border-color);
            border-radius: 0.5rem;
            padding: 0.75rem;
            transition: all 0.3s ease;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }

        .form-control-sm, .form-select-sm {
            padding: 0.5rem;
            font-size: 0.875rem;
        }

        .btn {
            border-radius: 0.5rem;
            padding: 0.75rem 1.5rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border: none;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .conversion-high { color: var(--success-color) !important; }
        .conversion-medium { color: var(--warning-color) !important; }
        .conversion-low { color: var(--danger-color) !important; }

        .form-label {
            font-weight: 600;
            color: var(--dark-color);
            margin-bottom: 0.5rem;
        }

        .form-label i {
            margin-right: 0.5rem;
        }

        /* Mobile Responsiveness */
        @media (max-width: 768px) {
            .sidebar {
                position: fixed;
                top: 0;
                left: -100%;
                width: 280px;
                z-index: 1050;
                transition: left 0.3s ease;
            }

            .sidebar.show {
                left: 0;
            }

            .main-content {
                margin: 0.5rem;
            }

            .page-header {
                padding: 1rem;
            }

            .page-header h1 {
                font-size: 1.5rem;
            }

            .stats-card .card-body {
                padding: 1rem;
            }

            .counter-btn {
                width: 35px;
                height: 35px;
            }

            .table-responsive {
                border-radius: 0.5rem;
            }

            .customer-table th,
            .customer-table td {
                padding: 0.5rem 0.25rem;
                font-size: 0.8rem;
            }

            .btn-toolbar {
                flex-direction: column;
                gap: 0.5rem;
            }

            .btn-toolbar .btn {
                width: 100%;
            }
        }

        @media (max-width: 576px) {
            .form-control-sm, .form-select-sm {
                font-size: 0.8rem;
                padding: 0.4rem;
            }

            .customer-table th,
            .customer-table td {
                padding: 0.4rem 0.2rem;
                font-size: 0.75rem;
            }

            .stats-card .fs-4 {
                font-size: 1.5rem !important;
            }

            .stats-card .fs-3 {
                font-size: 1.8rem !important;
            }
        }

        /* Animation for better UX */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .fade-in-up {
            animation: fadeInUp 0.6s ease-out;
        }

        /* Loading states */
        .loading {
            position: relative;
            pointer-events: none;
        }

        .loading::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.8);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Scrollbar customization */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        ::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #a1a1a1;
        }

        /* Course Selection with Search */
        .course-select {
            width: 100% !important;
        }
        
        /* Select2 Bootstrap styling */
        .select2-container--default .select2-selection--single {
            height: 31px;
            border: 1px solid #ced4da;
            border-radius: 0.375rem;
            font-size: 0.875rem;
        }

        /* Customer Table Optimizations */
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

        .customer-phone-input {
            font-size: 0.8rem;
        }

        .customer-name-input {
            font-size: 0.85rem;
        }

        .customer-notes-input {
            font-size: 0.8rem;
        }

        .customer-payment-select {
            font-size: 0.8rem;
        }

        .course-notes-cell {
            position: relative;
            padding: 0.375rem !important;
        }

        .course-notes-cell .course-select {
            margin-bottom: 4px !important;
        }

        /* Select2 container adjustments for course selection */
        .course-notes-cell .select2-container {
            font-size: 0.8rem;
        }

        .course-notes-cell .select2-selection__rendered {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            padding-right: 20px;
        }

        /* Editable combo box styling */
        .select2-container--default .select2-search--inline .select2-search__field {
            font-size: 0.8rem;
            border: none !important;
            outline: none !important;
            margin: 0;
            padding: 2px 4px;
            background: transparent !important;
            box-shadow: none !important;
        }

        .select2-container--default .select2-results__option--highlighted {
            background-color: #0d6efd !important;
            color: white !important;
        }

        .select2-container--default .select2-results__option {
            padding: 8px 12px;
            font-size: 0.8rem;
            line-height: 1.4;
            border-bottom: 1px solid #f1f1f1;
        }

        .select2-container--default .select2-results__option:last-child {
            border-bottom: none;
        }

        /* New tag styling */
        .select2-results__option .text-success {
            font-weight: 600;
        }

        /* Placeholder styling for combo box */
        .select2-container--default .select2-selection--single .select2-selection__placeholder {
            color: #6c757d;
            font-style: italic;
            font-size: 0.8rem;
        }

        /* Search field in dropdown */
        .select2-container--default .select2-search--dropdown .select2-search__field {
            border: 1px solid #ced4da;
            border-radius: 0.375rem;
            font-size: 0.8rem;
            padding: 6px 10px;
            width: 100% !important;
        }

        /* Dropdown container */
        .select2-dropdown {
            border: 1px solid #ced4da;
            border-radius: 0.375rem;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        }

        /* Results container - allow wrapping for long text */
        .select2-results__options {
            max-height: 200px;
            overflow-y: auto;
        }

        .select2-results__option div {
            max-width: 100%;
            overflow: visible;
        }

        /* Make selection input editable */
        .select2-container--default.select2-container--open .select2-selection--single {
            border-color: #86b7fe;
            outline: 0;
            box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            padding-left: 8px;
            padding-right: 20px;
        }

        /* Custom Course Combo Box Styling */
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
        }

        .dropdown-option:last-child {
            border-bottom: none;
        }

        .dropdown-option:hover {
            background-color: #f8f9fa;
        }

        .course-dropdown::-webkit-scrollbar {
            width: 6px;
        }

        .course-dropdown::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        .course-dropdown::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 3px;
        }

        .course-dropdown::-webkit-scrollbar-thumb:hover {
            background: #a1a1a1;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .customer-table th, .customer-table td {
                font-size: 0.75rem;
            }
            
            .customer-phone-input,
            .customer-name-input,
            .customer-notes-input,
            .customer-payment-select {
                font-size: 0.7rem;
            }
        }
        
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 29px;
            padding-left: 8px;
            color: #495057;
        }
        
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 29px;
        }
        
        .select2-dropdown {
            border: 1px solid #ced4da;
            border-radius: 0.375rem;
        }
        
        .select2-container--default .select2-search--dropdown .select2-search__field {
            border: 1px solid #ced4da;
            border-radius: 0.375rem;
            font-size: 0.875rem;
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

                        <form method="POST" action="/Quan_ly_trung_tam/public/reports" id="reportForm">
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
                                                        <th style="width: 120px">Hình thức đóng HP</th>
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
                <td>
                    <select class="form-select form-select-sm bg-light customer-payment-select" 
                            name="customers[${customerIndex}][payment_method]"
                            disabled>
                        <option value="">-- Chọn --</option>
                        <option value="transfer">Chuyển khoản</option>
                        <option value="cash">Tiền mặt</option>
                    </select>
                </td>
            `;
            
            tableBody.appendChild(row);
            
            // Fade in animation
            setTimeout(() => {
                row.style.transition = 'opacity 0.3s ease';
                row.style.opacity = '1';
            }, 50);
            
            // Enable payment method when customer is registered
            const checkbox = row.querySelector('input[name*="[registered]"]');
            const paymentSelect = row.querySelector('select[name*="[payment_method]"]');
            
            checkbox.addEventListener('change', function() {
                paymentSelect.disabled = !this.checked;
                if (!this.checked) {
                    paymentSelect.value = '';
                    paymentSelect.classList.remove('border-danger');
                    paymentSelect.classList.add('bg-light');
                } else {
                    paymentSelect.classList.remove('bg-light');
                }
                updateStats();
            });
            
            // Add validation for payment method when it changes
            paymentSelect.addEventListener('change', function() {
                const checkbox = row.querySelector('input[type="checkbox"]');
                if (checkbox.checked && this.value === '') {
                    this.classList.add('border-danger');
                } else {
                    this.classList.remove('border-danger');
                }
            });
            
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
                const courseSelect = lastRow.querySelector('select[name*="[course_id]"]');
                const paymentSelect = lastRow.querySelector('select[name*="[payment_method]"]');
                
                if (phoneInput) phoneInput.placeholder = 'VD: 0901234567';
                if (nameInput) nameInput.placeholder = 'Tên khách hàng';
                if (courseSelect) courseSelect.selectedIndex = 0;
                if (paymentSelect) paymentSelect.selectedIndex = 0;
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
            
            // Update payment method dropdowns based on registration status
            updatePaymentMethodDropdowns();
            
            // Validate payment methods for all registered customers
            validatePaymentMethods();
        }
        
        function updatePaymentMethodDropdowns() {
            const rows = document.querySelectorAll('#customers-table tr');
            
            rows.forEach(row => {
                const checkbox = row.querySelector('input[name*="[registered]"]');
                const paymentSelect = row.querySelector('select[name*="[payment_method]"]');
                
                if (checkbox && paymentSelect) {
                    if (checkbox.checked) {
                        // Enable dropdown when registered
                        paymentSelect.disabled = false;
                        paymentSelect.classList.remove('bg-light');
                    } else {
                        // Disable dropdown when not registered
                        paymentSelect.disabled = true;
                        paymentSelect.value = '';
                        paymentSelect.classList.add('bg-light');
                        paymentSelect.classList.remove('border-danger');
                    }
                }
            });
        }

        function validatePaymentMethods() {
            const rows = document.querySelectorAll('#customers-table tr');
            
            rows.forEach(row => {
                const checkbox = row.querySelector('input[name*="[registered]"]');
                const paymentSelect = row.querySelector('select[name*="[payment_method]"]');
                
                if (checkbox && paymentSelect && checkbox.checked) {
                    if (paymentSelect.value === '' || paymentSelect.value === null) {
                        paymentSelect.classList.add('border-danger');
                        paymentSelect.style.borderWidth = '2px';
                    } else {
                        paymentSelect.classList.remove('border-danger');
                        paymentSelect.style.borderWidth = '';
                    }
                }
            });
        }
        
        // Initialize course combo box with pure HTML/JS
        function initializeCourseCombo(wrapper) {
            const input = wrapper.querySelector('.course-combo-input');
            const hiddenInput = wrapper.querySelector('.course-id-input');
            const dropdown = wrapper.querySelector('.course-dropdown');
            
            console.log('Initializing course combo with', courses.length, 'courses');
            
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
                const paymentMethod = row.querySelector('select[name*="[payment_method]"]').value;
                const courseIdInput = row.querySelector('input[name*="[course_id]"]');
                const courseId = courseIdInput ? courseIdInput.value : '';
                
                if (phone.trim()) { // Only add if phone is provided
                    const customerData = {
                        phone: phone,
                        full_name: fullName,
                        notes: notes,
                        registered: registered ? 1 : 0,
                        registration_status: registered ? 'registered' : 'not_registered',
                        payment_method: paymentMethod || null,
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
            
            // Validate payment method for registered customers
            let validationError = false;
            let errorMessage = '';
            
            customers.forEach((customer, index) => {
                if (customer.registered === 1 && (!customer.payment_method || customer.payment_method === '')) {
                    validationError = true;
                    errorMessage = `Khách hàng "${customer.full_name || customer.phone}" đã chốt nhưng chưa chọn hình thức đóng học phí!`;
                    return;
                }
            });
            
            if (validationError) {
                alert(errorMessage);
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
    </script>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/main.php';
useModernLayout('Tạo báo cáo đến trung tâm', $content);
?>