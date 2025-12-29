-- Ledger for 10% withholding on part-time payroll
CREATE TABLE IF NOT EXISTS tax_withholding_ledger (
    id INT AUTO_INCREMENT PRIMARY KEY,
    payroll_id INT NULL,
    staff_id INT NOT NULL,
    period_start DATE NOT NULL,
    period_end DATE NOT NULL,
    gross_amount BIGINT NOT NULL DEFAULT 0,
    tax_rate DECIMAL(5,4) NOT NULL DEFAULT 0.10,
    tax_amount BIGINT NOT NULL DEFAULT 0,
    net_amount BIGINT NOT NULL DEFAULT 0,
    paid_at DATETIME NULL,
    remitted_at DATETIME NULL,
    status ENUM('active','cancelled') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_ledger_staff_period (staff_id, period_start, period_end),
    KEY idx_period (period_start, period_end),
    KEY idx_payroll (payroll_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
