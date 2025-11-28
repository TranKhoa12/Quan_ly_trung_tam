<?php
require_once __DIR__ . '/../layouts/main.php';

$scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
$scriptDir = str_replace('\\', '/', dirname($scriptName));
$basePath = rtrim($scriptDir, '/');
if ($basePath === '' || $basePath === '.') {
    $basePath = '';
}

ob_start();

$headerButtons = '<a href="' . $basePath . '/completion-slips" class="btn btn-outline-secondary">
    <i class="fas fa-arrow-left me-2"></i>Quay lại danh sách
</a>';

echo pageHeader(
    'Nhập phiếu hoàn thành học viên',
    'Ghi nhận thông tin học viên hoàn tất khóa và đính kèm ảnh phiếu xác nhận',
    $headerButtons
);

$selectedCourseName = '';
if (!empty($old_data['course_id'])) {
    foreach ($courses as $course) {
        if ((int)$course['id'] === (int)$old_data['course_id']) {
            $selectedCourseName = $course['course_name'] ?? ($course['course_code'] ?? '');
            break;
        }
    }
}

$hasTeachingStaff = !empty($staffList);
?>

<div class="p-3">
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle me-1"></i><?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <div class="stats-card">
        <div class="card-body">
            <form action="<?= $basePath ?>/completion-slips" method="POST" enctype="multipart/form-data">
                <div class="row g-4">
                    <div class="col-md-6">
                        <label class="form-label">
                            <i class="fas fa-user text-primary me-1"></i>Tên học viên <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control" name="student_name" required
                               value="<?= htmlspecialchars($old_data['student_name'] ?? '') ?>"
                               placeholder="Nhập họ tên học viên">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">
                            <i class="fas fa-book text-warning me-1"></i>Khóa học <span class="text-danger">*</span>
                        </label>
                        <div class="course-combo-wrapper position-relative" id="completionCourseCombo">
                            <input type="text"
                                   class="form-control course-combo-input"
                                   id="completion_course_search"
                                   placeholder="Gõ để tìm hoặc chọn khóa học..."
                                   autocomplete="off"
                                   value="<?= htmlspecialchars($selectedCourseName) ?>">
                            <input type="hidden"
                                   name="course_id"
                                   id="completion_course_id"
                                   class="course-id-input"
                                   value="<?= htmlspecialchars($old_data['course_id'] ?? '') ?>">
                            <div class="course-dropdown position-absolute w-100" style="display:none;"></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">
                            <i class="fas fa-chalkboard-teacher text-info me-1"></i>Giáo viên phụ trách <span class="text-danger">*</span>
                        </label>
                        <?php if ($hasTeachingStaff): ?>
                            <select class="form-select" id="teacher_select" required>
                                <option value="">-- Chọn giáo viên --</option>
                                <?php foreach ($staffList as $staff): ?>
                                    <option value="<?= htmlspecialchars($staff['full_name']) ?>"
                                            <?= (isset($old_data['teacher_name']) && $old_data['teacher_name'] === $staff['full_name']) ? 'selected' : '' ?>>
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
                        <input type="text" class="form-control mt-2" id="teacher_name_input" name="teacher_name"
                               value="<?= htmlspecialchars($old_data['teacher_name'] ?? '') ?>"
                               placeholder="Nhập tên giáo viên"
                               style="<?= $hasTeachingStaff ? 'display:none;' : 'display:block;' ?>;"
                               <?= $hasTeachingStaff ? '' : 'required' ?>>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label">
                            <i class="fas fa-sticky-note text-secondary me-1"></i>Ghi chú
                        </label>
                        <textarea class="form-control" name="notes" rows="3" placeholder="Thông tin bổ sung cho bộ phận quản lý"><?= htmlspecialchars($old_data['notes'] ?? '') ?></textarea>
                    </div>
                    <div class="col-12">
                        <label class="form-label">
                            <i class="fas fa-images text-danger me-1"></i>Ảnh phiếu hoàn thành <span class="text-danger">*</span>
                        </label>
                        <input type="file" class="form-control" id="completion_images" name="completion_images[]" multiple
                               accept="image/*,.pdf" required>
                        <div class="form-text">Hỗ trợ JPG, PNG, PDF. Tối đa 10MB mỗi file. Bắt buộc phải chọn ít nhất 1 ảnh.</div>
                        <div id="completion_preview" class="d-flex flex-wrap gap-2 mt-3"></div>
                    </div>
                </div>
                <div class="d-flex justify-content-end gap-2 mt-4">
                    <button type="reset" class="btn btn-light">
                        <i class="fas fa-eraser me-1"></i>Làm mới form
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>Lưu phiếu
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
const completionCourses = <?= json_encode($courses ?? []) ?>;

document.addEventListener('DOMContentLoaded', () => {
    initializeCompletionCourseCombo();
    initializeCompletionImagePreview();
    initializeTeacherSelect();
});

function initializeCompletionCourseCombo() {
    const wrapper = document.getElementById('completionCourseCombo');
    if (!wrapper) {
        return;
    }

    const input = document.getElementById('completion_course_search');
    const hiddenInput = document.getElementById('completion_course_id');
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
        return completionCourses.filter(course => {
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
            renderOptions(completionCourses);
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

function initializeCompletionImagePreview() {
    const fileInput = document.getElementById('completion_images');
    const previewContainer = document.getElementById('completion_preview');
    if (!fileInput || !previewContainer) {
        return;
    }

    const MAX_SIZE = 10 * 1024 * 1024; // 10MB

    fileInput.addEventListener('change', renderPreviews);

    function renderPreviews() {
        previewContainer.innerHTML = '';
        const files = Array.from(fileInput.files);

        if (!files.length) {
            return;
        }

        files.forEach((file, index) => {
            const isPdf = file.type === 'application/pdf' || file.name.toLowerCase().endsWith('.pdf');
            const isImage = file.type.startsWith('image/');

            if (!isPdf && !isImage) {
                alert(`File ${file.name} không nằm trong định dạng cho phép.`);
                return;
            }

            if (file.size > MAX_SIZE) {
                alert(`File ${file.name} vượt quá 10MB, vui lòng chọn file nhỏ hơn.`);
                return;
            }

            const card = document.createElement('div');
            card.className = 'completion-preview-card';

            const removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.className = 'btn btn-sm btn-danger preview-remove-btn';
            removeBtn.innerHTML = '<i class="fas fa-times"></i>';
            removeBtn.addEventListener('click', () => removeFile(index));
            card.appendChild(removeBtn);

            const caption = document.createElement('div');
            caption.className = 'preview-caption';
            caption.textContent = file.name.length > 18 ? file.name.substring(0, 15) + '...' : file.name;

            if (isPdf) {
                const icon = document.createElement('div');
                icon.className = 'preview-pdf d-flex flex-column align-items-center justify-content-center';
                icon.innerHTML = '<i class="fas fa-file-pdf text-danger fs-1"></i><small class="text-muted">PDF</small>';
                card.appendChild(icon);
                card.appendChild(caption);
            } else {
                const imgWrapper = document.createElement('div');
                imgWrapper.className = 'preview-image-wrapper';
                const img = document.createElement('img');
                img.className = 'w-100 h-100 rounded';
                img.style.objectFit = 'cover';
                imgWrapper.appendChild(img);
                const reader = new FileReader();
                reader.onload = (event) => {
                    img.src = event.target.result;
                    img.addEventListener('click', () => window.open(event.target.result, '_blank'));
                };
                reader.readAsDataURL(file);
                card.appendChild(imgWrapper);
                card.appendChild(caption);
            }

            previewContainer.appendChild(card);
        });
    }

    function removeFile(targetIndex) {
        const dt = new DataTransfer();
        Array.from(fileInput.files).forEach((file, index) => {
            if (index !== targetIndex) {
                dt.items.add(file);
            }
        });
        fileInput.files = dt.files;
        renderPreviews();
    }
}

function initializeTeacherSelect() {
    const selectEl = document.getElementById('teacher_select');
    const inputEl = document.getElementById('teacher_name_input');

    if (!inputEl) {
        return;
    }

    const form = selectEl ? selectEl.closest('form') : inputEl.closest('form');
    if (!form) {
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

    form.addEventListener('submit', function(e) {
        const fileInput = document.getElementById('completion_images');
        if (!fileInput.files || fileInput.files.length === 0) {
            e.preventDefault();
            alert('Vui lòng chọn ít nhất 1 ảnh phiếu hoàn thành!');
            fileInput.focus();
            return false;
        }

        const teacherValue = inputEl.value.trim();
        if (!teacherValue) {
            e.preventDefault();
            alert('Vui lòng nhập giáo viên phụ trách!');
            if (selectEl && selectEl.style.display !== 'none') {
                selectEl.focus();
            } else {
                inputEl.focus();
            }
            return false;
        }
    });
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

.completion-preview-card {
    width: 140px;
    min-height: 140px;
    border: 1px solid #e5e7eb;
    border-radius: 0.75rem;
    padding: 0.5rem;
    position: relative;
    background: #fff;
    display: flex;
    flex-direction: column;
    gap: 0.35rem;
    align-items: center;
    justify-content: center;
}

.preview-image-wrapper {
    width: 100%;
    height: 100px;
    overflow: hidden;
    border-radius: 0.5rem;
    cursor: pointer;
}

.preview-remove-btn {
    position: absolute;
    top: 6px;
    right: 6px;
    padding: 0.15rem 0.35rem;
}

.preview-caption {
    font-size: 0.75rem;
    text-align: center;
    color: #6b7280;
}

.preview-pdf {
    width: 100%;
    height: 100px;
    border-radius: 0.5rem;
    background: #f8fafc;
}
</style>

<?php
$content = ob_get_clean();
useModernLayout('Nhập phiếu hoàn thành', $content);
?>
