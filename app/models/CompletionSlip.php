<?php

class CompletionSlip extends BaseModel
{
    protected $table = 'completion_slips';
    protected $fillable = [
        'student_name',
        'phone',
        'course_id',
        'teacher_name',
        'notes',
        'image_files',
        'created_by',
        'updated_by'
    ];
    protected $timestamps = false;

    public function __construct()
    {
        parent::__construct();
    }

    public function getAllWithRelations($filters = [], $limit = null, $offset = null)
    {
        $sql = "SELECT cs.*, c.course_name, u1.full_name AS created_by_name, u2.full_name AS updated_by_name
                FROM completion_slips cs
                LEFT JOIN courses c ON cs.course_id = c.id
                LEFT JOIN users u1 ON cs.created_by = u1.id
                LEFT JOIN users u2 ON cs.updated_by = u2.id
                WHERE 1 = 1";
        $params = [];

        if (!empty($filters['course_id'])) {
            $sql .= " AND cs.course_id = ?";
            $params[] = $filters['course_id'];
        }

        if (!empty($filters['created_by'])) {
            $sql .= " AND cs.created_by = ?";
            $params[] = $filters['created_by'];
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (cs.student_name LIKE ? OR cs.phone LIKE ? OR cs.teacher_name LIKE ?)";
            $keyword = '%' . $filters['search'] . '%';
            $params[] = $keyword;
            $params[] = $keyword;
            $params[] = $keyword;
        }

        $sql .= " ORDER BY cs.created_at DESC";

        if ($limit !== null) {
            $sql .= " LIMIT ?";
            $params[] = (int)$limit;

            if ($offset !== null) {
                $sql .= " OFFSET ?";
                $params[] = (int)$offset;
            }
        }

        return $this->db->fetchAll($sql, $params);
    }

    public function countWithFilters($filters = [])
    {
        $sql = "SELECT COUNT(*) AS total FROM completion_slips cs WHERE 1 = 1";
        $params = [];

        if (!empty($filters['course_id'])) {
            $sql .= " AND cs.course_id = ?";
            $params[] = $filters['course_id'];
        }

        if (!empty($filters['created_by'])) {
            $sql .= " AND cs.created_by = ?";
            $params[] = $filters['created_by'];
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (cs.student_name LIKE ? OR cs.phone LIKE ? OR cs.teacher_name LIKE ?)";
            $keyword = '%' . $filters['search'] . '%';
            $params[] = $keyword;
            $params[] = $keyword;
            $params[] = $keyword;
        }

        $result = $this->db->fetch($sql, $params);
        return $result['total'] ?? 0;
    }

    public function findWithRelations($id)
    {
        $sql = "SELECT cs.*, c.course_name, u1.full_name AS created_by_name, u2.full_name AS updated_by_name
                FROM completion_slips cs
                LEFT JOIN courses c ON cs.course_id = c.id
                LEFT JOIN users u1 ON cs.created_by = u1.id
                LEFT JOIN users u2 ON cs.updated_by = u2.id
                WHERE cs.id = ?";

        return $this->db->fetch($sql, [$id]);
    }
}
