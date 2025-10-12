<?php

class PasswordResetToken extends BaseModel
{
    protected $table = 'password_reset_tokens';
    protected $fillable = [
        'user_id', 'token', 'expires_at', 'used'
    ];

    public function createToken($userId, $token, $expiresAt)
    {
        // Delete any existing unused tokens for this user
        $this->db->query(
            "DELETE FROM {$this->table} WHERE user_id = ? AND used = 0",
            [$userId]
        );

        // Create new token
        return $this->create([
            'user_id' => $userId,
            'token' => $token,
            'expires_at' => $expiresAt,
            'used' => 0
        ]);
    }

    public function validateToken($token)
    {
        $sql = "SELECT prt.*, u.id as user_id, u.username, u.full_name, u.email 
                FROM {$this->table} prt 
                JOIN users u ON prt.user_id = u.id 
                WHERE prt.token = ? AND prt.expires_at > NOW() AND prt.used = 0 AND u.status = 'active'";
        
        return $this->db->fetch($sql, [$token]);
    }

    public function markAsUsed($tokenId)
    {
        return $this->update($tokenId, ['used' => 1]);
    }

    public function cleanExpiredTokens()
    {
        $sql = "DELETE FROM {$this->table} WHERE expires_at < NOW() OR used = 1";
        return $this->db->query($sql);
    }

    public function getUserByToken($token)
    {
        $tokenData = $this->validateToken($token);
        return $tokenData ? $tokenData : null;
    }

    public function generateToken()
    {
        return bin2hex(random_bytes(32));
    }

    public function getExpirationTime($hours = 1)
    {
        return date('Y-m-d H:i:s', strtotime("+{$hours} hours"));
    }
}