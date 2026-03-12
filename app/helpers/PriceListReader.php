<?php

use PhpOffice\PhpSpreadsheet\IOFactory;

/**
 * PriceListReader
 * Đọc file bảng giá (.xlsx hoặc .csv) và chuyển thành text context cho AI
 */
class PriceListReader
{
    /**
     * Đọc file và trả về chuỗi text để đưa vào system prompt
     */
    public function getContextText(string $filePath): string
    {
        if (!file_exists($filePath)) {
            return 'Chưa có file bảng giá.';
        }

        $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        try {
            $sheets = ($ext === 'csv')
                ? $this->readCsv($filePath)
                : $this->readExcel($filePath);

            return $this->formatAsText($sheets);
        } catch (Throwable $e) {
            error_log('[PriceListReader] Error reading file: ' . $e->getMessage());
            return 'Không thể đọc file bảng giá.';
        }
    }

    private function readExcel(string $filePath): array
    {
        $spreadsheet = IOFactory::load($filePath);
        $sheets = [];

        foreach ($spreadsheet->getWorksheetIterator() as $sheet) {
            $rows = $sheet->toArray(null, true, true, false);
            $sheets[] = ['title' => $sheet->getTitle(), 'rows' => $rows];
        }

        return $sheets;
    }

    private function readCsv(string $filePath): array
    {
        $rows = [];
        $handle = fopen($filePath, 'r');
        if ($handle === false) {
            return [];
        }

        // Detect BOM (UTF-8)
        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            rewind($handle);
        }

        while (($row = fgetcsv($handle, 0, ',')) !== false) {
            $rows[] = array_map('trim', $row);
        }
        fclose($handle);

        return [['title' => 'Bảng giá', 'rows' => $rows]];
    }

    private function formatAsText(array $sheets): string
    {
        $output = [];

        foreach ($sheets as $sheet) {
            $rows = $sheet['rows'];
            if (empty($rows)) {
                continue;
            }

            $output[] = "=== {$sheet['title']} ===";

            // Hàng đầu tiên là tiêu đề cột
            $headers = array_shift($rows);

            foreach ($rows as $row) {
                // Bỏ qua dòng hoàn toàn trống
                if (empty(array_filter(array_map('strval', $row)))) {
                    continue;
                }

                $parts = [];
                foreach ($row as $i => $value) {
                    $val = trim((string)$value);
                    $hdr = trim((string)($headers[$i] ?? ''));
                    if ($val !== '' && $hdr !== '') {
                        $parts[] = $hdr . ': ' . $val;
                    }
                }

                if (!empty($parts)) {
                    $output[] = implode(' | ', $parts);
                }
            }

            $output[] = ''; // Dòng trống giữa các sheet
        }

        return trim(implode("\n", $output));
    }
}
