-- ============================================================
-- Cập nhật loại ca dạy - 08/03/2026
-- 5 ca mới: Ca1 8h30-10h | Ca2 10h-11h30 | Ca3 14h-15h30
--           Ca4 17h-18h30 | Ca5 19h-20h30
-- ============================================================
-- QUAN TRỌNG:
--   - Script này VÔ HIỆU HOÁ các ca cũ (is_active = 0), KHÔNG xoá.
--   - Các đăng ký (shift_registrations) cũ KHÔNG bị ảnh hưởng.
--   - Báo cáo & bảng lương vẫn chính xác.
-- ============================================================

-- Bước 1: Vô hiệu hoá tất cả ca cũ (giữ lại dữ liệu lịch sử)
UPDATE teaching_shifts SET is_active = 0;

-- Bước 2: Thêm 5 ca mới (nếu đã tồn tại tên trùng thì bỏ qua)
INSERT INTO teaching_shifts (name, start_time, end_time, hourly_rate, is_active)
SELECT 'Ca 1 (8h30-10h)', '08:30:00', '10:00:00', 50000.00, 1
WHERE NOT EXISTS (
    SELECT 1 FROM teaching_shifts WHERE name = 'Ca 1 (8h30-10h)'
);

INSERT INTO teaching_shifts (name, start_time, end_time, hourly_rate, is_active)
SELECT 'Ca 2 (10h-11h30)', '10:00:00', '11:30:00', 50000.00, 1
WHERE NOT EXISTS (
    SELECT 1 FROM teaching_shifts WHERE name = 'Ca 2 (10h-11h30)'
);

INSERT INTO teaching_shifts (name, start_time, end_time, hourly_rate, is_active)
SELECT 'Ca 3 (14h-15h30)', '14:00:00', '15:30:00', 50000.00, 1
WHERE NOT EXISTS (
    SELECT 1 FROM teaching_shifts WHERE name = 'Ca 3 (14h-15h30)'
);

INSERT INTO teaching_shifts (name, start_time, end_time, hourly_rate, is_active)
SELECT 'Ca 4 (17h-18h30)', '17:00:00', '18:30:00', 50000.00, 1
WHERE NOT EXISTS (
    SELECT 1 FROM teaching_shifts WHERE name = 'Ca 4 (17h-18h30)'
);

INSERT INTO teaching_shifts (name, start_time, end_time, hourly_rate, is_active)
SELECT 'Ca 5 (19h-20h30)', '19:00:00', '20:30:00', 50000.00, 1
WHERE NOT EXISTS (
    SELECT 1 FROM teaching_shifts WHERE name = 'Ca 5 (19h-20h30)'
);

-- Kiểm tra kết quả
SELECT id, name, start_time, end_time, hourly_rate,
       CASE WHEN is_active = 1 THEN 'Đang hoạt động' ELSE 'Đã vô hiệu hoá' END AS status,
       (SELECT COUNT(*) FROM shift_registrations sr WHERE sr.shift_id = teaching_shifts.id) AS so_dang_ky
FROM teaching_shifts
ORDER BY is_active DESC, start_time;
