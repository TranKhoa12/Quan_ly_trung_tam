# Phân Trang và Lọc Dữ Liệu - Danh Sách Doanh Thu

## ✅ Các tính năng đã hoàn thành:

### 1. **Phân Trang (Pagination)**
- Hiển thị 20 bản ghi mỗi trang
- Nút Previous/Next để chuyển trang
- Hiển thị số trang với dấu "..." khi có nhiều trang
- Thông tin: "Hiển thị X - Y trong tổng số Z bản ghi"
- Giữ nguyên các bộ lọc khi chuyển trang

### 2. **Bộ Lọc Dữ Liệu**

#### a. **Lọc theo ngày**
- Từ ngày: Chọn ngày bắt đầu
- Đến ngày: Chọn ngày kết thúc
- Tìm kiếm theo khoảng thời gian

#### b. **Lọc theo hình thức thanh toán**
- Tiền mặt
- TK Cô Nhi
- TK Thầy Hiền
- TK Công ty

#### c. **Lọc theo loại thanh toán**
- Thanh toán đầy đủ
- Đặt cọc
- Thanh toán L1/L2/L3

#### d. **Tìm kiếm văn bản**
- Tìm theo tên học viên
- Tìm theo tên khóa học
- Tìm theo mã biên lai

### 3. **Phân Quyền**

#### Admin:
- ✅ Xem tất cả doanh thu
- ✅ Lọc theo tất cả tiêu chí
- ✅ Xem thống kê đầy đủ
- ✅ Xóa/Sửa doanh thu

#### Staff:
- ✅ Chỉ xem doanh thu của mình trong ngày
- ✅ Không thấy thống kê số tiền
- ✅ Chỉ xem, không sửa/xóa
- ✅ Tự động lọc theo staff_id và ngày hiện tại

## 📝 Code Changes:

### RevenueController.php
- Thêm logic phân trang (page, perPage, offset)
- Xử lý các tham số GET (from_date, to_date, transfer_type, payment_content, search)
- Phân quyền staff/admin
- Truyền biến pagination vào view

### RevenueReport.php (Model)
- `getRevenueWithFilters()`: Lấy dữ liệu với điều kiện lọc và phân trang
- `countRevenue()`: Đếm tổng số bản ghi theo điều kiện lọc
- Hỗ trợ tìm kiếm LIKE cho nhiều trường

### revenue/index.php (View)
- Form filter với 5 trường lọc
- Nút Reset để xóa bộ lọc
- Pagination UI với Bootstrap
- Giữ nguyên query string khi chuyển trang

## 🎯 Cách sử dụng:

1. **Lọc dữ liệu**: Chọn các tiêu chí lọc và nhấn nút "Tìm kiếm"
2. **Reset filter**: Nhấn nút "↻" để xóa tất cả bộ lọc
3. **Chuyển trang**: Click vào số trang hoặc nút Trước/Sau
4. **Tìm kiếm**: Nhập tên học viên/khóa học/mã biên lai vào ô tìm kiếm

## 🔒 Bảo mật:

- Staff không thể xem doanh thu của staff khác
- Staff không thể thay đổi bộ lọc ngày (tự động là hôm nay)
- Staff không thể sửa/xóa doanh thu
- Tất cả query được sanitize qua PDO prepared statements
