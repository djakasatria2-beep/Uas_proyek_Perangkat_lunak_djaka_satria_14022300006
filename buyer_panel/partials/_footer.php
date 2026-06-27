<?php
// ============================================================
//  buyer_panel/partials/_footer.php
//  Footer untuk panel Buyer.
// ============================================================

$bpTahunSekarang = date('Y');
?>
<footer class="bp-footer">
    <div class="bp-footer__left">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="16" height="16">
            <path d="M12 2l8 4v6c0 5-3.5 8-8 10-4.5-2-8-5-8-10V6z"></path>
        </svg>
        <span>&copy; <?= $bpTahunSekarang ?> Buyer Panel. Hak cipta dilindungi.</span>
    </div>

    <div class="bp-footer__right">
        <a href="<?= SITE_URL ?>/terms.php" class="bp-footer__link">Syarat &amp; Ketentuan</a>
        <span class="bp-footer__divider">&middot;</span>
        <a href="<?= SITE_URL ?>/privacy.php" class="bp-footer__link">Kebijakan Privasi</a>
        <span class="bp-footer__divider">&middot;</span>
        <a href="<?= SITE_URL ?>/help.php" class="bp-footer__link">Bantuan</a>
    </div>
</footer>

<style>
    .bp-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 10px;
        padding: 16px 24px;
        margin-left: 240px;
        border-top: 1px solid #e5e7eb;
        font-size: 12.5px;
        color: #6b7280;
        background: #ffffff;
    }

    .bp-footer__left {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .bp-footer__right {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }

    .bp-footer__link {
        color: #6b7280;
        text-decoration: none;
    }
    .bp-footer__link:hover {
        color: #1d4ed8;
        text-decoration: underline;
    }

    .bp-footer__divider { color: #d1d5db; }

    /* ====== Responsive ====== */
    @media (max-width: 900px) {
        .bp-footer { margin-left: 0; }
    }

    @media (max-width: 480px) {
        .bp-footer {
            flex-direction: column;
            align-items: flex-start;
            text-align: left;
            padding: 14px 16px;
        }
        .bp-footer__right {
            gap: 6px;
        }
    }
</style>