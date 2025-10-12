<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quên mật khẩu - Hệ thống quản lý trung tâm</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .forgot-password-container {
            max-width: 500px;
            margin: 100px auto;
        }
        .card {
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="forgot-password-container">
            <div class="card">
                <div class="card-body p-4">
                    <div class="text-center mb-4">
                        <i class="fas fa-key fa-3x text-primary mb-3"></i>
                        <h4 class="card-title">Quên mật khẩu</h4>
                        <p class="text-muted">Nhập tên đăng nhập hoặc email để đặt lại mật khẩu</p>
                    </div>
                    
                    <?php if (isset($success) && $success): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i>
                            <?= htmlspecialchars($message) ?>
                            <?php if (isset($reset_link)): ?>
                                <hr>
                                <strong>Link đặt lại mật khẩu:</strong><br>
                                <a href="<?= htmlspecialchars($reset_link) ?>" class="btn btn-primary btn-sm mt-2">
                                    <i class="fas fa-link"></i> Đặt lại mật khẩu
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle"></i>
                            <?= htmlspecialchars($error) ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (isset($errors) && !empty($errors)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($errors as $error): ?>
                                    <li><?= htmlspecialchars($error) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!isset($success) || !$success): ?>
                        <form method="POST" action="/Quan_ly_trung_tam/public/forgot-password">
                            <div class="mb-3">
                                <label for="identifier" class="form-label">
                                    <i class="fas fa-user"></i> Tên đăng nhập hoặc Email
                                </label>
                                <input type="text" class="form-control" id="identifier" name="identifier" 
                                       value="<?= htmlspecialchars($_POST['identifier'] ?? '') ?>" 
                                       placeholder="Nhập tên đăng nhập hoặc email" required>
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-paper-plane"></i> Gửi yêu cầu đặt lại
                                </button>
                            </div>
                        </form>
                    <?php endif; ?>
                    
                    <div class="text-center mt-4">
                        <a href="/Quan_ly_trung_tam/public/login" class="text-decoration-none">
                            <i class="fas fa-arrow-left"></i> Quay lại đăng nhập
                        </a>
                    </div>
                    
                    <?php if (!isset($success) || !$success): ?>
                        <div class="mt-4 p-3 bg-light rounded">
                            <small class="text-muted">
                                <strong>Tài khoản demo:</strong><br>
                                - Tên đăng nhập: <code>admin</code> hoặc Email: <code>admin@example.com</code><br>
                                - Tên đăng nhập: <code>staff1</code> hoặc Email: <code>staff1@example.com</code>
                            </small>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>