<?php
namespace App\Services;

use App\Models\User;
use App\Config\App;

class DiscordNotifier {
    /**
     * Kirim notifikasi keamanan Discord untuk pengguna tertentu
     */
    public static function sendForUser(int $userId, string $eventType, array $extra = []): bool {
        try {
            $user = User::findById($userId);
            if (!$user) return false;

            $webhookUrl = trim($user['discord_webhook_url'] ?? '');
            if (empty($webhookUrl)) {
                // Cek fallback global webhook di environment jika disetel
                $webhookUrl = App::env('DISCORD_SECURITY_WEBHOOK_URL', '');
            }

            if (empty($webhookUrl) || !filter_var($webhookUrl, FILTER_VALIDATE_URL)) {
                return false;
            }

            $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
            $ua = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown Browser';

            return self::send($webhookUrl, $eventType, [
                'user_email' => $user['email'],
                'user_name' => $user['username'],
                'ip' => $ip,
                'user_agent' => $ua,
                'extra' => $extra
            ]);
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * Kirim payload Discord Embed via HTTP POST Webhook
     */
    public static function send(string $webhookUrl, string $eventType, array $data): bool {
        if (!filter_var($webhookUrl, FILTER_VALIDATE_URL)) {
            return false;
        }

        $meta = self::getEventMeta($eventType);
        $email = $data['user_email'] ?? 'User';
        $username = $data['user_name'] ?? 'Pengguna';
        $ip = $data['ip'] ?? ($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1');
        $rawUa = $data['user_agent'] ?? ($_SERVER['HTTP_USER_AGENT'] ?? 'Unknown');
        $shortUa = self::summarizeUserAgent($rawUa);

        $description = $meta['desc'];
        if (!empty($data['extra']['custom_message'])) {
            $description .= "\n" . $data['extra']['custom_message'];
        }

        $embed = [
            'title' => $meta['title'],
            'description' => $description,
            'color' => $meta['color'],
            'fields' => [
                [
                    'name' => '👤 Akun',
                    'value' => "`{$username}` ({$email})",
                    'inline' => true
                ],
                [
                    'name' => '🌐 Alamat IP',
                    'value' => "`{$ip}`",
                    'inline' => true
                ],
                [
                    'name' => '💻 Perangkat & Browser',
                    'value' => $shortUa,
                    'inline' => false
                ],
                [
                    'name' => '🕒 Waktu Aktivitas',
                    'value' => date('d M Y, H:i:s') . ' WIB',
                    'inline' => true
                ]
            ],
            'footer' => [
                'text' => 'GembokX Zero-Knowledge Security Monitor • Server Protection'
            ],
            'timestamp' => date('c')
        ];

        $payload = [
            'username' => 'GembokX Security',
            'avatar_url' => 'https://raw.githubusercontent.com/frambudi75/GembokX/main/public/assets/images/logo.png',
            'embeds' => [$embed]
        ];

        $jsonPayload = json_encode($payload);

        if (function_exists('curl_init')) {
            $ch = curl_init($webhookUrl);
            curl_setopt_array($ch, [
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $jsonPayload,
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                    'User-Agent: GembokX-Security-Bot/1.0'
                ],
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 4,
                CURLOPT_SSL_VERIFYPEER => true
            ]);
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            return $httpCode >= 200 && $httpCode < 300;
        } else {
            $opts = [
                'http' => [
                    'method' => 'POST',
                    'header' => "Content-Type: application/json\r\nUser-Agent: GembokX-Security-Bot/1.0\r\n",
                    'content' => $jsonPayload,
                    'timeout' => 4
                ]
            ];
            $context = stream_context_create($opts);
            $res = @file_get_contents($webhookUrl, false, $context);
            return $res !== false;
        }
    }

    /**
     * Dapatkan metadata judul, deskripsi, dan warna berdasarkan jenis event
     */
    private static function getEventMeta(string $eventType): array {
        return match ($eventType) {
            'login_success' => [
                'title' => '🔓 Sesi Login Berhasil',
                'desc' => 'Autentikasi berhasil diverifikasi dan brankas diakses.',
                'color' => 0x10B981 // Emerald
            ],
            'login_failed' => [
                'title' => '⚠️ Percobaan Login Gagal',
                'desc' => 'Terdeteksi upaya login dengan kata sandi master yang salah.',
                'color' => 0xF59E0B // Amber
            ],
            'account_locked' => [
                'title' => '🚨 Akun Terkunci Sementara!',
                'desc' => 'Akun dikunci sementara karena terdeteksi 5 kali percobaan login gagal berturut-turut (Proteksi Brute-Force aktif).',
                'color' => 0xEF4444 // Red
            ],
            'master_key_changed' => [
                'title' => '🔑 Master Password / KDF Diperbarui',
                'desc' => 'Kunci master dan konfigurasi enkripsi brankas berhasil diperbarui dari browser pengguna.',
                'color' => 0x3B82F6 // Blue
            ],
            '2fa_enabled' => [
                'title' => '🛡️ 2FA TOTP Diaktifkan',
                'desc' => 'Otentikasi Dua Faktor (TOTP RFC 6238) berhasil diaktifkan pada akun.',
                'color' => 0x10B981 // Emerald
            ],
            '2fa_disabled' => [
                'title' => '⚠️ 2FA TOTP Dinonaktifkan',
                'desc' => 'Otentikasi Dua Faktor telah dinonaktifkan dari pengaturan akun.',
                'color' => 0xEF4444 // Red
            ],
            'vault_exported' => [
                'title' => '📦 Cadangan Brankas Diekspor',
                'desc' => 'Seluruh data brankas terenkripsi telah diekspor sebagai berkas cadangan JSON.',
                'color' => 0x8B5CF6 // Purple
            ],
            'vault_imported' => [
                'title' => '📥 Data Brankas Diimpor',
                'desc' => 'Koleksi item kredensial baru telah berhasil diimpor ke dalam brankas.',
                'color' => 0x3B82F6 // Blue
            ],
            'emergency_kit_downloaded' => [
                'title' => '📄 Emergency Recovery Kit Diunduh',
                'desc' => 'Lembar pemulihan darurat akun (Emergency Kit) telah diunduh atau dicetak.',
                'color' => 0xF59E0B // Amber
            ],
            'webhook_test' => [
                'title' => '✅ Uji Integrasi Webhook Berhasil',
                'desc' => 'Koneksi Webhook Discord GembokX terverifikasi aktif dan siap mengirimkan notifikasi keamanan real-time.',
                'color' => 0x5865F2 // Discord Blurple
            ],
            default => [
                'title' => 'ℹ️ Aktivitas Keamanan Akun',
                'desc' => "Aktivitas tercatat: `{$eventType}`",
                'color' => 0x6B7280 // Gray
            ]
        };
    }

    /**
     * Sederhanakan User-Agent string agar ringkas dan mudah dibaca di embed Discord
     */
    private static function summarizeUserAgent(string $ua): string {
        $os = 'Unknown OS';
        if (stripos($ua, 'Windows NT 10.0') !== false) $os = 'Windows 10/11';
        elseif (stripos($ua, 'Windows') !== false) $os = 'Windows';
        elseif (stripos($ua, 'Macintosh') !== false || stripos($ua, 'Mac OS X') !== false) $os = 'macOS';
        elseif (stripos($ua, 'Android') !== false) $os = 'Android';
        elseif (stripos($ua, 'iPhone') !== false || stripos($ua, 'iPad') !== false) $os = 'iOS';
        elseif (stripos($ua, 'Linux') !== false) $os = 'Linux';

        $browser = 'Browser';
        if (stripos($ua, 'Edg/') !== false) $browser = 'Microsoft Edge';
        elseif (stripos($ua, 'Chrome/') !== false) $browser = 'Google Chrome';
        elseif (stripos($ua, 'Firefox/') !== false) $browser = 'Mozilla Firefox';
        elseif (stripos($ua, 'Safari/') !== false && stripos($ua, 'Chrome/') === false) $browser = 'Apple Safari';

        return "{$browser} pada {$os}";
    }
}
