<?php
/**
 * GembokX — Automated CI Suite
 * Validates Core Classes, Zero-Knowledge Crypto, TOTP Engine, and Manifest Integrity
 */

declare(strict_types=1);

echo "===========================================\n";
echo "🔐 Running GembokX Automated CI Suite\n";
echo "===========================================\n\n";

$errors = [];

// Autoload helper
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $baseDir = __DIR__ . '/../app/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) return;
    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
    if (file_exists($file)) require_once $file;
});

// Test 1: Config & Environment Boot
echo "[1/5] Testing App Configuration Boot... ";
try {
    \App\Config\App::boot();
    $appName = \App\Config\App::env('APP_NAME', 'GembokX');
    if ($appName !== 'GembokX') {
        throw new \Exception("Expected APP_NAME to be 'GembokX', got '$appName'");
    }
    echo "PASSED\n";
} catch (\Throwable $e) {
    echo "FAILED: " . $e->getMessage() . "\n";
    $errors[] = "Config Boot: " . $e->getMessage();
}

// Test 2: TOTP Authentication Engine
echo "[2/5] Testing TOTP 2FA Engine (RFC 6238)... ";
try {
    $secret = \App\Services\TotpService::generateSecret(16);
    if (strlen($secret) !== 16 || !preg_match('/^[A-Z2-7]+$/', $secret)) {
        throw new \Exception("Invalid Base32 secret generated: '$secret'");
    }

    $otpUrl = \App\Services\TotpService::getOtpAuthUrl('user@gembokx.local', $secret, 'GembokX');
    if (!str_contains($otpUrl, 'otpauth://totp/GembokX') || !str_contains($otpUrl, "secret=$secret")) {
        throw new \Exception("Invalid otpauth URI: $otpUrl");
    }

    $code = \App\Services\TotpService::getCode($secret);
    if (strlen($code) !== 6 || !ctype_digit($code)) {
        throw new \Exception("Generated TOTP code must be 6 digits, got: '$code'");
    }

    $isValid = \App\Services\TotpService::verifyCode($secret, $code, 1);
    if (!$isValid) {
        throw new \Exception("TOTP code verification failed for newly generated code");
    }
    echo "PASSED\n";
} catch (\Throwable $e) {
    echo "FAILED: " . $e->getMessage() . "\n";
    $errors[] = "TOTP Engine: " . $e->getMessage();
}

// Test 3: Zero-Knowledge AES-256-GCM Roundtrip
echo "[3/5] Testing AES-256-GCM Cryptographic Roundtrip... ";
try {
    $key = random_bytes(32); // 256-bit key
    $iv = random_bytes(12);  // 96-bit IV
    $plaintext = json_encode(['username' => 'testuser', 'password' => 'SuperSecretP@ssw0rd!123']);
    $tag = '';

    $ciphertext = openssl_encrypt($plaintext, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $iv, $tag, '', 16);
    if ($ciphertext === false || strlen($tag) !== 16) {
        throw new \Exception("OpenSSL AES-256-GCM encryption failed");
    }

    $decrypted = openssl_decrypt($ciphertext, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $iv, $tag);
    if ($decrypted !== $plaintext) {
        throw new \Exception("Decrypted plaintext does not match original payload");
    }
    echo "PASSED\n";
} catch (\Throwable $e) {
    echo "FAILED: " . $e->getMessage() . "\n";
    $errors[] = "AES-256-GCM: " . $e->getMessage();
}

// Test 4: Manifest & PWA Integrity
echo "[4/5] Testing Manifest JSON Structure... ";
try {
    $manifestPath = __DIR__ . '/../public/manifest.json';
    if (!file_exists($manifestPath)) throw new \Exception("public/manifest.json not found");
    $manifest = json_decode(file_get_contents($manifestPath), true);
    if (!is_array($manifest) || ($manifest['short_name'] ?? '') !== 'GembokX') {
        throw new \Exception("manifest.json has invalid short_name");
    }

    $extManifestPath = __DIR__ . '/../extension/manifest.json';
    if (!file_exists($extManifestPath)) throw new \Exception("extension/manifest.json not found");
    $extManifest = json_decode(file_get_contents($extManifestPath), true);
    if (!is_array($extManifest) || ($extManifest['manifest_version'] ?? 0) !== 3) {
        throw new \Exception("extension/manifest.json must be Manifest V3");
    }
    echo "PASSED\n";
} catch (\Throwable $e) {
    echo "FAILED: " . $e->getMessage() . "\n";
    $errors[] = "Manifest Integrity: " . $e->getMessage();
}

// Test 5: Static Image Asset Verification
echo "[5/5] Testing Branding Asset Presence... ";
try {
    $requiredAssets = [
        __DIR__ . '/../public/assets/img/icon-192.png',
        __DIR__ . '/../public/assets/img/icon-512.png',
        __DIR__ . '/../public/assets/img/favicon.png',
        __DIR__ . '/../public/assets/img/logo.png',
        __DIR__ . '/../extension/icon-128.png'
    ];
    foreach ($requiredAssets as $asset) {
        if (!file_exists($asset) || filesize($asset) < 100) {
            throw new \Exception("Missing or empty branding asset: " . basename($asset));
        }
    }
    echo "PASSED\n";
} catch (\Throwable $e) {
    echo "FAILED: " . $e->getMessage() . "\n";
    $errors[] = "Brand Assets: " . $e->getMessage();
}

echo "\n-------------------------------------------\n";
if (!empty($errors)) {
    echo "❌ CI SUITE FAILED with " . count($errors) . " error(s):\n";
    foreach ($errors as $err) echo "  - $err\n";
    exit(1);
}

echo "✅ ALL CI TESTS PASSED SUCCESSFULLY!\n";
echo "===========================================\n";
exit(0);
