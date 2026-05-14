<?php
namespace Metiq\Engines;

use Metiq\SlimsAPI\BiblioManager;

class Completeness {
    private $biblioAPI;
    private $total_required_fields = 10;
    private $settings;

    public function __construct() {
        $this->biblioAPI = new BiblioManager();
        
        // Membaca konfigurasi dari Pengaturan
        $settingsPath = dirname(__DIR__, 2) . '/database/settings.json';
        $this->settings = file_exists($settingsPath) ? json_decode(file_get_contents($settingsPath), true) : [
            'isbn_strict' => 'flexible',
            'check_ddc' => '1'
        ];
    }

    public function checkRecord($row) {
        $anomalies = [];

        // 1. Validasi Judul & Pengarang
        if (empty(trim($row['title']))) $anomalies[] = 'req_title';
        if ((int)$row['author_count'] === 0) $anomalies[] = 'req_author';
        
        // 2. Validasi ISBN (Keberadaan & Format)
        $isbn = trim($row['isbn_issn']);
        if (empty($isbn)) {
            $anomalies[] = 'req_isbn';
        } else {
            // Bersihkan dari spasi atau strip untuk menghitung panjang karakter
            $clean_isbn = preg_replace('/[^0-9XxiI]/', '', $isbn);
            if ($this->settings['isbn_strict'] === 'strict') {
                if (strlen($clean_isbn) !== 13) $anomalies[] = 'fmt_isbn';
            } else {
                if (strlen($clean_isbn) !== 10 && strlen($clean_isbn) !== 13) $anomalies[] = 'fmt_isbn';
            }
        }

        // 3. Validasi DDC/Klasifikasi (Keberadaan & Format)
        $ddc = trim($row['classification']);
        if (empty($ddc)) {
            $anomalies[] = 'req_class';
        } else {
            if ($this->settings['check_ddc'] === '1') {
                // Pola DDC standar biasanya diawali dengan minimal 3 digit angka (contoh: 004, 808.8)
                if (!preg_match('/^\d{3}/', $ddc)) {
                    $anomalies[] = 'fmt_class';
                }
            }
        }

        // 4. Validasi Wajib Lainnya
        if (empty($row['publisher_id'])) $anomalies[] = 'req_publisher';
        if (empty(trim($row['publish_year']))) $anomalies[] = 'req_year';
        if (empty($row['publish_place_id'])) $anomalies[] = 'req_place';
        if (empty(trim($row['call_number']))) $anomalies[] = 'req_callnumber';
        if (empty(trim($row['notes']))) $anomalies[] = 'req_notes';
        if (empty(trim($row['image']))) $anomalies[] = 'req_image';

        // 5. Parameter Disarankan
        if (empty(trim($row['sor']))) $anomalies[] = 'rec_sor';
        if (empty(trim($row['edition']))) $anomalies[] = 'rec_edition';
        if (empty($row['gmd_id'])) $anomalies[] = 'rec_gmd';
        if (empty($row['content_type_id'])) $anomalies[] = 'rec_content';
        if (empty($row['media_type_id'])) $anomalies[] = 'rec_media';
        if (empty($row['carrier_type_id'])) $anomalies[] = 'rec_carrier';
        if (empty($row['frequency_id'])) $anomalies[] = 'rec_frequency';
        if (empty(trim($row['collation']))) $anomalies[] = 'rec_collation';
        if (empty(trim($row['series_title']))) $anomalies[] = 'rec_series';
        if ((int)$row['subject_count'] === 0) $anomalies[] = 'rec_subject';
        if (empty($row['language_id'])) $anomalies[] = 'rec_language';
        if (empty(trim($row['labels']))) $anomalies[] = 'rec_labels';

        return $anomalies;
    }

    public function scanChunk($limit, $offset) {
        $records = $this->biblioAPI->getBiblioChunk($limit, $offset);
        $total_scanned = count($records);
        $anomalies_found = [];
        $chunk_score_sum = 0;
        $error_count = 0;

        foreach ($records as $row) {
            $errors = $this->checkRecord($row);
            
            $req_errors = 0;
            foreach ($errors as $err) {
                // req_ (kosong) dan fmt_ (format salah) dihitung sebagai penalti skor
                if (strpos($err, 'req_') === 0 || strpos($err, 'fmt_') === 0) {
                    $req_errors++;
                }
            }

            $book_score = (($this->total_required_fields - min($req_errors, $this->total_required_fields)) / $this->total_required_fields) * 100;
            $chunk_score_sum += $book_score;

            if (!empty($errors)) {
                $anomalies_found[] = [
                    'biblio_id' => $row['biblio_id'],
                    'title' => $row['title'] ?: 'Tanpa Judul',
                    'errors' => $errors
                ];
                if ($req_errors > 0) $error_count++;
            }
        }

        return [
            'scanned' => $total_scanned,
            'anomalies' => $anomalies_found,
            'chunk_score_sum' => $chunk_score_sum,
            'chunk_error_count' => $error_count
        ];
    }
}