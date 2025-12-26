# Hướng dẫn quản lý Extension

## 📦 Cập nhật Extension

Khi có thay đổi trong extension, chạy lệnh sau để tạo file ZIP mới:

```bash
cd c:\laragon\www\Quan_ly_trung_tam\public
php create-extension-zip.php
```

File ZIP mới sẽ được tạo tại: `public/downloads/certificate-auto-fill.zip`

## 🔗 Trang download

URL: `http://yourdomain.com/extension-download.php`

Menu: **Tài khoản → Cài đặt Extension**

## 📋 Checklist cập nhật

- [ ] Chỉnh sửa code extension
- [ ] Test extension locally
- [ ] Chạy `php create-extension-zip.php`
- [ ] Kiểm tra file ZIP đã được tạo
- [ ] Thông báo cho nhân viên update
- [ ] Hướng dẫn reload extension trong Chrome

## 🔄 Cách nhân viên update extension

1. Vào `chrome://extensions/`
2. Tìm extension "Trợ Lý Doanh Thu"
3. Click nút "Reload" (icon mũi tên tròn)
4. Hoặc: Xóa extension cũ → Tải ZIP mới → Cài lại

## 📝 Lưu ý

- File ZIP không được commit vào Git (đã có trong .gitignore)
- Mỗi lần update cần chạy lại script để tạo ZIP mới
- Nhớ cập nhật version trong manifest.json
