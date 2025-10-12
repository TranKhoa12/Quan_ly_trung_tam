<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tạo báo cáo mới</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
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
                                        <option value="">Nhân viên 1</option>
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
                                        <div class="card border-info">
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
                                        <div class="card border-success">
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
                                        <table class="table table-sm mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th style="width: 30%">SĐT *</th>
                                                    <th style="width: 35%">Họ tên/Zalo</th>
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
                </form>

                    <div class="row mt-3">
                        <div class="col-12">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save"></i> Lưu báo cáo
                            </button>
                            <a href="/Quan_ly_trung_tam/public/reports" class="btn btn-secondary ms-2">
                                <i class="fas fa-times"></i> Hủy
                            </a>
                        </div>
                    </div>
                </form>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let customerIndex = 0;
        
        function addCustomer() {
            const container = document.getElementById('customers-container');
            const customerHtml = `
                <div class="customer-item border rounded p-3 mb-3" data-index="${customerIndex}">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <strong>Khách hàng #${customerIndex + 1}</strong>
                        <button type="button" class="btn btn-sm btn-danger" onclick="removeCustomer(${customerIndex})">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <label class="form-label">Số điện thoại</label>
                            <input type="text" class="form-control" name="customers[${customerIndex}][phone]" placeholder="Nhập SĐT">
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="form-label">Họ tên</label>
                            <input type="text" class="form-control" name="customers[${customerIndex}][full_name]" placeholder="Nhập họ tên">
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="form-label">Tình trạng</label>
                            <select class="form-select" name="customers[${customerIndex}][status]">
                                <option value="new">Mới đến</option>
                                <option value="returning">Đã đến trước đó</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="form-label">Khóa học tham khảo</label>
                            <select class="form-select" name="customers[${customerIndex}][course_id]">
                                <option value="">Chọn khóa học</option>
                                <?php if (!empty($courses)): ?>
                                    <?php foreach ($courses as $course): ?>
                                        <option value="<?= $course['id'] ?>"><?= htmlspecialchars($course['course_name']) ?></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="form-label">Trạng thái đăng ký</label>
                            <select class="form-select" name="customers[${customerIndex}][registration_status]">
                                <option value="not_registered">Không đăng ký</option>
                                <option value="registered">Đăng ký</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="form-label">Hình thức thanh toán</label>
                            <select class="form-select" name="customers[${customerIndex}][payment_method]">
                                <option value="">Chọn hình thức</option>
                                <option value="cash">Tiền mặt</option>
                                <option value="transfer">Chuyển khoản</option>
                            </select>
                        </div>
                        <div class="col-12 mb-2">
                            <label class="form-label">Ghi chú</label>
                            <textarea class="form-control" name="customers[${customerIndex}][notes]" rows="2"></textarea>
                        </div>
                    </div>
                </div>
            `;
            
            container.insertAdjacentHTML('beforeend', customerHtml);
            customerIndex++;
        }
        
        function removeCustomer(index) {
            const customerItem = document.querySelector(`[data-index="${index}"]`);
            if (customerItem) {
                customerItem.remove();
            }
        }
        
        // Add one customer by default
        addCustomer();
    </script>
</body>
</html>