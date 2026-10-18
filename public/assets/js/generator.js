/**
 * PassVault — Password & Passphrase Generator
 * Generator kata sandi acak berbasis kriptografi dengan kalkulator entropi
 */

const PasswordGenerator = (() => {
    'use strict';

    const CHAR_SETS = {
        lowercase: 'abcdefghijklmnopqrstuvwxyz',
        uppercase: 'ABCDEFGHIJKLMNOPQRSTUVWXYZ',
        numbers: '0123456789',
        symbols: '!@#$%^&*()_+-=[]{}|;:,.<>?',
        ambiguous: 'il1Lo0O'
    };

    // Daftar kata ramah memori untuk mode Passphrase (Diceware style)
    const WORD_LIST = [
        'alpha', 'beacon', 'breeze', 'canvas', 'canyon', 'cascade', 'cipher', 'comet',
        'cradle', 'crystal', 'delta', 'echo', 'ember', 'falcon', 'feather', 'forest',
        'galaxy', 'glacier', 'gravity', 'harbor', 'horizon', 'island', 'jupiter', 'lagoon',
        'lantern', 'lotus', 'matrix', 'meadow', 'meteor', 'mirage', 'nebula', 'oasis',
        'orbit', 'origin', 'phoenix', 'planet', 'portal', 'prism', 'pulsar', 'pyramid',
        'quasar', 'radar', 'radiant', 'rainbow', 'ripple', 'river', 'rocket', 'safari',
        'saturn', 'shadow', 'shield', 'sierra', 'silver', 'solstice', 'spark', 'sphere',
        'summit', 'temple', 'thunder', 'timber', 'titan', 'tropic', 'valley', 'vector',
        'vertex', 'vortex', 'voyage', 'whisper', 'zenith', 'zephyr', 'zodiac', 'nebula'
    ];

    /**
     * Generate Password Acak Kriptografis
     */
    const generatePassword = (options = {}) => {
        const {
            length = 20,
            useUppercase = true,
            useLowercase = true,
            useNumbers = true,
            useSymbols = true,
            avoidAmbiguous = true
        } = options;

        let charset = '';
        if (useLowercase) charset += CHAR_SETS.lowercase;
        if (useUppercase) charset += CHAR_SETS.uppercase;
        if (useNumbers) charset += CHAR_SETS.numbers;
        if (useSymbols) charset += CHAR_SETS.symbols;

        if (avoidAmbiguous) {
            for (const ch of CHAR_SETS.ambiguous) {
                charset = charset.replaceAll(ch, '');
            }
        }

        if (!charset) {
            charset = CHAR_SETS.lowercase + CHAR_SETS.numbers;
        }

        const randomBytes = new Uint32Array(length);
        window.crypto.getRandomValues(randomBytes);

        let password = '';
        for (let i = 0; i < length; i++) {
            password += charset[randomBytes[i] % charset.length];
        }

        return password;
    };

    /**
     * Generate Passphrase Bergaya Diceware (misal: canyon-lotus-falcon-summit)
     */
    const generatePassphrase = (numWords = 4, separator = '-') => {
        const randomBytes = new Uint32Array(numWords);
        window.crypto.getRandomValues(randomBytes);

        const words = [];
        for (let i = 0; i < numWords; i++) {
            const word = WORD_LIST[randomBytes[i] % WORD_LIST.length];
            words.push(word);
        }

        return words.join(separator);
    };

    /**
     * Hitung Kekuatan Password (Entropi matematis)
     */
    const evaluateStrength = (password) => {
        if (!password) {
            return { score: 0, label: 'Kosong', color: 'bg-gray-200 text-gray-500', width: '0%' };
        }

        let poolSize = 0;
        if (/[a-z]/.test(password)) poolSize += 26;
        if (/[A-Z]/.test(password)) poolSize += 26;
        if (/[0-9]/.test(password)) poolSize += 10;
        if (/[^a-zA-Z0-9]/.test(password)) poolSize += 32;

        const entropy = password.length * Math.log2(Math.max(poolSize, 1));

        if (entropy < 35 || password.length < 8) {
            return { score: 1, label: 'Lemah', color: 'bg-rose-500', text: 'text-rose-500', width: '25%' };
        } else if (entropy < 60 || password.length < 12) {
            return { score: 2, label: 'Cukup', color: 'bg-amber-500', text: 'text-amber-500', width: '50%' };
        } else if (entropy < 85 || password.length < 16) {
            return { score: 3, label: 'Kuat', color: 'bg-blue-500', text: 'text-blue-500', width: '75%' };
        } else {
            return { score: 4, label: 'Sangat Kuat', color: 'bg-emerald-500', text: 'text-emerald-500', width: '100%' };
        }
    };

    return {
        generatePassword,
        generatePassphrase,
        evaluateStrength
    };
})();
