<?php

class Staff extends BaseModel {
    protected $table = 'users';
    
    public function getAllStaff($search = '', $department = '', $status = '') {
        $sql = "SELECT * FROM {$this->table} WHERE role = 'staff'";
        $params = [];
        
        if (!empty($search)) {
            $sql .= " AND (full_name LIKE ? OR email LIKE ? OR phone LIKE ?)";
            $searchTerm = "%{$search}%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        if (!empty($department)) {
            $sql .= " AND department = ?";
            $params[] = $department;
        }
        
        if (!empty($status)) {
            $sql .= " AND status = ?";
            $params[] = $status;
        }
        
        $sql .= " ORDER BY created_at DESC";
        
        return $this->db->fetchAll($sql, $params);
    }
    
    public function getStaffById($id) {
        return $this->find($id);
    }
    
    public function createStaff($data) {
        // Hash password
        if (isset($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        
        // Handle date fields - set to NULL if empty
        if (isset($data['hire_date']) && empty($data['hire_date'])) {
            $data['hire_date'] = null;
        }
        
        // Handle numeric fields - set to NULL if empty
        if (isset($data['salary']) && $data['salary'] === '') {
            $data['salary'] = null;
        }
        
        // Handle text fields - set to NULL if empty for optional fields
        $optionalFields = ['phone', 'address', 'department', 'notes'];
        foreach ($optionalFields as $field) {
            if (isset($data[$field]) && $data[$field] === '') {
                $data[$field] = null;
            }
        }
        
        $data['role'] = 'staff';
        $data['created_at'] = date('Y-m-d H:i:s');
        
        return $this->create($data);
    }
    
    public function updateStaff($id, $data) {
        // Hash password if provided
        if (isset($data['password']) && !empty($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        } else {
            unset($data['password']);
        }
        
        // Handle date fields - set to NULL if empty
        if (isset($data['hire_date']) && empty($data['hire_date'])) {
            $data['hire_date'] = null;
        }
        
        // Handle numeric fields - set to NULL if empty
        if (isset($data['salary']) && $data['salary'] === '') {
            $data['salary'] = null;
        }
        
        // Handle text fields - set to NULL if empty for optional fields
        $optionalFields = ['phone', 'address', 'department', 'notes'];
        foreach ($optionalFields as $field) {
            if (isset($data[$field]) && $data[$field] === '') {
                $data[$field] = null;
            }
        }
        
        $data['updated_at'] = date('Y-m-d H:i:s');
        
        return $this->update($id, $data);
    }
    
    public function deleteStaff($id) {
        return $this->delete($id);
    }
    
    public function getDepartments() {
        // Default departments
        $defaultDepartments = [
            'Tư vấn',
            'Giảng dạy',
            'Kế toán',
            'Marketing',
            'Hành chính',
            'IT',
            'Quản lý'
        ];
        
        // Get existing departments from database
        $sql = "SELECT DISTINCT department FROM {$this->table} WHERE role = 'staff' AND department IS NOT NULL ORDER BY department";
        $result = $this->db->fetchAll($sql);
        $existingDepartments = array_column($result, 'department');
        
        // Merge and return unique departments
        $allDepartments = array_unique(array_merge($defaultDepartments, $existingDepartments));
        sort($allDepartments);
        
        return $allDepartments;
    }
    
    public function getStaffStats() {
        $stats = [];
        
        // Total staff
        $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE role = 'staff'";
        $stats['total'] = $this->db->fetch($sql)['total'];
        
        // Active staff
        $sql = "SELECT COUNT(*) as active FROM {$this->table} WHERE role = 'staff' AND status = 'active'";
        $stats['active'] = $this->db->fetch($sql)['active'];
        
        // New staff this month
        $sql = "SELECT COUNT(*) as new_this_month FROM {$this->table} 
                WHERE role = 'staff' AND DATE_FORMAT(hire_date, '%Y-%m') = DATE_FORMAT(NOW(), '%Y-%m')";
        $stats['new_this_month'] = $this->db->fetch($sql)['new_this_month'];
        
        // Staff by department
        $sql = "SELECT department, COUNT(*) as count FROM {$this->table} 
                WHERE role = 'staff' AND status = 'active' 
                GROUP BY department ORDER BY count DESC";
        $stats['by_department'] = $this->db->fetchAll($sql);
        
        return $stats;
    }
    
    public function getRecentStaff($limit = 5) {
        $sql = "SELECT * FROM {$this->table} 
                WHERE role = 'staff' 
                ORDER BY created_at DESC 
                LIMIT ?";
        return $this->db->fetchAll($sql, [$limit]);
    }
}
?>