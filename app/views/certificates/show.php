<?php
require_once __DIR__ . '/../layouts/main.php';

// Compute base path
$scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
$scriptDir = str_replace('\\', '/', dirname($scriptName));
$certBasePath = rtrim($scriptDir, '/');
if ($certBasePath === '' || $certBasePath === '.') {
    $certBasePath = '';
}

// Start content buffering
ob_start();

// Page header
$headerTitle = 'Chi tiết yêu cầu chứng nhận';
$headerDesc = 'Xem thông tin chi tiết yêu cầu cấp chứng nhận';
$headerButton = '<a href="' . $certBasePath . '/certificates" class="btn btn-outline-secondary">
    <i class="fas fa-arrow-left me-2"></i>Quay lại
</a>';

echo pageHeader($headerTitle, $headerDesc, $headerButton);
?>

<?php $editLogs = $editLogs ?? []; ?>

<div class="p-3">
    <div class="row g-3">
        <!-- Cột trái: Thông tin yêu cầu -->
        <div class="col-12 col-xl-5">
            <div class="stats-card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="card-title mb-0">
                            <i class="fas fa-certificate text-primary me-2"></i>
                            Thông tin yêu cầu chứng nhận
                        </h6>
                        <button class="btn btn-sm btn-outline-primary" onclick="copyAllFields()" title="Copy tất cả thông tin">
                            <i class="fas fa-copy me-1"></i>Copy
                        </button>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-muted">TÊN HỌC VIÊN</label>
                            <p class="mb-0 fw-semibold"><?= htmlspecialchars($certificate['student_name']) ?></p>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-muted">TÊN ĐĂNG NHẬP</label>
                            <p class="mb-0">
                                <?= !empty($certificate['username']) ? htmlspecialchars($certificate['username']) : '<span class="text-muted">Chưa có</span>' ?>
                            </p>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-muted">SỐ ĐIỆN THOẠI</label>
                            <p class="mb-0">
                                <?= !empty($certificate['phone']) ? htmlspecialchars($certificate['phone']) : '<span class="text-muted">Chưa có</span>' ?>
                            </p>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-muted">NGƯỜI YÊU CẦU</label>
                            <p class="mb-0">
                                <?php if (!empty($certificate['requested_by_name'])): ?>
                                    <span class="badge bg-primary"><?= htmlspecialchars($certificate['requested_by_name']) ?></span>
                                <?php else: ?>
                                    <span class="badge bg-success">Administrator</span>
                                <?php endif; ?>
                            </p>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-muted">MÔN HỌC</label>
                            <p class="mb-0">
                                <span class="badge bg-info"><?= htmlspecialchars($certificate['subject']) ?></span>
                            </p>
                        </div>

                        <?php if (!empty($certificate['email'])): ?>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-muted">EMAIL</label>
                            <p class="mb-0"><?= htmlspecialchars($certificate['email']) ?></p>
                        </div>
                        <?php endif; ?>

                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-muted">TRẠNG THÁI DUYỆT</label>
                            <p class="mb-0">
                                <?php
                                $colors = ['pending' => 'warning', 'approved' => 'success', 'cancelled' => 'danger'];
                                $labels = ['pending' => 'Chờ duyệt', 'approved' => 'Đã duyệt', 'cancelled' => 'Đã hủy'];
                                $color = $colors[$certificate['approval_status']] ?? 'secondary';
                                $label = $labels[$certificate['approval_status']] ?? $certificate['approval_status'];
                                ?>
                                <span class="badge bg-<?= $color ?> text-uppercase"><?= $label ?></span>
                            </p>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-muted">TRẠNG THÁI NHẬN</label>
                            <p class="mb-0">
                                <?php
                                $rColors = ['not_received' => 'warning', 'received' => 'success'];
                                $rLabels = ['not_received' => 'Chưa nhận', 'received' => 'Đã nhận'];
                                $rColor = $rColors[$certificate['receive_status']] ?? 'secondary';
                                $rLabel = $rLabels[$certificate['receive_status']] ?? $certificate['receive_status'];
                                ?>
                                <span class="badge bg-<?= $rColor ?> text-uppercase"><?= $rLabel ?></span>
                            </p>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-muted">NGÀY TẠO</label>
                            <p class="mb-0 small">
                                <i class="fas fa-calendar-plus text-secondary me-1"></i>
                                <?= date('d/m/Y H:i', strtotime($certificate['created_at'])) ?>
                            </p>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-muted">CẬP NHẬT CUỐI</label>
                            <p class="mb-0 small">
                                <i class="fas fa-calendar-check text-secondary me-1"></i>
                                <?= !empty($certificate['updated_at']) ? date('d/m/Y H:i', strtotime($certificate['updated_at'])) : '<span class="text-muted">Chưa có</span>' ?>
                            </p>
                        </div>

                        <?php if (!empty($certificate['notes'])): ?>
                        <div class="col-12">
                            <label class="form-label fw-bold small text-muted">GHI CHÚ</label>
                            <p class="mb-0 text-muted small"><?= nl2br(htmlspecialchars($certificate['notes'])) ?></p>
                        </div>
                        <?php endif; ?>
                    </div>

                    <hr class="my-3">

                    <div class="d-flex flex-wrap gap-2">
                        <?php if ($userRole === 'admin'): ?>
                            <?php if ($certificate['approval_status'] === 'pending'): ?>
                                <button type="button" class="btn btn-sm btn-success" onclick="updateStatus(<?= $certificate['id'] ?>, 'approved')">
                                    <i class="fas fa-check me-1"></i>Phê duyệt
                                </button>
                                <button type="button" class="btn btn-sm btn-danger" onclick="updateStatus(<?= $certificate['id'] ?>, 'cancelled')">
                                    <i class="fas fa-times me-1"></i>Hủy
                                </button>
                            <?php endif; ?>
                        <?php endif; ?>
                        
                        <?php if ($certificate['receive_status'] === 'not_received'): ?>
                            <button type="button" class="btn btn-sm btn-info" onclick="updateReceiveStatus(<?= $certificate['id'] ?>, 'received', '<?= $certificate['approval_status'] ?>')">
                                <i class="fas fa-hand-holding me-1"></i>Đánh dấu đã nhận
                            </button>
                        <?php endif; ?>
                        
                        <a href="<?= $certBasePath ?>/certificates/<?= $certificate['id'] ?>/edit" class="btn btn-sm btn-warning">
                            <i class="fas fa-edit me-1"></i>Chỉnh sửa
                        </a>
                        
                        <a href="<?= $certBasePath ?>/certificates" class="btn btn-sm btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i>Quay lại
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cột phải: Google Form -->
        <div class="col-12 col-xl-7">
            <div class="stats-card h-100">
                <div class="card-body p-0">
                    <div class="d-flex justify-content-between align-items-center p-3 border-bottom">
                        <h6 class="card-title mb-0">
                            <i class="fab fa-google text-primary me-2"></i>
                            Google Form
                        </h6>
                        <a href="https://docs.google.com/forms/d/e/1FAIpQLSeCu930rAsE2vLZomTnFpqB22T3Lr1wPRCeY3OCjB8MBUcwbA/viewform" target="_blank" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-external-link-alt me-1"></i>Mở tab mới
                        </a>
                    </div>
                    <div class="position-relative" style="min-height: 900px;">
                        <iframe 
                            src="https://docs.google.com/forms/d/e/1FAIpQLSeCu930rAsE2vLZomTnFpqB22T3Lr1wPRCeY3OCjB8MBUcwbA/viewform?embedded=true" 
                            width="100%" 
                            height="100%" 
                            frameborder="0" 
                            marginheight="0" 
                            marginwidth="0"
                            style="border: none; position: absolute; top: 0; left: 0; right: 0; bottom: 0;">
                            Đang tải...
                        </iframe>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Lịch sử chỉnh sửa - Dòng riêng -->
    <?php if (!empty($editLogs)): ?>
    <div class="row mt-3">
        <div class="col-12">
            <div class="stats-card">
                <div class="card-body">
                    <h6 class="mb-3 text-muted">
                        <i class="fas fa-history me-2"></i>
                        Lịch sử chỉnh sửa
                    </h6>
                    <div class="list-group list-group-flush">
                        <?php foreach ($editLogs as $log): ?>
                            <div class="list-group-item px-0 py-2">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="flex-grow-1">
                                        <span class="fw-semibold small"><?= htmlspecialchars($log['user_name'] ?? 'Hệ thống') ?></span>
                                        <p class="mb-0 text-muted small"><?= htmlspecialchars($log['changes']) ?></p>
                                    </div>
                                    <small class="text-muted ms-2"><?= date('d/m/Y H:i', strtotime($log['created_at'])) ?></small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
function updateStatus(certId, status) {
    const messages = {
        'approved': 'Bạn có chắc chắn muốn phê duyệt yêu cầu này?',
        'cancelled': 'Bạn có chắc chắn muốn hủy yêu cầu này?'
    };
    
    if (confirm(messages[status])) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '/Quan_ly_trung_tam/public/certificates/' + certId + '/status';
        
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'approval_status';
        input.value = status;
        
        form.appendChild(input);
        document.body.appendChild(form);
        form.submit();
    }
}

function updateReceiveStatus(certId, status, approvalStatus) {
    // Kiểm tra nếu yêu cầu chưa được duyệt
    if (approvalStatus !== 'approved') {
        alert('⚠️ Không thể đánh dấu đã nhận!\n\nYêu cầu chứng nhận này chưa được phê duyệt.\nVui lòng đợi admin phê duyệt trước khi đánh dấu đã nhận.');
        return;
    }
    
    if (confirm('Xác nhận học viên đã nhận chứng nhận?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '<?= $certBasePath ?>/certificates/' + certId + '/receive';
        
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'receive_status';
        input.value = status;
        
        form.appendChild(input);
        document.body.appendChild(form);
        form.submit();
    }
}

function copyAllFields() {
    // Lấy tất cả thông tin
    const info = {
        'Tài khoản': <?= json_encode($certificate['username'] ?? '') ?>,
        'Họ và tên': <?= json_encode($certificate['student_name'] ?? '') ?>,
        'Số điện thoại': <?= json_encode($certificate['phone'] ?? '') ?>,
        'Môn học': <?= json_encode($certificate['subject'] ?? '') ?>,
        'Email': <?= json_encode($certificate['email'] ?? '') ?>,
        'Tình trạng': <?= json_encode($certificate['approval_status'] === 'approved' ? 'Đã duyệt' : ($certificate['approval_status'] === 'cancelled' ? 'Đã hủy' : 'Chờ duyệt')) ?>
    };
    
    // Format text để copy
    let textToCopy = '';
    for (const [key, value] of Object.entries(info)) {
        if (value) {
            textToCopy += value + '\n';
        }
    }
    
    // Copy vào clipboard
    navigator.clipboard.writeText(textToCopy).then(() => {
        // Hiển thị thông báo
        const btn = event.target.closest('button');
        const originalHTML = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-check me-1"></i>Đã copy!';
        btn.classList.remove('btn-outline-primary');
        btn.classList.add('btn-success');
        
        setTimeout(() => {
            btn.innerHTML = originalHTML;
            btn.classList.remove('btn-success');
            btn.classList.add('btn-outline-primary');
        }, 2000);
    }).catch(err => {
        if (typeof showToast === 'function') {
            showToast('❌ Không thể copy. Vui lòng thử lại!', 'danger');
        } else {
            alert('❌ Không thể copy. Vui lòng thử lại!');
        }
    });
}
</script>

<?php
$content = ob_get_clean();
useModernLayout('Chi tiết yêu cầu chứng nhận', $content);
?>
