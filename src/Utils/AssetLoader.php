<?php
namespace Metiq\Utils;

class AssetLoader {
    private $pluginWebUrl;
    private $version;

    public function __construct() {
        // Otomatis mendeteksi Base URL SLiMS
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $domain = $_SERVER['HTTP_HOST'];
        
        // Membersihkan /admin/index.php dari URL untuk mendapatkan root SLiMS
        $basePath = str_replace('/admin/index.php', '', $_SERVER['SCRIPT_NAME']);
        
        // Membentuk absolute URL untuk folder plugin Metiq
        $this->pluginWebUrl = rtrim($protocol . $domain . $basePath, '/') . '/plugins/metiq/';
        
        // Cache buster (gunakan time() saat development, ganti ke versi statis saat rilis)
        $this->version = time(); 
    }

    // Fungsi Reusable Load CSS
    public function css($filePath) {
        $url = $this->pluginWebUrl . ltrim($filePath, '/');
        return '<link rel="stylesheet" href="' . $url . '?v=' . $this->version . '">';
    }

    // Fungsi Reusable Load JS
    public function js($filePath) {
        $url = $this->pluginWebUrl . ltrim($filePath, '/');
        return '<script src="' . $url . '?v=' . $this->version . '"></script>';
    }

    // Fungsi Reusable untuk Image/Icon path
    public function url($filePath) {
        return $this->pluginWebUrl . ltrim($filePath, '/');
    }
}