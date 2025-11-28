-- Thêm cột received_by để lưu ID nhân viên xác nhận đã nhận chứng nhận
ALTER TABLE certificates 
ADD COLUMN received_by INT NULL AFTER received_at,
ADD FOREIGN KEY (received_by) REFERENCES users(id) ON DELETE SET NULL;
