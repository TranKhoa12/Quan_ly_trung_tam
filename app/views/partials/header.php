<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Hệ thống quản lý trung tâm' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
        }
        
        .sidebar .nav-link {
            color: #fff;
            padding: 12px 20px;
            border-radius: 8px;
            margin: 4px 8px;
            transition: all 0.3s ease;
            border: 1px solid transparent;
        }
        
        .sidebar .nav-link:hover {
            background-color: rgba(255,255,255,0.1);
            color: #fff;
            transform: translateX(5px);
            border-color: rgba(255,255,255,0.2);
        }
        
        .sidebar .nav-link.active {
            background-color: rgba(255,255,255,0.2);
            color: #fff;
            border-color: rgba(255,255,255,0.3);
        }
        
        .sidebar .nav-link i {
            width: 20px;
            text-align: center;
            margin-right: 10px;
        }
        
        .main-content {
            background-color: #f8f9fa;
            min-height: 100vh;
            padding: 20px;
        }
        
        .user-info {
            background: rgba(255,255,255,0.1);
            border-radius: 10px;
            padding: 15px;
            margin: 15px 8px 25px 8px;
            text-align: center;
            color: #fff;
        }
        
        .user-info .user-avatar {
            width: 50px;
            height: 50px;
            background: rgba(255,255,255,0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 10px;
            font-size: 20px;
        }
        
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
            transition: transform 0.2s ease;
        }
        
        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
        }
        
        .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px 15px 0 0 !important;
            border: none;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 8px;
            padding: 8px 20px;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
            transform: translateY(-1px);
        }
        
        .navbar-brand {
            font-weight: bold;
            font-size: 1.5rem;
        }
        
        .section-divider {
            border-top: 1px solid rgba(255,255,255,0.2);
            margin: 15px 0;
        }
        
        .nav-section-title {
            color: rgba(255,255,255,0.7);
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            padding: 10px 20px 5px;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 d-md-block sidebar">
                <div class="position-sticky pt-3">
                    <!-- User Info -->
                    <div class="user-info">
                        <div class="user-avatar">
                            <i class="fas fa-user"></i>
                        </div>
                        <h6 class="mb-1"><?= $_SESSION['full_name'] ?? 'Admin' ?></h6>
                        <small class="opacity-75"><?= ucfirst($_SESSION['role'] ?? 'admin') ?></small>
                    </div>
                    
                    <!-- Navigation -->
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link <?= (strpos($_SERVER['REQUEST_URI'], '/dashboard') !== false) ? 'active' : '' ?>" 
                               href="/Quan_ly_trung_tam/public/dashboard">
                                <i class="fas fa-tachometer-alt"></i> Dashboard
                            </a>
                        </li>
                        
                        <!-- Management Section -->
                        <div class="nav-section-title">Quản lý</div>
                        
                        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                            <li class="nav-item">
                                <a class="nav-link <?= (strpos($_SERVER['REQUEST_URI'], '/courses') !== false) ? 'active' : '' ?>" 
                                   href="/Quan_ly_trung_tam/public/courses">
                                    <i class="fas fa-book"></i> Khóa học
                                </a>
                            </li>
                        <?php endif; ?>
                        
                        <li class="nav-item">
                            <a class="nav-link <?= (strpos($_SERVER['REQUEST_URI'], '/students') !== false) ? 'active' : '' ?>" 
                               href="/Quan_ly_trung_tam/public/students">
                                <i class="fas fa-graduation-cap"></i> Học viên
                            </a>
                        </li>
                        
                        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                            <li class="nav-item">
                                <a class="nav-link <?= (strpos($_SERVER['REQUEST_URI'], '/staff') !== false) ? 'active' : '' ?>" 
                                   href="/Quan_ly_trung_tam/public/staff">
                                    <i class="fas fa-users"></i> Nhân viên
                                </a>
                            </li>
                        <?php endif; ?>
                        
                        <li class="nav-item">
                            <a class="nav-link <?= (strpos($_SERVER['REQUEST_URI'], '/certificates') !== false) ? 'active' : '' ?>" 
                               href="/Quan_ly_trung_tam/public/certificates">
                                <i class="fas fa-certificate"></i> Chứng nhận
                            </a>
                        </li>
                        
                        <!-- Reports Section -->
                        <div class="nav-section-title">Báo cáo</div>
                        
                        <li class="nav-item">
                            <a class="nav-link <?= (strpos($_SERVER['REQUEST_URI'], '/reports') !== false) ? 'active' : '' ?>" 
                               href="/Quan_ly_trung_tam/public/reports">
                                <i class="fas fa-chart-line"></i> Báo cáo đến trung tâm
                            </a>
                        </li>
                        
                        <li class="nav-item">
                            <a class="nav-link <?= (strpos($_SERVER['REQUEST_URI'], '/revenue') !== false) ? 'active' : '' ?>" 
                               href="/Quan_ly_trung_tam/public/revenue">
                                <i class="fas fa-money-bill-wave"></i> Báo cáo doanh thu
                            </a>
                        </li>
                        
                        <!-- Account Section -->
                        <div class="section-divider"></div>
                        
                        <li class="nav-item">
                            <a class="nav-link" href="/Quan_ly_trung_tam/public/logout">
                                <i class="fas fa-sign-out-alt"></i> Đăng xuất
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 main-content">
                <!-- Flash Messages -->
                <?php if (isset($_SESSION['success_message'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i>
                        <?= htmlspecialchars($_SESSION['success_message']) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php unset($_SESSION['success_message']); ?>
                <?php endif; ?>

                <?php if (isset($_SESSION['error_message'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <?= htmlspecialchars($_SESSION['error_message']) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php unset($_SESSION['error_message']); ?>
                <?php endif; ?>

                <?php if (isset($_SESSION['warning_message'])): ?>
                    <div class="alert alert-warning alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <?= htmlspecialchars($_SESSION['warning_message']) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php unset($_SESSION['warning_message']); ?>
                <?php endif; ?>

                <?php if (isset($_SESSION['info_message'])): ?>
                    <div class="alert alert-info alert-dismissible fade show" role="alert">
                        <i class="fas fa-info-circle me-2"></i>
                        <?= htmlspecialchars($_SESSION['info_message']) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php unset($_SESSION['info_message']); ?>
                <?php endif; ?>