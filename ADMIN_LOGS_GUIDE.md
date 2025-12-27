# Hệ thống ghi log hoạt động Admin

## 📋 Tổng quan

Hệ thống ghi log đã được cài đặt hoàn chỉnh để theo dõi và giám sát mọi hoạt động của nhân viên trong hệ thống.

## 🚀 Cài đặt

### Bước 1: Chạy migration SQL

```bash
# Kết nối vào MySQL và chạy file SQL
mysql -u root -p your_database < database/admin_logs.sql
```

Hoặc import thông qua phpMyAdmin/TablePlus/HeidiSQL.

### Bước 2: Kiểm tra bảng đã tạo

Sau khi chạy migration, bảng `admin_logs` sẽ được tạo với các trường:
- `id`: ID tự tăng
- `user_id`: ID của user thực hiện hành động
- `username`: Tên đăng nhập
- `action_type`: Loại hành động (login, logout, create, update, delete, view, export, other)
- `module`: Module bị tác động (students, courses, certificates...)
- `description`: Mô tả chi tiết
- `ip_address`: Địa chỉ IP của user
- `user_agent`: Thông tin browser
- `request_data`: Dữ liệu request (JSON)
- `old_data`: Dữ liệu cũ trước khi thay đổi (JSON)
- `new_data`: Dữ liệu mới sau khi thay đổi (JSON)
- `created_at`: Thời gian ghi log

## 📱 Truy cập trang xem logs

Sau khi cài đặt xong, admin có thể truy cập:

```
https://your-domain.com/Quan_ly_trung_tam/public/admin-logs
```

**Lưu ý**: Chỉ tài khoản có role = 'admin' mới có thể truy cập trang này.

## 💻 Cách sử dụng trong code

### 1. Import class AdminLogger

```php
require_once __DIR__ . '/../helpers/AdminLogger.php';
use App\Helpers\AdminLogger;
```

### 2. Khởi tạo logger

```php
$logger = new AdminLogger(
    $this->db->getConnection(), 
    $_SESSION['user_id'], 
    $_SESSION['username']
);
```

### 3. Ghi log theo từng loại hành động

#### a) Ghi log đăng nhập

```php
// Đăng nhập thành công
$logger->logLogin(true);

// Đăng nhập thất bại
$logger->logLogin(false);
```

#### b) Ghi log đăng xuất

```php
$logger->logLogout();
```

#### c) Ghi log tạo mới

```php
$logger->logCreate('students', "Tạo học viên mới: Nguyễn Văn A", [
    'student_id' => 123,
    'name' => 'Nguyễn Văn A',
    'phone' => '0123456789',
    'course' => 'Khóa học A'
]);
```

#### d) Ghi log cập nhật

```php
// Lưu dữ liệu cũ trước khi update
$oldData = [
    'name' => 'Nguyễn Văn A',
    'phone' => '0123456789',
    'status' => 'active'
];

// Update dữ liệu...

$newData = [
    'name' => 'Nguyễn Văn B',
    'phone' => '0987654321',
    'status' => 'inactive'
];

$logger->logUpdate('students', "Cập nhật học viên ID: 123", $oldData, $newData);
```

#### e) Ghi log xóa

```php
$logger->logDelete('students', "Xóa học viên: Nguyễn Văn A", [
    'id' => 123,
    'name' => 'Nguyễn Văn A',
    'phone' => '0123456789'
]);
```

#### f) Ghi log xem/truy cập

```php
$logger->logView('students', "Xem danh sách học viên", [
    'page' => 1,
    'limit' => 50,
    'filter' => 'active'
]);
```

#### g) Ghi log xuất file

```php
$logger->logExport('students', "Xuất danh sách học viên ra Excel", [
    'format' => 'xlsx',
    'total_records' => 150
]);
```

#### h) Ghi log tùy chỉnh

```php
$logger->log(
    'other',                    // action_type
    'system',                   // module
    'Backup database',          // description
    ['database' => 'main'],     // request_data
    null,                       // old_data
    null                        // new_data
);
```

## 🎯 Ví dụ tích hợp vào StudentController

```php
<?php

require_once __DIR__ . '/../helpers/AdminLogger.php';
use App\Helpers\AdminLogger;

class StudentController extends BaseController
{
    public function __construct()
    {
        $this->checkAuth();
        $this->db = Database::getInstance();
    }

    public function index()
    {
        // Ghi log xem danh sách
        $logger = new AdminLogger($this->db->getConnection(), $_SESSION['user_id'], $_SESSION['username']);
        $logger->logView('students', 'Xem danh sách học viên');

        $students = $this->db->fetchAll("SELECT * FROM students");
        $this->view('students/index', ['students' => $students]);
    }

    public function store()
    {
        // Validate và lưu dữ liệu...
        $studentData = [
            'name' => $_POST['name'],
            'phone' => $_POST['phone'],
            'email' => $_POST['email']
        ];

        $sql = "INSERT INTO students (name, phone, email) VALUES (?, ?, ?)";
        $this->db->query($sql, array_values($studentData));
        $studentId = $this->db->getConnection()->insert_id;

        // Ghi log tạo mới
        $logger = new AdminLogger($this->db->getConnection(), $_SESSION['user_id'], $_SESSION['username']);
        $logger->logCreate('students', "Tạo học viên mới: {$studentData['name']}", [
            'student_id' => $studentId,
            ...$studentData
        ]);

        $_SESSION['success'] = 'Thêm học viên thành công';
        $this->redirect('/Quan_ly_trung_tam/public/students');
    }

    public function update($id)
    {
        // Lấy dữ liệu cũ
        $oldData = $this->db->fetch("SELECT * FROM students WHERE id = ?", [$id]);

        // Update dữ liệu...
        $newData = [
            'name' => $_POST['name'],
            'phone' => $_POST['phone'],
            'email' => $_POST['email']
        ];

        $sql = "UPDATE students SET name = ?, phone = ?, email = ? WHERE id = ?";
        $this->db->query($sql, [...array_values($newData), $id]);

        // Ghi log cập nhật
        $logger = new AdminLogger($this->db->getConnection(), $_SESSION['user_id'], $_SESSION['username']);
        $logger->logUpdate('students', "Cập nhật học viên ID: {$id}", $oldData, $newData);

        $_SESSION['success'] = 'Cập nhật học viên thành công';
        $this->redirect('/Quan_ly_trung_tam/public/students');
    }

    public function delete($id)
    {
        // Lấy thông tin trước khi xóa
        $student = $this->db->fetch("SELECT * FROM students WHERE id = ?", [$id]);

        // Xóa
        $this->db->query("DELETE FROM students WHERE id = ?", [$id]);

        // Ghi log xóa
        $logger = new AdminLogger($this->db->getConnection(), $_SESSION['user_id'], $_SESSION['username']);
        $logger->logDelete('students', "Xóa học viên: {$student['name']}", $student);

        $_SESSION['success'] = 'Xóa học viên thành công';
        $this->redirect('/Quan_ly_trung_tam/public/students');
    }

    public function export()
    {
        // Export logic...
        
        // Ghi log xuất file
        $logger = new AdminLogger($this->db->getConnection(), $_SESSION['user_id'], $_SESSION['username']);
        $logger->logExport('students', 'Xuất danh sách học viên ra Excel');

        // Continue with export...
    }
}
```

## 📊 Các tính năng của trang Admin Logs

1. **Xem danh sách logs**: Hiển thị tất cả logs với phân trang
2. **Lọc logs**: Theo user, action type, module, khoảng thời gian
3. **Tìm kiếm**: Tìm trong mô tả và tên user
4. **Xem chi tiết**: Click vào từng log để xem đầy đủ thông tin
5. **So sánh thay đổi**: Tự động so sánh old_data vs new_data
6. **Xuất CSV**: Xuất logs ra file CSV
7. **Xóa logs cũ**: Xóa logs cũ hơn X ngày (tối thiểu 30 ngày)

## 🔒 Bảo mật

- Chỉ admin mới có quyền xem logs (kiểm tra trong `AdminLogController::__construct()`)
- Logs không thể chỉnh sửa, chỉ có thể xem
- IP address được ghi lại để phát hiện truy cập bất thường
- User agent được lưu để phát hiện bot/automation

## 📈 Thống kê

Truy cập API để lấy thống kê:
```
GET /admin-logs/statistics?days=30
```

Trả về JSON:
```json
{
  "daily": [
    {
      "date": "2025-12-27",
      "total": 150,
      "login_count": 20,
      "create_count": 30,
      "update_count": 50,
      "delete_count": 5
    }
  ],
  "users": [
    {
      "username": "admin",
      "total_actions": 500,
      "login_count": 50
    }
  ]
}
```

## 🛠 Bảo trì

### Xóa logs cũ tự động (Cronjob)

Tạo file `cron/cleanup_logs.php`:

```php
<?php
require_once '../config/database.php';

// Xóa logs cũ hơn 90 ngày
$days = 90;
$dateThreshold = date('Y-m-d', strtotime("-{$days} days"));

$sql = "DELETE FROM admin_logs WHERE DATE(created_at) < ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $dateThreshold);
$stmt->execute();

echo "Đã xóa {$stmt->affected_rows} logs cũ hơn {$days} ngày\n";
```

Thêm vào crontab (chạy hàng ngày lúc 2h sáng):
```bash
0 2 * * * /usr/bin/php /path/to/project/cron/cleanup_logs.php
```

## 📝 Module Names (Tham khảo)

- `auth` - Đăng nhập/đăng xuất
- `students` - Quản lý học viên
- `courses` - Quản lý khóa học
- `certificates` - Quản lý chứng chỉ
- `completion_slips` - Phiếu hoàn thành
- `staff` - Quản lý nhân viên
- `teaching_shifts` - Ca dạy
- `revenue` - Doanh thu
- `reports` - Báo cáo
- `system` - Hệ thống

## ⚠️ Lưu ý

1. **Performance**: Log quá nhiều có thể ảnh hưởng performance. Cân nhắc log những action quan trọng.
2. **Storage**: Bảng logs sẽ tăng nhanh. Nên có chiến lược xóa logs cũ định kỳ.
3. **Privacy**: Không log thông tin nhạy cảm như password, credit card...
4. **Try-Catch**: Hàm `log()` đã có try-catch, không làm gián đoạn luồng chính nếu lỗi.

## 🎉 Hoàn thành!

Hệ thống log đã sẵn sàng sử dụng. Bắt đầu tích hợp vào các controller quan trọng của bạn!
