<?php
if (!defined('TRANSFER_STYLES_LOADED')) {
    define('TRANSFER_STYLES_LOADED', true);
    ?>
    <style>
    :root {
        --transfer-gradient-start: #667eea;
        --transfer-gradient-end: #764ba2;
        --transfer-border-radius: 12px;
        --transfer-border-color: #e9ecef;
    }

    .transfer-page .gradient-header {
        background: linear-gradient(135deg, var(--transfer-gradient-start), var(--transfer-gradient-end));
        border: none;
        padding: 1.5rem;
    }

    .transfer-page .card {
        border-radius: var(--transfer-border-radius);
        border: none;
        box-shadow: 0 12px 30px rgba(102, 126, 234, 0.08);
    }

    .transfer-page .card-header {
        border: none;
    }

    .transfer-page .alert,
    .transfer-page .transfer-box,
    .transfer-page .timeline-content {
        border-radius: 10px;
    }

    .transfer-page .form-select,
    .transfer-page .form-control,
    .transfer-page textarea {
        border-radius: 8px;
        border: 2px solid var(--transfer-border-color);
        padding: 0.75rem 1rem;
        transition: all 0.3s ease;
    }

    .transfer-page .form-select:focus,
    .transfer-page .form-control:focus,
    .transfer-page textarea:focus {
        border-color: var(--transfer-gradient-start);
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
    }

    .transfer-page .btn {
        border-radius: 8px;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .transfer-page .btn-group-vertical .btn {
        border-radius: 6px !important;
    }

    .transfer-page .btn-primary,
    .transfer-page .gradient-button {
        background: linear-gradient(135deg, var(--transfer-gradient-start), var(--transfer-gradient-end));
        border: none;
        color: #fff;
    }

    .transfer-page .btn-primary:hover,
    .transfer-page .gradient-button:hover {
        transform: translateY(-1px);
        box-shadow: 0 6px 16px rgba(102, 126, 234, 0.35);
    }

    .transfer-page .transfer-table {
        margin-bottom: 0;
    }

    .transfer-page .transfer-table thead th {
        font-weight: 600;
        font-size: 0.875rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-bottom: 2px solid #dee2e6;
    }

    .transfer-page .badge,
    .transfer-page .status-badge {
        padding: 0.45rem 0.75rem;
        font-weight: 500;
        border-radius: 999px;
    }

    .transfer-page .transfer-box {
        border: 2px solid var(--transfer-border-color);
        overflow: hidden;
    }

    .transfer-page .transfer-header {
        padding: 0.75rem 1rem;
        font-weight: 600;
        background: #f8f9fa;
    }

    .transfer-page .from-box .transfer-header {
        background: #fff3cd;
        color: #856404;
    }

    .transfer-page .to-box .transfer-header {
        background: #d1ecf1;
        color: #0c5460;
    }

    .transfer-page .transfer-body {
        padding: 1.5rem;
    }

    .transfer-page .timeline {
        position: relative;
        padding: 1.5rem 1rem;
    }

    .transfer-page .timeline-item {
        position: relative;
        padding-left: 2.5rem;
        padding-bottom: 1.5rem;
    }

    .transfer-page .timeline-item:not(.last)::before {
        content: '';
        position: absolute;
        left: 0.6rem;
        top: 1.5rem;
        bottom: -0.5rem;
        width: 2px;
        background: #dee2e6;
    }

    .transfer-page .timeline-marker {
        position: absolute;
        left: 0;
        top: 0;
        width: 1.5rem;
        height: 1.5rem;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .transfer-page .timeline-marker i {
        font-size: 1.2rem;
    }

    .transfer-page .timeline-content {
        background: #f8f9fa;
        padding: 0.75rem 1rem;
        border-left: 3px solid var(--transfer-gradient-start);
    }

    .transfer-page .empty-state {
        text-align: center;
        padding: 4rem 1rem;
    }

    .transfer-page .empty-state i {
        color: #cbd5f5;
    }
    </style>
    <?php
}
?>
