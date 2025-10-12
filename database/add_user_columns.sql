-- Script để thêm các thuộc tính mới vào bảng users
-- Ngày tạo: 2025-10-12

-- Kiểm tra và thêm cột phone (số điện thoại)
ALTER TABLE users 
ADD COLUMN IF NOT EXISTS phone VARCHAR(20) DEFAULT NULL 
AFTER email;

-- Kiểm tra và thêm cột address (địa chỉ)
ALTER TABLE users 
ADD COLUMN IF NOT EXISTS address TEXT DEFAULT NULL 
AFTER phone;

-- Kiểm tra và thêm cột hire_date (ngày vào làm)
ALTER TABLE users 
ADD COLUMN IF NOT EXISTS hire_date DATE DEFAULT NULL 
AFTER address;

-- Kiểm tra và thêm cột salary (lương)
ALTER TABLE users 
ADD COLUMN IF NOT EXISTS salary DECIMAL(10,2) DEFAULT NULL 
AFTER hire_date;

-- Kiểm tra và thêm cột department (phòng ban)
ALTER TABLE users 
ADD COLUMN IF NOT EXISTS department VARCHAR(100) DEFAULT NULL 
AFTER salary;

-- Kiểm tra và thêm cột status (trạng thái)
ALTER TABLE users 
ADD COLUMN IF NOT EXISTS status ENUM('active', 'inactive', 'suspended') DEFAULT 'active' 
AFTER department;

-- Kiểm tra và thêm cột notes (ghi chú)
ALTER TABLE users 
ADD COLUMN IF NOT EXISTS notes TEXT DEFAULT NULL 
AFTER status;

-- Cập nhật status cho các user hiện có (nếu chưa có)
UPDATE users SET status = 'active' WHERE status IS NULL;

-- Thêm một số dữ liệu mẫu cho nhân viên
INSERT IGNORE INTO users (username, email, password, full_name, role, phone, address, hire_date, salary, department, status, created_at) VALUES
('nv001', 'nguyen.van.a@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Nguyễn Văn A', 'staff', '0901234567', '123 Đường ABC, Quận 1, TP.HCM', '2023-01-15', 8000000, 'Tư vấn', 'active', NOW()),
('nv002', 'tran.thi.b@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Trần Thị B', 'staff', '0901234568', '456 Đường DEF, Quận 2, TP.HCM', '2023-02-20', 7500000, 'Giảng dạy', 'active', NOW()),
('nv003', 'le.van.c@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Lê Văn C', 'staff', '0901234569', '789 Đường GHI, Quận 3, TP.HCM', '2023-03-10', 9000000, 'Kế toán', 'active', NOW()),
('nv004', 'pham.thi.d@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Phạm Thị D', 'staff', '0901234570', '321 Đường JKL, Quận 4, TP.HCM', '2023-04-05', 8500000, 'Marketing', 'active', NOW()),
('nv005', 'hoang.van.e@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Hoàng Văn E', 'staff', '0901234571', '654 Đường MNO, Quận 5, TP.HCM', '2023-05-12', 7800000, 'Hỗ trợ', 'inactive', NOW());

-- Cập nhật thông tin cho admin user hiện có
UPDATE users 
SET phone = '0909876543', 
    address = '100 Đường Admin, Quận 1, TP.HCM', 
    hire_date = '2022-01-01', 
    salary = 15000000, 
    department = 'Quản lý', 
    status = 'active',
    notes = 'Quản trị viên hệ thống'
WHERE role = 'admin' AND username = 'admin';

-- Kiểm tra kết quả
SELECT 'Cấu trúc bảng users sau khi cập nhật:' as message;
DESCRIBE users;

SELECT 'Danh sách users với thông tin mới:' as message;
SELECT id, username, full_name, role, phone, department, status, hire_date 
FROM users 
ORDER BY created_at DESC;