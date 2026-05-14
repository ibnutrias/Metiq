<?php
/**
 * Plugin Name: Metiq - Metadata Quality Dashboard
 * Plugin URI: -
 * Description: Mesin pemindai dan analisis kualitas kelengkapan metadata bibliografi SLiMS secara komprehensif.
 * Version: 1.0.0
 * Author: Ibnu Trias Falah
 * Author URI: -
 */

// Pastikan file ini dipanggil dari dalam ekosistem SLiMS
defined('INDEX_AUTH') or die('Direct access not allowed!');

// Ambil instance core plugin SLiMS
$plugin = \SLiMS\Plugins::getInstance();

// Daftarkan menu "Metiq Dashboard" ke dalam modul Bibliografi (bibliography)
// Arahkan eksekusi file ke router.php yang telah kita bangun
$plugin->registerMenu('bibliography', 'Metiq Dashboard', __DIR__ . '/router.php');