# 🔧 FIX LỖI XUẤT PDF TRÊN HOSTING

## 📋 Các bước kiểm tra và sửa lỗi

### Bước 1: Upload file debug
Upload các file sau lên hosting:
- `public/debug_pdf_export.php`
- `public/test_pdf_simple.php`

### Bước 2: Chạy file debug
Truy cập: `https://your-domain.com/public/debug_pdf_export.php`

Xem kết quả và check:
- ✅ **PHP >= 7.4**: Nếu ❌ → Nâng cấp PHP trên hosting
- ✅ **Composer installed**: Nếu ❌ → Chạy `composer install` trên hosting
- ✅ **FPDI library**: Nếu ❌ → Chạy `composer require setasign/fpdi-tcpdf`
- ✅ **Network access**: Nếu ❌ → Liên hệ hosting bật cURL
- ✅ **Temp dir writable**: Nếu ❌ → Check permissions

### Bước 3: Test tạo PDF đơn giản
Truy cập: `https://your-domain.com/public/test_pdf_simple.php`

- Nếu tải được PDF → Thư viện hoạt động OK
- Nếu lỗi → Xem thông báo lỗi cụ thể

### Bước 4: Các giải pháp phổ biến

#### A. Thiếu thư viện Composer
```bash
# SSH vào hosting, cd vào thư mục project
cd /path/to/Quan_ly_trung_tam
composer install --no-dev
```

#### B. Thiếu FPDI
```bash
composer require setasign/fpdi-tcpdf
```

#### C. Memory limit thấp
Thêm vào `.htaccess`:
```apache
php_value memory_limit 256M
php_value max_execution_time 300
```

#### D. cURL bị tắt
Liên hệ hosting yêu cầu:
- Bật PHP cURL extension
- HOẶC bật `allow_url_fopen`

#### E. Error 500 không rõ nguyên nhân
1. Bật error logging trên hosting
2. Check file error log của hosting
3. Thường ở: `/var/log/apache2/error.log` hoặc `/home/username/logs/error_log`

### Bước 5: Kiểm tra lại chức năng
Sau khi fix xong, thử lại:
`https://your-domain.com/Quan_ly_trung_tam/public/completion-slips/export/pdf`

## 🆘 Nếu vẫn lỗi

### Xem error log hosting:
1. Đăng nhập cPanel/DirectAdmin
2. Vào "Error Logs" hoặc "Logs"
3. Tìm dòng log gần nhất có "completion-slips/export/pdf"
4. Gửi error log cho developer

### Check permissions:
```bash
# Vendor folder cần readable
chmod -R 755 vendor/

# Temp directory
chmod 777 /tmp
```

### Fallback: Tắt Cloudinary download tạm
Nếu vẫn lỗi, có thể tạm tắt download Cloudinary:
- Chỉ export ảnh local (không export ảnh Cloudinary)
- Hoặc migrate tất cả ảnh về local trước

## 📝 Thông tin cần gửi khi báo lỗi:
1. Output của `debug_pdf_export.php`
2. Output của `test_pdf_simple.php`
3. Error log từ hosting
4. PHP version trên hosting
5. Tên hosting provider
