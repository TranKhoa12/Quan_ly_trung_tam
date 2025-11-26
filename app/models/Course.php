<?php

class CourseModel extends BaseModel
{
    public function __construct()
    {
        parent::__construct();
        $this->table = 'courses';
    }

    /**
     * Lấy danh sách khóa học đang hoạt động
     * (Sử dụng cho StudentController)
     */
    public function getActiveCourses()
    {
        try {
            $sql = "SELECT id, course_code, course_name, description, 
                           duration_hours, price, start_date, end_date, status
                    FROM {$this->table}
                    WHERE status = 'active'
                    ORDER BY course_name ASC";
            
            return $this->db->fetchAll($sql);
        } catch (PDOException $e) {
            error_log("Error getting active courses: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Lấy tất cả khóa học với filter và phân trang
     */
    public function getAllCourses($search = '', $status = '', $page = 1, $perPage = 10)
    {
        try {
            $offset = ($page - 1) * $perPage;
            
            $sql = "SELECT * FROM {$this->table} WHERE 1=1";
            $params = [];

            if (!empty($search)) {
                $sql .= " AND (course_code LIKE ? OR course_name LIKE ? OR description LIKE ?)";
                $searchParam = "%{$search}%";
                $params[] = $searchParam;
                $params[] = $searchParam;
                $params[] = $searchParam;
            }

            if (!empty($status)) {
                $sql .= " AND status = ?";
                $params[] = $status;
            }

            $sql .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";
            $params[] = (int)$perPage;
            $params[] = (int)$offset;

            return $this->db->fetchAll($sql, $params);
        } catch (PDOException $e) {
            error_log("Error getting all courses: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Đếm tổng số khóa học với filter
     */
    public function getTotalCourses($search = '', $status = '')
    {
        try {
            $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE 1=1";
            $params = [];

            if (!empty($search)) {
                $sql .= " AND (course_code LIKE ? OR course_name LIKE ? OR description LIKE ?)";
                $searchParam = "%{$search}%";
                $params[] = $searchParam;
                $params[] = $searchParam;
                $params[] = $searchParam;
            }

            if (!empty($status)) {
                $sql .= " AND status = ?";
                $params[] = $status;
            }

            $result = $this->db->fetch($sql, $params);
            return $result['total'] ?? 0;
        } catch (PDOException $e) {
            error_log("Error getting total courses: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Lấy thống kê khóa học
     */
    public function getCourseStats()
    {
        try {
            $stats = [
                'total' => 0,
                'active' => 0,
                'inactive' => 0,
                'completed' => 0
            ];

            // Total
            $sql = "SELECT COUNT(*) as count FROM {$this->table}";
            $result = $this->db->fetch($sql);
            $stats['total'] = $result['count'] ?? 0;

            // Active
            $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE status = 'active'";
            $result = $this->db->fetch($sql);
            $stats['active'] = $result['count'] ?? 0;

            // Inactive
            $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE status = 'inactive'";
            $result = $this->db->fetch($sql);
            $stats['inactive'] = $result['count'] ?? 0;

            // Completed
            $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE status = 'completed'";
            $result = $this->db->fetch($sql);
            $stats['completed'] = $result['count'] ?? 0;

            return $stats;
        } catch (PDOException $e) {
            error_log("Error getting course stats: " . $e->getMessage());
            return [
                'total' => 0,
                'active' => 0,
                'inactive' => 0,
                'completed' => 0
            ];
        }
    }

    /**
     * Lấy khóa học theo ID
     */
    public function getCourseById($id)
    {
        try {
            $sql = "SELECT * FROM {$this->table} WHERE id = ?";
            return $this->db->fetch($sql, [$id]);
        } catch (PDOException $e) {
            error_log("Error getting course by ID: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Lấy khóa học theo mã khóa học
     */
    public function getCourseByCode($courseCode)
    {
        try {
            $sql = "SELECT * FROM {$this->table} WHERE course_code = ?";
            return $this->db->fetch($sql, [$courseCode]);
        } catch (PDOException $e) {
            error_log("Error getting course by code: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Tạo khóa học mới
     */
    public function createCourse($data)
    {
        try {
            $sql = "INSERT INTO {$this->table} 
                    (course_code, course_name, description, duration_hours, price, 
                     start_date, end_date, max_students, status, created_at, updated_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
            
            $params = [
                $data['course_code'],
                $data['course_name'],
                !empty($data['description']) ? $data['description'] : null,
                !empty($data['duration']) ? $data['duration'] : null,
                !empty($data['fee']) ? $data['fee'] : 0,
                !empty($data['start_date']) ? $data['start_date'] : null,
                !empty($data['end_date']) ? $data['end_date'] : null,
                !empty($data['max_students']) ? $data['max_students'] : null,
                $data['status']
            ];

            return $this->db->query($sql, $params);
        } catch (PDOException $e) {
            error_log("Error creating course: " . $e->getMessage());
            throw new Exception("Không thể tạo khóa học: " . $e->getMessage());
        }
    }

    /**
     * Cập nhật khóa học
     */
    public function updateCourse($id, $data)
    {
        try {
            $sql = "UPDATE {$this->table} 
                    SET course_code = ?, course_name = ?, description = ?, 
                        duration_hours = ?, price = ?, start_date = ?, end_date = ?, 
                        max_students = ?, status = ?, updated_at = NOW() 
                    WHERE id = ?";
            
            $params = [
                $data['course_code'],
                $data['course_name'],
                !empty($data['description']) ? $data['description'] : null,
                !empty($data['duration']) ? $data['duration'] : null,
                !empty($data['fee']) ? $data['fee'] : 0,
                !empty($data['start_date']) ? $data['start_date'] : null,
                !empty($data['end_date']) ? $data['end_date'] : null,
                !empty($data['max_students']) ? $data['max_students'] : null,
                $data['status'],
                $id
            ];

            return $this->db->query($sql, $params);
        } catch (PDOException $e) {
            error_log("Error updating course: " . $e->getMessage());
            throw new Exception("Không thể cập nhật khóa học: " . $e->getMessage());
        }
    }

    /**
     * Xóa khóa học
     */
    public function deleteCourse($id)
    {
        try {
            $sql = "DELETE FROM {$this->table} WHERE id = ?";
            return $this->db->query($sql, [$id]);
        } catch (PDOException $e) {
            error_log("Error deleting course: " . $e->getMessage());
            throw new Exception("Không thể xóa khóa học: " . $e->getMessage());
        }
    }
}
