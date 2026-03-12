<?php

/**
 * PdfTextExtractor
 * Trích xuất text thuần từ file PDF (text-based, không phải scan ảnh).
 * Hỗ trợ FlateDecode streams, parenthesis strings và hex UTF-16BE (tiếng Việt).
 */
class PdfTextExtractor
{
    /**
     * Trả về chuỗi text từ file PDF, hoặc thông báo lỗi.
     */
    public function extract(string $filePath): string
    {
        if (!file_exists($filePath)) {
            return '';
        }

        $raw = file_get_contents($filePath);
        if ($raw === false || $raw === '') {
            return '';
        }

        // Thu thập text từ tất cả streams
        $allText = '';
        $allText .= $this->extractFromStreams($raw);

        $cleaned = $this->cleanText($allText);

        // Fallback: nếu không trích được gì, thử brute-force string scan
        if (mb_strlen($cleaned) < 30) {
            $cleaned = $this->bruteForceScan($raw);
        }

        return $cleaned;
    }

    // ----------------------------------------------------------------
    // Giải nén và parse streams
    // ----------------------------------------------------------------
    private function extractFromStreams(string $raw): string
    {
        $text = '';
        $offset = 0;

        while (($sStart = strpos($raw, 'stream', $offset)) !== false) {
            // Tìm vị trí bắt đầu dữ liệu stream (sau \r\n hoặc \n)
            $sData = $sStart + 6;
            if (isset($raw[$sData]) && $raw[$sData] === "\r") $sData++;
            if (isset($raw[$sData]) && $raw[$sData] === "\n") $sData++;

            $eStart = strpos($raw, 'endstream', $sData);
            if ($eStart === false) break;

            $streamBytes = substr($raw, $sData, $eStart - $sData);
            // Trim trailing \r\n trước endstream
            $streamBytes = rtrim($streamBytes, "\r\n");

            // Kiểm tra FlateDecode trong dictionary trước stream
            $dictChunk = substr($raw, max(0, $sStart - 512), 512);
            $isFlate   = strpos($dictChunk, 'FlateDecode') !== false
                      || strpos($dictChunk, '/Fl ')          !== false;

            if ($isFlate) {
                $decoded = @gzuncompress($streamBytes);
                if ($decoded === false) {
                    // Thử bỏ 2 byte zlib header
                    $decoded = @gzinflate(substr($streamBytes, 2));
                }
                if ($decoded !== false) {
                    $streamBytes = $decoded;
                }
            }

            $text .= $this->parseTextBlocks($streamBytes);
            $offset = $eStart + 9;
        }

        return $text;
    }

    // ----------------------------------------------------------------
    // Parse text từ BT…ET blocks trong một stream
    // ----------------------------------------------------------------
    private function parseTextBlocks(string $stream): string
    {
        $text = '';

        if (!preg_match_all('/BT\s*(.*?)\s*ET/s', $stream, $blocks)) {
            return '';
        }

        foreach ($blocks[1] as $block) {
            // Theo dõi dãy ký tự trong block, thêm space sau mỗi lệnh Td/TD/T*
            $blockText = '';

            // 1. (string) Tj
            if (preg_match_all('/\(([^)\\\\]*(?:\\\\.[^)\\\\]*)*)\)\s*Tj\b/s', $block, $m)) {
                foreach ($m[1] as $s) {
                    $blockText .= $this->decodePdfString($s) . ' ';
                }
            }

            // 2. [(string/-num...)] TJ
            if (preg_match_all('/\[(.*?)\]\s*TJ\b/s', $block, $m)) {
                foreach ($m[1] as $tj) {
                    // parenthesis parts
                    if (preg_match_all('/\(([^)\\\\]*(?:\\\\.[^)\\\\]*)*)\)/s', $tj, $parts)) {
                        foreach ($parts[1] as $p) {
                            $blockText .= $this->decodePdfString($p);
                        }
                    }
                    // hex parts inside TJ
                    if (preg_match_all('/<([0-9a-fA-F\s]+)>/s', $tj, $hexParts)) {
                        foreach ($hexParts[1] as $h) {
                            $blockText .= $this->decodeHexString($h);
                        }
                    }
                    $blockText .= ' ';
                }
            }

            // 3. <hex> Tj
            if (preg_match_all('/<([0-9a-fA-F\s]+)>\s*Tj\b/s', $block, $m)) {
                foreach ($m[1] as $h) {
                    $blockText .= $this->decodeHexString($h) . ' ';
                }
            }

            // Mỗi lệnh Td/TD/T* → xuống dòng
            $blockText = preg_replace('/\d*\.?\d+\s+\d*\.?\d+\s+Td\b/', "\n", $blockText);
            $blockText = preg_replace('/T[D\*]\b/', "\n", $blockText);

            $text .= $blockText . "\n";
        }

        return $text;
    }

    // ----------------------------------------------------------------
    // Giải mã chuỗi PDF dạng ngoặc đơn: unescape \n \r \t \( \) \ooo
    // ----------------------------------------------------------------
    private function decodePdfString(string $s): string
    {
        // Octal escapes
        $s = preg_replace_callback('/\\\\([0-7]{1,3})/', function ($m) {
            return chr(octdec($m[1]));
        }, $s);
        // Named escapes
        $s = str_replace(
            ['\\n', '\\r', '\\t', '\\b', '\\f', '\\(', '\\)', '\\\\'],
            ["\n",  "\r",  "\t",  "\x08", "\x0C", '(',   ')',   '\\'],
            $s
        );
        return $s;
    }

    // ----------------------------------------------------------------
    // Giải mã hex string: <FEFF...> → UTF-16BE → UTF-8
    // ----------------------------------------------------------------
    private function decodeHexString(string $hex): string
    {
        $hex = preg_replace('/\s+/', '', $hex);
        if ($hex === '') return '';

        if (strlen($hex) % 2 !== 0) $hex .= '0';

        $bytes = pack('H*', $hex);

        // BOM UTF-16BE (FEFF) hoặc độ dài chẵn gợi ý UTF-16
        if (strlen($bytes) >= 2 && $bytes[0] === "\xFE" && $bytes[1] === "\xFF") {
            return mb_convert_encoding(substr($bytes, 2), 'UTF-8', 'UTF-16BE');
        }
        if (strlen($bytes) % 2 === 0 && strlen($hex) >= 4) {
            // Thử UTF-16BE
            $converted = @mb_convert_encoding($bytes, 'UTF-8', 'UTF-16BE');
            if ($converted !== false) {
                // Kiểm tra kết quả trông như text (không phải binary rác)
                $printable = preg_replace('/[^\x09\x0A\x0D\x20-\x7E]/u', '', $converted);
                if (strlen($printable) > strlen($converted) * 0.3) {
                    return $converted;
                }
            }
        }

        // Fallback: Latin-1
        return mb_convert_encoding($bytes, 'UTF-8', 'ISO-8859-1');
    }

    // ----------------------------------------------------------------
    // Brute-force: quét tất cả chuỗi ASCII ≥ 4 ký tự in được
    // ----------------------------------------------------------------
    private function bruteForceScan(string $raw): string
    {
        preg_match_all('/(?:[\x20-\x7E]){4,}/', $raw, $m);
        $lines = array_filter($m[0], function ($s) {
            // Lọc những dòng trông như text thực (không phải header PDF)
            return !preg_match('/^(obj|endobj|xref|trailer|PDF|stream|endstream)$/', trim($s));
        });
        return implode("\n", array_unique($lines));
    }

    // ----------------------------------------------------------------
    // Dọn dẹp text cuối cùng
    // ----------------------------------------------------------------
    private function cleanText(string $text): string
    {
        // Bỏ ký tự không in được (giữ UTF-8 tiếng Việt)
        $text = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $text);
        // Collapse khoảng trắng
        $text = preg_replace('/[ \t]+/', ' ', $text);
        $text = preg_replace('/\n{3,}/', "\n\n", $text);
        return trim($text);
    }
}
