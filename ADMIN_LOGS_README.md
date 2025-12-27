# 🚀 Hướng dẫn nhanh - Hệ thống Admin Logs

## ✅ Các file đã tạo

### 1. Database
- `database/admin_logs.sql` - Tạo bảng admin_logs

### 2. Backend
- `app/helpers/AdminLogger.php` - Class xử lý ghi log
- `app/controllers/AdminLogController.php` - Controller quản lý logs
- `app/controllers/AuthController.php` - Đã tích hợp log đăng nhập/đăng xuất
- `app/controllers/StudentControllerExample.php` - Ví dụ tích hợp vào controller

### 3. Views
- `app/views/admin-logs/index.php` - Trang danh sách logs
- `app/views/admin-logs/detail.php` - Trang chi tiết log

### 4. Routes
- Đã thêm routes vào `public/index.php`:
  - `GET /admin-logs` - Xem danh sách logs
  - `GET /admin-logs/detail/{id}` - Xem chi tiết
  - `GET /admin-logs/export` - Xuất CSV
  - `POST /admin-logs/delete-old` - Xóa logs cũ
  - `GET /admin-logs/statistics` - API thống kê

### 5. Documentation
- `ADMIN_LOGS_GUIDE.md` - Hướng dẫn chi tiết đầy đủ

## 🎯 Cài đặt nhanh (3 bước)

### Bước 1: Tạo bảng database
```bash
# Import file SQL vào database
mysql -u root -p your_database < database/admin_logs.sql
```

Hoặc mở phpMyAdmin/TablePlus và import file `database/admin_logs.sql`

### Bước 2: Kiểm tra quyền admin
Đảm bảo tài khoản admin có `role = 'admin'` trong bảng `users`

### Bước 3: Truy cập trang logs
```
https://your-domain.com/Quan_ly_trung_tam/public/admin-logs
```

## 💡 Sử dụng cơ bản

### Trong bất kỳ Controller nào:

```php
// 1. Import class
require_once __DIR__ . '/../helpers/AdminLogger.php';
use App\Helpers\AdminLogger;

// 2. Khởi tạo logger
$logger = new AdminLogger(
    $this->db->getConnection(), 
    $_SESSION['user_id'], 
    $_SESSION['username']
);

// 3. Ghi log
$logger->logCreate('students', 'Tạo học viên mới: Nguyễn Văn A');
$logger->logUpdate('students', 'Cập nhật học viên ID: 123', $oldData, $newData);
$logger->logDelete('students', 'Xóa học viên: Nguyễn Văn A', $deletedData);
$logger->logView('students', 'Xem danh sách học viên');
$logger->logExport('students', 'Xuất danh sách ra Excel');
```

## 📊 Tính năng

✅ Ghi log tự động đăng nhập/đăng xuất
✅ Theo dõi mọi thao tác create/update/delete
✅ Lưu dữ liệu cũ và mới (so sánh thay đổi)
✅ Ghi lại IP address và User Agent
✅ Giao diện admin xem logs đầy đủ
✅ Lọc theo user, action, module, thời gian
✅ Tìm kiếm trong logs
✅ Xuất logs ra CSV
✅ Xóa logs cũ
✅ API thống kê

## 🔥 Ví dụ nhanh

### AuthController (Đã tích hợp sẵn)
- ✅ Log khi đăng nhập thành công/thất bại
- ✅ Log khi đăng xuất

### Tích hợp vào controller khác
Xem file: `app/controllers/StudentControllerExample.php` để tham khảo

## 📖 Đọc thêm
Xem file `ADMIN_LOGS_GUIDE.md` để biết chi tiết đầy đủ về:
- Cách sử dụng nâng cao
- Ví dụ code chi tiết
- Bảo mật
- Bảo trì
- Performance tips

## 🆘 Troubleshooting

### Lỗi: Table 'admin_logs' doesn't exist
➡️ Chạy file `database/admin_logs.sql`

### Lỗi: Access denied (403) khi vào trang /admin-logs
➡️ Đảm bảo role của user là 'admin'

### Logs không được ghi
➡️ Kiểm tra quyền write của database
➡️ Xem error log trong `error_log()`

### Page not found /admin-logs
➡️ Kiểm tra routes đã thêm vào `public/index.php`

## ✨ Hoàn thành!

Hệ thống log đã hoàn toàn sẵn sàng. Bắt đầu sử dụng ngay!

---

**Tác giả**: AI Assistant  
**Ngày tạo**: December 27, 2025  
**Version**: 1.0
