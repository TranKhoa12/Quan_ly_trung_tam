<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thêm học viên hoàn thành</title>
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
                            <a class="nav-link text-white active bg-primary" href="/Quan_ly_trung_tam/public/students">
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
                    <h1 class="h2">Thêm học viên hoàn thành khóa học</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="/Quan_ly_trung_tam/public/students" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Quay lại
                        </a>
                    </div>
                </div>

                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <form method="POST" action="/Quan_ly_trung_tam/public/students" enctype="multipart/form-data">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Thông tin học viên</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="full_name" class="form-label">Họ tên học viên *</label>
                                        <input type="text" class="form-control" id="full_name" name="full_name" 
                                               value="<?= $old_data['full_name'] ?? '' ?>" required>
                                    </div>

                                    <div class="mb-3">
                                        <label for="phone" class="form-label">Số điện thoại</label>
                                        <input type="text" class="form-control" id="phone" name="phone" 
                                               value="<?= $old_data['phone'] ?? '' ?>">
                                    </div>

                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email</label>
                                        <input type="email" class="form-control" id="email" name="email" 
                                               value="<?= $old_data['email'] ?? '' ?>">
                                    </div>

                                    <div class="mb-3">
                                        <label for="course_id" class="form-label">Khóa học</label>
                                        <select class="form-select" id="course_id" name="course_id">
                                            <option value="">Chọn khóa học</option>
                                            <?php if (!empty($courses)): ?>
                                                <?php foreach ($courses as $course): ?>
                                                    <option value="<?= $course['id'] ?>" 
                                                            <?= (isset($old_data['course_id']) && $old_data['course_id'] == $course['id']) ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($course['course_name']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="instructor_id" class="form-label">Giảng viên phụ trách</label>
                                        <select class="form-select" id="instructor_id" name="instructor_id">
                                            <option value="">Chọn giảng viên</option>
                                            <?php if (!empty($instructors)): ?>
                                                <?php foreach ($instructors as $instructor): ?>
                                                    <option value="<?= $instructor['id'] ?>" 
                                                            <?= (isset($old_data['instructor_id']) && $old_data['instructor_id'] == $instructor['id']) ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($instructor['full_name']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label for="enrollment_date" class="form-label">Ngày nhập học</label>
                                        <input type="date" class="form-control" id="enrollment_date" name="enrollment_date" 
                                               value="<?= $old_data['enrollment_date'] ?? '' ?>">
                                    </div>

                                    <div class="mb-3">
                                        <label for="completion_date" class="form-label">Ngày hoàn thành *</label>
                                        <input type="date" class="form-control" id="completion_date" name="completion_date" 
                                               value="<?= $old_data['completion_date'] ?? date('Y-m-d') ?>" required>
                                    </div>

                                    <div class="mb-3">
                                        <label for="status" class="form-label">Trạng thái</label>
                                        <select class="form-select" id="status" name="status">
                                            <option value="studying" <?= (isset($old_data['status']) && $old_data['status'] == 'studying') ? 'selected' : '' ?>>Đang học</option>
                                            <option value="completed" <?= (isset($old_data['status']) && $old_data['status'] == 'completed') ? 'selected' : 'selected' ?>>Hoàn thành</option>
                                            <option value="dropped" <?= (isset($old_data['status']) && $old_data['status'] == 'dropped') ? 'selected' : '' ?>>Bỏ học</option>
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label for="tracking_image" class="form-label">Ảnh phiếu theo dõi học tập</label>
                                        <input type="file" class="form-control" id="tracking_image" name="tracking_image" 
                                               accept="image/*,.pdf">
                                        <div class="form-text">Chấp nhận file: JPG, PNG, PDF. Tối đa 5MB.</div>
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-12">
                                    <button type="submit" class="btn btn-success">
                                        <i class="fas fa-save"></i> Lưu thông tin
                                    </button>
                                    <a href="/Quan_ly_trung_tam/public/students" class="btn btn-secondary ms-2">
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
</body>
</html>