<?php
/**
 * PassVault — Database Seeder
 * Membuat akun user dan beberapa item contoh dengan enkripsi Zero-Knowledge
 */

declare(strict_types=1);

require_once __DIR__ . '/app/Config/App.php';
require_once __DIR__ . '/app/Config/Database.php';
require_once __DIR__ . '/app/Models/User.php';
require_once __DIR__ . '/app/Models/VaultItem.php';
require_once __DIR__ . '/app/Models/Folder.php';

use App\Config\App;
use App\Config\Database;
use App\Models\User;
use App\Models\VaultItem;
use App\Models\Folder;

App::boot();
$pdo = Database::getConnection();

// Akun Target Pengguna Demo
$email = 'demo@gembokx.local';
$username = 'admin';
$password = 'GembokXAdmin2026!';

echo "=== GembokX Seeder ===\n";
echo "Demo User: {$username} ({$email})\n";
echo "Password: {$password}\n\n";

// Cek apakah user sudah ada
$existing = User::findByEmail($email);
if ($existing) {
    echo "Pengguna {$email} sudah ada, menghapus data lama untuk seeding ulang...\n";
    $pdo->prepare("DELETE FROM users WHERE email = ?")->execute([$email]);
}

// 1. Generate Salt Kriptografis
$saltHex = bin2hex(random_bytes(32));
$iterations = 600000;

// 2. Derivasi Master Key (PBKDF2 600.000 iterasi)
$masterKey = hash_pbkdf2('sha256', $password, hex2bin($saltHex), $iterations, 32, true);

// 3. Derivasi Auth Key untuk otentikasi login
$authKey = hash_hmac('sha256', 'auth-key-derivation', $masterKey, false);

// 4. Buat Kunci Brankas Simetris Acak (Vault Key: 32 bytes AES-256)
$rawVaultKey = random_bytes(32);
$vaultKeyIv = random_bytes(12);

$tag = '';
$cipherVaultKey = openssl_encrypt($rawVaultKey, 'aes-256-gcm', $masterKey, OPENSSL_RAW_DATA, $vaultKeyIv, $tag, '', 16);
$fullCipherVaultKey = $cipherVaultKey . $tag;

$encryptedVaultKeyB64 = base64_encode($fullCipherVaultKey);
$vaultKeyIvB64 = base64_encode($vaultKeyIv);

// 5. Generate Emergency Recovery Code
$recoveryCode = 'PV-7K9M-X4WQ-2P8N-L3T5';

// 6. Simpan User ke Database
$userId = User::create([
    'email' => $email,
    'username' => $username,
    'auth_key' => $authKey,
    'kdf_salt' => $saltHex,
    'kdf_iterations' => $iterations,
    'encrypted_vault_key' => $encryptedVaultKeyB64,
    'vault_key_iv' => $vaultKeyIvB64,
    'recovery_code' => $recoveryCode
]);

echo "Akun pengguna berhasil dibuat! ID: {$userId}\n";

// Helper enkripsi data dengan Vault Key
function encryptPayload(array $data, string $rawKey): array {
    $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    $iv = random_bytes(12);
    $tag = '';
    $ciphertext = openssl_encrypt($json, 'aes-256-gcm', $rawKey, OPENSSL_RAW_DATA, $iv, $tag, '', 16);
    return [
        'ciphertext' => base64_encode($ciphertext . $tag),
        'iv' => base64_encode($iv),
        'auth_tag' => 'gcm-embedded'
    ];
}

// 7. Buat Folder Contoh Terenkripsi
$folderNames = ['Pekerjaan', 'Sosial Media', 'Keuangan'];
$folderIds = [];

foreach ($folderNames as $fn) {
    $enc = encryptPayload(['name' => $fn], $rawVaultKey);
    // Simpan string nama folder terenkripsi
    $stmt = $pdo->prepare("INSERT INTO folders (user_id, encrypted_name, iv) VALUES (?, ?, ?)");
    $stmt->execute([$userId, $enc['ciphertext'], $enc['iv']]);
    $folderIds[$fn] = (int) $pdo->lastInsertId();
}

echo "Berhasil membuat 3 folder kategori terenkripsi.\n";

// 8. Buat Sample Vault Items
$sampleItems = [
    [
        'type' => 'login',
        'folder' => 'Pekerjaan',
        'fav' => 1,
        'data' => [
            'name' => 'GitHub Enterprise Demo',
            'username' => 'developer@example.com',
            'password' => 'ghp_demoSecretToken99238472918237',
            'url' => 'https://github.com/login',
            'notes' => 'Akun contoh repository proyek dan deployment.',
            'totp_seed' => 'JBSWY3DPEHPK3PXP'
        ]
    ],
    [
        'type' => 'login',
        'folder' => 'Pekerjaan',
        'fav' => 1,
        'data' => [
            'name' => 'VPS Server SSH (Ubuntu)',
            'username' => 'root',
            'password' => 'VpS-s3cure#2026-Demo',
            'url' => 'ssh://192.168.1.100',
            'notes' => 'Port SSH: 2222. Kunci privat RSA tersimpan di catatan rahasia.'
        ]
    ],
    [
        'type' => 'login',
        'folder' => 'Sosial Media',
        'fav' => 0,
        'data' => [
            'name' => 'Google Workspace Demo',
            'username' => 'demo@example.com',
            'password' => 'Google@DemoSecure#2026',
            'url' => 'https://accounts.google.com',
            'notes' => 'Email contoh kontak profesional.'
        ]
    ],
    [
        'type' => 'card',
        'folder' => 'Keuangan',
        'fav' => 1,
        'data' => [
            'name' => 'BCA Debit Mastercard',
            'cardholder' => 'DEMO USER',
            'brand' => 'Mastercard',
            'number' => '5210889923417721',
            'exp_month' => '08',
            'exp_year' => '2029',
            'cvv' => '782',
            'notes' => 'Limit transaksi online harian: Rp 25.000.000'
        ]
    ],
    [
        'type' => 'note',
        'folder' => 'Keuangan',
        'fav' => 0,
        'data' => [
            'name' => 'Recovery Seed Kripto (12 Kata)',
            'notes' => "witch collapse practice feed shame open\ndesert canyon harbor radiant velvet zenith\n\nJANGAN PERNAH DIBAGIKAN KE SIAPAPUN!"
        ]
    ]
];

foreach ($sampleItems as $si) {
    $enc = encryptPayload($si['data'], $rawVaultKey);
    $fid = isset($folderIds[$si['folder']]) ? $folderIds[$si['folder']] : null;

    VaultItem::create([
        'user_id' => $userId,
        'folder_id' => $fid,
        'item_type' => $si['type'],
        'is_favorite' => $si['fav'],
        'encrypted_payload' => $enc['ciphertext'],
        'iv' => $enc['iv'],
        'auth_tag' => $enc['auth_tag']
    ]);
}

echo "Berhasil membuat 5 item brankas terenkripsi (Login, Kartu, Catatan Rahasia).\n";
echo "=== SEEDING SELESAI DENGAN SUKSES ===\n";
