-- Migration: Thêm cột available_at_center vào bảng certificates
-- Mục đích: Theo dõi chứng nhận đã có tại trung tâm hay chưa
-- Ngày tạo: 2025-12-24

-- Kiểm tra và thêm cột available_at_center
SET @column_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'certificates' 
    AND COLUMN_NAME = 'available_at_center'
);

SET @sql = IF(@column_exists = 0,
    'ALTER TABLE certificates ADD COLUMN available_at_center ENUM(''yes'', ''no'') DEFAULT ''no'' AFTER receive_status',
    'SELECT "Column available_at_center already exists" AS message'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Kiểm tra và thêm cột available_date
SET @column_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'certificates' 
    AND COLUMN_NAME = 'available_date'
);

SET @sql = IF(@column_exists = 0,
    'ALTER TABLE certificates ADD COLUMN available_date DATETIME NULL AFTER available_at_center',
    'SELECT "Column available_date already exists" AS message'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Kiểm tra và thêm cột available_confirmed_by
SET @column_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'certificates' 
    AND COLUMN_NAME = 'available_confirmed_by'
);

SET @sql = IF(@column_exists = 0,
    'ALTER TABLE certificates ADD COLUMN available_confirmed_by INT NULL AFTER available_date',
    'SELECT "Column available_confirmed_by already exists" AS message'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Kiểm tra và thêm foreign key
SET @fk_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'certificates' 
    AND CONSTRAINT_NAME = 'fk_certificates_available_confirmed_by'
);

SET @sql = IF(@fk_exists = 0,
    'ALTER TABLE certificates ADD CONSTRAINT fk_certificates_available_confirmed_by FOREIGN KEY (available_confirmed_by) REFERENCES users(id) ON DELETE SET NULL',
    'SELECT "Foreign key fk_certificates_available_confirmed_by already exists" AS message'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Hiển thị kết quả
SELECT 'Migration completed successfully!' AS status;
SELECT 'Added columns: available_at_center, available_date, available_confirmed_by' AS changes;
