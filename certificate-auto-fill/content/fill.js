// Content script để điền dữ liệu vào form báo cáo doanh thu
// Script này chạy trên trang bt.banhmibebe.com và localhost

console.log('🎯 Fill.js content script đã được load trên:', window.location.href);

// Tự động kiểm tra và điền khi trang load
(async function autoFillOnLoad() {
    // Đợi DOM sẵn sàng
    if (document.readyState === 'loading') {
        await new Promise(resolve => {
            document.addEventListener('DOMContentLoaded', resolve);
        });
    }
    
    // Đợi thêm 1 giây để form render xong
    await new Promise(resolve => setTimeout(resolve, 1000));
    
    console.log('🔍 Kiểm tra autoFillPending trong storage...');
    
    // Kiểm tra có flag auto fill không
    chrome.storage.local.get(['autoFillPending', 'autoFillData'], (result) => {
        console.log('📦 Storage result:', result);
        
        if (result.autoFillPending && result.autoFillData) {
            console.log('✅ Phát hiện flag auto fill, bắt đầu điền dữ liệu...');
            
            // Xóa flag ngay để không điền lại lần sau
            chrome.storage.local.set({ autoFillPending: false });
            
            // Điền dữ liệu
            try {
                fillFormData(result.autoFillData);
                console.log('✅ Đã điền dữ liệu tự động thành công!');
            } catch (error) {
                console.error('❌ Lỗi khi điền dữ liệu:', error);
            }
        } else {
            console.log('ℹ️ Không có yêu cầu auto fill');
        }
    });
})();

// Lắng nghe message từ popup hoặc background (giữ lại để dự phòng)
chrome.runtime.onMessage.addListener((request, sender, sendResponse) => {
    console.log('📨 Nhận được message:', request);
    
    if (request.action === 'fillData') {
        console.log('✅ Action = fillData, bắt đầu xử lý...');
        try {
            fillFormData(request.data);
            sendResponse({ success: true });
        } catch (error) {
            console.error('❌ Lỗi trong fillFormData:', error);
            sendResponse({ success: false, error: error.message });
        }
    }
    return true; // Keep channel open for async response
});

// Hàm điền dữ liệu vào form
function fillFormData(data) {
    console.log('📝 Bắt đầu điền form báo cáo doanh thu...');
    console.log('📦 Dữ liệu nhận được:', data);
    console.log('🌐 Current URL:', window.location.href);
    
    let filledCount = 0;
    
    // Debug: List all input fields on page
    console.log('🔍 Tất cả input fields trên trang:');
    document.querySelectorAll('input, select, textarea').forEach(el => {
        console.log(`  - ${el.tagName} [name="${el.name}"] [placeholder="${el.placeholder}"] [type="${el.type}"]`);
    });
    
    // 1. Ngày đóng học phí = Ngày hôm qua (để tránh lỗi validation)
    const paymentDateInput = document.querySelector('input[name="ngay_dong_hoc_phi"]') ||
                             document.querySelector('input[name="date"]') ||
                             document.querySelector('input[type="date"]');
    console.log('🔍 Payment date input found:', paymentDateInput);
    
    if (paymentDateInput) {
        // Dùng ngày hôm qua để tránh lỗi "không được vượt quá ngày hôm nay"
        const yesterday = new Date();
        yesterday.setDate(yesterday.getDate() - 1);
        
        let formattedDate;
        
        // Kiểm tra type của input để dùng format phù hợp
        if (paymentDateInput.type === 'date') {
            // Input type="date" yêu cầu format YYYY-MM-DD
            formattedDate = `${yesterday.getFullYear()}-${String(yesterday.getMonth() + 1).padStart(2, '0')}-${String(yesterday.getDate()).padStart(2, '0')}`;
            console.log('📅 Dùng format ISO (YYYY-MM-DD):', formattedDate);
        } else {
            // Input text thường dùng dd/mm/yyyy
            formattedDate = `${String(yesterday.getDate()).padStart(2, '0')}/${String(yesterday.getMonth() + 1).padStart(2, '0')}/${yesterday.getFullYear()}`;
            console.log('📅 Dùng format dd/mm/yyyy:', formattedDate);
        }
        
        paymentDateInput.value = formattedDate;
        // Không trigger change event ngay để tránh validation
        paymentDateInput.dispatchEvent(new Event('input', { bubbles: true }));
        highlightField(paymentDateInput);
        filledCount++;
        console.log('✅ Ngày đóng học phí (hôm qua):', formattedDate);
    } else {
        console.log('❌ Không tìm thấy trường ngày đóng học phí');
    }
    
    // 2. Họ tên học viên
    const studentNameInput = document.querySelector('input[name="student_name"]') ||
                            document.querySelector('input[name="name"]') ||
                            document.querySelector('input[placeholder*="tên"]') ||
                            document.querySelector('input[placeholder*="học viên"]');
    console.log('🔍 Student name input found:', studentNameInput);
    
    if (studentNameInput && data.student_name) {
        studentNameInput.value = data.student_name;
        studentNameInput.dispatchEvent(new Event('input', { bubbles: true }));
        studentNameInput.dispatchEvent(new Event('change', { bubbles: true }));
        highlightField(studentNameInput);
        filledCount++;
        console.log('✅ Họ tên:', data.student_name);
    } else {
        console.log('❌ Không tìm thấy trường họ tên');
    }
    
    // 3. Khóa học (dropdown or input)
    const courseInput = document.querySelector('select[name="khoa_hoc"]') ||
                       document.querySelector('select[name="course"]') ||
                       document.querySelector('input[name="khoa_hoc"]') ||
                       document.querySelector('input[name="course"]') ||
                       document.querySelector('input[placeholder*="khóa học"]');
    console.log('🔍 Course input found:', courseInput);
    
    if (courseInput && data.course_name) {
        if (courseInput.tagName === 'SELECT') {
            // Find matching option
            const options = courseInput.querySelectorAll('option');
            for (let option of options) {
                if (option.text.includes(data.course_name) || data.course_name.includes(option.text)) {
                    courseInput.value = option.value;
                    break;
                }
            }
        } else {
            courseInput.value = data.course_name;
        }
        courseInput.dispatchEvent(new Event('input', { bubbles: true }));
        courseInput.dispatchEvent(new Event('change', { bubbles: true }));
        highlightField(courseInput);
        filledCount++;
        console.log('✅ Khóa học:', data.course_name);
    } else {
        console.log('❌ Không tìm thấy trường khóa học');
    }
    
    // 4. Mã phiếu thu = Số phiếu thu
    const receiptInput = document.querySelector('input[name="ma_phieu_thu"]') || 
                         document.querySelector('input[name="receipt"]') ||
                         document.querySelector('input[name="receipt_no"]') ||
                         document.querySelector('input[placeholder*="phiếu thu"]') ||
                         document.querySelector('input[placeholder*="mã phiếu"]');
    console.log('🔍 Receipt input found:', receiptInput);
    
    if (receiptInput && data.bill_no) {
        receiptInput.value = data.bill_no;
        receiptInput.dispatchEvent(new Event('input', { bubbles: true }));
        receiptInput.dispatchEvent(new Event('change', { bubbles: true }));
        highlightField(receiptInput);
        filledCount++;
        console.log('✅ Mã phiếu thu:', data.bill_no);
    } else {
        console.log('❌ Không tìm thấy trường mã phiếu thu');
    }
    
    // 5. Số tiền = Thực thu (amount_received)
    const amountInput = document.querySelector('input[name="so_tien"]') ||
                       document.querySelector('input[name="amount"]') ||
                       document.querySelector('input[placeholder*="số tiền"]');
    console.log('🔍 Amount input found:', amountInput);
    
    if (amountInput && data.amount_received) {
        // Use actual amount received, not the original price
        const cleanAmount = data.amount_received.toString().replace(/[^\d]/g, '');
        amountInput.value = cleanAmount;
        amountInput.dispatchEvent(new Event('input', { bubbles: true }));
        amountInput.dispatchEvent(new Event('change', { bubbles: true }));
        highlightField(amountInput);
        filledCount++;
        console.log('✅ Số tiền:', cleanAmount);
    } else {
        console.log('❌ Không tìm thấy trường số tiền');
    }
    
    // 6. Loại chuyển khoản (transfer_type)
    const transferTypeSelect = document.querySelector('select[name="loai_chuyen_khoan"]') ||
                              document.querySelector('select[name="transfer_type"]') ||
                              document.querySelector('select[placeholder*="loại chuyển khoản"]');
    console.log('🔍 Transfer type select found:', transferTypeSelect);
    
    if (transferTypeSelect && data.transfer_type) {
        // Tìm option khớp với transfer_type
        const options = transferTypeSelect.querySelectorAll('option');
        for (let option of options) {
            const optionText = option.text.toLowerCase();
            const transferTypeLower = data.transfer_type.toLowerCase();
            
            if (optionText.includes(transferTypeLower) || 
                transferTypeLower.includes(optionText) ||
                option.value.toLowerCase() === transferTypeLower) {
                transferTypeSelect.value = option.value;
                transferTypeSelect.dispatchEvent(new Event('input', { bubbles: true }));
                transferTypeSelect.dispatchEvent(new Event('change', { bubbles: true }));
                highlightField(transferTypeSelect);
                filledCount++;
                console.log('✅ Loại chuyển khoản:', data.transfer_type, '→', option.text);
                break;
            }
        }
    } else {
        console.log('❌ Không tìm thấy trường loại chuyển khoản');
    }
    
    console.log(`✅ Đã điền ${filledCount}/6 trường thành công!`);

    
    // Show summary alert
    showSuccessMessage(data, filledCount);
}

// Helper function to highlight filled fields
function highlightField(element) {
    element.style.backgroundColor = '#d4edda';
    element.style.borderColor = '#28a745';
    element.style.transition = 'all 0.3s ease';
    
    setTimeout(() => {
        element.style.backgroundColor = '';
        element.style.borderColor = '';
    }, 3000);
}

// Helper function to show success message
function showSuccessMessage(data, count) {
    const today = new Date().toLocaleDateString('vi-VN');
    const amount = data.amount_received ? 
        Number(data.amount_received.toString().replace(/[^\d]/g, '')).toLocaleString('vi-VN') : '0';
    
    const message = `
✅ ĐÃ ĐIỀN ${count} TRƯỜNG TỰ ĐỘNG

📅 Ngày đóng: ${today}
👤 Học viên: ${data.student_name || 'N/A'}
📚 Khóa học: ${data.course_name || 'N/A'}
💰 Số tiền: ${amount} đ
🧾 Mã phiếu: ${data.bill_no || 'N/A'}

⚠️ VUI LÒNG KIỂM TRA:
- Loại chuyển khoản
- Nơi dụng chuyển khoản  
- Ảnh xác nhận (nếu có)
    `.trim();
    
    alert(message);
}

// Hàm helper để set giá trị cho input/textarea
function setFieldValue(selector, value) {
    if (!value) return;
    
    const element = document.querySelector(selector);
    if (element) {
        element.value = value;
        
        // Trigger events để form validation hoạt động
        element.dispatchEvent(new Event('input', { bubbles: true }));
        element.dispatchEvent(new Event('change', { bubbles: true }));
        element.dispatchEvent(new Event('blur', { bubbles: true }));
        
        console.log(`Filled ${selector} with: ${value}`);
    }
}

// Hàm helper để highlight các trường đã điền
function highlightFilledFields() {
    const fields = document.querySelectorAll('input[name="student_name"], input[name="username"], input[name="phone"], input[name="email"], input[name="subject"]');
    
    fields.forEach(field => {
        if (field.value) {
            field.style.transition = 'background-color 0.5s ease';
            field.style.backgroundColor = '#d4edda';
            
            // Reset màu sau 2 giây
            setTimeout(() => {
                field.style.backgroundColor = '';
            }, 2000);
        }
    });
}

// Tự động phát hiện form khi load trang
if (window.location.href.includes('certificates/create')) {
    console.log('Certificate Auto Fill Extension: Ready to fill form');
}
