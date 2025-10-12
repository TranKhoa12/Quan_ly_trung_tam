<?php
ob_start();
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Dashboard</h1>
</div>

<div class="row">
    <div class="col-md-3 mb-4">
        <div class="card text-white bg-primary">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4><?= $stats['total_reports_today'] ?></h4>
                        <p class="mb-0">Báo cáo hôm nay</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-chart-line fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-4">
        <div class="card text-white bg-success">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4><?= number_format($stats['total_revenue_today']) ?> đ</h4>
                        <p class="mb-0">Doanh thu hôm nay</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-money-bill-wave fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-4">
        <div class="card text-white bg-info">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4><?= $stats['total_students'] ?></h4>
                        <p class="mb-0">Học viên đang học</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-graduation-cap fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-4">
        <div class="card text-white bg-warning">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4><?= $stats['pending_certificates'] ?></h4>
                        <p class="mb-0">Chứng nhận chờ duyệt</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-certificate fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Thông tin người dùng</h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <td><strong>Họ tên:</strong></td>
                        <td><?= htmlspecialchars($user['full_name']) ?></td>
                    </tr>
                    <tr>
                        <td><strong>Tên đăng nhập:</strong></td>
                        <td><?= htmlspecialchars($user['username']) ?></td>
                    </tr>
                    <tr>
                        <td><strong>Vai trò:</strong></td>
                        <td>
                            <span class="badge bg-<?= $user['role'] === 'admin' ? 'danger' : 'primary' ?>">
                                <?= $user['role'] === 'admin' ? 'Quản trị viên' : 'Nhân viên' ?>
                            </span>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Thao tác nhanh</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="/Quan_ly_trung_tam/public/reports/create" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Tạo báo cáo đến trung tâm
                    </a>
                    <a href="/Quan_ly_trung_tam/public/revenue/create" class="btn btn-success">
                        <i class="fas fa-plus"></i> Tạo báo cáo doanh thu
                    </a>
                    <a href="/Quan_ly_trung_tam/public/students/create" class="btn btn-info">
                        <i class="fas fa-plus"></i> Thêm học viên hoàn thành
                    </a>
                    <a href="/Quan_ly_trung_tam/public/certificates/create" class="btn btn-warning">
                        <i class="fas fa-plus"></i> Yêu cầu cấp chứng nhận
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
$title = 'Dashboard - Hệ thống quản lý trung tâm';
include __DIR__ . '/../layouts/app.php';
?>