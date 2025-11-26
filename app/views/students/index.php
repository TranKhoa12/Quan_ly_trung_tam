<?php
require_once __DIR__ . '/../layouts/main.php';

// Page content
ob_start();
?>

<?= pageHeader(
    'Quản lý học viên', 
    'Theo dõi tiến độ học tập và quản lý thông tin học viên', 
    '<a href="/Quan_ly_trung_tam/public/students/create" class="btn btn-primary">
        <i class="fas fa-user-plus me-2"></i>Thêm học viên mới
    </a>'
) ?>

<div class="p-3">
    <!-- Statistics -->
    <div class="row g-3 mb-4">
        <div class="col-md-3 col-sm-6">
            <?= statsCard(
                'fas fa-users', 
                'Tổng học viên', 
                count($students), 
                'Tổng số học viên trong hệ thống',
                'primary'
            ) ?>
        </div>
        
        <div class="col-md-3 col-sm-6">
            <?php 
            $studying = array_filter($students, function($s) { return $s['status'] === 'studying'; });
            ?>
            <?= statsCard(
                'fas fa-book-open', 
                'Đang học', 
                count($studying), 
                'Học viên đang theo học',
                'info'
            ) ?>
        </div>
        
        <div class="col-md-3 col-sm-6">
            <?php 
            $completed = array_filter($students, function($s) { return $s['status'] === 'completed'; });
            ?>
            <?= statsCard(
                'fas fa-check-circle', 
                'Đã hoàn thành', 
                count($completed), 
                'Học viên đã hoàn thành khóa học',
                'success'
            ) ?>
        </div>
        
        <div class="col-md-3 col-sm-6">
            <?php 
            $dropped = array_filter($students, function($s) { return $s['status'] === 'dropped'; });
            ?>
            <?= statsCard(
                'fas fa-times-circle', 
                'Đã nghỉ', 
                count($dropped), 
                'Học viên đã nghỉ học',
                'danger'
            ) ?>
        </div>
    </div>

    <!-- Filter and Search -->
    <div class="stats-card mb-4">
        <div class="card-body">
            <form method="GET" action="/Quan_ly_trung_tam/public/students">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label">
                            <i class="fas fa-search text-primary"></i> Tìm kiếm
                        </label>
                        <input type="text" class="form-control" name="search" 
                               placeholder="Tên, SĐT, Email..." 
                               value="<?= $_GET['search'] ?? '' ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">
                            <i class="fas fa-book text-primary"></i> Khóa học
                        </label>
                        <select class="form-select" name="course_id">
                            <option value="">Tất cả khóa học</option>
                            <?php if (!empty($courses)): ?>
                                <?php foreach ($courses as $course): ?>
                                    <option value="<?= $course['id'] ?>" 
                                            <?= (isset($_GET['course_id']) && $_GET['course_id'] == $course['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($course['course_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">
                            <i class="fas fa-flag text-primary"></i> Trạng thái
                        </label>
                        <select class="form-select" name="status">
                            <option value="">Tất cả trạng thái</option>
                            <option value="studying" <?= (isset($_GET['status']) && $_GET['status'] === 'studying') ? 'selected' : '' ?>>Đang học</option>
                            <option value="completed" <?= (isset($_GET['status']) && $_GET['status'] === 'completed') ? 'selected' : '' ?>>Đã hoàn thành</option>
                            <option value="dropped" <?= (isset($_GET['status']) && $_GET['status'] === 'dropped') ? 'selected' : '' ?>>Đã nghỉ</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search me-2"></i>Tìm kiếm
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Students Table -->
    <div class="stats-card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="card-title mb-0">
                    <i class="fas fa-list text-primary me-2"></i>
                    Danh sách học viên
                </h6>
                <span class="badge bg-primary"><?= count($students) ?> học viên</span>
            </div>
            
            <?php if (!empty($students)): ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Học viên</th>
                                <th>Liên hệ</th>
                                <th>Khóa học</th>
                                <th>Giảng viên</th>
                                <th>Ngày nhập học</th>
                                <th>Trạng thái</th>
                                <th>Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($students as $student): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-circle bg-primary text-white me-3 d-flex align-items-center justify-content-center" 
                                                 style="width: 40px; height: 40px; border-radius: 50%; font-weight: 600;">
                                                <?= strtoupper(substr($student['full_name'], 0, 1)) ?>
                                            </div>
                                            <div>
                                                <div class="fw-semibold"><?= htmlspecialchars($student['full_name']) ?></div>
                                                <small class="text-muted">ID: #<?= $student['id'] ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <?php if (!empty($student['phone'])): ?>
                                                <div><i class="fas fa-phone text-muted me-1"></i> <?= htmlspecialchars($student['phone']) ?></div>
                                            <?php endif; ?>
                                            <?php if (!empty($student['email'])): ?>
                                                <div><i class="fas fa-envelope text-muted me-1"></i> <?= htmlspecialchars($student['email']) ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if (!empty($student['course_name'])): ?>
                                            <span class="badge bg-info"><?= htmlspecialchars($student['course_name']) ?></span>
                                        <?php else: ?>
                                            <span class="text-muted">Chưa có</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($student['instructor_name'])): ?>
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-chalkboard-teacher text-muted me-2"></i>
                                                <?= htmlspecialchars($student['instructor_name']) ?>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-muted">Chưa phân công</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($student['enrollment_date'])): ?>
                                            <div>
                                                <span class="fw-semibold"><?= date('d/m/Y', strtotime($student['enrollment_date'])) ?></span>
                                                <br><small class="text-muted"><?= date('H:i', strtotime($student['enrollment_date'])) ?></small>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-muted">Chưa có</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php
                                        $statusColors = [
                                            'studying' => 'info',
                                            'completed' => 'success', 
                                            'dropped' => 'danger'
                                        ];
                                        $statusLabels = [
                                            'studying' => 'Đang học',
                                            'completed' => 'Đã hoàn thành',
                                            'dropped' => 'Đã nghỉ'
                                        ];
                                        $color = $statusColors[$student['status']] ?? 'secondary';
                                        $label = $statusLabels[$student['status']] ?? $student['status'];
                                        ?>
                                        <span class="badge bg-<?= $color ?>"><?= $label ?></span>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="/Quan_ly_trung_tam/public/students/<?= $student['id'] ?>" 
                                               class="btn btn-sm btn-outline-primary" title="Xem chi tiết">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="/Quan_ly_trung_tam/public/students/<?= $student['id'] ?>/edit" 
                                               class="btn btn-sm btn-outline-warning" title="Chỉnh sửa">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-outline-danger" 
                                                    onclick="deleteStudent(<?= $student['id'] ?>)" title="Xóa">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-graduation-cap text-muted fa-4x mb-3"></i>
                    <h5 class="text-muted">Chưa có học viên nào</h5>
                    <p class="text-muted">Bắt đầu thêm học viên đầu tiên vào hệ thống</p>
                    <a href="/Quan_ly_trung_tam/public/students/create" class="btn btn-primary">
                        <i class="fas fa-user-plus me-2"></i>Thêm học viên đầu tiên
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();

// Custom JavaScript
$customJs = '
function deleteStudent(studentId) {
    if (confirm("Bạn có chắc chắn muốn xóa học viên này?")) {
        // Create form and submit
        const form = document.createElement("form");
        form.method = "POST";
        form.action = "/Quan_ly_trung_tam/public/students/" + studentId + "/delete";
        
        document.body.appendChild(form);
        form.submit();
    }
}
';

// Render layout
useModernLayout('Quản lý học viên', $content);
?>