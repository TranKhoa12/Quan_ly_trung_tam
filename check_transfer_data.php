<?php
// Script kiểm tra dữ liệu ca dạy trong database

$dbConfig = require __DIR__ . '/config/database.php';

try {
    $dsn = "mysql:host={$dbConfig['host']};dbname={$dbConfig['database']};charset={$dbConfig['charset']}";
    $db = new PDO($dsn, $dbConfig['username'], $dbConfig['password'], $dbConfig['options']);
    
    echo "=== KIỂM TRA DỮ LIỆU DATABASE ===\n\n";
    
    // 1. Kiểm tra bảng shift_registrations
    echo "1. Bảng shift_registrations (ca đã đăng ký):\n";
    $stmt = $db->query("SELECT sr.*, u.full_name, u.email 
                        FROM shift_registrations sr 
                        LEFT JOIN users u ON sr.staff_id = u.id 
                        ORDER BY sr.shift_date DESC 
                        LIMIT 10");
    $registrations = $stmt->fetchAll();
    
    if (empty($registrations)) {
        echo "   ⚠️ KHÔNG CÓ CA DẠY NÀO!\n";
        echo "   → Cần đăng ký ca dạy trước khi test chuyển ca\n\n";
    } else {
        echo "   ✓ Tìm thấy " . count($registrations) . " ca dạy:\n";
        foreach ($registrations as $reg) {
            echo "   - ID: {$reg['id']} | Ngày: {$reg['shift_date']} | ";
            echo "Giờ: {$reg['custom_start']}-{$reg['custom_end']} | ";
            echo "NV: {$reg['full_name']} | Trạng thái: {$reg['status']}\n";
        }
        echo "\n";
    }
    
    // 2. Kiểm tra bảng users (nhân viên)
    echo "2. Danh sách nhân viên:\n";
    $stmt = $db->query("SELECT id, full_name, email, role FROM users ORDER BY id");
    $users = $stmt->fetchAll();
    
    if (empty($users)) {
        echo "   ⚠️ KHÔNG CÓ NHÂN VIÊN NÀO!\n\n";
    } else {
        echo "   ✓ Tìm thấy " . count($users) . " người dùng:\n";
        foreach ($users as $user) {
            echo "   - ID: {$user['id']} | {$user['full_name']} ({$user['email']}) | Role: {$user['role']}\n";
        }
        echo "\n";
    }
    
    // 3. Kiểm tra bảng shift_transfers
    echo "3. Bảng shift_transfers (yêu cầu chuyển ca):\n";
    $stmt = $db->query("SELECT * FROM shift_transfers ORDER BY created_at DESC LIMIT 5");
    $transfers = $stmt->fetchAll();
    
    if (empty($transfers)) {
        echo "   ℹ️ Chưa có yêu cầu chuyển ca nào (bình thường khi mới cài đặt)\n\n";
    } else {
        echo "   ✓ Tìm thấy " . count($transfers) . " yêu cầu:\n";
        foreach ($transfers as $t) {
            echo "   - ID: {$t['id']} | Ca: {$t['shift_registration_id']} | ";
            echo "Từ: {$t['from_staff_id']} → Đến: {$t['to_staff_id']} | ";
            echo "Trạng thái: {$t['status']}\n";
        }
        echo "\n";
    }
    
    // 4. Kiểm tra bảng shift_transfer_logs
    echo "4. Bảng shift_transfer_logs:\n";
    $stmt = $db->query("SELECT COUNT(*) as count FROM shift_transfer_logs");
    $logCount = $stmt->fetch()['count'];
    echo "   ✓ Có {$logCount} log entries\n\n";
    
    // 5. Đề xuất
    echo "=== ĐỀ XUẤT ===\n";
    if (empty($registrations)) {
        echo "❌ Không có ca dạy nào trong hệ thống!\n";
        echo "→ Hãy đăng ký ca dạy tại: /teaching-shifts\n";
        echo "→ Hoặc admin tạo ca tại: /teaching-shifts/admin/create\n\n";
    } else {
        $approvedCount = count(array_filter($registrations, fn($r) => $r['status'] === 'approved'));
        if ($approvedCount === 0) {
            echo "⚠️ Không có ca nào đã DUYỆT!\n";
            echo "→ Chỉ có thể chuyển ca đã được duyệt\n";
            echo "→ Admin cần duyệt ca tại: /teaching-shifts/admin\n\n";
        } else {
            echo "✅ Có {$approvedCount} ca đã duyệt - có thể test chuyển ca!\n";
            echo "→ Nhân viên: Vào /teaching-shifts → Click ca → 'Yêu cầu chuyển ca'\n";
            echo "→ Hoặc trực tiếp: /teaching-shifts/transfer/" . $registrations[0]['id'] . "\n\n";
        }
    }
    
    if (count($users) < 2) {
        echo "⚠️ Cần ít nhất 2 nhân viên để test chuyển ca!\n";
        echo "→ Tạo thêm nhân viên tại: /staff/create\n\n";
    }
    
} catch (PDOException $e) {
    echo "LỖI DATABASE: " . $e->getMessage() . "\n";
    exit(1);
}
