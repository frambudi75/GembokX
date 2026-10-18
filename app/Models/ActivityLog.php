<?php
namespace App\Models;

use App\Config\Database;
use PDO;

class ActivityLog {
    public static function log(int $userId, string $eventType): void {
        try {
            $pdo = Database::getConnection();
            $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
            $ua = substr($_SERVER['HTTP_USER_AGENT'] ?? 'Unknown', 0, 255);

            $stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, event_type, ip_address, user_agent) VALUES (?, ?, ?, ?)");
            $stmt->execute([$userId, $eventType, $ip, $ua]);

            // Kirim notifikasi webhook Discord secara asinkron/langsung
            \App\Services\DiscordNotifier::sendForUser($userId, $eventType);
        } catch (\Throwable) {
            // Jangan hentikan flow jika logging audit gagal
        }
    }

    public static function getRecentByUser(int $userId, int $limit = 20): array {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT event_type, ip_address, user_agent, created_at FROM activity_logs WHERE user_id = ? ORDER BY created_at DESC LIMIT ?");
        $stmt->bindValue(1, $userId, PDO::PARAM_INT);
        $stmt->bindValue(2, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
