-- Migration: Tăng kích thước cột để hỗ trợ Cloudinary JSON
-- Date: 2025-12-26

-- Tăng kích thước cột confirmation_image trong revenue_reports
ALTER TABLE revenue_reports 
MODIFY COLUMN confirmation_image TEXT NULL;

-- Tăng kích thước cột confirmation_images nếu có
ALTER TABLE revenue_reports 
MODIFY COLUMN confirmation_images TEXT NULL;

-- Tăng kích thước cho bảng completion_slips
ALTER TABLE completion_slips 
MODIFY COLUMN images TEXT NULL;

-- Tăng kích thước cho bảng students
ALTER TABLE students 
MODIFY COLUMN tracking_image TEXT NULL;

-- Tăng kích thước cho bảng certificates nếu có
-- ALTER TABLE certificates 
-- MODIFY COLUMN image_path TEXT NULL;

SELECT 'Migration completed successfully!' as status;
