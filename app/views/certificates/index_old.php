<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý chứng nhận</title>
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
                    <h1 class="h2">Quản lý chứng nhận</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="/Quan_ly_trung_tam/public/certificates/create" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Yêu cầu cấp chứng nhận
                        </a>
                    </div>
                </div>

                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-body">
                        <?php if (empty($certificates)): ?>
                            <div class="text-center py-4">
                                <i class="fas fa-certificate fa-3x text-muted mb-3"></i>
                                <h5>Chưa có yêu cầu chứng nhận nào</h5>
                                <p class="text-muted">Hãy tạo yêu cầu chứng nhận đầu tiên</p>
                                <a href="/Quan_ly_trung_tam/public/certificates/create" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> Yêu cầu cấp chứng nhận
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Tên học viên</th>
                                            <th>Số điện thoại</th>
                                            <th>Bộ môn học</th>
                                            <th>Tình trạng nhận</th>
                                            <th>Trạng thái duyệt</th>
                                            <th>Ngày tạo</th>
                                            <th>Thao tác</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($certificates as $cert): ?>
                                            <tr>
                                                <td><?= $cert['id'] ?></td>
                                                <td><?= htmlspecialchars($cert['student_name']) ?></td>
                                                <td><?= htmlspecialchars($cert['phone']) ?></td>
                                                <td><?= htmlspecialchars($cert['subject']) ?></td>
                                                <td>
                                                    <?php
                                                    $receiveClass = $cert['receive_status'] === 'received' ? 'bg-success' : 'bg-warning';
                                                    $receiveText = $cert['receive_status'] === 'received' ? 'Đã nhận' : 'Chưa nhận';
                                                    ?>
                                                    <span class="badge <?= $receiveClass ?>">
                                                        <?= $receiveText ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php
                                                    $statusClass = [
                                                        'pending' => 'bg-warning',
                                                        'approved' => 'bg-success',
                                                        'cancelled' => 'bg-danger'
                                                    ];
                                                    $statusText = [
                                                        'pending' => 'Đang đợi',
                                                        'approved' => 'Đã duyệt',
                                                        'cancelled' => 'Hủy'
                                                    ];
                                                    ?>
                                                    <span class="badge <?= $statusClass[$cert['approval_status']] ?? 'bg-secondary' ?>">
                                                        <?= $statusText[$cert['approval_status']] ?? $cert['approval_status'] ?>
                                                    </span>
                                                </td>
                                                <td><?= date('d/m/Y', strtotime($cert['created_at'])) ?></td>
                                                <td>
                                                    <?php if ($cert['approval_status'] === 'pending'): ?>
                                                        <button class="btn btn-sm btn-success" onclick="approveCertificate(<?= $cert['id'] ?>, 'approve')">
                                                            <i class="fas fa-check"></i> Duyệt
                                                        </button>
                                                        <button class="btn btn-sm btn-danger" onclick="approveCertificate(<?= $cert['id'] ?>, 'cancel')">
                                                            <i class="fas fa-times"></i> Hủy
                                                        </button>
                                                    <?php endif; ?>
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
    <script>
        function approveCertificate(id, action) {
            const actionText = action === 'approve' ? 'duyệt' : 'hủy';
            const notes = prompt(`Nhập ghi chú khi ${actionText}:`);
            
            if (notes !== null) {
                fetch(`/Quan_ly_trung_tam/public/certificates/${id}/approve`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: action,
                        notes: notes
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        location.reload();
                    } else {
                        alert('Lỗi: ' + data.message);
                    }
                })
                .catch(error => {
                    alert('Có lỗi xảy ra: ' + error);
                });
            }
        }
    </script>
</body>
</html>