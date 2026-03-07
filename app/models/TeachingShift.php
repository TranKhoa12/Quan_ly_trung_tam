<?php

class TeachingShift extends BaseModel
{
    protected $table = 'teaching_shifts';
    protected $fillable = [
        'name',
        'start_time',
        'end_time',
        'hourly_rate',
        'is_active'
    ];

    public function getActiveShifts()
    {
        $sql = "SELECT * FROM {$this->table} WHERE is_active = 1 ORDER BY start_time IS NULL, start_time";
        return $this->db->fetchAll($sql);
    }

    public function getAllShifts()
    {
        $sql = "SELECT ts.*,
                    (SELECT COUNT(*) FROM shift_registrations sr WHERE sr.shift_id = ts.id) AS registration_count
                FROM {$this->table} ts
                ORDER BY ts.start_time IS NULL, ts.start_time";
        return $this->db->fetchAll($sql);
    }

    public function hasRegistrations($id)
    {
        $sql = "SELECT COUNT(*) AS total FROM shift_registrations WHERE shift_id = ?";
        $result = $this->db->fetch($sql, [$id]);
        return isset($result['total']) && (int)$result['total'] > 0;
    }

    public function createShiftType(array $data)
    {
        $sql = "INSERT INTO {$this->table} (name, start_time, end_time, hourly_rate, is_active)
                VALUES (?, ?, ?, ?, 1)";
        $this->db->execute($sql, [
            $data['name'],
            $data['start_time'],
            $data['end_time'],
            $data['hourly_rate']
        ]);
        return $this->db->lastInsertId();
    }

    public function updateShiftType($id, array $data)
    {
        $sql = "UPDATE {$this->table} SET name = ?, start_time = ?, end_time = ?, hourly_rate = ? WHERE id = ?";
        return $this->db->execute($sql, [
            $data['name'],
            $data['start_time'],
            $data['end_time'],
            $data['hourly_rate'],
            $id
        ]);
    }

    public function toggleActive($id)
    {
        $sql = "UPDATE {$this->table} SET is_active = 1 - is_active WHERE id = ?";
        return $this->db->execute($sql, [$id]);
    }
}
