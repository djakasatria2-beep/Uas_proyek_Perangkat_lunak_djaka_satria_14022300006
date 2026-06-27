<?php
// ============================================================
//  buyer_panel/partials/overdue-banner.php
//  Banner peringatan invoice overdue untuk panel Buyer.
//  Membutuhkan: $hasOverdue, BUYER_URL (dari config.php)
//  Statis (tidak bisa ditutup user) — tampil selama masih overdue.
// ============================================================

if (empty($hasOverdue)) {
    return; // Tidak ada overdue, banner tidak ditampilkan
}
?>
<div class="bp-overdue-banner" role="alert">
    <div class="bp-overdue-banner__icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="20" height="20">
            <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
            <line x1="12" y1="9" x2="12" y2="13"></line>
            <line x1="12" y1="17" x2="12.01" y2="17"></line>
        </svg>
    </div>
    <div class="bp-overdue-banner__text">
        <strong>Perhatian:</strong> Anda memiliki invoice yang telah melewati jatuh tempo. Segera lakukan pembayaran untuk menghindari keterlambatan lebih lanjut.
    </div>
    <a href="<?= BUYER_URL ?>/invoices.php?status=overdue" class="bp-overdue-banner__cta">
        Lihat Invoice
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="16" height="16">
            <line x1="5" y1="12" x2="19" y2="12"></line>
            <polyline points="12 5 19 12 12 19"></polyline>
        </svg>
    </a>
</div>

<style>
    .bp-overdue-banner {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-left: 240px;
        padding: 12px 24px;
        background: #fef2f2;
        border-bottom: 1px solid #fecaca;
        color: #991b1b;
    }

    .bp-overdue-banner__icon {
        flex-shrink: 0;
        display: flex;
        align-items: center;
    }

    .bp-overdue-banner__text {
        flex: 1;
        min-width: 0;
        font-size: 13.5px;
        line-height: 1.4;
    }

    .bp-overdue-banner__cta {
        flex-shrink: 0;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 7px 14px;
        background: #dc2626;
        color: #ffffff;
        border-radius: 8px;
        text-decoration: none;
        font-size: 13px;
        font-weight: 600;
        white-space: nowrap;
        transition: background 0.15s ease;
    }
    .bp-overdue-banner__cta:hover { background: #b91c1c; }

    /* ====== Responsive ====== */
    @media (max-width: 900px) {
        .bp-overdue-banner { margin-left: 0; }
    }

    @media (max-width: 640px) {
        .bp-overdue-banner {
            flex-wrap: wrap;
            padding: 12px 16px;
        }
        .bp-overdue-banner__text {
            flex: 1 1 100%;
            order: 2;
        }
        .bp-overdue-banner__cta {
            order: 3;
            margin-left: auto;
        }
        .bp-overdue-banner__icon { order: 1; }
    }
</style>