<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý học viên</title>
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
                    <h1 class="h2">Quản lý học viên</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="/Quan_ly_trung_tam/public/students/create" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Thêm học viên hoàn thành
                        </a>
                    </div>
                </div>

                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-body">
                        <?php if (empty($students)): ?>
                            <div class="text-center py-4">
                                <i class="fas fa-graduation-cap fa-3x text-muted mb-3"></i>
                                <h5>Chưa có học viên nào</h5>
                                <p class="text-muted">Hãy thêm học viên đầu tiên</p>
                                <a href="/Quan_ly_trung_tam/public/students/create" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> Thêm học viên
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Họ tên</th>
                                            <th>Số điện thoại</th>
                                            <th>Khóa học</th>
                                            <th>Giảng viên</th>
                                            <th>Ngày hoàn thành</th>
                                            <th>Trạng thái</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($students as $student): ?>
                                            <tr>
                                                <td><?= $student['id'] ?></td>
                                                <td><?= htmlspecialchars($student['full_name']) ?></td>
                                                <td><?= htmlspecialchars($student['phone']) ?></td>
                                                <td><?= htmlspecialchars($student['course_name'] ?? 'Chưa xác định') ?></td>
                                                <td><?= htmlspecialchars($student['instructor_name'] ?? 'Chưa xác định') ?></td>
                                                <td>
                                                    <?= $student['completion_date'] ? date('d/m/Y', strtotime($student['completion_date'])) : 'Chưa hoàn thành' ?>
                                                </td>
                                                <td>
                                                    <?php
                                                    $statusClass = [
                                                        'studying' => 'bg-primary',
                                                        'completed' => 'bg-success',
                                                        'dropped' => 'bg-danger'
                                                    ];
                                                    $statusText = [
                                                        'studying' => 'Đang học',
                                                        'completed' => 'Hoàn thành',
                                                        'dropped' => 'Bỏ học'
                                                    ];
                                                    ?>
                                                    <span class="badge <?= $statusClass[$student['status']] ?? 'bg-secondary' ?>">
                                                        <?= $statusText[$student['status']] ?? $student['status'] ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>