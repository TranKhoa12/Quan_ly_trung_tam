<?php
require_once __DIR__ . '/../layouts/main.php';

// Page content
ob_start();
?>

<?= pageHeader(
    'Chi tiết khóa học', 
    'Thông tin chi tiết khóa học: ' . htmlspecialchars($course['course_name']),
    '<div class="d-flex gap-2">
        <a href="/Quan_ly_trung_tam/public/courses" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Quay lại
        </a>
        <a href="/Quan_ly_trung_tam/public/courses/' . $course['id'] . '/edit" class="btn btn-primary">
            <i class="fas fa-edit me-2"></i>Sửa
        </a>
    </div>'
) ?>

<div class="p-3">
    <div class="row">
        <!-- Thông tin chính -->
        <div class="col-md-8">
            <div class="stats-card mb-4">
                <div class="card-body">
                    <h5 class="card-title mb-4">
                        <i class="fas fa-info-circle text-primary me-2"></i>
                        Thông tin khóa học
                    </h5>

                    <div class="row g-4">
                        <div class="col-md-6">
                            <label class="form-label text-muted">Mã khóa học</label>
                            <div class="fw-bold fs-5">
                                <span class="badge bg-primary fs-6"><?= htmlspecialchars($course['course_code']) ?></span>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label text-muted">Tên khóa học</label>
                            <div class="fw-bold fs-5 text-dark">
                                <i class="fas fa-graduation-cap text-primary me-2"></i>
                                <?= htmlspecialchars($course['course_name']) ?>
                            </div>
                        </div>

                        <?php if (!empty($course['description'])): ?>
                        <div class="col-12">
                            <label class="form-label text-muted">Mô tả</label>
                            <div class="fw-bold">
                                <i class="fas fa-align-left text-primary me-2"></i>
                                <?= nl2br(htmlspecialchars($course['description'])) ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <div class="col-md-6">
                            <label class="form-label text-muted">Thời lượng</label>
                            <div class="fw-bold">
                                <i class="fas fa-clock text-primary me-2"></i>
                                <?= htmlspecialchars($course['duration_hours'] ?? 0) ?> giờ
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label text-muted">Học phí</label>
                            <div class="fw-bold text-success fs-5">
                                <i class="fas fa-money-bill-wave me-2"></i>
                                <?= number_format($course['price'] ?? 0) ?> VNĐ
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label text-muted">Ngày bắt đầu</label>
                            <div class="fw-bold">
                                <i class="fas fa-calendar-alt text-primary me-2"></i>
                                <?= date('d/m/Y', strtotime($course['start_date'])) ?>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label text-muted">Ngày kết thúc</label>
                            <div class="fw-bold">
                                <i class="fas fa-calendar-check text-primary me-2"></i>
                                <?= date('d/m/Y', strtotime($course['end_date'])) ?>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label text-muted">Số học viên tối đa</label>
                            <div class="fw-bold">
                                <i class="fas fa-users text-primary me-2"></i>
                                <?= htmlspecialchars($course['max_students']) ?> học viên
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label text-muted">Trạng thái</label>
                            <div class="fw-bold">
                                <?php
                                $statusInfo = match($course['status']) {
                                    'active' => ['text-success', 'fas fa-check-circle', 'Đang hoạt động'],
                                    'inactive' => ['text-danger', 'fas fa-times-circle', 'Ngừng hoạt động'],
                                    'completed' => ['text-info', 'fas fa-flag-checkered', 'Đã hoàn thành'],
                                    default => ['text-muted', 'fas fa-question-circle', 'Không xác định']
                                };
                                ?>
                                <span class="<?= $statusInfo[0] ?> fs-5">
                                    <i class="<?= $statusInfo[1] ?> me-2"></i>
                                    <?= $statusInfo[2] ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-md-4">
            <!-- Thống kê -->
            <div class="stats-card mb-4">
                <div class="card-body">
                    <h5 class="card-title mb-3">
                        <i class="fas fa-chart-bar text-info me-2"></i>
                        Thống kê
                    </h5>

                    <div class="d-grid gap-2">
                        <div class="bg-light p-3 rounded">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted">ID Khóa học</span>
                                <strong class="text-primary">#<?= $course['id'] ?></strong>
                            </div>
                        </div>
                        
                        <div class="bg-light p-3 rounded">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted">Tổng thời gian</span>
                                <strong class="text-success"><?= $course['duration_hours'] ?? 0 ?> giờ</strong>
                            </div>
                        </div>

                        <?php
                        $start = new DateTime($course['start_date']);
                        $end = new DateTime($course['end_date']);
                        $diff = $start->diff($end);
                        $totalDays = $diff->days;
                        ?>
                        <div class="bg-light p-3 rounded">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted">Tổng số ngày</span>
                                <strong class="text-info"><?= $totalDays ?> ngày</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Thông tin hệ thống -->
            <div class="stats-card mb-4">
                <div class="card-body">
                    <h5 class="card-title mb-3">
                        <i class="fas fa-cog text-secondary me-2"></i>
                        Hệ thống
                    </h5>

                    <div class="small">
                        <div class="mb-2">
                            <i class="fas fa-calendar-plus text-primary me-1"></i>
                            Ngày tạo: <strong><?= date('d/m/Y H:i', strtotime($course['created_at'])) ?></strong>
                        </div>
                        <div>
                            <i class="fas fa-edit text-warning me-1"></i>
                            Cập nhật: <strong><?= date('d/m/Y H:i', strtotime($course['updated_at'])) ?></strong>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Hành động -->
            <div class="stats-card">
                <div class="card-body">
                    <h5 class="card-title mb-3">
                        <i class="fas fa-tools text-dark me-2"></i>
                        Thao tác
                    </h5>

                    <div class="d-grid gap-2">
                        <a href="/Quan_ly_trung_tam/public/courses/<?= $course['id'] ?>/edit" class="btn btn-primary">
                            <i class="fas fa-edit me-2"></i>Chỉnh sửa khóa học
                        </a>
                        
                        <?php if ($course['status'] === 'active'): ?>
                            <button class="btn btn-warning" onclick="changeStatus(<?= $course['id'] ?>, 'inactive')">
                                <i class="fas fa-pause me-2"></i>Ngừng hoạt động
                            </button>
                        <?php elseif ($course['status'] === 'inactive'): ?>
                            <button class="btn btn-success" onclick="changeStatus(<?= $course['id'] ?>, 'active')">
                                <i class="fas fa-play me-2"></i>Kích hoạt
                            </button>
                        <?php endif; ?>

                        <button class="btn btn-danger" onclick="deleteCourse(<?= $course['id'] ?>, '<?= htmlspecialchars($course['course_name']) ?>')">
                            <i class="fas fa-trash me-2"></i>Xóa khóa học
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();

// Custom JavaScript
$customJs = '
<script>
function changeStatus(id, newStatus) {
    const statusText = {
        "active": "kích hoạt",
        "inactive": "ngừng hoạt động",
        "completed": "đánh dấu hoàn thành"
    };
    
    if (confirm("Bạn có chắc muốn " + statusText[newStatus] + " khóa học này?")) {
        const form = document.createElement("form");
        form.method = "POST";
        form.action = "/Quan_ly_trung_tam/public/courses/" + id + "/update";
        
        const methodInput = document.createElement("input");
        methodInput.type = "hidden";
        methodInput.name = "_method";
        methodInput.value = "PUT";
        form.appendChild(methodInput);
        
        const statusInput = document.createElement("input");
        statusInput.type = "hidden";
        statusInput.name = "status";
        statusInput.value = newStatus;
        form.appendChild(statusInput);
        
        document.body.appendChild(form);
        form.submit();
    }
}

function deleteCourse(id, name) {
    if (confirm("Bạn có chắc muốn xóa khóa học \\"" + name + "\\"?\\n\\nHành động này không thể hoàn tác!")) {
        if (confirm("Bạn THỰC SỰ chắc chắn muốn xóa?")) {
            const form = document.createElement("form");
            form.method = "POST";
            form.action = "/Quan_ly_trung_tam/public/courses/" + id + "/delete";
            
            const input = document.createElement("input");
            input.type = "hidden";
            input.name = "_method";
            input.value = "DELETE";
            form.appendChild(input);
            
            document.body.appendChild(form);
            form.submit();
        }
    }
}
</script>';

// Render layout
useModernLayout('Chi tiết khóa học', $content);
?>
