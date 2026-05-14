<?php 
defined('INDEX_AUTH') OR die('Direct access not allowed!'); 

// Baca pengaturan saat ini
$settingsPath = dirname(__DIR__, 2) . '/database/settings.json';
$currentSettings = file_exists($settingsPath) ? json_decode(file_get_contents($settingsPath), true) : [
    'isbn_strict' => 'flexible',
    'check_ddc' => '1'
];
?>

<div class="row">
    <div class="col-md-7">
        <h5 class="font-weight-bold mb-4 text-dark"><i class="fa-solid fa-sliders mr-2 text-primary"></i> Pengaturan Engine Metiq</h5>
        
        <div class="card border-0 shadow-sm mb-5" style="border-radius: 16px;">
            <div class="card-body p-4">
                <form id="formSettings">
                    <div class="form-group mb-4">
                        <label class="font-weight-bold text-dark">Standar Validasi Panjang ISBN</label>
                        <select class="form-control bg-light border-0" id="set_isbn_strict" style="border-radius: 8px;">
                            <option value="strict" <?= $currentSettings['isbn_strict'] === 'strict' ? 'selected' : '' ?>>Strict (Hanya wajib 13 Digit)</option>
                            <option value="flexible" <?= $currentSettings['isbn_strict'] === 'flexible' ? 'selected' : '' ?>>Flexible (Boleh 10 atau 13 Digit)</option>
                        </select>
                        <small class="text-muted mt-2 d-block">Menentukan seberapa ketat Metiq memvalidasi format angka ISBN.</small>
                    </div>
                    
                    <div class="form-group mb-4">
                        <label class="font-weight-bold text-dark">Validasi Pola Klasifikasi (DDC)</label>
                        <div class="custom-control custom-switch mt-2">
                            <input type="checkbox" class="custom-control-input" id="set_check_ddc" <?= $currentSettings['check_ddc'] === '1' ? 'checked' : '' ?>>
                            <label class="custom-control-label" for="set_check_ddc" style="padding-top:2px;">Aktifkan pemeriksaan struktur DDC</label>
                        </div>
                        <small class="text-muted mt-2 d-block">Jika aktif, Metiq tidak hanya mengecek DDC kosong, tapi juga memastikan formatnya diawali 3 digit angka standar.</small>
                    </div>
                    
                    <button type="submit" class="btn btn-primary font-weight-bold px-4" id="btnSaveSettings">
                        <i class="fa-solid fa-save mr-2"></i> Simpan Pengaturan
                    </button>
                </form>
            </div>
        </div>

        <h5 class="font-weight-bold mb-3 text-danger"><i class="fa-solid fa-triangle-exclamation mr-2"></i> Danger Zone</h5>
        <div class="card border-danger shadow-sm" style="border-radius: 16px; background-color: #fffafa;">
            <div class="card-body p-4">
                <h6 class="font-weight-bold text-danger">Reset Data Pemindaian</h6>
                <p class="text-muted small mb-3">Tindakan ini akan menghapus permanen seluruh riwayat skor dan laporan anomali yang tersimpan di dalam database Metiq. <strong>(Data asli SLiMS Anda 100% aman dan tidak akan terhapus)</strong>.</p>
                <button type="button" class="btn btn-danger font-weight-bold shadow-sm" id="btnResetDB">
                    <i class="fa-solid fa-trash-can mr-2"></i> Bersihkan Database Metiq
                </button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    const ajaxUrl = '<?= $_SERVER["REQUEST_URI"] ?>';

    // Simpan Pengaturan
    $('#formSettings').on('submit', function(e) {
        e.preventDefault();
        const $btn = $('#btnSaveSettings');
        const originalHtml = $btn.html();
        
        $btn.html('<i class="fa-solid fa-spinner fa-spin mr-2"></i> Menyimpan...').prop('disabled', true);

        const payload = {
            action: 'save_settings',
            isbn_strict: $('#set_isbn_strict').val(),
            check_ddc: $('#set_check_ddc').is(':checked') ? '1' : '0'
        };

        $.post(ajaxUrl, payload, function(res) {
            $btn.html('<i class="fa-solid fa-check mr-2"></i> Tersimpan').removeClass('btn-primary').addClass('btn-success');
            setTimeout(() => {
                $btn.html(originalHtml).removeClass('btn-success').addClass('btn-primary').prop('disabled', false);
            }, 2000);
        }, 'json').fail(function() {
            $btn.html(originalHtml).prop('disabled', false);
            alert('Gagal menyimpan pengaturan.');
        });
    });

    // Reset Database
    $('#btnResetDB').on('click', function() {
        if(confirm('Peringatan Keras: Apakah Anda yakin ingin menghapus seluruh riwayat laporan Metiq?')) {
            const $btn = $(this);
            const originalHtml = $btn.html();
            $btn.html('<i class="fa-solid fa-spinner fa-spin mr-2"></i> Memproses...').prop('disabled', true);
            
            $.post(ajaxUrl, { action: 'reset_database' }, function(res) {
                alert('Database berhasil dibersihkan.');
                loadMetiqTab('dashboard'); // Kembali ke dashboard
            }, 'json').fail(function() {
                $btn.html(originalHtml).prop('disabled', false);
                alert('Gagal membersihkan database.');
            });
        }
    });
});
</script>