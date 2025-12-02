-- Script xóa toàn bộ dữ liệu lịch dạy
-- Chạy script này để reset dữ liệu ca dạy trong database

-- Tắt foreign key checks tạm thời
SET FOREIGN_KEY_CHECKS = 0;

-- Xóa log chuyển ca
TRUNCATE TABLE shift_transfer_logs;

-- Xóa yêu cầu chuyển ca
TRUNCATE TABLE shift_transfers;

-- Xóa bảng lương ca dạy
TRUNCATE TABLE shift_payrolls;

-- Xóa đăng ký ca dạy
TRUNCATE TABLE shift_registrations;

-- Bật lại foreign key checks
SET FOREIGN_KEY_CHECKS = 1;

-- Thông báo hoàn thành
SELECT 'Đã xóa toàn bộ dữ liệu lịch dạy thành công!' AS message;
