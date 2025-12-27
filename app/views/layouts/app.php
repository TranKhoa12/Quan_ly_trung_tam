<!DOCTYPE html>
<html lang="vi" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="light only">
    <title><?= $title ?? 'Dashboard' ?> - Quản lý trung tâm</title>
    
    <!-- Preload fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    
    <!-- App Layout Styles -->
    <link href="/assets/css/app-layout.css" rel="stylesheet">
    
</head>
<body>
    <?php
    // Get user session info
    $user = [
        'id' => $_SESSION['user_id'] ?? 1,
        'username' => $_SESSION['username'] ?? 'demo',
        'full_name' => $_SESSION['full_name'] ?? 'Demo User',
        'role' => $_SESSION['role'] ?? 'staff'
    ];
    
    function getPageTitle() {
        $uri = $_SERVER['REQUEST_URI'];
        if (strpos($uri, '/dashboard') !== false) return 'Dashboard';
        if (strpos($uri, '/students') !== false) return 'Quản lý học viên';
        if (strpos($uri, '/courses') !== false) return 'Quản lý khóa học';
        if (strpos($uri, '/reports') !== false) return 'Báo cáo học viên';
        if (strpos($uri, '/revenue') !== false) return 'Báo cáo doanh thu';
        if (strpos($uri, '/certificates') !== false) return 'Cấp chứng nhận';
        if (strpos($uri, '/staff') !== false) return 'Quản lý nhân viên';
        return 'Dashboard';
    }
    
    function getCurrentSection() {
        $uri = $_SERVER['REQUEST_URI'];
        if (strpos($uri, '/dashboard') !== false) return 'dashboard';
        if (strpos($uri, '/students') !== false) return 'students';
        if (strpos($uri, '/courses') !== false) return 'courses';
        if (strpos($uri, '/reports') !== false) return 'reports';
        if (strpos($uri, '/revenue') !== false) return 'revenue';
        if (strpos($uri, '/certificates') !== false) return 'certificates';
        if (strpos($uri, '/staff') !== false) return 'staff';
        return 'dashboard';
    }
    
    function isActiveNav($section) {
        return getCurrentSection() === $section ? 'active' : '';
    }
    
    function getUserInitials($name) {
        $words = explode(' ', trim($name));
        if (count($words) >= 2) {
            return strtoupper(substr($words[0], 0, 1) . substr(end($words), 0, 1));
        }
        return strtoupper(substr($name, 0, 2));
    }
    
    $currentPageTitle = getPageTitle();
    $currentSection = getCurrentSection();
    $userInitials = getUserInitials($user['full_name']);
    $isAdmin = $user['role'] === 'admin';
    ?>

    <!-- App Layout -->
    <div class="app-layout">
        <!-- Sidebar Navigation -->
        <aside class="sidebar" id="sidebar">
            <!-- Logo Header -->
            <div class="sidebar-header">
                <a href="/dashboard" class="sidebar-logo">
                    <div class="logo-icon">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                    <div class="logo-text">
                        <div class="logo-title">QuanLy</div>
                        <div class="logo-subtitle">Center</div>
                    </div>
                </a>
            </div>
            
            <!-- Navigation Menu -->
            <nav class="sidebar-nav">
                <!-- Main Section -->
                <div class="nav-section">
                    <div class="nav-section-title">
                        <i class="fas fa-home me-1"></i>
                        Trang chủ
                    </div>
                    <ul class="nav-list">
                        <li class="nav-item">
                            <a href="/dashboard" class="nav-link <?= isActiveNav('dashboard') ?>">
                                <i class="nav-icon fas fa-tachometer-alt"></i>
                                <span class="nav-text">Dashboard</span>
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- Daily Tasks -->
                <div class="nav-section">
                    <div class="nav-section-title">
                        <i class="fas fa-tasks me-1"></i>
                        Công việc hàng ngày
                    </div>
                    <ul class="nav-list">
                        <li class="nav-item">
                            <a href="/reports" class="nav-link <?= isActiveNav('reports') ?>">
                                <i class="nav-icon fas fa-clipboard-list"></i>
                                <span class="nav-text">Báo cáo học viên</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="/revenue" class="nav-link <?= isActiveNav('revenue') ?>">
                                <i class="nav-icon fas fa-chart-line"></i>
                                <span class="nav-text">Báo cáo doanh thu</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="/certificates" class="nav-link <?= isActiveNav('certificates') ?>">
                                <i class="nav-icon fas fa-award"></i>
                                <span class="nav-text">Cấp chứng nhận</span>
                            </a>
                        </li>
                    </ul>
                </div>

                <?php if ($isAdmin): ?>
                <!-- Admin Section -->
                <div class="nav-section">
                    <div class="nav-section-title">
                        <i class="fas fa-crown me-1"></i>
                        Quản trị hệ thống
                    </div>
                    <ul class="nav-list">
                        <li class="nav-item">
                            <a href="/students" class="nav-link <?= isActiveNav('students') ?>">
                                <i class="nav-icon fas fa-user-graduate"></i>
                                <span class="nav-text">Quản lý học viên</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="/courses" class="nav-link <?= isActiveNav('courses') ?>">
                                <i class="nav-icon fas fa-book"></i>
                                <span class="nav-text">Quản lý khóa học</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="/staff" class="nav-link <?= isActiveNav('staff') ?>">
                                <i class="nav-icon fas fa-users-cog"></i>
                                <span class="nav-text">Quản lý nhân viên</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="/admin-logs" class="nav-link <?= isActiveNav('admin-logs') ?>">
                                <i class="nav-icon fas fa-history"></i>
                                <span class="nav-text">Nhật ký hoạt động</span>
                            </a>
                        </li>
                    </ul>
                </div>
                <?php endif; ?>

                <!-- Account Section -->
                <div class="nav-section">
                    <div class="nav-section-title">
                        <i class="fas fa-user me-1"></i>
                        Tài khoản
                    </div>
                    <ul class="nav-list">
                        <li class="nav-item">
                            <a href="/profile" class="nav-link">
                                <i class="nav-icon fas fa-id-card"></i>
                                <span class="nav-text">Thông tin cá nhân</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="/logout" class="nav-link text-danger" onclick="return confirmLogout()">
                                <i class="nav-icon fas fa-sign-out-alt"></i>
                                <span class="nav-text">Đăng xuất</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Top Header -->
            <header class="top-header">
                <div class="header-left">
                    <button class="mobile-menu-btn d-lg-none" onclick="toggleSidebar()">
                        <i class="fas fa-bars"></i>
                    </button>
                    
                    <div class="page-info">
                        <h1 class="page-title"><?= $currentPageTitle ?></h1>
                        <nav class="breadcrumb">
                            <span class="breadcrumb-item">
                                <i class="fas fa-home me-1"></i>Trang chủ
                            </span>
                            <i class="fas fa-chevron-right breadcrumb-separator"></i>
                            <span class="breadcrumb-item current"><?= $currentPageTitle ?></span>
                        </nav>
                    </div>
                </div>
                
                <div class="header-right">
                    <!-- Search -->
                    <div class="header-search d-none d-md-flex">
                        <div class="search-wrapper">
                            <i class="fas fa-search search-icon"></i>
                            <input type="text" class="search-input" placeholder="Tìm kiếm..." id="globalSearch">
                        </div>
                    </div>
                    
                    <!-- Notifications -->
                    <div class="header-actions">
                        <button class="action-btn" onclick="showNotifications()" title="Thông báo">
                            <i class="fas fa-bell"></i>
                            <span class="notification-badge">3</span>
                        </button>
                        
                        <button class="action-btn" onclick="showMessages()" title="Tin nhắn">
                            <i class="fas fa-envelope"></i>
                            <span class="notification-badge">2</span>
                        </button>
                        <button class="action-btn" onclick="showSettings()" title="Cài đặt">
                            <i class="fas fa-cog"></i>
                        </button>
                    </div>
                    
                    <!-- User Menu -->
                    <div class="user-menu" onclick="toggleUserDropdown()">
                        <div class="user-avatar">
                            <?= $userInitials ?>
                        </div>
                        <div class="user-info d-none d-sm-block">
                            <div class="user-name"><?= htmlspecialchars($user['full_name']) ?></div>
                            <div class="user-role"><?= $isAdmin ? 'Quản trị viên' : 'Nhân viên' ?></div>
                        </div>
                        <i class="fas fa-chevron-down dropdown-arrow"></i>
                        
                        <!-- User Dropdown -->
                        <div class="user-dropdown" id="userDropdown">
                            <a href="/profile" class="dropdown-item">
                                <i class="fas fa-user me-2"></i>Thông tin cá nhân
                            </a>
                            <a href="/settings" class="dropdown-item">
                                <i class="fas fa-cog me-2"></i>Cài đặt
                            </a>
                            <div class="dropdown-divider"></div>
                            <a href="/logout" class="dropdown-item text-danger" onclick="return confirmLogout()">
                                <i class="fas fa-sign-out-alt me-2"></i>Đăng xuất
                            </a>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <div class="page-content" id="pageContent">
                    <div class="text-center text-white mb-4">
                        <h5>Quản lý trung tâm</h5>
                        <?php if (isset($_SESSION['full_name'])): ?>
                            <small>Xin chào, <?= htmlspecialchars($_SESSION['full_name']) ?></small>
                        <?php endif; ?>
                    </div>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="/Quan_ly_trung_tam/public/dashboard">
                                <i class="fas fa-tachometer-alt"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/Quan_ly_trung_tam/public/reports">
                                <i class="fas fa-chart-line"></i> Báo cáo đến trung tâm
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/Quan_ly_trung_tam/public/revenue">
                                <i class="fas fa-money-bill-wave"></i> Báo cáo doanh thu
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/Quan_ly_trung_tam/public/students">
                                <i class="fas fa-graduation-cap"></i> Học viên
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/Quan_ly_trung_tam/public/certificates">
                                <i class="fas fa-certificate"></i> Chứng nhận
                            </a>
                        </li>
                        <li class="nav-item mt-3">
                            <a class="nav-link" href="/Quan_ly_trung_tam/public/logout">
                                <i class="fas fa-sign-out-alt"></i> Đăng xuất
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                <?= $content ?? '' ?>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <?= $scripts ?? '' ?>
</body>
</html>