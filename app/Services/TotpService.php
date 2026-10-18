<?php
namespace App\Services;

class TotpService {
    private static string $base32Chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

    /**
     * Generate 16-karakter Base32 secret acak
     */
    public static function generateSecret(int $length = 16): string {
        $secret = '';
        $max = strlen(self::$base32Chars) - 1;
        for ($i = 0; $i < $length; $i++) {
            $secret .= self::$base32Chars[random_int(0, $max)];
        }
        return $secret;
    }

    /**
     * Hitung TOTP 6-digit untuk secret dan timestamp tertentu (interval 30 detik)
     */
    public static function getCode(string $secret, ?int $timestamp = null): string {
        if ($timestamp === null) {
            $timestamp = time();
        }

        $timeSlice = (int) floor($timestamp / 30);
        $timeBytes = pack('N*', 0) . pack('N*', $timeSlice);

        $secretBytes = self::base32Decode($secret);
        $hash = hash_hmac('sha1', $timeBytes, $secretBytes, true);

        $offset = ord(substr($hash, -1)) & 0x0F;
        $hashPart = substr($hash, $offset, 4);
        $value = unpack('N', $hashPart)[1] & 0x7FFFFFFF;

        $pin = $value % 1000000;
        return str_pad((string) $pin, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Verifikasi kode TOTP dengan toleransi jeda waktu (+- 30 detik)
     */
    public static function verifyCode(string $secret, string $code, int $discrepancy = 1): bool {
        $currentTime = time();
        $code = trim($code);

        for ($i = -$discrepancy; $i <= $discrepancy; $i++) {
            $calculated = self::getCode($secret, $currentTime + ($i * 30));
            if (hash_equals($calculated, $code)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Buat URL otpauth:// untuk pemindaian aplikasi Google Authenticator / Authy
     */
    public static function getOtpAuthUrl(string $label, string $secret, string $issuer = 'PassVault'): string {
        $encodedLabel = rawurlencode($label);
        $encodedIssuer = rawurlencode($issuer);
        return "otpauth://totp/{$encodedIssuer}:{$encodedLabel}?secret={$secret}&issuer={$encodedIssuer}&algorithm=SHA1&digits=6&period=30";
    }

    private static function base32Decode(string $secret): string {
        $secret = strtoupper($secret);
        $binaryString = '';
        
        for ($i = 0; $i < strlen($secret); $i++) {
            $char = $secret[$i];
            $pos = strpos(self::$base32Chars, $char);
            if ($pos !== false) {
                $binaryString .= str_pad(decbin($pos), 5, '0', STR_PAD_LEFT);
            }
        }

        $bytes = '';
        $length = strlen($binaryString);
        for ($i = 0; $i + 8 <= $length; $i += 8) {
            $bytes .= chr(bindec(substr($binaryString, $i, 8)));
        }

        return $bytes;
    }
}
