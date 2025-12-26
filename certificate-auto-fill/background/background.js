// Background Service Worker cho Certificate Auto Fill Extension
// Xử lý logic backend và giao tiếp giữa các components

// Lắng nghe sự kiện cài đặt extension
chrome.runtime.onInstalled.addListener((details) => {
    console.log('Certificate Auto Fill Extension installed:', details.reason);
    
    if (details.reason === 'install') {
        // Khởi tạo storage khi cài đặt lần đầu
        chrome.storage.local.set({
            certificateData: null,
            settings: {
                autoHighlight: true,
                autoSave: true
            }
        });
        
        // Mở trang hướng dẫn (optional)
        // chrome.tabs.create({ url: 'popup/popup.html' });
    }
});

// Lắng nghe tin nhắn từ content scripts và popup
chrome.runtime.onMessage.addListener((request, sender, sendResponse) => {
    console.log('Background received message:', request.action);
    
    switch (request.action) {
        case 'saveData':
            handleSaveData(request.data, sendResponse);
            break;
            
        case 'getData':
            handleGetData(sendResponse);
            break;
            
        case 'clearData':
            handleClearData(sendResponse);
            break;
            
        case 'extractFromTab':
            handleExtractFromTab(request.tabId, sendResponse);
            break;
            
        case 'fillToTab':
            handleFillToTab(request.tabId, request.data, sendResponse);
            break;
            
        default:
            sendResponse({ success: false, error: 'Unknown action' });
    }
    
    return true; // Keep message channel open for async response
});

// Xử lý lưu dữ liệu
function handleSaveData(data, sendResponse) {
    chrome.storage.local.set({ certificateData: data }, () => {
        if (chrome.runtime.lastError) {
            sendResponse({ success: false, error: chrome.runtime.lastError.message });
        } else {
            console.log('Data saved successfully');
            sendResponse({ success: true });
        }
    });
}

// Xử lý lấy dữ liệu
function handleGetData(sendResponse) {
    chrome.storage.local.get(['certificateData'], (result) => {
        if (chrome.runtime.lastError) {
            sendResponse({ success: false, error: chrome.runtime.lastError.message });
        } else {
            sendResponse({ success: true, data: result.certificateData });
        }
    });
}

// Xử lý xóa dữ liệu
function handleClearData(sendResponse) {
    chrome.storage.local.remove(['certificateData'], () => {
        if (chrome.runtime.lastError) {
            sendResponse({ success: false, error: chrome.runtime.lastError.message });
        } else {
            console.log('Data cleared successfully');
            sendResponse({ success: true });
        }
    });
}

// Xử lý trích xuất dữ liệu từ tab
async function handleExtractFromTab(tabId, sendResponse) {
    try {
        const response = await chrome.tabs.sendMessage(tabId, { action: 'extractData' });
        
        if (response.success) {
            // Lưu dữ liệu vào storage
            await chrome.storage.local.set({ certificateData: response.data });
            sendResponse({ success: true, data: response.data });
        } else {
            sendResponse({ success: false, error: response.error });
        }
    } catch (error) {
        sendResponse({ success: false, error: error.message });
    }
}

// Xử lý điền dữ liệu vào tab
async function handleFillToTab(tabId, data, sendResponse) {
    try {
        const response = await chrome.tabs.sendMessage(tabId, { 
            action: 'fillData', 
            data: data 
        });
        
        sendResponse(response);
    } catch (error) {
        sendResponse({ success: false, error: error.message });
    }
}

// Lắng nghe khi tab được update (optional - để auto-detect form)
chrome.tabs.onUpdated.addListener((tabId, changeInfo, tab) => {
    if (changeInfo.status === 'complete') {
        // Kiểm tra nếu là trang đăng ký hoặc tạo chứng nhận
        if (tab.url && (tab.url.includes('register.php') || tab.url.includes('certificates/create'))) {
            console.log('Detected certificate-related page:', tab.url);
            
            // Có thể hiển thị badge notification
            chrome.action.setBadgeText({ text: '!', tabId: tabId });
            chrome.action.setBadgeBackgroundColor({ color: '#4CAF50', tabId: tabId });
        }
    }
});

// Context menu (optional - right-click menu)
chrome.runtime.onInstalled.addListener(() => {
    chrome.contextMenus.create({
        id: 'extractData',
        title: 'Lấy dữ liệu học viên',
        contexts: ['page'],
        documentUrlPatterns: ['*://hocvien.tinhocsaoviet.com/register.php*']
    });
    
    chrome.contextMenus.create({
        id: 'fillData',
        title: 'Điền dữ liệu chứng nhận',
        contexts: ['page'],
        documentUrlPatterns: ['*://localhost/*/certificates/create*', '*://127.0.0.1/*/certificates/create*']
    });
});

// Xử lý context menu clicks
chrome.contextMenus.onClicked.addListener((info, tab) => {
    if (info.menuItemId === 'extractData') {
        chrome.tabs.sendMessage(tab.id, { action: 'extractData' });
    } else if (info.menuItemId === 'fillData') {
        chrome.storage.local.get(['certificateData'], (result) => {
            if (result.certificateData) {
                chrome.tabs.sendMessage(tab.id, { 
                    action: 'fillData', 
                    data: result.certificateData 
                });
            }
        });
    }
});
