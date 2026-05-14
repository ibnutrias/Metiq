<?php
if (!function_exists('metiq_load')) {
    function metiq_load($filePath) {
        static $pluginWebUrl = null;
        static $version = null;

        if ($pluginWebUrl === null) {
            // Deteksi absolute URL SLiMS secara dinamis
            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
            $domain = $_SERVER['HTTP_HOST'];
            // Antisipasi script dipanggil via index.php atau plugin_container.php
            $basePath = str_replace(['/admin/index.php', '/admin/plugin_container.php'], '', $_SERVER['SCRIPT_NAME']);
            $pluginWebUrl = rtrim($protocol . $domain . $basePath, '/') . '/plugins/metiq/';
            $version = time(); // Ganti dengan angka versi saat production
        }

        $url = $pluginWebUrl . ltrim($filePath, '/');
        $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        // Auto-generate tag HTML berdasarkan ekstensi file
        if ($ext === 'css') {
            return '<link rel="stylesheet" href="' . $url . '?v=' . $version . '">';
        } elseif ($ext === 'js') {
            return '<script src="' . $url . '?v=' . $version . '"></script>';
        }
        
        // Return URL mentah untuk gambar/font (Contoh: src="<?= metiq_load('img/logo.png') ? >")
        return $url; 
    }
}
?>