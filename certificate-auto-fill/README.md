# Certificate Auto Fill - Browser Extension

Extension trình duyệt tự động lấy dữ liệu từ form đăng ký học viên và điền vào form tạo chứng nhận.

## 📋 Tính năng

- ✅ Tự động lấy dữ liệu từ trang đăng ký học viên (hocvien.tinhocsaoviet.com)
- ✅ Lưu trữ dữ liệu tạm thời trong extension
- ✅ Tự động điền dữ liệu vào form tạo báo cáo doanh thu
- ✅ Tự động lấy ngày hiện tại làm ngày đóng học phí
- ✅ Mapping thông minh: Học phí → Số tiền, Số phiếu thu → Mã phiếu thu
- ✅ Giao diện popup thân thiện, dễ sử dụng
- ✅ Hiển thị preview dữ liệu đã lưu với icon rõ ràng
- ✅ Highlight các trường đã điền tự động
- ✅ Hỗ trợ xóa dữ liệu đã lưu

## 🚀 Cài đặt

### Bước 1: Chuẩn bị Icons
1. Tạo 3 file icon với kích thước:
   - `icons/icon16.png` (16x16px)
   - `icons/icon48.png` (48x48px)
   - `icons/icon128.png` (128x128px)

2. Hoặc sử dụng icon online, tải về và đổi tên phù hợp

### Bước 2: Load Extension vào Chrome

1. Mở Chrome và truy cập: `chrome://extensions/`
2. Bật **Developer mode** (góc trên bên phải)
3. Click **"Load unpacked"**
4. Chọn thư mục `certificate-auto-fill`
5. Extension sẽ xuất hiện trong danh sách

### Bước 3: Cấu hình (nếu cần)

Chỉnh sửa file `manifest.json` để thay đổi:
- URL của trang đăng ký học viên
- URL của hệ thống quản lý của bạn
- Permissions

## 📖 Hướng dẫn sử dụng

### Lấy dữ liệu từ form đăng ký

1. Mở trang đăng ký học viên: `http://hocvien.tinhocsaoviet.com/register.php`
2. Điền thông tin học viên vào form
3. Click vào icon extension trên thanh công cụ Chrome
4. Nhấn nút **"📥 Lấy dữ liệu từ form"**
5. Dữ liệu sẽ được lưu và hiển thị trong popup

### Điền dữ liệu vào form báo cáo doanh thu

1. Mở trang tạo báo cáo doanh thu: `http://localhost/Quan_ly_trung_tam/public/revenue/create`
2. Click vào icon extension
3. Kiểm tra dữ liệu đã lưu trong section "Dữ liệu đã lưu"
4. Nhấn nút **"📝 Điền vào form chứng nhận"**
5. Form sẽ tự động được điền với các thông tin:
   - 📅 **Ngày đóng học phí**: Ngày hiện tại
   - 👤 **Họ tên học viên**: Từ form đăng ký
   - 📚 **Khóa học**: Từ form đăng ký
   - 💰 **Số tiền**: Học phí từ form đăng ký
   - 🧾 **Mã phiếu thu**: Số phiếu thu từ form đăng ký
6. Các trường đã điền sẽ được highlight màu xanh trong 2 giây
7. Kiểm tra và điền thủ công các trường còn lại (Loại chuyển khoản, Nơi dụng, Ảnh xác nhận)

### Xóa dữ liệu

- Nhấn nút **"🗑️ Xóa dữ liệu"** để xóa dữ liệu đã lưu

## 🛠️ Tùy chỉnh

### Thay đổi các trường dữ liệu

Chỉnh sửa file `popup/popup.js`:

```javascript
// Trong hàm extractFormData()
function extractFormData() {
    const data = {
        student_name: document.querySelector('input[name="ho_ten"]')?.value || '',
        username: document.querySelector('input[name="ten_dang_nhap"]')?.value || '',
        // Thêm các trường khác...
    };
    return data;
}

// Trong hàm fillFormData()
function fillFormData(data) {
    setFieldValue('input[name="student_name"]', data.student_name);
    // Thêm các trường khác...
}
```

### Thay đổi selector của form

Nếu trang web có cấu trúc HTML khác, cần cập nhật các selector:

**File `content/extract.js`:**
```javascript
student_name: getFieldValue('input[name="YOUR_FIELD_NAME"]'),
```

**File `content/fill.js`:**
```javascript
setFieldValue('input[name="YOUR_FIELD_NAME"]', data.student_name);
```

## � Mapping Dữ liệu

| Form Đăng ký học viên | Form Báo cáo doanh thu | Ghi chú |
|----------------------|------------------------|---------|
| Họ và tên | Họ tên học viên | ✅ Auto fill |
| Khóa học | Khóa học | ✅ Auto fill (dropdown search) |
| Học phí | Số tiền | ✅ Auto fill, tự động làm sạch format |
| Số phiếu thu | Mã phiếu thu | ✅ Auto fill |
| Ngày bắt đầu | - | ℹ️ Chỉ lưu trữ |
| - | Ngày đóng học phí | ✅ Tự động = Ngày hiện tại |
| Điện thoại | - | ℹ️ Hiển thị trong preview |
| Email | - | ℹ️ Hiển thị trong preview |

### Các trường cần điền thủ công:
- ⚠️ **Loại chuyển khoản**: Chọn từ dropdown
- ⚠️ **Nơi dụng chuyển khoản**: Chọn từ dropdown
- ⚠️ **Ảnh xác nhận chuyển khoản**: Upload file

## 🔍 Debug

### Xem Console Logs

1. **Popup Console:**
   - Right-click vào icon extension → "Inspect popup"
   
2. **Background Service Worker:**
   - Vào `chrome://extensions/`
   - Click "Inspect views: service worker"

3. **Content Script:**
   - Mở DevTools (F12) trên trang web
   - Tab Console → Tìm các log bắt đầu bằng ✓, 📥, 📝

### Common Issues

**Problem:** Extension không hoạt động
- ✅ Kiểm tra URL patterns trong `manifest.json`
- ✅ Reload extension sau khi chỉnh sửa code
- ✅ Kiểm tra permissions

**Problem:** Không lấy được dữ liệu
- ✅ Kiểm tra selector trong `extractFormData()`
- ✅ Mở Console để xem error messages
- ✅ Đảm bảo đang ở đúng trang

**Problem:** Không điền được dữ liệu
- ✅ Kiểm tra selector trong `fillFormData()`
- ✅ Đảm bảo form đã load hoàn tất
- ✅ Kiểm tra dữ liệu đã được lưu chưa

## 📁 Cấu trúc thư mục

```
certificate-auto-fill/
├── manifest.json              # Cấu hình extension
├── README.md                  # Tài liệu này
├── popup/
│   ├── popup.html            # Giao diện popup
│   ├── popup.css             # Styles
│   └── popup.js              # Logic popup
├── content/
│   ├── extract.js            # Script lấy dữ liệu
│   └── fill.js               # Script điền dữ liệu
├── background/
│   └── background.js         # Service worker
└── icons/
    ├── icon16.png            # Icon 16x16
    ├── icon48.png            # Icon 48x48
    └── icon128.png           # Icon 128x128
```

## 🔐 Bảo mật

- Dữ liệu chỉ được lưu trữ local trong extension
- Không gửi dữ dữ liệu từ form đăng ký học viên
- ✅ Tự động điền vào form báo cáo doanh thu
- ✅ Mapping thông minh: Học phí → Số tiền, Số phiếu thu → Mã phiếu thu
- ✅ Tự động lấy ngày hiện tại làm ngày đóng học phí
- ✅ Giao diện popup với icon emoji trực quan
- ✅ Highlight các trường đã điền
- ✅ Lưu trữ dữ liệu local an toàn
- ✅ Console logs chi tiết để debug
- ✅ Thông báo chi tiết khi điền form thành côngữ liệu bất kỳ lúc nào

## 📄 License

MIT License - Sử dụng tự do cho mục đích cá nhân và thương mại

## 🤝 Đóng góp

Mọi đóng góp và góp ý xin gửi về:
- Email: support@tinhocsaoviet.com
- GitHub Issues

## 📝 Changelog

### Version 1.0.0 (2025-12-24)
- ✨ Phiên bản đầu tiên
- ✅ Hỗ trợ lấy và điền dữ liệu cơ bản
- ✅ Giao diện popup đơn giản
- ✅ Lưu trữ dữ liệu local
