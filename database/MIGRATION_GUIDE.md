# Database Migration System - Hướng dẫn sử dụng

## Tổng quan
Hệ thống Migration tự động cho phép bạn:
- ✅ Tự động cập nhật cấu trúc database
- 🔧 Tạo Model, Controller, View tự động
- 📝 Quản lý phiên bản database
- 🔄 Rollback khi cần thiết

## Cách sử dụng

### 1. Chạy Migration hiện có
```bash
php migrate.php migrate
```

### 2. Tạo Migration mới
```bash
# Tạo bảng mới
php migrate.php make create_products_table

# Thêm cột vào bảng có sẵn  
php migrate.php make add_image_to_products add_column products
```

### 3. Tạo CRUD hoàn chỉnh (Model + Controller + Views)
```bash
php migrate.php crud products
```
Lệnh này sẽ tạo:
- Migration file: `create_products_table.php`
- Model: `app/models/Product.php`
- Controller: `app/controllers/ProductController.php`
- Views: `app/views/products/index.php`, `create.php`, `edit.php`

### 4. Kiểm tra trạng thái Migration
```bash
php migrate.php status
```

### 5. Rollback Migration cuối
```bash
php migrate.php rollback
```

### 6. Fresh Migration (Xóa tất cả và chạy lại)
```bash
php migrate.php fresh
```

## Ví dụ tạo Migration thực tế

### Tạo bảng sản phẩm
1. Tạo migration:
```bash
php migrate.php make create_products_table
```

2. Chỉnh sửa file migration được tạo:
```php
<?php
class CreateProductsTable {
    public function up($db) {
        $sql = "CREATE TABLE products (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            code VARCHAR(100) UNIQUE NOT NULL,
            price DECIMAL(10,2) NOT NULL,
            category_id INT,
            description TEXT,
            image_url VARCHAR(500),
            stock_quantity INT DEFAULT 0,
            status ENUM('active', 'inactive') DEFAULT 'active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (category_id) REFERENCES categories(id)
        )";
        $db->query($sql);
        $db->execute();
    }
    
    public function down($db) {
        $sql = "DROP TABLE IF EXISTS products";
        $db->query($sql);
        $db->execute();
    }
}
```

3. Chạy migration:
```bash
php migrate.php migrate
```

## Tạo CRUD hoàn chỉnh

### Ví dụ tạo quản lý khóa học
```bash
php migrate.php crud courses
```

Sau khi chạy, bạn cần:

1. **Chỉnh sửa Migration** (`database/migrations/xxx_create_courses_table.php`):
```php
$sql = "CREATE TABLE courses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    code VARCHAR(50) UNIQUE NOT NULL,
    description TEXT,
    duration_hours INT NOT NULL,
    fee DECIMAL(10,2) NOT NULL,
    max_students INT DEFAULT 30,
    instructor_id INT,
    status ENUM('active', 'inactive') DEFAULT 'active',
    start_date DATE,
    end_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";
```

2. **Chỉnh sửa Model** (`app/models/Course.php`):
```php
protected $fillable = [
    'name', 'code', 'description', 'duration_hours', 
    'fee', 'max_students', 'instructor_id', 'status',
    'start_date', 'end_date'
];
```

3. **Chỉnh sửa Controller** (`app/controllers/CourseController.php`):
Thêm logic xử lý form trong methods `store()` và `update()`

4. **Chỉnh sửa Views**: 
Cập nhật form fields trong `create.php` và `edit.php`

5. **Thêm Routes** vào `core/Router.php`:
```php
$router->get('/courses', 'CourseController@index');
$router->get('/courses/create', 'CourseController@create');
$router->post('/courses/store', 'CourseController@store');
$router->get('/courses/edit/{id}', 'CourseController@edit');
$router->post('/courses/update/{id}', 'CourseController@update');
$router->get('/courses/delete/{id}', 'CourseController@destroy');
```

6. **Chạy Migration**:
```bash
php migrate.php migrate
```

## Các lệnh hữu ích khác

### Xem danh sách migration
```bash
php migrate.php status
```

### Tạo migration thêm cột
```bash
php migrate.php make add_avatar_to_users add_column users
```

### Tạo migration sửa đổi cột  
```bash
php migrate.php make modify_user_email modify_column users
```

## Lưu ý quan trọng

1. **Backup Database** trước khi chạy migration
2. **Test trên môi trường phát triển** trước
3. **Không chỉnh sửa** migration đã chạy
4. **Sử dụng rollback** nếu có lỗi
5. **Fresh migration** chỉ dùng trong development

## Troubleshooting

### Lỗi kết nối database
- Kiểm tra file `config/database.php`
- Đảm bảo MySQL đang chạy

### Migration bị lỗi
```bash
# Rollback và sửa lại
php migrate.php rollback
# Chỉnh sửa migration file
php migrate.php migrate
```

### Tạo lại toàn bộ
```bash
php migrate.php fresh
```

Hệ thống migration này giúp bạn quản lý database một cách chuyên nghiệp và tự động hóa việc tạo CRUD! 🚀