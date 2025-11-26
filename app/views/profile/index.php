<?php
require_once __DIR__ . '/../layouts/main.php';

ob_start();
?>

<?php
// Header configuration
$headerTitle = 'Hồ sơ cá nhân';
$headerDesc = 'Quản lý thông tin cá nhân và bảo mật tài khoản';
$headerButton = '
    <div class="d-flex gap-2">
        <span class="badge bg-light text-dark border p-2">
            <i class="fas fa-clock me-1"></i>Thành viên từ: ' . (isset($user['created_at']) ? date('d/m/Y', strtotime($user['created_at'])) : 'N/A') . '
        </span>
    </div>';
?>

<?= pageHeader($headerTitle, $headerDesc, $headerButton) ?>

<div class="p-3">
    <div class="row g-4">
        <!-- Left Column: Profile Card -->
        <div class="col-lg-4">
            <div class="stats-card h-100">
                <div class="card-body text-center pt-5 pb-4">
                    <div class="position-relative d-inline-block mb-4">
                        <div class="avatar-xl bg-primary-subtle rounded-circle d-flex align-items-center justify-content-center mx-auto" style="width: 120px; height: 120px; border: 4px solid #fff; box-shadow: 0 0 0 1px rgba(0,0,0,0.1);">
                            <i class="fas fa-user fa-4x text-primary"></i>
                        </div>
                        <div class="position-absolute bottom-0 end-0">
                            <span class="badge rounded-pill bg-success border border-white p-2" title="Active">
                                <span class="visually-hidden">Active</span>
                            </span>
                        </div>
                    </div>
                    
                    <h4 class="fw-bold mb-1"><?= htmlspecialchars($user['full_name']) ?></h4>
                    <p class="text-muted mb-3"><?= ucfirst($user['role']) ?></p>
                    
                    <div class="d-flex justify-content-center gap-2 mb-4">
                        <span class="badge bg-primary-subtle text-primary px-3 py-2 rounded-pill">
                            <i class="fas fa-shield-alt me-1"></i><?= ucfirst($user['role']) ?>
                        </span>
                        <span class="badge bg-success-subtle text-success px-3 py-2 rounded-pill">
                            <i class="fas fa-check-circle me-1"></i><?= ucfirst($user['status']) ?>
                        </span>
                    </div>

                    <hr class="my-4 opacity-10">

                    <div class="text-start px-3">
                        <h6 class="text-uppercase text-muted small fw-bold mb-3">Thông tin liên hệ</h6>
                        
                        <div class="d-flex align-items-center mb-3">
                            <div class="icon-box bg-light rounded-circle p-2 me-3 text-primary" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <div>
                                <small class="text-muted d-block">Email</small>
                                <span class="fw-medium"><?= htmlspecialchars($user['email'] ?? 'Chưa cập nhật') ?></span>
                            </div>
                        </div>
                        
                        <div class="d-flex align-items-center mb-3">
                            <div class="icon-box bg-light rounded-circle p-2 me-3 text-primary" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-phone"></i>
                            </div>
                            <div>
                                <small class="text-muted d-block">Số điện thoại</small>
                                <span class="fw-medium"><?= htmlspecialchars($user['phone'] ?? 'Chưa cập nhật') ?></span>
                            </div>
                        </div>

                        <div class="d-flex align-items-center">
                            <div class="icon-box bg-light rounded-circle p-2 me-3 text-primary" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                            <div>
                                <small class="text-muted d-block">Địa chỉ</small>
                                <span class="fw-medium"><?= htmlspecialchars($user['address'] ?? 'Chưa cập nhật') ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Right Column: Edit Form -->
        <div class="col-lg-8">
            <div class="stats-card h-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-edit text-primary me-2"></i>Cập nhật thông tin
                        </h5>
                    </div>

                    <?php if (isset($success)): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <div class="d-flex">
                                <i class="fas fa-check-circle mt-1 me-3"></i>
                                <div>
                                    <strong>Thành công!</strong> <?= $success ?>
                                </div>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <div class="d-flex">
                                <i class="fas fa-exclamation-circle mt-1 me-3"></i>
                                <div>
                                    <strong>Lỗi!</strong> <?= $error ?>
                                </div>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <form action="/Quan_ly_trung_tam/public/profile/update" method="POST">
                        <div class="row g-4">
                            <div class="col-12">
                                <h6 class="text-uppercase text-muted small fw-bold mb-3">Thông tin cơ bản</h6>
                            </div>

                            <div class="col-md-6">
                                <label for="username" class="form-label">Tên đăng nhập</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="fas fa-user"></i></span>
                                    <input type="text" class="form-control bg-light" id="username" value="<?= htmlspecialchars($user['username']) ?>" readonly disabled>
                                </div>
                                <div class="form-text">Tên đăng nhập không thể thay đổi.</div>
                            </div>

                            <div class="col-md-6">
                                <label for="full_name" class="form-label">Họ và tên <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-id-card"></i></span>
                                    <input type="text" class="form-control" id="full_name" name="full_name" value="<?= htmlspecialchars($user['full_name']) ?>" required>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="email" class="form-label">Email</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                    <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($user['email'] ?? '') ?>">
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="phone" class="form-label">Số điện thoại</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                    <input type="text" class="form-control" id="phone" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
                                </div>
                            </div>
                            
                            <div class="col-12">
                                <label for="address" class="form-label">Địa chỉ</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-map-marker-alt"></i></span>
                                    <input type="text" class="form-control" id="address" name="address" value="<?= htmlspecialchars($user['address'] ?? '') ?>">
                                </div>
                            </div>

                            <div class="col-12">
                                <hr class="my-2 opacity-10">
                                <h6 class="text-uppercase text-muted small fw-bold mb-3 mt-3">Bảo mật</h6>
                            </div>

                            <div class="col-md-6">
                                <label for="password" class="form-label">Mật khẩu mới</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                    <input type="password" class="form-control" id="password" name="password" minlength="6" placeholder="Để trống nếu không đổi">
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="password_confirm" class="form-label">Xác nhận mật khẩu</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                    <input type="password" class="form-control" id="password_confirm" name="password_confirm" minlength="6" placeholder="Nhập lại mật khẩu mới">
                                </div>
                            </div>
                            
                            <div class="col-12 mt-4 text-end">
                                <button type="reset" class="btn btn-light me-2">
                                    <i class="fas fa-undo me-2"></i>Đặt lại
                                </button>
                                <button type="submit" class="btn btn-primary px-4">
                                    <i class="fas fa-save me-2"></i>Lưu thay đổi
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();

// Render layout with modern UI
useModernLayout('Hồ sơ cá nhân', $content);
?>
