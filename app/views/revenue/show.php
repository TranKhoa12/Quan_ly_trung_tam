<?php
require_once __DIR__ . '/../layouts/main.php';

// Page content
ob_start();
?>

<?= pageHeader(
    'Chi tiết doanh thu', 
    'Thông tin chi tiết về giao dịch doanh thu', 
    '<a href="/Quan_ly_trung_tam/public/revenue" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-2"></i>Quay lại
    </a>'
) ?>

<div class="p-3">
    <div class="row g-4">
        <!-- Thông tin chung -->
        <div class="col-md-6">
            <div class="stats-card">
                <div class="card-body">
                    <h6 class="card-subtitle mb-3 text-primary">
                        <i class="fas fa-info-circle me-2"></i>Thông tin chung
                    </h6>
                    <table class="table table-borderless">
                        <tr>
                            <th width="40%" class="text-muted">Mã giao dịch:</th>
                            <td><strong>#<?= $revenue['id'] ?></strong></td>
                        </tr>
                        <tr>
                            <th class="text-muted">Ngày thanh toán:</th>
                            <td><?= date('d/m/Y', strtotime($revenue['payment_date'])) ?></td>
                        </tr>
                        <tr>
                            <th class="text-muted">Thời gian tạo:</th>
                            <td><?= date('d/m/Y H:i', strtotime($revenue['created_at'])) ?></td>
                        </tr>
                        <tr>
                            <th class="text-muted">Nhân viên:</th>
                            <td><?= htmlspecialchars($staffName) ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <!-- Thông tin thanh toán -->
        <div class="col-md-6">
            <div class="stats-card">
                <div class="card-body">
                    <h6 class="card-subtitle mb-3 text-primary">
                        <i class="fas fa-money-bill-wave me-2"></i>Thông tin thanh toán
                    </h6>
                    <table class="table table-borderless">
                        <tr>
                            <th width="40%" class="text-muted">Số tiền:</th>
                            <td><strong class="text-success fs-5"><?= number_format($revenue['amount']) ?> đ</strong></td>
                        </tr>
                        <tr>
                            <th class="text-muted">Hình thức:</th>
                            <td>
                                <?php
                                $transferTypes = [
                                    'cash' => ['label' => 'Tiền mặt', 'color' => 'warning', 'icon' => 'fas fa-coins'],
                                    'account_co_nhi' => ['label' => 'TK Cô Nhi', 'color' => 'primary', 'icon' => 'fas fa-university'],
                                    'account_thay_hien' => ['label' => 'TK Thầy Hiến', 'color' => 'info', 'icon' => 'fas fa-university'],
                                    'account_company' => ['label' => 'TK Công ty', 'color' => 'dark', 'icon' => 'fas fa-building']
                                ];
                                $type = $transferTypes[$revenue['transfer_type']] ?? ['label' => $revenue['transfer_type'], 'color' => 'secondary', 'icon' => 'fas fa-question'];
                                ?>
                                <span class="badge bg-<?= $type['color'] ?>">
                                    <i class="<?= $type['icon'] ?> me-1"></i><?= $type['label'] ?>
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th class="text-muted">Loại thanh toán:</th>
                            <td>
                                <?php
                                $paymentTypes = [
                                    'full_payment' => ['label' => 'Thanh toán đầy đủ', 'color' => 'success'],
                                    'deposit' => ['label' => 'Đặt cọc', 'color' => 'warning'],
                                    'l1_payment' => ['label' => 'Thanh toán L1', 'color' => 'secondary'],
                                    'l2_payment' => ['label' => 'Thanh toán L2', 'color' => 'secondary'],
                                    'l3_payment' => ['label' => 'Thanh toán L3', 'color' => 'secondary']
                                ];
                                $payment = $paymentTypes[$revenue['payment_content']] ?? ['label' => $revenue['payment_content'], 'color' => 'light'];
                                ?>
                                <span class="badge bg-<?= $payment['color'] ?>"><?= $payment['label'] ?></span>
                            </td>
                        </tr>
                        <tr>
                            <th class="text-muted">Mã biên lai:</th>
                            <td>
                                <?php if (!empty($revenue['receipt_code'])): ?>
                                    <code class="text-primary"><?= htmlspecialchars($revenue['receipt_code']) ?></code>
                                <?php else: ?>
                                    <span class="text-muted">Chưa có</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <!-- Thông tin học viên và khóa học -->
        <div class="col-12">
            <div class="stats-card">
                <div class="card-body">
                    <h6 class="card-subtitle mb-3 text-primary">
                        <i class="fas fa-user-graduate me-2"></i>Thông tin học viên
                    </h6>
                    <table class="table table-borderless">
                        <tr>
                            <th width="20%" class="text-muted">Tên học viên:</th>
                            <td><strong><?= htmlspecialchars($revenue['student_name']) ?></strong></td>
                        </tr>
                        <tr>
                            <th class="text-muted">Khóa học:</th>
                            <td>
                                <?php if ($courseName): ?>
                                    <span class="badge bg-info"><?= htmlspecialchars($courseName) ?></span>
                                <?php else: ?>
                                    <span class="text-muted">Chưa chọn khóa học</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <!-- Ghi chú -->
        <?php if (!empty($revenue['notes'])): ?>
        <div class="col-12">
            <div class="stats-card">
                <div class="card-body">
                    <h6 class="card-subtitle mb-3 text-primary">
                        <i class="fas fa-sticky-note me-2"></i>Ghi chú
                    </h6>
                    <p class="mb-0"><?= nl2br(htmlspecialchars($revenue['notes'])) ?></p>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Action buttons -->
        <div class="col-12">
            <div class="d-flex gap-2">
                <a href="/Quan_ly_trung_tam/public/revenue" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Quay lại danh sách
                </a>
                <?php if ($userRole === 'admin'): ?>
                <a href="/Quan_ly_trung_tam/public/revenue/<?= $revenue['id'] ?>/edit" class="btn btn-warning">
                    <i class="fas fa-edit me-2"></i>Chỉnh sửa
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
renderLayout('Quan Ly Trung Tam', $content);
?>
