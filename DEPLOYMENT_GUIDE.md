# 🚀 Hướng dẫn Deploy lên Hosting & Tự động Migration

## ❓ Câu hỏi của bạn
> "Khi tôi đưa code lên hosting thì code có cập nhập lại database trên phpadmin trên hosting không?"

## ⚠️ Trả lời
**KHÔNG** - Migration không tự động chạy khi upload code. Bạn cần thực hiện các bước sau:

## 🎯 Quy trình Deploy chuẩn

### Bước 1: Chuẩn bị trước khi upload
```bash
# Kiểm tra migration trên local
php migrate.php status

# Test migration trên local trước
php migrate.php migrate
```

### Bước 2: Upload code lên hosting
- Upload tất cả files (trừ `/vendor` nếu hosting có Composer)
- Đảm bảo upload cả thư mục `/database/migrations`

### Bước 3: Cấu hình database trên hosting
Cập nhật `config/app.php` với thông tin database hosting:
```php
'database' => [
    'host' => 'localhost', // Hoặc IP hosting cung cấp
    'database' => 'tên_database_trên_hosting',
    'username' => 'username_hosting',
    'password' => 'password_hosting',
    // ... các config khác
]
```

### Bước 4: Chạy migration trên hosting
Có 3 cách:

#### Cách 1: SSH vào hosting (Khuyến nghị)
```bash
ssh username@hosting_ip
cd /path/to/your/project
php migrate.php migrate
```

#### Cách 2: Tạo file deploy.php
```php
<?php
// Tạo file này và chạy 1 lần qua browser
require_once 'migrate.php';

// Chạy migration
$cli = new MigrationCLI();
$cli->run(['migrate.php', 'migrate']);

echo "Migration completed on hosting!";
// XÓA FILE NÀY SAU KHI CHẠY XONG
?>
```

#### Cách 3: phpMyAdmin Manual (Không khuyến nghị)
- Export SQL từ migration files
- Import vào phpMyAdmin hosting

## 🔧 Tạo Auto-Deployment System

Tôi đã tạo hệ thống tự động cho bạn:

### 1. Web Migration Interface - `web_migrate.php`
- Giao diện web để chạy migration trên hosting
- Không cần SSH access
- Có bảo mật password

**Cách sử dụng:**
```
yoursite.com/web_migrate.php?password=your_secret_password_123
```

### 2. Auto Deploy Hook - `auto_deploy.php`
- Tự động chạy migration khi upload code
- API endpoint cho Git webhooks
- JSON response cho monitoring

**Cách sử dụng:**
```
yoursite.com/auto_deploy.php?secret=deploy_secret_123456
```

### 3. Complete Workflow

## 📋 Quy trình Deploy hoàn chỉnh

### A. Deploy lần đầu (Fresh deployment)

1. **Upload code lên hosting**
   - FTP/SFTP tất cả files
   - Bao gồm `/database/migrations/`
   - Cấu hình `config/app.php` với database hosting

2. **Chạy migration lần đầu**
   ```bash
   # Cách 1: SSH (nếu có)
   ssh user@hosting
   cd /path/to/project
   php migrate.php migrate
   
   # Cách 2: Web interface (không cần SSH)
   yoursite.com/web_migrate.php?password=your_secret_password_123
   
   # Cách 3: Auto deploy API
   curl "yoursite.com/auto_deploy.php?secret=deploy_secret_123456"
   ```

3. **Xóa files deploy (Quan trọng!)**
   - Xóa `web_migrate.php`
   - Xóa `auto_deploy.php`
   - Hoặc đổi password khác

### B. Update code sau này

1. **Upload code mới lên hosting**
2. **Chạy migration cho thay đổi mới**
   ```bash
   # Nếu có migration mới
   php migrate.php status  # Kiểm tra
   php migrate.php migrate # Chạy pending migrations
   ```

## 🎯 Các tình huống thực tế

### Tình huống 1: Hosting có SSH
```bash
# Bước 1: Upload code
scp -r * user@hosting:/path/to/project/

# Bước 2: SSH vào và chạy migration  
ssh user@hosting
cd /path/to/project
php migrate.php migrate
```

### Tình huống 2: Hosting chỉ có FTP/cPanel
```bash
# Bước 1: Upload qua FTP
# - Upload tất cả files
# - Kể cả web_migrate.php

# Bước 2: Chạy qua browser
# yoursite.com/web_migrate.php?password=your_secret_password_123

# Bước 3: Xóa web_migrate.php
```

### Tình huống 3: Git Deployment
```bash
# Bước 1: Setup webhook
# Thêm webhook URL: yoursite.com/auto_deploy.php?secret=xxx

# Bước 2: Push code  
git push origin main

# Bước 3: Webhook tự động chạy migration
```

## 🔒 Bảo mật quan trọng

### ⚠️ MUST DO - Bắt buộc làm:

1. **Đổi password trong files:**
   ```php
   // web_migrate.php
   $deploy_password = "your_new_strong_password";
   
   // auto_deploy.php  
   $deploy_secret = "your_new_deploy_secret";
   ```

2. **Xóa files sau khi dùng:**
   - `web_migrate.php` - Xóa ngay sau migration
   - `auto_deploy.php` - Chỉ giữ nếu cần auto deploy

3. **Backup database trước migration:**
   ```bash
   # Backup qua phpMyAdmin hoặc
   mysqldump -u user -p database > backup.sql
   ```

## 📱 Monitoring & Logging

### Check deployment log:
```bash
# File auto tạo: deployment.log
cat deployment.log
```

### API response format:
```json
{
  "status": "success",
  "timestamp": "2024-11-08 12:45:00",
  "steps": [
    {"step": "database_connection", "status": "success"},
    {"step": "migration_files", "status": "success"},
    {"step": "run_migrations", "status": "success"}
  ]
}
```

## 🚨 Troubleshooting

### Lỗi thường gặp:

1. **Database connection failed**
   ```bash
   # Kiểm tra config/app.php
   # Kiểm tra database credentials trên hosting
   ```

2. **Migration files not found**
   ```bash
   # Đảm bảo upload thư mục /database/migrations/
   ```

3. **Permission denied**
   ```bash
   # Chmod 755 cho directories
   # Chmod 644 cho files
   ```

## ✅ Checklist Deploy

- [ ] Backup database cũ
- [ ] Upload tất cả code (kể cả migrations)
- [ ] Cấu hình database connection
- [ ] Đổi password trong deploy files
- [ ] Test database connection
- [ ] Chạy migration
- [ ] Test website hoạt động
- [ ] Xóa deploy files
- [ ] Commit & push nếu có thay đổi

## 🎉 Kết quả

Sau khi làm theo hướng dẫn:
- ✅ Database hosting được cập nhật tự động
- ✅ Không cần manually import SQL
- ✅ Version control cho database changes
- ✅ Có thể rollback khi cần
- ✅ Deployment process chuẩn chuyên nghiệp

**Database trên hosting sẽ được cập nhật chính xác như local!** 🚀