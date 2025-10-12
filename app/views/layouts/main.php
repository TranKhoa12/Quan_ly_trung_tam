<?php
// Layout helper functions
function renderLayout($title, $content, $activePage = '', $customCss = '', $customJs = '') {
    // Lấy thông tin user từ session
    $userName = $_SESSION['full_name'] ?? 'User';
    $userRole = $_SESSION['role'] ?? 'staff';
    
    // Debug thông tin (chỉ để test)
    if (isset($_GET['debug'])) {
        echo "<pre style='background: #f8f9fa; padding: 10px; margin: 10px; border: 1px solid #ddd;'>";
        echo "DEBUG SESSION:\n";
        echo "Username: " . ($_SESSION['username'] ?? 'Not set') . "\n";
        echo "Full Name: " . ($_SESSION['full_name'] ?? 'Not set') . "\n";
        echo "Role: " . ($_SESSION['role'] ?? 'Not set') . "\n";
        echo "User ID: " . ($_SESSION['user_id'] ?? 'Not set') . "\n";
        echo "All Session: " . print_r($_SESSION, true);
        echo "</pre>";
    }
    
    ob_start();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title) ?> - Quản lý trung tâm</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
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

        .sidebar .nav-link:hover .badge {
            animation: pulse 1s infinite;
        }

        .sidebar .nav-link.active {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            box-shadow: var(--shadow-md);
            transform: translateX(5px);
        }

        .sidebar .nav-header {
            font-size: 0.75rem;
            letter-spacing: 0.05em;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            margin-bottom: 0.5rem;
            animation: fadeInLeft 0.6s ease-out;
        }

        .sidebar .badge {
            font-size: 0.6rem;
            padding: 0.25rem 0.5rem;
            animation: fadeIn 0.8s ease-out;
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }

        @keyframes fadeInLeft {
            from { opacity: 0; transform: translateX(-20px); }
            to { opacity: 1; transform: translateX(0); }
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
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

        .form-label {
            font-weight: 600;
            color: var(--dark-color);
            margin-bottom: 0.5rem;
        }

        .form-label i {
            margin-right: 0.5rem;
        }

        .table {
            border-radius: var(--border-radius);
            overflow: hidden;
        }

        .table th {
            background: var(--light-color);
            border: none;
            padding: 1rem;
            font-weight: 600;
            color: var(--dark-color);
        }

        .table td {
            padding: 1rem;
            border: none;
            border-bottom: 1px solid #f1f5f9;
        }

        .table tr:hover {
            background: #f8fafc;
        }

        .user-menu {
            background: rgba(255, 255, 255, 0.1);
            border-radius: var(--border-radius);
            padding: 0.75rem;
            margin-top: 1rem;
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

            .btn-toolbar {
                flex-direction: column;
                gap: 0.5rem;
            }

            .btn-toolbar .btn {
                width: 100%;
            }
        }

        @media (max-width: 576px) {
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

        <?= $customCss ?>
    </style>
</head>
<body>
    <!-- Mobile Menu Toggle -->
    <button class="btn btn-primary d-md-none position-fixed" 
            style="top: 1rem; left: 1rem; z-index: 1055;" 
            onclick="toggleSidebar()">
        <i class="fas fa-bars"></i>
    </button>

    <!-- Sidebar Overlay for Mobile -->
    <div class="position-fixed w-100 h-100 bg-dark bg-opacity-50 d-md-none" 
         id="sidebar-overlay" 
         style="z-index: 1049; display: none;"
         onclick="toggleSidebar()"></div>

    <div class="container-fluid p-0">
        <div class="row g-0">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 sidebar" id="sidebar">
                <div class="position-sticky pt-3">
                    <div class="text-center text-white mb-4 p-3">
                        <i class="fas fa-graduation-cap fs-2 mb-2"></i>
                        <h5 class="mb-0">Quản lý trung tâm</h5>
                    </div>
                    
                    <ul class="nav flex-column px-3">
                        <li class="nav-item">
                            <a class="nav-link <?= $activePage === 'dashboard' ? 'active' : '' ?>" 
                               href="/Quan_ly_trung_tam/public/dashboard">
                                <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= $activePage === 'reports' ? 'active' : '' ?>" 
                               href="/Quan_ly_trung_tam/public/reports">
                                <i class="fas fa-chart-line me-2"></i> Báo cáo học viên đến trung tâm
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= $activePage === 'revenue' ? 'active' : '' ?>" 
                               href="/Quan_ly_trung_tam/public/revenue">
                                <i class="fas fa-money-bill-wave me-2"></i> Báo cáo doanh thu
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= $activePage === 'students' ? 'active' : '' ?>" 
                               href="/Quan_ly_trung_tam/public/students">
                                <i class="fas fa-graduation-cap me-2"></i> Học viên
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= $activePage === 'certificates' ? 'active' : '' ?>" 
                               href="/Quan_ly_trung_tam/public/certificates">
                                <i class="fas fa-certificate me-2"></i> Chứng nhận
                                <span class="badge bg-warning ms-auto">3</span>
                            </a>
                        </li>
                        
                        <!-- Admin Only Features -->
                        <?php if ($userRole === 'admin'): ?>
                        <!-- Debug: User role is: <?= $userRole ?> -->
                        <li class="nav-item mt-3">
                            <div class="nav-header text-white-50 px-3 py-2 text-uppercase small fw-bold">
                                <i class="fas fa-shield-alt me-2"></i>Quản trị hệ thống
                            </div>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= $activePage === 'staff' ? 'active' : '' ?>" 
                               href="/Quan_ly_trung_tam/public/staff">
                                <i class="fas fa-users-cog me-2"></i> Quản lý nhân viên
                                <span class="badge bg-success ms-auto">5</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= $activePage === 'departments' ? 'active' : '' ?>" 
                               href="/Quan_ly_trung_tam/public/departments">
                                <i class="fas fa-building me-2"></i> Quản lý phòng ban
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= $activePage === 'settings' ? 'active' : '' ?>" 
                               href="/Quan_ly_trung_tam/public/settings">
                                <i class="fas fa-cog me-2"></i> Cài đặt hệ thống
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= $activePage === 'backups' ? 'active' : '' ?>" 
                               href="/Quan_ly_trung_tam/public/backups">
                                <i class="fas fa-database me-2"></i> Sao lưu & Khôi phục
                            </a>
                        </li>
                        <?php else: ?>
                        <!-- Debug: User role is: <?= $userRole ?> - Not admin, hiding admin menu -->
                        <?php endif; ?>
                    </ul>

                    <!-- User Menu -->
                    <div class="user-menu mx-3">
                        <div class="d-flex align-items-center text-white mb-2">
                            <i class="fas fa-user-circle me-2"></i>
                            <div>
                                <div class="fw-semibold"><?= htmlspecialchars($userName) ?></div>
                                <small class="text-muted"><?= ucfirst($userRole) ?></small>
                            </div>
                        </div>
                        <div class="d-grid">
                            <a href="/Quan_ly_trung_tam/public/logout" class="btn btn-outline-light btn-sm">
                                <i class="fas fa-sign-out-alt me-1"></i> Đăng xuất
                            </a>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10">
                <div class="main-content fade-in-up">
                    <?= $content ?>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
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
        
        // Auto-hide mobile sidebar when clicking on main content
        document.addEventListener('click', function(e) {
            if (window.innerWidth < 768 && !e.target.closest('#sidebar') && !e.target.closest('button[onclick="toggleSidebar()"]')) {
                const sidebar = document.getElementById('sidebar');
                const overlay = document.getElementById('sidebar-overlay');
                if (sidebar.classList.contains('show')) {
                    sidebar.classList.remove('show');
                    overlay.style.display = 'none';
                }
            }
        });
        
        // Handle window resize
        window.addEventListener('resize', function() {
            if (window.innerWidth >= 768) {
                const sidebar = document.getElementById('sidebar');
                const overlay = document.getElementById('sidebar-overlay');
                sidebar.classList.remove('show');
                overlay.style.display = 'none';
            }
        });

        <?= $customJs ?>
    </script>
</body>
</html>
<?php
    return ob_get_clean();
}

function pageHeader($title, $subtitle = '', $buttons = '') {
    return '
    <div class="page-header">
        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center">
            <div>
                <h1 class="h2 mb-2">' . htmlspecialchars($title) . '</h1>
                ' . ($subtitle ? '<p class="text-muted mb-0">' . htmlspecialchars($subtitle) . '</p>' : '') . '
            </div>
            ' . ($buttons ? '<div class="btn-toolbar mb-2 mb-md-0">' . $buttons . '</div>' : '') . '
        </div>
    </div>';
}

function statsCard($icon, $title, $value, $description = '', $color = 'primary', $trend = '') {
    return '
    <div class="stats-card border-' . $color . '">
        <div class="card-body text-center">
            <div class="d-flex align-items-center justify-content-center mb-3">
                <i class="' . $icon . ' text-' . $color . ' me-2 fs-4"></i>
                <span class="fw-semibold">' . htmlspecialchars($title) . '</span>
            </div>
            <div class="mb-2">
                <span class="fs-2 fw-bold text-' . $color . ' d-block">' . htmlspecialchars($value) . '</span>
                ' . ($trend ? '<small class="text-muted">' . $trend . '</small>' : '') . '
            </div>
            ' . ($description ? '<small class="text-muted">' . htmlspecialchars($description) . '</small>' : '') . '
        </div>
    </div>';
}
?>