/**
 * PassVault — WebAuthn Platform Authenticator (Windows Hello, Touch ID, Fingerprint)
 * Client-Side Biometric Vault Unlock
 */

const BiometricAuth = (() => {
    'use strict';

    const isAvailable = async () => {
        if (!window.PublicKeyCredential) return false;
        if (!PublicKeyCredential.isUserVerifyingPlatformAuthenticatorAvailable) return false;
        try {
            return await PublicKeyCredential.isUserVerifyingPlatformAuthenticatorAvailable();
        } catch (_) {
            return false;
        }
    };

    const isEnabled = () => {
        return localStorage.getItem('pv_bio_enabled') === '1' && localStorage.getItem('pv_bio_cred_id') !== null;
    };

    /**
     * Mendaftarkan autentikator platform (Windows Hello / Touch ID / Face ID)
     */
    const registerBiometrics = async (activeVaultKey) => {
        const available = await isAvailable();
        if (!available) {
            throw new Error('Sensor biometrik (Windows Hello / Touch ID) tidak didukung pada perangkat ini.');
        }

        if (!activeVaultKey) {
            throw new Error('Brankas harus dibuka terlebih dahulu sebelum mengaktifkan biometrik.');
        }

        // Export active vault key ke base64
        const rawVault = await window.crypto.subtle.exportKey('raw', activeVaultKey);
        const vaultKeyB64 = VaultCrypto.bufferToBase64(rawVault);

        // Challenge acak 32-byte
        const challenge = new Uint8Array(32);
        window.crypto.getRandomValues(challenge);

        const userIdBytes = new Uint8Array(16);
        window.crypto.getRandomValues(userIdBytes);

        const user = window.PV_CONFIG.currentUser || { username: 'user', email: 'user@local' };

        const credential = await navigator.credentials.create({
            publicKey: {
                challenge: challenge,
                rp: { name: 'PassVault Security' },
                user: {
                    id: userIdBytes,
                    name: user.email || 'passvault-user',
                    displayName: user.username || 'Pengguna PassVault'
                },
                pubKeyCredParams: [
                    { type: 'public-key', alg: -7 },  // ES256
                    { type: 'public-key', alg: -257 } // RS256
                ],
                authenticatorSelection: {
                    authenticatorAttachment: 'platform',
                    userVerification: 'required',
                    requireResidentKey: false
                },
                timeout: 60000
            }
        });

        if (!credential) throw new Error('Pendaftaran biometrik dibatalkan.');

        const credIdB64 = VaultCrypto.bufferToBase64(credential.rawId);

        // Derivasi kunci lokal dari Credential ID untuk mengenkripsi vault key di localStorage
        const enc = new TextEncoder();
        const baseKey = await window.crypto.subtle.importKey(
            'raw',
            enc.encode('pv_bio_wrap_' + credIdB64),
            'PBKDF2',
            false,
            ['deriveKey']
        );
        const wrappingKey = await window.crypto.subtle.deriveKey(
            { name: 'PBKDF2', salt: credential.rawId, iterations: 100000, hash: 'SHA-256' },
            baseKey,
            { name: 'AES-GCM', length: 256 },
            false,
            ['encrypt', 'decrypt']
        );

        const iv = window.crypto.getRandomValues(new Uint8Array(12));
        const encryptedVaultKey = await window.crypto.subtle.encrypt(
            { name: 'AES-GCM', iv: iv },
            wrappingKey,
            enc.encode(vaultKeyB64)
        );

        localStorage.setItem('pv_bio_enabled', '1');
        localStorage.setItem('pv_bio_cred_id', credIdB64);
        localStorage.setItem('pv_bio_enc_key', VaultCrypto.bufferToBase64(encryptedVaultKey));
        localStorage.setItem('pv_bio_iv', VaultCrypto.bufferToBase64(iv.buffer));

        return true;
    };

    /**
     * Buka kunci brankas menggunakan sensor Biometrik / Windows Hello
     */
    const authenticateBiometrics = async () => {
        if (!isEnabled()) {
            throw new Error('Biometrik belum diaktifkan di perangkat ini.');
        }

        const credIdB64 = localStorage.getItem('pv_bio_cred_id');
        const encVaultKeyB64 = localStorage.getItem('pv_bio_enc_key');
        const ivB64 = localStorage.getItem('pv_bio_iv');

        if (!credIdB64 || !encVaultKeyB64 || !ivB64) {
            throw new Error('Data kredensial biometrik tidak lengkap.');
        }

        const rawCredId = VaultCrypto.base64ToBuffer(credIdB64);
        const challenge = new Uint8Array(32);
        window.crypto.getRandomValues(challenge);

        // Minta verifikasi pengguna (Sidik Jari / Windows Hello / Face ID)
        const assertion = await navigator.credentials.get({
            publicKey: {
                challenge: challenge,
                allowCredentials: [{
                    type: 'public-key',
                    id: rawCredId,
                    transports: ['internal']
                }],
                userVerification: 'required',
                timeout: 60000
            }
        });

        if (!assertion) throw new Error('Verifikasi biometrik dibatalkan.');

        // Rekonstruksi wrapping key
        const enc = new TextEncoder();
        const baseKey = await window.crypto.subtle.importKey(
            'raw',
            enc.encode('pv_bio_wrap_' + credIdB64),
            'PBKDF2',
            false,
            ['deriveKey']
        );
        const wrappingKey = await window.crypto.subtle.deriveKey(
            { name: 'PBKDF2', salt: rawCredId, iterations: 100000, hash: 'SHA-256' },
            baseKey,
            { name: 'AES-GCM', length: 256 },
            false,
            ['encrypt', 'decrypt']
        );

        const decryptedVaultRaw = await window.crypto.subtle.decrypt(
            { name: 'AES-GCM', iv: new Uint8Array(VaultCrypto.base64ToBuffer(ivB64)) },
            wrappingKey,
            VaultCrypto.base64ToBuffer(encVaultKeyB64)
        );

        const dec = new TextDecoder();
        const vaultKeyB64 = dec.decode(decryptedVaultRaw);
        const rawVaultBuffer = VaultCrypto.base64ToBuffer(vaultKeyB64);

        const importedVaultKey = await window.crypto.subtle.importKey(
            'raw',
            rawVaultBuffer,
            { name: 'AES-GCM', length: 256 },
            true,
            ['encrypt', 'decrypt']
        );

        return importedVaultKey;
    };

    const disableBiometrics = () => {
        localStorage.removeItem('pv_bio_enabled');
        localStorage.removeItem('pv_bio_cred_id');
        localStorage.removeItem('pv_bio_enc_key');
        localStorage.removeItem('pv_bio_iv');
    };

    return {
        isAvailable,
        isEnabled,
        registerBiometrics,
        authenticateBiometrics,
        disableBiometrics
    };
})();
