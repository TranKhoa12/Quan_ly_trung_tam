<?php
session_start();

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$pageTitle = "Cài đặt Extension";
$currentPage = 'extension';

ob_start();
?>

<!-- Tiêu đề -->
<div class="pagetitle">
    <h1><i class="fas fa-puzzle-piece"></i> Cài đặt Extension</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">Tài khoản</li>
            <li class="breadcrumb-item active">Cài đặt Extension</li>
        </ol>
    </nav>
</div>

<!-- Download Card -->
<div class="card">
    <div class="card-body text-center py-5">
        <i class="fas fa-puzzle-piece text-primary mb-3" style="font-size: 4rem;"></i>
        <h3 class="fw-bold mb-2">Trợ Lý Doanh Thu</h3>
        <p class="text-muted mb-4">Extension tự động điền thông tin doanh thu</p>
        
        <a href="downloads/certificate-auto-fill.zip" class="btn btn-primary btn-lg mb-3" download>
            <i class="fas fa-download me-2"></i> Tải Extension
        </a>
        
        <div class="d-flex justify-content-center gap-2 mb-3">
            <span class="badge bg-primary">v1.0.0</span>
            <span class="badge bg-primary">~50KB</span>
            <span class="badge bg-primary">Chrome/Edge</span>
        </div>
        
        <div class="alert alert-info text-start mt-4 mb-0">
            <i class="fas fa-info-circle me-2"></i>
            Extension chỉ hoạt động trên Google Chrome và Microsoft Edge (desktop)
        </div>
    </div>
</div>

<!-- Hướng dẫn cài đặt -->
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0"><i class="fas fa-list-ol me-2"></i> Hướng dẫn cài đặt</h5>
    </div>
    <div class="card-body">
        <ol class="list-group list-group-numbered list-group-flush">
            <li class="list-group-item d-flex align-items-start">
                <div class="ms-2 me-auto">
                    <div class="fw-bold">Tải file ZIP</div>
                    Click nút "Tải Extension" ở trên
                </div>
            </li>
            <li class="list-group-item d-flex align-items-start">
                <div class="ms-2 me-auto">
                    <div class="fw-bold">Giải nén file</div>
                    Click chuột phải → Extract All
                </div>
            </li>
            <li class="list-group-item d-flex align-items-start">
                <div class="ms-2 me-auto">
                    <div class="fw-bold">Mở Chrome Extensions</div>
                    Gõ <code>chrome://extensions/</code> vào thanh địa chỉ
                </div>
            </li>
            <li class="list-group-item d-flex align-items-start">
                <div class="ms-2 me-auto">
                    <div class="fw-bold">Bật Developer mode</div>
                    Bật công tắc "Developer mode" góc trên bên phải
                </div>
            </li>
            <li class="list-group-item d-flex align-items-start">
                <div class="ms-2 me-auto">
                    <div class="fw-bold">Load Extension</div>
                    Click "Load unpacked" → Chọn thư mục <code>certificate-auto-fill</code>
                </div>
            </li>
        </ol>
        
        <div class="alert alert-warning mt-3 mb-0">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <strong>Lưu ý:</strong> Không xóa thư mục extension sau khi cài đặt
        </div>
    </div>
</div>

<!-- Cách sử dụng -->
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0"><i class="fas fa-play-circle me-2"></i> Cách sử dụng</h5>
    </div>
    <div class="card-body">
        <ol class="mb-0">
            <li class="mb-2">Mở trang <strong>register.php</strong> hoặc <strong>edit-profile-admin.php</strong></li>
            <li class="mb-2">Click icon <strong>Trợ Lý Doanh Thu</strong> trên thanh công cụ Chrome</li>
            <li class="mb-2">Click <strong>"Lấy dữ liệu từ form"</strong></li>
            <li class="mb-0">Click <strong>"Điền vào form báo cáo doanh thu"</strong> → Xong!</li>
        </ol>
    </div>
</div>

<!-- FAQ -->
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0"><i class="fas fa-question-circle me-2"></i> Câu hỏi thường gặp</h5>
    </div>
    <div class="card-body">
        <div class="accordion" id="faqAccordion">
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                        Tại sao phải bật Developer mode?
                    </button>
                </h2>
                <div id="faq1" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                    <div class="accordion-body">
                        Extension nội bộ chưa publish lên Chrome Web Store. Developer mode cho phép cài từ source code.
                    </div>
                </div>
            </div>
            
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                        Extension có tự động cập nhật không?
                    </button>
                </h2>
                <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                    <div class="accordion-body">
                        Không. Khi có phiên bản mới, tải file ZIP mới và reload extension.
                    </div>
                </div>
            </div>
            
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                        Có an toàn không?
                    </button>
                </h2>
                <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                    <div class="accordion-body">
                        Hoàn toàn an toàn. Extension chỉ hoạt động local, không gửi dữ liệu ra ngoài.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../app/views/layouts/modern.php';
?>
