<?php

class Course extends BaseModel
{
    protected $table = 'courses';
    protected $fillable = [
        'course_name', 'description', 'price', 'status'
    ];
    protected $timestamps = false; // Tắt timestamps cho bảng courses

    public function getActiveCourses()
    {
        return $this->where(['status' => 'active'], 'course_name ASC');
    }

    public function getCourseWithStudentCount($courseId)
    {
        $sql = "SELECT c.*, 
                COUNT(CASE WHEN s.status = 'studying' THEN 1 END) as active_students,
                COUNT(CASE WHEN s.status = 'completed' THEN 1 END) as completed_students
                FROM courses c 
                LEFT JOIN students s ON c.id = s.course_id 
                WHERE c.id = ? 
                GROUP BY c.id";
        
        return $this->db->fetch($sql, [$courseId]);
    }
}