<?php
require 'vendor/autoload.php';
require 'config/database.php';
require 'core/Database.php';
require 'core/BaseModel.php';
require 'app/models/Certificate.php';

$certModel = new Certificate();
$certificates = $certModel->getCertificatesWithDetails();

echo "<h3>Data from Model getCertificatesWithDetails():</h3>";
echo "<pre>";
foreach ($certificates as $cert) {
    echo "ID: " . $cert['id'] . "\n";
    echo "Student: " . $cert['student_name'] . "\n";
    echo "Approval Status: " . $cert['approval_status'] . "\n";
    echo "Approved At: " . (isset($cert['approved_at']) ? $cert['approved_at'] : 'NOT SET') . "\n";
    echo "Receive Status: " . $cert['receive_status'] . "\n";
    echo "Received At: " . (isset($cert['received_at']) ? $cert['received_at'] : 'NOT SET') . "\n";
    echo "---\n";
    
    if ($cert['id'] <= 2) {
        echo "Full data for ID " . $cert['id'] . ":\n";
        print_r($cert);
        echo "\n\n";
    }
}
echo "</pre>";
