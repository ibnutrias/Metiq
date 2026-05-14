<?php
defined('INDEX_AUTH') OR die('Direct access not allowed!');

spl_autoload_register(function ($class) {
    $prefix = 'Metiq\\';
    $base_dir = __DIR__ . '/src/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) return;
    $file = $base_dir . str_replace('\\', '/', substr($class, $len)) . '.php';
    if (file_exists($file)) require $file;
});

require_once __DIR__ . '/src/Utils/helpers.php';
use Metiq\Config\SQLiteManager;

global $dbs; 
$dbDir = __DIR__ . '/database';
$dbPath = $dbDir . '/metiq_data.sqlite';

if (!is_dir($dbDir)) mkdir($dbDir, 0755, true);
$sqlite = new SQLiteManager($dbPath);

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'export_csv') {
    $jsonPath = __DIR__ . '/database/latest_anomalies.json';
    $anomalies = file_exists($jsonPath) ? json_decode(file_get_contents($jsonPath), true) : [];
    
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=Metiq_Laporan_Anomali_' . date('Y-m-d_His') . '.csv');
    
    $output = fopen('php://output', 'w');
    // Header Kolom CSV
    fputcsv($output, ['ID Bibliografi', 'Judul Buku', 'Daftar Masalah (Kode Error)']);
    
    foreach ($anomalies as $row) {
        $error_list = implode(", ", $row['errors']);
        fputcsv($output, [$row['biblio_id'], $row['title'], $error_list]);
    }
    fclose($output);
    exit;
}

// TANGANI POST AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    while (ob_get_level()) ob_end_clean();
    
    if ($_POST['action'] === 'finish_setup') {
        header('Content-Type: application/json');
        if ($sqlite->markSetupComplete()) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Gagal membuat database.']);
        }
    } 
    // ROUTING TAB DINAMIS
    elseif ($_POST['action'] === 'load_tab') {
        header('Content-Type: text/html; charset=utf-8');
        $tab = preg_replace('/[^a-z0-9_]/', '', $_POST['tab']); // Sanitasi input
        $tabFile = __DIR__ . '/views/tabs/' . $tab . '.php';
        
        if (file_exists($tabFile)) {
            require $tabFile;
        } else {
            echo '<div class="alert alert-danger">Metiq Error: Tab file tidak ditemukan.</div>';
        }
    }
    elseif ($_POST['action'] === 'get_scan_meta') {
        $biblioManager = new \Metiq\SlimsAPI\BiblioManager();
        echo json_encode([
            'status' => 'success',
            'total_biblio' => $biblioManager->getTotalBiblio()
        ]);
    }
    elseif ($_POST['action'] === 'run_scan_chunk') {
        $limit = isset($_POST['limit']) ? (int)$_POST['limit'] : 100;
        $offset = isset($_POST['offset']) ? (int)$_POST['offset'] : 0;
        
        $engine = new \Metiq\Engines\Completeness();
        $result = $engine->scanChunk($limit, $offset);
        
        echo json_encode([
            'status' => 'success',
            'data' => $result
        ]);
    }
    elseif ($_POST['action'] === 'save_scan_result') {
        $total = (int)$_POST['total'];
        $errors = (int)$_POST['errors'];
        $score = (int)$_POST['score'];
        $anomalies = $_POST['anomalies'];

        // 1. Simpan history skor ke SQLite
        $pdo = $sqlite->getPDO();
        $stmt = $pdo->prepare("INSERT INTO metiq_scan_history (health_score, total_biblio, total_anomalies) VALUES (?, ?, ?)");
        $stmt->execute([$score, $total, $errors]);

        // 2. Simpan detail anomali ke file JSON (lebih cepat diload untuk tabel laporan daripada SQLite)
        $jsonPath = __DIR__ . '/database/latest_anomalies.json';
        file_put_contents($jsonPath, $anomalies);

        echo json_encode(['status' => 'success']);
    }
    // QUICK FIX HANDLERS
    elseif ($_POST['action'] === 'get_biblio') {
        $id = (int)$_POST['id'];
        $biblioManager = new \Metiq\SlimsAPI\BiblioManager();
        $data = $biblioManager->getBiblioById($id);
        echo json_encode(['status' => 'success', 'data' => $data]);
    }
    elseif ($_POST['action'] === 'update_biblio') {
        $id = (int)$_POST['id'];
        $data = $_POST['data'];
        
        $biblioManager = new \Metiq\SlimsAPI\BiblioManager();
        $success = $biblioManager->updateBiblio($id, $data);
        
        echo json_encode(['status' => $success ? 'success' : 'error']);
    }
    // RESET DATABASE HANDLER
    elseif ($_POST['action'] === 'reset_database') {
        $pdo = $sqlite->getPDO();
        // Hapus riwayat pindaian
        $pdo->exec("DELETE FROM metiq_scan_history");
        
        // Hapus file JSON anomali
        $jsonPath = __DIR__ . '/database/latest_anomalies.json';
        if (file_exists($jsonPath)) {
            unlink($jsonPath);
        }
        
        echo json_encode(['status' => 'success']);
    }
    elseif ($_POST['action'] === 'get_anomalies_json') {
        $jsonPath = __DIR__ . '/database/latest_anomalies.json';
        $anomalies = file_exists($jsonPath) ? json_decode(file_get_contents($jsonPath), true) : [];
        echo json_encode(['data' => $anomalies]);
    }
    // SIMPAN PENGATURAN
    elseif ($_POST['action'] === 'save_settings') {
        $settings = [
            'isbn_strict' => $_POST['isbn_strict'] ?? 'flexible',
            'check_ddc' => $_POST['check_ddc'] ?? '1'
        ];
        file_put_contents(__DIR__ . '/database/settings.json', json_encode($settings));
        echo json_encode(['status' => 'success']);
    }
    elseif ($_POST['action'] === 'get_chart_data') {
        $pdo = $sqlite->getPDO();
        // Ambil 15 riwayat pindaian terakhir secara berurutan
        $stmt = $pdo->query("SELECT created_at, health_score FROM metiq_scan_history ORDER BY id DESC LIMIT 15");
        $history = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        // Balikkan urutan agar dari yang paling lama ke paling baru (kiri ke kanan di grafik)
        $history = array_reverse($history);
        
        $labels = [];
        $scores = [];
        
        foreach ($history as $row) {
            $labels[] = date('d/m H:i', strtotime($row['created_at']));
            $scores[] = (float)$row['health_score'];
        }
        
        echo json_encode(['status' => 'success', 'labels' => $labels, 'scores' => $scores]);
    }
    exit;
}

if ($sqlite->isSetupComplete()) {
    $viewPath = __DIR__ . '/views/layout.php';
} else {
    $viewPath = __DIR__ . '/views/setup.php';
}

if (file_exists($viewPath)) {
    require $viewPath;
} else {
    echo '<div class="alert alert-danger">Error: View file missing!</div>';
}