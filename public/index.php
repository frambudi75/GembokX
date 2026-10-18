<?php
/**
 * PassVault — Front Controller Utama
 */

declare(strict_types=1);

// PSR-4 Autoloader Sederhana
spl_autoload_register(function (string $class): void {
    $prefix = 'App\\';
    $baseDir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR;

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', DIRECTORY_SEPARATOR, $relativeClass) . '.php';

    if (file_exists($file)) {
        require_once $file;
    }
});

use App\Config\App;
use App\Config\Security;
use App\Services\SessionService;
use App\Helpers\Router;
use App\Middleware\CsrfMiddleware;
use App\Controllers\AuthController;
use App\Controllers\VaultController;
use App\Controllers\ApiVaultController;
use App\Controllers\SettingsController;
use App\Controllers\ExportController;
use App\Controllers\AdminUserController;

// Inisialisasi Environment & Keamanan
App::boot();
Security::applyHeaders();
SessionService::start();

// Validasi Token CSRF pada HTTP Method non-GET
CsrfMiddleware::verify();

// Dynamic Routes (Burn-After-Reading Secret Links)
if (str_starts_with($route, '/secret/')) {
    $token = substr($route, strlen('/secret/'));
    (new ShareController())->showSecretPage($token);
    exit;
}

if (str_starts_with($route, '/api/secret/consume/')) {
    $token = substr($route, strlen('/api/secret/consume/'));
    (new ShareController())->consumeSecret($token);
    exit;
}

switch ($route) {
    case '/':
    case '/vault':
        (new VaultController())->index();
        break;

    case '/login':
        $controller = new AuthController();
        if ($method === 'POST') {
            $controller->login();
        } else {
            $controller->showLogin();
        }
        break;

    case '/register':
        $controller = new AuthController();
        if ($method === 'POST') {
            $controller->register();
        } else {
            $controller->showRegister();
        }
        break;

    case '/logout':
        (new AuthController())->logout();
        break;

    case '/audit':
        (new VaultController())->audit();
        break;

    case '/settings':
        (new VaultController())->settings();
        break;

    case '/emergency-kit':
        (new VaultController())->emergencyKit();
        break;

    case '/export':
        (new ExportController())->export();
        break;

    // API Endpoints
    case '/api/prelogin':
        (new AuthController())->prelogin();
        break;

    case '/api/login':
        (new AuthController())->login();
        break;

    case '/api/register':
        (new AuthController())->register();
        break;

    case '/api/vault/sync':
        (new ApiVaultController())->sync();
        break;

    case '/api/vault/save':
        (new ApiVaultController())->saveItem();
        break;

    case '/api/vault/delete':
        (new ApiVaultController())->deleteItem();
        break;

    case '/api/vault/favorite':
        (new ApiVaultController())->toggleFavorite();
        break;

    case '/api/vault/import':
        (new ExportController())->importBatch();
        break;

    case '/api/folder/save':
        (new ApiVaultController())->saveFolder();
        break;

    case '/api/folder/delete':
        (new ApiVaultController())->deleteFolder();
        break;

    case '/api/settings/2fa/setup':
        (new SettingsController())->setup2fa();
        break;

    case '/api/settings/2fa/confirm':
        (new SettingsController())->confirm2fa();
        break;

    case '/api/settings/2fa/disable':
        (new SettingsController())->disable2fa();
        break;

    case '/api/settings/master-key':
        (new SettingsController())->changeMasterKey();
        break;

    case '/api/profile/update':
        (new SettingsController())->updateProfile();
        break;

    case '/api/profile/avatar':
        (new SettingsController())->uploadAvatar();
        break;

    // Admin User Manager Endpoints
    case '/admin/users':
        (new AdminUserController())->index();
        break;

    case '/api/admin/users/list':
        (new AdminUserController())->listUsers();
        break;

    case '/api/admin/users/create':
        (new AdminUserController())->createUser();
        break;

    case '/api/admin/users/toggle-status':
        (new AdminUserController())->toggleStatus();
        break;

    case '/api/admin/users/change-role':
        (new AdminUserController())->changeRole();
        break;

    case '/api/admin/users/delete':
        (new AdminUserController())->deleteUser();
        break;

    // Secure Sharing & Burn-After-Reading Endpoints
    case '/api/secret/create':
        (new ShareController())->createSecret();
        break;

    case '/api/share/user':
        (new ShareController())->shareWithUser();
        break;

    case '/api/share/received':
        (new ShareController())->getSharedWithMe();
        break;

    case '/api/share/delete':
        (new ShareController())->deleteSharedItem();
        break;

    default:
        // Cek jika API
        if (str_starts_with($route, '/api/')) {
            Router::json(['success' => false, 'message' => 'Endpoint tidak ditemukan: ' . $route], 404);
        }

        // Tampilkan 404
        http_response_code(404);
        echo "<div style='font-family:sans-serif;text-align:center;padding:50px;'><h1>404 — Halaman Tidak Ditemukan</h1><p>Rute: " . htmlspecialchars($route) . "</p><p><a href='" . Router::url('/') . "'>Kembali ke Brankas</a></p></div>";
        exit;
}
