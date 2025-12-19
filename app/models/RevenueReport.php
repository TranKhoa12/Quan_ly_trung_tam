<?php

class RevenueReport extends BaseModel
{
    protected $table = 'revenue_reports';
    protected $fillable = [
        'payment_date', 'transfer_type', 'confirmation_image', 'confirmation_images', 'receipt_code',
        'amount', 'student_name', 'course_id', 'payment_content', 'staff_id', 'notes'
    ];
    protected $timestamps = false; // Tắt timestamps cho bảng revenue_reports (có trigger SQL)

    public function getRevenueWithDetails($conditions = [], $orderBy = 'payment_date DESC', $limit = null)
    {
        $sql = "SELECT rr.*, c.course_name, u.full_name as staff_name 
                FROM revenue_reports rr 
                LEFT JOIN courses c ON rr.course_id = c.id 
                JOIN users u ON rr.staff_id = u.id";
        $params = [];
        
        if (!empty($conditions)) {
            $whereClause = [];
            foreach ($conditions as $column => $value) {
                if ($column === 'payment_date_from') {
                    $whereClause[] = "rr.payment_date >= ?";
                    $params[] = $value;
                } elseif ($column === 'payment_date_to') {
                    $whereClause[] = "rr.payment_date <= ?";
                    $params[] = $value;
                } else {
                    $whereClause[] = "rr.$column = ?";
                    $params[] = $value;
                }
            }
            if (!empty($whereClause)) {
                $sql .= " WHERE " . implode(' AND ', $whereClause);
            }
        }
        
        if ($orderBy) {
            $sql .= " ORDER BY $orderBy";
        }
        
        if ($limit) {
            $sql .= " LIMIT $limit";
        }
        
        return $this->db->fetchAll($sql, $params);
    }

    public function getRevenueByStaff($staffId)
    {
        $sql = "SELECT rr.*, c.course_name, u.full_name as staff_name 
                FROM revenue_reports rr 
                LEFT JOIN courses c ON rr.course_id = c.id 
                JOIN users u ON rr.staff_id = u.id 
                WHERE rr.staff_id = ?
                ORDER BY rr.payment_date DESC";
        
        return $this->db->fetchAll($sql, [$staffId]);
    }

    public function getTodayRevenueByStaff($staffId)
    {
        $sql = "SELECT rr.*, c.course_name, u.full_name as staff_name 
                FROM revenue_reports rr 
                LEFT JOIN courses c ON rr.course_id = c.id 
                JOIN users u ON rr.staff_id = u.id 
                WHERE rr.staff_id = ? AND DATE(rr.payment_date) = CURDATE()
                ORDER BY rr.payment_date DESC, rr.created_at DESC";
        
        return $this->db->fetchAll($sql, [$staffId]);
    }

    public function checkReceiptCodeExists($receiptCode, $excludeId = null)
    {
        $sql = "SELECT COUNT(*) as count FROM revenue_reports WHERE receipt_code = ?";
        $params = [$receiptCode];
        
        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }
        
        $result = $this->db->fetch($sql, $params);
        return $result['count'] > 0;
    }

    public function getTodayRevenue()
    {
        return $this->getRevenueWithDetails(['DATE(payment_date)' => date('Y-m-d')]);
    }

    public function getRevenueByDateRange($startDate, $endDate)
    {
        return $this->getRevenueWithDetails([
            'payment_date_from' => $startDate,
            'payment_date_to' => $endDate
        ]);
    }

    public function getTotalRevenueByDate($date)
    {
        $sql = "SELECT SUM(amount) as total FROM revenue_reports WHERE DATE(payment_date) = ?";
        $result = $this->db->fetch($sql, [$date]);
        return $result['total'] ?? 0;
    }

    public function getRevenueByTransferType($startDate, $endDate)
    {
        $sql = "SELECT transfer_type, SUM(amount) as total, COUNT(*) as count 
                FROM revenue_reports 
                WHERE payment_date BETWEEN ? AND ? 
                GROUP BY transfer_type";
        
        return $this->db->fetchAll($sql, [$startDate, $endDate]);
    }

    public function getRevenueByPaymentContent($startDate, $endDate)
    {
        $sql = "SELECT payment_content, SUM(amount) as total, COUNT(*) as count 
                FROM revenue_reports 
                WHERE payment_date BETWEEN ? AND ? 
                GROUP BY payment_content";
        
        return $this->db->fetchAll($sql, [$startDate, $endDate]);
    }

    public function getRevenueWithFilters($conditions = [], $search = null, $offset = 0, $limit = 20)
    {
        $sql = "SELECT rr.*, c.course_name, u.full_name as staff_name 
                FROM revenue_reports rr 
                LEFT JOIN courses c ON rr.course_id = c.id 
                JOIN users u ON rr.staff_id = u.id";
        $params = [];
        $whereClause = [];
        
        // Build WHERE conditions
        foreach ($conditions as $column => $value) {
            if ($column === 'payment_date_from') {
                $whereClause[] = "rr.payment_date >= ?";
                $params[] = $value;
            } elseif ($column === 'payment_date_to') {
                $whereClause[] = "rr.payment_date <= ?";
                $params[] = $value;
            } elseif ($column === 'DATE(payment_date)') {
                $whereClause[] = "DATE(rr.payment_date) = ?";
                $params[] = $value;
            } elseif ($column === 'transfer_type_in' && is_array($value) && !empty($value)) {
                $placeholders = implode(',', array_fill(0, count($value), '?'));
                $whereClause[] = "rr.transfer_type IN ($placeholders)";
                foreach ($value as $v) {
                    $params[] = $v;
                }
            } else {
                $whereClause[] = "rr.$column = ?";
                $params[] = $value;
            }
        }
        
        // Add search condition
        if (!empty($search)) {
            $whereClause[] = "(rr.student_name LIKE ? OR c.course_name LIKE ? OR rr.receipt_code LIKE ?)";
            $searchTerm = "%$search%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        if (!empty($whereClause)) {
            $sql .= " WHERE " . implode(' AND ', $whereClause);
        }
        
        $sql .= " ORDER BY rr.payment_date DESC, rr.created_at DESC";
        $sql .= " LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        
        return $this->db->fetchAll($sql, $params);
    }

    public function countRevenue($conditions = [], $search = null)
    {
        $sql = "SELECT COUNT(*) as total 
                FROM revenue_reports rr 
                LEFT JOIN courses c ON rr.course_id = c.id";
        $params = [];
        $whereClause = [];
        
        // Build WHERE conditions
        foreach ($conditions as $column => $value) {
            if ($column === 'payment_date_from') {
                $whereClause[] = "rr.payment_date >= ?";
                $params[] = $value;
            } elseif ($column === 'payment_date_to') {
                $whereClause[] = "rr.payment_date <= ?";
                $params[] = $value;
            } elseif ($column === 'DATE(payment_date)') {
                $whereClause[] = "DATE(rr.payment_date) = ?";
                $params[] = $value;
            } elseif ($column === 'transfer_type_in' && is_array($value) && !empty($value)) {
                $placeholders = implode(',', array_fill(0, count($value), '?'));
                $whereClause[] = "rr.transfer_type IN ($placeholders)";
                foreach ($value as $v) {
                    $params[] = $v;
                }
            } else {
                $whereClause[] = "rr.$column = ?";
                $params[] = $value;
            }
        }
        
        // Add search condition
        if (!empty($search)) {
            $whereClause[] = "(rr.student_name LIKE ? OR c.course_name LIKE ? OR rr.receipt_code LIKE ?)";
            $searchTerm = "%$search%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        if (!empty($whereClause)) {
            $sql .= " WHERE " . implode(' AND ', $whereClause);
        }
        
        $result = $this->db->fetch($sql, $params);
        return $result['total'] ?? 0;
    }
}