<?php

class CertificateEditLog extends BaseModel
{
    protected $table = 'certificate_edit_logs';
    protected $fillable = ['certificate_id', 'user_id', 'changes'];
    protected $timestamps = false;

    public function getLogsByCertificate($certificateId)
    {
        $sql = "SELECT l.*, u.full_name AS user_name
                FROM {$this->table} l
                LEFT JOIN users u ON l.user_id = u.id
                WHERE l.certificate_id = ?
                ORDER BY l.created_at DESC";

        return $this->db->fetchAll($sql, [$certificateId]);
    }
}
