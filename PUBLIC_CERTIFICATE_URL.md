# URL Public - Yêu cầu cấp chứng nhận

## 📋 Thông tin

**URL Public cho học viên:**
```
http://localhost/Quan_ly_trung_tam/public/certificate-request
```

Hoặc trên server thực tế:
```
https://your-domain.com/certificate-request
```

## ✨ Tính năng

- ✅ Học viên tự điền form yêu cầu chứng nhận
- ✅ Không cần đăng nhập
- ✅ Giao diện đẹp, responsive
- ✅ Validation đầy đủ
- ✅ Tự động gửi email thông báo (tương lai)

## 🎯 Cách sử dụng

### Cho học viên:
1. Truy cập URL public
2. Điền đầy đủ thông tin:
   - Họ tên
   - Tên đăng nhập (thường là SĐT)
   - Số điện thoại
   - Bộ môn đã học
   - Email (để nhận thông báo)
   - Ghi chú (nếu có)
3. Nhấn "Gửi yêu cầu"
4. Chờ admin phê duyệt

### Cho Admin/Nhân viên:
1. Xem danh sách yêu cầu trong hệ thống
2. Phân biệt nguồn:
   - **Badge xanh dương (Nhân viên/Admin)**: Yêu cầu do nhân viên tạo
   - **Badge xanh lá (Học viên)**: Yêu cầu từ form public
3. Xử lý như bình thường

## 🔐 Logic người yêu cầu

### Database: `requested_by`
- **NULL**: Học viên tự điền qua form public
- **ID nhân viên/admin**: Nhân viên/admin tạo trong hệ thống

### Hiển thị:
```php
if (!empty($cert['requested_by_name'])) {
    // Hiển thị: Badge xanh dương + Tên nhân viên/admin
    echo '<span class="badge bg-primary">' . $cert['requested_by_name'] . '</span>';
} else {
    // Hiển thị: Badge xanh lá + "Học viên"
    echo '<span class="badge bg-success">Học viên</span>';
}
```

## 📱 Chia sẻ URL

Có thể tạo QR Code hoặc rút gọn link để chia sẻ:
- In QR Code tại trung tâm
- Gửi link qua Zalo/Facebook
- Đặt trong tài liệu hướng dẫn

## 🎨 Tùy chỉnh

File: `app/views/public/certificate_request.php`

Có thể tùy chỉnh:
- Logo trung tâm
- Màu sắc giao diện
- Thông tin liên hệ (hotline, địa chỉ)
- Danh sách bộ môn
- Email tự động (khi tích hợp)

## 🔄 Tương lai

- [ ] Tích hợp gửi email tự động
- [ ] Thêm captcha chống spam
- [ ] Upload ảnh chứng nhận hoàn thành
- [ ] Tra cứu trạng thái yêu cầu
- [ ] Tích hợp thanh toán (nếu có phí)
