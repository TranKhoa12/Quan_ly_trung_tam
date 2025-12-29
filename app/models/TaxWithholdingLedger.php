<?php

class TaxWithholdingLedger extends BaseModel
{
    protected $table = 'tax_withholding_ledger';
    protected $timestamps = false;
    protected $fillable = [
        'payroll_id',
        'staff_id',
        'period_start',
        'period_end',
        'gross_amount',
        'tax_rate',
        'tax_amount',
        'net_amount',
        'paid_at',
        'remitted_at',
        'status'
    ];

    public function upsertLedger($payrollId, $staffId, $periodStart, $periodEnd, $grossAmount, $taxRate, $taxAmount, $netAmount)
    {
        $existing = $this->findByPeriod($staffId, $periodStart, $periodEnd);
        $data = [
            'payroll_id' => $payrollId,
            'staff_id' => $staffId,
            'period_start' => $periodStart,
            'period_end' => $periodEnd,
            'gross_amount' => $grossAmount,
            'tax_rate' => $taxRate,
            'tax_amount' => $taxAmount,
            'net_amount' => $netAmount,
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

    public function cancelByPeriod($periodStart, $periodEnd)
    {
        $sql = "UPDATE {$this->table} SET status = 'cancelled' WHERE period_start = ? AND period_end = ? AND status = 'active'";
        return $this->db->execute($sql, [$periodStart, $periodEnd]);
    }

    public function cancelStaff($staffId, $periodStart, $periodEnd)
    {
        $sql = "UPDATE {$this->table} SET status = 'cancelled' WHERE staff_id = ? AND period_start = ? AND period_end = ? AND status = 'active'";
        return $this->db->execute($sql, [$staffId, $periodStart, $periodEnd]);
    }

    public function getByPeriod($periodStart, $periodEnd, $status = 'active')
    {
        $sql = "SELECT * FROM {$this->table} WHERE period_start = ? AND period_end = ?";
        $params = [$periodStart, $periodEnd];
        if ($status !== 'all') {
            $sql .= " AND status = ?";
            $params[] = $status;
        }
        $sql .= " ORDER BY staff_id";
        return $this->db->fetchAll($sql, $params);
    }

    public function getMonthlySummary($year)
    {
        $sql = "SELECT 
                    DATE_FORMAT(period_start, '%Y-%m') AS month_key,
                    SUM(gross_amount) AS gross_sum,
                    SUM(tax_amount) AS tax_sum,
                    SUM(net_amount) AS net_sum,
                    COUNT(DISTINCT staff_id) AS staff_count
                FROM {$this->table}
                WHERE status = 'active' AND YEAR(period_start) = ?
                GROUP BY month_key
                ORDER BY month_key";
        return $this->db->fetchAll($sql, [$year]);
    }

    public function getMonthStaffDetail($periodStart, $periodEnd)
    {
        $sql = "SELECT 
                    l.staff_id,
                    u.full_name,
                    SUM(l.gross_amount) AS gross_sum,
                    SUM(l.tax_amount) AS tax_sum,
                    SUM(l.net_amount) AS net_sum
                FROM {$this->table} l
                LEFT JOIN users u ON l.staff_id = u.id
                WHERE l.status = 'active' AND l.period_start = ? AND l.period_end = ?
                GROUP BY l.staff_id, u.full_name
                ORDER BY u.full_name";
        return $this->db->fetchAll($sql, [$periodStart, $periodEnd]);
    }

    public function getYearTotals($year)
    {
        $sql = "SELECT 
                    SUM(gross_amount) AS gross_sum,
                    SUM(tax_amount) AS tax_sum,
                    SUM(net_amount) AS net_sum,
                    COUNT(DISTINCT staff_id) AS staff_count,
                    COUNT(DISTINCT CONCAT(period_start, ':', period_end)) AS periods
                FROM {$this->table}
                WHERE status = 'active' AND YEAR(period_start) = ?";
        return $this->db->fetch($sql, [$year]);
    }
}
