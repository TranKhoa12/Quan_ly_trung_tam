document.addEventListener('DOMContentLoaded', function() {
    const extractBtn = document.getElementById('extractBtn');
    const fillBtn = document.getElementById('fillBtn');
    const clearBtn = document.getElementById('clearBtn');
    const savedDataDiv = document.getElementById('savedData');
    const statusDiv = document.getElementById('status');

    // Load và hiển thị dữ liệu đã lưu
    loadSavedData();

    // Lấy dữ liệu từ form đăng ký
    extractBtn.addEventListener('click', async () => {
        try {
            showStatus('⏳ Đang lấy dữ liệu...', 'info');
            
            const [tab] = await chrome.tabs.query({ active: true, currentWindow: true });
            
            console.log('Current URL:', tab.url);
            
            // Kiểm tra URL - phải là trang register.php hoặc edit-profile-admin.php
            if (!tab.url.includes('register.php') && !tab.url.includes('edit-profile-admin.php')) {
                showStatus('❌ Vui lòng mở trang register.php hoặc edit-profile-admin.php', 'error');
                return;
            }

            showStatus('⏳ Đang trích xuất dữ liệu...', 'info');

            // Gửi message trực tiếp (content script đã được auto-inject)
            chrome.tabs.sendMessage(tab.id, { action: 'extractData' }, (response) => {
                if (chrome.runtime.lastError) {
                    console.error('Message error:', chrome.runtime.lastError);
                    showStatus('❌ Lỗi: ' + chrome.runtime.lastError.message + '. Vui lòng reload trang (F5)', 'error');
                    return;
                }

                if (response && response.success) {
                    console.log('✅ Dữ liệu nhận được:', response.data);
                    
                    // Lưu vào storage
                    chrome.storage.local.set({ certificateData: response.data }, () => {
                        showStatus('✅ Đã lấy dữ liệu thành công!', 'success');
                        loadSavedData();
                    });
                } else {
                    console.error('❌ Response lỗi:', response);
                    showStatus('❌ Không thể lấy dữ liệu: ' + (response?.error || 'Lỗi không xác định'), 'error');
                }
            });
        } catch (error) {
            console.error('❌ Exception:', error);
            showStatus('❌ Lỗi: ' + error.message, 'error');
        }
    });

    // Điền dữ liệu vào form chứng nhận
    fillBtn.addEventListener('click', async () => {
        try {
            // Lấy dữ liệu đã lưu
            const result = await chrome.storage.local.get(['certificateData']);
            
            if (!result.certificateData) {
                showStatus('❌ Chưa có dữ liệu. Vui lòng lấy dữ liệu trước', 'error');
                return;
            }

            // Lưu flag để content script biết cần tự động điền
            await chrome.storage.local.set({ 
                autoFillPending: true,
                autoFillData: result.certificateData 
            });

            // Mở tab mới với trang revenue/create
            const targetUrl = 'https://bt.banhmibebe.com/Quan_ly_trung_tam/public/revenue/create';
            await chrome.tabs.create({ url: targetUrl, active: true });
            
            showStatus('✅ Đang mở trang, dữ liệu sẽ tự động điền...', 'success');
            
        } catch (error) {
            console.error('❌ Lỗi:', error);
            showStatus('❌ Lỗi: ' + error.message, 'error');
        }
    });

    // Xóa dữ liệu
    clearBtn.addEventListener('click', async () => {
        if (confirm('Bạn có chắc muốn xóa dữ liệu đã lưu?')) {
            await chrome.storage.local.remove(['certificateData']);
            showStatus('🗑️ Đã xóa dữ liệu', 'success');
            loadSavedData();
        }
    });

    // Hàm hiển thị dữ liệu đã lưu
    async function loadSavedData() {
        const result = await chrome.storage.local.get(['certificateData']);
        
        if (result.certificateData) {
            const data = result.certificateData;
            const today = new Date().toLocaleDateString('vi-VN');
            const amount = data.amount_received ? 
                Number(data.amount_received.toString().replace(/[^\d]/g, '')).toLocaleString('vi-VN') : '0';
            
            savedDataDiv.innerHTML = `
                <div class="data-item">
                    <span class="data-label">📅 Ngày đóng:</span>
                    <span class="data-value">${today}</span>
                </div>
                <div class="data-item">
                    <span class="data-label">👤 Họ tên:</span>
                    <span class="data-value">${data.student_name || 'N/A'}</span>
                </div>
                <div class="data-item">
                    <span class="data-label">📚 Khóa học:</span>
                    <span class="data-value">${data.course_name || 'N/A'}</span>
                </div>
                <div class="data-item">
                    <span class="data-label">💰 Số tiền:</span>
                    <span class="data-value">${amount} đ</span>
                </div>
                <div class="data-item">
                    <span class="data-label">🧾 Mã phiếu:</span>
                    <span class="data-value">${data.bill_no || 'N/A'}</span>
                </div>
                <div class="data-item">
                    <span class="data-label">� Loại CK:</span>
                    <span class="data-value">${data.transfer_type || 'N/A'}</span>
                </div>
                <div class="data-item">
                    <span class="data-label">�📞 Điện thoại:</span>
                    <span class="data-value">${data.phone || 'N/A'}</span>
                </div>
                <div class="data-item">
                    <span class="data-label">⏰ Lấy lúc:</span>
                    <span class="data-value">${data.extracted_at || 'N/A'}</span>
                </div>
            `;
            fillBtn.disabled = false;
        } else {
            savedDataDiv.innerHTML = '<p class="empty">Chưa có dữ liệu</p>';
            fillBtn.disabled = true;
        }
    }

    // Hàm hiển thị status
    function showStatus(message, type) {
        statusDiv.textContent = message;
        statusDiv.className = `status show ${type}`;
        
        setTimeout(() => {
            statusDiv.classList.remove('show');
        }, 3000);
    }
});
