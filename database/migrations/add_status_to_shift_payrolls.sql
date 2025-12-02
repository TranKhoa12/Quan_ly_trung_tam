-- Thêm cột status vào bảng shift_payrolls để quản lý trạng thái
ALTER TABLE shift_payrolls 
ADD COLUMN status ENUM('active', 'cancelled') NOT NULL DEFAULT 'active' AFTER generated_by;

-- Thêm index cho tìm kiếm nhanh
CREATE INDEX idx_payroll_status ON shift_payrolls(status);
CREATE INDEX idx_payroll_period_status ON shift_payrolls(period_start, period_end, status);
