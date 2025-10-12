<?php

class Report extends BaseModel
{
    protected $table = 'reports';
    protected $fillable = [
        'report_date', 'report_time', 'staff_id', 'total_visitors', 'total_registered', 'notes'
    ];
    protected $timestamps = false; // Tắt timestamps cho bảng reports

    public function getReportsWithStaff($conditions = [], $orderBy = 'report_date DESC, report_time DESC', $limit = null)
    {
        $sql = "SELECT r.*, u.full_name as staff_name 
                FROM reports r 
                JOIN users u ON r.staff_id = u.id";
        $params = [];
        
        if (!empty($conditions)) {
            $whereClause = [];
            foreach ($conditions as $column => $value) {
                $whereClause[] = "r.$column = ?";
                $params[] = $value;
            }
            $sql .= " WHERE " . implode(' AND ', $whereClause);
        }
        
        if ($orderBy) {
            $sql .= " ORDER BY $orderBy";
        }
        
        if ($limit) {
            $sql .= " LIMIT $limit";
        }
        
        return $this->db->fetchAll($sql, $params);
    }

    public function getReportsByStaff($staffId)
    {
        $sql = "SELECT r.*, u.full_name as staff_name 
                FROM reports r 
                JOIN users u ON r.staff_id = u.id 
                WHERE r.staff_id = ?
                ORDER BY r.report_date DESC, r.report_time DESC";
        
        return $this->db->fetchAll($sql, [$staffId]);
    }

    public function getTodayReportsByStaff($staffId)
    {
        $sql = "SELECT r.*, u.full_name as staff_name 
                FROM reports r 
                JOIN users u ON r.staff_id = u.id 
                WHERE r.staff_id = ? AND DATE(r.report_date) = CURDATE()
                ORDER BY r.report_time DESC";
        
        return $this->db->fetchAll($sql, [$staffId]);
    }

    public function getReportWithDetails($reportId)
    {
        $sql = "SELECT r.*, u.full_name as staff_name 
                FROM reports r 
                JOIN users u ON r.staff_id = u.id 
                WHERE r.id = ?";
        
        return $this->db->fetch($sql, [$reportId]);
    }

    public function getTodayReports()
    {
        return $this->getReportsWithStaff(['DATE(report_date)' => date('Y-m-d')]);
    }

    public function getReportsByDateRange($startDate, $endDate)
    {
        $sql = "SELECT r.*, u.full_name as staff_name 
                FROM reports r 
                JOIN users u ON r.staff_id = u.id 
                WHERE r.report_date BETWEEN ? AND ?
                ORDER BY r.report_date DESC, r.report_time DESC";
        
        return $this->db->fetchAll($sql, [$startDate, $endDate]);
    }
}