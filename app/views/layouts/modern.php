<?php
$scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
$scriptDir = str_replace('\\', '/', dirname($scriptName));
$appBasePath = rtrim($scriptDir, '/');
if ($appBasePath === '' || $appBasePath === '.') {
    $appBasePath = '';
}

$buildUrl = function (string $path = '') use ($appBasePath): string {
    $normalized = '/' . ltrim($path, '/');
    return ($appBasePath ? $appBasePath : '') . $normalized;
};

$appBasePathString = $appBasePath ? $appBasePath : '';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Dashboard' ?> - Quản lý trung tâm</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    
    <!-- Modern Dashboard CSS -->
    <link href="<?= $buildUrl('assets/css/modern-dashboard.css') ?>" rel="stylesheet">
    <link href="<?= $buildUrl('assets/css/dark-theme.css') ?>" rel="stylesheet">
    
    <!-- Bootstrap for utilities only -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div id="globalToastContainer" class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 11000;"></div>
    <?php
    // Get user info from session
    $currentUser = [
        'id' => $_SESSION['user_id'] ?? 1,
        'username' => $_SESSION['username'] ?? 'demo',
        'full_name' => $_SESSION['full_name'] ?? 'Demo User',
        'role' => $_SESSION['role'] ?? 'staff'
    ];
    
    function getActiveNavItem() {
        $currentPath = $_SERVER['REQUEST_URI'];
        if (strpos($currentPath, '/dashboard') !== false) return 'dashboard';
        if (strpos($currentPath, '/students') !== false) return 'students';
        if (strpos($currentPath, '/courses') !== false) return 'courses';
        if (strpos($currentPath, '/reports') !== false) return 'reports';
        if (strpos($currentPath, '/revenue') !== false) return 'revenue';
        if (strpos($currentPath, '/certificates') !== false) return 'certificates';
        if (strpos($currentPath, '/completion-slips') !== false) return 'completion_slips';
        if (strpos($currentPath, '/staff') !== false) return 'staff';
        return 'dashboard';
    }

    function isActive($item) {
        return getActiveNavItem() === $item ? 'active' : '';
    }

    function getUserInitials($fullName) {
        $words = explode(' ', trim($fullName));
        if (count($words) >= 2) {
            return strtoupper(substr($words[0], 0, 1) . substr(end($words), 0, 1));
        }
        return strtoupper(substr($fullName, 0, 2));
    }

    ?>

    <div class="app-layout">
        <!-- Sidebar Navigation -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <a href="<?= $buildUrl('dashboard') ?>" class="sidebar-logo">
                    <i class="fas fa-graduation-cap"></i>
                    <span>QuanLy Center</span>
                </a>
            </div>
            
            <nav class="sidebar-nav">
                <div class="nav-section">
                    <div class="nav-section-title">Trang chủ</div>
                    <div class="nav-item">
                        <a href="<?= $buildUrl('dashboard') ?>" class="nav-link <?= isActive('dashboard') ?>">
                            <i class="fas fa-tachometer-alt"></i>
                            <span>Dashboard</span>
                        </a>
                    </div>
                </div>
                
                <div class="nav-section">
                    <div class="nav-section-title">Công việc hàng ngày</div>
                    <div class="nav-item">
                        <a href="<?= $buildUrl('reports') ?>" class="nav-link <?= isActive('reports') ?>">
                            <i class="fas fa-clipboard-list"></i>
                            <span>Báo cáo học viên</span>
                        </a>
                    </div>
                    <div class="nav-item">
                        <a href="<?= $buildUrl('revenue') ?>" class="nav-link <?= isActive('revenue') ?>">
                            <i class="fas fa-chart-line"></i>
                            <span>Báo cáo doanh thu</span>
                        </a>
                    </div>
                    <div class="nav-item">
                        <a href="<?= $buildUrl('certificates') ?>" class="nav-link <?= isActive('certificates') ?>">
                            <i class="fas fa-award"></i>
                            <span>Cấp chứng nhận</span>
                        </a>
                    </div>
                    <div class="nav-item">
                        <a href="<?= $buildUrl('completion-slips') ?>" class="nav-link <?= isActive('completion_slips') ?>">
                            <i class="fas fa-user-check"></i>
                            <span>Phiếu hoàn thành HV</span>
                            <span class="badge bg-info-soft">Mới</span>
                        </a>
                    </div>
                </div>

                <?php if ($currentUser['role'] === 'admin'): ?>
                <div class="nav-section">
                    <div class="nav-section-title">Quản trị hệ thống</div>
                    <div class="nav-item">
                        <a href="<?= $buildUrl('students') ?>" class="nav-link <?= isActive('students') ?>">
                            <i class="fas fa-user-graduate"></i>
                            <span>Quản lý học viên</span>
                        </a>
                    </div>
                    <div class="nav-item">
                        <a href="<?= $buildUrl('courses') ?>" class="nav-link <?= isActive('courses') ?>">
                            <i class="fas fa-book"></i>
                            <span>Quản lý khóa học</span>
                        </a>
                    </div>
                    <div class="nav-item">
                        <a href="<?= $buildUrl('staff') ?>" class="nav-link <?= isActive('staff') ?>">
                            <i class="fas fa-users-cog"></i>
                            <span>Quản lý nhân viên</span>
                        </a>
                    </div>
                </div>
                <?php endif; ?>

                <div class="nav-section">
                    <div class="nav-section-title">Tài khoản</div>
                    <div class="nav-item">
                        <a href="<?= $buildUrl('profile') ?>" class="nav-link">
                            <i class="fas fa-user"></i>
                            <span>Thông tin cá nhân</span>
                        </a>
                    </div>
                    <div class="nav-item">
                        <a href="<?= $buildUrl('logout') ?>" class="nav-link" onclick="return confirm('Bạn có chắc muốn đăng xuất?')">
                            <i class="fas fa-sign-out-alt"></i>
                            <span>Đăng xuất</span>
                        </a>
                    </div>
                </div>
            </nav>
        </aside>

        <!-- Main Content Area -->
        <main class="main-content">
            <!-- Top Header -->
            <header class="top-header">
                <div class="header-left">
                    <button class="header-btn mobile-menu-btn d-lg-none" onclick="toggleSidebar()">
                        <i class="fas fa-bars"></i>
                    </button>
                    <div>
                        <h1 class="page-title"><?= $pageTitle ?? 'Dashboard' ?></h1>
                        <!-- <nav class="breadcrumb">
                            <span class="breadcrumb-item">Trang chủ</span>
                            <span class="breadcrumb-item"><?= $pageTitle ?? 'Dashboard' ?></span>
                        </nav> -->
                    </div>
                </div>
                
                <div class="header-right">
                    <!-- <div class="header-search d-none d-md-block">
                        <i class="fas fa-search search-icon"></i>
                        <input type="text" class="search-input" placeholder="Tìm kiếm...">
                    </div> -->
                    
                    <div class="header-actions">
                        <button class="header-btn" title="Thông báo" onclick="showNotifications()">
                            <i class="fas fa-bell"></i>
                            <span class="notification-badge">3</span>
                        </button>
                        <!-- <button class="header-btn" title="Tin nhắn" onclick="showMessages()">
                            <i class="fas fa-envelope"></i>
                            <span class="notification-badge">2</span>
                        </button> -->
                        <div class="theme-toggle" onclick="toggleTheme()" title="Chuyển đổi theme">
                            <i class="fas fa-sun theme-toggle-icon sun-icon"></i>
                            <i class="fas fa-moon theme-toggle-icon moon-icon"></i>
                        </div>
                        
                        <!-- <button class="header-btn" title="Cài đặt" onclick="showSettings()">
                            <i class="fas fa-cog"></i>
                        </button> -->
                    </div>
                    
                    <div class="user-menu" onclick="toggleUserMenu()">
                        <div class="user-avatar"><?= getUserInitials($currentUser['full_name']) ?></div>
                        <div class="user-info d-none d-sm-block">
                            <div class="user-name"><?= htmlspecialchars($currentUser['full_name']) ?></div>
                            <div class="user-role"><?= $currentUser['role'] === 'admin' ? 'Quản trị viên' : 'Nhân viên' ?></div>
                        </div>
                        <i class="fas fa-chevron-down ms-2" style="color: var(--gray-400);"></i>
                    </div>
                </div>
            </header>

            <!-- Dashboard Content -->
            <div class="dashboard-content">
                <!-- Content will be injected here by individual pages -->
                <?= $content ?? '' ?>
            </div>
        </main>
    </div>

    <!-- Mobile Overlay -->
    <div class="mobile-overlay" id="mobileOverlay" onclick="closeSidebar()"></div>

    <!-- JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
    const APP_BASE_PATH = <?= json_encode($appBasePathString) ?>;

    // Sidebar Toggle Functions
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('mobileOverlay');
        
        if (window.innerWidth <= 1024) {
            sidebar.classList.add('open');
            overlay.style.display = 'block';
            document.body.style.overflow = 'hidden';
        }
    }

    function closeSidebar() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('mobileOverlay');
        
        sidebar.classList.remove('open');
        overlay.style.display = 'none';
        document.body.style.overflow = '';
    }

    // Header Functions
    function toggleUserMenu() {
        // User menu dropdown functionality
        console.log('Toggle user menu');
    }

    function showNotifications() {
        // Show notifications
        alert('Thông báo: Bạn có 3 thông báo mới!');
    }

    const toastContainer = document.getElementById('globalToastContainer');

    window.showToast = function(message, type = 'success') {
        const spawnToast = () => {
            if (!toastContainer) {
                console.warn('Toast container is not available');
                return;
            }
            const toastId = `toast-${Date.now()}-${Math.floor(Math.random() * 1000)}`;
            const iconMap = {
                success: 'fa-check-circle',
                danger: 'fa-exclamation-triangle',
                warning: 'fa-exclamation-circle',
                info: 'fa-info-circle'
            };
            const bgMap = {
                success: 'bg-success',
                danger: 'bg-danger',
                warning: 'bg-warning',
                info: 'bg-info'
            };
            const toastHtml = `
                <div id="${toastId}" class="toast align-items-center text-white ${bgMap[type] || 'bg-primary'} border-0" role="alert" aria-live="assertive" aria-atomic="true">
                    <div class="d-flex">
                        <div class="toast-body">
                            <i class="fas ${iconMap[type] || 'fa-info-circle'} me-2"></i>${message}
                        </div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                    </div>
                </div>
            `;
            toastContainer.insertAdjacentHTML('beforeend', toastHtml);
            const toastElement = document.getElementById(toastId);
            const toastInstance = new bootstrap.Toast(toastElement, { autohide: true, delay: 4000 });
            toastInstance.show();
            toastElement.addEventListener('hidden.bs.toast', () => toastElement.remove());
        };

        if (window.bootstrap && bootstrap.Toast) {
            spawnToast();
        } else {
            window.addEventListener('load', spawnToast, { once: true });
        }
    };

    window.pendingToasts = window.pendingToasts || [];
    
    <?php if (isset($_SESSION['success'])): ?>
    window.pendingToasts.push({ message: <?= json_encode($_SESSION['success']) ?>, type: 'success' });
    <?php unset($_SESSION['success']); ?>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error'])): ?>
    window.pendingToasts.push({ message: <?= json_encode($_SESSION['error']) ?>, type: 'danger' });
    <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    function flushPendingToasts() {
        if (window.pendingToasts && window.pendingToasts.length) {
            const queued = window.pendingToasts.slice();
            window.pendingToasts.length = 0;
            queued.forEach(toast => {
                window.showToast(toast.message, toast.type);
            });
        }
    }

    if (document.readyState === 'complete') {
        flushPendingToasts();
    } else {
        window.addEventListener('load', flushPendingToasts);
    }

    function showMessages() {
        // Show messages
        alert('Tin nhắn: Bạn có 2 tin nhắn mới!');
    }

    function showSettings() {
        // Show settings
        alert('Cài đặt hệ thống');
    }

    // Real-time Clock Update
    function updateClock() {
        const now = new Date();
        const timeStr = now.toLocaleString('vi-VN', {
            day: '2-digit',
            month: '2-digit', 
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
        
        // Update any time displays
        const clockElements = document.querySelectorAll('.live-clock');
        clockElements.forEach(el => {
            el.textContent = timeStr;
        });
    }

    // Theme Management
    function toggleTheme() {
        const currentTheme = document.documentElement.getAttribute('data-theme');
        const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
        
        document.documentElement.setAttribute('data-theme', newTheme);
        localStorage.setItem('theme', newTheme);
        
        // Add transition class for smooth animation
        document.body.classList.add('theme-transition');
        setTimeout(() => {
            document.body.classList.remove('theme-transition');
        }, 300);
    }

    function initializeTheme() {
        const savedTheme = localStorage.getItem('theme');
        const systemTheme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
        const theme = savedTheme || systemTheme;
        
        document.documentElement.setAttribute('data-theme', theme);
    }

    // Initialize
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize theme
        initializeTheme();
        
        // Update clock every minute
        updateClock();
        setInterval(updateClock, 60000);
        
        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(e) {
            const sidebar = document.getElementById('sidebar');
            const menuBtn = document.querySelector('.mobile-menu-btn');
            
            if (window.innerWidth <= 1024 && 
                !sidebar.contains(e.target) && 
                !menuBtn.contains(e.target) && 
                sidebar.classList.contains('open')) {
                closeSidebar();
            }
        });

        // Handle window resize
        window.addEventListener('resize', function() {
            if (window.innerWidth > 1024) {
                closeSidebar();
            }
        });

        // Add loading states to navigation links
        const navLinks = document.querySelectorAll('.nav-link');
        navLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                if (this.classList.contains('active')) return;
                
                const originalHTML = this.innerHTML;
                this.innerHTML = originalHTML.replace(/<span>(.+)<\/span>/, '<span>Đang tải...</span>');
                
                // Reset after a short delay if still on page
                setTimeout(() => {
                    this.innerHTML = originalHTML;
                }, 2000);
            });
        });
    });

    // Keyboard Shortcuts
    document.addEventListener('keydown', function(e) {
        // Alt + D for Dashboard
        if (e.altKey && e.key === 'd') {
            e.preventDefault();
            window.location.href = APP_BASE_PATH + '/dashboard';
        }
        
        // Alt + R for Reports
        if (e.altKey && e.key === 'r') {
            e.preventDefault();
            window.location.href = APP_BASE_PATH + '/reports';
        }
        
        // Alt + C for Certificates
        if (e.altKey && e.key === 'c') {
            e.preventDefault();
            window.location.href = APP_BASE_PATH + '/certificates';
        }
    });
    </script>

    <style>
    /* Mobile Overlay */
    .mobile-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        z-index: 1000;
        backdrop-filter: blur(2px);
    }

    /* Enhanced Mobile Styles */
    @media (max-width: 1024px) {
        .sidebar {
            transform: translateX(-100%);
            position: fixed;
            height: 100vh;
            z-index: 1001;
            box-shadow: var(--shadow-xl);
        }
        
        .sidebar.open {
            transform: translateX(0);
        }
        
        .mobile-overlay {
            display: none;
        }
    }

    /* Loading Animation for Nav Links */
    .nav-link.loading {
        opacity: 0.7;
        pointer-events: none;
    }

    .nav-link.loading::after {
        content: '';
        width: 12px;
        height: 12px;
        border: 2px solid transparent;
        border-top: 2px solid currentColor;
        border-radius: 50%;
        animation: spin 1s linear infinite;
        margin-left: auto;
    }

    @keyframes spin {
        to { transform: rotate(360deg); }
    }

    /* Additional Custom Animations */
    .slide-in-right {
        animation: slideInRight 0.3s ease-out;
    }

    @keyframes slideInRight {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    </style>
</body>
</html>