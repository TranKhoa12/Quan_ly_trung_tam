<?php
require_once __DIR__ . '/../layouts/main.php';

// Page content
ob_start();
?>

<style>
/* Fix dropdown menu visibility in table */
.table-responsive {
    overflow-x: auto;
    overflow-y: visible !important;
}

.stats-card .card-body {
    overflow: visible !important;
}

/* Ensure dropdown menu appears above other elements */
.dropdown-menu {
    position: absolute !important;
    z-index: 1050 !important;
}

/* Make sure button group doesn't clip dropdown */
.btn-group {
    position: static !important;
}

.table tbody tr:last-child .dropdown-menu {
    bottom: 0;
    top: auto;
}
</style>

<?= pageHeader(
    'Quản lý chứng nhận', 
    'Theo dõi và xử lý các yêu cầu cấp chứng nhận hoàn thành khóa học', 
    '<a href="/Quan_ly_trung_tam/public/certificates/create" class="btn btn-primary">
        <i class="fas fa-plus me-2"></i>Tạo yêu cầu mới
    </a>'
) ?>

<div class="p-3">
    <!-- Alert Messages -->
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            <?= htmlspecialchars($_SESSION['success']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <?= htmlspecialchars($_SESSION['error']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

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
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-primary"><?= count($certificates) ?> yêu cầu</span>
                    <?php if (!empty($certificates)): ?>
                    <button type="button" class="btn btn-sm btn-danger" id="deleteSelectedBtn" style="display: none;" onclick="deleteSelected()">
                        <i class="fas fa-trash me-1"></i>Xóa đã chọn (<span id="selectedCount">0</span>)
                    </button>
                    <?php endif; ?>
                </div>
            </div>
            
            <?php if (!empty($certificates)): ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th width="50">
                                    <input type="checkbox" class="form-check-input" id="selectAll" onchange="toggleSelectAll(this)">
                                </th>
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
                                        <input type="checkbox" class="form-check-input certificate-checkbox" value="<?= $cert['id'] ?>" onchange="updateSelectedCount()">
                                    </td>
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
                                        <div>
                                            <span class="badge bg-<?= $color ?>">
                                                <i class="<?= $icon ?> me-1"></i><?= $label ?>
                                            </span>
                                            <?php 
                                            // Debug data
                                            echo "<!-- approved_at: " . ($cert['approved_at'] ?? 'NULL') . ", status: " . $cert['approval_status'] . " -->";
                                            if (!empty($cert['approved_at']) && in_array($cert['approval_status'], ['approved', 'cancelled'])): 
                                            ?>
                                                <div class="mt-1">
                                                    <small class="text-muted d-block">
                                                        <i class="fas fa-calendar-alt me-1"></i><?= date('d/m/Y', strtotime($cert['approved_at'])) ?>
                                                    </small>
                                                    <small class="text-muted d-block">
                                                        <i class="fas fa-clock me-1"></i><?= date('H:i', strtotime($cert['approved_at'])) ?>
                                                    </small>
                                                </div>
                                            <?php endif; ?>
                                        </div>
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
                                        <div>
                                            <span class="badge bg-<?= $rColor ?>">
                                                <i class="<?= $rIcon ?> me-1"></i><?= $rLabel ?>
                                            </span>
                                            <?php 
                                            // Debug data
                                            echo "<!-- received_at: " . ($cert['received_at'] ?? 'NULL') . ", status: " . $cert['receive_status'] . " -->";
                                            if (!empty($cert['received_at']) && $cert['receive_status'] === 'received'): 
                                            ?>
                                                <div class="mt-1">
                                                    <small class="text-muted d-block">
                                                        <i class="fas fa-calendar-alt me-1"></i><?= date('d/m/Y', strtotime($cert['received_at'])) ?>
                                                    </small>
                                                    <small class="text-muted d-block">
                                                        <i class="fas fa-clock me-1"></i><?= date('H:i', strtotime($cert['received_at'])) ?>
                                                    </small>
                                                </div>
                                            <?php endif; ?>
                                        </div>
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
                                            <a href="/Quan_ly_trung_tam/public/certificates/<?= $cert['id'] ?>/edit" 
                                               class="btn btn-sm btn-outline-warning" title="Chỉnh sửa">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            
                                            <!-- Dropdown for status changes (Admin only) -->
                                            <div class="btn-group" role="group">
                                                <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" 
                                                        data-bs-toggle="dropdown" aria-expanded="false" title="Thay đổi trạng thái">
                                                    <i class="fas fa-tasks"></i>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <?php if ($cert['approval_status'] === 'pending'): ?>
                                                        <li>
                                                            <a class="dropdown-item text-success" href="#" 
                                                               onclick="updateApprovalStatus(<?= $cert['id'] ?>, 'approved'); return false;">
                                                                <i class="fas fa-check me-2"></i>Phê duyệt
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a class="dropdown-item text-danger" href="#" 
                                                               onclick="updateApprovalStatus(<?= $cert['id'] ?>, 'cancelled'); return false;">
                                                                <i class="fas fa-times me-2"></i>Hủy
                                                            </a>
                                                        </li>
                                                    <?php elseif ($cert['approval_status'] === 'approved'): ?>
                                                        <li>
                                                            <a class="dropdown-item text-warning" href="#" 
                                                               onclick="updateApprovalStatus(<?= $cert['id'] ?>, 'pending'); return false;">
                                                                <i class="fas fa-undo me-2"></i>Chuyển về Chờ duyệt
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a class="dropdown-item text-danger" href="#" 
                                                               onclick="updateApprovalStatus(<?= $cert['id'] ?>, 'cancelled'); return false;">
                                                                <i class="fas fa-times me-2"></i>Hủy
                                                            </a>
                                                        </li>
                                                    <?php elseif ($cert['approval_status'] === 'cancelled'): ?>
                                                        <li>
                                                            <a class="dropdown-item text-warning" href="#" 
                                                               onclick="updateApprovalStatus(<?= $cert['id'] ?>, 'pending'); return false;">
                                                                <i class="fas fa-undo me-2"></i>Chuyển về Chờ duyệt
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a class="dropdown-item text-success" href="#" 
                                                               onclick="updateApprovalStatus(<?= $cert['id'] ?>, 'approved'); return false;">
                                                                <i class="fas fa-check me-2"></i>Phê duyệt
                                                            </a>
                                                        </li>
                                                    <?php endif; ?>
                                                    
                                                    <li><hr class="dropdown-divider"></li>
                                                    
                                                    <?php if ($cert['receive_status'] === 'not_received'): ?>
                                                        <li>
                                                            <a class="dropdown-item text-info" href="#" 
                                                               onclick="updateReceiveStatus(<?= $cert['id'] ?>, 'received'); return false;">
                                                                <i class="fas fa-hand-holding me-2"></i>Đánh dấu đã nhận
                                                            </a>
                                                        </li>
                                                    <?php else: ?>
                                                        <li>
                                                            <a class="dropdown-item text-warning" href="#" 
                                                               onclick="updateReceiveStatus(<?= $cert['id'] ?>, 'not_received'); return false;">
                                                                <i class="fas fa-undo me-2"></i>Chuyển về Chưa nhận
                                                            </a>
                                                        </li>
                                                    <?php endif; ?>
                                                </ul>
                                            </div>
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

<script>
// Custom JavaScript for certificates page

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

function updateApprovalStatus(certId, status) {
    const messages = {
        "approved": "Bạn có chắc chắn muốn phê duyệt yêu cầu chứng nhận này?",
        "cancelled": "Bạn có chắc chắn muốn hủy yêu cầu chứng nhận này?",
        "pending": "Bạn có chắc chắn muốn chuyển về trạng thái Chờ duyệt?"
    };
    
    if (confirm(messages[status] || "Bạn có chắc chắn muốn thay đổi trạng thái này?")) {
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
}

function updateReceiveStatus(certId, status) {
    const messages = {
        "received": "Xác nhận học viên đã nhận chứng nhận?",
        "not_received": "Bạn có chắc chắn muốn chuyển về trạng thái Chưa nhận?"
    };
    
    if (confirm(messages[status] || "Bạn có chắc chắn muốn thay đổi trạng thái này?")) {
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
}

// Legacy functions for backward compatibility
function updateCertificateStatus(certId, status) {
    updateApprovalStatus(certId, status);
}

function updateCertificateReceiveStatus(certId, status) {
    updateReceiveStatus(certId, status);
}

// Chọn tất cả checkbox
function toggleSelectAll(checkbox) {
    const checkboxes = document.querySelectorAll(".certificate-checkbox");
    checkboxes.forEach(cb => {
        cb.checked = checkbox.checked;
    });
    updateSelectedCount();
}

// Cập nhật số lượng đã chọn
function updateSelectedCount() {
    const checkboxes = document.querySelectorAll(".certificate-checkbox:checked");
    const count = checkboxes.length;
    const deleteBtn = document.getElementById("deleteSelectedBtn");
    const selectedCountSpan = document.getElementById("selectedCount");
    
    if (selectedCountSpan) {
        selectedCountSpan.textContent = count;
    }
    
    if (deleteBtn) {
        if (count > 0) {
            deleteBtn.style.display = "inline-block";
        } else {
            deleteBtn.style.display = "none";
        }
    }
    
    // Cập nhật trạng thái checkbox "Chọn tất cả"
    const selectAllCheckbox = document.getElementById("selectAll");
    const allCheckboxes = document.querySelectorAll(".certificate-checkbox");
    if (selectAllCheckbox && allCheckboxes.length > 0) {
        selectAllCheckbox.checked = checkboxes.length === allCheckboxes.length;
    }
}

// Xóa các yêu cầu chứng nhận đã chọn
function deleteSelected() {
    const checkboxes = document.querySelectorAll(".certificate-checkbox:checked");
    
    if (checkboxes.length === 0) {
        alert("Vui lòng chọn ít nhất một yêu cầu để xóa");
        return;
    }
    
    const ids = Array.from(checkboxes).map(cb => cb.value);
    const count = ids.length;
    
    if (confirm(`Bạn có chắc chắn muốn xóa ${count} yêu cầu chứng nhận đã chọn?\n\nHành động này không thể hoàn tác!`)) {
        // Hiển thị loading
        const deleteBtn = document.getElementById("deleteSelectedBtn");
        const originalHtml = deleteBtn.innerHTML;
        deleteBtn.disabled = true;
        deleteBtn.innerHTML = "<i class=\"fas fa-spinner fa-spin me-1\"></i>Đang xóa...";
        
        fetch("/Quan_ly_trung_tam/public/certificates/delete-multiple", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
            },
            body: JSON.stringify({ ids: ids })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message || `Đã xóa thành công ${count} yêu cầu`);
                location.reload();
            } else {
                alert("Lỗi: " + (data.message || "Không thể xóa yêu cầu"));
                deleteBtn.disabled = false;
                deleteBtn.innerHTML = originalHtml;
            }
        })
        .catch(error => {
            alert("Lỗi kết nối: " + error.message);
            deleteBtn.disabled = false;
            deleteBtn.innerHTML = originalHtml;
        });
    }
}
</script>

<?php
$content = ob_get_clean();

// Render layout
useModernLayout('Quản lý chứng nhận', $content);
?>