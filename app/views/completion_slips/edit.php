<?php
require_once __DIR__ . '/../layouts/main.php';

$scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
$scriptDir = str_replace('\\', '/', dirname($scriptName));
$basePath = rtrim($scriptDir, '/');
if ($basePath === '' || $basePath === '.') {
    $basePath = '';
}

$uploadsPath = $basePath . '/uploads/';

$selectedCourseName = '';
if (!empty($slip['course_id'])) {
    foreach ($courses as $course) {
        if ((int)$course['id'] === (int)$slip['course_id']) {
            $selectedCourseName = $course['course_name'] ?? ($course['course_code'] ?? '');
            break;
        }
    }
}

$hasTeachingStaff = !empty($staffList);

ob_start();

$headerButtons = '<a href="' . $basePath . '/completion-slips" class="btn btn-outline-secondary">
    <i class="fas fa-arrow-left me-1"></i>Quay lại danh sách
</a>';

echo pageHeader(
    'Chỉnh sửa phiếu #' . $slip['id'],
    'Cập nhật thông tin phiếu hoàn thành học viên',
    $headerButtons
);
?>

<div class="p-3">
    <form action="<?= $basePath ?>/completion-slips/<?= $slip['id'] ?>/update" method="POST" enctype="multipart/form-data">
        <!-- Thông tin cơ bản -->
        <div class="stats-card mb-4">
            <div class="card-body">
                <h6 class="fw-bold mb-3">
                    <i class="fas fa-info-circle text-primary me-2"></i>Thông tin cơ bản
                </h6>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">
                            <i class="fas fa-user text-primary me-1"></i>Tên học viên <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control" name="student_name" required 
                               value="<?= htmlspecialchars($slip['student_name']) ?>"
                               placeholder="Nhập họ tên học viên">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">
                            <i class="fas fa-book text-warning me-1"></i>Khóa học <span class="text-danger">*</span>
                        </label>
                        <div class="course-combo-wrapper position-relative" id="editCourseCombo">
                            <input type="text"
                                   class="form-control course-combo-input"
                                   id="edit_course_search"
                                   placeholder="Gõ để tìm hoặc chọn khóa học..."
                                   autocomplete="off"
                                   value="<?= htmlspecialchars($selectedCourseName) ?>">
                            <input type="hidden"
                                   name="course_id"
                                   id="edit_course_id"
                                   class="course-id-input"
                                   value="<?= htmlspecialchars($slip['course_id'] ?? '') ?>">
                            <div class="course-dropdown position-absolute w-100" style="display:none;"></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">
                            <i class="fas fa-chalkboard-teacher text-info me-1"></i>Giáo viên phụ trách <span class="text-danger">*</span>
                        </label>
                        <?php if ($hasTeachingStaff): ?>
                            <select class="form-select" id="teacher_select_edit" required>
                                <option value="">-- Chọn giáo viên --</option>
                                <?php foreach ($staffList as $staff): ?>
                                    <option value="<?= htmlspecialchars($staff['full_name']) ?>"
                                            <?= (($slip['teacher_name'] ?? '') === $staff['full_name']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($staff['full_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                                <option value="__other__">+ Nhập tên khác</option>
                            </select>
                            <div class="form-text">Danh sách chỉ gồm nhân sự thuộc phòng Giảng dạy.</div>
                        <?php else: ?>
                            <div class="alert alert-warning py-2" role="alert">
                                <i class="fas fa-info-circle me-1"></i>Chưa có nhân sự thuộc phòng Giảng dạy. Vui lòng nhập tên giáo viên thủ công.
                            </div>
                        <?php endif; ?>
                        <input type="text" class="form-control mt-2" id="teacher_custom_edit" name="teacher_name"
                               value="<?= htmlspecialchars($slip['teacher_name'] ?? '') ?>"
                               placeholder="Nhập tên giáo viên"
                               style="<?= $hasTeachingStaff ? 'display:none;' : 'display:block;' ?>;"
                               <?= $hasTeachingStaff ? '' : 'required' ?>>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">
                            <i class="fas fa-sticky-note text-secondary me-1"></i>Ghi chú
                        </label>
                        <textarea class="form-control" name="notes" rows="3" 
                                  placeholder="Thông tin bổ sung cho bộ phận quản lý"><?= htmlspecialchars($slip['notes'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quản lý ảnh -->
        <div class="stats-card mb-4">
            <div class="card-body">
                <h6 class="fw-bold mb-3">
                    <i class="fas fa-images text-danger me-2"></i>Quản lý ảnh phiếu
                </h6>
                
                <?php if (!empty($images)): ?>
                    <div class="alert alert-info d-flex align-items-center mb-3">
                        <i class="fas fa-info-circle me-2"></i>
                        <span>Chọn checkbox để xóa ảnh không cần thiết. Ảnh sẽ bị xóa khi bạn nhấn "Lưu thay đổi".</span>
                    </div>
                    
                    <div class="row g-3 mb-4">
                        <?php foreach ($images as $index => $image): ?>
                            <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
                                <div class="image-card border rounded p-2 h-100 position-relative">
                                    <div class="position-absolute top-0 start-0 m-2" style="z-index: 10;">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" 
                                                   id="remove_img_<?= $index ?>" 
                                                   name="remove_images[]" 
                                                   value="<?= htmlspecialchars($image) ?>"
                                                   style="cursor: pointer; width: 20px; height: 20px;">
                                            <label class="form-check-label ms-1 badge bg-danger" for="remove_img_<?= $index ?>" style="cursor: pointer;">
                                                <i class="fas fa-trash"></i> Xóa
                                            </label>
                                        </div>
                                    </div>
                                    
                                    <?php if (preg_match('/\.pdf$/i', $image)): ?>
                                        <div class="text-center d-flex flex-column align-items-center justify-content-center" style="min-height: 150px;">
                                            <i class="fas fa-file-pdf text-danger mb-2" style="font-size: 3rem;"></i>
                                            <small class="text-muted mb-2">PDF #<?= $index + 1 ?></small>
                                            <a href="<?= htmlspecialchars($uploadsPath . $image) ?>" 
                                               target="_blank" 
                                               class="btn btn-sm btn-outline-primary w-100">
                                                <i class="fas fa-external-link-alt me-1"></i>Xem
                                            </a>
                                        </div>
                                    <?php else: ?>
                                        <a href="<?= htmlspecialchars($uploadsPath . $image) ?>" 
                                           target="_blank" 
                                           class="d-block mb-2">
                                            <img src="<?= htmlspecialchars($uploadsPath . $image) ?>" 
                                                 class="img-fluid rounded" 
                                                 alt="Ảnh #<?= $index + 1 ?>"
                                                 style="height: 150px; width: 100%; object-fit: cover;">
                                        </a>
                                        <small class="text-muted d-block text-center">Ảnh #<?= $index + 1 ?></small>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="alert alert-light border text-center py-4">
                        <i class="fas fa-image text-muted mb-2" style="font-size: 2rem;"></i>
                        <p class="mb-0 text-muted">Chưa có ảnh nào được lưu trữ</p>
                    </div>
                <?php endif; ?>

                <hr class="my-3">

                <label class="form-label fw-semibold">
                    <i class="fas fa-plus-circle text-success me-1"></i>Thêm ảnh mới
                </label>
                <input type="file" class="form-control" id="new_completion_images" 
                       name="completion_images[]" multiple accept="image/*,.pdf">
                <div class="form-text">
                    <i class="fas fa-info-circle me-1"></i>
                    Hỗ trợ JPG, PNG, PDF. Tối đa 10MB mỗi file. Ảnh mới sẽ được thêm vào danh sách hiện có.
                </div>
                <div id="new_images_preview" class="row g-3 mt-2"></div>
            </div>
        </div>

        <!-- Action buttons -->
        <div class="d-flex justify-content-between align-items-center">
            <a href="<?= $basePath ?>/completion-slips" class="btn btn-light">
                <i class="fas fa-times me-1"></i>Hủy bỏ
            </a>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save me-1"></i>Lưu thay đổi
            </button>
        </div>
    </form>
</div>

<script>
const editCourses = <?= json_encode($courses ?? []) ?>;

(function() {
    // Initialize course combo
    initializeEditCourseCombo();
    
    const fileInput = document.getElementById('new_completion_images');
    const previewContainer = document.getElementById('new_images_preview');
    if (!fileInput || !previewContainer) {
        return;
    }

    const MAX_SIZE = 10 * 1024 * 1024; // 10MB

    fileInput.addEventListener('change', renderNewPreviews);

    function renderNewPreviews() {
        previewContainer.innerHTML = '';
        const files = Array.from(fileInput.files);

        if (!files.length) {
            return;
        }

        files.forEach((file, index) => {
            const isPdf = file.type === 'application/pdf' || file.name.toLowerCase().endsWith('.pdf');
            const isImage = file.type.startsWith('image/');

            if (!isPdf && !isImage) {
                alert(`File ${file.name} không được hỗ trợ.`);
                return;
            }

            if (file.size > MAX_SIZE) {
                alert(`File ${file.name} vượt quá 10MB.`);
                return;
            }

            const col = document.createElement('div');
            col.className = 'col-xl-2 col-lg-3 col-md-4 col-sm-6';

            const card = document.createElement('div');
            card.className = 'border rounded p-2 position-relative';

            const removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.className = 'btn btn-sm btn-danger position-absolute top-0 end-0 m-2';
            removeBtn.style.cssText = 'z-index: 10; padding: 0.25rem 0.5rem;';
            removeBtn.innerHTML = '<i class="fas fa-times"></i>';
            removeBtn.onclick = () => removeNewFile(index);
            card.appendChild(removeBtn);

            const caption = document.createElement('small');
            caption.className = 'text-muted d-block text-center mb-2';
            caption.textContent = file.name.length > 20 ? file.name.substring(0, 17) + '...' : file.name;

            if (isPdf) {
                const icon = document.createElement('div');
                icon.className = 'text-center d-flex flex-column align-items-center justify-content-center';
                icon.style.minHeight = '150px';
                icon.innerHTML = '<i class="fas fa-file-pdf text-danger mb-2" style="font-size: 3rem;"></i>';
                card.appendChild(icon);
                card.appendChild(caption);
            } else {
                const imgWrapper = document.createElement('div');
                imgWrapper.className = 'mb-2';
                const img = document.createElement('img');
                img.className = 'img-fluid rounded';
                img.style.cssText = 'height: 150px; width: 100%; object-fit: cover;';
                imgWrapper.appendChild(img);
                const reader = new FileReader();
                reader.onload = (e) => {
                    img.src = e.target.result;
                };
                reader.readAsDataURL(file);
                card.appendChild(imgWrapper);
                card.appendChild(caption);
            }

            col.appendChild(card);
            previewContainer.appendChild(col);
        });
    }

    function removeNewFile(targetIndex) {
        const dt = new DataTransfer();
        Array.from(fileInput.files).forEach((file, index) => {
            if (index !== targetIndex) {
                dt.items.add(file);
            }
        });
        fileInput.files = dt.files;
        renderNewPreviews();
    }
    
    // Initialize teacher select
    initializeEditTeacherSelect();
})();

function initializeEditCourseCombo() {
    const wrapper = document.getElementById('editCourseCombo');
    if (!wrapper) {
        return;
    }

    const input = document.getElementById('edit_course_search');
    const hiddenInput = document.getElementById('edit_course_id');
    const dropdown = wrapper.querySelector('.course-dropdown');

    const renderOptions = (list) => {
        dropdown.innerHTML = '';

        if (!list.length) {
            const empty = document.createElement('div');
            empty.className = 'p-2 text-muted small';
            empty.textContent = 'Không tìm thấy khóa học nào';
            dropdown.appendChild(empty);
        } else {
            list.forEach(course => {
                const courseName = course.course_name || course.course_code || 'Khóa học';
                const option = document.createElement('div');
                option.className = 'dropdown-option';
                option.innerHTML = `
                    <div class="fw-semibold">${courseName}</div>
                `;

                option.addEventListener('click', () => {
                    input.value = courseName;
                    hiddenInput.value = course.id;
                    dropdown.style.display = 'none';
                });

                dropdown.appendChild(option);
            });
        }

        dropdown.style.display = 'block';
    };

    const filterCourses = (keyword) => {
        const normalized = keyword.toLowerCase();
        return editCourses.filter(course => {
            const name = (course.course_name || '').toLowerCase();
            const code = (course.course_code || '').toLowerCase();
            return name.includes(normalized) || code.includes(normalized);
        });
    };

    input.addEventListener('input', () => {
        const value = input.value.trim();
        if (!value) {
            hiddenInput.value = '';
            dropdown.style.display = 'none';
            return;
        }
        renderOptions(filterCourses(value));
    });

    input.addEventListener('focus', () => {
        const current = input.value.trim();
        if (!current) {
            renderOptions(editCourses);
        } else {
            renderOptions(filterCourses(current));
        }
    });

    document.addEventListener('click', (event) => {
        if (!wrapper.contains(event.target)) {
            dropdown.style.display = 'none';
        }
    });

    input.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            dropdown.style.display = 'none';
        }
    });
}

function initializeEditTeacherSelect() {
    const selectEl = document.getElementById('teacher_select_edit');
    const inputEl = document.getElementById('teacher_custom_edit');

    if (!inputEl) {
        return;
    }

    if (selectEl) {
        selectEl.addEventListener('change', function() {
            if (this.value === '__other__') {
                selectEl.removeAttribute('required');
                selectEl.style.display = 'none';
                inputEl.style.display = 'block';
                inputEl.setAttribute('required', 'required');
                inputEl.focus();
                inputEl.value = '';
            } else {
                inputEl.value = this.value;
                inputEl.removeAttribute('required');
            }
        });

        const currentValue = inputEl.value.trim();
        if (currentValue) {
            const optionExists = Array.from(selectEl.options).some(opt => opt.value === currentValue);
            if (!optionExists && currentValue !== '__other__') {
                selectEl.removeAttribute('required');
                selectEl.style.display = 'none';
                inputEl.style.display = 'block';
                inputEl.setAttribute('required', 'required');
            }
        }
    } else {
        inputEl.style.display = 'block';
        inputEl.setAttribute('required', 'required');
    }
}
</script>

<style>
.course-combo-wrapper {
    position: relative;
}

.course-dropdown {
    background: #fff;
    border: 1px solid #ced4da;
    border-radius: 0.5rem;
    box-shadow: 0 0.5rem 1rem rgba(15, 23, 42, 0.1);
    max-height: 280px;
    overflow-y: auto;
    z-index: 1000;
    margin-top: 4px;
}

.course-dropdown .dropdown-option {
    padding: 0.5rem 0.75rem;
    cursor: pointer;
    border-bottom: 1px solid #f1f5f9;
}

.course-dropdown .dropdown-option:last-child {
    border-bottom: none;
}

.course-dropdown .dropdown-option:hover {
    background: #f8fafc;
}

.image-card {
    transition: all 0.3s ease;
}

.image-card:hover {
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    transform: translateY(-2px);
}

.image-card img {
    transition: transform 0.2s ease;
}

.image-card:hover img {
    transform: scale(1.05);
}

.form-check-input:checked + .form-check-label {
    background-color: #dc3545 !important;
}
</style>

<?php
$content = ob_get_clean();
useModernLayout('Chỉnh sửa phiếu hoàn thành', $content);
?>
