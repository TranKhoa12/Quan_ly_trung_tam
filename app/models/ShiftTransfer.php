<?php

require_once __DIR__ . '/../../core/BaseModel.php';

class ShiftTransfer extends BaseModel
{
    protected $table = 'shift_transfers';
    protected $timestamps = false;
    protected $fillable = [
        'shift_registration_id',
        'from_staff_id',
        'to_staff_id',
        'reason',
        'status',
        'admin_id',
        'admin_note',
        'processed_at'
    ];

    /**
     * Tạo yêu cầu chuyển ca mới
     */
    public function create($data)
    {
        return parent::create($data);
    }

    /**
     * Lấy tất cả yêu cầu chuyển ca với thông tin chi tiết
     */
    public function getAll()
    {
        $sql = "SELECT 
                    st.*,
                    sr.shift_date,
                    COALESCE(sr.custom_start, ts.start_time) as custom_start,
                    COALESCE(sr.custom_end, ts.end_time) as custom_end,
                    ts.name as shift_name,
                    from_staff.full_name as from_staff_name,
                    from_staff.email as from_staff_email,
                    to_staff.full_name as to_staff_name,
                    to_staff.email as to_staff_email,
                    admin.full_name as admin_name
                FROM {$this->table} st
                INNER JOIN shift_registrations sr ON st.shift_registration_id = sr.id
                LEFT JOIN teaching_shifts ts ON sr.shift_id = ts.id
                INNER JOIN users from_staff ON st.from_staff_id = from_staff.id
                INNER JOIN users to_staff ON st.to_staff_id = to_staff.id
                LEFT JOIN users admin ON st.admin_id = admin.id
                ORDER BY st.created_at DESC";
        
        return $this->db->query($sql)->fetchAll();
    }

    /**
     * Lấy yêu cầu theo trạng thái
     */
    public function getByStatus($status)
    {
        $sql = "SELECT 
                    st.*,
                    sr.shift_date,
                    COALESCE(sr.custom_start, ts.start_time) as custom_start,
                    COALESCE(sr.custom_end, ts.end_time) as custom_end,
                    ts.name as shift_name,
                    from_staff.full_name as from_staff_name,
                    from_staff.email as from_staff_email,
                    to_staff.full_name as to_staff_name,
                    to_staff.email as to_staff_email
                FROM {$this->table} st
                INNER JOIN shift_registrations sr ON st.shift_registration_id = sr.id
                LEFT JOIN teaching_shifts ts ON sr.shift_id = ts.id
                INNER JOIN users from_staff ON st.from_staff_id = from_staff.id
                INNER JOIN users to_staff ON st.to_staff_id = to_staff.id
                WHERE st.status = :status
                ORDER BY st.created_at DESC";
        
        return $this->db->query($sql, ['status' => $status])->fetchAll();
    }

    /**
     * Lấy yêu cầu của một nhân viên (cả gửi và nhận)
     */
    public function getByStaff($staffId)
    {
        $sql = "SELECT 
                    st.*,
                    sr.shift_date,
                    COALESCE(sr.custom_start, ts.start_time) as custom_start,
                    COALESCE(sr.custom_end, ts.end_time) as custom_end,
                    ts.name as shift_name,
                    from_staff.full_name as from_staff_name,
                    to_staff.full_name as to_staff_name,
                    admin.full_name as admin_name,
                    CASE 
                        WHEN st.from_staff_id = :staff_id_case THEN 'sender'
                        WHEN st.to_staff_id = :staff_id_case2 THEN 'receiver'
                    END as role
                FROM {$this->table} st
                INNER JOIN shift_registrations sr ON st.shift_registration_id = sr.id
                LEFT JOIN teaching_shifts ts ON sr.shift_id = ts.id
                INNER JOIN users from_staff ON st.from_staff_id = from_staff.id
                INNER JOIN users to_staff ON st.to_staff_id = to_staff.id
                LEFT JOIN users admin ON st.admin_id = admin.id
                WHERE st.from_staff_id = :staff_id_where1 OR st.to_staff_id = :staff_id_where2
                ORDER BY st.created_at DESC";
        
        return $this->db->query($sql, [
            'staff_id_case' => $staffId,
            'staff_id_case2' => $staffId,
            'staff_id_where1' => $staffId,
            'staff_id_where2' => $staffId
        ])->fetchAll();
    }

    /**
     * Lấy chi tiết yêu cầu chuyển ca
     */
    public function getDetailById($id)
    {
        $sql = "SELECT 
                    st.*,
                    sr.shift_date,
                    COALESCE(sr.custom_start, ts.start_time) as custom_start,
                    COALESCE(sr.custom_end, ts.end_time) as custom_end,
                    sr.status as registration_status,
                    ts.name as shift_name,
                    from_staff.full_name as from_staff_name,
                    from_staff.email as from_staff_email,
                    to_staff.full_name as to_staff_name,
                    to_staff.email as to_staff_email,
                    admin.full_name as admin_name
                FROM {$this->table} st
                INNER JOIN shift_registrations sr ON st.shift_registration_id = sr.id
                LEFT JOIN teaching_shifts ts ON sr.shift_id = ts.id
                INNER JOIN users from_staff ON st.from_staff_id = from_staff.id
                INNER JOIN users to_staff ON st.to_staff_id = to_staff.id
                LEFT JOIN users admin ON st.admin_id = admin.id
                WHERE st.id = :id";
        
        return $this->db->query($sql, ['id' => $id])->fetch();
    }

    /**
     * Duyệt yêu cầu chuyển ca
     */
    public function approve($id, $adminId, $adminNote = null)
    {
        $sql = "UPDATE {$this->table} 
                SET status = 'approved',
                    admin_id = :admin_id,
                    admin_note = :admin_note,
                    processed_at = NOW()
                WHERE id = :id AND status = 'pending'";
        
        return $this->db->execute($sql, [
            'id' => $id,
            'admin_id' => $adminId,
            'admin_note' => $adminNote
        ]);
    }

    /**
     * Từ chối yêu cầu chuyển ca
     */
    public function reject($id, $adminId, $adminNote)
    {
        $sql = "UPDATE {$this->table} 
                SET status = 'rejected',
                    admin_id = :admin_id,
                    admin_note = :admin_note,
                    processed_at = NOW()
                WHERE id = :id AND status = 'pending'";
        
        return $this->db->execute($sql, [
            'id' => $id,
            'admin_id' => $adminId,
            'admin_note' => $adminNote
        ]);
    }

    /**
     * Kiểm tra ca dạy đã có yêu cầu chuyển đang chờ duyệt chưa
     */
    public function hasPendingTransfer($shiftRegistrationId)
    {
        $sql = "SELECT COUNT(*) as count 
                FROM {$this->table} 
                WHERE shift_registration_id = :shift_registration_id 
                AND status = 'pending'";
        
        $result = $this->db->query($sql, ['shift_registration_id' => $shiftRegistrationId])->fetch();
        return $result['count'] > 0;
    }
}
