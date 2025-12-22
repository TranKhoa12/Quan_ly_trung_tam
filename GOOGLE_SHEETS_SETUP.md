# Hướng dẫn thiết lập Google Sheets API

## Bước 1: Tạo Google Cloud Project và Service Account

1. Truy cập [Google Cloud Console](https://console.cloud.google.com/)
2. Tạo project mới hoặc chọn project hiện có
3. Bật Google Sheets API:
   - Vào "APIs & Services" > "Library"
   - Tìm "Google Sheets API"
   - Click "Enable"

## Bước 2: Tạo Service Account

1. Vào "APIs & Services" > "Credentials"
2. Click "Create Credentials" > "Service Account"
3. Điền thông tin:
   - Service account name: `quan-ly-trung-tam-sheets`
   - Service account ID: tự động tạo
   - Click "Create and Continue"
4. Grant role: "Editor" hoặc "Owner"
5. Click "Done"

## Bước 3: Tạo và tải JSON key

1. Vào "Service Accounts" tab
2. Click vào service account vừa tạo
3. Chọn tab "Keys"
4. Click "Add Key" > "Create new key"
5. Chọn "JSON"
6. Click "Create" - file JSON sẽ được tải xuống

## Bước 4: Cấu hình trong project

1. Đổi tên file JSON thành `google-credentials.json`
2. Copy file vào thư mục `config/` của project:
   ```
   c:\laragon\www\Quan_ly_trung_tam\config\google-credentials.json
   ```

## Bước 5: Chia sẻ Google Sheet

1. Mở file JSON `google-credentials.json`
2. Tìm field `client_email` (có dạng: xxx@xxx.iam.gserviceaccount.com)
3. Copy email này
4. Mở Google Sheet cần ghi dữ liệu
5. Click "Share" và thêm email của service account
6. Cấp quyền "Editor"
7. Click "Send"

## Bước 6: Cài đặt Google API Client

Chạy lệnh trong terminal:

```bash
cd c:\laragon\www\Quan_ly_trung_tam
composer install
```

hoặc nếu đã có composer.lock:

```bash
composer update google/apiclient
```

## Bước 7: Kiểm tra

1. Truy cập trang "Quản lý đợt chuyển tiền"
2. Chọn khoảng thời gian
3. Click "Xem báo cáo"
4. Click "Đưa vào Sheet"
5. Kiểm tra Google Sheet xem dữ liệu đã được thêm chưa

## Cấu trúc dữ liệu trong Sheet

Dữ liệu sẽ được ghi vào các cột:

| Cột A | Cột B | Cột C | Cột D | Cột E | Cột F | Cột G |
|-------|-------|-------|-------|-------|-------|-------|
| Từ ngày | Đến ngày | Tổng thực thu | Chuyển khoản TK CTY | Chuyển khoản TK Th Hiến | Chi nhánh CK cho Th Hiến | Chênh lệch |

## Lưu ý bảo mật

⚠️ **QUAN TRỌNG**: 
- File `google-credentials.json` chứa thông tin nhạy cảm
- Không commit file này lên Git
- Thêm vào `.gitignore`:
  ```
  config/google-credentials.json
  ```
- Chỉ chia sẻ với người có quyền quản trị hệ thống

## Xử lý lỗi thường gặp

### Lỗi: "Không tìm thấy file credentials"
- Kiểm tra file `google-credentials.json` có tồn tại trong thư mục `config/`
- Kiểm tra đường dẫn trong code

### Lỗi: "Permission denied"
- Kiểm tra email service account đã được chia sẻ trong Google Sheet
- Đảm bảo cấp quyền "Editor"

### Lỗi: "Google Sheets API has not been used"
- Truy cập Google Cloud Console
- Bật Google Sheets API cho project

## ID Google Sheet hiện tại

Google Sheet ID: `17ATz02rJKSfV5rBrQGoei-W9nTCr0-hmYZzCQ1zuzD4`

Link: https://docs.google.com/spreadsheets/d/17ATz02rJKSfV5rBrQGoei-W9nTCr0-hmYZzCQ1zuzD4/edit

Nếu cần đổi sheet khác, cập nhật ID trong file:
`app/controllers/TransferBatchController.php` tại dòng:
```php
$spreadsheetId = '17ATz02rJKSfV5rBrQGoei-W9nTCr0-hmYZzCQ1zuzD4';
```
