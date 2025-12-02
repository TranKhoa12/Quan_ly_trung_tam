<?php

class CreateTeachingShiftTables
{
    public function up($db)
    {
        // teaching_shifts
        $sql = "CREATE TABLE IF NOT EXISTS teaching_shifts (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            start_time TIME NULL,
            end_time TIME NULL,
            hourly_rate DECIMAL(8,2) NOT NULL DEFAULT 50.00,
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        $db->query($sql);
        $db->execute();

        // shift_registrations
        $sql = "CREATE TABLE IF NOT EXISTS shift_registrations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            staff_id INT NOT NULL,
            shift_id INT NULL,
            shift_date DATE NOT NULL,
            custom_start TIME NULL,
            custom_end TIME NULL,
            hours DECIMAL(5,2) NOT NULL DEFAULT 0,
            status ENUM('pending','approved','rejected','cancelled') NOT NULL DEFAULT 'pending',
            notes TEXT NULL,
            approved_by INT NULL,
            approved_at TIMESTAMP NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            CONSTRAINT fk_shift_reg_staff FOREIGN KEY (staff_id) REFERENCES users(id) ON DELETE CASCADE,
            CONSTRAINT fk_shift_reg_shift FOREIGN KEY (shift_id) REFERENCES teaching_shifts(id) ON DELETE SET NULL,
            CONSTRAINT fk_shift_reg_approver FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        $db->query($sql);
        $db->execute();

        // shift_payrolls
        $sql = "CREATE TABLE IF NOT EXISTS shift_payrolls (
            id INT AUTO_INCREMENT PRIMARY KEY,
            staff_id INT NOT NULL,
            period_start DATE NOT NULL,
            period_end DATE NOT NULL,
            total_hours DECIMAL(8,2) NOT NULL DEFAULT 0,
            total_amount DECIMAL(12,2) NOT NULL DEFAULT 0,
            notes TEXT NULL,
            generated_by INT NULL,
            generated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY uniq_staff_period (staff_id, period_start, period_end),
            CONSTRAINT fk_shift_payroll_staff FOREIGN KEY (staff_id) REFERENCES users(id) ON DELETE CASCADE,
            CONSTRAINT fk_shift_payroll_generated FOREIGN KEY (generated_by) REFERENCES users(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        $db->query($sql);
        $db->execute();

        // Seed default shifts if empty
        $sql = "SELECT COUNT(*) AS total FROM teaching_shifts";
        $db->query($sql);
        $countResult = $db->single();
        $count = isset($countResult->total) ? (int)$countResult->total : 0;

        if ($count === 0) {
            $seedSql = "INSERT INTO teaching_shifts (name, start_time, end_time, hourly_rate) VALUES
                ('Ca 1 (8h30-10h30)', '08:30:00', '10:30:00', 50.00),
                ('Ca 2 (14h00-16h00)', '14:00:00', '16:00:00', 50.00),
                ('Ca 3 (17h00-19h00)', '17:00:00', '19:00:00', 50.00),
                ('Ca 4 (18h00-20h00)', '18:00:00', '20:00:00', 50.00),
                ('Ca 5 (19h00-21h00)', '19:00:00', '21:00:00', 50.00)";
            $db->query($seedSql);
            $db->execute();
        }
    }

    public function down($db)
    {
        $db->query("DROP TABLE IF EXISTS shift_payrolls");
        $db->execute();

        $db->query("DROP TABLE IF EXISTS shift_registrations");
        $db->execute();

        $db->query("DROP TABLE IF EXISTS teaching_shifts");
        $db->execute();
    }
}
