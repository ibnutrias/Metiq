<?php defined('INDEX_AUTH') OR die('Direct access not allowed!'); ?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<?= metiq_load('assets/js/metiq_core.js') ?>

<style>
#metiq-app-container { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; }
.metiq-nav-pills .nav-link { color: #6c757d; border-radius: 8px; font-weight: 600; padding: 10px 20px; transition: all 0.2s; }
.metiq-nav-pills .nav-link:hover { background-color: #f8f9fa; color: #343a40; }
.metiq-nav-pills .nav-link.active { background-color: #0d6efd; color: #fff; box-shadow: 0 4px 10px rgba(13, 110, 253, 0.2); }
.metiq-content-box { border-radius: 16px; border: 1px solid #eaeaea; box-shadow: 0 10px 30px rgba(0,0,0,0.02); background: #fff; min-height: 500px; }
.metiq-btn-scan { border-radius: 8px; font-weight: 600; padding: 10px 24px; transition: transform 0.2s, box-shadow 0.2s; }
.metiq-btn-scan:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(13, 110, 253, 0.3); }
</style>

<div class="container-fluid py-4" id="metiq-app-container">
    
    <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom">
        <div class="d-flex align-items-center">
            <div class="bg-primary text-white rounded p-2 mr-3 shadow-sm">
                <i class="fa-solid fa-layer-group fa-lg"></i>
            </div>
            <div>
                <h4 class="mb-0 font-weight-bold text-dark">Metiq</h4>
                <small class="text-muted text-uppercase" style="letter-spacing: 1px;">Dashboard Kualitas Metadata</small>
            </div>
        </div>
        <div>
            <button class="btn btn-primary metiq-btn-scan" id="btnStartScan">
                <i class="fa-solid fa-play mr-2"></i> Mulai Pindai
            </button>
        </div>
    </div>

    <ul class="nav nav-pills metiq-nav-pills mb-4" id="metiqTabs" role="tablist">
        <li class="nav-item mr-2">
            <a class="nav-link active" id="nav-dashboard" data-tab="dashboard" href="#">
                <i class="fa-solid fa-chart-pie mr-2"></i> Ringkasan
            </a>
        </li>
        <li class="nav-item mr-2">
            <a class="nav-link" id="nav-report" data-tab="report" href="#">
                <i class="fa-solid fa-table-list mr-2"></i> Laporan Anomali
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="nav-settings" data-tab="settings" href="#">
                <i class="fa-solid fa-gear mr-2"></i> Pengaturan
            </a>
        </li>
    </ul>
    <div id="metiq-progress-container" class="mb-4" style="display: none;">
        <div class="d-flex justify-content-between mb-1 small font-weight-bold">
            <span id="metiq-progress-text" class="text-primary">Memindai metadata... (0/0)</span>
            <span id="metiq-progress-percent" class="text-primary">0%</span>
        </div>
        <div class="progress" style="height: 10px; border-radius: 5px;">
            <div id="metiq-progress-bar" class="progress-bar progress-bar-striped progress-bar-animated bg-primary" role="progressbar" style="width: 0%;"></div>
        </div>
    </div>

    <div id="metiq-tab-content" class="metiq-content-box p-4 position-relative">
        <div class="d-flex flex-column align-items-center justify-content-center h-100 py-5 text-muted">
            <i class="fa-solid fa-circle-notch fa-spin fa-2x mb-3"></i>
            <span class="font-weight-bold">Memuat modul...</span>
        </div>
    </div>

</div>
<script>
const pluginAjaxUrl = '<?= $_SERVER["REQUEST_URI"] ?>';
let isScanning = false;
let scanSession = { total: 0, currentOffset: 0, chunkSize: 200, scoreSum: 0, totalErrors: 0 };
let globalAnomalies = [];

function loadMetiqTab(tabName) {
    const $content = $('#metiq-tab-content');
    if (isScanning && tabName === 'report') {
        renderProcessingState();
        $('.metiq-nav-pills .nav-link').removeClass('active');
        $('#nav-' + tabName).addClass('active');
        return;
    }
    $content.html('<div class="d-flex flex-column align-items-center justify-content-center h-100 py-5 text-muted"><i class="fa-solid fa-circle-notch fa-spin fa-2x mb-3"></i><span class="font-weight-bold">Mengambil data...</span></div>');
    $('.metiq-nav-pills .nav-link').removeClass('active');
    $('#nav-' + tabName).addClass('active');

    $.ajax({
        url: pluginAjaxUrl, type: 'POST', data: { action: 'load_tab', tab: tabName },
        dataType: 'html', global: false,
        success: function(html) { $content.hide().html(html).fadeIn(200); },
        error: function() { $content.html('<div class="alert alert-danger">Gagal memuat modul.</div>'); }
    });
}

function renderProcessingState() {
    $('#metiq-tab-content').html(`
        <div class="d-flex flex-column align-items-center justify-content-center h-100 py-5 text-muted">
            <div class="spinner-grow text-primary mb-3" role="status"></div>
            <h5 class="font-weight-bold text-dark">Pemindaian Sedang Berlangsung</h5>
            <p class="text-center">Mohon tunggu sebentar...</p>
        </div>
    `);
}

$('#btnStartScan').on('click', function() {
    if (isScanning) return;
    isScanning = true;
    scanSession = { total: 0, currentOffset: 0, chunkSize: 200, scoreSum: 0, totalErrors: 0 };
    globalAnomalies = []; 
    
    if ($('#nav-report').hasClass('active')) renderProcessingState();
    $(this).html('<i class="fa-solid fa-spinner fa-spin mr-2"></i> Memindai...').addClass('disabled');
    $('#metiq-progress-container').slideDown();
    
    $.post(pluginAjaxUrl, { action: 'get_scan_meta' }, function(res) {
        if (res.status === 'success' && res.total_biblio > 0) {
            scanSession.total = res.total_biblio;
            processNextChunk();
        } else {
            finishScan('Database kosong.');
        }
    }, 'json');
});

function processNextChunk() {
    if (scanSession.currentOffset >= scanSession.total) {
        finishScan(); return;
    }

    $.post(pluginAjaxUrl, { 
        action: 'run_scan_chunk', limit: scanSession.chunkSize, offset: scanSession.currentOffset 
    }, function(res) {
        if (res.status === 'success') {
            let data = res.data;
            scanSession.currentOffset += scanSession.chunkSize;
            scanSession.scoreSum += data.chunk_score_sum; // Akumulasi Skor Rata-rata
            scanSession.totalErrors += data.chunk_error_count; // Buku yang memiliki error wajib
            
            if (data.anomalies.length > 0) globalAnomalies = globalAnomalies.concat(data.anomalies);

            let percent = Math.round((Math.min(scanSession.currentOffset, scanSession.total) / scanSession.total) * 100);
            $('#metiq-progress-bar').css('width', percent + '%');
            $('#metiq-progress-percent').text(percent + '%');
            $('#metiq-progress-text').text(`Memproses data... (${Math.min(scanSession.currentOffset, scanSession.total)}/${scanSession.total})`);

            processNextChunk();
        } else {
            finishScan('Error saat memproses data.');
        }
    }, 'json');
}

function finishScan(errorMsg = null) {
    if (errorMsg) {
        isScanning = false;
        $('#btnStartScan').html('<i class="fa-solid fa-play mr-2"></i> Mulai Pindai').removeClass('disabled');
        alert(errorMsg);
    } else {
        // Kalkulasi skor akhir: Rata-rata dari seluruh buku
        let finalScore = scanSession.total > 0 ? Math.round(scanSession.scoreSum / scanSession.total) : 100;

        $.post(pluginAjaxUrl, {
            action: 'save_scan_result', total: scanSession.total, errors: scanSession.totalErrors,
            score: finalScore, anomalies: JSON.stringify(globalAnomalies)
        }, function(res) {
            isScanning = false;
            $('#btnStartScan').html('<i class="fa-solid fa-play mr-2"></i> Mulai Pindai').removeClass('disabled');
            if ($('#nav-dashboard').hasClass('active')) loadMetiqTab('dashboard');
            if ($('#nav-report').hasClass('active')) loadMetiqTab('report');
            $('#metiq-progress-container').slideUp();
        }, 'json');
    }
}

$(document).ready(function() {
    loadMetiqTab('dashboard');
    $('.metiq-nav-pills .nav-link').on('click', function(e) {
        e.preventDefault(); e.stopPropagation();
        if (!$(this).hasClass('active')) loadMetiqTab($(this).data('tab'));
    });
});
</script>