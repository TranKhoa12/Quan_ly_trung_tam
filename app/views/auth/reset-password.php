<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đặt lại mật khẩu - Hệ thống quản lý trung tâm</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .reset-password-container {
            max-width: 500px;
            margin: 100px auto;
        }
        .card {
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }
        .password-strength {
            font-size: 0.85em;
            margin-top: 5px;
        }
        .strength-weak { color: #dc3545; }
        .strength-medium { color: #ffc107; }
        .strength-strong { color: #28a745; }
    </style>
</head>
<body>
    <div class="container">
        <div class="reset-password-container">
            <div class="card">
                <div class="card-body p-4">
                    <div class="text-center mb-4">
                        <i class="fas fa-lock fa-3x text-primary mb-3"></i>
                        <h4 class="card-title">Đặt lại mật khẩu</h4>
                        <?php if (isset($user)): ?>
                            <p class="text-muted">Đặt mật khẩu mới cho tài khoản: <strong><?= htmlspecialchars($user['username']) ?></strong></p>
                        <?php endif; ?>
                    </div>
                    
                    <?php if (isset($success) && $success): ?>
                        <div class="alert alert-success text-center">
                            <i class="fas fa-check-circle fa-2x mb-3 d-block"></i>
                            <h5>Thành công!</h5>
                            <?= htmlspecialchars($message) ?>
                            <hr>
                            <a href="/Quan_ly_trung_tam/public/login" class="btn btn-success">
                                <i class="fas fa-sign-in-alt"></i> Đăng nhập ngay
                            </a>
                        </div>
                    <?php elseif (isset($invalid_token) && $invalid_token): ?>
                        <div class="alert alert-danger text-center">
                            <i class="fas fa-exclamation-triangle fa-2x mb-3 d-block"></i>
                            <h5>Token không hợp lệ</h5>
                            <?= htmlspecialchars($error) ?>
                            <hr>
                            <a href="/Quan_ly_trung_tam/public/forgot-password" class="btn btn-primary">
                                <i class="fas fa-redo"></i> Yêu cầu đặt lại mới
                            </a>
                        </div>
                    <?php else: ?>
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-triangle"></i>
                                <?= htmlspecialchars($error) ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (isset($errors) && !empty($errors)): ?>
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    <?php foreach ($errors as $field => $error): ?>
                                        <li><?= htmlspecialchars($error) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST" action="/Quan_ly_trung_tam/public/reset-password" id="resetForm">
                            <input type="hidden" name="token" value="<?= htmlspecialchars($token ?? '') ?>">
                            
                            <div class="mb-3">
                                <label for="password" class="form-label">
                                    <i class="fas fa-lock"></i> Mật khẩu mới
                                </label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="password" name="password" 
                                           placeholder="Nhập mật khẩu mới" required minlength="6">
                                    <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                        <i class="fas fa-eye" id="toggleIcon"></i>
                                    </button>
                                </div>
                                <div id="passwordStrength" class="password-strength"></div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">
                                    <i class="fas fa-lock"></i> Xác nhận mật khẩu
                                </label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                                       placeholder="Nhập lại mật khẩu mới" required>
                                <div id="passwordMatch" class="password-strength"></div>
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary" id="submitBtn">
                                    <i class="fas fa-save"></i> Đặt lại mật khẩu
                                </button>
                            </div>
                        </form>
                    <?php endif; ?>
                    
                    <div class="text-center mt-4">
                        <a href="/Quan_ly_trung_tam/public/login" class="text-decoration-none">
                            <i class="fas fa-arrow-left"></i> Quay lại đăng nhập
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle password visibility
        document.getElementById('togglePassword').addEventListener('click', function() {
            const password = document.getElementById('password');
            const icon = document.getElementById('toggleIcon');
            
            if (password.type === 'password') {
                password.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                password.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });

        // Password strength checker
        document.getElementById('password').addEventListener('input', function() {
            const password = this.value;
            const strengthDiv = document.getElementById('passwordStrength');
            
            if (password.length === 0) {
                strengthDiv.innerHTML = '';
                return;
            }
            
            let strength = 0;
            let feedback = [];
            
            // Length check
            if (password.length >= 8) strength++;
            else feedback.push('Ít nhất 8 ký tự');
            
            // Uppercase check
            if (/[A-Z]/.test(password)) strength++;
            else feedback.push('Chữ hoa');
            
            // Lowercase check
            if (/[a-z]/.test(password)) strength++;
            else feedback.push('Chữ thường');
            
            // Number check
            if (/[0-9]/.test(password)) strength++;
            else feedback.push('Số');
            
            // Special character check
            if (/[^A-Za-z0-9]/.test(password)) strength++;
            else feedback.push('Ký tự đặc biệt');
            
            let message, className;
            if (strength < 2) {
                message = '<i class="fas fa-times-circle"></i> Yếu';
                className = 'strength-weak';
            } else if (strength < 4) {
                message = '<i class="fas fa-exclamation-circle"></i> Trung bình';
                className = 'strength-medium';
            } else {
                message = '<i class="fas fa-check-circle"></i> Mạnh';
                className = 'strength-strong';
            }
            
            if (feedback.length > 0 && strength < 4) {
                message += ' (Thiếu: ' + feedback.join(', ') + ')';
            }
            
            strengthDiv.innerHTML = message;
            strengthDiv.className = 'password-strength ' + className;
        });

        // Password match checker
        document.getElementById('confirm_password').addEventListener('input', function() {
            const password = document.getElementById('password').value;
            const confirmPassword = this.value;
            const matchDiv = document.getElementById('passwordMatch');
            
            if (confirmPassword.length === 0) {
                matchDiv.innerHTML = '';
                return;
            }
            
            if (password === confirmPassword) {
                matchDiv.innerHTML = '<i class="fas fa-check-circle"></i> Mật khẩu khớp';
                matchDiv.className = 'password-strength strength-strong';
            } else {
                matchDiv.innerHTML = '<i class="fas fa-times-circle"></i> Mật khẩu không khớp';
                matchDiv.className = 'password-strength strength-weak';
            }
        });

        // Form validation
        document.getElementById('resetForm').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (password.length < 6) {
                e.preventDefault();
                alert('Mật khẩu phải có ít nhất 6 ký tự');
                return false;
            }
            
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Mật khẩu xác nhận không khớp');
                return false;
            }
        });
    </script>
</body>
</html>