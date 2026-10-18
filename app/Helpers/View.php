<?php
namespace App\Helpers;

use App\Config\Security;

class View {
    public static function render(string $view, array $data = [], ?string $layout = 'main'): void {
        // Ekstrak data variabel untuk view
        extract($data, EXTR_SKIP);

        // Tambahkan helper global untuk view
        $url = fn(string $path = '/') => Router::url($path);
        $csrf_token = fn() => Security::generateCsrfToken();
        $csrf_field = fn() => '<input type="hidden" name="csrf_token" value="' . Security::generateCsrfToken() . '">';
        $e = fn(?string $str) => Security::clean($str);

        // Render isi konten view ke dalam buffer
        $viewFile = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $view) . '.php';
        if (!file_exists($viewFile)) {
            die("View file tidak ditemukan: {$viewFile}");
        }

        ob_start();
        include $viewFile;
        $content = ob_get_clean();

        // Jika ada layout pembungkus
        if ($layout !== null) {
            $layoutFile = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'layouts' . DIRECTORY_SEPARATOR . $layout . '.php';
            if (file_exists($layoutFile)) {
                include $layoutFile;
                return;
            }
        }

        echo $content;
    }
}
