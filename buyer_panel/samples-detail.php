<?php
// ============================================================
//  buyer_panel/samples-detail.php
//  Detail permintaan sample + hasil & aksi approval untuk panel Buyer.
// ============================================================

define('REQUIRED_ROLE', 'buyer');
require_once __DIR__ . '/../assets/verifyRoleRedirect.php';
require_once __DIR__ . '/partials/config.php';

$idBuyer    = (int) ($currentBuyer['id_buyer'] ?? 0);
$idRequest  = (int) ($_GET['id'] ?? 0);
$justCreated = isset($_GET['created']);
$pageTitle  = 'Detail Sample Request';

if ($idRequest <= 0) {
    header('Location: ' . BUYER_URL . '/samples.php');
    exit;
}

$actionError = null;
$actionSuccess = null;

// ------------------------------------------------------------
// Ambil data sample request milik buyer ini (pastikan kepemilikan)
// ------------------------------------------------------------
function fetchSampleDetail(mysqli $conn, int $idRequest, int $idBuyer): ?array
{
    $stmt = $conn->prepare("
        SELECT
            sr.id_request, sr.id_buyer, sr.jenis_benang, sr.ukuran_benang,
            sr.kode_warna_target, sr.upload_sampel, sr.tanggal,
            sr.tanggal_dibutuhkan, sr.catatan, sr.status,
            sres.id_result, sres.kode_warna_hasil, sres.pilihan,
            sres.gambar, sres.nilai_delta_e, sres.catatan AS catatan_hasil,
            sres.status_approval
        FROM sample_requests sr
        LEFT JOIN sample_results sres ON sres.id_request = sr.id_request
        WHERE sr.id_request = ? AND sr.id_buyer = ?
        LIMIT 1
    ");
    $stmt->bind_param('ii', $idRequest, $idBuyer);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $row ?: null;
}

$sample = fetchSampleDetail($conn, $idRequest, $idBuyer);

if (!$sample) {
    header('Location: ' . BUYER_URL . '/samples.php');
    exit;
}

// ------------------------------------------------------------
// Proses aksi approval (approve / reject / revision)
// ------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {

    $canAct = $sample['id_result']
        && $sample['status'] === 'result_ready'
        && $sample['status_approval'] === 'pending';

    if (!$canAct) {
        $actionError = 'Aksi tidak dapat dilakukan untuk status saat ini.';
    } else {
        $action = $_POST['action'];
        $newApproval = null;
        $newReqStatus = null;

        if ($action === 'approve') {
            $newApproval = 'approved';
            $newReqStatus = 'approved';
        } elseif ($action === 'reject') {
            $newApproval = 'rejected';
            $newReqStatus = 'rejected';
        } elseif ($action === 'revision') {
            $newApproval = 'revision_requested';
            $newReqStatus = 'revision';
        }

        if ($newApproval) {
            $conn->begin_transaction();
            try {
                $stmt1 = $conn->prepare("UPDATE sample_results SET status_approval = ? WHERE id_result = ?");
                $stmt1->bind_param('si', $newApproval, $sample['id_result']);
                $stmt1->execute();
                $stmt1->close();

                $stmt2 = $conn->prepare("UPDATE sample_requests SET status = ? WHERE id_request = ?");
                $stmt2->bind_param('si', $newReqStatus, $idRequest);
                $stmt2->execute();
                $stmt2->close();

                $conn->commit();

                // Refresh data setelah update
                $sample = fetchSampleDetail($conn, $idRequest, $idBuyer);
                $actionSuccess = 'Respons Anda berhasil disimpan.';
            } catch (Exception $e) {
                $conn->rollback();
                $actionError = 'Gagal menyimpan respons. Silakan coba lagi.';
            }
        } else {
            $actionError = 'Aksi tidak dikenali.';
        }
    }
}

// ------------------------------------------------------------
// Label & badge
// ------------------------------------------------------------
$statusLabel = [
    'pending'        => ['label' => 'Menunggu',    'class' => 'badge-pending'],
    'waiting_result' => ['label' => 'Proses Hasil', 'class' => 'badge-info'],
    'result_ready'   => ['label' => 'Hasil Siap',   'class' => 'badge-attention'],
    'approved'       => ['label' => 'Disetujui',    'class' => 'badge-verified'],
    'rejected'       => ['label' => 'Ditolak',      'class' => 'badge-rejected'],
    'revision'       => ['label' => 'Revisi',       'class' => 'badge-pending'],
];
$st = $statusLabel[$sample['status']] ?? ['label' => $sample['status'], 'class' => 'badge-pending'];

$pilihanLabel = [
    'A'        => 'Pilihan A',
    'B'        => 'Pilihan B',
    'rejected' => 'Ditolak Produksi',
    'pending'  => 'Belum Ditentukan',
][$sample['pilihan'] ?? 'pending'] ?? '-';

$approvalLabel = [
    'pending'             => ['label' => 'Menunggu Respons Anda', 'class' => 'badge-pending'],
    'approved'            => ['label' => 'Disetujui',             'class' => 'badge-verified'],
    'rejected'            => ['label' => 'Ditolak',               'class' => 'badge-rejected'],
    'revision_requested'  => ['label' => 'Revisi Diminta',        'class' => 'badge-pending'],
][$sample['status_approval'] ?? 'pending'] ?? null;

$canRespond = $sample['id_result']
    && $sample['status'] === 'result_ready'
    && $sample['status_approval'] === 'pending';

$uploadUrl = $sample['upload_sampel'] ? SITE_URL . '/uploads/samples/' . $sample['upload_sampel'] : null;
$resultImgUrl = $sample['gambar'] ? SITE_URL . '/uploads/sample_results/' . $sample['gambar'] : null;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> — Buyer Panel</title>
</head>
<body>

<?php include __DIR__ . '/partials/_header.php'; ?>
<?php include __DIR__ . '/partials/_sidebar.php'; ?>
<?php include __DIR__ . '/partials/overdue-banner.php'; ?>

<div class="bp-content">
    <main class="bp-smpdt">

        <div class="bp-smpdt__heading">
            <a href="<?= BUYER_URL ?>/samples.php" class="bp-back-link">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="16" height="16">
                    <line x1="19" y1="12" x2="5" y2="12"></line>
                    <polyline points="12 19 5 12 12 5"></polyline>
                </svg>
                <span>Kembali ke Sample Request</span>
            </a>
            <div class="bp-smpdt__heading-row">
                <h1>Sample #<?= $sample['id_request'] ?></h1>
                <span class="bp-badge <?= $st['class'] ?>"><?= htmlspecialchars($st['label']) ?></span>
            </div>
        </div>

        <?php if ($justCreated): ?>
            <div class="bp-alert bp-alert--success">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="18" height="18">
                    <polyline points="20 6 9 17 4 12"></polyline>
                </svg>
                <span>Permintaan sample berhasil diajukan. Tim kami akan segera memprosesnya.</span>
            </div>
        <?php endif; ?>

        <?php if ($actionSuccess): ?>
            <div class="bp-alert bp-alert--success">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="18" height="18">
                    <polyline points="20 6 9 17 4 12"></polyline>
                </svg>
                <span><?= htmlspecialchars($actionSuccess) ?></span>
            </div>
        <?php endif; ?>

        <?php if ($actionError): ?>
            <div class="bp-alert bp-alert--danger">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="18" height="18">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="15" y1="9" x2="9" y2="15"></line>
                    <line x1="9" y1="9" x2="15" y2="15"></line>
                </svg>
                <span><?= htmlspecialchars($actionError) ?></span>
            </div>
        <?php endif; ?>

        <div class="bp-smpdt__grid">

            <!-- ============ Kolom kiri: Detail Permintaan ============ -->
            <section class="bp-card">
                <h2 class="bp-card__title">Detail Permintaan</h2>

                <dl class="bp-detail-list">
                    <div class="bp-detail-list__row">
                        <dt>Jenis Benang</dt>
                        <dd><?= htmlspecialchars($sample['jenis_benang']) ?></dd>
                    </div>
                    <div class="bp-detail-list__row">
                        <dt>Ukuran Benang</dt>
                        <dd><?= htmlspecialchars($sample['ukuran_benang'] ?? '-') ?></dd>
                    </div>
                    <div class="bp-detail-list__row">
                        <dt>Kode Warna Target</dt>
                        <dd><?= htmlspecialchars($sample['kode_warna_target'] ?? '-') ?></dd>
                    </div>
                    <div class="bp-detail-list__row">
                        <dt>Tanggal Permintaan</dt>
                        <dd><?= date('d M Y', strtotime($sample['tanggal'])) ?></dd>
                    </div>
                    <div class="bp-detail-list__row">
                        <dt>Tanggal Dibutuhkan</dt>
                        <dd><?= $sample['tanggal_dibutuhkan'] ? date('d M Y', strtotime($sample['tanggal_dibutuhkan'])) : '-' ?></dd>
                    </div>
                </dl>

                <?php if (!empty($sample['catatan'])): ?>
                    <div class="bp-detail-note">
                        <div class="bp-detail-note__label">Catatan Anda</div>
                        <p><?= nl2br(htmlspecialchars($sample['catatan'])) ?></p>
                    </div>
                <?php endif; ?>

                <?php if ($uploadUrl): ?>
                    <div class="bp-detail-file">
                        <div class="bp-detail-note__label">File Referensi</div>
                        <a href="<?= htmlspecialchars($uploadUrl) ?>" target="_blank" rel="noopener" class="bp-file-link">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="18" height="18">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                <polyline points="14 2 14 8 20 8"></polyline>
                            </svg>
                            <span><?= htmlspecialchars($sample['upload_sampel']) ?></span>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="14" height="14">
                                <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path>
                                <polyline points="15 3 21 3 21 9"></polyline>
                                <line x1="10" y1="14" x2="21" y2="3"></line>
                            </svg>
                        </a>
                    </div>
                <?php endif; ?>
            </section>

            <!-- ============ Kolom kanan: Hasil Sample ============ -->
            <section class="bp-card">
                <h2 class="bp-card__title">Hasil Sample</h2>

                <?php if (!$sample['id_result']): ?>
                    <div class="bp-empty">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="36" height="36">
                            <circle cx="12" cy="12" r="10"></circle>
                            <path d="M12 6v6l4 2"></path>
                        </svg>
                        <p>Hasil sample belum tersedia. Tim produksi sedang memproses permintaan Anda.</p>
                    </div>
                <?php else: ?>

                    <?php if ($resultImgUrl): ?>
                        <a href="<?= htmlspecialchars($resultImgUrl) ?>" target="_blank" rel="noopener" class="bp-result-img-link">
                            <img src="<?= htmlspecialchars($resultImgUrl) ?>" alt="Hasil sample" class="bp-result-img">
                        </a>
                    <?php endif; ?>

                    <dl class="bp-detail-list">
                        <div class="bp-detail-list__row">
                            <dt>Hasil Produksi</dt>
                            <dd><?= htmlspecialchars($pilihanLabel) ?></dd>
                        </div>
                        <div class="bp-detail-list__row">
                            <dt>Kode Warna Hasil</dt>
                            <dd><?= htmlspecialchars($sample['kode_warna_hasil'] ?? '-') ?></dd>
                        </div>
                        <?php if ($sample['nilai_delta_e'] !== null): ?>
                            <div class="bp-detail-list__row">
                                <dt>Nilai Delta E</dt>
                                <dd><?= htmlspecialchars((string) $sample['nilai_delta_e']) ?></dd>
                            </div>
                        <?php endif; ?>
                        <?php if ($approvalLabel): ?>
                            <div class="bp-detail-list__row">
                                <dt>Status Approval</dt>
                                <dd><span class="bp-badge <?= $approvalLabel['class'] ?>"><?= htmlspecialchars($approvalLabel['label']) ?></span></dd>
                            </div>
                        <?php endif; ?>
                    </dl>

                    <?php if (!empty($sample['catatan_hasil'])): ?>
                        <div class="bp-detail-note">
                            <div class="bp-detail-note__label">Catatan dari Produksi</div>
                            <p><?= nl2br(htmlspecialchars($sample['catatan_hasil'])) ?></p>
                        </div>
                    <?php endif; ?>

                    <?php if ($canRespond): ?>
                        <form method="POST" class="bp-approval-actions">
                            <p class="bp-approval-actions__prompt">Apakah hasil sample ini sesuai dengan kebutuhan Anda?</p>
                            <div class="bp-approval-actions__buttons">
                                <button type="submit" name="action" value="approve" class="bp-btn-approve">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="16" height="16">
                                        <polyline points="20 6 9 17 4 12"></polyline>
                                    </svg>
                                    <span>Setujui</span>
                                </button>
                                <button type="submit" name="action" value="revision" class="bp-btn-revision">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="16" height="16">
                                        <polyline points="1 4 1 10 7 10"></polyline>
                                        <path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"></path>
                                    </svg>
                                    <span>Minta Revisi</span>
                                </button>
                                <button type="submit" name="action" value="reject" class="bp-btn-reject">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="16" height="16">
                                        <circle cx="12" cy="12" r="10"></circle>
                                        <line x1="15" y1="9" x2="9" y2="15"></line>
                                        <line x1="9" y1="9" x2="15" y2="15"></line>
                                    </svg>
                                    <span>Tolak</span>
                                </button>
                            </div>
                        </form>
                    <?php endif; ?>

                <?php endif; ?>
            </section>

        </div>

    </main>
</div>

<?php include __DIR__ . '/partials/_footer.php'; ?>

<style>
    body {
        margin: 0;
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
        background: #f9fafb;
        color: #111827;
    }

    .bp-smpdt {
        padding: 24px;
        max-width: 1100px;
        margin: 0 auto;
        display: flex;
        flex-direction: column;
        gap: 18px;
    }

    .bp-back-link {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 13px;
        font-weight: 600;
        color: #6b7280;
        text-decoration: none;
        margin-bottom: 10px;
    }
    .bp-back-link:hover { color: #1d4ed8; }

    .bp-smpdt__heading-row {
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
    }
    .bp-smpdt__heading-row h1 { margin: 0; font-size: 22px; font-weight: 700; }

    .bp-card {
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        padding: 20px;
    }
    .bp-card__title { margin: 0 0 16px; font-size: 15px; font-weight: 700; }

    /* Alert */
    .bp-alert {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        padding: 12px 16px;
        border-radius: 10px;
        font-size: 13.5px;
        line-height: 1.4;
    }
    .bp-alert--success { background: #ecfdf5; color: #047857; }
    .bp-alert--danger { background: #fef2f2; color: #b91c1c; }
    .bp-alert svg { flex-shrink: 0; margin-top: 1px; }

    /* Badge */
    .bp-badge {
        display: inline-flex;
        align-items: center;
        font-size: 12px;
        font-weight: 600;
        padding: 5px 10px;
        border-radius: 999px;
        white-space: nowrap;
    }
    .badge-verified  { background: #ecfdf5; color: #047857; }
    .badge-pending   { background: #fffbeb; color: #b45309; }
    .badge-rejected  { background: #fef2f2; color: #b91c1c; }
    .badge-info      { background: #eff6ff; color: #1d4ed8; }
    .badge-attention { background: #fff7ed; color: #c2410c; }

    /* Grid 2 kolom */
    .bp-smpdt__grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
        align-items: start;
    }

    /* Detail list */
    .bp-detail-list { margin: 0; }
    .bp-detail-list__row {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        padding: 10px 0;
        border-bottom: 1px solid #f3f4f6;
        font-size: 13.5px;
    }
    .bp-detail-list__row:last-child { border-bottom: none; }
    .bp-detail-list__row dt { color: #6b7280; margin: 0; }
    .bp-detail-list__row dd { margin: 0; font-weight: 600; text-align: right; }

    .bp-detail-note {
        margin-top: 16px;
        padding-top: 16px;
        border-top: 1px solid #f3f4f6;
    }
    .bp-detail-note__label {
        font-size: 12px;
        font-weight: 600;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.02em;
        margin-bottom: 6px;
    }
    .bp-detail-note p { margin: 0; font-size: 13.5px; line-height: 1.5; }

    .bp-detail-file { margin-top: 16px; padding-top: 16px; border-top: 1px solid #f3f4f6; }
    .bp-file-link {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 9px 14px;
        background: #f3f4f6;
        border-radius: 9px;
        text-decoration: none;
        color: #374151;
        font-size: 13px;
        font-weight: 600;
    }
    .bp-file-link:hover { background: #e5e7eb; }
    .bp-file-link svg:last-child { margin-left: auto; color: #9ca3af; }

    /* Hasil gambar */
    .bp-result-img-link { display: block; margin-bottom: 16px; }
    .bp-result-img {
        width: 100%;
        max-height: 280px;
        object-fit: cover;
        border-radius: 10px;
        border: 1px solid #e5e7eb;
    }

    /* Empty state hasil */
    .bp-empty {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 10px;
        padding: 32px 10px;
        color: #9ca3af;
        text-align: center;
    }
    .bp-empty p { margin: 0; font-size: 13.5px; }

    /* Aksi approval */
    .bp-approval-actions {
        margin-top: 18px;
        padding-top: 18px;
        border-top: 1px solid #f3f4f6;
    }
    .bp-approval-actions__prompt {
        margin: 0 0 12px;
        font-size: 13.5px;
        font-weight: 600;
        color: #374151;
    }
    .bp-approval-actions__buttons {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }

    .bp-btn-approve, .bp-btn-revision, .bp-btn-reject {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 9px 14px;
        border: none;
        border-radius: 9px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        font-family: inherit;
    }
    .bp-btn-approve { background: #ecfdf5; color: #047857; }
    .bp-btn-approve:hover { background: #d1fae5; }
    .bp-btn-revision { background: #fffbeb; color: #b45309; }
    .bp-btn-revision:hover { background: #fef3c7; }
    .bp-btn-reject { background: #fef2f2; color: #b91c1c; }
    .bp-btn-reject:hover { background: #fee2e2; }

    /* ====== Responsive ====== */
    @media (max-width: 900px) {
        .bp-smpdt__grid { grid-template-columns: 1fr; }
    }

    @media (max-width: 640px) {
        .bp-smpdt { padding: 16px; gap: 14px; }
        .bp-card { padding: 16px; }
        .bp-approval-actions__buttons { flex-direction: column; }
        .bp-btn-approve, .bp-btn-revision, .bp-btn-reject { justify-content: center; width: 100%; }
    }
</style>

</body>
</html>