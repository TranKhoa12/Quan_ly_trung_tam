# HƯỚNG DẪN TEST CHỨC NĂNG NHIỀU ẢNH

## ✓ Đã sửa xong các vấn đề:

### 1. Database Migration
- ✅ Thêm column `confirmation_images` vào bảng `revenue_reports`
- ✅ Migrate dữ liệu cũ từ `confirmation_image` sang format JSON array
- ✅ Giữ lại column `confirmation_image` để backward compatibility

### 2. Model (RevenueReport.php)
- ✅ Thêm `'confirmation_images'` vào `$fillable` array

### 3. Controllers
- ✅ **ReportController.php**: Xử lý upload nhiều ảnh khi tạo revenue từ báo cáo học viên
  - Hỗ trợ multiple files upload
  - Lưu vào cả 2 columns: `confirmation_image` + `confirmation_images`
  
- ✅ **RevenueController.php**: Xử lý upload nhiều ảnh khi tạo revenue trực tiếp
  - Sửa logic lưu từ notes sang `confirmation_images` JSON

### 4. View (revenue/index.php)
- ✅ Đọc từ `confirmation_images` JSON column
- ✅ Fallback về `confirmation_image` nếu chưa có data mới
- ✅ Hiển thị badge số lượng ảnh
- ✅ Modal xem nhiều ảnh với navigation

---

## 🧪 CÁCH TEST:

### Test 1: Tạo Revenue từ Báo cáo học viên
1. Vào `/reports/create`
2. Chọn nhân viên, ngày
3. Thêm khách hàng
4. Click "Nhập doanh thu"
5. **Chọn nhiều ảnh** (2-3 ảnh) trong trường "Ảnh xác nhận"
6. Điền thông tin, click "Lưu doanh thu"
7. Vào "Danh sách doanh thu" → Thấy badge số ảnh
8. Click nút 📷 → Xem tất cả ảnh với prev/next

### Test 2: Tạo Revenue trực tiếp
1. Vào `/revenue/create`
2. Điền thông tin học viên, số tiền
3. **Chọn nhiều ảnh** trong "Ảnh xác nhận chuyển khoản"
4. Click "Tạo báo cáo"
5. Xem danh sách → Thấy badge số ảnh
6. Click xem → Hiển thị tất cả ảnh

### Test 3: Dữ liệu cũ
1. Records cũ (đã có trước khi update) đã được migrate
2. Vào danh sách doanh thu
3. Tất cả records cũ vẫn hiển thị ảnh bình thường

---

## 📊 KẾT QUẢ MONG ĐỢI:

✅ Badge hiển thị số lượng ảnh chính xác (vd: "2", "3")
✅ Click vào nút 📷 mở modal với tất cả ảnh
✅ Navigation prev/next hoạt động
✅ Thumbnails hiển thị đầy đủ
✅ Download button hoạt động
✅ Dữ liệu cũ vẫn xem được bình thường

---

## 🔧 MIGRATION SCRIPTS ĐÃ CHẠY:

```bash
php migrate_revenue_images.php           # Thêm column mới
php migrate_old_revenue_images.php       # Migrate data cũ
```

Bây giờ bạn có thể reload trang và test chức năng!
