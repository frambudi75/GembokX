<?php
namespace App\Services;

use App\Config\App;

/**
 * CacheService — High-performance Redis Cache with Seamless Fallback
 * 
 * Mendukung:
 * 1. Native PHP \Redis extension (jika terinstall).
 * 2. Pure PHP RESP Socket client (berkomunikasi langsung ke port 6379 tanpa extension tambahan).
 * 3. Graceful File Cache fallback jika Redis offline/unreachable.
 */
class CacheService {
    private static ?object $client = null;
    private static string $driver = 'none';
    private static bool $booted = false;

    public static function boot(): void {
        if (self::$booted) {
            return;
        }
        self::$booted = true;
        App::boot();

        $enabled = filter_var(App::env('REDIS_ENABLED', true), FILTER_VALIDATE_BOOLEAN);
        if (!$enabled) {
            self::$driver = 'file';
            return;
        }

        $host = App::env('REDIS_HOST', '127.0.0.1');
        $port = (int) App::env('REDIS_PORT', 6379);
        $pass = App::env('REDIS_PASSWORD', null);
        $prefix = App::env('REDIS_PREFIX', 'passvault:');

        // 1. Coba native PHP Redis extension
        if (extension_loaded('redis') && class_exists('\Redis')) {
            try {
                $redis = new \Redis();
                $connected = @$redis->connect($host, $port, 1.5);
                if ($connected) {
                    if (!empty($pass)) {
                        @$redis->auth($pass);
                    }
                    $redis->setOption(\Redis::OPT_PREFIX, $prefix);
                    self::$client = $redis;
                    self::$driver = 'redis_ext';
                    return;
                }
            } catch (\Throwable $e) {}
        }

        // 2. Coba pure PHP RESP Socket client
        try {
            $socketClient = new SimpleRedisSocketClient($host, $port, $pass, $prefix, 1.5);
            if ($socketClient->ping()) {
                self::$client = $socketClient;
                self::$driver = 'redis_socket';
                return;
            }
        } catch (\Throwable $e) {}

        // 3. Fallback ke File Cache
        self::$driver = 'file';
    }

    public static function getDriver(): string {
        self::boot();
        return self::$driver;
    }

    public static function get(string $key, mixed $default = null): mixed {
        self::boot();

        try {
            if (self::$driver === 'redis_ext') {
                $val = self::$client->get($key);
                if ($val === false || $val === null) return $default;
                $decoded = json_decode($val, true);
                return json_last_error() === JSON_ERROR_NONE ? $decoded : $val;
            }

            if (self::$driver === 'redis_socket') {
                $val = self::$client->get($key);
                if ($val === null) return $default;
                $decoded = json_decode($val, true);
                return json_last_error() === JSON_ERROR_NONE ? $decoded : $val;
            }

            // File fallback
            return self::fileGet($key, $default);
        } catch (\Throwable $e) {
            return $default;
        }
    }

    public static function set(string $key, mixed $value, int $ttl = 3600): bool {
        self::boot();
        $payload = is_scalar($value) && !is_bool($value) ? (string) $value : json_encode($value);

        try {
            if (self::$driver === 'redis_ext') {
                return (bool) self::$client->setex($key, $ttl, $payload);
            }

            if (self::$driver === 'redis_socket') {
                return self::$client->setex($key, $ttl, $payload);
            }

            // File fallback
            return self::fileSet($key, $value, $ttl);
        } catch (\Throwable $e) {
            return false;
        }
    }

    public static function forget(string $key): bool {
        self::boot();

        try {
            if (self::$driver === 'redis_ext') {
                return (bool) self::$client->del($key);
            }

            if (self::$driver === 'redis_socket') {
                return self::$client->del($key);
            }

            return self::fileForget($key);
        } catch (\Throwable $e) {
            return false;
        }
    }

    public static function remember(string $key, int $ttl, callable $callback): mixed {
        $cached = self::get($key);
        if ($cached !== null) {
            return $cached;
        }

        $fresh = $callback();
        if ($fresh !== null) {
            self::set($key, $fresh, $ttl);
        }
        return $fresh;
    }

    public static function flush(): bool {
        self::boot();
        try {
            if (self::$driver === 'redis_ext') {
                return (bool) self::$client->flushDB();
            }
            if (self::$driver === 'redis_socket') {
                return self::$client->flushDB();
            }
            return self::fileFlush();
        } catch (\Throwable $e) {
            return false;
        }
    }

    // ----------------------------------------------------
    // File Fallback Implementation
    // ----------------------------------------------------
    private static function getCacheDir(): string {
        $dir = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'cache';
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        return $dir;
    }

    private static function fileGet(string $key, mixed $default): mixed {
        $path = self::getCacheDir() . DIRECTORY_SEPARATOR . md5($key) . '.cache';
        if (!file_exists($path)) return $default;

        $content = @file_get_contents($path);
        if (!$content) return $default;

        $data = @unserialize($content);
        if (!$data || !is_array($data) || !isset($data['expires_at'], $data['payload'])) {
            @unlink($path);
            return $default;
        }

        if (time() > $data['expires_at']) {
            @unlink($path);
            return $default;
        }

        return $data['payload'];
    }

    private static function fileSet(string $key, mixed $value, int $ttl): bool {
        $path = self::getCacheDir() . DIRECTORY_SEPARATOR . md5($key) . '.cache';
        $data = [
            'expires_at' => time() + $ttl,
            'payload' => $value
        ];
        return (bool) @file_put_contents($path, serialize($data), LOCK_EX);
    }

    private static function fileForget(string $key): bool {
        $path = self::getCacheDir() . DIRECTORY_SEPARATOR . md5($key) . '.cache';
        if (file_exists($path)) {
            return @unlink($path);
        }
        return true;
    }

    private static function fileFlush(): bool {
        $dir = self::getCacheDir();
        $files = glob($dir . DIRECTORY_SEPARATOR . '*.cache');
        if ($files) {
            foreach ($files as $file) {
                @unlink($file);
            }
        }
        return true;
    }
}

/**
 * Lightweight RESP (REdis Serialization Protocol) Socket Client
 */
class SimpleRedisSocketClient {
    private string $host;
    private int $port;
    private ?string $password;
    private string $prefix;
    private float $timeout;
    /** @var resource|null */
    private $fp = null;

    public function __construct(string $host, int $port, ?string $password, string $prefix = '', float $timeout = 1.5) {
        $this->host = $host;
        $this->port = $port;
        $this->password = $password;
        $this->prefix = $prefix;
        $this->timeout = $timeout;
    }

    private function connect(): bool {
        if ($this->fp && is_resource($this->fp) && !feof($this->fp)) {
            return true;
        }

        $errno = 0;
        $errstr = '';
        $this->fp = @fsockopen($this->host, $this->port, $errno, $errstr, $this->timeout);
        if (!$this->fp) {
            return false;
        }

        stream_set_timeout($this->fp, (int) $this->timeout, (int) (($this->timeout - (int) $this->timeout) * 1000000));

        if (!empty($this->password)) {
            $this->sendCommand('AUTH', [$this->password]);
            $res = $this->readResponse();
            if ($res !== 'OK') {
                $this->close();
                return false;
            }
        }

        return true;
    }

    public function ping(): bool {
        if (!$this->connect()) return false;
        $this->sendCommand('PING');
        return $this->readResponse() === 'PONG';
    }

    public function get(string $key): ?string {
        if (!$this->connect()) return null;
        $this->sendCommand('GET', [$this->prefix . $key]);
        $res = $this->readResponse();
        return is_string($res) ? $res : null;
    }

    public function setex(string $key, int $ttl, string $value): bool {
        if (!$this->connect()) return false;
        $this->sendCommand('SETEX', [$this->prefix . $key, (string) $ttl, $value]);
        $res = $this->readResponse();
        return $res === 'OK';
    }

    public function del(string $key): bool {
        if (!$this->connect()) return false;
        $this->sendCommand('DEL', [$this->prefix . $key]);
        $res = $this->readResponse();
        return (int) $res > 0;
    }

    public function flushDB(): bool {
        if (!$this->connect()) return false;
        $this->sendCommand('FLUSHDB');
        return $this->readResponse() === 'OK';
    }

    private function sendCommand(string $command, array $args = []): void {
        $parts = array_merge([$command], $args);
        $payload = '*' . count($parts) . "\r\n";
        foreach ($parts as $part) {
            $payload .= '$' . strlen($part) . "\r\n" . $part . "\r\n";
        }
        @fwrite($this->fp, $payload);
    }

    private function readResponse(): mixed {
        $line = @fgets($this->fp);
        if ($line === false) return null;

        $type = $line[0];
        $content = substr($line, 1, -2);

        switch ($type) {
            case '+': // Simple string
                return $content;
            case ':': // Integer
                return (int) $content;
            case '$': // Bulk string
                $length = (int) $content;
                if ($length === -1) return null;
                $data = '';
                $remaining = $length;
                while ($remaining > 0 && !feof($this->fp)) {
                    $chunk = @fread($this->fp, min($remaining, 8192));
                    if ($chunk === false) break;
                    $data .= $chunk;
                    $remaining -= strlen($chunk);
                }
                @fread($this->fp, 2); // Read trailing \r\n
                return $data;
            case '-': // Error
                return null;
            case '*': // Array
                return null;
            default:
                return null;
        }
    }

    public function close(): void {
        if ($this->fp && is_resource($this->fp)) {
            @fclose($this->fp);
            $this->fp = null;
        }
    }

    public function __destruct() {
        $this->close();
    }
}
