<?php

require_once __DIR__ . '/../../core/BaseModel.php';

class ShiftTransferLog extends BaseModel
{
    protected $table = 'shift_transfer_logs';
    protected $timestamps = false;
    protected $fillable = [
        'shift_transfer_id',
        'action',
        'actor_id',
        'notes'
    ];

    /**
     * Tạo log mới
     */
    public function createLog($shiftTransferId, $action, $actorId, $notes = null)
    {
        return $this->create([
            'shift_transfer_id' => $shiftTransferId,
            'action' => $action,
            'actor_id' => $actorId,
            'notes' => $notes
        ]);
    }

    /**
     * Lấy tất cả log của một yêu cầu chuyển ca
     */
    public function getByTransferId($shiftTransferId)
    {
        $sql = "SELECT 
                    stl.*,
                    u.full_name as actor_name,
                    u.email as actor_email
                FROM {$this->table} stl
                INNER JOIN users u ON stl.actor_id = u.id
                WHERE stl.shift_transfer_id = :shift_transfer_id
                ORDER BY stl.created_at ASC";
        
        return $this->db->query($sql, ['shift_transfer_id' => $shiftTransferId])->fetchAll();
    }

    /**
     * Lấy tất cả log với thông tin chi tiết
     */
    public function getAllWithDetails($limit = 100)
    {
        $sql = "SELECT 
                    stl.*,
                    u.full_name as actor_name,
                    st.from_staff_id,
                    st.to_staff_id,
                    from_staff.full_name as from_staff_name,
                    to_staff.full_name as to_staff_name
                FROM {$this->table} stl
                INNER JOIN users u ON stl.actor_id = u.id
                INNER JOIN shift_transfers st ON stl.shift_transfer_id = st.id
                INNER JOIN users from_staff ON st.from_staff_id = from_staff.id
                INNER JOIN users to_staff ON st.to_staff_id = to_staff.id
                ORDER BY stl.created_at DESC
                LIMIT :limit";
        
        return $this->db->query($sql, ['limit' => $limit])->fetchAll();
    }
}
