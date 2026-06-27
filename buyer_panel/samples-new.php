<?php
// ============================================================
//  buyer_panel/samples-new.php
//  Form pengajuan permintaan sample baru untuk panel Buyer.
// ============================================================

define('REQUIRED_ROLE', 'buyer');
require_once __DIR__ . '/../assets/verifyRoleRedirect.php';
require_once __DIR__ . '/partials/config.php';

$idBuyer   = (int) ($currentBuyer['id_buyer'] ?? 0);
$pageTitle = 'Ajukan Sample Baru';

$errors = [];
$old = [
    'jenis_benang'       => '',
    'ukuran_benang'      => '',
    'kode_warna_target'  => '',
    'tanggal_dibutuhkan' => '',
    'catatan'            => '',
];

// ------------------------------------------------------------
// Konfigurasi upload
// ------------------------------------------------------------
$uploadDir      = dirname(__DIR__) . '/uploads/samples/';   // path filesystem
$uploadUrlBase  = SITE_URL . '/uploads/samples/';            // path publik
$allowedExt     = ['jpg', 'jpeg', 'png', 'pdf'];
$maxFileSizeMb  = 5;

if (!is_dir($uploadDir)) {
    @mkdir($uploadDir, 0755, true);
}

// ------------------------------------------------------------
// Proses submit form
// ------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $old['jenis_benang']       = trim($_POST['jenis_benang'] ?? '');
    $old['ukuran_benang']      = trim($_POST['ukuran_benang'] ?? '');
    $old['kode_warna_target']  = trim($_POST['kode_warna_target'] ?? '');
    $old['tanggal_dibutuhkan'] = trim($_POST['tanggal_dibutuhkan'] ?? '');
    $old['catatan']            = trim($_POST['catatan'] ?? '');

    // Validasi field wajib
    if ($old['jenis_benang'] === '') {
        $errors['jenis_benang'] = 'Jenis benang wajib diisi.';
    }

    if (
        $old['tanggal_dibutuhkan'] !== '' &&
        !DateTime::createFromFormat('Y-m-d', $old['tanggal_dibutuhkan'])
    ) {
        $errors['tanggal_dibutuhkan'] = 'Format tanggal tidak valid.';
    }

    // Validasi upload file (wajib, minimal 1 file)
    $uploadedFileName = null;

    if (!isset($_FILES['upload_sampel']) || $_FILES['upload_sampel']['error'] === UPLOAD_ERR_NO_FILE) {
        $errors['upload_sampel'] = 'File referensi sample wajib diunggah.';
    } elseif ($_FILES['upload_sampel']['error'] !== UPLOAD_ERR_OK) {
        $errors['upload_sampel'] = 'Terjadi kesalahan saat mengunggah file. Silakan coba lagi.';
    } else {
        $file = $_FILES['upload_sampel'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, $allowedExt, true)) {
            $errors['upload_sampel'] = 'Format file harus JPG, PNG, atau PDF.';
        } elseif ($file['size'] > $maxFileSizeMb * 1024 * 1024) {
            $errors['upload_sampel'] = "Ukuran file maksimal {$maxFileSizeMb}MB.";
        } else {
            // Nama file unik: buyer_id + timestamp + ekstensi asli
            $uploadedFileName = 'sample_' . $idBuyer . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
            $destination = $uploadDir . $uploadedFileName;

            if (!move_uploaded_file($file['tmp_name'], $destination)) {
                $errors['upload_sampel'] = 'Gagal menyimpan file. Silakan coba lagi.';
                $uploadedFileName = null;
            }
        }
    }

    // ------------------------------------------------------------
    // Simpan ke database jika tidak ada error
    // ------------------------------------------------------------
    if (empty($errors)) {
        $tanggalDibutuhkan = $old['tanggal_dibutuhkan'] !== '' ? $old['tanggal_dibutuhkan'] : null;

        $stmt = $conn->prepare("
            INSERT INTO sample_requests
                (id_buyer, jenis_benang, ukuran_benang, kode_warna_target, upload_sampel, tanggal, tanggal_dibutuhkan, catatan, status)
            VALUES (?, ?, ?, ?, ?, CURDATE(), ?, ?, 'pending')
        ");
        $stmt->bind_param(
            'isssss',
            $idBuyer,
            $old['jenis_benang'],
            $old['ukuran_benang'],
            $old['kode_warna_target'],
            $uploadedFileName,
            $tanggalDibutuhkan,
            $old['catatan']
        );

        if ($stmt->execute()) {
            $newId = $stmt->insert_id;
            $stmt->close();
            header('Location: ' . BUYER_URL . '/samples-detail.php?id=' . $newId . '&created=1');
            exit;
        }

        $stmt->close();
        $errors['general'] = 'Gagal menyimpan permintaan sample. Silakan coba lagi.';

        // Hapus file yang sudah terupload jika simpan ke DB gagal
        if ($uploadedFileName && file_exists($uploadDir . $uploadedFileName)) {
            @unlink($uploadDir . $uploadedFileName);
        }
    }
}
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
    <main class="bp-smpnew">

        <div class="bp-smpnew__heading">
            <a href="<?= BUYER_URL ?>/samples.php" class="bp-back-link">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="16" height="16">
                    <line x1="19" y1="12" x2="5" y2="12"></line>
                    <polyline points="12 19 5 12 12 5"></polyline>
                </svg>
                <span>Kembali ke Sample Request</span>
            </a>
            <h1>Ajukan Sample Baru</h1>
            <p>Isi detail benang dan warna yang ingin Anda ajukan untuk pembuatan sample.</p>
        </div>

        <?php if (!empty($errors['general'])): ?>
            <div class="bp-alert bp-alert--danger">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="18" height="18">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="15" y1="9" x2="9" y2="15"></line>
                    <line x1="9" y1="9" x2="15" y2="15"></line>
                </svg>
                <span><?= htmlspecialchars($errors['general']) ?></span>
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" class="bp-card bp-form" novalidate>

            <div class="bp-form__grid">

                <!-- Jenis Benang -->
                <div class="bp-form__group bp-form__group--full">
                    <label for="jenis_benang">
                        Jenis Benang <span class="bp-required">*</span>
                    </label>
                    <input
                        type="text"
                        id="jenis_benang"
                        name="jenis_benang"
                        value="<?= htmlspecialchars($old['jenis_benang']) ?>"
                        placeholder="Contoh: Nylon Stretch Yarn"
                        class="<?= isset($errors['jenis_benang']) ? 'is-invalid' : '' ?>"
                    >
                    <?php if (isset($errors['jenis_benang'])): ?>
                        <span class="bp-form__error"><?= htmlspecialchars($errors['jenis_benang']) ?></span>
                    <?php endif; ?>
                </div>

                <!-- Ukuran Benang -->
                <div class="bp-form__group">
                    <label for="ukuran_benang">Ukuran Benang</label>
                    <input
                        type="text"
                        id="ukuran_benang"
                        name="ukuran_benang"
                        value="<?= htmlspecialchars($old['ukuran_benang']) ?>"
                        placeholder="Contoh: 70D/24FX2"
                    >
                </div>

                <!-- Kode Warna Target -->
                <div class="bp-form__group">
                    <label for="kode_warna_target">Kode Warna Target</label>
                    <input
                        type="text"
                        id="kode_warna_target"
                        name="kode_warna_target"
                        value="<?= htmlspecialchars($old['kode_warna_target']) ?>"
                        placeholder="Contoh: 59651"
                    >
                </div>

                <!-- Tanggal Dibutuhkan -->
                <div class="bp-form__group">
                    <label for="tanggal_dibutuhkan">Tanggal Dibutuhkan</label>
                    <input
                        type="date"
                        id="tanggal_dibutuhkan"
                        name="tanggal_dibutuhkan"
                        value="<?= htmlspecialchars($old['tanggal_dibutuhkan']) ?>"
                        min="<?= date('Y-m-d') ?>"
                        class="<?= isset($errors['tanggal_dibutuhkan']) ? 'is-invalid' : '' ?>"
                    >
                    <?php if (isset($errors['tanggal_dibutuhkan'])): ?>
                        <span class="bp-form__error"><?= htmlspecialchars($errors['tanggal_dibutuhkan']) ?></span>
                    <?php endif; ?>
                </div>

                <!-- Upload Sampel -->
                <div class="bp-form__group bp-form__group--full">
                    <label for="upload_sampel">
                        Upload Referensi Sample <span class="bp-required">*</span>
                    </label>
                    <div class="bp-upload <?= isset($errors['upload_sampel']) ? 'is-invalid' : '' ?>" id="bpUploadBox">
                        <input type="file" id="upload_sampel" name="upload_sampel" accept=".jpg,.jpeg,.png,.pdf" hidden>
                        <div class="bp-upload__placeholder" id="bpUploadPlaceholder">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="28" height="28">
                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                <polyline points="17 8 12 3 7 8"></polyline>
                                <line x1="12" y1="3" x2="12" y2="15"></line>
                            </svg>
                            <span><strong>Klik untuk unggah</strong> atau drag &amp; drop</span>
                            <span class="bp-upload__hint">JPG, PNG, atau PDF. Maks. <?= $maxFileSizeMb ?>MB.</span>
                        </div>
                        <div class="bp-upload__filename" id="bpUploadFilename" hidden>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="20" height="20">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                <polyline points="14 2 14 8 20 8"></polyline>
                            </svg>
                            <span id="bpUploadFilenameText"></span>
                        </div>
                    </div>
                    <?php if (isset($errors['upload_sampel'])): ?>
                        <span class="bp-form__error"><?= htmlspecialchars($errors['upload_sampel']) ?></span>
                    <?php endif; ?>
                </div>

                <!-- Catatan -->
                <div class="bp-form__group bp-form__group--full">
                    <label for="catatan">Catatan Tambahan</label>
                    <textarea
                        id="catatan"
                        name="catatan"
                        rows="4"
                        placeholder="Jelaskan detail tambahan terkait permintaan sample Anda (opsional)"
                    ><?= htmlspecialchars($old['catatan']) ?></textarea>
                </div>

            </div>

            <div class="bp-form__actions">
                <a href="<?= BUYER_URL ?>/samples.php" class="bp-btn-secondary">Batal</a>
                <button type="submit" class="bp-btn-primary">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="18" height="18">
                        <line x1="22" y1="2" x2="11" y2="13"></line>
                        <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                    </svg>
                    <span>Kirim Permintaan</span>
                </button>
            </div>

        </form>

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

    .bp-smpnew {
        padding: 24px;
        max-width: 800px;
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

    .bp-smpnew__heading h1 { margin: 0 0 4px; font-size: 22px; font-weight: 700; }
    .bp-smpnew__heading p { margin: 0; font-size: 14px; color: #6b7280; }

    .bp-card {
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        padding: 22px;
    }

    .bp-alert {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        padding: 12px 16px;
        border-radius: 10px;
        font-size: 13.5px;
        line-height: 1.4;
    }
    .bp-alert--danger { background: #fef2f2; color: #b91c1c; }
    .bp-alert svg { flex-shrink: 0; margin-top: 1px; }

    /* Form */
    .bp-form__grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
    }
    .bp-form__group { display: flex; flex-direction: column; gap: 6px; }
    .bp-form__group--full { grid-column: 1 / -1; }

    .bp-form__group label {
        font-size: 13.5px;
        font-weight: 600;
        color: #374151;
    }
    .bp-required { color: #dc2626; }

    .bp-form__group input[type="text"],
    .bp-form__group input[type="date"],
    .bp-form__group textarea {
        padding: 10px 12px;
        border: 1px solid #d1d5db;
        border-radius: 9px;
        font-size: 14px;
        font-family: inherit;
        color: #111827;
        background: #ffffff;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }
    .bp-form__group input:focus,
    .bp-form__group textarea:focus {
        outline: none;
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
    }
    .bp-form__group input.is-invalid,
    .bp-upload.is-invalid {
        border-color: #dc2626;
    }
    .bp-form__group textarea { resize: vertical; }

    .bp-form__error {
        font-size: 12.5px;
        color: #dc2626;
    }

    /* Upload box */
    .bp-upload {
        position: relative;
        border: 2px dashed #d1d5db;
        border-radius: 12px;
        padding: 24px;
        text-align: center;
        cursor: pointer;
        transition: border-color 0.15s ease, background 0.15s ease;
    }
    .bp-upload:hover { border-color: #2563eb; background: #f8faff; }
    .bp-upload.is-dragover { border-color: #2563eb; background: #eff6ff; }

    .bp-upload__placeholder {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 8px;
        color: #6b7280;
        font-size: 13.5px;
    }
    .bp-upload__placeholder svg { color: #9ca3af; }
    .bp-upload__hint { font-size: 12px; color: #9ca3af; }

    .bp-upload__filename {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        color: #1d4ed8;
        font-size: 13.5px;
        font-weight: 600;
    }

    /* Tombol */
    .bp-form__actions {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        margin-top: 22px;
        padding-top: 18px;
        border-top: 1px solid #e5e7eb;
    }

    .bp-btn-primary {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 18px;
        background: #2563eb;
        color: #ffffff;
        border: none;
        border-radius: 10px;
        text-decoration: none;
        font-size: 13.5px;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.15s ease;
    }
    .bp-btn-primary:hover { background: #1d4ed8; }

    .bp-btn-secondary {
        display: inline-flex;
        align-items: center;
        padding: 10px 18px;
        background: #ffffff;
        color: #374151;
        border: 1px solid #d1d5db;
        border-radius: 10px;
        text-decoration: none;
        font-size: 13.5px;
        font-weight: 600;
        transition: background 0.15s ease;
    }
    .bp-btn-secondary:hover { background: #f3f4f6; }

    /* ====== Responsive ====== */
    @media (max-width: 640px) {
        .bp-smpnew { padding: 16px; gap: 14px; }
        .bp-form__grid { grid-template-columns: 1fr; }
        .bp-card { padding: 16px; }
        .bp-form__actions { flex-direction: column-reverse; }
        .bp-form__actions .bp-btn-primary,
        .bp-form__actions .bp-btn-secondary {
            justify-content: center;
            width: 100%;
        }
    }
</style>

<script>
(function () {
    var fileInput = document.getElementById('upload_sampel');
    var uploadBox = document.getElementById('bpUploadBox');
    var placeholder = document.getElementById('bpUploadPlaceholder');
    var filenameBox = document.getElementById('bpUploadFilename');
    var filenameText = document.getElementById('bpUploadFilenameText');

    if (!fileInput || !uploadBox) return;

    function showFileName(name) {
        filenameText.textContent = name;
        placeholder.hidden = true;
        filenameBox.hidden = false;
    }

    function resetBox() {
        placeholder.hidden = false;
        filenameBox.hidden = true;
    }

    uploadBox.addEventListener('click', function () {
        fileInput.click();
    });

    fileInput.addEventListener('change', function () {
        if (fileInput.files && fileInput.files.length > 0) {
            showFileName(fileInput.files[0].name);
        } else {
            resetBox();
        }
    });

    // Drag & drop
    ['dragenter', 'dragover'].forEach(function (evt) {
        uploadBox.addEventListener(evt, function (e) {
            e.preventDefault();
            uploadBox.classList.add('is-dragover');
        });
    });

    ['dragleave', 'drop'].forEach(function (evt) {
        uploadBox.addEventListener(evt, function (e) {
            e.preventDefault();
            uploadBox.classList.remove('is-dragover');
        });
    });

    uploadBox.addEventListener('drop', function (e) {
        var files = e.dataTransfer.files;
        if (files && files.length > 0) {
            fileInput.files = files;
            showFileName(files[0].name);
        }
    });
})();
</script>

</body>
</html>