<?php

class User extends BaseModel
{
    protected $table = 'users';
    protected $fillable = [
        'username', 'password', 'full_name', 'email', 'phone', 'role', 'status'
    ];
    protected $timestamps = false; // Tắt timestamps cho bảng users

    public function authenticate($username, $password)
    {
        $user = $this->findBy('username', $username);
        
        if ($user && $user['status'] === 'active' && password_verify($password, $user['password'])) {
            return $user;
        }
        
        return false;
    }

    public function createUser($data)
    {
        if (isset($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        
        return $this->create($data);
    }

    public function updatePassword($id, $newPassword)
    {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        return $this->update($id, ['password' => $hashedPassword]);
    }

    public function getStaffList()
    {
        return $this->where(['status' => 'active'], 'full_name ASC');
    }

    public function getAdminList()
    {
        return $this->where(['role' => 'admin', 'status' => 'active'], 'full_name ASC');
    }
}