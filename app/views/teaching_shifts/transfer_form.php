<?php
require_once __DIR__ . '/../layouts/main.php';

$scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
$scriptDir = str_replace('\\', '/', dirname($scriptName));
$basePath = rtrim($scriptDir, '/');
if ($basePath === '' || $basePath === '.') {
    $basePath = '';
}

ob_start();
?>

<div class="container-fluid py-4 transfer-page">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0">
                <div class="card-header gradient-header text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-exchange-alt me-2"></i>
                        Yêu cầu chuyển ca
                    </h5>
                </div>
                <div class="card-body">
                    <!-- Thông tin ca hiện tại -->
                    <div class="alert alert-info border-0 mb-4">
                        <h6 class="alert-heading">
                            <i class="fas fa-info-circle me-2"></i>
                            Thông tin ca dạy
                        </h6>
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <p class="mb-2">
                                    <strong>Ngày dạy:</strong> 
                                    <?= date('d/m/Y', strtotime($registration['shift_date'])) ?>
                                </p>
                                <p class="mb-0">
                                    <strong>Giờ:</strong> 
                                    <?= date('H:i', strtotime($registration['custom_start'])) ?> - 
                                    <?= date('H:i', strtotime($registration['custom_end'])) ?>
                                </p>
                            </div>
                            <div class="col-md-6">
                                <?php if (!empty($registration['shift_name'])): ?>
                                    <p class="mb-2">
                                        <strong>Ca:</strong> 
                                        <?= htmlspecialchars($registration['shift_name']) ?>
                                    </p>
                                <?php endif; ?>
                                <?php if (!empty($registration['notes'])): ?>
                                    <p class="mb-0">
                                        <strong>Ghi chú:</strong> 
                                        <?= htmlspecialchars($registration['notes']) ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Form chuyển ca -->
                    <form method="POST" action="<?= $basePath ?>/teaching-shifts/transfer/store">
                        <input type="hidden" name="registration_id" value="<?= $registration['id'] ?>">
                        
                        <div class="mb-4">
                            <label class="form-label fw-bold">
                                <i class="fas fa-user me-2 text-primary"></i>
                                Chuyển ca cho nhân viên <span class="text-danger">*</span>
                            </label>
                            <select name="to_staff_id" class="form-select" id="staffSelect" required>
                                <option value="">-- Chọn nhân viên nhận ca --</option>
                                <?php foreach ($otherStaff as $staff): ?>
                                    <option value="<?= $staff['id'] ?>" 
                                            data-conflicts="<?= isset($staffConflicts[$staff['id']]) ? '1' : '0' ?>">
                                        <?= htmlspecialchars($staff['full_name']) ?> 
                                        (<?= htmlspecialchars($staff['email']) ?>)
                                        <?php if (isset($staffConflicts[$staff['id']])): ?>
                                            ⚠️ Trùng ca
                                        <?php endif; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <small class="form-text text-muted">
                                Chọn nhân viên sẽ nhận ca thay bạn
                            </small>
                            <div id="conflictWarning" class="alert alert-danger mt-2" style="display: none;">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <strong>Cảnh báo:</strong> Nhân viên này đã có ca dạy trùng giờ trong ngày này. Vui lòng chọn nhân viên khác!
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">
                                <i class="fas fa-comment-dots me-2 text-primary"></i>
                                Lý do chuyển ca <span class="text-danger">*</span>
                            </label>
                            <textarea name="reason" class="form-control" rows="4" required 
                                placeholder="Vui lòng nhập lý do chuyển ca (ví dụ: bận việc gia đình, ốm đau, v.v.)"></textarea>
                            <small class="form-text text-muted">
                                Lý do sẽ được gửi đến admin để xem xét
                            </small>
                        </div>

                        <div class="alert alert-warning border-0">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Lưu ý:</strong>
                            <ul class="mb-0 mt-2">
                                <li>Yêu cầu chuyển ca cần được <strong>admin duyệt</strong> mới có hiệu lực</li>
                                <li>Sau khi gửi yêu cầu, bạn vẫn phải giữ ca cho đến khi được duyệt</li>
                                <li>Admin có thể từ chối yêu cầu nếu không hợp lý</li>
                            </ul>
                        </div>

                        <div class="d-flex gap-3 justify-content-end mt-4">
                            <a href="<?= $basePath ?>/teaching-shifts" class="btn btn-secondary">
                                <i class="fas fa-times me-2"></i>
                                Hủy
                            </a>
                            <button type="submit" class="btn btn-primary" id="submitBtn">
                                <i class="fas fa-paper-plane me-2"></i>
                                Gửi yêu cầu
                            </button>
                        </div>
                    </form>
                    
                    <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        const staffSelect = document.getElementById('staffSelect');
                        const conflictWarning = document.getElementById('conflictWarning');
                        const submitBtn = document.getElementById('submitBtn');
                        
                        staffSelect.addEventListener('change', function() {
                            const selectedOption = this.options[this.selectedIndex];
                            const hasConflict = selectedOption.dataset.conflicts === '1';
                            
                            if (hasConflict) {
                                conflictWarning.style.display = 'block';
                                submitBtn.disabled = true;
                            } else {
                                conflictWarning.style.display = 'none';
                                submitBtn.disabled = false;
                            }
                        });
                    });
                    </script>
                </div>
            </div>
        </div>
    </div>
<?php require_once __DIR__ . '/partials/transfer_styles.php'; ?>

<?php
$content = ob_get_clean();
useModernLayout('Yêu cầu chuyển ca', $content);
?>
