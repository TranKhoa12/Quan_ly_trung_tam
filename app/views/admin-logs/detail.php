<?php
require_once __DIR__ . '/../layouts/main.php';
useModernLayout('Chi tiết log', function() use ($log) { ?>
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col">
            <h2><i class="bi bi-file-text"></i> Chi tiết log #<?= $log['id'] ?></h2>
        </div>
        <div class="col-auto">
            <a href="<?= buildUrl('/admin-logs') ?>" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Quay lại
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Thông tin cơ bản -->
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-info-circle"></i> Thông tin cơ bản</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th style="width: 150px;">ID:</th>
                            <td><?= $log['id'] ?></td>
                        </tr>
                        <tr>
                            <th>Thời gian:</th>
                            <td><?= date('d/m/Y H:i:s', strtotime($log['created_at'])) ?></td>
                        </tr>
                        <tr>
                            <th>Nhân viên:</th>
                            <td><strong><?= htmlspecialchars($log['username']) ?></strong> (ID: <?= $log['user_id'] ?>)</td>
                        </tr>
                        <tr>
                            <th>Loại hành động:</th>
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
                                <span class="badge <?= $badgeClass ?> fs-6">
                                    <?= ucfirst($log['action_type']) ?>
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>Module:</th>
                            <td><code class="fs-6"><?= htmlspecialchars($log['module']) ?></code></td>
                        </tr>
                        <tr>
                            <th>Mô tả:</th>
                            <td><?= htmlspecialchars($log['description']) ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <!-- Thông tin kỹ thuật -->
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0"><i class="bi bi-gear"></i> Thông tin kỹ thuật</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th style="width: 150px;">IP Address:</th>
                            <td><code><?= htmlspecialchars($log['ip_address']) ?></code></td>
                        </tr>
                        <tr>
                            <th>User Agent:</th>
                            <td>
                                <small class="text-muted">
                                    <?= htmlspecialchars($log['user_agent']) ?>
                                </small>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Request Data -->
    <?php if (!empty($log['request_data_decoded'])): ?>
        <div class="card mb-4">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0"><i class="bi bi-arrow-right-circle"></i> Request Data</h5>
            </div>
            <div class="card-body">
                <pre class="bg-light p-3 rounded" style="max-height: 400px; overflow-y: auto;"><code><?= json_encode($log['request_data_decoded'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) ?></code></pre>
            </div>
        </div>
    <?php endif; ?>

    <!-- Old Data (cho update/delete) -->
    <?php if (!empty($log['old_data_decoded'])): ?>
        <div class="card mb-4">
            <div class="card-header bg-warning">
                <h5 class="mb-0"><i class="bi bi-archive"></i> Dữ liệu cũ (trước khi thay đổi)</h5>
            </div>
            <div class="card-body">
                <pre class="bg-light p-3 rounded" style="max-height: 400px; overflow-y: auto;"><code><?= json_encode($log['old_data_decoded'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) ?></code></pre>
            </div>
        </div>
    <?php endif; ?>

    <!-- New Data (cho create/update) -->
    <?php if (!empty($log['new_data_decoded'])): ?>
        <div class="card mb-4">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="bi bi-plus-circle"></i> Dữ liệu mới (sau khi thay đổi)</h5>
            </div>
            <div class="card-body">
                <pre class="bg-light p-3 rounded" style="max-height: 400px; overflow-y: auto;"><code><?= json_encode($log['new_data_decoded'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) ?></code></pre>
            </div>
        </div>
    <?php endif; ?>

    <!-- So sánh thay đổi (cho update) -->
    <?php if (!empty($log['old_data_decoded']) && !empty($log['new_data_decoded'])): ?>
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-arrow-left-right"></i> So sánh thay đổi</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 200px;">Trường</th>
                                <th>Giá trị cũ</th>
                                <th>Giá trị mới</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $oldData = $log['old_data_decoded'];
                            $newData = $log['new_data_decoded'];
                            $allKeys = array_unique(array_merge(array_keys($oldData), array_keys($newData)));
                            foreach ($allKeys as $key):
                                $oldValue = $oldData[$key] ?? null;
                                $newValue = $newData[$key] ?? null;
                                $isChanged = $oldValue !== $newValue;
                            ?>
                                <tr class="<?= $isChanged ? 'table-warning' : '' ?>">
                                    <td><strong><?= htmlspecialchars($key) ?></strong></td>
                                    <td>
                                        <?php if (is_array($oldValue) || is_object($oldValue)): ?>
                                            <code><?= json_encode($oldValue, JSON_UNESCAPED_UNICODE) ?></code>
                                        <?php else: ?>
                                            <?= htmlspecialchars($oldValue ?? '(null)') ?>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($isChanged): ?>
                                            <i class="bi bi-arrow-right text-primary"></i>
                                        <?php endif; ?>
                                        <?php if (is_array($newValue) || is_object($newValue)): ?>
                                            <code><?= json_encode($newValue, JSON_UNESCAPED_UNICODE) ?></code>
                                        <?php else: ?>
                                            <strong><?= htmlspecialchars($newValue ?? '(null)') ?></strong>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">

<?php }); ?>
