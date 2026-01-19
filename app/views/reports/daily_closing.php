<?php
// Ensure layout helpers (buildUrl, useModernLayout) are available before rendering content
require_once __DIR__ . '/../layouts/main.php';

$pageTitle = 'Báo cáo chốt hàng ngày';
ob_start();
?>

<div class="page-header">
    <div class="page-header-content">
        <div>
            <h1 class="page-title">
                <i class="fas fa-file-contract me-2"></i>
                Báo cáo chốt hàng ngày
            </h1>
            <p class="page-subtitle">Xem báo cáo tổng hợp số lượng đến, chốt và doanh thu trong ngày</p>
        </div>
    </div>
</div>

<div class="page-content">
    <?php if (isset($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>
            <?= htmlspecialchars($error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Date Picker Card -->
    <div class="card modern-card mb-4">
        <div class="card-body">
            <form method="GET" action="<?= buildUrl('reports/daily-closing') ?>" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label for="reportDate" class="form-label fw-semibold">
                        <i class="fas fa-calendar-alt me-2 text-primary"></i>
                        Chọn ngày báo cáo
                    </label>
                    <input 
                        type="date" 
                        class="form-control form-control-lg" 
                        id="reportDate" 
                        name="date" 
                        value="<?= htmlspecialchars($date ?? date('Y-m-d')) ?>"
                        max="<?= date('Y-m-d') ?>">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary btn-lg w-100">
                        <i class="fas fa-sync-alt me-2"></i>Cập nhật
                    </button>
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-success btn-lg w-100" onclick="copyReport()">
                        <i class="fas fa-copy me-2"></i>Sao chép
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="stat-card stat-card-primary">
                <div class="stat-card-body">
                    <div class="stat-card-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-card-content">
                        <div class="stat-card-label">SL đến</div>
                        <div class="stat-card-value"><?= isset($studentStats) ? $studentStats['arrivals'] : 0 ?></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="stat-card stat-card-success">
                <div class="stat-card-body">
                    <div class="stat-card-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-card-content">
                        <div class="stat-card-label">SL chốt</div>
                        <div class="stat-card-value"><?= isset($studentStats) ? $studentStats['closed'] : 0 ?></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="stat-card stat-card-warning">
                <div class="stat-card-body">
                    <div class="stat-card-icon">
                        <i class="fas fa-file-invoice-dollar"></i>
                    </div>
                    <div class="stat-card-content">
                        <div class="stat-card-label">Tổng giao dịch</div>
                        <div class="stat-card-value"><?= isset($revenueStats) ? $revenueStats['total_count'] : 0 ?></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="stat-card stat-card-info">
                <div class="stat-card-body">
                    <div class="stat-card-icon">
                        <i class="fas fa-calendar-day"></i>
                    </div>
                    <div class="stat-card-content">
                        <div class="stat-card-label">Ngày báo cáo</div>
                        <div class="stat-card-value" style="font-size: 1.5rem;"><?= date('d/m/Y', strtotime($date ?? date('Y-m-d'))) ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Report Text Display -->
    <div class="card modern-card mb-4">
        <div class="card-header bg-gradient-primary text-white">
            <h5 class="mb-0">
                <i class="fas fa-file-alt me-2"></i>
                Nội dung báo cáo
            </h5>
        </div>
        <div class="card-body p-4">
            <div class="report-text-container">
                <pre id="reportText" class="report-text mb-0"><?= htmlspecialchars($reportText ?? '') ?></pre>
            </div>
        </div>
    </div>

    <!-- Revenue Details Table -->
    <?php if (isset($revenueStats) && !empty($revenueStats['details'])): ?>
    <div class="card modern-card">
        <div class="card-header bg-light">
            <h5 class="mb-0">
                <i class="fas fa-list-alt me-2 text-primary"></i>
                Chi tiết giao dịch
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="100" class="text-center">STT</th>
                            <th>Khóa học</th>
                            <th>Loại thanh toán</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($revenueStats['details'] as $index => $detail): ?>
                        <tr>
                            <td class="text-center fw-bold"><?= str_pad($index + 1, 2, '0', STR_PAD_LEFT) ?></td>
                            <td>
                                <i class="fas fa-book text-primary me-2"></i>
                                <?= htmlspecialchars($detail['course_name'] ?? 'Chưa xác định') ?>
                            </td>
                            <td>
                                <span class="badge bg-info">
                                    <?= htmlspecialchars($detail['payment_content'] ?? 'Không rõ') ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<style>
/* Modern Card Styles */
.modern-card {
    border: none;
    border-radius: 16px;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    overflow: hidden;
    transition: all 0.3s ease;
}

.modern-card:hover {
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    transform: translateY(-2px);
}

/* Stat Cards */
.stat-card {
    border-radius: 16px;
    border: none;
    overflow: hidden;
    transition: all 0.3s ease;
    height: 100%;
}

.stat-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
}

.stat-card-body {
    padding: 0.75rem 1rem;
    display: flex;
    align-items: center;
    gap: 0.65rem;
}

.stat-card-icon {
    width: 24px;
    height: 24px;
    border-radius: 7px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    flex-shrink: 0;
}

.stat-card-content {
    flex: 1;
}

.stat-card-label {
    font-size: 0.72rem;
    font-weight: 500;
    color: #6b7280;
    margin-bottom: 0.25rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.stat-card-value {
    font-size: 1.6rem;
    font-weight: 700;
    line-height: 1;
}

/* Stat Card Variants */
.stat-card-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.stat-card-primary .stat-card-icon {
    background: rgba(255, 255, 255, 0.2);
    color: white;
}

.stat-card-primary .stat-card-label {
    color: rgba(255, 255, 255, 0.9);
}

.stat-card-success {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: white;
}

.stat-card-success .stat-card-icon {
    background: rgba(255, 255, 255, 0.2);
    color: white;
}

.stat-card-success .stat-card-label {
    color: rgba(255, 255, 255, 0.9);
}

.stat-card-warning {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    color: white;
}

.stat-card-warning .stat-card-icon {
    background: rgba(255, 255, 255, 0.2);
    color: white;
}

.stat-card-warning .stat-card-label {
    color: rgba(255, 255, 255, 0.9);
}

.stat-card-info {
    background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
    color: white;
}

.stat-card-info .stat-card-icon {
    background: rgba(255, 255, 255, 0.2);
    color: white;
}

.stat-card-info .stat-card-label {
    color: rgba(255, 255, 255, 0.9);
}

/* Report Text Container */
.report-text-container {
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    border: 2px solid #e2e8f0;
    border-radius: 12px;
    padding: 2rem;
    position: relative;
    overflow: hidden;
}

.report-text-container::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 4px;
    height: 100%;
    background: linear-gradient(180deg, #667eea 0%, #764ba2 100%);
}

.report-text {
    font-family: 'Courier New', 'Consolas', monospace;
    font-size: 15px;
    line-height: 2;
    color: #1e293b;
    white-space: pre-wrap;
    word-wrap: break-word;
    font-weight: 500;
    padding-left: 1rem;
}

/* Card Header Gradient */
.bg-gradient-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

/* Table Styles */
.table thead th {
    font-weight: 600;
    font-size: 0.875rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: #475569;
    border-bottom: 2px solid #e2e8f0;
    padding: 1rem;
}

.table tbody td {
    padding: 1rem;
    vertical-align: middle;
    color: #334155;
}

.table-hover tbody tr {
    transition: all 0.2s ease;
}

.table-hover tbody tr:hover {
    background-color: #f8fafc;
    transform: scale(1.01);
}

/* Buttons */
.btn-lg {
    padding: 0.75rem 1.5rem;
    font-weight: 600;
    border-radius: 10px;
}

.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
}

.btn-primary:hover {
    background: linear-gradient(135deg, #5568d3 0%, #653a8b 100%);
    transform: translateY(-2px);
    box-shadow: 0 10px 15px -3px rgba(102, 126, 234, 0.4);
}

.btn-success {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    border: none;
}

.btn-success:hover {
    background: linear-gradient(135deg, #059669 0%, #047857 100%);
    transform: translateY(-2px);
    box-shadow: 0 10px 15px -3px rgba(16, 185, 129, 0.4);
}

/* Form Controls */
.form-control-lg {
    border-radius: 10px;
    border: 2px solid #e2e8f0;
    padding: 0.75rem 1rem;
    font-size: 1rem;
    transition: all 0.3s ease;
}

.form-control-lg:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

/* Badge */
.badge {
    padding: 0.5rem 1rem;
    font-weight: 600;
    font-size: 0.75rem;
    border-radius: 6px;
}

/* Animation */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.stat-card, .modern-card {
    animation: fadeInUp 0.5s ease-out;
}

.stat-card:nth-child(1) { animation-delay: 0.1s; }
.stat-card:nth-child(2) { animation-delay: 0.2s; }
.stat-card:nth-child(3) { animation-delay: 0.3s; }
.stat-card:nth-child(4) { animation-delay: 0.4s; }
</style>

<script>
function copyReport() {
    const reportText = document.getElementById('reportText').textContent;
    
    // Use modern clipboard API
    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(reportText).then(() => {
            showToast('success', 'Đã sao chép báo cáo vào clipboard!');
        }).catch(err => {
            // Fallback to old method
            fallbackCopy(reportText);
        });
    } else {
        fallbackCopy(reportText);
    }
}

function fallbackCopy(text) {
    const textarea = document.createElement('textarea');
    textarea.value = text;
    textarea.style.position = 'fixed';
    textarea.style.opacity = '0';
    document.body.appendChild(textarea);
    textarea.select();
    
    try {
        document.execCommand('copy');
        showToast('success', 'Đã sao chép báo cáo vào clipboard!');
    } catch (err) {
        showToast('error', 'Không thể sao chép. Vui lòng thử lại!');
    }
    
    document.body.removeChild(textarea);
}

function showToast(type, message) {
    const toastContainer = document.getElementById('globalToastContainer');
    if (!toastContainer) return;
    
    const toastId = 'toast-' + Date.now();
    const iconClass = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
    const bgClass = type === 'success' ? 'bg-success' : 'bg-danger';
    
    const toastHTML = `
        <div id="${toastId}" class="toast align-items-center text-white ${bgClass} border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    <i class="fas ${iconClass} me-2"></i>
                    ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    `;
    
    toastContainer.insertAdjacentHTML('beforeend', toastHTML);
    
    const toastElement = document.getElementById(toastId);
    const toast = new bootstrap.Toast(toastElement, { delay: 3000 });
    toast.show();
    
    toastElement.addEventListener('hidden.bs.toast', () => {
        toastElement.remove();
    });
}
</script>

<?php
$content = ob_get_clean();
useModernLayout($pageTitle, $content);
?>