-- Add tax-related columns to shift_payrolls
ALTER TABLE shift_payrolls
    ADD COLUMN tax_rate DECIMAL(5,4) NOT NULL DEFAULT 0.10 AFTER total_amount,
    ADD COLUMN tax_amount BIGINT NOT NULL DEFAULT 0 AFTER tax_rate,
    ADD COLUMN net_amount BIGINT NOT NULL DEFAULT 0 AFTER tax_amount;
