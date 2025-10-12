# Hướng dẫn import database

## Cách 1: Sử dụng phpMyAdmin
1. Mở phpMyAdmin: http://localhost/phpmyadmin
2. Tạo database mới tên: `quan_ly_trung_tam`
3. Chọn database vừa tạo
4. Click tab "Import"
5. Chọn file: `database/schema.sql`
6. Click "Go" để import

## Cách 2: Sử dụng MySQL Command Line
```bash
# Mở command prompt/terminal
# Chuyển đến thư mục dự án
cd C:\laragon\www\Quan_ly_trung_tam

# Chạy lệnh import
mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS quan_ly_trung_tam;"
mysql -u root -p quan_ly_trung_tam < database/schema.sql
```

## Thông tin đăng nhập mẫu sau khi import:
- **Admin**: username: `admin`, password: `password`
- **Staff**: username: `staff1`, password: `password`

## Kiểm tra sau khi import:
Bạn sẽ có các bảng sau:
- users (2 records)
- courses (3 records)
- reports (empty)
- report_customers (empty)
- revenue_reports (empty)
- students (empty)
- certificates (empty)