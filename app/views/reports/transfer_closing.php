<?php
require_once __DIR__ . '/../layouts/main.php';

$pageTitle = 'Báo cáo chuyển khoản';
ob_start();
?>

<div class="page-header">
    <div class="page-header-content">
        <div>
            <h1 class="page-title">
                <i class="fas fa-money-check-alt me-2"></i>
                Báo cáo chuyển khoản (TK Thầy Hiến)
            </h1>
            <p class="page-subtitle">Tổng hợp số giao dịch CK, số tiền và ảnh CK trong ngày</p>
        </div>
    </div>
</div>

<div class="page-content">
    <?php if (isset($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>
            <?= htmlspecialchars($error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card modern-card mb-4">
        <div class="card-body">
            <form method="GET" action="<?= buildUrl('reports/transfer-closing') ?>" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label fw-semibold">
                        <i class="fas fa-calendar-alt me-2 text-primary"></i>
                        Chọn ngày báo cáo
                    </label>
                    <input
                        type="date"
                        class="form-control form-control-lg"
                        name="date"
                        value="<?= htmlspecialchars($date ?? date('Y-m-d')) ?>"
                        max="<?= date('Y-m-d') ?>">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary btn-lg w-100">
                        <i class="fas fa-sync-alt me-2"></i>Cập nhật
                    </button>
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-success btn-lg w-100" onclick="copyTransferReport()">
                        <i class="fas fa-copy me-2"></i>Sao chép
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="stat-card stat-card-primary">
                <div class="stat-card-body">
                    <div class="stat-card-icon">
                        <i class="fas fa-hashtag"></i>
                    </div>
                    <div class="stat-card-content">
                        <div class="stat-card-label">SL giao dịch</div>
                        <div class="stat-card-value"><?= $transferStats['total_count'] ?? 0 ?></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card stat-card-warning">
                <div class="stat-card-body">
                    <div class="stat-card-icon">
                        <i class="fas fa-coins"></i>
                    </div>
                    <div class="stat-card-content">
                        <div class="stat-card-label">Tổng tiền</div>
                        <div class="stat-card-value" style="font-size:1.6rem;">
                            <?= isset($transferStats['total_amount']) ? number_format($transferStats['total_amount'], 0, ',', ',') . 'đ' : '0đ' ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card stat-card-info">
                <div class="stat-card-body">
                    <div class="stat-card-icon">
                        <i class="fas fa-calendar-day"></i>
                    </div>
                    <div class="stat-card-content">
                        <div class="stat-card-label">Ngày báo cáo</div>
                        <div class="stat-card-value" style="font-size:1.4rem;">
                            <?= date('d/m/Y', strtotime($date ?? date('Y-m-d'))) ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card modern-card mb-4">
        <div class="card-header bg-gradient-primary text-white">
            <h5 class="mb-0">
                <i class="fas fa-file-alt me-2"></i>
                Nội dung báo cáo
            </h5>
        </div>
        <div class="card-body p-4">
            <div class="report-text-container">
                <pre id="transferReportText" class="report-text mb-0"><?= htmlspecialchars($reportText ?? '') ?></pre>
            </div>
        </div>
    </div>

    <div class="card modern-card">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="fas fa-image me-2 text-primary"></i>
                Ảnh chuyển khoản
            </h5>
            <span class="badge bg-secondary"><?= isset($transferStats['images']) ? count($transferStats['images']) : 0 ?> ảnh</span>
        </div>
        <div class="card-body">
            <?php if (!empty($transferStats['images'])): ?>
                <div class="ck-image-grid">
                    <?php foreach ($transferStats['images'] as $idx => $img): ?>
                        <?php
                            $resolved = $img;
                            $decoded = json_decode($img, true);
                            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                                if (isset($decoded['url'])) {
                                    $resolved = $decoded['url'];
                                } elseif (isset($decoded['secure_url'])) {
                                    $resolved = $decoded['secure_url'];
                                } elseif (isset($decoded['path'])) {
                                    $resolved = $decoded['path'];
                                } elseif (isset($decoded['filePath'])) {
                                    $resolved = $decoded['filePath'];
                                } elseif (isset($decoded[0]) && is_string($decoded[0])) {
                                    $resolved = $decoded[0];
                                }
                            }

                            $isUrl = preg_match('/^https?:\/\//', $resolved) === 1;
                            $normalizedPath = ltrim($resolved, '/');
                            if (!$isUrl && (strpos($normalizedPath, 'uploads/') === 0 || strpos($normalizedPath, 'public/uploads/') === 0)) {
                                $normalizedPath = $normalizedPath;
                            }
                            $url = $isUrl ? $resolved : buildUrl($normalizedPath !== '' ? $normalizedPath : ('uploads/' . $resolved));
                            $path = parse_url($url, PHP_URL_PATH);
                            $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
                            $isImage = in_array($ext, ['jpg','jpeg','png','gif','webp','bmp']);
                            $fileLabel = 'Tệp CK ' . ($idx + 1) . ($ext ? ' (' . strtoupper($ext) . ')' : '');
                        ?>
                        <?php if ($isImage): ?>
                            <div class="ck-image-item">
                                <button type="button" class="btn btn-light btn-copy-ck" onclick="copyImageUrl('<?= htmlspecialchars($url) ?>')" title="Copy URL">
                                    <i class="fas fa-copy"></i>
                                </button>
                                <a href="<?= htmlspecialchars($url) ?>" target="_blank" rel="noopener" class="ck-image-thumb">
                                    <img src="<?= htmlspecialchars($url) ?>" alt="Ảnh CK <?= $idx + 1 ?>" loading="lazy">
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="ck-file-item">
                                <button type="button" class="btn btn-light btn-copy-ck me-2" onclick="copyImageUrl('<?= htmlspecialchars($url) ?>')" title="Copy URL">
                                    <i class="fas fa-copy"></i>
                                </button>
                                <a href="<?= htmlspecialchars($url) ?>" target="_blank" class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-file me-1"></i><?= htmlspecialchars($fileLabel) ?>
                                </a>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="text-muted">Không có ảnh CK</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.stat-card {
    border-radius: 16px;
    border: none;
    overflow: hidden;
    transition: all 0.3s ease;
    height: 100%;
}
.stat-card:hover { transform: translateY(-4px); box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1), 0 10px 10px -5px rgba(0,0,0,0.04); }
.stat-card-body { padding: 0.75rem 1rem; display: flex; align-items: center; gap: 0.65rem; }
.stat-card-icon { width: 24px; height: 24px; border-radius: 7px; display: flex; align-items: center; justify-content: center; font-size: 12px; flex-shrink: 0; }
.stat-card-content { flex: 1; }
.stat-card-label { font-size: 0.72rem; font-weight: 500; color: #6b7280; margin-bottom: 0.25rem; text-transform: uppercase; letter-spacing: 0.05em; }
.stat-card-value { font-size: 1.6rem; font-weight: 700; line-height: 1; }
.stat-card-primary { background: linear-gradient(135deg,#667eea 0%,#764ba2 100%); color: #fff; }
.stat-card-warning { background: linear-gradient(135deg,#f59e0b 0%,#d97706 100%); color: #fff; }
.stat-card-info { background: linear-gradient(135deg,#06b6d4 0%,#0891b2 100%); color: #fff; }
.stat-card-primary .stat-card-icon, .stat-card-warning .stat-card-icon, .stat-card-info .stat-card-icon { background: rgba(255,255,255,0.2); color: #fff; }
.stat-card-primary .stat-card-label, .stat-card-warning .stat-card-label, .stat-card-info .stat-card-label { color: rgba(255,255,255,0.9); }

.report-text-container { background: linear-gradient(135deg,#f8fafc 0%,#f1f5f9 100%); border: 2px solid #e2e8f0; border-radius: 12px; padding: 2rem; position: relative; overflow: hidden; }
.report-text-container::before { content: ''; position: absolute; top: 0; left: 0; width: 4px; height: 100%; background: linear-gradient(180deg,#667eea 0%,#764ba2 100%); }
.report-text { font-family: 'Courier New','Consolas', monospace; font-size: 15px; line-height: 2; color: #1e293b; white-space: pre-wrap; word-wrap: break-word; font-weight: 500; padding-left: 1rem; margin: 0; }

.btn-outline-primary.btn-sm { border-radius: 10px; }

.ck-image-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 16px; }
.ck-image-item { position: relative; }
.ck-image-thumb { display: block; border-radius: 12px; overflow: hidden; border: 1px solid #e5e7eb; box-shadow: 0 6px 18px rgba(0,0,0,0.06); transition: transform 0.2s ease, box-shadow 0.2s ease; background: #fff; }
.ck-image-thumb img { width: 100%; height: 200px; object-fit: cover; display: block; }
.ck-image-thumb:hover { transform: translateY(-3px); box-shadow: 0 12px 22px rgba(0,0,0,0.12); }
.ck-file-item { display: flex; align-items: center; }
.btn-copy-ck { position: absolute; top: 8px; right: 8px; padding: 6px 9px; border-radius: 10px; box-shadow: 0 6px 18px rgba(0,0,0,0.06); }
.ck-file-item .btn-copy-ck { position: static; box-shadow: none; }
</style>

<script>
function copyTransferReport() {
    const text = document.getElementById('transferReportText').textContent;
    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(text).then(() => showToast('success', 'Đã sao chép báo cáo vào clipboard!')).catch(() => fallbackCopy(text));
    } else {
        fallbackCopy(text);
    }
}

function copyImageUrl(url) {
    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(url).then(() => showToast('success', 'Đã copy link ảnh!')).catch(() => fallbackCopy(url));
    } else {
        fallbackCopy(url);
    }
}

function fallbackCopy(text) {
    const ta = document.createElement('textarea');
    ta.value = text;
    ta.style.position = 'fixed';
    ta.style.opacity = '0';
    document.body.appendChild(ta);
    ta.select();
    try { document.execCommand('copy'); showToast('success', 'Đã sao chép báo cáo vào clipboard!'); }
    catch { showToast('error', 'Không thể sao chép. Vui lòng thử lại!'); }
    document.body.removeChild(ta);
}

function showToast(type, message) {
    const toastContainer = document.getElementById('globalToastContainer');
    if (!toastContainer) return;
    const toastId = 'toast-' + Date.now();
    const iconClass = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
    const bgClass = type === 'success' ? 'bg-success' : 'bg-danger';
    const html = `
        <div id="${toastId}" class="toast align-items-center text-white ${bgClass} border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body"><i class="fas ${iconClass} me-2"></i>${message}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>`;
    toastContainer.insertAdjacentHTML('beforeend', html);
    const el = document.getElementById(toastId);
    const toast = new bootstrap.Toast(el, { delay: 3000 });
    toast.show();
    el.addEventListener('hidden.bs.toast', () => el.remove());
}
</script>

<?php
$content = ob_get_clean();
useModernLayout($pageTitle, $content);
?>
