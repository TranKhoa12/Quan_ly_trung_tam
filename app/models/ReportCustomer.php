<?php

class ReportCustomer extends BaseModel
{
    protected $table = 'report_customers';
    protected $fillable = [
        'report_id', 'phone', 'full_name', 'status', 'course_id', 
        'registration_status', 'payment_method', 'notes'
    ];
    protected $timestamps = false; // Tắt timestamps cho bảng report_customers

    public function getCustomersByReport($reportId)
    {
        $sql = "SELECT rc.*, c.course_name 
                FROM report_customers rc 
                LEFT JOIN courses c ON rc.course_id = c.id 
                WHERE rc.report_id = ?
                ORDER BY rc.created_at ASC";
        
        return $this->db->fetchAll($sql, [$reportId]);
    }

    public function getCustomersWithCourse($conditions = [])
    {
        $sql = "SELECT rc.*, c.course_name 
                FROM report_customers rc 
                LEFT JOIN courses c ON rc.course_id = c.id";
        $params = [];
        
        if (!empty($conditions)) {
            $whereClause = [];
            foreach ($conditions as $column => $value) {
                $whereClause[] = "rc.$column = ?";
                $params[] = $value;
            }
            $sql .= " WHERE " . implode(' AND ', $whereClause);
        }
        
        $sql .= " ORDER BY rc.created_at DESC";
        
        return $this->db->fetchAll($sql, $params);
    }

    public function deleteByReport($reportId)
    {
        $sql = "DELETE FROM {$this->table} WHERE report_id = ?";
        return $this->db->query($sql, [$reportId]);
    }
}