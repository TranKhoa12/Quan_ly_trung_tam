            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    
    <script>
        // Auto-hide alerts after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
            alerts.forEach(function(alert) {
                setTimeout(function() {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }, 5000);
            });
        });

        // Confirm delete functions
        function confirmDelete(message = 'Bạn có chắc chắn muốn xóa?') {
            return confirm(message + '\nHành động này không thể hoàn tác!');
        }

        // Loading state for buttons
        function setLoadingState(button, loading = true) {
            if (loading) {
                button.disabled = true;
                const originalHtml = button.innerHTML;
                button.dataset.originalHtml = originalHtml;
                button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang xử lý...';
            } else {
                button.disabled = false;
                button.innerHTML = button.dataset.originalHtml || button.innerHTML;
            }
        }

        // Format number with thousand separators
        function formatNumber(num) {
            return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        }

        // Format currency
        function formatCurrency(amount) {
            return formatNumber(amount) + ' VNĐ';
        }

        // Validate date range
        function validateDateRange(startDate, endDate) {
            if (!startDate || !endDate) return true;
            return new Date(startDate) <= new Date(endDate);
        }

        // Show toast notification
        function showToast(message, type = 'success') {
            const toastContainer = document.getElementById('toast-container') || createToastContainer();
            const toast = createToast(message, type);
            toastContainer.appendChild(toast);
            
            const bsToast = new bootstrap.Toast(toast);
            bsToast.show();
            
            toast.addEventListener('hidden.bs.toast', function() {
                toast.remove();
            });
        }

        function createToastContainer() {
            const container = document.createElement('div');
            container.id = 'toast-container';
            container.className = 'toast-container position-fixed top-0 end-0 p-3';
            container.style.zIndex = '9999';
            document.body.appendChild(container);
            return container;
        }

        function createToast(message, type) {
            const toast = document.createElement('div');
            toast.className = 'toast';
            toast.setAttribute('role', 'alert');
            
            const iconMap = {
                'success': 'fas fa-check-circle text-success',
                'error': 'fas fa-exclamation-circle text-danger',
                'warning': 'fas fa-exclamation-triangle text-warning',
                'info': 'fas fa-info-circle text-info'
            };
            
            toast.innerHTML = `
                <div class="toast-header">
                    <i class="${iconMap[type] || iconMap.info} me-2"></i>
                    <strong class="me-auto">Thông báo</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
                </div>
                <div class="toast-body">
                    ${message}
                </div>
            `;
            
            return toast;
        }
    </script>
</body>
</html>