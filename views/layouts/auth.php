<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $e($title ?? 'Masuk — GembokX') ?></title>
    <meta name="csrf-token" content="<?= $csrf_token() ?>">
    <!-- Favicon & Touch Icon -->
    <link rel="icon" type="image/png" sizes="64x64" href="<?= $url('/assets/img/favicon.png?v=2.0') ?>">
    <link rel="icon" type="image/svg+xml" href="<?= $url('/assets/img/favicon.svg?v=2.0') ?>">
    <link rel="apple-touch-icon" href="<?= $url('/assets/img/icon-192.png?v=2.0') ?>">
    <!-- PWA Web App Manifest & Mobile Meta -->
    <link rel="manifest" href="<?= $url('/manifest.json') ?>">
    <meta name="theme-color" content="#0b0f19">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="GembokX">
    <!-- Tailwind CSS CDN -->
    <script>
        (function() {
            const _warn = console.warn;
            console.warn = function(...args) {
                if (args[0] && typeof args[0] === 'string' && args[0].includes('cdn.tailwindcss.com')) return;
                _warn.apply(console, args);
            };
        })();
    </script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        gray: {
                            750: '#262f40',
                            850: '#151d2e',
                            950: '#0b0f19'
                        }
                    },
                    fontFamily: {
                        sans: ['Inter', 'system-ui', 'sans-serif'],
                        mono: ['JetBrains Mono', 'Menlo', 'Courier New', 'monospace']
                    }
                }
            }
        }
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', system-ui, sans-serif; }
        .font-mono { font-family: 'JetBrains Mono', monospace; }
    </style>
    <script>
        window.PV_CONFIG = {
            baseUrl: '<?= $url('/') ?>',
            csrfToken: '<?= $csrf_token() ?>',
            routes: {
                prelogin: '<?= $url('/api/prelogin') ?>',
                login: '<?= $url('/api/login') ?>',
                register: '<?= $url('/api/register') ?>'
            }
        };
    </script>
</head>
<body class="bg-gray-100 dark:bg-gray-950 text-gray-800 dark:text-gray-100 min-h-screen flex flex-col justify-center items-center p-4 antialiased selection:bg-blue-500 selection:text-white">

    <div class="w-full max-w-md">
        <!-- Brand Header -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center mb-3">
                <img src="<?= $url('/assets/img/icon-192.png?v=2.0') ?>" class="w-16 h-16 rounded-2xl shadow-lg object-contain" alt="GembokX">
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">GembokX</h1>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 font-medium">Zero-Knowledge Encrypted Password Manager</p>
        </div>

        <!-- Auth Card -->
        <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl p-6 sm:p-8 shadow-xl">
            <?= $content ?>
        </div>

        <!-- Footer Notice -->
        <div class="text-center mt-6 text-xs text-gray-400 dark:text-gray-600">
            Enkripsi client-side AES-256-GCM • Master Password tidak pernah dikirim ke server
        </div>
    </div>

    <!-- Scripts -->
    <script src="<?= $url('/assets/js/argon2.umd.min.js?v=2.2') ?>"></script>
    <script src="<?= $url('/assets/js/crypto.js?v=2.3') ?>"></script>
    <script src="<?= $url('/assets/js/generator.js') ?>"></script>
    <script src="<?= $url('/assets/js/app.js') ?>"></script>

    <!-- Service Worker Registration -->
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('<?= $url('/sw.js') ?>')
                    .then(reg => console.log('[PWA] Service Worker terdaftar (Scope:', reg.scope, ')'))
                    .catch(err => console.warn('[PWA] Service Worker gagal daftar:', err));
            });
        }
    </script>
</body>
</html>
