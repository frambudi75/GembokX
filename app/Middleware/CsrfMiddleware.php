<?php
namespace App\Middleware;

use App\Config\Security;
use App\Helpers\Router;
use App\Services\SessionService;

class CsrfMiddleware {
    public static function verify(): void {
        if (str_starts_with(Router::getRoute(), '/api/secret/consume/')) {
            return;
        }

        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        if (in_array(strtoupper($method), ['POST', 'PUT', 'DELETE', 'PATCH'])) {
            $token = $_POST['csrf_token'] 
                  ?? $_SERVER['HTTP_X_CSRF_TOKEN'] 
                  ?? null;

            // Jika payload JSON
            if (!$token) {
                $raw = file_get_contents('php://input');
                $json = json_decode($raw, true);
                if (is_array($json) && !empty($json['csrf_token'])) {
                    $token = $json['csrf_token'];
                }
            }

            if (!Security::verifyCsrfToken($token)) {
                $isJson = str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json')
                       || str_starts_with(Router::getRoute(), '/api');

                if ($isJson) {
                    Router::json([
                        'success' => false,
                        'message' => 'Token CSRF tidak valid atau telah kadaluarsa. Silakan refresh halaman.'
                    ], 403);
                }

                SessionService::flash('error', 'Sesi formulir kadaluarsa. Silakan coba kembali.');
                Router::redirect($_SERVER['HTTP_REFERER'] ?? '/login');
            }
        }
    }
}
