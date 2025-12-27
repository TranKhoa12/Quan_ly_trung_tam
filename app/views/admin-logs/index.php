<?php
require_once __DIR__ . '/../layouts/main.php';
useModernLayout('Nhật ký hoạt động', function() use ($logs, $filters, $users, $modules, $currentPage, $totalPages, $totalLogs) { ?>
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col">
            <h2><i class="bi bi-journal-text"></i> Nhật ký hoạt động hệ thống</h2>
            <p class="text-muted">Theo dõi và giám sát mọi hoạt động của nhân viên trong hệ thống</p>
        </div>
        <div class="col-auto">
            <a href="<?= buildUrl('/admin-logs/export?' . http_build_query($filters)) ?>" class="btn btn-success">
                <i class="bi bi-download"></i> Xuất CSV
            </a>
        </div>
    </div>

    <!-- Thống kê tổng quan -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="text-primary"><?= number_format($totalLogs) ?></h3>
                    <p class="mb-0">Tổng số logs</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="text-success"><?= count(array_filter($logs, fn($l) => $l['action_type'] === 'login')) ?></h3>
                    <p class="mb-0">Đăng nhập (trang này)</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="text-warning"><?= count(array_filter($logs, fn($l) => in_array($l['action_type'], ['create', 'update']))) ?></h3>
                    <p class="mb-0">Thêm/Sửa (trang này)</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="text-danger"><?= count(array_filter($logs, fn($l) => $l['action_type'] === 'delete')) ?></h3>
                    <p class="mb-0">Xóa (trang này)</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Bộ lọc -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-funnel"></i> Bộ lọc</h5>
        </div>
        <div class="card-body">
            <form method="GET" action="<?= buildUrl('/admin-logs') ?>" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Nhân viên</label>
                    <select name="user_id" class="form-select">
                        <option value="">-- Tất cả --</option>
                        <?php foreach ($users as $user): ?>
                            <option value="<?= $user['id'] ?>" <?= ($filters['user_id'] == $user['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($user['username']) ?> - <?= htmlspecialchars($user['full_name'] ?? '') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-2">
                    <label class="form-label">Loại hành động</label>
                    <select name="action_type" class="form-select">
                        <option value="">-- Tất cả --</option>
                        <option value="login" <?= ($filters['action_type'] === 'login') ? 'selected' : '' ?>>Đăng nhập</option>
                        <option value="logout" <?= ($filters['action_type'] === 'logout') ? 'selected' : '' ?>>Đăng xuất</option>
                        <option value="create" <?= ($filters['action_type'] === 'create') ? 'selected' : '' ?>>Tạo mới</option>
                        <option value="update" <?= ($filters['action_type'] === 'update') ? 'selected' : '' ?>>Cập nhật</option>
                        <option value="delete" <?= ($filters['action_type'] === 'delete') ? 'selected' : '' ?>>Xóa</option>
                        <option value="view" <?= ($filters['action_type'] === 'view') ? 'selected' : '' ?>>Xem</option>
                        <option value="export" <?= ($filters['action_type'] === 'export') ? 'selected' : '' ?>>Xuất file</option>
                    </select>
                </div>
                
                <div class="col-md-2">
                    <label class="form-label">Module</label>
                    <select name="module" class="form-select">
                        <option value="">-- Tất cả --</option>
                        <?php foreach ($modules as $module): ?>
                            <option value="<?= htmlspecialchars($module['module']) ?>" <?= ($filters['module'] === $module['module']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($module['module']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-2">
                    <label class="form-label">Từ ngày</label>
                    <input type="date" name="from_date" class="form-control" value="<?= htmlspecialchars($filters['from_date']) ?>">
                </div>
                
                <div class="col-md-2">
                    <label class="form-label">Đến ngày</label>
                    <input type="date" name="to_date" class="form-control" value="<?= htmlspecialchars($filters['to_date']) ?>">
                </div>
                
                <div class="col-md-1 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-search"></i> Lọc
                    </button>
                </div>
            </form>
            
            <div class="row mt-3">
                <div class="col-md-11">
                    <input type="text" class="form-control" placeholder="Tìm kiếm trong mô tả hoặc tên nhân viên..." id="searchInput" value="<?= htmlspecialchars($filters['search'] ?? '') ?>">
                </div>
                <div class="col-md-1">
                    <button type="button" class="btn btn-secondary w-100" onclick="doSearch()">
                        <i class="bi bi-search"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bảng logs -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-bordered table-sm admin-logs-table align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th style="width: 160px; white-space: nowrap;">Thời gian</th>
                            <th style="width: 150px;">Nhân viên</th>
                            <th style="width: 100px;">Hành động</th>
                            <th style="width: 120px;">Module</th>
                            <th>Mô tả</th>
                            <th style="width: 120px;">IP Address</th>
                            <th style="width: 80px;">Chi tiết</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($logs)): ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    <i class="bi bi-inbox" style="font-size: 2rem;"></i>
                                    <p class="mb-0 mt-2">Không có dữ liệu</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($logs as $log): ?>
                                <tr>
                                    <td class="datetime-cell">
                                        <small><?= date('d/m/Y H:i:s', strtotime($log['created_at'])) ?></small>
                                    </td>
                                    <td>
                                        <strong><?= htmlspecialchars($log['username']) ?></strong>
                                    </td>
                                    <td>
                                        <?php
                                        $badgeClass = match($log['action_type']) {
                                            'login' => 'bg-info',
                                            'logout' => 'bg-secondary',
                                            'create' => 'bg-success',
                                            'update' => 'bg-warning text-dark',
                                            'delete' => 'bg-danger',
                                            'view' => 'bg-primary',
                                            'export' => 'bg-dark',
                                            default => 'bg-secondary'
                                        };
                                        ?>
                                        <span class="badge <?= $badgeClass ?>">
                                            <?= ucfirst($log['action_type']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <code><?= htmlspecialchars($log['module']) ?></code>
                                    </td>
                                    <td>
                                        <?= htmlspecialchars($log['description']) ?>
                                    </td>
                                    <td>
                                        <small class="text-muted"><?= htmlspecialchars($log['ip_address']) ?></small>
                                    </td>
                                    <td class="text-center">
                                        <a href="<?= buildUrl('/admin-logs/detail/' . $log['id']) ?>" class="btn btn-sm btn-info">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <nav aria-label="Page navigation" class="mt-4">
                    <ul class="pagination justify-content-center">
                        <?php if ($currentPage > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="<?= buildUrl('/admin-logs?' . http_build_query(array_merge($filters, ['page' => $currentPage - 1]))) ?>">
                                    Trước
                                </a>
                            </li>
                        <?php endif; ?>

                        <?php 
                        $startPage = max(1, $currentPage - 2);
                        $endPage = min($totalPages, $currentPage + 2);
                        for ($i = $startPage; $i <= $endPage; $i++): 
                        ?>
                            <li class="page-item <?= ($i === $currentPage) ? 'active' : '' ?>">
                                <a class="page-link" href="<?= buildUrl('/admin-logs?' . http_build_query(array_merge($filters, ['page' => $i]))) ?>">
                                    <?= $i ?>
                                </a>
                            </li>
                        <?php endfor; ?>

                        <?php if ($currentPage < $totalPages): ?>
                            <li class="page-item">
                                <a class="page-link" href="<?= buildUrl('/admin-logs?' . http_build_query(array_merge($filters, ['page' => $currentPage + 1]))) ?>">
                                    Sau
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                    <p class="text-center text-muted">
                        Trang <?= $currentPage ?> / <?= $totalPages ?> (Tổng: <?= number_format($totalLogs) ?> logs)
                    </p>
                </nav>
            <?php endif; ?>
        </div>
    </div>

    <!-- Xóa logs cũ -->
    <div class="card mt-4">
        <div class="card-header bg-danger text-white">
            <h5 class="mb-0"><i class="bi bi-trash"></i> Xóa logs cũ</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="<?= buildUrl('/admin-logs/delete-old') ?>" onsubmit="return confirm('Bạn có chắc chắn muốn xóa logs cũ? Hành động này không thể hoàn tác!')">
                <div class="row align-items-end">
                    <div class="col-md-3">
                        <label class="form-label">Xóa logs cũ hơn (ngày)</label>
                        <input type="number" name="days" class="form-control" value="90" min="30" required>
                        <small class="text-muted">Tối thiểu 30 ngày</small>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-trash"></i> Xóa logs cũ
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function doSearch() {
    const searchValue = document.getElementById('searchInput').value;
    const urlParams = new URLSearchParams(window.location.search);
    if (searchValue) {
        urlParams.set('search', searchValue);
    } else {
        urlParams.delete('search');
    }
    urlParams.set('page', '1'); // Reset về trang 1 khi search
    window.location.href = '<?= buildUrl('/admin-logs') ?>?' + urlParams.toString();
}

document.getElementById('searchInput')?.addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        doSearch();
    }
});
</script>

<style>
.admin-logs-table td,
.admin-logs-table th {
    font-size: 0.9rem;
    padding: 0.45rem 0.55rem;
}

.admin-logs-table code {
    font-size: 0.9rem;
}

.admin-logs-table .datetime-cell {
    white-space: nowrap;
}
</style>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">

<?php }); ?>
