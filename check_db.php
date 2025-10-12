<?php
try {
    $pdo = new PDO('mysql:host=localhost;dbname=quan_ly_trung_tam', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo 'Database connection: OK' . PHP_EOL;
    
    // Kiểm tra bảng reports có tồn tại không
    $stmt = $pdo->query('SHOW TABLES LIKE "reports"');
    if ($stmt->rowCount() > 0) {
        echo 'Table reports exists' . PHP_EOL;
        
        // Kiểm tra cấu trúc bảng
        $stmt = $pdo->query('DESCRIBE reports');
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo 'Table structure:' . PHP_EOL;
        foreach ($columns as $column) {
            echo '- ' . $column['Field'] . ' (' . $column['Type'] . ')' . PHP_EOL;
        }
        
        // Kiểm tra số lượng records
        $stmt = $pdo->query('SELECT COUNT(*) as count FROM reports');
        $count = $stmt->fetch(PDO::FETCH_ASSOC);
        echo 'Total records: ' . $count['count'] . PHP_EOL;
        
        // Kiểm tra records mới nhất
        $stmt = $pdo->query('SELECT * FROM reports ORDER BY id DESC LIMIT 3');
        $reports = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo 'Latest reports:' . PHP_EOL;
        foreach ($reports as $report) {
            echo sprintf('ID: %d, Date: %s, Visitors: %d, Registered: %d, Notes: %s', 
                $report['id'], $report['report_date'], $report['total_visitors'], 
                $report['total_registered'], substr($report['notes'], 0, 50)) . PHP_EOL;
        }
        
    } else {
        echo 'Table reports does not exist' . PHP_EOL;
    }
    
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage() . PHP_EOL;
}
?>