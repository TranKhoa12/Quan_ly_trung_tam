<?php

class Certificate extends BaseModel
{
    protected $table = 'certificates';
    protected $fillable = [
        'student_name', 'username', 'phone', 'subject', 'email', 'receive_status',
        'approval_status', 'notes', 'requested_by', 'approved_by',
        'approved_at', 'received_at', 'received_by', 'available_at_center',
        'available_date', 'available_confirmed_by'
    ];
    protected $timestamps = false; // Tắt timestamps cho bảng certificates

    public function getCertificatesWithDetails($conditions = [], $orderBy = 'id DESC', $limit = null)
    {
        $sql = "SELECT c.*, 
                u1.full_name as requested_by_name,
                u2.full_name as approved_by_name,
                u3.full_name as received_by_name,
                u4.full_name as available_confirmed_by_name 
                FROM certificates c 
                LEFT JOIN users u1 ON c.requested_by = u1.id 
                LEFT JOIN users u2 ON c.approved_by = u2.id 
                LEFT JOIN users u3 ON c.received_by = u3.id 
                LEFT JOIN users u4 ON c.available_confirmed_by = u4.id";
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

    public function findWithRelations($id)
    {
        $sql = "SELECT c.*, 
                u1.full_name as requested_by_name,
                u2.full_name as approved_by_name,
                u3.full_name as received_by_name,
                u4.full_name as available_confirmed_by_name
                FROM certificates c
                LEFT JOIN users u1 ON c.requested_by = u1.id
                LEFT JOIN users u2 ON c.approved_by = u2.id
                LEFT JOIN users u3 ON c.received_by = u3.id
                LEFT JOIN users u4 ON c.available_confirmed_by = u4.id
                WHERE c.id = ?";

        return $this->db->fetch($sql, [$id]);
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
            'approved_by' => $approvedBy,
            'approved_at' => date('Y-m-d H:i:s')
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
            'approved_by' => $approvedBy,
            'approved_at' => date('Y-m-d H:i:s')
        ];
        
        if ($notes !== null) {
            $data['notes'] = $notes;
        }
        
        return $this->update($certificateId, $data);
    }

    public function markReceived($certificateId)
    {
        return $this->update($certificateId, [
            'receive_status' => 'received',
            'received_at' => date('Y-m-d H:i:s')
        ]);
    }

    public function updateApprovalStatus($certificateId, $status, $userId = null)
    {
        $data = ['approval_status' => $status];
        
        if ($status === 'pending') {
            // Chuyển về pending: xóa approved_by và approved_at
            $data['approved_by'] = null;
            $data['approved_at'] = null;
        } else {
            // Approved hoặc cancelled: lưu thời gian và người duyệt
            $data['approved_at'] = date('Y-m-d H:i:s');
            if ($userId) {
                $data['approved_by'] = $userId;
            }
        }
        
        return $this->update($certificateId, $data);
    }

    public function updateReceiveStatus($certificateId, $status, $userId = null)
    {
        $data = ['receive_status' => $status];
        
        if ($status === 'received') {
            $data['received_at'] = date('Y-m-d H:i:s');
            if ($userId) {
                $data['received_by'] = $userId;
            }
        } else {
            // Chuyển về not_received: xóa received_at và received_by
            $data['received_at'] = null;
            $data['received_by'] = null;
        }
        
        return $this->update($certificateId, $data);
    }

    public function updateAvailableStatus($certificateId, $status, $userId = null)
    {
        $data = ['available_at_center' => $status];
        
        if ($status === 'yes') {
            $data['available_date'] = date('Y-m-d H:i:s');
            if ($userId) {
                $data['available_confirmed_by'] = $userId;
            }
        } else {
            // Chuyển về no: xóa available_date và available_confirmed_by
            $data['available_date'] = null;
            $data['available_confirmed_by'] = null;
        }
        
        return $this->update($certificateId, $data);
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