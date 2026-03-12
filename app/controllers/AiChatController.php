<?php

/**
 * AiChatController
 * Quản lý ghi chú bổ sung và tài liệu tri thức cho trợ lý AI
 * Chỉ admin
 */
class AiChatController extends BaseController
{
    private string $dataDir;
    private string $notesPath;
    private string $knowledgeDir;

    public function __construct()
    {
        parent::__construct();
        $this->requireAuth();
        $this->requireAdmin();

        $this->dataDir      = BASE_PATH . '/data';
        $this->notesPath    = $this->dataDir . '/ai_notes.txt';
        $this->knowledgeDir = $this->dataDir . '/knowledge';
    }

    // ----------------------------------------------------------------
    // GET /ai-chat/manage  — Trang quản lý
    // ----------------------------------------------------------------
    public function manage(): void
    {
        $notes          = file_exists($this->notesPath)
            ? file_get_contents($this->notesPath) : '';
        $coursesCount   = $this->db->fetch("SELECT COUNT(*) AS cnt FROM courses WHERE status = 'active'")['cnt'] ?? 0;
        $knowledgeFiles = $this->listKnowledgeFiles();

        $this->view('ai-chat/index', [
            'notes'          => $notes,
            'coursesCount'   => (int)$coursesCount,
            'knowledgeFiles' => $knowledgeFiles,
        ]);
    }

    // ----------------------------------------------------------------
    // POST /ai-chat/save-notes  — Lưu ghi chú bổ sung
    // ----------------------------------------------------------------
    public function saveNotes(): void
    {
        $notes = mb_substr(trim($_POST['notes'] ?? ''), 0, 10000);

        if (!is_dir($this->dataDir)) {
            mkdir($this->dataDir, 0755, true);
        }

        file_put_contents($this->notesPath, $notes);

        $_SESSION['success'] = 'Đã lưu ghi chú bổ sung.';
        $this->redirect($this->buildUrl('ai-chat/manage'));
    }

    // ----------------------------------------------------------------
    // POST /ai-chat/upload-knowledge  — Upload tài liệu tri thức
    // ----------------------------------------------------------------
    public function uploadKnowledgeFile(): void
    {
        if (empty($_FILES['knowledge_file']) || $_FILES['knowledge_file']['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['error'] = 'Lỗi upload file. Vui lòng thử lại.';
            $this->redirect($this->buildUrl('ai-chat/manage'));
            return;
        }

        $file     = $_FILES['knowledge_file'];
        $origName = basename($file['name']);
        $ext      = strtolower(pathinfo($origName, PATHINFO_EXTENSION));

        if (!in_array($ext, ['csv', 'xlsx', 'xls', 'txt', 'pdf'])) {
            $_SESSION['error'] = 'Chỉ chấp nhận file CSV, Excel (.xlsx, .xls), TXT hoặc PDF.';
            $this->redirect($this->buildUrl('ai-chat/manage'));
            return;
        }

        if ($file['size'] > 5 * 1024 * 1024) {
            $_SESSION['error'] = 'File quá lớn (tối đa 5MB).';
            $this->redirect($this->buildUrl('ai-chat/manage'));
            return;
        }

        if (!is_dir($this->knowledgeDir)) {
            mkdir($this->knowledgeDir, 0755, true);
        }

        // Tên file an toàn: chỉ giữ chữ/số/gạch ngang/chấm
        $safeName = preg_replace('/[^a-zA-Z0-9_\-\.]/u', '_', $origName);
        $destPath = $this->knowledgeDir . '/' . $safeName;

        if (!move_uploaded_file($file['tmp_name'], $destPath)) {
            $_SESSION['error'] = 'Không thể lưu file. Kiểm tra quyền ghi thư mục data/knowledge.';
            $this->redirect($this->buildUrl('ai-chat/manage'));
            return;
        }

        $_SESSION['success'] = 'Đã upload tài liệu "' . $safeName . '" thành công.';
        $this->redirect($this->buildUrl('ai-chat/manage'));
    }

    // ----------------------------------------------------------------
    // POST /ai-chat/delete-knowledge  — Xóa tài liệu tri thức
    // ----------------------------------------------------------------
    public function deleteKnowledgeFile(): void
    {
        $filename = basename($_POST['filename'] ?? '');

        if ($filename === '' || !preg_match('/^[a-zA-Z0-9_\-\.]+$/', $filename)) {
            $_SESSION['error'] = 'Tên file không hợp lệ.';
            $this->redirect($this->buildUrl('ai-chat/manage'));
            return;
        }

        $path = $this->knowledgeDir . '/' . $filename;

        if (!file_exists($path)) {
            $_SESSION['error'] = 'File không tồn tại.';
            $this->redirect($this->buildUrl('ai-chat/manage'));
            return;
        }

        unlink($path);
        $_SESSION['success'] = 'Đã xóa tài liệu "' . $filename . '".';
        $this->redirect($this->buildUrl('ai-chat/manage'));
    }

    // ----------------------------------------------------------------
    // Liệt kê các file tri thức
    // ----------------------------------------------------------------
    private function listKnowledgeFiles(): array
    {
        if (!is_dir($this->knowledgeDir)) {
            return [];
        }

        $files = [];
        foreach (glob($this->knowledgeDir . '/*') as $filePath) {
            $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
            if (!in_array($ext, ['csv', 'xlsx', 'xls', 'txt', 'pdf'])) {
                continue;
            }
            $files[] = [
                'name'     => basename($filePath),
                'ext'      => $ext,
                'size'     => $this->formatBytes(filesize($filePath)),
                'modified' => date('d/m/Y H:i', filemtime($filePath)),
            ];
        }

        usort($files, fn($a, $b) => strcmp($a['name'], $b['name']));
        return $files;
    }

    private function formatBytes(int $bytes): string
    {
        if ($bytes < 1024) return $bytes . ' B';
        if ($bytes < 1048576) return round($bytes / 1024, 1) . ' KB';
        return round($bytes / 1048576, 1) . ' MB';
    }

    // ----------------------------------------------------------------
    // Helpers
    // ----------------------------------------------------------------
    private function buildUrl(string $path): string
    {
        $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
        $base      = rtrim($scriptDir, '/');
        return ($base ?: '') . '/' . ltrim($path, '/');
    }
}
