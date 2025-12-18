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

    public function updateReport($id, $data)
    {
        $fields = [];
        $values = [];
        
        foreach ($data as $field => $value) {
            if (in_array($field, $this->fillable)) {
                $fields[] = "$field = ?";
                $values[] = $value;
            }
        }
        
        $values[] = $id;
        
        $sql = "UPDATE {$this->table} SET " . implode(', ', $fields) . " WHERE id = ?";
        
        $stmt = $this->db->query($sql, $values);
        return $stmt->rowCount() > 0;
    }

    public function deleteReport($id)
    {
        $sql = "DELETE FROM {$this->table} WHERE id = ?";
        $stmt = $this->db->query($sql, [$id]);
        return $stmt->rowCount() > 0;
    }

    public function countReports($conditions = [])
    {
        $sql = "SELECT COUNT(*) as total FROM reports r";
        $params = [];
        
        if (!empty($conditions)) {
            $whereClause = [];
            foreach ($conditions as $column => $value) {
                if (strpos($column, '(') !== false) {
                    $whereClause[] = "$column = ?";
                } else {
                    $whereClause[] = "r.$column = ?";
                }
                $params[] = $value;
            }
            $sql .= " WHERE " . implode(' AND ', $whereClause);
        }
        
        $result = $this->db->fetch($sql, $params);
        return $result['total'] ?? 0;
    }

    public function getReportsWithStaffPaginated($conditions = [], $offset = 0, $limit = 20, $orderBy = 'report_date DESC, report_time DESC')
    {
        $sql = "SELECT r.*, u.full_name as staff_name 
                FROM reports r 
                JOIN users u ON r.staff_id = u.id";
        $params = [];
        
        if (!empty($conditions)) {
            $whereClause = [];
            foreach ($conditions as $column => $value) {
                if (strpos($column, '(') !== false) {
                    $whereClause[] = "$column = ?";
                } else {
                    $whereClause[] = "r.$column = ?";
                }
                $params[] = $value;
            }
            $sql .= " WHERE " . implode(' AND ', $whereClause);
        }
        
        if ($orderBy) {
            $sql .= " ORDER BY $orderBy";
        }
        
        $sql .= " LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        
        return $this->db->fetchAll($sql, $params);
    }

    public function getReportsByDateRangePaginated($startDate, $endDate, $staffId = null, $offset = 0, $limit = 20)
    {
        $sql = "SELECT r.*, u.full_name as staff_name 
                FROM reports r 
                JOIN users u ON r.staff_id = u.id 
                WHERE r.report_date BETWEEN ? AND ?";
        $params = [$startDate, $endDate];
        
        if ($staffId) {
            $sql .= " AND r.staff_id = ?";
            $params[] = $staffId;
        }
        
        $sql .= " ORDER BY r.report_date DESC, r.report_time DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        
        return $this->db->fetchAll($sql, $params);
    }

    public function countReportsByDateRange($startDate, $endDate, $staffId = null)
    {
        $sql = "SELECT COUNT(*) as total FROM reports r WHERE r.report_date BETWEEN ? AND ?";
        $params = [$startDate, $endDate];
        
        if ($staffId) {
            $sql .= " AND r.staff_id = ?";
            $params[] = $staffId;
        }
        
        $result = $this->db->fetch($sql, $params);
        return $result['total'] ?? 0;
    }

}