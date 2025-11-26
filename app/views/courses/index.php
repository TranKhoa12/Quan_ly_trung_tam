<?php
require_once __DIR__ . '/../layouts/main.php';

// Page content
ob_start();
?>

<?= pageHeader(
    'Quản lý khóa học', 
    'Quản lý thông tin và theo dõi các khóa học',
    '<div class="d-flex gap-2">
        <a href="/Quan_ly_trung_tam/public/courses/create" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Thêm khóa học mới
        </a>
        <a href="/Quan_ly_trung_tam/public/courses/import" class="btn btn-success">
            <i class="fas fa-file-excel me-2"></i>Import Excel
        </a>
        <a href="/Quan_ly_trung_tam/public/courses/export" class="btn btn-info">
            <i class="fas fa-download me-2"></i>Export Excel
        </a>
    </div>'
) ?>

<div class="p-3">
    <!-- Success/Error Messages -->
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle me-2"></i>
            <?= htmlspecialchars($_SESSION['success_message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-circle me-2"></i>
            <?= htmlspecialchars($_SESSION['error_message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>
    <!-- Statistics Cards -->
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-6">
            <?= statsCard('fas fa-book', 'Tổng khóa học', $stats['total'], 'Tất cả khóa học', 'primary') ?>
        </div>
        <div class="col-xl-3 col-md-6">
            <?= statsCard('fas fa-play-circle', 'Đang hoạt động', $stats['active'], 'Khóa học đang mở', 'success') ?>
        </div>
        <div class="col-xl-3 col-md-6">
            <?= statsCard('fas fa-check-circle', 'Đã hoàn thành', $stats['completed'], 'Khóa học đã kết thúc', 'info') ?>
        </div>
        <div class="col-xl-3 col-md-6">
            <?= statsCard('fas fa-pause-circle', 'Ngừng hoạt động', $stats['inactive'], 'Khóa học tạm dừng', 'warning') ?>
        </div>
    </div>
    
    <!-- Filters -->
    <div class="stats-card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-5">
                    <label class="form-label">
                        <i class="fas fa-search"></i> Tìm kiếm
                    </label>
                    <input type="text" class="form-control" name="search" 
                           value="<?= htmlspecialchars($currentSearch) ?>" 
                           placeholder="Mã khóa học, tên khóa học...">
                </div>
                <div class="col-md-4">
                    <label class="form-label">
                        <i class="fas fa-toggle-on"></i> Trạng thái
                    </label>
                    <select class="form-select" name="status">
                        <option value="">Tất cả trạng thái</option>
                        <option value="active" <?= $currentStatus === 'active' ? 'selected' : '' ?>>Đang hoạt động</option>
                        <option value="inactive" <?= $currentStatus === 'inactive' ? 'selected' : '' ?>>Ngừng hoạt động</option>
                        <option value="completed" <?= $currentStatus === 'completed' ? 'selected' : '' ?>>Đã hoàn thành</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter"></i> Lọc
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Course List -->
    <div class="stats-card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="mb-0">
                    <i class="fas fa-list me-2"></i>Danh sách khóa học
                </h6>
                <div class="d-flex align-items-center gap-3">
                    <span class="text-muted">
                        Hiển thị <?= count($courses) ?> / <?= $totalCourses ?> khóa học
                        <?php if (!empty($currentSearch) || !empty($currentStatus)): ?>
                            (đã lọc)
                        <?php endif; ?>
                    </span>
                    <span class="badge bg-light text-dark">
                        Trang <?= $currentPage ?> / <?= $totalPages ?>
                    </span>
                </div>
            </div>
            
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th class="border-0">Mã khóa học</th>
                            <th class="border-0">Tên khóa học</th>
                            <th class="border-0">Học phí</th>
                            <th class="border-0 text-end">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($courses)): ?>
                            <tr>
                                <td colspan="4" class="text-center py-4">
                                    <i class="fas fa-inbox fa-3x text-muted mb-3 d-block"></i>
                                    <p class="text-muted">Chưa có khóa học nào</p>
                                    <a href="/Quan_ly_trung_tam/public/courses/create" class="btn btn-primary btn-sm">
                                        <i class="fas fa-plus me-1"></i>Thêm khóa học đầu tiên
                                    </a>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($courses as $course): ?>
                                <tr>
                                    <td>
                                        <span class="badge bg-primary"><?= htmlspecialchars($course['course_code']) ?></span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="icon-circle bg-primary bg-opacity-10 text-primary me-2">
                                                <i class="fas fa-graduation-cap"></i>
                                            </div>
                                            <div>
                                                <strong><?= htmlspecialchars($course['course_name']) ?></strong>
                                                <?php if (!empty($course['description'])): ?>
                                                    <br><small class="text-muted"><?= htmlspecialchars(substr($course['description'], 0, 50)) ?>...</small>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <strong class="text-success"><?= number_format($course['price'] ?? 0) ?> VNĐ</strong>
                                    </td>
                                    <td class="text-end">
                                        <div class="btn-group btn-group-sm">
                                            <a href="/Quan_ly_trung_tam/public/courses/<?= $course['id'] ?>" 
                                               class="btn btn-outline-primary" title="Xem chi tiết">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="/Quan_ly_trung_tam/public/courses/<?= $course['id'] ?>/edit" 
                                               class="btn btn-outline-warning" title="Sửa">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" class="btn btn-outline-danger" 
                                                    onclick="deleteCourse(<?= $course['id'] ?>, '<?= htmlspecialchars($course['course_name']) ?>')" 
                                                    title="Xóa">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <div class="d-flex justify-content-center mt-4">
                    <nav aria-label="Phân trang khóa học">
                        <ul class="pagination pagination-sm">
                            <!-- Previous button -->
                            <?php if ($currentPage > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $currentPage - 1])) ?>">
                                        <i class="fas fa-chevron-left"></i> Trước
                                    </a>
                                </li>
                            <?php else: ?>
                                <li class="page-item disabled">
                                    <span class="page-link"><i class="fas fa-chevron-left"></i> Trước</span>
                                </li>
                            <?php endif; ?>

                            <!-- Page numbers -->
                            <?php
                            $start = max(1, $currentPage - 2);
                            $end = min($totalPages, $currentPage + 2);
                            
                            if ($start > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => 1])) ?>">1</a>
                                </li>
                                <?php if ($start > 2): ?>
                                    <li class="page-item disabled">
                                        <span class="page-link">...</span>
                                    </li>
                                <?php endif; ?>
                            <?php endif; ?>

                            <?php for ($i = $start; $i <= $end; $i++): ?>
                                <li class="page-item <?= $i == $currentPage ? 'active' : '' ?>">
                                    <?php if ($i == $currentPage): ?>
                                        <span class="page-link"><?= $i ?></span>
                                    <?php else: ?>
                                        <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"><?= $i ?></a>
                                    <?php endif; ?>
                                </li>
                            <?php endfor; ?>

                            <?php if ($end < $totalPages): ?>
                                <?php if ($end < $totalPages - 1): ?>
                                    <li class="page-item disabled">
                                        <span class="page-link">...</span>
                                    </li>
                                <?php endif; ?>
                                <li class="page-item">
                                    <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $totalPages])) ?>"><?= $totalPages ?></a>
                                </li>
                            <?php endif; ?>

                            <!-- Next button -->
                            <?php if ($currentPage < $totalPages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $currentPage + 1])) ?>">
                                        Sau <i class="fas fa-chevron-right"></i>
                                    </a>
                                </li>
                            <?php else: ?>
                                <li class="page-item disabled">
                                    <span class="page-link">Sau <i class="fas fa-chevron-right"></i></span>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                </div>
                
                <!-- Page info -->
                <div class="text-center text-muted mt-2">
                    <small>
                        Trang <?= $currentPage ?> / <?= $totalPages ?> 
                        (<?= $totalCourses ?> khóa học tổng cộng)
                    </small>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();

// Custom CSS
$customCss = '
<style>
.icon-circle {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
</style>';

// Custom JavaScript
$customJs = '
<script>
function deleteCourse(id, name) {
    if (confirm("Bạn có chắc muốn xóa khóa học \"" + name + "\"?\n\nHành động này không thể hoàn tác!")) {
        const form = document.createElement("form");
        form.method = "POST";
        form.action = "/Quan_ly_trung_tam/public/courses/" + id + "/delete";
        
        const input = document.createElement("input");
        input.type = "hidden";
        input.name = "_method";
        input.value = "DELETE";
        form.appendChild(input);
        
        document.body.appendChild(form);
        form.submit();
    }
}
</script>';

// Render layout
useModernLayout('Quản lý khóa học', $content);
?>
