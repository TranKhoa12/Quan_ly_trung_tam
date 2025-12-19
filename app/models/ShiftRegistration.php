<?php

class ShiftRegistration extends BaseModel
{
    protected $table = 'shift_registrations';
    protected $fillable = [
        'staff_id',
        'shift_id',
        'shift_date',
        'custom_start',
        'custom_end',
        'hours',
        'hourly_rate',
        'status',
        'notes',
        'approved_by',
        'approved_at'
    ];

    public function getForStaff($staffId, $fromDate = null, $toDate = null)
    {
        $sql = "SELECT sr.*, ts.name AS shift_name, ts.start_time AS preset_start, ts.end_time AS preset_end,
                       ts.hourly_rate, approver.full_name AS approver_name
                FROM {$this->table} sr
                LEFT JOIN teaching_shifts ts ON sr.shift_id = ts.id
                LEFT JOIN users approver ON sr.approved_by = approver.id
                WHERE sr.staff_id = ?";
        $params = [$staffId];

        if ($fromDate) {
            $sql .= " AND sr.shift_date >= ?";
            $params[] = $fromDate;
        }

        if ($toDate) {
            $sql .= " AND sr.shift_date <= ?";
            $params[] = $toDate;
        }

        $sql .= " ORDER BY sr.shift_date DESC, COALESCE(sr.custom_start, ts.start_time)";
        return $this->db->fetchAll($sql, $params);
    }

    public function hasOverlap($staffId, $shiftDate, $startTime, $endTime, $excludeId = null)
    {
        $sql = "SELECT COUNT(*) as total
                FROM {$this->table} sr
                LEFT JOIN teaching_shifts ts ON sr.shift_id = ts.id
                WHERE sr.staff_id = ?
                  AND sr.shift_date = ?
                  AND sr.status IN ('pending','approved')
                  AND (
                        (COALESCE(sr.custom_start, ts.start_time) < ? AND COALESCE(sr.custom_end, ts.end_time) > ?)
                     OR (COALESCE(sr.custom_start, ts.start_time) >= ? AND COALESCE(sr.custom_start, ts.start_time) < ?)
                     OR (COALESCE(sr.custom_end, ts.end_time) > ? AND COALESCE(sr.custom_end, ts.end_time) <= ?)
                  )";
        $params = [$staffId, $shiftDate, $endTime, $startTime, $startTime, $endTime, $startTime, $endTime];

        if ($excludeId) {
            $sql .= " AND sr.id != ?";
            $params[] = $excludeId;
        }

        $result = $this->db->fetch($sql, $params);
        return (int)($result['total'] ?? 0) > 0;
    }

    public function getAdminList($filters = [])
    {
        $sql = "SELECT sr.*, ts.name AS shift_name, ts.start_time AS preset_start, ts.end_time AS preset_end,
                       u.full_name AS staff_name, approver.full_name AS approver_name
                FROM {$this->table} sr
                LEFT JOIN teaching_shifts ts ON sr.shift_id = ts.id
                LEFT JOIN users u ON sr.staff_id = u.id
                LEFT JOIN users approver ON sr.approved_by = approver.id
                WHERE 1 = 1";
        $params = [];

        if (!empty($filters['status'])) {
            $sql .= " AND sr.status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['date_from'])) {
            $sql .= " AND sr.shift_date >= ?";
            $params[] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $sql .= " AND sr.shift_date <= ?";
            $params[] = $filters['date_to'];
        }

        if (!empty($filters['staff_id'])) {
            $sql .= " AND sr.staff_id = ?";
            $params[] = $filters['staff_id'];
        }

        $sql .= " ORDER BY sr.shift_date DESC, sr.created_at DESC";
        return $this->db->fetchAll($sql, $params);
    }

    public function aggregateHours($periodStart, $periodEnd)
    {
        $sql = "SELECT sr.staff_id, u.full_name, COUNT(*) AS total_shifts,
                   SUM(sr.hours) AS total_hours,
                   SUM(sr.hours * COALESCE(ts.hourly_rate, 50) * 1000) AS total_amount
                FROM {$this->table} sr
                LEFT JOIN teaching_shifts ts ON sr.shift_id = ts.id
                LEFT JOIN users u ON sr.staff_id = u.id
                WHERE sr.status = 'approved'
                  AND sr.shift_date BETWEEN ? AND ?
                GROUP BY sr.staff_id, u.full_name
                ORDER BY u.full_name";
        return $this->db->fetchAll($sql, [$periodStart, $periodEnd]);
    }
}
