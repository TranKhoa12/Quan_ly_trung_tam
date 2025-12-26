<?php
require_once __DIR__ . '/../layouts/main.php';

$scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
$scriptDir = str_replace('\\', '/', dirname($scriptName));
$basePath = rtrim($scriptDir, '/');
if ($basePath === '' || $basePath === '.') {
    $basePath = '';
}

ob_start();

$staffList = $staffList ?? [];
$creators = $creators ?? [];

$headerButtons = '<a href="' . $basePath . '/completion-slips/create" class="btn btn-primary">
    <i class="fas fa-plus me-2"></i>Nhập phiếu mới
</a>';

echo pageHeader(
    'Phiếu hoàn thành học viên',
    'Theo dõi tiến trình xuất phiếu hoàn thành và lưu hồ sơ hình ảnh',
    $headerButtons
);
?>

<div class="p-3">
    <div class="stats-card mb-4">
        <div class="card-body">
            <form action="<?= $basePath ?>/completion-slips" method="GET" id="completionSlipsFilterForm">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">
                            <i class="fas fa-search text-primary me-1"></i>Tìm kiếm
                        </label>
                        <input type="text" class="form-control" name="search" placeholder="Tên học viên..."
                               value="<?= htmlspecialchars($filters['search'] ?? '') ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">
                            <i class="fas fa-book text-primary me-1"></i>Khóa học
                        </label>
                        <?php
                        $selectedCourseName = '';
                        if (!empty($filters['course_id'])) {
                            foreach ($courses as $course) {
                                if ((int)$course['id'] === (int)$filters['course_id']) {
                                    $selectedCourseName = $course['course_name'] ?? ($course['course_code'] ?? '');
                                    break;
                                }
                            }
                        }
                        ?>
                        <div class="course-combo-wrapper position-relative" id="filterCourseCombo">
                            <input type="text" class="form-control course-combo-input" id="filter_course_search" placeholder="-- Tất cả khóa học --" autocomplete="off" value="<?= htmlspecialchars($selectedCourseName) ?>">
                            <input type="hidden" name="course_id" id="filter_course_id" value="<?= htmlspecialchars($filters['course_id'] ?? '') ?>">
                            <div class="course-dropdown position-absolute w-100" style="display:none;"></div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">
                            <i class="fas fa-chalkboard-teacher text-primary me-1"></i>Giáo viên
                        </label>
                        <?php
                        $teacherValue = $filters['teacher'] ?? '';
                        $teacherFromList = false;
                        if (!empty($teacherValue) && !empty($staffList)) {
                            foreach ($staffList as $staff) {
                                if ($staff['full_name'] === $teacherValue) {
                                    $teacherFromList = true;
                                    break;
                                }
                            }
                        }
                        ?>
                        <select class="form-select" id="filter_teacher_select">
                            <option value="">-- Tất cả giáo viên --</option>
                            <?php if (!empty($staffList)): ?>
                                <?php foreach ($staffList as $staff): ?>
                                    <option value="<?= htmlspecialchars($staff['full_name']) ?>" <?= ($teacherFromList && $staff['full_name'] === $teacherValue) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($staff['full_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            <option value="__custom__" <?= (!$teacherFromList && !empty($teacherValue)) ? 'selected' : '' ?>>+ Giáo viên khác</option>
                        </select>
                        <input type="text" class="form-control mt-2" id="filter_teacher_input" name="teacher" placeholder="Tên giáo viên..." value="<?= htmlspecialchars($teacherValue) ?>" style="display: <?= (!$teacherFromList && !empty($teacherValue)) ? 'block' : 'none' ?>;">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">
                            <i class="fas fa-sort text-primary me-1"></i>Sắp xếp
                        </label>
                        <select class="form-select" name="sort">
                            <option value="newest" <?= ($filters['sort'] ?? 'newest') === 'newest' ? 'selected' : '' ?>>Mới nhất</option>
                            <option value="oldest" <?= ($filters['sort'] ?? '') === 'oldest' ? 'selected' : '' ?>>Cũ nhất</option>
                            <option value="student_asc" <?= ($filters['sort'] ?? '') === 'student_asc' ? 'selected' : '' ?>>Tên HV A-Z</option>
                            <option value="student_desc" <?= ($filters['sort'] ?? '') === 'student_desc' ? 'selected' : '' ?>>Tên HV Z-A</option>
                        </select>
                    </div>
                </div>
                
                <div class="row g-3 mt-1">
                    <div class="col-md-3">
                        <label class="form-label">
                            <i class="fas fa-calendar text-primary me-1"></i>Từ ngày
                        </label>
                        <input type="date" class="form-control" name="date_from"
                               value="<?= htmlspecialchars($filters['date_from'] ?? '') ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">
                            <i class="fas fa-calendar text-primary me-1"></i>Đến ngày
                        </label>
                        <input type="date" class="form-control" name="date_to"
                               value="<?= htmlspecialchars($filters['date_to'] ?? '') ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">
                            <i class="fas fa-user text-primary me-1"></i>Người tạo
                        </label>
                        <select class="form-select" name="created_by">
                            <option value="">-- Tất cả --</option>
                            <?php if (!empty($creators)): ?>
                                <?php foreach ($creators as $staff): ?>
                                    <option value="<?= $staff['id'] ?>" <?= (!empty($filters['created_by']) && (int)$filters['created_by'] === (int)$staff['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($staff['full_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label d-block">&nbsp;</label>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary flex-fill">
                                <i class="fas fa-filter me-1"></i>Lọc
                            </button>
                            <?php if (($userRole ?? 'staff') === 'admin'): ?>
                                <button type="submit" class="btn btn-outline-success flex-fill"
                                        formaction="<?= $basePath ?>/completion-slips/export/pdf">
                                    <i class="fas fa-file-pdf me-1"></i>Xuất PDF
                                </button>
                            <?php endif; ?>
                            <button class="btn btn-outline-secondary flex-fill" type="button" onclick="window.location.href='<?= $basePath ?>/completion-slips'">
                                <i class="fas fa-undo me-1"></i>Đặt lại
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <?php if (!empty($slips)): ?>
        <div class="stats-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="mb-0">
                        <i class="fas fa-list text-primary me-2"></i>Danh sách phiếu
                    </h6>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-primary">Tổng <?= $totalRecords ?> phiếu</span>
                        <?php if ($userRole === 'admin'): ?>
                        <button type="button" class="btn btn-sm btn-danger" id="deleteSelectedBtn" style="display: none;" onclick="deleteSelected()">
                            <i class="fas fa-trash me-1"></i>Xóa đã chọn (<span id="selectedCount">0</span>)
                        </button>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <?php if ($userRole === 'admin'): ?>
                                <th width="50">
                                    <input type="checkbox" class="form-check-input" id="selectAll" onchange="toggleSelectAll(this)">
                                </th>
                                <?php endif; ?>
                                <th>Học viên</th>
                                <th>Khóa học</th>
                                <th>Giáo viên</th>
                                <th>Ảnh phiếu</th>
                                <th>Ngày tạo</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($slips as $slip): ?>
                                <?php $images = !empty($slip['image_files']) ? (json_decode($slip['image_files'], true) ?: []) : []; ?>
                                <tr>
                                    <?php if ($userRole === 'admin'): ?>
                                    <td>
                                        <input type="checkbox" class="form-check-input slip-checkbox" value="<?= $slip['id'] ?>" onchange="updateSelectedCount()">
                                    </td>
                                    <?php endif; ?>
                                    <td>
                                        <div class="fw-semibold"><?= htmlspecialchars($slip['student_name']) ?></div>
                                        <small class="text-muted">Tạo bởi: <?= htmlspecialchars($slip['created_by_name'] ?? 'Không rõ') ?></small>
                                    </td>
                                    <td><?= htmlspecialchars($slip['course_name'] ?? 'Chưa xác định') ?></td>
                                    <td><?= htmlspecialchars($slip['teacher_name'] ?? '-') ?></td>
                                    <td>
                                        <?php if (!empty($images)): 
                                            $imageCount = count($images);
                                            $imageData = json_encode(array_map(function($img) {
                                                return getFileUrl($img);
                                            }, $images));
                                        ?>
                                            <button type="button" 
                                                    class="btn btn-sm btn-outline-info position-relative" 
                                                    onclick='showMultipleImages(<?= $imageData ?>)'
                                                    title="Xem <?= $imageCount ?> ảnh">
                                                <i class="fas fa-images"></i>
                                                <?php if ($imageCount > 1): ?>
                                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" 
                                                          style="font-size: 0.65rem; padding: 0.2rem 0.4rem;">
                                                        <?= $imageCount ?>
                                                    </span>
                                                <?php endif; ?>
                                            </button>
                                        <?php else: ?>
                                            <span class="text-muted">Chưa có</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?= !empty($slip['created_at']) ? date('d/m/Y H:i', strtotime($slip['created_at'])) : '-' ?>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <?php 
                                            $canEdit = ($userRole === 'admin') || ((int)$slip['created_by'] === (int)($userId ?? 0));
                                            $canDelete = ($userRole === 'admin');
                                            ?>
                                            <?php if ($canEdit): ?>
                                                <a href="<?= $basePath ?>/completion-slips/<?= $slip['id'] ?>/edit" 
                                                   class="btn btn-sm btn-outline-warning" 
                                                   title="Chỉnh sửa">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            <?php endif; ?>
                                            <?php if ($canDelete): ?>
                                                <button type="button" 
                                                        class="btn btn-sm btn-outline-danger" 
                                                        onclick="confirmDelete(<?= $slip['id'] ?>)"
                                                        title="Xóa">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            <?php endif; ?>
                                            <?php if (!$canEdit && !$canDelete): ?>
                                                <span class="text-muted small">Không có quyền</span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <?php if ($totalPages > 1): ?>
                    <nav class="mt-3">
                        <ul class="pagination justify-content-center">
                            <?php
                            $startPage = max(1, $currentPage - 2);
                            $endPage = min($totalPages, $currentPage + 2);
                            $queryString = http_build_query(array_filter([
                                'search' => $filters['search'] ?? '',
                                'course_id' => $filters['course_id'] ?? ''
                            ]));
                            $queryPrefix = $queryString ? '&' . $queryString : '';
                            ?>
                            
                            <!-- Previous Button -->
                            <li class="page-item <?= $currentPage <= 1 ? 'disabled' : '' ?>">
                                <a class="page-link" href="?page=<?= $currentPage - 1 ?><?= $queryPrefix ?>">
                                    <i class="fas fa-chevron-left"></i> Trước
                                </a>
                            </li>
                            
                            <!-- First page -->
                            <?php if ($startPage > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=1<?= $queryPrefix ?>">1</a>
                                </li>
                                <?php if ($startPage > 2): ?>
                                    <li class="page-item disabled"><span class="page-link">...</span></li>
                                <?php endif; ?>
                            <?php endif; ?>
                            
                            <!-- Page numbers -->
                            <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                                <li class="page-item <?= $i === $currentPage ? 'active' : '' ?>">
                                    <a class="page-link" href="?page=<?= $i ?><?= $queryPrefix ?>"><?= $i ?></a>
                                </li>
                            <?php endfor; ?>
                            
                            <!-- Last page -->
                            <?php if ($endPage < $totalPages): ?>
                                <?php if ($endPage < $totalPages - 1): ?>
                                    <li class="page-item disabled"><span class="page-link">...</span></li>
                                <?php endif; ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?= $totalPages ?><?= $queryPrefix ?>"><?= $totalPages ?></a>
                                </li>
                            <?php endif; ?>
                            
                            <!-- Next Button -->
                            <li class="page-item <?= $currentPage >= $totalPages ? 'disabled' : '' ?>">
                                <a class="page-link" href="?page=<?= $currentPage + 1 ?><?= $queryPrefix ?>">
                                    Sau <i class="fas fa-chevron-right"></i>
                                </a>
                            </li>
                        </ul>
                        
                        <!-- Pagination Info -->
                        <div class="text-center text-muted mt-2">
                            <small>
                                Hiển thị <?= min(($currentPage - 1) * $perPage + 1, $totalRecords) ?> - 
                                <?= min($currentPage * $perPage, $totalRecords) ?> 
                                trong tổng số <?= $totalRecords ?> phiếu
                            </small>
                        </div>
                    </nav>
                <?php endif; ?>
            </div>
        </div>
    <?php else: ?>
        <div class="stats-card text-center p-5">
            <i class="fas fa-inbox fs-1 text-muted mb-3"></i>
            <p class="text-muted mb-3">Chưa có phiếu nào. Hãy tạo phiếu đầu tiên ngay bây giờ.</p>
            <a href="<?= $basePath ?>/completion-slips/create" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Tạo phiếu
            </a>
        </div>
    <?php endif; ?>
</div>

<!-- Multiple Images Preview Modal -->
<div class="modal fade" id="imageModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-images text-primary me-2"></i>Ảnh phiếu hoàn thành
                    <span id="imageCounter" class="badge bg-primary ms-2"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center p-0 position-relative" style="min-height: 400px;">
                <!-- Navigation Buttons -->
                <button type="button" id="prevImageBtn" 
                        class="btn btn-dark position-absolute top-50 start-0 translate-middle-y ms-3" 
                        style="z-index: 10; opacity: 0.7;"
                        onclick="navigateImage(-1)">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <button type="button" id="nextImageBtn" 
                        class="btn btn-dark position-absolute top-50 end-0 translate-middle-y me-3" 
                        style="z-index: 10; opacity: 0.7;"
                        onclick="navigateImage(1)">
                    <i class="fas fa-chevron-right"></i>
                </button>
                
                <!-- Main Image -->
                <img id="modalImage" src="" alt="Completion Slip Image" 
                     class="img-fluid" style="max-height: 70vh; width: auto;">
                
                <!-- Thumbnails -->
                <div id="thumbnailsContainer" class="d-flex justify-content-center gap-2 p-3 bg-light" 
                     style="overflow-x: auto;"></div>
            </div>
            <div class="modal-footer">
                <a id="downloadImageBtn" href="" download class="btn btn-primary">
                    <i class="fas fa-download me-2"></i>Tải xuống
                </a>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Đóng
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let currentImages = [];
let currentImageIndex = 0;

function showMultipleImages(images) {
    currentImages = Array.isArray(images) ? images : [images];
    currentImageIndex = 0;
    
    const modal = new bootstrap.Modal(document.getElementById('imageModal'));
    
    // Update counter
    const counter = document.getElementById('imageCounter');
    if (currentImages.length > 1) {
        counter.textContent = `1/${currentImages.length}`;
    } else {
        counter.textContent = '';
    }
    
    // Create thumbnails
    createThumbnails();
    
    // Show first image
    displayImage(0);
    
    // Show/hide navigation buttons
    updateNavigationButtons();
    
    modal.show();
}

function createThumbnails() {
    const container = document.getElementById('thumbnailsContainer');
    container.innerHTML = '';
    
    if (currentImages.length <= 1) {
        container.style.display = 'none';
        return;
    }
    
    container.style.display = 'flex';
    
    currentImages.forEach((imagePath, index) => {
        const thumb = document.createElement('img');
        thumb.src = imagePath;
        thumb.className = 'border rounded';
        thumb.style.cssText = 'width: 80px; height: 80px; object-fit: cover; cursor: pointer; transition: all 0.2s;';
        thumb.onclick = () => displayImage(index);
        thumb.dataset.index = index;
        
        if (index === 0) {
            thumb.classList.add('border-primary');
            thumb.style.borderWidth = '3px';
        }
        
        container.appendChild(thumb);
    });
}

function displayImage(index) {
    if (index < 0 || index >= currentImages.length) return;
    
    currentImageIndex = index;
    const modalImage = document.getElementById('modalImage');
    const downloadBtn = document.getElementById('downloadImageBtn');
    const counter = document.getElementById('imageCounter');
    
    // Update main image
    modalImage.src = currentImages[index];
    downloadBtn.href = currentImages[index];
    
    // Update counter
    if (currentImages.length > 1) {
        counter.textContent = `${index + 1}/${currentImages.length}`;
    }
    
    // Update thumbnails
    const thumbnails = document.querySelectorAll('#thumbnailsContainer img');
    thumbnails.forEach((thumb, i) => {
        if (i === index) {
            thumb.classList.add('border-primary');
            thumb.style.borderWidth = '3px';
        } else {
            thumb.classList.remove('border-primary');
            thumb.style.borderWidth = '1px';
        }
    });
    
    updateNavigationButtons();
}

function navigateImage(direction) {
    const newIndex = currentImageIndex + direction;
    if (newIndex >= 0 && newIndex < currentImages.length) {
        displayImage(newIndex);
    }
}

function updateNavigationButtons() {
    const prevBtn = document.getElementById('prevImageBtn');
    const nextBtn = document.getElementById('nextImageBtn');
    
    if (currentImages.length <= 1) {
        prevBtn.style.display = 'none';
        nextBtn.style.display = 'none';
        return;
    }
    
    prevBtn.style.display = currentImageIndex > 0 ? 'block' : 'none';
    nextBtn.style.display = currentImageIndex < currentImages.length - 1 ? 'block' : 'none';
}

// Keyboard navigation
document.addEventListener('keydown', function(e) {
    const modal = document.getElementById('imageModal');
    if (!modal.classList.contains('show')) return;
    
    if (e.key === 'ArrowLeft') {
        navigateImage(-1);
    } else if (e.key === 'ArrowRight') {
        navigateImage(1);
    } else if (e.key === 'Escape') {
        bootstrap.Modal.getInstance(modal).hide();
    }
});

function confirmDelete(slipId) {
    if (confirm('Bạn có chắc chắn muốn xóa phiếu hoàn thành này?\n\nThao tác này không thể hoàn tác.')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '<?= $basePath ?>/completion-slips/' + slipId + '/delete';
        document.body.appendChild(form);
        form.submit();
    }
}

// Toggle chọn tất cả
function toggleSelectAll(checkbox) {
    const checkboxes = document.querySelectorAll('.slip-checkbox');
    checkboxes.forEach(cb => cb.checked = checkbox.checked);
    updateSelectedCount();
}

// Cập nhật số lượng đã chọn
function updateSelectedCount() {
    const checkboxes = document.querySelectorAll('.slip-checkbox:checked');
    const count = checkboxes.length;
    const deleteBtn = document.getElementById('deleteSelectedBtn');
    const countSpan = document.getElementById('selectedCount');
    
    if (deleteBtn && countSpan) {
        countSpan.textContent = count;
        deleteBtn.style.display = count > 0 ? 'inline-block' : 'none';
    }
    
    // Cập nhật trạng thái checkbox "Chọn tất cả"
    const selectAllCheckbox = document.getElementById('selectAll');
    const allCheckboxes = document.querySelectorAll('.slip-checkbox');
    if (selectAllCheckbox && allCheckboxes.length > 0) {
        selectAllCheckbox.checked = checkboxes.length === allCheckboxes.length;
    }
}

// Xóa nhiều phiếu đã chọn
function deleteSelected() {
    const checkboxes = document.querySelectorAll('.slip-checkbox:checked');
    
    if (checkboxes.length === 0) {
        alert('Vui lòng chọn ít nhất một phiếu để xóa');
        return;
    }
    
    const ids = Array.from(checkboxes).map(cb => cb.value);
    const count = ids.length;
    
    if (confirm(`Bạn có chắc chắn muốn xóa ${count} phiếu hoàn thành đã chọn?\n\nHành động này không thể hoàn tác!`)) {
        const deleteBtn = document.getElementById('deleteSelectedBtn');
        const originalHtml = deleteBtn.innerHTML;
        deleteBtn.disabled = true;
        deleteBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Đang xóa...';
        
        fetch('<?= $basePath ?>/completion-slips/delete-multiple', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ ids: ids })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message || `Đã xóa thành công ${count} phiếu`);
                location.reload();
            } else {
                alert('Lỗi: ' + (data.message || 'Không thể xóa phiếu'));
                deleteBtn.disabled = false;
                deleteBtn.innerHTML = originalHtml;
            }
        })
        .catch(error => {
            alert('Lỗi kết nối: ' + error.message);
            deleteBtn.disabled = false;
            deleteBtn.innerHTML = originalHtml;
        });
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const teacherSelect = document.getElementById('filter_teacher_select');
    const teacherInput = document.getElementById('filter_teacher_input');
    if (teacherSelect && teacherInput) {
        teacherSelect.addEventListener('change', function() {
            if (this.value === '__custom__') {
                teacherInput.style.display = 'block';
                teacherInput.value = '';
                teacherInput.focus();
            } else if (this.value === '') {
                teacherInput.style.display = 'none';
                teacherInput.value = '';
            } else {
                teacherInput.value = this.value;
                teacherInput.style.display = 'none';
            }
        });
    }

    const courseCombo = document.getElementById('filterCourseCombo');
    if (courseCombo) {
        const searchInput = document.getElementById('filter_course_search');
        const hiddenInput = document.getElementById('filter_course_id');
        const dropdown = courseCombo.querySelector('.course-dropdown');
        const courses = <?= json_encode(array_map(function($course) {
            return [
                'id' => $course['id'],
                'name' => $course['course_name'] ?? ($course['course_code'] ?? 'Khóa học'),
                'code' => $course['course_code'] ?? ''
            ];
        }, $courses)) ?>;

        let debounceTimer;

        function filterCourses(keyword) {
            const term = keyword.trim().toLowerCase();
            if (!term) return courses;
            return courses.filter(course =>
                course.name.toLowerCase().includes(term) ||
                (course.code && course.code.toLowerCase().includes(term))
            );
        }

        function renderDropdown(items) {
            if (!items.length) {
                dropdown.innerHTML = '<div class="course-item p-2 text-muted">Không tìm thấy khóa học</div>';
            } else {
                dropdown.innerHTML = items.map(item => `
                    <div class="course-item p-2" data-id="${item.id}" data-name="${item.name}">
                        <strong>${item.name}</strong>
                        ${item.code ? `<small class="text-muted d-block">${item.code}</small>` : ''}
                    </div>
                `).join('');
            }
            dropdown.style.display = 'block';
        }

        searchInput.addEventListener('focus', () => {
            renderDropdown(filterCourses(searchInput.value));
        });

        searchInput.addEventListener('input', () => {
            clearTimeout(debounceTimer);
            const value = searchInput.value;
            if (!value.trim()) {
                hiddenInput.value = '';
            }
            debounceTimer = setTimeout(() => {
                renderDropdown(filterCourses(value));
            }, 200);
        });

        dropdown.addEventListener('click', (e) => {
            const item = e.target.closest('.course-item');
            if (!item || !item.dataset.id) return;
            searchInput.value = item.dataset.name;
            hiddenInput.value = item.dataset.id;
            dropdown.style.display = 'none';
        });

        document.addEventListener('click', (e) => {
            if (!courseCombo.contains(e.target)) {
                dropdown.style.display = 'none';
            }
        });
    }
});
</script>

<style>
.course-combo-wrapper {
    position: relative;
}

.course-dropdown {
    background: #fff;
    border: 1px solid #e0e6ed;
    border-radius: 8px;
    box-shadow: 0 6px 18px rgba(15, 23, 42, 0.12);
    max-height: 320px;
    overflow-y: auto;
    z-index: 1060;
    margin-top: 4px;
}

.course-item {
    cursor: pointer;
    border-bottom: 1px solid #f1f5f9;
}

.course-item:last-child {
    border-bottom: none;
}

.course-item:hover {
    background: #f8fafc;
}
</style>

<?php
$content = ob_get_clean();
useModernLayout('Phiếu hoàn thành học viên', $content);
?>
