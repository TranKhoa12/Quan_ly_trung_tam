-- Cho phép cột phone trong completion_slips nhận giá trị NULL
ALTER TABLE completion_slips
    MODIFY COLUMN phone VARCHAR(20) NULL;
