-- =====================================================
-- QUAN TRỌNG: CHẠY FILE NÀY TRƯỚC KHI SỬ DỤNG
-- =====================================================
-- File: database/admin_logs.sql
-- Mục đích: Tạo bảng admin_logs để lưu log hoạt động
-- Chạy lệnh: mysql -u root -p your_database < admin_logs.sql
-- Hoặc import trực tiếp qua phpMyAdmin/TablePlus
-- =====================================================

-- Kiểm tra và tạo bảng admin_logs
CREATE TABLE IF NOT EXISTS admin_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL COMMENT 'ID của user thực hiện hành động',
    username VARCHAR(100) COMMENT 'Tên đăng nhập của user',
    action_type ENUM('login', 'logout', 'create', 'update', 'delete', 'view', 'export', 'other') NOT NULL COMMENT 'Loại hành động',
    module VARCHAR(100) NOT NULL COMMENT 'Module bị tác động: students, teachers, courses, etc.',
    description TEXT COMMENT 'Mô tả chi tiết về hành động',
    ip_address VARCHAR(45) COMMENT 'IP address của user (hỗ trợ IPv6)',
    user_agent TEXT COMMENT 'Browser và thiết bị của user',
    request_data JSON COMMENT 'Dữ liệu request (tham số, form data)',
    old_data JSON COMMENT 'Dữ liệu cũ trước khi thay đổi (cho update/delete)',
    new_data JSON COMMENT 'Dữ liệu mới sau khi thay đổi (cho create/update)',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_id (user_id),
    INDEX idx_action_type (action_type),
    INDEX idx_module (module),
    INDEX idx_created_at (created_at),
    INDEX idx_user_action (user_id, action_type),
    INDEX idx_user_created (user_id, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Ghi log các hoạt động của nhân viên trong hệ thống';

-- Kiểm tra bảng đã tạo thành công
SELECT 'Bảng admin_logs đã được tạo thành công!' AS status;

-- Xem cấu trúc bảng
DESCRIBE admin_logs;
