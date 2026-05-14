<?php 
defined('INDEX_AUTH') OR die('Direct access not allowed!'); 

global $sqlite;
$pdo = $sqlite->getPDO();

$stmt = $pdo->query("SELECT * FROM metiq_scan_history ORDER BY id DESC LIMIT 1");
$latest = $stmt->fetch(\PDO::FETCH_ASSOC);

$score = $latest ? (int)$latest['health_score'] : '--';
$total = $latest ? $latest['total_biblio'] : 0;
$anomalies_count = $latest ? $latest['total_anomalies'] : 0;
$date = $latest ? date('d M Y H:i', strtotime($latest['created_at'])) : 'Belum pernah dipindai';

// Sistem Grade
$grade = '-'; $scoreColor = 'bg-secondary'; $gradeText = 'Belum Ada Data';
if ($score !== '--') {
    if ($score >= 90) { $grade = 'A'; $scoreColor = 'bg-success'; $gradeText = 'Sangat Baik'; }
    elseif ($score >= 75) { $grade = 'B'; $scoreColor = 'bg-primary'; $gradeText = 'Cukup Baik'; }
    elseif ($score >= 60) { $grade = 'C'; $scoreColor = 'bg-warning text-dark'; $gradeText = 'Perlu Perbaikan'; }
    else { $grade = 'D'; $scoreColor = 'bg-danger'; $gradeText = 'Kritis'; }
}

// Analisis Distribusi Anomali dari JSON
$jsonPath = dirname(__DIR__, 2) . '/database/latest_anomalies.json';
$anomaliesData = file_exists($jsonPath) ? json_decode(file_get_contents($jsonPath) ?: '[]', true) : [];

$errorCounts = [];
foreach ($anomaliesData as $row) {
    foreach ($row['errors'] as $err) {
        if (strpos($err, 'req_') === 0) { // Hanya hitung yang wajib
            $errorCounts[$err] = ($errorCounts[$err] ?? 0) + 1;
        }
    }
}
arsort($errorCounts); // Urutkan dari terbanyak

$labelNames = [
    'req_title' => 'Judul', 'req_author' => 'Pengarang', 'req_isbn' => 'ISBN/ISSN',
    'req_publisher' => 'Penerbit', 'req_year' => 'Tahun Terbit', 'req_place' => 'Kota Terbit',
    'req_callnumber' => 'Call Number', 'req_notes' => 'Abstrak/Catatan', 'req_image' => 'Cover/Gambar',
    'req_class' => 'Klasifikasi (DDC)'
];
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h5 class="font-weight-bold mb-0">Ringkasan Kualitas Metadata</h5>
        <small class="text-muted">Analisis bobot kelengkapan data koleksi perpustakaan Anda.</small>
    </div>
    <div class="text-right">
        <small class="text-muted d-block"><i class="fa-regular fa-clock mr-1"></i> Update terakhir:</small>
        <span class="font-weight-bold"><?= $date ?></span>
    </div>
</div>

<div class="row">
    <div class="col-md-5 mb-4">
        <div class="card <?= $scoreColor ?> <?= ($grade === 'C') ? '' : 'text-white' ?> border-0 shadow-sm h-100" style="border-radius: 16px;">
            <div class="card-body p-4 d-flex flex-column justify-content-center align-items-center text-center">
                <h6 class="font-weight-bold text-uppercase mb-1" style="letter-spacing: 1px; opacity: 0.8;">Global Health Score</h6>
                <div class="display-1 font-weight-bold mb-0"><?= $score ?><?= $score !== '--' ? '<small>%</small>' : '' ?></div>
                <div class="mt-2">
                    <span class="badge badge-light px-3 py-2" style="font-size: 1rem;">Grade <?= $grade ?> : <?= $gradeText ?></span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-7 mb-4">
        <div class="row h-100">
            <div class="col-sm-6 mb-3 mb-sm-0">
                <div class="card bg-light border-0 shadow-sm h-100" style="border-radius: 16px;">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="text-muted font-weight-bold text-uppercase mb-0">Total Koleksi</h6>
                            <i class="fa-solid fa-book fa-2x text-secondary opacity-50"></i>
                        </div>
                        <h2 class="mb-0 font-weight-bold text-dark"><?= number_format($total, 0, ',', '.') ?> <small class="text-muted font-weight-normal" style="font-size: 1rem;">Judul</small></h2>
                    </div>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="card bg-light border-0 shadow-sm h-100" style="border-radius: 16px;">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="text-muted font-weight-bold text-uppercase mb-0">Buku Bermasalah</h6>
                            <i class="fa-solid fa-triangle-exclamation fa-2x text-danger opacity-50"></i>
                        </div>
                        <h2 class="mb-0 font-weight-bold text-dark"><?= number_format($anomalies_count, 0, ',', '.') ?> <small class="text-muted font-weight-normal" style="font-size: 1rem;">Buku</small></h2>
                        <small class="text-danger font-weight-bold mt-1 d-block">Kehilangan atribut wajib</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if ($latest && !empty($errorCounts)): ?>
<div class="card border-0 shadow-sm mt-2" style="border-radius: 16px;">
    <div class="card-body p-4">
        <h6 class="font-weight-bold mb-4 text-dark"><i class="fa-solid fa-chart-bar text-primary mr-2"></i> Distribusi Atribut Hilang Terbanyak</h6>
        
        <?php 
        $maxCount = reset($errorCounts); // Ambil nilai tertinggi untuk perhitungan persentase bar
        $i = 0;
        foreach ($errorCounts as $errKey => $count): 
            if ($i >= 5) break; // Tampilkan top 5 saja
            $percent = ($count / $total) * 100;
            $barWidth = ($count / $maxCount) * 100;
        ?>
        <div class="mb-3">
            <div class="d-flex justify-content-between mb-1">
                <span class="font-weight-bold text-dark"><?= $labelNames[$errKey] ?? $errKey ?></span>
                <span class="text-muted small"><strong><?= number_format($count, 0, ',', '.') ?></strong> buku kosong (<?= round($percent, 1) ?>%)</span>
            </div>
            <div class="progress" style="height: 8px; border-radius: 4px;">
                <div class="progress-bar bg-danger" role="progressbar" style="width: <?= $barWidth ?>%;"></div>
            </div>
        </div>
        <?php $i++; endforeach; ?>
        
    </div>
</div>
<?php elseif (!$latest): ?>
<div class="alert alert-info border-0 shadow-sm" style="border-radius: 12px;">
    <i class="fa-solid fa-info-circle mr-2"></i> Silakan klik tombol <strong>Mulai Pindai</strong> di pojok kanan atas untuk membangun laporan.
</div>
<?php else: ?>
<div class="alert alert-success border-0 shadow-sm" style="border-radius: 12px;">
    <i class="fa-solid fa-check-circle mr-2"></i> Koleksi Anda sempurna! Tidak ditemukan atribut wajib yang kosong.
</div>
<?php endif; ?>