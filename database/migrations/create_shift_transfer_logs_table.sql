-- Tạo bảng shift_transfer_logs để lưu lịch sử chuyển ca
CREATE TABLE IF NOT EXISTS shift_transfer_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    shift_transfer_id INT NOT NULL COMMENT 'ID yêu cầu chuyển ca',
    action VARCHAR(50) NOT NULL COMMENT 'Hành động: created, approved, rejected',
    actor_id INT NOT NULL COMMENT 'Người thực hiện hành động',
    notes TEXT NULL COMMENT 'Ghi chú',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (shift_transfer_id) REFERENCES shift_transfers(id) ON DELETE CASCADE,
    FOREIGN KEY (actor_id) REFERENCES users(id) ON DELETE CASCADE,
    
    INDEX idx_log_transfer (shift_transfer_id),
    INDEX idx_log_action (action),
    INDEX idx_log_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
