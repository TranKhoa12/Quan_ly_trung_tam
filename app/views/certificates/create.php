<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yêu cầu cấp chứng nhận</title>
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
                            <a class="nav-link text-white active bg-primary" href="/Quan_ly_trung_tam/public/certificates">
                                <i class="fas fa-certificate"></i> Chứng nhận
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Yêu cầu cấp chứng nhận</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="/Quan_ly_trung_tam/public/certificates" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Quay lại
                        </a>
                    </div>
                </div>

                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <form method="POST" action="/Quan_ly_trung_tam/public/certificates">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Thông tin yêu cầu chứng nhận</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="student_name" class="form-label">Tên học viên *</label>
                                        <input type="text" class="form-control" id="student_name" name="student_name" 
                                               value="<?= $old_data['student_name'] ?? '' ?>" required 
                                               placeholder="Nhập họ tên học viên">
                                    </div>

                                    <div class="mb-3">
                                        <label for="username" class="form-label">Tài khoản đăng nhập</label>
                                        <input type="text" class="form-control" id="username" name="username" 
                                               value="<?= $old_data['username'] ?? '' ?>" 
                                               placeholder="Nhập tài khoản đăng nhập">
                                    </div>

                                    <div class="mb-3">
                                        <label for="phone" class="form-label">Số điện thoại *</label>
                                        <input type="text" class="form-control" id="phone" name="phone" 
                                               value="<?= $old_data['phone'] ?? '' ?>" required 
                                               placeholder="Nhập số điện thoại">
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="subject" class="form-label">Bộ môn học *</label>
                                        <input type="text" class="form-control" id="subject" name="subject" 
                                               value="<?= $old_data['subject'] ?? '' ?>" required 
                                               placeholder="Nhập tên bộ môn học">
                                    </div>

                                    <div class="mb-3">
                                        <label for="receive_status" class="form-label">Tình trạng học viên nhận</label>
                                        <select class="form-select" id="receive_status" name="receive_status">
                                            <option value="not_received" <?= (isset($old_data['receive_status']) && $old_data['receive_status'] == 'not_received') ? 'selected' : 'selected' ?>>
                                                Chưa nhận
                                            </option>
                                            <option value="received" <?= (isset($old_data['receive_status']) && $old_data['receive_status'] == 'received') ? 'selected' : '' ?>>
                                                Đã nhận
                                            </option>
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label for="notes" class="form-label">Ghi chú</label>
                                        <textarea class="form-control" id="notes" name="notes" rows="4" 
                                                  placeholder="Nhập ghi chú (nếu có)"><?= $old_data['notes'] ?? '' ?></textarea>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-12">
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle"></i>
                                        <strong>Lưu ý:</strong> Yêu cầu sẽ được gửi với trạng thái "Đang đợi" và cần được admin phê duyệt.
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-12">
                                    <button type="submit" class="btn btn-success">
                                        <i class="fas fa-paper-plane"></i> Gửi yêu cầu
                                    </button>
                                    <a href="/Quan_ly_trung_tam/public/certificates" class="btn btn-secondary ms-2">
                                        <i class="fas fa-times"></i> Hủy
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-format phone number
        document.getElementById('phone').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 10) {
                value = value.slice(0, 10);
            }
            e.target.value = value;
        });

        // Validate form before submit
        document.querySelector('form').addEventListener('submit', function(e) {
            const studentName = document.getElementById('student_name').value.trim();
            const phone = document.getElementById('phone').value.trim();
            const subject = document.getElementById('subject').value.trim();

            if (!studentName || !phone || !subject) {
                e.preventDefault();
                alert('Vui lòng điền đầy đủ thông tin bắt buộc (*)');
                return false;
            }

            if (phone.length < 10) {
                e.preventDefault();
                alert('Số điện thoại phải có ít nhất 10 chữ số');
                return false;
            }
        });
    </script>
</body>
</html>