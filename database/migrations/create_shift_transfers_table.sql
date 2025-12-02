-- Tạo bảng shift_transfers để quản lý yêu cầu chuyển ca
CREATE TABLE IF NOT EXISTS shift_transfers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    shift_registration_id INT NOT NULL COMMENT 'ID ca dạy cần chuyển',
    from_staff_id INT NOT NULL COMMENT 'Nhân viên chuyển ca',
    to_staff_id INT NOT NULL COMMENT 'Nhân viên nhận ca',
    reason TEXT NULL COMMENT 'Lý do chuyển ca',
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending' COMMENT 'Trạng thái yêu cầu',
    admin_id INT NULL COMMENT 'Admin duyệt/từ chối',
    admin_note TEXT NULL COMMENT 'Ghi chú của admin',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    processed_at TIMESTAMP NULL COMMENT 'Thời gian xử lý',
    
    FOREIGN KEY (shift_registration_id) REFERENCES shift_registrations(id) ON DELETE CASCADE,
    FOREIGN KEY (from_staff_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (to_staff_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (admin_id) REFERENCES users(id) ON DELETE SET NULL,
    
    INDEX idx_transfer_status (status),
    INDEX idx_transfer_from_staff (from_staff_id),
    INDEX idx_transfer_to_staff (to_staff_id),
    INDEX idx_transfer_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
