<?php
/**
 * Root Fallback Entry Point untuk XAMPP & cPanel
 * Memastikan aplikasi tetap dapat diakses jika mod_rewrite di root tidak aktif
 */
require_once __DIR__ . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'index.php';
