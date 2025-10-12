<?php

class Student extends BaseModel
{
    protected $table = 'students';
    protected $fillable = [
        'full_name', 'phone', 'email', 'course_id', 'instructor_id',
        'enrollment_date', 'completion_date', 'tracking_image', 'status'
    ];
    protected $timestamps = false; // Tắt timestamps nếu bảng không có created_at/updated_at

    public function getStudentsWithDetails($conditions = [], $orderBy = 'enrollment_date DESC', $limit = null)
    {
        $sql = "SELECT s.*, c.course_name, u.full_name as instructor_name 
                FROM students s 
                LEFT JOIN courses c ON s.course_id = c.id 
                LEFT JOIN users u ON s.instructor_id = u.id";
        $params = [];
        
        if (!empty($conditions)) {
            $whereClause = [];
            foreach ($conditions as $column => $value) {
                $whereClause[] = "s.$column = ?";
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

    public function getStudentWithDetails($studentId)
    {
        $sql = "SELECT s.*, c.course_name, u.full_name as instructor_name 
                FROM students s 
                LEFT JOIN courses c ON s.course_id = c.id 
                LEFT JOIN users u ON s.instructor_id = u.id 
                WHERE s.id = ?";
        
        return $this->db->fetch($sql, [$studentId]);
    }

    public function getActiveStudents()
    {
        return $this->getStudentsWithDetails(['status' => 'studying']);
    }

    public function getCompletedStudents()
    {
        return $this->getStudentsWithDetails(['status' => 'completed']);
    }

    public function getStudentsByCourse($courseId)
    {
        return $this->getStudentsWithDetails(['course_id' => $courseId]);
    }

    public function getStudentsByInstructor($instructorId)
    {
        return $this->getStudentsWithDetails(['instructor_id' => $instructorId]);
    }

    public function markCompleted($studentId, $completionDate, $trackingImage = null)
    {
        $data = [
            'status' => 'completed',
            'completion_date' => $completionDate
        ];
        
        if ($trackingImage) {
            $data['tracking_image'] = $trackingImage;
        }
        
        return $this->update($studentId, $data);
    }

    public function getStudentStats()
    {
        $sql = "SELECT 
                    COUNT(CASE WHEN status = 'studying' THEN 1 END) as studying,
                    COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed,
                    COUNT(CASE WHEN status = 'dropped' THEN 1 END) as dropped,
                    COUNT(*) as total
                FROM students";
        
        return $this->db->fetch($sql);
    }
}