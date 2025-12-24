<?php
require_once __DIR__ . '/../layouts/main.php';

// Page content
ob_start();
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-4 border-bottom">
    <div>
        <h1 class="h2 mb-2">
            <i class="fas fa-edit text-primary me-2"></i>
            Chỉnh sửa báo cáo doanh thu
        </h1>
        <p class="text-muted mb-0">Chỉ admin có quyền chỉnh sửa giao dịch doanh thu</p>
    </div>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="/Quan_ly_trung_tam/public/revenue" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Quay lại danh sách
        </a>
    </div>
</div>

<div class="p-3">
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>
            <?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            <?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="stats-card">
        <div class="card-body">
            <form method="POST" action="/Quan_ly_trung_tam/public/revenue/<?= $revenue['id'] ?>/update" enctype="multipart/form-data">
                <div class="row">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Thông tin thanh toán</h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="payment_date" class="form-label">Ngày đóng học phí *</label>
                                    <input type="date" class="form-control" id="payment_date" name="payment_date"
                                           value="<?= htmlspecialchars(date('Y-m-d', strtotime($revenue['payment_date']))) ?>" required>
                                </div>

                                <div class="mb-3">
                                    <label for="transfer_type" class="form-label">Hình thức chuyển khoản *</label>
                                    <select class="form-select" id="transfer_type" name="transfer_type" required>
                                        <?php
                                            $transferOptions = [
                                                'cash' => 'Tiền mặt',
                                                'account_co_nhi' => 'TK Cô Nhi',
                                                'account_thay_hien' => 'TK Thầy Hiến',
                                                'account_company' => 'TK Công ty'
                                            ];
                                            foreach ($transferOptions as $value => $label):
                                        ?>
                                        <option value="<?= $value ?>" <?= ($revenue['transfer_type'] === $value) ? 'selected' : '' ?>>
                                            <?= $label ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label for="receipt_code" class="form-label">Mã phiếu thu</label>
                                    <input type="text" class="form-control" id="receipt_code" name="receipt_code"
                                           value="<?= htmlspecialchars($revenue['receipt_code'] ?? '') ?>" autocomplete="off">
                                </div>

                                <div class="mb-3">
                                    <label for="amount" class="form-label">Số tiền *</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="amount" name="amount"
                                               value="<?= number_format((float)$revenue['amount']) ?>" required inputmode="numeric">
                                        <span class="input-group-text">VNĐ</span>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Ảnh xác nhận chuyển khoản/phiếu thu (tùy chọn)</label>
                                    <input type="file" class="form-control" name="confirmation_images[]" accept="image/*" multiple>
                                    <div class="form-text">Thêm ảnh mới nếu cần, ảnh hiện có sẽ được giữ lại.</div>
                                    <?php
                                        $existingImages = [];
                                        if (!empty($revenue['confirmation_images'])) {
                                            $decoded = json_decode($revenue['confirmation_images'], true);
                                            if (is_array($decoded)) { $existingImages = array_values(array_filter($decoded)); }
                                        }
                                        if (empty($existingImages) && !empty($revenue['confirmation_image'])) {
                                            $existingImages[] = $revenue['confirmation_image'];
                                        }
                                    ?>
                                    <?php if (!empty($existingImages)): ?>
                                    <div class="mt-2 d-flex flex-wrap gap-2" id="existingImages">
                                        <?php foreach ($existingImages as $img): ?>
                                        <div class="d-flex align-items-center gap-2 border rounded px-2 py-1" data-filename="<?= htmlspecialchars($img) ?>">
                                            <a href="/Quan_ly_trung_tam/public/uploads/<?= htmlspecialchars($img) ?>" target="_blank" class="btn btn-sm btn-outline-info">
                                                <i class="fas fa-image me-1"></i><?= htmlspecialchars($img) ?>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteImage('<?= htmlspecialchars($img) ?>')">
                                                <i class="fas fa-trash me-1"></i>Xóa
                                            </button>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Thông tin học viên</h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="student_name" class="form-label">Họ tên học viên *</label>
                                    <input type="text" class="form-control" id="student_name" name="student_name"
                                           value="<?= htmlspecialchars($revenue['student_name']) ?>" required>
                                </div>

                                <div class="mb-3">
                                    <label for="course_id" class="form-label">Khóa học</label>
                                    <select class="form-select" id="course_id" name="course_id">
                                        <option value="">-- Không chọn --</option>
                                        <?php foreach ($courses as $course): ?>
                                            <option value="<?= $course['id'] ?>" <?= ($revenue['course_id'] == $course['id']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($course['course_name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label for="payment_content" class="form-label">Nội dung thanh toán *</label>
                                    <select class="form-select" id="payment_content" name="payment_content" required>
                                        <?php
                                            $paymentOptions = [
                                                'full_payment' => 'Thanh toán đủ',
                                                'deposit' => 'Đặt cọc',
                                                'full_payment_after_deposit' => 'Thanh toán sau cọc',
                                                'accounting_deposit' => 'Cọc học phí (kế toán)',
                                                'l1_payment' => 'Thanh toán L1',
                                                'l2_payment' => 'Thanh toán L2',
                                                'l3_payment' => 'Thanh toán L3'
                                            ];
                                            foreach ($paymentOptions as $value => $label):
                                        ?>
                                        <option value="<?= $value ?>" <?= ($revenue['payment_content'] === $value) ? 'selected' : '' ?>>
                                            <?= $label ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label for="staff_id" class="form-label">Nhân viên phụ trách</label>
                                    <select class="form-select" id="staff_id" name="staff_id">
                                        <?php foreach ($staff as $st): ?>
                                            <option value="<?= $st['id'] ?>" <?= ($revenue['staff_id'] == $st['id']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($st['full_name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label for="notes" class="form-label">Ghi chú</label>
                                    <textarea class="form-control" id="notes" name="notes" rows="3" placeholder="Ghi chú thêm nếu có..."><?= htmlspecialchars($revenue['notes'] ?? '') ?></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-12 d-flex gap-2">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save me-2"></i>Lưu thay đổi
                        </button>
                        <a href="/Quan_ly_trung_tam/public/revenue" class="btn btn-secondary">
                            <i class="fas fa-times me-2"></i>Hủy
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Format amount with thousand separators
const amountInput = document.getElementById('amount');
amountInput.addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    if (value) { value = parseInt(value).toLocaleString('en-US'); }
    e.target.value = value;
});

// Prevent future date
const dateInput = document.getElementById('payment_date');
dateInput.addEventListener('change', function(e) {
    const selected = new Date(e.target.value);
    const today = new Date();
    today.setHours(0,0,0,0);
    if (selected > today) {
        alert('Ngày đóng học phí không được vượt quá ngày hôm nay!');
        e.target.value = new Date().toISOString().split('T')[0];
    }
});
</script>

<script>
function deleteImage(filename) {
    if (!confirm('Bạn chắc chắn muốn xóa ảnh này?')) return;
    const btns = document.querySelectorAll(`#existingImages [data-filename='${CSS.escape(filename)}'] button`);
    btns.forEach(btn => { btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Đang xóa'; });

    fetch('/Quan_ly_trung_tam/public/revenue/<?= $revenue['id'] ?>/image/delete', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({ file: filename })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            const item = document.querySelector(`#existingImages [data-filename='${CSS.escape(filename)}']`);
            if (item) item.remove();
        } else {
            alert('Lỗi: ' + (data.message || 'Không thể xóa ảnh'));
            btns.forEach(btn => { btn.disabled = false; btn.innerHTML = '<i class="fas fa-trash me-1"></i>Xóa'; });
        }
    })
    .catch(err => {
        alert('Lỗi kết nối: ' + err.message);
        btns.forEach(btn => { btn.disabled = false; btn.innerHTML = '<i class="fas fa-trash me-1"></i>Xóa'; });
    });
}
</script>

<?php
$content = ob_get_clean();
useModernLayout('Chỉnh sửa doanh thu', $content);
?>
