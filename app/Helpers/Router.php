<?php
namespace App\Helpers;

class Router {
    private static ?string $basePath = null;
    private static ?string $currentRoute = null;

    /**
     * Mengidentifikasi base path dan route saat ini secara otomatis
     */
    private static function resolve(): void {
        if (self::$basePath !== null && self::$currentRoute !== null) {
            return;
        }

        // Cek jika rute dilewatkan melalui query parameter ?action=...
        if (!empty($_GET['action'])) {
            self::$currentRoute = '/' . trim($_GET['action'], '/');
            self::$basePath = '';
            return;
        }

        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $uri = explode('?', $uri, 2)[0]; // Pisahkan query string
        $uri = rawurldecode($uri);

        $docRoot = str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT'] ?? '') ?: '');
        $projRoot = str_replace('\\', '/', realpath(dirname(__DIR__, 2)) ?: '');

        $subfolder = '';
        if ($docRoot !== '' && $projRoot !== '' && str_starts_with(strtolower($projRoot), strtolower($docRoot))) {
            $subfolder = substr($projRoot, strlen($docRoot));
            $subfolder = rtrim($subfolder, '/');
        }

        $base = '';
        $cleanUri = $uri;

        if ($subfolder !== '') {
            if (str_starts_with($cleanUri, $subfolder . '/public')) {
                $cleanUri = substr($cleanUri, strlen($subfolder . '/public'));
                $base = $subfolder . '/public';
            } elseif (str_starts_with($cleanUri, $subfolder)) {
                $cleanUri = substr($cleanUri, strlen($subfolder));
                $base = $subfolder;
            }
        } elseif (str_starts_with($cleanUri, '/public')) {
            $cleanUri = substr($cleanUri, 7);
            $base = '/public';
        }

        self::$basePath = rtrim($base, '/');
        $route = '/' . trim($cleanUri, '/');
        self::$currentRoute = $route === '' ? '/' : $route;
    }

    /**
     * Menghitung base path otomatis
     */
    public static function basePath(): string {
        self::resolve();
        return self::$basePath ?? '';
    }

    /**
     * Membentuk URL relatif yang aman terhadap subfolder XAMPP maupun root domain cPanel/Docker
     */
    public static function url(string $path = '/'): string {
        $base = self::basePath();
        $path = '/' . ltrim($path, '/');
        
        if ($base === '' || $base === '.') {
            return $path;
        }
        
        return $base . $path;
    }

    /**
     * Mendapatkan rute saat ini yang bersih dari query string dan base path
     */
    public static function getRoute(): string {
        self::resolve();
        return self::$currentRoute ?? '/';
    }

    /**
     * Redirect ke rute tertentu
     */
    public static function redirect(string $path): void {
        $url = self::url($path);
        header("Location: {$url}");
        exit;
    }

    /**
     * Memeriksa apakah request saat ini ditujukan untuk endpoint API / AJAX
     */
    public static function isApiRequest(): bool {
        $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
        $xReq = $_SERVER['HTTP_X_REQUESTED_WITH'] ?? '';
        return str_contains($accept, 'application/json') 
            || strcasecmp($xReq, 'xmlhttprequest') === 0 
            || str_starts_with(self::getRoute(), '/api');
    }

    /**
     * Mengirim respon JSON dan menghentikan eksekusi
     */
    public static function json(array $data, int $statusCode = 200): void {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
}
