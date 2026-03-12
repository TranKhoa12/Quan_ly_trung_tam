<?php

/**
 * ChatController
 * Xử lý API endpoint POST /api/chat
 *
 * Luồng: User message → Gemini (với Function Calling) → nếu AI gọi tool
 *        → ChatContextBuilder thực thi query DB → trả kết quả lại AI → câu trả lời cuối
 */
class ChatController extends BaseController
{
    private array $aiConfig;

    public function __construct()
    {
        parent::__construct();
        $this->requireAuth();
        $this->aiConfig = require CONFIG_PATH . '/ai.php';
    }

    // ----------------------------------------------------------------
    // POST /api/chat
    // ----------------------------------------------------------------
    public function message(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['error' => 'Method not allowed']);
            exit;
        }

        $raw   = file_get_contents('php://input');
        $input = json_decode($raw, true);

        $userMessage = trim((string)($input['message'] ?? ''));
        $rawHistory  = is_array($input['history'] ?? null) ? $input['history'] : [];

        // Validate
        if ($userMessage === '') {
            echo json_encode(['error' => 'Message không được để trống.']);
            exit;
        }
        if (mb_strlen($userMessage) > 500) {
            echo json_encode(['error' => 'Message quá dài (tối đa 500 ký tự).']);
            exit;
        }

        if (empty($this->aiConfig['api_key'])) {
            echo json_encode([
                'reply' => 'Tính năng AI chưa được cấu hình API key. Vui lòng liên hệ quản trị viên.'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        // Lấy thông tin người dùng từ session
        $userId   = (int)($_SESSION['user_id'] ?? 0);
        $userRole = in_array($_SESSION['role'] ?? '', ['admin', 'staff']) ? $_SESSION['role'] : 'staff';
        $userName = htmlspecialchars($_SESSION['full_name'] ?? 'Người dùng', ENT_QUOTES);

        // Làm sạch lịch sử hội thoại (tối đa 10 lượt gần nhất)
        $history = $this->sanitizeHistory($rawHistory);

        // Đọc ghi chú bổ sung
        $extraNotes = $this->getExtraNotes();

        // Gọi AI
        $reply = $this->callGemini($userMessage, $extraNotes, $history, $userId, $userRole, $userName);

        echo json_encode(['reply' => $reply], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // ----------------------------------------------------------------
    // Gemini API với Function Calling (2 vòng tối đa)
    // ----------------------------------------------------------------
    private function callGemini(
        string $message,
        string $extraNotes,
        array  $history,
        int    $userId,
        string $userRole,
        string $userName
    ): string {
        $fallback = 'Xin lỗi, tôi không thể trả lời lúc này. Vui lòng thử lại hoặc liên hệ trực tiếp với trung tâm.';

        try {
            $model   = $this->aiConfig['model'] ?? 'gemini-2.5-flash-lite';
            $apiKeys = array_filter((array)($this->aiConfig['api_keys'] ?? [$this->aiConfig['api_key']]));
            if (empty($apiKeys)) {
                return 'Chưa cấu hình API key. Vui lòng liên hệ admin.';
            }

            $systemPrompt = $this->buildSystemPrompt($extraNotes, $userName, $userRole);
            $contents     = $this->buildContents($history, $message);

            $body = [
                'system_instruction' => ['parts' => [['text' => $systemPrompt]]],
                'contents'           => $contents,
                'tools'              => [['function_declarations' => $this->getToolDeclarations($userRole)]],
                'tool_config'        => ['function_calling_config' => ['mode' => 'AUTO']],
                'generationConfig'   => ['temperature' => 0.2, 'maxOutputTokens' => 800],
            ];

            // Vòng 1: thử từng key cho đến khi thành công
            $res1   = null;
            $usedKey = null;
            foreach ($apiKeys as $apiKey) {
                $url  = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";
                $res1 = $this->sendRequest($url, $body);
                if ($res1['ok'] || $res1['code'] !== 429) {
                    $usedKey = $apiKey;
                    break;
                }
                error_log("[ChatController] Key quota exhausted, trying next key...");
            }
            if (!$res1['ok']) {
                return $this->geminiErrorMessage($res1['code'], $res1['body']);
            }

            $data1     = json_decode($res1['body'], true);
            $candidate = $data1['candidates'][0] ?? null;
            if (!$candidate) {
                return $fallback;
            }

            $parts = $candidate['content']['parts'] ?? [];

            // Kiểm tra AI có gọi function không
            $funcCallPart = null;
            foreach ($parts as $part) {
                if (isset($part['functionCall'])) {
                    $funcCallPart = $part['functionCall'];
                    break;
                }
            }

            // Không có function call → trả lời trực tiếp
            if ($funcCallPart === null) {
                return $this->extractText($parts) ?: $fallback;
            }

            // Có function call → thực thi
            $funcName   = (string)($funcCallPart['name'] ?? '');
            $funcArgs   = (array)($funcCallPart['args'] ?? []);
            $funcResult = $this->executeFunction($funcName, $funcArgs, $userId, $userRole);

            // Vòng 2: gửi kết quả function về AI
            $contents[] = [
                'role'  => 'model',
                'parts' => [['functionCall' => ['name' => $funcName, 'args' => (object)$funcArgs]]],
            ];
            $contents[] = [
                'role'  => 'user',
                'parts' => [[
                    'functionResponse' => [
                        'name'     => $funcName,
                        'response' => ['result' => $funcResult ?: (object)[]],
                    ],
                ]],
            ];

            $body['contents'] = $contents;
            $url2 = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$usedKey}";
            $res2 = $this->sendRequest($url2, $body);
            if (!$res2['ok']) {
                return $this->geminiErrorMessage($res2['code'], $res2['body']);
            }

            $data2 = json_decode($res2['body'], true);
            $parts2 = $data2['candidates'][0]['content']['parts'] ?? [];

            return $this->extractText($parts2) ?: $fallback;

        } catch (Throwable $e) {
            error_log('[ChatController] Gemini error: ' . $e->getMessage());
            return $fallback;
        }
    }

    // ----------------------------------------------------------------
    // Thực thi function (ChatContextBuilder)
    // ----------------------------------------------------------------
    private function executeFunction(string $funcName, array $funcArgs, int $userId, string $userRole): array
    {
        require_once APP_PATH . '/helpers/ChatContextBuilder.php';
        $builder = new ChatContextBuilder($this->db, $userId, $userRole);
        return $builder->execute($funcName, $funcArgs);
    }

    // ----------------------------------------------------------------
    // Đọc ghi chú bổ sung + tài liệu tri thức cho AI
    // ----------------------------------------------------------------
    private function getExtraNotes(): string
    {
        $parts = [];

        // 1. Ghi chú tự do của admin
        $notesPath = BASE_PATH . '/data/ai_notes.txt';
        if (file_exists($notesPath)) {
            $n = trim(file_get_contents($notesPath));
            if ($n !== '') {
                $parts[] = $n;
            }
        }

        // 2. Các file tri thức trong data/knowledge/
        $knowledgeDir = BASE_PATH . '/data/knowledge';
        if (is_dir($knowledgeDir)) {
            foreach (glob($knowledgeDir . '/*') as $filePath) {
                $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
                if (!in_array($ext, ['csv', 'xlsx', 'xls', 'txt', 'pdf'])) {
                    continue;
                }
                try {
                    if ($ext === 'txt') {
                        $text = trim(file_get_contents($filePath));
                    } elseif ($ext === 'pdf') {
                        require_once APP_PATH . '/helpers/PdfTextExtractor.php';
                        $text = (new PdfTextExtractor())->extract($filePath);
                    } else {
                        require_once APP_PATH . '/helpers/PriceListReader.php';
                        $text = (new PriceListReader())->getContextText($filePath);
                    }
                    if ($text !== '') {
                        $parts[] = '--- ' . basename($filePath) . " ---\n" . $text;
                    }
                } catch (Throwable $e) {
                    error_log('[ChatController] Knowledge file error: ' . $e->getMessage());
                }
            }
        }

        return implode("\n\n", $parts);
    }

    // ----------------------------------------------------------------
    // Xây dựng System Prompt
    // ----------------------------------------------------------------
    private function buildSystemPrompt(string $extraNotes, string $userName, string $userRole): string
    {
        $roleLabel = $userRole === 'admin' ? 'Quản trị viên' : 'Nhân viên';
        $now = date('d/m/Y H:i', time());

        if ($userRole === 'admin') {
            $capText = "Với quyền Quản trị viên, bạn có thể trả lời về TẤT CẢ dữ liệu trung tâm:\n"
                . "- Học phí, danh sách khóa học → gọi get_courses_list\n"
                . "- Doanh thu, tài chính theo ngày/tuần/tháng/năm → gọi get_revenue_stats\n"
                . "- Tìm kiếm học viên theo tên/SĐT → gọi search_students\n"
                . "- Báo cáo học viên đến trung tâm theo ngày → gọi get_recent_reports\n"
                . "- Thống kê tổng quan toàn hệ thống → gọi get_dashboard_stats\n"
                . "- Chứng nhận: số chờ duyệt, đã duyệt, bị hủy → gọi get_certificates_stats\n"
                . "- Danh sách nhân viên/tài khoản → gọi get_staff_list\n"
                . "- Phiếu hoàn thành học viên → gọi get_completion_slips_stats";
        } else {
            $capText = "Với quyền Nhân viên, bạn chỉ xem được dữ liệu của bản thân:\n"
                . "- Học phí, danh sách khóa học\n"
                . "- Báo cáo và doanh thu cá nhân\n"
                . "- Tìm kiếm thông tin học viên";
        }

        $prompt = <<<PROMPT
Bạn là trợ lý AI nội bộ của trung tâm đào tạo, hỗ trợ {$roleLabel} "{$userName}".
Ngày giờ hiện tại: {$now}.

{$capText}

Khi cần dữ liệu thực tế, hãy gọi hàm tương ứng trước khi trả lời.
Trả lời bằng tiếng Việt, ngắn gọn, thân thiện và chính xác.
Nếu thông tin không có trong dữ liệu, hãy nói rõ và đề nghị liên hệ trực tiếp.
PROMPT;

        if ($extraNotes !== '') {
            $prompt .= "\n\nTHÔNG TIN BỔ SUNG TỪ ADMIN:\n{$extraNotes}";
        }

        return $prompt;
    }

    // ----------------------------------------------------------------
    // Khai báo các tools cho Gemini
    // ----------------------------------------------------------------
    private function getToolDeclarations(string $userRole = 'staff'): array
    {
        $tools = [
            [
                'name'        => 'get_dashboard_stats',
                'description' => 'Lấy thống kê tổng quan: số học viên theo trạng thái, doanh thu hôm nay và tháng này, số báo cáo hôm nay. Dùng khi hỏi về tổng quan, tình hình chung.',
                'parameters'  => [
                    'type'       => 'object',
                    'properties' => (object)[],
                ],
            ],
            [
                'name'        => 'get_revenue_stats',
                'description' => 'Lấy thống kê doanh thu theo kỳ thời gian. Dùng khi hỏi về thu tiền, doanh thu, số tiền, tài chính.',
                'parameters'  => [
                    'type'       => 'object',
                    'properties' => [
                        'period' => [
                            'type'        => 'string',
                            'enum'        => ['today', 'week', 'month', 'year'],
                            'description' => 'Kỳ: today=hôm nay, week=tuần này, month=tháng, year=năm',
                        ],
                        'month' => [
                            'type'        => 'integer',
                            'description' => 'Số tháng 1-12, chỉ dùng khi period=month. Mặc định tháng hiện tại.',
                        ],
                        'year' => [
                            'type'        => 'integer',
                            'description' => 'Năm (vd 2026), dùng khi period=month hoặc year.',
                        ],
                    ],
                    'required' => ['period'],
                ],
            ],
            [
                'name'        => 'search_students',
                'description' => 'Tìm kiếm học viên theo tên hoặc số điện thoại. Dùng khi hỏi về thông tin cụ thể của một học viên.',
                'parameters'  => [
                    'type'       => 'object',
                    'properties' => [
                        'query' => [
                            'type'        => 'string',
                            'description' => 'Tên hoặc số điện thoại cần tìm (tối thiểu 2 ký tự)',
                        ],
                    ],
                    'required' => ['query'],
                ],
            ],
            [
                'name'        => 'get_recent_reports',
                'description' => 'Lấy báo cáo số học viên/khách đến trung tâm theo ngày. Dùng khi hỏi về báo cáo ca, số người đến, số đăng ký.',
                'parameters'  => [
                    'type'       => 'object',
                    'properties' => [
                        'date' => [
                            'type'        => 'string',
                            'description' => 'Ngày cần xem (định dạng YYYY-MM-DD). Bỏ trống để lấy hôm nay.',
                        ],
                    ],
                ],
            ],
            [
                'name'        => 'get_courses_list',
                'description' => 'Lấy danh sách khóa học trong hệ thống từ cơ sở dữ liệu, bao gồm tên, học phí, thời lượng, trạng thái.',
                'parameters'  => [
                    'type'       => 'object',
                    'properties' => (object)[],
                ],
            ],
        ];

        if ($userRole === 'admin') {
            $tools[] = [
                'name'        => 'get_certificates_stats',
                'description' => 'Lấy thống kê chứng nhận: số lượng chờ duyệt, đã duyệt, bị hủy và danh sách gần đây. Dùng khi hỏi về chứng nhận, bằng cấp, phê duyệt.',
                'parameters'  => [
                    'type'       => 'object',
                    'properties' => (object)[],
                ],
            ];
            $tools[] = [
                'name'        => 'get_staff_list',
                'description' => 'Lấy danh sách nhân viên và tài khoản trong hệ thống. Dùng khi hỏi về nhân viên, tài khoản, số lượng người dùng.',
                'parameters'  => [
                    'type'       => 'object',
                    'properties' => (object)[],
                ],
            ];
            $tools[] = [
                'name'        => 'get_completion_slips_stats',
                'description' => 'Lấy thống kê phiếu hoàn thành học viên: tổng số, tháng này, danh sách gần đây. Dùng khi hỏi về phiếu hoàn thành.',
                'parameters'  => [
                    'type'       => 'object',
                    'properties' => (object)[],
                ],
            ];
        }

        return $tools;
    }

    // ----------------------------------------------------------------
    // Helpers
    // ----------------------------------------------------------------
    private function buildContents(array $history, string $newMessage): array
    {
        $contents = [];
        foreach ($history as $turn) {
            $contents[] = [
                'role'  => $turn['role'] === 'user' ? 'user' : 'model',
                'parts' => [['text' => (string)$turn['text']]],
            ];
        }
        $contents[] = ['role' => 'user', 'parts' => [['text' => $newMessage]]];
        return $contents;
    }

    private function sanitizeHistory(array $rawHistory): array
    {
        $clean = [];
        foreach (array_slice($rawHistory, -10) as $turn) {
            $role = $turn['role'] ?? '';
            $text = $turn['text'] ?? '';
            if (in_array($role, ['user', 'bot'], true) && is_string($text) && $text !== '') {
                $clean[] = [
                    'role' => $role,
                    'text' => mb_substr($text, 0, 1000),
                ];
            }
        }
        return $clean;
    }

    private function sendRequest(string $url, array $body): array
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($body, JSON_UNESCAPED_UNICODE),
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);

        $response  = curl_exec($ch);
        $httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError || $response === false) {
            error_log("[ChatController] cURL error: {$curlError}");
            return ['ok' => false, 'code' => 0, 'body' => null];
        }

        if ($httpCode !== 200) {
            error_log("[ChatController] Gemini HTTP {$httpCode}: {$response}");
            return ['ok' => false, 'code' => $httpCode, 'body' => $response];
        }

        return ['ok' => true, 'code' => 200, 'body' => $response];
    }

    private function geminiErrorMessage(int $code, ?string $body): string
    {
        if ($code === 429) {
            return 'Trợ lý AI đang quá tải (vượt giới hạn quota). Vui lòng thử lại sau vài giây.';
        }
        if ($code === 401 || $code === 403) {
            return 'API key AI không hợp lệ hoặc chưa được cấp phép. Liên hệ admin cấu hình lại.';
        }
        if ($code === 404) {
            return 'Model AI không tìm thấy. Vui lòng kiểm tra cấu hình model trong config/ai.php.';
        }
        if ($code === 500 || $code === 503) {
            return 'Máy chủ AI đang gặp sự cố. Vui lòng thử lại sau.';
        }
        if ($body) {
            $json = json_decode($body, true);
            $msg  = $json['error']['message'] ?? null;
            if ($msg) {
                error_log('[ChatController] Gemini error message: ' . $msg);
            }
        }
        return 'Xin lỗi, trợ lý AI không thể trả lời lúc này. Vui lòng thử lại hoặc liên hệ trực tiếp với trung tâm.';
    }

    private function extractText(array $parts): string
    {
        foreach ($parts as $part) {
            if (isset($part['text']) && $part['text'] !== '') {
                return $part['text'];
            }
        }
        return '';
    }
}
