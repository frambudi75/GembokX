/**
 * PassVault — Client-Side Web Crypto Engine
 * Zero-Knowledge End-to-End Cryptography (PBKDF2 + AES-256-GCM)
 */

const VaultCrypto = (() => {
    'use strict';

    // Helper Konversi ArrayBuffer <-> Base64 / Hex
    const bufferToBase64 = (buf) => {
        const bin = String.fromCharCode(...new Uint8Array(buf));
        return window.btoa(bin);
    };

    const base64ToBuffer = (b64) => {
        const bin = window.atob(b64);
        const bytes = new Uint8Array(bin.length);
        for (let i = 0; i < bin.length; i++) {
            bytes[i] = bin.charCodeAt(i);
        }
        return bytes.buffer;
    };

    const bufferToHex = (buf) => {
        return Array.from(new Uint8Array(buf))
            .map(b => b.toString(16).padStart(2, '0'))
            .join('');
    };

    const hexToBuffer = (hex) => {
        const bytes = new Uint8Array(hex.length / 2);
        for (let i = 0; i < bytes.length; i++) {
            bytes[i] = parseInt(hex.substr(i * 2, 2), 16);
        }
        return bytes.buffer;
    };

    /**
     * Generate Salt Kriptografis Acak
     */
    const generateSalt = (bytes = 32) => {
        const arr = new Uint8Array(bytes);
        window.crypto.getRandomValues(arr);
        return bufferToHex(arr);
    };

    /**
     * Generate IV Acak 12-Byte (96-bit) untuk AES-GCM
     */
    const generateIv = (bytes = 12) => {
        const arr = new Uint8Array(bytes);
        window.crypto.getRandomValues(arr);
        return arr;
    };

    /**
     * Derivasi Master Key dari Master Password menggunakan PBKDF2 (SHA-256)
     */
    const deriveMasterKey = async (password, saltHex, iterations = 600000) => {
        const enc = new TextEncoder();
        const baseKey = await window.crypto.subtle.importKey(
            'raw',
            enc.encode(password),
            'PBKDF2',
            false,
            ['deriveKey', 'deriveBits']
        );

        const saltBuf = hexToBuffer(saltHex);

        const masterKey = await window.crypto.subtle.deriveKey(
            {
                name: 'PBKDF2',
                salt: saltBuf,
                iterations: iterations,
                hash: 'SHA-256'
            },
            baseKey,
            { name: 'AES-GCM', length: 256 },
            true, // Dapat diekspor untuk membuat Auth Key
            ['encrypt', 'decrypt', 'wrapKey', 'unwrapKey']
        );

        return masterKey;
    };

    /**
     * Derivasi Auth Key dari Master Key untuk dikirim ke server saat login/register
     * Server hanya menerima Auth Key ini, sehingga Master Password tetap Zero-Knowledge.
     */
    const deriveAuthKey = async (masterKey) => {
        const rawMaster = await window.crypto.subtle.exportKey('raw', masterKey);
        const hmacKey = await window.crypto.subtle.importKey(
            'raw',
            rawMaster,
            { name: 'HMAC', hash: 'SHA-256' },
            false,
            ['sign']
        );

        const enc = new TextEncoder();
        const signature = await window.crypto.subtle.sign('HMAC', hmacKey, enc.encode('auth-key-derivation'));
        return bufferToHex(signature);
    };

    /**
     * Membuat Kunci Brankas Simetris Acak (Vault Symmetric Key: 256-bit AES-GCM)
     */
    const generateVaultKey = async () => {
        return await window.crypto.subtle.generateKey(
            { name: 'AES-GCM', length: 256 },
            true,
            ['encrypt', 'decrypt']
        );
    };

    /**
     * Enkripsi Vault Key menggunakan Master Key
     */
    const encryptVaultKey = async (vaultKey, masterKey) => {
        const rawKey = await window.crypto.subtle.exportKey('raw', vaultKey);
        const iv = generateIv(12);

        const encrypted = await window.crypto.subtle.encrypt(
            { name: 'AES-GCM', iv: iv },
            masterKey,
            rawKey
        );

        return {
            encryptedKey: bufferToBase64(encrypted),
            iv: bufferToBase64(iv)
        };
    };

    /**
     * Dekripsi Vault Key menggunakan Master Key
     */
    const decryptVaultKey = async (encryptedB64, ivB64, masterKey) => {
        const encrypted = base64ToBuffer(encryptedB64);
        const iv = base64ToBuffer(ivB64);

        const decrypted = await window.crypto.subtle.decrypt(
            { name: 'AES-GCM', iv: new Uint8Array(iv) },
            masterKey,
            encrypted
        );

        return await window.crypto.subtle.importKey(
            'raw',
            decrypted,
            { name: 'AES-GCM', length: 256 },
            true,
            ['encrypt', 'decrypt']
        );
    };

    /**
     * Enkripsi Data Objek (Item Vault) menggunakan Vault Key
     */
    const encryptData = async (dataObj, vaultKey) => {
        const jsonStr = JSON.stringify(dataObj);
        const enc = new TextEncoder();
        const iv = generateIv(12);

        // AES-GCM di WebCrypto otomatis menyematkan 128-bit authentication tag di akhir ciphertext
        const ciphertext = await window.crypto.subtle.encrypt(
            { name: 'AES-GCM', iv: iv },
            vaultKey,
            enc.encode(jsonStr)
        );

        return {
            ciphertext: bufferToBase64(ciphertext),
            iv: bufferToBase64(iv),
            authTag: 'gcm-embedded'
        };
    };

    /**
     * Dekripsi Data Objek menggunakan Vault Key
     */
    const decryptData = async (ciphertextB64, ivB64, vaultKey) => {
        try {
            const ciphertext = base64ToBuffer(ciphertextB64);
            const iv = base64ToBuffer(ivB64);

            const decrypted = await window.crypto.subtle.decrypt(
                { name: 'AES-GCM', iv: new Uint8Array(iv) },
                vaultKey,
                ciphertext
            );

            const dec = new TextDecoder();
            return JSON.parse(dec.decode(decrypted));
        } catch (err) {
            console.error('Gagal dekripsi item:', err);
            return null;
        }
    };

    /**
     * Generate Kode Pemulihan Darurat (Emergency Recovery Key)
     */
    const generateRecoveryCode = () => {
        const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        const segments = [];
        for (let s = 0; s < 4; s++) {
            let seg = '';
            for (let i = 0; i < 4; i++) {
                const randIndex = Math.floor(Math.random() * chars.length);
                seg += chars[randIndex];
            }
            segments.push(seg);
        }
        return 'PV-' + segments.join('-');
    };

    return {
        generateSalt,
        deriveMasterKey,
        deriveAuthKey,
        generateVaultKey,
        encryptVaultKey,
        decryptVaultKey,
        encryptData,
        decryptData,
        generateRecoveryCode,
        bufferToBase64,
        base64ToBuffer
    };
})();
