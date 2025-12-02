<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Phiếu lương tháng <?= date('m/Y', strtotime($periodStart)) ?> - <?= htmlspecialchars($staff['full_name']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @media print {
            .no-print { display: none; }
            body { padding: 0; }
        }
        
        .payslip-container {
            max-width: 800px;
            margin: 20px auto;
            padding: 30px;
            background: white;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #4f46e5;
            padding-bottom: 20px;
        }
        
        .header h1 {
            color: #4f46e5;
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .header p {
            margin: 0;
            color: #6c757d;
        }
        
        .info-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        
        .info-label {
            font-weight: 600;
            color: #495057;
        }
        
        .info-value {
            color: #212529;
        }
        
        .shifts-table {
            margin: 20px 0;
        }
        
        .shifts-table th {
            background: #4f46e5;
            color: white;
            padding: 12px;
        }
        
        .shifts-table td {
            padding: 10px;
        }
        
        .total-section {
            background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%);
            color: white;
            padding: 20px;
            border-radius: 8px;
            margin-top: 20px;
        }
        
        .total-amount {
            font-size: 32px;
            font-weight: bold;
            text-align: center;
        }
        
        .signature-section {
            margin-top: 40px;
            display: flex;
            justify-content: space-between;
        }
        
        .signature-box {
            text-align: center;
            width: 45%;
        }
        
        .signature-line {
            border-top: 1px solid #dee2e6;
            margin-top: 60px;
            padding-top: 10px;
        }
        
        @media print {
            .payslip-container {
                margin: 0;
                padding: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="payslip-container">
        <!-- Print Button -->
        <div class="no-print text-end mb-3">
            <button onclick="window.print()" class="btn btn-primary">
                <i class="fas fa-print me-2"></i>In phiếu lương
            </button>
            <button onclick="window.close()" class="btn btn-secondary">
                Đóng
            </button>
        </div>

        <!-- Header -->
        <div class="header">
            <h1>PHIẾU LƯƠNG CA DẠY</h1>
            <p>Tháng <?= date('m/Y', strtotime($periodStart)) ?></p>
            <p><small>Từ ngày <?= date('d/m/Y', strtotime($periodStart)) ?> đến <?= date('d/m/Y', strtotime($periodEnd)) ?></small></p>
        </div>

        <!-- Staff Info -->
        <div class="info-section">
            <div class="info-row">
                <span class="info-label">Họ và tên:</span>
                <span class="info-value"><?= htmlspecialchars($staff['full_name']) ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Email:</span>
                <span class="info-value"><?= htmlspecialchars($staff['email'] ?? 'N/A') ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Số điện thoại:</span>
                <span class="info-value"><?= htmlspecialchars($staff['phone'] ?? 'N/A') ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Ngày in:</span>
                <span class="info-value"><?= date('d/m/Y H:i') ?></span>
            </div>
        </div>

        <!-- Shift Details -->
        <h5 class="mb-3">Chi tiết ca dạy đã duyệt:</h5>
        <table class="table table-bordered shifts-table">
            <thead>
                <tr>
                    <th style="width: 50px;">STT</th>
                    <th>Ngày</th>
                    <th>Ca dạy</th>
                    <th>Thời gian</th>
                    <th class="text-end">Đơn giá</th>
                    <th class="text-end">Số giờ</th>
                    <th class="text-end">Thành tiền</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $index = 1;
                $totalAmount = 0;
                foreach ($shifts as $shift): 
                    // Calculate hours
                    $startTime = $shift['custom_start'] ?? $shift['preset_start'];
                    $endTime = $shift['custom_end'] ?? $shift['preset_end'];
                    $start = strtotime($startTime);
                    $end = strtotime($endTime);
                    $hours = ($end - $start) / 3600;
                    
                    $hourlyRate = (float)($shift['hourly_rate'] ?? 50000);
                    $amount = $hours * $hourlyRate;
                    $totalAmount += $amount;
                ?>
                <tr>
                    <td class="text-center"><?= $index++ ?></td>
                    <td><?= date('d/m/Y', strtotime($shift['shift_date'])) ?></td>
                    <td><?= htmlspecialchars($shift['shift_name'] ?? 'N/A') ?></td>
                    <td><?= date('H:i', $start) ?> - <?= date('H:i', $end) ?></td>
                    <td class="text-end"><?= number_format($hourlyRate, 0, ',', '.') ?></td>
                    <td class="text-end"><?= number_format($hours, 1) ?>h</td>
                    <td class="text-end"><?= number_format($amount, 0, ',', '.') ?> ₫</td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($shifts)): ?>
                <tr>
                    <td colspan="7" class="text-center text-muted">Không có ca dạy nào</td>
                </tr>
                <?php endif; ?>
            </tbody>
            <tfoot class="table-light">
                <tr class="fw-bold">
                    <td colspan="5" class="text-end">TỔNG CỘNG:</td>
                    <td class="text-end"><?= number_format($payroll['total_hours'], 1) ?>h</td>
                    <td class="text-end"><?= number_format($payroll['total_amount'], 0, ',', '.') ?> ₫</td>
                </tr>
            </tfoot>
        </table>

        <!-- Total Amount -->
        <div class="total-section">
            <div class="text-center mb-2">
                <strong>TỔNG TIỀN LƯƠNG</strong>
            </div>
            <div class="total-amount">
                <?= number_format($payroll['total_amount'], 0, ',', '.') ?> ₫
            </div>
            <div class="text-center mt-2">
                <small>(<?= ucfirst(convert_number_to_words($payroll['total_amount'])) ?> đồng)</small>
            </div>
        </div>

        <!-- Signatures -->
        <div class="signature-section">
            <div class="signature-box">
                <strong>Người nhận</strong>
                <div class="signature-line">
                    <?= htmlspecialchars($staff['full_name']) ?>
                </div>
            </div>
            <div class="signature-box">
                <strong>Người phê duyệt</strong>
                <div class="signature-line">
                    Ban Giám Hiệu
                </div>
            </div>
        </div>

        <div class="text-center mt-4 text-muted">
            <small>--- HẾT ---</small>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
</body>
</html>

<?php
// Helper function to convert number to words
function convert_number_to_words($number) {
    $ones = ['', 'một', 'hai', 'ba', 'bốn', 'năm', 'sáu', 'bảy', 'tám', 'chín'];
    $tens = ['', '', 'hai mươi', 'ba mươi', 'bốn mươi', 'năm mươi', 'sáu mươi', 'bảy mươi', 'tám mươi', 'chín mươi'];
    
    if ($number < 1000) {
        return 'không';
    }
    
    $thousands = floor($number / 1000);
    $result = '';
    
    if ($thousands > 0) {
        $result .= convert_hundreds($thousands, $ones, $tens) . ' nghìn';
    }
    
    return trim($result);
}

function convert_hundreds($num, $ones, $tens) {
    if ($num < 10) return $ones[$num];
    if ($num < 20) return 'mười ' . $ones[$num % 10];
    if ($num < 100) return $tens[floor($num / 10)] . ' ' . $ones[$num % 10];
    return $ones[floor($num / 100)] . ' trăm ' . convert_hundreds($num % 100, $ones, $tens);
}
?>
