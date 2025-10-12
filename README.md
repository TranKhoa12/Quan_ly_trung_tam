# Hướng dẫn cài đặt và chạy hệ thống

## 1. Yêu cầu hệ thống
- PHP 7.4 trở lên
- MySQL 5.7 trở lên
- Apache web server với mod_rewrite
- Laragon (đã cài đặt sẵn)

## 2. Cài đặt database

1. Mở phpMyAdmin hoặc MySQL command line
2. Chạy file SQL: `database/schema.sql`
3. Hoặc import bằng command line:
```bash
mysql -u root -p < database/schema.sql
```

## 3. Cấu hình

1. Chỉnh sửa file `config/database.php` nếu cần thiết (username, password, host)
2. Đảm bảo thư mục `public/uploads` có quyền ghi

## 4. Truy cập hệ thống

- URL: http://localhost/Quan_ly_trung_tam/public
- Tài khoản admin: admin / password
- Tài khoản nhân viên: staff1 / password

## 5. Cấu trúc dự án

```
Quan_ly_trung_tam/
├── app/
│   ├── controllers/     # Các controller xử lý logic
│   ├── models/         # Các model tương tác với database
│   ├── views/          # Các template hiển thị
│   └── middleware/     # Middleware xác thực
├── config/             # File cấu hình
├── core/              # Core classes (Router, Database, Base classes)
├── database/          # Schema và migration
└── public/            # Thư mục public (entry point)
    ├── assets/        # CSS, JS, images
    └── uploads/       # File upload
```

## 6. API Endpoints

### Authentication
- POST `/api/v1/auth/login` - Đăng nhập
- POST `/api/v1/auth/logout` - Đăng xuất

### Reports (Báo cáo đến trung tâm)
- GET `/api/v1/reports` - Lấy danh sách báo cáo
- POST `/api/v1/reports` - Tạo báo cáo mới
- GET `/api/v1/reports/{id}` - Lấy chi tiết báo cáo
- PUT `/api/v1/reports/{id}` - Cập nhật báo cáo
- DELETE `/api/v1/reports/{id}` - Xóa báo cáo

### Revenue (Báo cáo doanh thu)
- GET `/api/v1/revenue` - Lấy danh sách doanh thu
- POST `/api/v1/revenue` - Tạo báo cáo doanh thu
- GET `/api/v1/revenue/{id}` - Lấy chi tiết doanh thu

### Students (Học viên)
- GET `/api/v1/students` - Lấy danh sách học viên
- POST `/api/v1/students` - Thêm học viên
- PUT `/api/v1/students/{id}` - Cập nhật học viên

### Certificates (Chứng nhận)
- GET `/api/v1/certificates` - Lấy danh sách chứng nhận
- POST `/api/v1/certificates` - Tạo yêu cầu chứng nhận
- PUT `/api/v1/certificates/{id}` - Cập nhật trạng thái chứng nhận

## 7. Phân quyền

- **Admin**: Có thể truy cập và quản lý tất cả chức năng
- **Staff**: Có thể tạo báo cáo, xem dữ liệu nhưng không thể xóa

## 8. Tính năng chính

1. **Báo cáo số lượng đến trung tâm**
   - Ghi nhận ngày giờ, nhân viên báo cáo
   - Chi tiết khách hàng (SĐT, tên, khóa học, trạng thái)
   - Thống kê số lượng đến và chốt

2. **Báo cáo doanh thu**
   - Ghi nhận các khoản thu theo ngày
   - Phân loại theo hình thức thanh toán
   - Upload ảnh xác nhận

3. **Quản lý học viên**
   - Nhập thông tin học viên hoàn thành khóa học
   - Upload ảnh phiếu theo dõi
   - Theo dõi trạng thái học tập

4. **Quản lý chứng nhận**
   - Yêu cầu cấp chứng nhận
   - Phê duyệt chứng nhận
   - Theo dõi tình trạng nhận chứng nhận

## 9. Tính năng API

Hệ thống hỗ trợ đầy đủ REST API để tích hợp với các ứng dụng khác như mobile app, dashboard riêng biệt.