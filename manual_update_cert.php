<?php
define('BASE_PATH', __DIR__);
define('CONFIG_PATH', __DIR__ . '/config');

require 'vendor/autoload.php';
require 'config/database.php';
require 'core/Database.php';
require 'core/BaseModel.php';
require 'app/models/Certificate.php';

$certificateId = isset($argv[1]) ? (int)$argv[1] : 0;
$status = $argv[2] ?? 'approved';
$receiveStatus = $argv[3] ?? 'received';

if ($certificateId <= 0) {
    echo "Usage: php manual_update_cert.php <certificate_id> [approval_status] [receive_status]\n";
    exit(1);
}

$certModel = new Certificate();

$certModel->updateApprovalStatus($certificateId, $status, 1);
$certModel->updateReceiveStatus($certificateId, $receiveStatus);

echo "Updated certificate {$certificateId}\n";
