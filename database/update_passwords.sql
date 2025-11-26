-- Cập nhật password cho các tài khoản mặc định
-- Admin: username = admin, password = admin123
-- Staff: username = staff1, password = staff123

USE quan_ly_trung_tam;

-- Cập nhật password cho admin
UPDATE users SET password = '$2y$10$jDvYOFFJEq7c4hO61hCLAOV3aOk8pOVhi4gKBdFMJtqtumxQh6ba.' WHERE username = 'admin';

-- Cập nhật password cho staff
UPDATE users SET password = '$2y$10$J0Ppcku1BVtGcZCTLR5Kpe29Ux9Qy2xGxgKyDRd22uiJrBsQl30Li' WHERE username = 'staff1';

-- Kiểm tra kết quả
SELECT username, full_name, role, status FROM users WHERE username IN ('admin', 'staff1');