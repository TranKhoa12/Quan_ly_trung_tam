<?php
require_once __DIR__ . '/../layouts/main.php';

// Page content
ob_start();
?>

<?= pageHeader(
    'Quản lý chứng nhận', 
    'Theo dõi và xử lý các yêu cầu cấp chứng nhận hoàn thành khóa học', 
    '<a href="/Quan_ly_trung_tam/public/certificates/create" class="btn btn-primary">
        <i class="fas fa-plus me-2"></i>Tạo yêu cầu mới
    </a>'
) ?>

<div class="p-3">
    <!-- Statistics -->
    <div class="row g-3 mb-4">
        <div class="col-md-3 col-sm-6">
            <?= statsCard(
                'fas fa-certificate', 
                'Tổng yêu cầu', 
                count($certificates), 
                'Tổng số yêu cầu chứng nhận',
                'primary'
            ) ?>
        </div>
        
        <div class="col-md-3 col-sm-6">
            <?php 
            $pending = array_filter($certificates, function($c) { return $c['approval_status'] === 'pending'; });
            ?>
            <?= statsCard(
                'fas fa-clock', 
                'Chờ duyệt', 
                count($pending), 
                'Yêu cầu đang chờ xử lý',
                'warning'
            ) ?>
        </div>
        
        <div class="col-md-3 col-sm-6">
            <?php 
            $approved = array_filter($certificates, function($c) { return $c['approval_status'] === 'approved'; });
            ?>
            <?= statsCard(
                'fas fa-check-circle', 
                'Đã duyệt', 
                count($approved), 
                'Yêu cầu đã được phê duyệt',
                'success'
            ) ?>
        </div>
        
        <div class="col-md-3 col-sm-6">
            <?php 
            $received = array_filter($certificates, function($c) { return $c['receive_status'] === 'received'; });
            ?>
            <?= statsCard(
                'fas fa-hand-holding', 
                'Đã nhận', 
                count($received), 
                'Chứng nhận đã được nhận',
                'info'
            ) ?>
        </div>
    </div>

    <!-- Filter and Search -->
    <div class="stats-card mb-4">
        <div class="card-body">
            <form method="GET" action="/Quan_ly_trung_tam/public/certificates">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label">
                            <i class="fas fa-search text-primary"></i> Tìm kiếm
                        </label>
                        <input type="text" class="form-control" name="search" 
                               placeholder="Tên học viên, SĐT..." 
                               value="<?= $_GET['search'] ?? '' ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">
                            <i class="fas fa-flag text-primary"></i> Trạng thái duyệt
                        </label>
                        <select class="form-select" name="approval_status">
                            <option value="">Tất cả trạng thái</option>
                            <option value="pending" <?= (isset($_GET['approval_status']) && $_GET['approval_status'] === 'pending') ? 'selected' : '' ?>>Chờ duyệt</option>
                            <option value="approved" <?= (isset($_GET['approval_status']) && $_GET['approval_status'] === 'approved') ? 'selected' : '' ?>>Đã duyệt</option>
                            <option value="cancelled" <?= (isset($_GET['approval_status']) && $_GET['approval_status'] === 'cancelled') ? 'selected' : '' ?>>Đã hủy</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">
                            <i class="fas fa-hand-holding text-primary"></i> Trạng thái nhận
                        </label>
                        <select class="form-select" name="receive_status">
                            <option value="">Tất cả trạng thái</option>
                            <option value="not_received" <?= (isset($_GET['receive_status']) && $_GET['receive_status'] === 'not_received') ? 'selected' : '' ?>>Chưa nhận</option>
                            <option value="received" <?= (isset($_GET['receive_status']) && $_GET['receive_status'] === 'received') ? 'selected' : '' ?>>Đã nhận</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search me-2"></i>Tìm kiếm
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Certificates Table -->
    <div class="stats-card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="card-title mb-0">
                    <i class="fas fa-list text-primary me-2"></i>
                    Danh sách yêu cầu chứng nhận
                </h6>
                <span class="badge bg-primary"><?= count($certificates) ?> yêu cầu</span>
            </div>
            
            <?php if (!empty($certificates)): ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Học viên</th>
                                <th>Liên hệ</th>
                                <th>Môn học</th>
                                <th>Người yêu cầu</th>
                                <th>Trạng thái duyệt</th>
                                <th>Trạng thái nhận</th>
                                <th>Ngày tạo</th>
                                <th>Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($certificates as $cert): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-circle bg-primary text-white me-3 d-flex align-items-center justify-content-center" 
                                                 style="width: 40px; height: 40px; border-radius: 50%; font-weight: 600;">
                                                <?= strtoupper(substr($cert['student_name'], 0, 1)) ?>
                                            </div>
                                            <div>
                                                <div class="fw-semibold"><?= htmlspecialchars($cert['student_name']) ?></div>
                                                <?php if (!empty($cert['username'])): ?>
                                                    <small class="text-muted">@<?= htmlspecialchars($cert['username']) ?></small>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if (!empty($cert['phone'])): ?>
                                            <div><i class="fas fa-phone text-muted me-1"></i> <?= htmlspecialchars($cert['phone']) ?></div>
                                        <?php else: ?>
                                            <span class="text-muted">Chưa có</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-info"><?= htmlspecialchars($cert['subject']) ?></span>
                                    </td>
                                    <td>
                                        <?php if (!empty($cert['requester_name'])): ?>
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-user text-muted me-2"></i>
                                                <?= htmlspecialchars($cert['requester_name']) ?>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-muted">Hệ thống</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php
                                        $approvalColors = [
                                            'pending' => 'warning',
                                            'approved' => 'success', 
                                            'cancelled' => 'danger'
                                        ];
                                        $approvalLabels = [
                                            'pending' => 'Chờ duyệt',
                                            'approved' => 'Đã duyệt',
                                            'cancelled' => 'Đã hủy'
                                        ];
                                        $approvalIcons = [
                                            'pending' => 'fas fa-clock',
                                            'approved' => 'fas fa-check',
                                            'cancelled' => 'fas fa-times'
                                        ];
                                        $color = $approvalColors[$cert['approval_status']] ?? 'secondary';
                                        $label = $approvalLabels[$cert['approval_status']] ?? $cert['approval_status'];
                                        $icon = $approvalIcons[$cert['approval_status']] ?? 'fas fa-question';
                                        ?>
                                        <span class="badge bg-<?= $color ?>">
                                            <i class="<?= $icon ?> me-1"></i><?= $label ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php
                                        $receiveColors = [
                                            'not_received' => 'warning',
                                            'received' => 'success'
                                        ];
                                        $receiveLabels = [
                                            'not_received' => 'Chưa nhận',
                                            'received' => 'Đã nhận'
                                        ];
                                        $receiveIcons = [
                                            'not_received' => 'fas fa-hourglass-half',
                                            'received' => 'fas fa-hand-holding'
                                        ];
                                        $rColor = $receiveColors[$cert['receive_status']] ?? 'secondary';
                                        $rLabel = $receiveLabels[$cert['receive_status']] ?? $cert['receive_status'];
                                        $rIcon = $receiveIcons[$cert['receive_status']] ?? 'fas fa-question';
                                        ?>
                                        <span class="badge bg-<?= $rColor ?>">
                                            <i class="<?= $rIcon ?> me-1"></i><?= $rLabel ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div>
                                            <span class="fw-semibold"><?= date('d/m/Y', strtotime($cert['created_at'])) ?></span>
                                            <br><small class="text-muted"><?= date('H:i', strtotime($cert['created_at'])) ?></small>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="/Quan_ly_trung_tam/public/certificates/<?= $cert['id'] ?>" 
                                               class="btn btn-sm btn-outline-primary" title="Xem chi tiết">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <?php if ($cert['approval_status'] === 'pending'): ?>
                                                <button type="button" class="btn btn-sm btn-outline-success" 
                                                        onclick="approveCertificate(<?= $cert['id'] ?>)" title="Phê duyệt">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-danger" 
                                                        onclick="cancelCertificate(<?= $cert['id'] ?>)" title="Hủy">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            <?php endif; ?>
                                            <?php if ($cert['approval_status'] === 'approved' && $cert['receive_status'] === 'not_received'): ?>
                                                <button type="button" class="btn btn-sm btn-outline-info" 
                                                        onclick="markAsReceived(<?= $cert['id'] ?>)" title="Đánh dấu đã nhận">
                                                    <i class="fas fa-hand-holding"></i>
                                                </button>
                                            <?php endif; ?>
                                            <a href="/Quan_ly_trung_tam/public/certificates/<?= $cert['id'] ?>/edit" 
                                               class="btn btn-sm btn-outline-warning" title="Chỉnh sửa">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-certificate text-muted fa-4x mb-3"></i>
                    <h5 class="text-muted">Chưa có yêu cầu chứng nhận nào</h5>
                    <p class="text-muted">Bắt đầu tạo yêu cầu cấp chứng nhận đầu tiên</p>
                    <a href="/Quan_ly_trung_tam/public/certificates/create" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Tạo yêu cầu đầu tiên
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();

// Custom JavaScript
$customJs = '
function approveCertificate(certId) {
    if (confirm("Bạn có chắc chắn muốn phê duyệt yêu cầu chứng nhận này?")) {
        updateCertificateStatus(certId, "approved");
    }
}

function cancelCertificate(certId) {
    if (confirm("Bạn có chắc chắn muốn hủy yêu cầu chứng nhận này?")) {
        updateCertificateStatus(certId, "cancelled");
    }
}

function markAsReceived(certId) {
    if (confirm("Xác nhận học viên đã nhận chứng nhận?")) {
        updateCertificateReceiveStatus(certId, "received");
    }
}

function updateCertificateStatus(certId, status) {
    const form = document.createElement("form");
    form.method = "POST";
    form.action = "/Quan_ly_trung_tam/public/certificates/" + certId + "/status";
    
    const statusInput = document.createElement("input");
    statusInput.type = "hidden";
    statusInput.name = "approval_status";
    statusInput.value = status;
    
    form.appendChild(statusInput);
    document.body.appendChild(form);
    form.submit();
}

function updateCertificateReceiveStatus(certId, status) {
    const form = document.createElement("form");
    form.method = "POST";
    form.action = "/Quan_ly_trung_tam/public/certificates/" + certId + "/receive";
    
    const statusInput = document.createElement("input");
    statusInput.type = "hidden";
    statusInput.name = "receive_status";
    statusInput.value = status;
    
    form.appendChild(statusInput);
    document.body.appendChild(form);
    form.submit();
}
';

// Render layout
useModernLayout('Quản lý chứng nhận', $content);
?>