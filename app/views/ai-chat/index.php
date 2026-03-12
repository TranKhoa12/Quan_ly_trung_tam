<?php
require_once __DIR__ . '/../layouts/main.php';
ob_start();

$baseUrl = (function () {
    $s = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
    return rtrim($s, '/');
})();
$url = fn(string $p) => ($baseUrl ?: '') . '/' . ltrim($p, '/');
?>

<?= pageHeader(
    'Trợ lý AI — Cấu hình',
    'Tài liệu tri thức và ghi chú bổ sung cho AI Chat',
    '<button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalUploadKnowledge">
        <i class="fas fa-upload me-2"></i>Upload tài liệu tri thức
    </button>'
) ?>

<div class="p-3">

<!--  Flash messages  -->
<?php if (!empty($_SESSION['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($_SESSION['success']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['success']); ?>
<?php endif; ?>
<?php if (!empty($_SESSION['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="fas fa-exclamation-triangle me-2"></i><?= htmlspecialchars($_SESSION['error']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>

<!--  Info cards  -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="stats-card border-primary">
            <div class="card-body text-center">
                <i class="fas fa-robot fs-2 text-primary mb-2"></i>
                <div class="fw-semibold">Trạng thái AI</div>
                <?php
                $aiConfig = require CONFIG_PATH . '/ai.php';
                $hasKey   = !empty($aiConfig['api_key']);
                ?>
                <span class="badge <?= $hasKey ? 'bg-success' : 'bg-danger' ?> mt-1">
                    <?= $hasKey ? '● Đã cấu hình' : '● Chưa có API key' ?>
                </span>
                <?php if ($hasKey): ?>
                    <div class="small text-muted mt-1"><?= htmlspecialchars($aiConfig['model'] ?? '') ?></div>
                <?php else: ?>
                    <div class="small text-muted mt-1">Thêm AI_API_KEY vào .env</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-card border-success">
            <div class="card-body text-center">
                <i class="fas fa-book fs-2 text-success mb-2"></i>
                <div class="fw-semibold">Khóa học trong hệ thống</div>
                <div class="fw-bold fs-5 text-success mt-1"><?= (int)$coursesCount ?> khóa</div>
                <div class="small text-muted">
                    AI tự lấy dữ liệu qua hàm <code>get_courses_list</code><br>
                    <a href="<?= $url('courses') ?>">Quản lý khóa học →</a>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-card border-warning">
            <div class="card-body text-center">
                <i class="fas fa-file-alt fs-2 text-warning mb-2"></i>
                <div class="fw-semibold">Tài liệu tri thức</div>
                <div class="fw-bold fs-5 text-warning mt-1"><?= count($knowledgeFiles) ?> file</div>
                <div class="small text-muted">CSV, Excel, TXT — AI đọc tự động</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-card border-info">
            <div class="card-body text-center">
                <i class="fas fa-sticky-note fs-2 text-info mb-2"></i>
                <div class="fw-semibold">Ghi chú bổ sung</div>
                <?php $noteLen = mb_strlen($notes ?? ''); ?>
                <div class="fw-bold fs-5 text-info mt-1"><?= number_format($noteLen) ?> ký tự</div>
                <div class="small text-muted">Thông tin thêm cho AI (tối đa 10.000 ký tự)</div>
            </div>
        </div>
    </div>
</div>

<!--  Tài liệu tri thức  -->
<div class="stats-card mb-4">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0"><i class="fas fa-file-alt me-2 text-warning"></i>Tài liệu tri thức</h5>
            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalUploadKnowledge">
                <i class="fas fa-upload me-1"></i>Upload file
            </button>
        </div>
        <p class="text-muted small mb-3">
            Upload file chứa dữ liệu ngoài (bảng giá, quy chế, FAQ, danh mục...) — hỗ trợ CSV, Excel, TXT, PDF.
            AI sẽ tự đọc và sử dụng khi trả lời. Mỗi file có thể upload lại để cập nhật.
        </p>

        <?php if (empty($knowledgeFiles)): ?>
            <div class="text-center py-4 text-muted">
                <i class="fas fa-folder-open fs-1 mb-3 d-block opacity-25"></i>
                <p class="mb-2">Chưa có tài liệu nào</p>
                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalUploadKnowledge">
                    <i class="fas fa-upload me-1"></i>Upload tài liệu đầu tiên
                </button>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover table-sm align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Tên file</th>
                            <th style="width:80px">Loại</th>
                            <th style="width:80px">Kích thước</th>
                            <th style="width:140px">Cập nhật</th>
                            <th style="width:80px" class="text-center">Xóa</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($knowledgeFiles as $kf): ?>
                            <tr>
                                <td>
                                    <i class="fas fa-file-<?= in_array($kf['ext'], ['xlsx','xls']) ? 'excel text-success' : ($kf['ext'] === 'csv' ? 'csv text-success' : ($kf['ext'] === 'pdf' ? 'pdf text-danger' : 'alt text-secondary')) ?> me-2"></i>
                                    <?= htmlspecialchars($kf['name']) ?>
                                </td>
                                <td><span class="badge bg-secondary"><?= strtoupper($kf['ext']) ?></span></td>
                                <td class="text-muted small"><?= $kf['size'] ?></td>
                                <td class="text-muted small"><?= $kf['modified'] ?></td>
                                <td class="text-center">
                                    <form method="POST" action="<?= $url('ai-chat/delete-knowledge') ?>"
                                          onsubmit="return confirm('Xóa tài liệu «<?= htmlspecialchars(addslashes($kf['name'])) ?>»?')">
                                        <input type="hidden" name="filename" value="<?= htmlspecialchars($kf['name']) ?>">
                                        <button type="submit" class="btn btn-xs btn-outline-danger" title="Xóa">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!--  Ghi chú bổ sung  -->
<div class="stats-card mb-4">
    <div class="card-body">
        <h5 class="mb-3"><i class="fas fa-lightbulb me-2 text-warning"></i>Ghi chú bổ sung cho AI</h5>
        <p class="text-muted small mb-3">
            Thêm thông tin mà database không có: chính sách học, ưu đãi, quy trình đăng ký,
            câu hỏi thường gặp, địa chỉ, giờ làm việc,... AI sẽ dùng nội dung này khi trả lời người dùng.
        </p>
        <form method="POST" action="<?= $url('ai-chat/save-notes') ?>">
            <textarea name="notes" class="form-control font-monospace"
                      rows="12" maxlength="10000"
                      placeholder="Ví dụ:&#10;- Chính sách hoàn tiền: Hoàn 100% nếu nghỉ trong tuần đầu tiên.&#10;- Ưu đãi học viên giới thiệu: Giảm 10% học phí.&#10;- Giờ học: Sáng 8h-11h, Chiều 14h-17h, Tối 18h-21h.&#10;- Địa chỉ: 123 Đường ABC, Quận 1, TP.HCM. Hotline: 0901 234 567."
                      id="notesArea"><?= htmlspecialchars($notes ?? '') ?></textarea>
            <div class="d-flex justify-content-between align-items-center mt-2">
                <small class="text-muted">
                    <span id="noteCharCount"><?= mb_strlen($notes ?? '') ?></span>/10.000 ký tự
                </small>
                <button type="submit" class="btn btn-warning">
                    <i class="fas fa-save me-2"></i>Lưu ghi chú
                </button>
            </div>
        </form>
    </div>
</div>

</div><!-- /p-3 -->

<!-- ================================================================
     MODAL: Upload tài liệu tri thức
     ================================================================ -->
<div class="modal fade" id="modalUploadKnowledge" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-upload me-2"></i>Upload tài liệu tri thức</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="<?= $url('ai-chat/upload-knowledge') ?>" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="alert alert-info small mb-3">
                        <i class="fas fa-info-circle me-1"></i>
                        Upload file chứa dữ liệu ngoài cho AI: bảng giá, FAQ, quy chế, danh mục,...<br>
                        Hỗ trợ: CSV, Excel, TXT và PDF (text-based). Nếu đã có file cùng tên, file cũ sẽ bị ghi đè.
                    </div>

                    <div id="knowledgeDropZone"
                         class="border border-2 border-dashed rounded-3 p-4 text-center mb-3"
                         style="cursor:pointer; transition:background .2s"
                         onclick="document.getElementById('knowledgeFileInput').click()">
                        <i class="fas fa-cloud-upload-alt fs-2 text-primary mb-2 d-block"></i>
                        <div id="knowledgeDropText">Kéo thả file vào đây hoặc <u>click để chọn</u></div>
                        <small class="text-muted">CSV, XLSX, XLS, TXT, PDF — tối đa 5MB</small>
                    </div>
                    <input type="file" id="knowledgeFileInput" name="knowledge_file"
                           accept=".csv,.xlsx,.xls,.txt,.pdf" class="d-none" required>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary" id="knowledgeSubmitBtn" disabled>
                        <i class="fas fa-upload me-2"></i>Upload
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Notes char count
(function () {
    const ta  = document.getElementById('notesArea');
    const cnt = document.getElementById('noteCharCount');
    if (!ta || !cnt) return;
    ta.addEventListener('input', function () { cnt.textContent = this.value.length; });
})();

// Knowledge file drop zone
(function () {
    const zone      = document.getElementById('knowledgeDropZone');
    const input     = document.getElementById('knowledgeFileInput');
    const label     = document.getElementById('knowledgeDropText');
    const submitBtn = document.getElementById('knowledgeSubmitBtn');
    if (!zone || !input) return;

    const ALLOWED = ['csv', 'xlsx', 'xls', 'txt', 'pdf'];

    function applyFile(file) {
        if (!file) return;
        const ext = file.name.split('.').pop().toLowerCase();
        if (!ALLOWED.includes(ext)) { alert('Chỉ chấp nhận CSV, Excel hoặc TXT.'); return; }
        if (file.size > 5 * 1024 * 1024) { alert('File quá lớn (tối đa 5MB).'); return; }
        label.innerHTML    = '<i class="fas fa-file-check me-2 text-success"></i><strong>' + file.name + '</strong>';
        submitBtn.disabled = false;
        zone.style.background = '#f0fdf4';
    }

    input.addEventListener('change', function () { applyFile(this.files[0]); });
    zone.addEventListener('dragover', e => { e.preventDefault(); zone.style.background = '#ede9fe'; });
    zone.addEventListener('dragleave', () => { zone.style.background = ''; });
    zone.addEventListener('drop', e => {
        e.preventDefault();
        zone.style.background = '';
        if (e.dataTransfer.files.length) {
            const dt = new DataTransfer();
            dt.items.add(e.dataTransfer.files[0]);
            input.files = dt.files;
            applyFile(e.dataTransfer.files[0]);
        }
    });
})();
</script>

<?php
$content = ob_get_clean();
useModernLayout('Trợ lý AI  Cấu hình', $content);