-- Tạo database quản lý trung tâm
CREATE DATABASE IF NOT EXISTS quan_ly_trung_tam CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE quan_ly_trung_tam;

-- Bảng users (quản lý tài khoản admin và nhân viên)
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    phone VARCHAR(15),
    role ENUM('admin', 'staff') NOT NULL DEFAULT 'staff',
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Bảng courses (khóa học)
CREATE TABLE courses (
    id INT PRIMARY KEY AUTO_INCREMENT,
    course_name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Bảng reports (báo cáo số lượng đến trung tâm)
CREATE TABLE reports (
    id INT PRIMARY KEY AUTO_INCREMENT,
    report_date DATE NOT NULL,
    report_time TIME NOT NULL,
    staff_id INT NOT NULL,
    total_visitors INT NOT NULL DEFAULT 0,
    total_registered INT NOT NULL DEFAULT 0,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (staff_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Bảng report_customers (chi tiết khách hàng trong báo cáo)
CREATE TABLE report_customers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    report_id INT NOT NULL,
    phone VARCHAR(15) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    status ENUM('new', 'returning') NOT NULL DEFAULT 'new',
    course_id INT,
    registration_status ENUM('registered', 'not_registered') NOT NULL DEFAULT 'not_registered',
    payment_method VARCHAR(50) NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (report_id) REFERENCES reports(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE SET NULL
);

-- Bảng revenue_reports (báo cáo doanh thu)
CREATE TABLE revenue_reports (
    id INT PRIMARY KEY AUTO_INCREMENT,
    payment_date DATE NOT NULL,
    transfer_type ENUM('cash', 'account_co_nhi', 'account_thay_hien', 'account_company') NOT NULL,
    confirmation_image VARCHAR(255),
    receipt_code VARCHAR(50),
    amount DECIMAL(10, 2) NOT NULL,
    student_name VARCHAR(100) NOT NULL,
    course_id INT,
    payment_content ENUM(
        'full_payment', 
        'deposit', 
        'full_payment_after_deposit', 
        'accounting_deposit', 
        'l1_payment', 
        'l2_payment', 
        'l3_payment'
    ) NOT NULL,
    staff_id INT NOT NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE SET NULL,
    FOREIGN KEY (staff_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Bảng students (học viên)
CREATE TABLE students (
    id INT PRIMARY KEY AUTO_INCREMENT,
    full_name VARCHAR(100) NOT NULL,
    phone VARCHAR(15),
    email VARCHAR(100),
    course_id INT,
    instructor_id INT,
    enrollment_date DATE,
    completion_date DATE,
    tracking_image VARCHAR(255),
    status ENUM('studying', 'completed', 'dropped') NOT NULL DEFAULT 'studying',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE SET NULL,
    FOREIGN KEY (instructor_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Bảng certificates (yêu cầu cấp chứng nhận)
CREATE TABLE certificates (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_name VARCHAR(100) NOT NULL,
    username VARCHAR(50),
    phone VARCHAR(15),
    subject VARCHAR(100) NOT NULL,
    receive_status ENUM('received', 'not_received') NOT NULL DEFAULT 'not_received',
    approval_status ENUM('pending', 'approved', 'cancelled') NOT NULL DEFAULT 'pending',
    notes TEXT,
    requested_by INT,
    approved_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (requested_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL
);

-- Bảng completion_slips (phiếu hoàn thành học viên)
CREATE TABLE completion_slips (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    course_id INT,
    teacher_name VARCHAR(100),
    notes TEXT,
    image_files TEXT,
    created_by INT,
    updated_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL
);

-- Insert dữ liệu mẫu
INSERT INTO users (username, password, full_name, email, role) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', 'admin@example.com', 'admin'),
('staff1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Nhân viên 1', 'staff1@example.com', 'staff');

INSERT INTO courses (course_name, description, price) VALUES
('Khóa học cơ bản', 'Khóa học dành cho người mới bắt đầu', 1000000.00),
('Khóa học nâng cao', 'Khóa học dành cho người có kinh nghiệm', 2000000.00),
('Khóa học chuyên sâu', 'Khóa học chuyên sâu về lĩnh vực chuyên môn', 3000000.00);