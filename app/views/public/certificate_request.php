<?php
$success = $success ?? '';
$error = $error ?? '';
$formData = $old_data ?? [];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yêu cầu cấp chứng nhận - Trung tâm đào tạo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            padding: 20px 0;
        }
        .certificate-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
        }
        .certificate-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px 30px;
            text-align: center;
        }
        .certificate-header i {
            font-size: 60px;
            margin-bottom: 15px;
        }
        .certificate-body {
            padding: 40px 30px;
        }
        .form-label {
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
        }
        .form-control, .form-select {
            border-radius: 10px;
            border: 2px solid #e0e0e0;
            padding: 12px 15px;
            transition: all 0.3s;
        }
        .form-control:focus, .form-select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .btn-submit {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 10px;
            padding: 15px 40px;
            font-weight: 600;
            font-size: 16px;
            transition: transform 0.3s;
        }
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.4);
        }
        .info-box {
            background: #f8f9fa;
            border-left: 4px solid #667eea;
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 30px;
        }
        .required-mark {
            color: #dc3545;
            font-weight: bold;
        }
        .qr-section {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            padding: 30px;
            text-align: center;
            margin-bottom: 30px;
        }
        .qr-section h5 {
            color: #667eea;
            font-weight: 700;
            margin-bottom: 15px;
        }
        .qr-code-container {
            background: white;
            padding: 20px;
            border-radius: 15px;
            display: inline-block;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        @media (max-width: 768px) {
            .qr-section {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <!-- QR Code Section - Only visible on desktop -->
            <div class="col-lg-8 qr-section d-none d-lg-block">
                <h5>
                    <i class="fas fa-qrcode me-2"></i>
                    Quét mã QR để điền form trên điện thoại
                </h5>
                <p class="text-muted mb-3">Sử dụng camera điện thoại để quét mã QR và truy cập form</p>
                <div class="qr-code-container">
                    <div id="qrcode"></div>
                </div>
                <p class="text-muted mt-3 mb-0">
                    <small>
                        <i class="fas fa-mobile-alt me-1"></i>
                        Hoặc cuộn xuống để điền form trực tiếp trên máy tính
                    </small>
                </p>
            </div>

            <div class="col-lg-8">
                <div class="certificate-card">
                    <div class="certificate-header">
                        <i class="fas fa-certificate"></i>
                        <h2 class="mb-2">Yêu cầu cấp chứng nhận</h2>
                        <p class="mb-0">Tin học học Sao Việt CN Bình Thạnh</p>
                    </div>
                    
                    <div class="certificate-body">
                        <?php if (!empty($success)): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="fas fa-check-circle me-2"></i>
                                <?= htmlspecialchars($success) ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($error)): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-circle me-2"></i>
                                <?= htmlspecialchars($error) ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <div class="info-box">
                            <i class="fas fa-info-circle text-primary me-2"></i>
                            <strong>Hướng dẫn:</strong> Vui lòng điền đầy đủ thông tin bên dưới. 
                            Yêu cầu của bạn sẽ được gửi và bạn sẽ nhận được thông báo qua email khi chứng nhận hoàn thành.
                        </div>

                        <form method="POST" action="/Quan_ly_trung_tam/public/certificate-request" id="certificateForm">
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label for="student_name" class="form-label">
                                        <i class="fas fa-user text-primary me-1"></i>
                                        Họ và tên <span class="required-mark">*</span>
                                    </label>
                                    <input type="text" class="form-control" id="student_name" name="student_name" 
                                           value="<?= htmlspecialchars($formData['student_name'] ?? '') ?>" required 
                                           placeholder="Nhập họ tên đầy đủ">
                                </div>

                                <div class="col-md-6">
                                    <label for="username" class="form-label">
                                        <i class="fas fa-user-circle text-info me-1"></i>
                                        Tên đăng nhập <span class="required-mark">*</span>
                                    </label>
                                    <input type="text" class="form-control" id="username" name="username" 
                                           value="<?= htmlspecialchars($formData['username'] ?? '') ?>" required
                                           placeholder="Tên tài khoản học tại trung tâm">
                                    <small class="text-muted">Mặc định là số điện thoại</small>
                                </div>

                                <div class="col-md-6">
                                    <label for="phone" class="form-label">
                                        <i class="fas fa-phone text-success me-1"></i>
                                        Số điện thoại <span class="required-mark">*</span>
                                    </label>
                                    <input type="tel" class="form-control" id="phone" name="phone" 
                                           value="<?= htmlspecialchars($formData['phone'] ?? '') ?>" required 
                                           placeholder="0xxxxxxxxx" maxlength="11" pattern="[0-9]{10,11}">
                                    <small class="text-muted">Nhập 10-11 chữ số</small>
                                </div>

                                <div class="col-md-6">
                                    <label for="subject" class="form-label">
                                        <i class="fas fa-book text-warning me-1"></i>
                                        Bộ môn <span class="required-mark">*</span>
                                    </label>
                                    <select class="form-select" id="subject" name="subject" required>
                                        <option value="">-- Chọn bộ môn đã học --</option>
                                        <?php
                                        $subjects = [
                                            'Tin học văn phòng',
                                            'Trí tuệ nhân tạo (AI)',
                                            'Kế toán',
                                            'Thiết kế đồ họa',
                                            'Vẽ kỹ thuật',
                                            'Tin học trẻ em',
                                            'Lập trình',
                                            'MOS',
                                            'IC3',
                                            'Quảng cáo'
                                        ];
                                        foreach ($subjects as $subject):
                                            $selected = (isset($formData['subject']) && $formData['subject'] === $subject) ? 'selected' : '';
                                        ?>
                                            <option value="<?= htmlspecialchars($subject) ?>" <?= $selected ?>><?= htmlspecialchars($subject) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="col-12">
                                    <label for="email" class="form-label">
                                        <i class="fas fa-envelope text-danger me-1"></i>
                                        Email <span class="required-mark">*</span>
                                    </label>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           value="<?= htmlspecialchars($formData['email'] ?? '') ?>" required
                                           placeholder="example@email.com">
                                    <small class="text-muted">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Bạn sẽ nhận email thông báo khi chứng nhận được phê duyệt
                                    </small>
                                </div>

                                <div class="col-12">
                                    <label for="notes" class="form-label">
                                        <i class="fas fa-sticky-note text-secondary me-1"></i>
                                        Ghi chú
                                    </label>
                                    <textarea class="form-control" id="notes" name="notes" rows="3" 
                                              placeholder="Nếu có yêu cầu đặc biệt, vui lòng ghi chú tại đây..."><?= htmlspecialchars($formData['notes'] ?? '') ?></textarea>
                                </div>
                            </div>

                            <div class="text-center mt-4">
                                <button type="submit" class="btn btn-primary btn-submit">
                                    <i class="fas fa-paper-plane me-2"></i>Gửi yêu cầu
                                </button>
                            </div>

                            <div class="text-center mt-3">
                                <small class="text-muted">
                                    <i class="fas fa-shield-alt me-1"></i>
                                    Thông tin của bạn được bảo mật và chỉ dùng cho mục đích cấp chứng nhận
                                </small>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="text-center mt-4 text-white">

                    <p class="mb-0">
                        <i class="fas fa-map-marker-alt me-2"></i>Địa chỉ trung tâm: 016 21/12 Lê Trực, Phường Gia Định (P.7 cũ), Q.Bình Thạnh, TP.HCM
                    </p>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- QR Code Library -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script>
        // Generate QR Code for this page
        const currentUrl = window.location.href;
        if (document.getElementById('qrcode')) {
            new QRCode(document.getElementById('qrcode'), {
                text: currentUrl,
                width: 200,
                height: 200,
                colorDark: '#667eea',
                colorLight: '#ffffff',
                correctLevel: QRCode.CorrectLevel.H
            });
        }

        const phoneInput = document.getElementById('phone');
        if (phoneInput) {
            phoneInput.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, '');
                if (value.length > 11) {
                    value = value.slice(0, 11);
                }
                e.target.value = value;
            });
        }

        const certificateForm = document.getElementById('certificateForm');
        if (certificateForm) {
            certificateForm.addEventListener('submit', function(e) {
                const phone = document.getElementById('phone').value.trim();
                const email = document.getElementById('email').value.trim();

                if (phone.length < 10 || phone.length > 11) {
                    e.preventDefault();
                    alert('Số điện thoại phải có 10-11 chữ số');
                    return false;
                }

                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(email)) {
                    e.preventDefault();
                    alert('Vui lòng nhập địa chỉ email hợp lệ');
                    return false;
                }
            });
        }
    </script>
</body>
</html>
