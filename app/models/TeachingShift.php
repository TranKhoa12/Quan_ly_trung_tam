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
}
