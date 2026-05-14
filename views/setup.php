<?php defined('INDEX_AUTH') OR die('Direct access not allowed!'); ?>

<?= metiq_load('assets/css/metiq.css') // Jika nanti ada custom CSS ?>
<?= metiq_load('assets/js/metiq_core.js') ?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
#metiq-app-container { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; overflow-x: hidden; }
.metiq-card { border-radius: 16px; border: 1px solid #eaeaea; box-shadow: 0 10px 30px rgba(0,0,0,0.03); background: #fff; }

/* ANIMASI SLIDE KIRI KANAN */
.metiq-step-container { position: relative; width: 100%; min-height: 400px; }
.metiq-step { 
    position: absolute; top: 0; left: 0; width: 100%; 
    opacity: 0; visibility: hidden;
    transform: translateX(50px); /* Standby di kanan */
    transition: opacity 0.3s ease-out, transform 0.3s ease-out; 
}
.metiq-step.active { 
    position: relative; opacity: 1; visibility: visible;
    transform: translateX(0); /* Masuk ke tengah */
}
.metiq-step.fade-out-left { 
    position: absolute; opacity: 0; visibility: hidden;
    transform: translateX(-50px); /* Keluar ke kiri */
}

/* UI Komponen */
.metiq-icon-box { width: 54px; height: 54px; display: flex; align-items: center; justify-content: center; border-radius: 14px; background: #f8f9fc; }
.metiq-btn-modern { border-radius: 8px; font-weight: 600; padding: 10px 24px; transition: all 0.2s; }
.metiq-btn-modern:not(:disabled):hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
.metiq-btn-modern:disabled { cursor: not-allowed; opacity: 0.6; }
.step-indicator { display: flex; gap: 8px; margin-bottom: 24px; }
.step-dot { height: 6px; width: 32px; background: #e2e8f0; border-radius: 4px; transition: 0.3s; }
.step-dot.active { background: #0d6efd; width: 48px; }
</style>

<div class="container-fluid py-4" id="metiq-app-container">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="metiq-card overflow-hidden">
                <div class="p-5">
                    <div class="d-flex align-items-center mb-4 pb-3 border-bottom">
                        <div class="bg-primary text-white rounded p-3 mr-3 shadow-sm">
                            <i class="fa-solid fa-layer-group fa-xl"></i>
                        </div>
                        <div>
                            <h3 class="mb-0 font-weight-bold text-dark">Metiq</h3>
                            <span class="text-muted small text-uppercase" style="letter-spacing: 1px;">Metadata Quality Dashboard</span>
                        </div>
                    </div>

                    <form id="setupForm">
                        <div class="metiq-step-container">
                            
                            <div id="step-1" class="metiq-step active">
                                <div class="step-indicator">
                                    <div class="step-dot active"></div><div class="step-dot"></div>
                                </div>
                                <h5 class="font-weight-bold mb-4 text-dark">Kapabilitas Sistem</h5>
                                <div class="row mb-5">
                                    <div class="col-sm-6 mb-4">
                                        <div class="d-flex align-items-start">
                                            <div class="metiq-icon-box text-danger mr-3"><i class="fa-solid fa-magnifying-glass-chart fa-lg"></i></div>
                                            <div><h6 class="font-weight-bold mb-1">Deteksi Presisi</h6><p class="text-muted small mb-0">Identifikasi metadata kosong instan.</p></div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 mb-4">
                                        <div class="d-flex align-items-start">
                                            <div class="metiq-icon-box text-success mr-3"><i class="fa-solid fa-chart-pie fa-lg"></i></div>
                                            <div><h6 class="font-weight-bold mb-1">Analitik Visual</h6><p class="text-muted small mb-0">Pemantauan metrik kualitas real-time.</p></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <button type="button" class="btn btn-primary metiq-btn-modern" onclick="nextStep(1, 2)">
                                        Lanjutkan <i class="fa-solid fa-arrow-right ml-2"></i>
                                    </button>
                                </div>
                            </div>

                            <div id="step-2" class="metiq-step">
                                <div class="step-indicator">
                                    <div class="step-dot"></div><div class="step-dot active"></div>
                                </div>
                                <h5 class="font-weight-bold mb-4 text-dark">Inisialisasi Keamanan Data</h5>
                                <div class="bg-light p-4 rounded mb-4 border border-light">
                                    <div class="d-flex mb-3">
                                        <i class="fa-solid fa-shield-halved fa-2x mr-3 text-primary"></i>
                                        <div>
                                            <h6 class="font-weight-bold mb-1">Isolasi Database Storage</h6>
                                            <p class="text-muted small mb-0">Seluruh log disimpan secara terisolasi pada <code>metiq_data.sqlite</code> untuk menjamin integritas database utama.</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="p-3 border rounded mb-5">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="understandCheck" required>
                                        <label class="custom-control-label font-weight-bold text-dark" for="understandCheck" style="cursor: pointer; padding-top: 2px;">
                                            Otorisasi pembuatan database lokal dan mulai penggunaan Metiq.
                                        </label>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <button type="button" class="btn btn-light metiq-btn-modern border" onclick="prevStep(2, 1)">
                                        <i class="fa-solid fa-arrow-left mr-2"></i> Kembali
                                    </button>
                                    <button type="submit" class="btn btn-dark metiq-btn-modern" id="btnSubmit" disabled>
                                        <i class="fa-solid fa-check mr-2"></i> Konfirmasi & Mulai
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const pluginAjaxUrl = '<?= $_SERVER["REQUEST_URI"] ?>';

function nextStep(current, next) {
    const $current = $('#step-' + current);
    const $next = $('#step-' + next);
    
    $current.removeClass('active').addClass('fade-out-left');
    $next.removeClass('fade-out-left'); 
    void $next[0].offsetWidth; 
    $next.addClass('active');
}

function prevStep(current, prev) {
    const $current = $('#step-' + current);
    const $prev = $('#step-' + prev);
    
    $current.removeClass('active fade-out-left'); 
    $prev.removeClass('fade-out-left'); 
    void $prev[0].offsetWidth; 
    $prev.addClass('active');
}

$('#understandCheck').on('change', function() {
    $('#btnSubmit').prop('disabled', !$(this).is(':checked'));
});

$('#setupForm').on('submit', function(e) {
    e.preventDefault();
    if (!$('#understandCheck').is(':checked')) return;

    const payload = { action: 'finish_setup' };
    const $btn = $('#btnSubmit');

    if (typeof Metiq !== 'undefined') {
        Metiq.postAction(pluginAjaxUrl, payload, $btn, function(res) {
            Metiq.reloadView(pluginAjaxUrl);
        });
    } else {
        alert("Sistem Error: metiq_core.js gagal dimuat.");
    }
});
</script>