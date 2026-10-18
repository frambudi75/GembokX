<?php
namespace App\Middleware;

use App\Services\SessionService;
use App\Helpers\Router;

class AuthMiddleware {
    public static function check(): void {
        if (!SessionService::isLoggedIn()) {
            if (self::isApiRequest()) {
                Router::json([
                    'success' => false,
                    'message' => 'Sesi login telah berakhir. Silakan login kembali.'
                ], 401);
            }

            SessionService::flash('error', 'Silakan masuk untuk mengakses brankas.');
            Router::redirect('/login');
        }
    }

    public static function guestOnly(): void {
        if (SessionService::isLoggedIn()) {
            Router::redirect('/vault');
        }
    }

    private static function isApiRequest(): bool {
        $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
        $xReq = $_SERVER['HTTP_X_REQUESTED_WITH'] ?? '';
        return str_contains($accept, 'application/json') 
            || strcasecmp($xReq, 'xmlhttprequest') === 0 
            || str_starts_with(Router::getRoute(), '/api');
    }
}
