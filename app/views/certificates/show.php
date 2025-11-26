<?php
require_once __DIR__ . '/../layouts/main.php';

// Page content
ob_start();
?>

<?= pageHeader(
    'Chi tiết yêu cầu chứng nhận', 
    'Xem thông tin chi tiết yêu cầu cấp chứng nhận', 
    '<a href="/Quan_ly_trung_tam/public/certificates" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-2"></i>Quay lại
    </a>'
) ?>

<div class="p-3">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="stats-card">
                <div class="card-body">
                    <h5 class="card-title mb-4">
                        <i class="fas fa-certificate text-primary me-2"></i>
                        Thông tin yêu cầu chứng nhận
                    </h5>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Tên học viên</label>
                            <p class="form-control-plaintext"><?= htmlspecialchars($certificate['student_name']) ?></p>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Tên đăng nhập</label>
                            <p class="form-control-plaintext">
                                <?= !empty($certificate['username']) ? '@' . htmlspecialchars($certificate['username']) : '<span class="text-muted">Chưa có</span>' ?>
                            </p>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Số điện thoại</label>
                            <p class="form-control-plaintext">
                                <?= !empty($certificate['phone']) ? htmlspecialchars($certificate['phone']) : '<span class="text-muted">Chưa có</span>' ?>
                            </p>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Môn học</label>
                            <p class="form-control-plaintext">
                                <span class="badge bg-info"><?= htmlspecialchars($certificate['subject']) ?></span>
                            </p>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Trạng thái duyệt</label>
                            <p class="form-control-plaintext">
                                <?php
                                $colors = ['pending' => 'warning', 'approved' => 'success', 'cancelled' => 'danger'];
                                $labels = ['pending' => 'Chờ duyệt', 'approved' => 'Đã duyệt', 'cancelled' => 'Đã hủy'];
                                $color = $colors[$certificate['approval_status']] ?? 'secondary';
                                $label = $labels[$certificate['approval_status']] ?? $certificate['approval_status'];
                                ?>
                                <span class="badge bg-<?= $color ?>"><?= $label ?></span>
                            </p>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Trạng thái nhận</label>
                            <p class="form-control-plaintext">
                                <?php
                                $rColors = ['not_received' => 'warning', 'received' => 'success'];
                                $rLabels = ['not_received' => 'Chưa nhận', 'received' => 'Đã nhận'];
                                $rColor = $rColors[$certificate['receive_status']] ?? 'secondary';
                                $rLabel = $rLabels[$certificate['receive_status']] ?? $certificate['receive_status'];
                                ?>
                                <span class="badge bg-<?= $rColor ?>"><?= $rLabel ?></span>
                            </p>
                        </div>

                        <?php if (!empty($certificate['notes'])): ?>
                        <div class="col-12">
                            <label class="form-label fw-bold">Ghi chú</label>
                            <p class="form-control-plaintext"><?= nl2br(htmlspecialchars($certificate['notes'])) ?></p>
                        </div>
                        <?php endif; ?>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Ngày tạo</label>
                            <p class="form-control-plaintext">
                                <?= date('d/m/Y H:i', strtotime($certificate['created_at'])) ?>
                            </p>
                        </div>

                        <?php if (!empty($certificate['updated_at'])): ?>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Cập nhật lần cuối</label>
                            <p class="form-control-plaintext">
                                <?= date('d/m/Y H:i', strtotime($certificate['updated_at'])) ?>
                            </p>
                        </div>
                        <?php endif; ?>
                    </div>

                    <hr class="my-4">

                    <div class="d-flex gap-2 justify-content-end">
                        <?php if ($certificate['approval_status'] === 'pending'): ?>
                            <button type="button" class="btn btn-success" onclick="updateStatus(<?= $certificate['id'] ?>, 'approved')">
                                <i class="fas fa-check me-2"></i>Phê duyệt
                            </button>
                            <button type="button" class="btn btn-danger" onclick="updateStatus(<?= $certificate['id'] ?>, 'cancelled')">
                                <i class="fas fa-times me-2"></i>Hủy
                            </button>
                        <?php endif; ?>
                        
                        <?php if ($certificate['approval_status'] === 'approved' && $certificate['receive_status'] === 'not_received'): ?>
                            <button type="button" class="btn btn-info" onclick="updateReceiveStatus(<?= $certificate['id'] ?>, 'received')">
                                <i class="fas fa-hand-holding me-2"></i>Đánh dấu đã nhận
                            </button>
                        <?php endif; ?>
                        
                        <a href="/Quan_ly_trung_tam/public/certificates/<?= $certificate['id'] ?>/edit" class="btn btn-warning">
                            <i class="fas fa-edit me-2"></i>Chỉnh sửa
                        </a>
                        <a href="/Quan_ly_trung_tam/public/certificates" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Quay lại
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
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

function updateReceiveStatus(certId, status) {
    if (confirm('Xác nhận học viên đã nhận chứng nhận?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '/Quan_ly_trung_tam/public/certificates/' + certId + '/receive';
        
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'receive_status';
        input.value = status;
        
        form.appendChild(input);
        document.body.appendChild(form);
        form.submit();
    }
}
</script>

<?php
$content = ob_get_clean();
useModernLayout('Chi tiết yêu cầu chứng nhận', $content);
?>
