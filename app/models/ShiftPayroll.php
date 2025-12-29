<?php

class ShiftPayroll extends BaseModel
{
    protected $table = 'shift_payrolls';
    protected $timestamps = false;
    protected $fillable = [
        'staff_id',
        'period_start',
        'period_end',
        'total_hours',
        'total_amount',
        'tax_rate',
        'tax_amount',
        'net_amount',
        'notes',
        'generated_by',
        'status'
    ];

    public function upsertPayroll($staffId, $periodStart, $periodEnd, $hours, $grossAmount, $generatedBy, $taxRate = 0.10)
    {
        $existing = $this->findByPeriod($staffId, $periodStart, $periodEnd);
        $taxAmount = round($grossAmount * $taxRate);
        $netAmount = $grossAmount - $taxAmount;
        $data = [
            'staff_id' => $staffId,
            'period_start' => $periodStart,
            'period_end' => $periodEnd,
            'total_hours' => $hours,
            'total_amount' => $grossAmount,
            'tax_rate' => $taxRate,
            'tax_amount' => $taxAmount,
            'net_amount' => $netAmount,
            'generated_by' => $generatedBy,
            'status' => 'active'
        ];

        if ($existing) {
            $this->update($existing['id'], $data);
            return $existing['id'];
        }

        return $this->create($data);
    }

    public function findByPeriod($staffId, $periodStart, $periodEnd)
    {
        $sql = "SELECT * FROM {$this->table} WHERE staff_id = ? AND period_start = ? AND period_end = ? LIMIT 1";
        return $this->db->fetch($sql, [$staffId, $periodStart, $periodEnd]);
    }

    public function getByPeriod($periodStart, $periodEnd, $includeStatus = 'active')
    {
        $sql = "SELECT sp.*, u.full_name
                FROM {$this->table} sp
                LEFT JOIN users u ON sp.staff_id = u.id
                WHERE sp.period_start = ? AND sp.period_end = ?";
        
        if ($includeStatus !== 'all') {
            $sql .= " AND sp.status = '" . $includeStatus . "'";
        }
        
        $sql .= " ORDER BY u.full_name";
        return $this->db->fetchAll($sql, [$periodStart, $periodEnd]);
    }

    public function cancelByPeriod($periodStart, $periodEnd)
    {
        $sql = "UPDATE {$this->table} SET status = 'cancelled' WHERE period_start = ? AND period_end = ? AND status = 'active'";
        return $this->db->execute($sql, [$periodStart, $periodEnd]);
    }

    public function isShiftInLockedPayroll($staffId, $shiftDate)
    {
        // Check if shift is in any active (saved) payroll
        $sql = "SELECT COUNT(*) as count FROM {$this->table} 
                WHERE staff_id = ? 
                AND status = 'active'
                AND period_start <= ? 
                AND period_end >= ?";
        $result = $this->db->fetch($sql, [$staffId, $shiftDate, $shiftDate]);
        return ($result['count'] ?? 0) > 0;
    }
}
