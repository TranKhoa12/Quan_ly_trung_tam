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
            $sql .= " AND (cs.student_name LIKE ? OR cs.phone LIKE ?)";
            $keyword = '%' . $filters['search'] . '%';
            $params[] = $keyword;
            $params[] = $keyword;
        }

        if (!empty($filters['teacher'])) {
            $sql .= " AND cs.teacher_name LIKE ?";
            $params[] = '%' . $filters['teacher'] . '%';
        }

        if (!empty($filters['date_from'])) {
            $sql .= " AND DATE(cs.created_at) >= ?";
            $params[] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $sql .= " AND DATE(cs.created_at) <= ?";
            $params[] = $filters['date_to'];
        }

        // Sorting
        $sort = $filters['sort'] ?? 'newest';
        switch ($sort) {
            case 'oldest':
                $sql .= " ORDER BY cs.created_at ASC";
                break;
            case 'student_asc':
                $sql .= " ORDER BY cs.student_name ASC";
                break;
            case 'student_desc':
                $sql .= " ORDER BY cs.student_name DESC";
                break;
            case 'newest':
            default:
                $sql .= " ORDER BY cs.created_at DESC";
                break;
        }

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
            $sql .= " AND (cs.student_name LIKE ? OR cs.phone LIKE ?)";
            $keyword = '%' . $filters['search'] . '%';
            $params[] = $keyword;
            $params[] = $keyword;
        }

        if (!empty($filters['teacher'])) {
            $sql .= " AND cs.teacher_name LIKE ?";
            $params[] = '%' . $filters['teacher'] . '%';
        }

        if (!empty($filters['date_from'])) {
            $sql .= " AND DATE(cs.created_at) >= ?";
            $params[] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $sql .= " AND DATE(cs.created_at) <= ?";
            $params[] = $filters['date_to'];
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

    public function getDistinctCreators()
    {
        $sql = "SELECT DISTINCT cs.created_by AS id, COALESCE(u.full_name, CONCAT('Người dùng #', cs.created_by)) AS full_name
                FROM completion_slips cs
                LEFT JOIN users u ON cs.created_by = u.id
                WHERE cs.created_by IS NOT NULL
                ORDER BY full_name ASC";

        return $this->db->fetchAll($sql);
    }
}
