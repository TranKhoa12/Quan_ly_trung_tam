<?php
// Simple test to create empty report directly in database

try {
    $pdo = new PDO('mysql:host=localhost;dbname=quan_ly_trung_tam', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Testing direct database insertion...\n";
    
    // Test data for empty report
    $data = [
        'report_date' => date('Y-m-d'),
        'report_time' => date('H:i:s'),
        'staff_id' => 1,
        'total_visitors' => 0,
        'total_registered' => 0,
        'notes' => 'Báo cáo rỗng - Test trực tiếp'
    ];
    
    // Insert query
    $sql = "INSERT INTO reports (report_date, report_time, staff_id, total_visitors, total_registered, notes) 
            VALUES (:report_date, :report_time, :staff_id, :total_visitors, :total_registered, :notes)";
    
    $stmt = $pdo->prepare($sql);
    $result = $stmt->execute($data);
    
    if ($result) {
        $reportId = $pdo->lastInsertId();
        echo "SUCCESS: Empty report created with ID: " . $reportId . "\n";
        
        // Verify the record
        $stmt = $pdo->prepare("SELECT * FROM reports WHERE id = ?");
        $stmt->execute([$reportId]);
        $report = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo "Verified record:\n";
        foreach ($report as $key => $value) {
            echo "  $key: $value\n";
        }
        
    } else {
        echo "FAILED: Could not create report\n";
    }
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
?>