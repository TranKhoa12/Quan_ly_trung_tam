// Content script để trích xuất dữ liệu từ form đăng ký học viên
// Script này chạy trên trang hocvien.tinhocsaoviet.com/register.php và edit-profile-admin.php

console.log('✅ Extract.js loaded');

// Lắng nghe message từ popup
chrome.runtime.onMessage.addListener((request, sender, sendResponse) => {
    console.log('📨 Received message:', request);
    
    if (request.action === 'extractData') {
        try {
            console.log('🔄 Extracting data...');
            const data = extractFormData();
            console.log('✅ Data extracted:', data);
            sendResponse({ success: true, data: data });
        } catch (error) {
            console.error('❌ Error:', error);
            sendResponse({ success: false, error: error.message });
        }
    }
    return true;
});

// Phát hiện trang hiện tại
function detectCurrentPage() {
    const url = window.location.href;
    if (url.includes('edit-profile-admin.php')) {
        return 'edit-profile';
    } else if (url.includes('register.php')) {
        return 'register';
    }
    return 'unknown';
}

// Hàm trích xuất dữ liệu từ form
function extractFormData() {
    console.log('🔍 Starting extraction...');
    
    const pageType = detectCurrentPage();
    console.log('📄 Page type:', pageType);
    
    // Tìm học phí
    let tuitionFee = '0';
    const priceHidden = document.querySelector('input[name="price_"]');
    if (priceHidden && priceHidden.value && priceHidden.value !== '0') {
        tuitionFee = priceHidden.value;
        console.log(`💰 Price: ${tuitionFee}`);
    }
    
    // Lấy thực thu
    let actualReceived = tuitionFee;
    const amountReceivedHidden = document.querySelector('input[name="amount_received_"]');
    if (amountReceivedHidden && amountReceivedHidden.value && amountReceivedHidden.value !== '0') {
        actualReceived = amountReceivedHidden.value;
        console.log(`💵 Amount received: ${actualReceived}`);
    }
    
    // Lấy giảm giá
    let discount = '0';
    const discountHidden = document.querySelector('input[name="discount_"]');
    if (discountHidden && discountHidden.value) {
        discount = discountHidden.value;
    }
    
    // Xác định loại chuyển khoản
    const paymentMethodText = getSelectText('select[name="payment_method"]');
    let transferType = 'Tiền mặt';
    
    console.log(`💳 Payment method: ${paymentMethodText}`);
    
    if (paymentMethodText && paymentMethodText.toLowerCase().includes('tiền mặt')) {
        transferType = 'Tiền mặt';
    } else if (paymentMethodText && paymentMethodText.toLowerCase().includes('chuyển khoản')) {
        const companyCheckbox = document.querySelector('input[name="is_paid_to_company"]');
        console.log(`🏢 Company checkbox checked: ${companyCheckbox?.checked}`);
        
        if (companyCheckbox && companyCheckbox.checked) {
            transferType = 'Tài Khoản Công ty';
        } else {
            transferType = 'Tài khoản Thầy Hiến';
        }
    }
    
    console.log(`✅ Transfer type: ${transferType}`);
    
    // Thu thập tất cả dữ liệu
    const formData = {
        student_name: getFieldValue('input[name="f-name"]') || getFieldValue('input[name="f_name"]'),
        username: getFieldValue('input[name="uid"]'),
        phone: getFieldValue('input[name="phone"]'),
        address: getFieldValue('input[name="address"]'),
        course_id: getFieldValue('select[name="id_course"]'),
        course_name: getSelectText('select[name="id_course"]'),
        price: tuitionFee,
        amount_received: actualReceived,
        discount: discount,
        debt_amount: getFieldValue('input[name="debt_amount_"]') || '0',
        bill_no: getFieldValue('input[name="bill_no"]'),
        payment_date: getFieldValue('input[name="payment_date"]'),
        start_date: getFieldValue('input[name="start_date"]'),
        teacher_id: getFieldValue('select[name="id_teacher"]'),
        teacher_name: getSelectText('select[name="id_teacher"]'),
        payment_method: paymentMethodText,
        transfer_type: transferType,
        study_schedule: getSelectText('select[name="study_schedule"]'),
        study_shift: getSelectText('select[name="study_shift"]'),
        study_sessions: getFieldValue('input[name="study_sessions"]'),
        lichhoc: getFieldValue('input[name="lichhoc"]'),
        source_contact: getSelectText('select[name="source_contact"]'),
        employee: getSelectText('select[name="employee"]'),
        extracted_at: new Date().toLocaleString('vi-VN'),
        page_type: pageType
    };
    
    console.log('✅ Final data:', formData);
    return formData;
}

// Helper: Lấy giá trị input/textarea
function getFieldValue(selector) {
    const element = document.querySelector(selector);
    return element ? element.value.trim() : '';
}

// Helper: Lấy text từ select option
function getSelectText(selector) {
    const element = document.querySelector(selector);
    if (element && element.selectedOptions && element.selectedOptions.length > 0) {
        const text = element.selectedOptions[0].text.trim();
        return (text === '---' || text === '') ? '' : text;
    }
    return '';
}

console.log('✅ Extract.js ready');
