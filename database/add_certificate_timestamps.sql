-- Thêm cột approved_at và received_at vào bảng certificates

ALTER TABLE certificates 
ADD COLUMN approved_at TIMESTAMP NULL AFTER approved_by,
ADD COLUMN received_at TIMESTAMP NULL AFTER approved_at;

-- Comment
-- approved_at: Thời gian phê duyệt chứng nhận
-- received_at: Thời gian học viên nhận chứng nhận
