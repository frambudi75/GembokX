<?php
namespace App\Controllers;

use App\Models\VaultItem;
use App\Models\Folder;
use App\Models\ActivityLog;
use App\Services\SessionService;
use App\Middleware\AuthMiddleware;
use App\Helpers\Router;

class ExportController {
    public function __construct() {
        AuthMiddleware::check();
    }

    /**
     * Ekspor seluruh ciphertext brankas sebagai backup terenkripsi
     */
    public function export(): void {
        $userId = SessionService::userId();
        $rateKey = 'export_rate_user_' . $userId;
        $recent = (int) \App\Services\CacheService::get($rateKey, 0);
        if ($recent >= 3) {
            http_response_code(429);
            echo "Terlalu sering melakukan ekspor brankas. Harap tunggu 2 menit sebelum mencoba lagi.";
            exit;
        }
        \App\Services\CacheService::set($rateKey, $recent + 1, 120);

        $items = VaultItem::getAllByUser($userId);
        $folders = Folder::getAllByUser($userId);

        ActivityLog::log($userId, 'vault_exported');

        $backup = [
            'encrypted' => true,
            'app' => 'GembokX',
            'version' => '1.0',
            'exported_at' => date('c'),
            'folders' => $folders,
            'items' => $items
        ];

        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="gembokx_encrypted_backup_' . date('Y-m-d_His') . '.json"');
        echo json_encode($backup, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        exit;
    }

    /**
     * Mengimpor sekumpulan item terenkripsi yang sudah dienkripsi di browser klien
     */
    public function importBatch(): void {
        $userId = SessionService::userId();
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true) ?: $_POST;

        $items = $data['items'] ?? [];
        if (!is_array($items) || empty($items)) {
            Router::json(['success' => false, 'message' => 'Tidak ada item yang ditemukan untuk diimpor.'], 400);
        }

        $importedCount = 0;
        foreach ($items as $item) {
            if (empty($item['encrypted_payload']) || empty($item['iv']) || empty($item['auth_tag'])) {
                continue;
            }

            VaultItem::create([
                'user_id' => $userId,
                'folder_id' => !empty($item['folder_id']) ? (int) $item['folder_id'] : null,
                'item_type' => $item['item_type'] ?? 'login',
                'is_favorite' => !empty($item['is_favorite']) ? 1 : 0,
                'encrypted_payload' => $item['encrypted_payload'],
                'iv' => $item['iv'],
                'auth_tag' => $item['auth_tag']
            ]);
            $importedCount++;
        }

        ActivityLog::log($userId, 'vault_imported');

        Router::json([
            'success' => true,
            'message' => "Berhasil mengimpor {$importedCount} item ke dalam brankas.",
            'count' => $importedCount
        ]);
    }
}
