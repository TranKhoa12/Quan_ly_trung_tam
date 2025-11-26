-- Update payment_method column to VARCHAR to support Vietnamese text
ALTER TABLE report_customers 
MODIFY COLUMN payment_method VARCHAR(50) NULL;
