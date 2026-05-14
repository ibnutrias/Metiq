<?php
namespace Metiq\Config;

use PDO;
use PDOException;

class SQLiteManager {
    private $pdo;
    private $dbPath;

    public function __construct($dbPath) {
        $this->dbPath = $dbPath;
        $this->connect();
        $this->initTables();
    }

    private function connect() {
        try {
            $this->pdo = new PDO('sqlite:' . $this->dbPath);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die('<div class="alert alert-danger">Metiq SQLite Error: ' . htmlspecialchars($e->getMessage()) . '</div>');
        }
    }

    private function initTables() {
        // Membuat tabel untuk preferensi dan riwayat skor
        $query = "
            CREATE TABLE IF NOT EXISTS metiq_config (
                config_key TEXT PRIMARY KEY,
                config_value TEXT NOT NULL
            );
            CREATE TABLE IF NOT EXISTS metiq_scan_history (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                health_score INTEGER NOT NULL,
                total_biblio INTEGER NOT NULL,
                total_anomalies INTEGER NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            );
        ";
        $this->pdo->exec($query);
    }

    public function isSetupComplete() {
        $stmt = $this->pdo->query("SELECT config_value FROM metiq_config WHERE config_key = 'setup_completed'");
        $result = $stmt->fetchColumn();
        return $result === '1';
    }

    public function markSetupComplete() {
        $stmt = $this->pdo->prepare("INSERT OR REPLACE INTO metiq_config (config_key, config_value) VALUES ('setup_completed', '1')");
        return $stmt->execute();
    }

    public function getPDO() {
        return $this->pdo;
    }
}