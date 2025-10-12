<?php

class Certificate extends BaseModel
{
    protected $table = 'certificates';
    protected $fillable = [
        'student_name', 'username', 'phone', 'subject', 'receive_status',
        'approval_status', 'notes', 'requested_by', 'approved_by'
    ];
    protected $timestamps = false; // Tắt timestamps cho bảng certificates

    public function getCertificatesWithDetails($conditions = [], $orderBy = 'id DESC', $limit = null)
    {
        $sql = "SELECT c.*, 
                u1.full_name as requested_by_name,
                u2.full_name as approved_by_name 
                FROM certificates c 
                LEFT JOIN users u1 ON c.requested_by = u1.id 
                LEFT JOIN users u2 ON c.approved_by = u2.id";
        $params = [];
        
        if (!empty($conditions)) {
            $whereClause = [];
            foreach ($conditions as $column => $value) {
                $whereClause[] = "c.$column = ?";
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

    public function getCertificatesByStaff($staffId)
    {
        $sql = "SELECT c.*, 
                u1.full_name as requested_by_name,
                u2.full_name as approved_by_name 
                FROM certificates c 
                LEFT JOIN users u1 ON c.requested_by = u1.id 
                LEFT JOIN users u2 ON c.approved_by = u2.id 
                WHERE c.requested_by = ?
                ORDER BY c.id DESC";
        
        return $this->db->fetchAll($sql, [$staffId]);
    }

    public function getTodayCertificatesByStaff($staffId)
    {
        // Vì bảng certificates không có trường ngày tháng, trả về tất cả certificates của staff
        return $this->getCertificatesByStaff($staffId);
    }

    public function getCertificateWithDetails($certificateId)
    {
        $sql = "SELECT c.*, 
                u1.full_name as requested_by_name,
                u2.full_name as approved_by_name 
                FROM certificates c 
                LEFT JOIN users u1 ON c.requested_by = u1.id 
                LEFT JOIN users u2 ON c.approved_by = u2.id 
                WHERE c.id = ?";
        
        return $this->db->fetch($sql, [$certificateId]);
    }

    public function getPendingCertificates()
    {
        return $this->getCertificatesWithDetails(['approval_status' => 'pending']);
    }

    public function getApprovedCertificates()
    {
        return $this->getCertificatesWithDetails(['approval_status' => 'approved']);
    }

    public function getCancelledCertificates()
    {
        return $this->getCertificatesWithDetails(['approval_status' => 'cancelled']);
    }

    public function approveCertificate($certificateId, $approvedBy, $notes = null)
    {
        $data = [
            'approval_status' => 'approved',
            'approved_by' => $approvedBy
        ];
        
        if ($notes !== null) {
            $data['notes'] = $notes;
        }
        
        return $this->update($certificateId, $data);
    }

    public function cancelCertificate($certificateId, $approvedBy, $notes = null)
    {
        $data = [
            'approval_status' => 'cancelled',
            'approved_by' => $approvedBy
        ];
        
        if ($notes !== null) {
            $data['notes'] = $notes;
        }
        
        return $this->update($certificateId, $data);
    }

    public function markReceived($certificateId)
    {
        return $this->update($certificateId, ['receive_status' => 'received']);
    }

    public function getCertificateStats()
    {
        $sql = "SELECT 
                    COUNT(CASE WHEN approval_status = 'pending' THEN 1 END) as pending,
                    COUNT(CASE WHEN approval_status = 'approved' THEN 1 END) as approved,
                    COUNT(CASE WHEN approval_status = 'cancelled' THEN 1 END) as cancelled,
                    COUNT(CASE WHEN receive_status = 'received' THEN 1 END) as received,
                    COUNT(*) as total
                FROM certificates";
        
        return $this->db->fetch($sql);
    }
}