<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tạo báo cáo doanh thu</title>
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
                            <a class="nav-link text-white" href="/Quan_ly_trung_tam/public/reports">
                                <i class="fas fa-chart-line"></i> Báo cáo đến trung tâm
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white active bg-primary" href="/Quan_ly_trung_tam/public/revenue">
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
                    <h1 class="h2">Tạo báo cáo doanh thu</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="/Quan_ly_trung_tam/public/revenue" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Quay lại
                        </a>
                    </div>
                </div>

                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <form method="POST" action="/Quan_ly_trung_tam/public/revenue" enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">Thông tin thanh toán</h5>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label for="payment_date" class="form-label">Ngày đóng học phí *</label>
                                        <input type="date" class="form-control" id="payment_date" name="payment_date" 
                                               value="<?= $old_data['payment_date'] ?? date('Y-m-d') ?>" required>
                                    </div>

                                    <div class="mb-3">
                                        <label for="transfer_type" class="form-label">Loại chuyển khoản *</label>
                                        <select class="form-select" id="transfer_type" name="transfer_type" required>
                                            <option value="">Chọn loại chuyển khoản</option>
                                            <option value="cash" <?= (isset($old_data['transfer_type']) && $old_data['transfer_type'] == 'cash') ? 'selected' : '' ?>>
                                                Tiền mặt
                                            </option>
                                            <option value="account_co_nhi" <?= (isset($old_data['transfer_type']) && $old_data['transfer_type'] == 'account_co_nhi') ? 'selected' : '' ?>>
                                                Tài khoản Cô Nhi
                                            </option>
                                            <option value="account_thay_hien" <?= (isset($old_data['transfer_type']) && $old_data['transfer_type'] == 'account_thay_hien') ? 'selected' : '' ?>>
                                                Tài khoản Thầy Hiến
                                            </option>
                                            <option value="account_company" <?= (isset($old_data['transfer_type']) && $old_data['transfer_type'] == 'account_company') ? 'selected' : '' ?>>
                                                Tài khoản Công ty
                                            </option>
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label for="confirmation_image" class="form-label">Ảnh xác nhận</label>
                                        <input type="file" class="form-control" id="confirmation_image" name="confirmation_image" 
                                               accept="image/*,.pdf">
                                        <div class="form-text">Chấp nhận file: JPG, PNG, PDF. Tối đa 5MB.</div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="receipt_code" class="form-label">Mã phiếu thu</label>
                                        <input type="text" class="form-control" id="receipt_code" name="receipt_code" 
                                               value="<?= $old_data['receipt_code'] ?? '' ?>" 
                                               placeholder="Nhập mã phiếu thu">
                                    </div>

                                    <div class="mb-3">
                                        <label for="amount" class="form-label">Số tiền *</label>
                                        <div class="input-group">
                                            <input type="number" class="form-control" id="amount" name="amount" 
                                                   value="<?= $old_data['amount'] ?? '' ?>" min="0" step="1000" required 
                                                   placeholder="Nhập số tiền">
                                            <span class="input-group-text">VNĐ</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">Thông tin học viên</h5>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label for="student_name" class="form-label">Họ tên học viên *</label>
                                        <input type="text" class="form-control" id="student_name" name="student_name" 
                                               value="<?= $old_data['student_name'] ?? '' ?>" required 
                                               placeholder="Nhập họ tên học viên">
                                    </div>

                                    <div class="mb-3">
                                        <label for="course_id" class="form-label">Khóa học</label>
                                        <select class="form-select" id="course_id" name="course_id">
                                            <option value="">Chọn khóa học</option>
                                            <?php if (!empty($courses)): ?>
                                                <?php foreach ($courses as $course): ?>
                                                    <option value="<?= $course['id'] ?>" 
                                                            <?= (isset($old_data['course_id']) && $old_data['course_id'] == $course['id']) ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($course['course_name']) ?> - <?= number_format($course['price']) ?> VNĐ
                                                    </option>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label for="payment_content" class="form-label">Nội dung chuyển khoản *</label>
                                        <select class="form-select" id="payment_content" name="payment_content" required>
                                            <option value="">Chọn nội dung thanh toán</option>
                                            <option value="full_payment" <?= (isset($old_data['payment_content']) && $old_data['payment_content'] == 'full_payment') ? 'selected' : '' ?>>
                                                Thanh toán đủ
                                            </option>
                                            <option value="deposit" <?= (isset($old_data['payment_content']) && $old_data['payment_content'] == 'deposit') ? 'selected' : '' ?>>
                                                Cọc học phí
                                            </option>
                                            <option value="full_payment_after_deposit" <?= (isset($old_data['payment_content']) && $old_data['payment_content'] == 'full_payment_after_deposit') ? 'selected' : '' ?>>
                                                Thanh toán đủ (đã cọc)
                                            </option>
                                            <option value="accounting_deposit" <?= (isset($old_data['payment_content']) && $old_data['payment_content'] == 'accounting_deposit') ? 'selected' : '' ?>>
                                                Cọc học phí (kế toán)
                                            </option>
                                            <option value="l1_payment" <?= (isset($old_data['payment_content']) && $old_data['payment_content'] == 'l1_payment') ? 'selected' : '' ?>>
                                                Thanh toán L1
                                            </option>
                                            <option value="l2_payment" <?= (isset($old_data['payment_content']) && $old_data['payment_content'] == 'l2_payment') ? 'selected' : '' ?>>
                                                Thanh toán L2
                                            </option>
                                            <option value="l3_payment" <?= (isset($old_data['payment_content']) && $old_data['payment_content'] == 'l3_payment') ? 'selected' : '' ?>>
                                                Thanh toán L3
                                            </option>
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label for="staff_id" class="form-label">Nhân viên xử lý *</label>
                                        <select class="form-select" id="staff_id" name="staff_id" required>
                                            <option value="">Chọn nhân viên</option>
                                            <?php if (!empty($staff)): ?>
                                                <?php foreach ($staff as $s): ?>
                                                    <option value="<?= $s['id'] ?>" 
                                                            <?= (isset($old_data['staff_id']) && $old_data['staff_id'] == $s['id']) ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($s['full_name']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <option value="1">Test Staff</option>
                                            <?php endif; ?>
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label for="notes" class="form-label">Ghi chú</label>
                                        <textarea class="form-control" id="notes" name="notes" rows="3" 
                                                  placeholder="Nhập ghi chú (nếu có)"><?= $old_data['notes'] ?? '' ?></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-12">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save"></i> Lưu báo cáo doanh thu
                            </button>
                            <a href="/Quan_ly_trung_tam/public/revenue" class="btn btn-secondary ms-2">
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
        // Format number input
        document.getElementById('amount').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            e.target.value = value;
        });

        // Auto-select course price when course is selected
        document.getElementById('course_id').addEventListener('change', function(e) {
            const selectedOption = e.target.options[e.target.selectedIndex];
            if (selectedOption.value) {
                // Extract price from option text (format: "Course Name - 1,000,000 VNĐ")
                const text = selectedOption.text;
                const priceMatch = text.match(/[\d,]+(?=\s*VN)/);
                if (priceMatch) {
                    const price = priceMatch[0].replace(/,/g, '');
                    const amountInput = document.getElementById('amount');
                    if (!amountInput.value) {
                        amountInput.value = price;
                    }
                }
            }
        });

        // Validate form before submit
        document.querySelector('form').addEventListener('submit', function(e) {
            const requiredFields = ['payment_date', 'transfer_type', 'amount', 'student_name', 'payment_content', 'staff_id'];
            let isValid = true;

            requiredFields.forEach(function(fieldName) {
                const field = document.getElementById(fieldName);
                if (!field.value.trim()) {
                    isValid = false;
                    field.classList.add('is-invalid');
                } else {
                    field.classList.remove('is-invalid');
                }
            });

            if (!isValid) {
                e.preventDefault();
                alert('Vui lòng điền đầy đủ thông tin bắt buộc (*)');
                return false;
            }

            const amount = document.getElementById('amount').value;
            if (amount && parseInt(amount) < 0) {
                e.preventDefault();
                alert('Số tiền phải lớn hơn 0');
                return false;
            }
        });
    </script>
</body>
</html>