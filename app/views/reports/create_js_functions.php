// Course combo box functions - extracted from create.php

function initializeCourseCombo(wrapper) {
    const input = wrapper.querySelector('.course-combo-input');
    const hiddenInput = wrapper.querySelector('.course-id-input');
    const dropdown = wrapper.querySelector('.course-dropdown');
    
    function filterCourses(searchTerm) {
        const filtered = courses.filter(course => {
            const fullText = `${course.course_code} - ${course.course_name}`;
            return fullText.toLowerCase().includes(searchTerm.toLowerCase());
        });
        
        displayDropdown(filtered, searchTerm);
    }
    
    function displayDropdown(filteredCourses, searchTerm) {
        dropdown.innerHTML = '';
        
        filteredCourses.forEach(course => {
            const option = document.createElement('div');
            option.className = 'dropdown-option';
            option.innerHTML = `
                <div style="font-weight: 500; font-size: 0.8rem;">${course.course_code}</div>
                <div style="font-size: 0.75rem; color: #6c757d;">${course.course_name}</div>
            `;
            
            option.addEventListener('click', () => {
                input.value = `${course.course_code} - ${course.course_name}`;
                hiddenInput.value = course.id;
                dropdown.style.display = 'none';
            });
            
            option.addEventListener('mouseenter', () => {
                option.style.backgroundColor = '#f8f9fa';
            });
            
            option.addEventListener('mouseleave', () => {
                option.style.backgroundColor = 'white';
            });
            
            dropdown.appendChild(option);
        });
        
        if (searchTerm && !filteredCourses.some(course => 
            `${course.course_code} - ${course.course_name}`.toLowerCase() === searchTerm.toLowerCase())) {
            const newOption = document.createElement('div');
            newOption.className = 'dropdown-option text-success';
            newOption.innerHTML = `
                <div style="font-weight: 600; font-size: 0.8rem;">
                    <i class="fas fa-plus-circle me-1"></i>Thêm khóa học: "${searchTerm}"
                </div>
            `;
            
            newOption.addEventListener('click', () => {
                input.value = searchTerm;
                hiddenInput.value = searchTerm;
                dropdown.style.display = 'none';
            });
            
            newOption.addEventListener('mouseenter', () => {
                newOption.style.backgroundColor = '#d1edff';
            });
            
            newOption.addEventListener('mouseleave', () => {
                newOption.style.backgroundColor = 'white';
            });
            
            dropdown.appendChild(newOption);
        }
        
        if (dropdown.children.length === 0) {
            const noResult = document.createElement('div');
            noResult.className = 'p-2 text-muted';
            noResult.style.fontSize = '0.8rem';
            noResult.textContent = 'Không tìm thấy khóa học nào';
            dropdown.appendChild(noResult);
        }
        
        dropdown.style.display = 'block';
    }
    
    input.addEventListener('input', (e) => {
        const value = e.target.value;
        if (value.length > 0) {
            filterCourses(value);
        } else {
            hiddenInput.value = '';
            dropdown.style.display = 'none';
        }
    });
    
    input.addEventListener('focus', () => {
        if (input.value.length > 0) {
            filterCourses(input.value);
        } else {
            displayDropdown(courses, '');
        }
    });
    
    input.addEventListener('blur', (e) => {
        setTimeout(() => {
            if (!dropdown.matches(':hover')) {
                dropdown.style.display = 'none';
            }
        }, 150);
    });
    
    document.addEventListener('click', (e) => {
        if (!wrapper.contains(e.target)) {
            dropdown.style.display = 'none';
        }
    });
}

function addCustomerRow() {
    addCustomerRowWithData({});
}

function removeLastCustomerRow() {
    const tableBody = document.getElementById('customers-table');
    const rows = tableBody.querySelectorAll('tr');
    if (rows.length > 0) {
        const lastRow = rows[rows.length - 1];
        lastRow.remove();
        updateCustomerCount();
        updateStats();
    }
}

function updateStats() {
    const checkboxes = document.querySelectorAll('input[name*="[registered]"]:checked');
    const registeredCount = checkboxes.length;
    
    document.getElementById('registered-count').textContent = registeredCount;
    document.getElementById('total_registered').value = registeredCount;
    
    const visitorCount = parseInt(document.getElementById('visitors-count').textContent);
    const rateElement = document.getElementById('conversion-rate');
    if (rateElement) {
        const conversionRate = visitorCount > 0 ? (registeredCount / visitorCount * 100).toFixed(1) : 0;
        rateElement.textContent = conversionRate + '%';
    }
    
    // Update payment method dropdowns
    const rows = document.querySelectorAll('#customers-table tr');
    rows.forEach(row => {
        const checkbox = row.querySelector('input[name*="[registered]"]');
        const paymentSelect = row.querySelector('select[name*="[payment_method]"]');
        
        if (checkbox && paymentSelect) {
            if (checkbox.checked) {
                paymentSelect.disabled = false;
                paymentSelect.classList.remove('bg-light');
            } else {
                paymentSelect.disabled = true;
                paymentSelect.value = '';
                paymentSelect.classList.add('bg-light');
            }
        }
    });
    
    updateCustomerCount();
}

// Update visitor count when changed
document.addEventListener('DOMContentLoaded', function() {
    const visitorsInput = document.getElementById('total_visitors');
    if (visitorsInput) {
        visitorsInput.addEventListener('input', function() {
            document.getElementById('visitors-count').textContent = this.value || 0;
            updateStats();
        });
    }
});