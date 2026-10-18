/**
 * PassVault — Client-Side Web Crypto Engine
 * Zero-Knowledge End-to-End Cryptography (PBKDF2 + AES-256-GCM)
 * Termasuk Polyfill SubtleCrypto mandiri untuk akses HTTP / IP LAN (Insecure Context)
 */

// Inisialisasi Polyfill jika window.crypto.subtle dinonaktifkan oleh browser (misal akses via IP HTTP LAN)
(function initWebCryptoFallback() {
    if (typeof window === 'undefined') return;
    if (!window.crypto) {
        window.crypto = {};
    }
    if (!window.crypto.getRandomValues) {
        window.crypto.getRandomValues = function (arr) {
            for (let i = 0; i < arr.length; i++) {
                arr[i] = Math.floor(Math.random() * 256);
            }
            return arr;
        };
    }
    if (!window.crypto.subtle) {
        console.warn('GembokX: window.crypto.subtle tidak tersedia (Konteks HTTP LAN). Mengaktifkan WebCrypto Pure-JS Fallback.');

        const K = [
            0x428a2f98, 0x71374491, 0xb5c0fbcf, 0xe9b5dba5, 0x3956c25b, 0x59f111f1, 0x923f82a4, 0xab1c5ed5,
            0xd807aa98, 0x12835b01, 0x243185be, 0x550c7dc3, 0x72be5d74, 0x80deb1fe, 0x9bdc06a7, 0xc19bf174,
            0xe49b69c1, 0xefbe4786, 0x0fc19dc6, 0x240ca1cc, 0x2de92c6f, 0x4a7484aa, 0x5cb0a9dc, 0x76f988da,
            0x983e5152, 0xa831c66d, 0xb00327c8, 0xbf597fc7, 0xc6e00bf3, 0xd5a79147, 0x06ca6351, 0x14292967,
            0x27b70a85, 0x2e1b2138, 0x4d2c6dfc, 0x53380d13, 0x650a7354, 0x766a0abb, 0x81c2c92e, 0x92722c85,
            0xa2bfe8a1, 0xa81a664b, 0xc24b8b70, 0xc76c51a3, 0xd192e819, 0xd6990624, 0xf40e3585, 0x106aa070,
            0x19a4c116, 0x1e376c08, 0x2748774c, 0x34b0bcb5, 0x391c0cb3, 0x4ed8aa4a, 0x5b9cca4f, 0x682e6ff3,
            0x748f82ee, 0x78a5636f, 0x84c87814, 0x8cc70208, 0x90befffa, 0xa4506ceb, 0xbef9a3f7, 0xc67178f2
        ];

        function sha256Bytes(msgBytes) {
            let H = [0x6a09e667, 0xbb67ae85, 0x3c6ef372, 0xa54ff53a, 0x510e527f, 0x9b05688c, 0x1f83d9ab, 0x5be0cd19];
            const l = msgBytes.length;
            const bitLen = l * 8;
            const padLen = (l % 64 < 56) ? (56 - (l % 64)) : (120 - (l % 64));
            const padded = new Uint8Array(l + padLen + 8);
            padded.set(msgBytes);
            padded[l] = 0x80;
            const view = new DataView(padded.buffer);
            view.setUint32(padded.length - 4, bitLen >>> 0);
            view.setUint32(padded.length - 8, Math.floor(bitLen / 0x100000000));

            const W = new Int32Array(64);
            for (let i = 0; i < padded.length; i += 64) {
                for (let t = 0; t < 16; t++) {
                    W[t] = view.getInt32(i + t * 4);
                }
                for (let t = 16; t < 64; t++) {
                    const s0 = ((W[t-15] >>> 7) | (W[t-15] << 25)) ^ ((W[t-15] >>> 18) | (W[t-15] << 14)) ^ (W[t-15] >>> 3);
                    const s1 = ((W[t-2] >>> 17) | (W[t-2] << 15)) ^ ((W[t-2] >>> 19) | (W[t-2] << 13)) ^ (W[t-2] >>> 10);
                    W[t] = (W[t-16] + s0 + W[t-7] + s1) | 0;
                }

                let a = H[0], b = H[1], c = H[2], d = H[3], e = H[4], f = H[5], g = H[6], h = H[7];
                for (let t = 0; t < 64; t++) {
                    const S1 = ((e >>> 6) | (e << 26)) ^ ((e >>> 11) | (e << 21)) ^ ((e >>> 25) | (e << 7));
                    const ch = (e & f) ^ ((~e) & g);
                    const temp1 = (h + S1 + ch + K[t] + W[t]) | 0;
                    const S0 = ((a >>> 2) | (a << 30)) ^ ((a >>> 13) | (a << 19)) ^ ((a >>> 22) | (a << 10));
                    const maj = (a & b) ^ (a & c) ^ (b & c);
                    const temp2 = (S0 + maj) | 0;

                    h = g; g = f; f = e; e = (d + temp1) | 0;
                    d = c; c = b; b = a; a = (temp1 + temp2) | 0;
                }

                H[0] = (H[0] + a) | 0;
                H[1] = (H[1] + b) | 0;
                H[2] = (H[2] + c) | 0;
                H[3] = (H[3] + d) | 0;
                H[4] = (H[4] + e) | 0;
                H[5] = (H[5] + f) | 0;
                H[6] = (H[6] + g) | 0;
                H[7] = (H[7] + h) | 0;
            }

            const out = new Uint8Array(32);
            const outView = new DataView(out.buffer);
            for (let i = 0; i < 8; i++) {
                outView.setInt32(i * 4, H[i]);
            }
            return out;
        }

        function hmacSha256(keyBytes, msgBytes) {
            let k = keyBytes;
            if (k.length > 64) k = sha256Bytes(k);
            const ipad = new Uint8Array(64);
            const opad = new Uint8Array(64);
            for (let i = 0; i < 64; i++) {
                const b = (i < k.length) ? k[i] : 0;
                ipad[i] = b ^ 0x36;
                opad[i] = b ^ 0x5c;
            }
            const inner = new Uint8Array(64 + msgBytes.length);
            inner.set(ipad);
            inner.set(msgBytes, 64);
            const innerHash = sha256Bytes(inner);

            const outer = new Uint8Array(64 + 32);
            outer.set(opad);
            outer.set(innerHash, 64);
            return sha256Bytes(outer);
        }

        function pbkdf2Sha256(pwdBytes, saltBytes, iterations, keyLen) {
            const numBlocks = Math.ceil(keyLen / 32);
            const result = new Uint8Array(numBlocks * 32);
            for (let i = 1; i <= numBlocks; i++) {
                const blockSalt = new Uint8Array(saltBytes.length + 4);
                blockSalt.set(saltBytes);
                blockSalt[saltBytes.length] = (i >>> 24) & 0xff;
                blockSalt[saltBytes.length + 1] = (i >>> 16) & 0xff;
                blockSalt[saltBytes.length + 2] = (i >>> 8) & 0xff;
                blockSalt[saltBytes.length + 3] = i & 0xff;

                let u = hmacSha256(pwdBytes, blockSalt);
                const t = new Uint8Array(u);
                for (let j = 1; j < iterations; j++) {
                    u = hmacSha256(pwdBytes, u);
                    for (let k = 0; k < 32; k++) t[k] ^= u[k];
                }
                result.set(t, (i - 1) * 32);
            }
            return result.slice(0, keyLen);
        }

        const SBOX = new Uint8Array([
            0x63, 0x7c, 0x77, 0x7b, 0xf2, 0x6b, 0x6f, 0xc5, 0x30, 0x01, 0x67, 0x2b, 0xfe, 0xd7, 0xab, 0x76,
            0xca, 0x82, 0xc9, 0x7d, 0xfa, 0x59, 0x47, 0xf0, 0xad, 0xd4, 0xa2, 0xaf, 0x9c, 0xa4, 0x72, 0xc0,
            0xb7, 0xfd, 0x93, 0x26, 0x36, 0x3f, 0xf7, 0xcc, 0x34, 0xa5, 0xe5, 0xf1, 0x71, 0xd8, 0x31, 0x15,
            0x04, 0xc7, 0x23, 0xc3, 0x18, 0x96, 0x05, 0x9a, 0x07, 0x12, 0x80, 0xe2, 0xeb, 0x27, 0xb2, 0x75,
            0x09, 0x83, 0x2c, 0x1a, 0x1b, 0x6e, 0x5a, 0xa0, 0x52, 0x3b, 0xd6, 0xb3, 0x29, 0xe3, 0x2f, 0x84,
            0x53, 0xd1, 0x00, 0xed, 0x20, 0xfc, 0xb1, 0x5b, 0x6a, 0xcb, 0xbe, 0x39, 0x4a, 0x4c, 0x58, 0xcf,
            0xd0, 0xef, 0xaa, 0xfb, 0x43, 0x4d, 0x33, 0x85, 0x45, 0xf9, 0x02, 0x7f, 0x50, 0x3c, 0x9f, 0xa8,
            0x51, 0xa3, 0x40, 0x8f, 0x92, 0x9d, 0x38, 0xf5, 0xbc, 0xb6, 0xda, 0x21, 0x10, 0xff, 0xf3, 0xd2,
            0xcd, 0x0c, 0x13, 0xec, 0x5f, 0x97, 0x44, 0x17, 0xc4, 0xa7, 0x7e, 0x3d, 0x64, 0x5d, 0x19, 0x73,
            0x60, 0x81, 0x4f, 0xdc, 0x22, 0x2a, 0x90, 0x88, 0x46, 0xee, 0xb8, 0x14, 0xde, 0x5e, 0x0b, 0xdb,
            0xe0, 0x32, 0x3a, 0x0a, 0x49, 0x06, 0x24, 0x5c, 0xc2, 0xd3, 0xac, 0x62, 0x91, 0x95, 0xe4, 0x79,
            0xe7, 0xc8, 0x37, 0x6d, 0x8d, 0xd5, 0x4e, 0xa9, 0x6c, 0x56, 0xf4, 0xea, 0x65, 0x7a, 0xae, 0x08,
            0xba, 0x78, 0x25, 0x2e, 0x1c, 0xa6, 0xb4, 0xc6, 0xe8, 0xdd, 0x74, 0x1f, 0x4b, 0xbd, 0x8b, 0x8a,
            0x70, 0x3e, 0xb5, 0x66, 0x48, 0x03, 0xf6, 0x0e, 0x61, 0x35, 0x57, 0xb9, 0x86, 0xc1, 0x1d, 0x9e,
            0xe1, 0xf8, 0x98, 0x11, 0x69, 0xd9, 0x8e, 0x94, 0x9b, 0x1e, 0x87, 0xe9, 0xce, 0x55, 0x28, 0xdf,
            0x8c, 0xa1, 0x89, 0x0d, 0xbf, 0xe6, 0x42, 0x68, 0x41, 0x99, 0x2d, 0x0f, 0xb0, 0x54, 0xbb, 0x16
        ]);
        const RCON = [0x00, 0x01, 0x02, 0x04, 0x08, 0x10, 0x20, 0x40, 0x80, 0x1b, 0x36];

        function expandKey(k) {
            const w = new Uint32Array(60);
            for (let i = 0; i < 8; i++) w[i] = (k[4*i]<<24)|(k[4*i+1]<<16)|(k[4*i+2]<<8)|k[4*i+3];
            for (let i = 8; i < 60; i++) {
                let temp = w[i - 1];
                if (i % 8 === 0) {
                    temp = (temp << 8) | (temp >>> 24);
                    temp = (SBOX[(temp >>> 24) & 0xff] << 24) | (SBOX[(temp >>> 16) & 0xff] << 16) | (SBOX[(temp >>> 8) & 0xff] << 8) | SBOX[temp & 0xff];
                    temp ^= (RCON[i / 8] << 24);
                } else if (i % 8 === 4) {
                    temp = (SBOX[(temp >>> 24) & 0xff] << 24) | (SBOX[(temp >>> 16) & 0xff] << 16) | (SBOX[(temp >>> 8) & 0xff] << 8) | SBOX[temp & 0xff];
                }
                w[i] = w[i - 8] ^ temp;
            }
            return w;
        }

        function xtime(a) { return ((a << 1) ^ (((a >>> 7) & 1) * 0x11b)) & 0xff; }

        function aesEncryptBlock(w, input, inOff, output, outOff) {
            let s0 = (input[inOff] << 24) | (input[inOff+1] << 16) | (input[inOff+2] << 8) | input[inOff+3];
            let s1 = (input[inOff+4] << 24) | (input[inOff+5] << 16) | (input[inOff+6] << 8) | input[inOff+7];
            let s2 = (input[inOff+8] << 24) | (input[inOff+9] << 16) | (input[inOff+10] << 8) | input[inOff+11];
            let s3 = (input[inOff+12] << 24) | (input[inOff+13] << 16) | (input[inOff+14] << 8) | input[inOff+15];

            s0 ^= w[0]; s1 ^= w[1]; s2 ^= w[2]; s3 ^= w[3];

            for (let r = 1; r <= 13; r++) {
                const b00 = SBOX[(s0 >>> 24) & 0xff], b01 = SBOX[(s1 >>> 16) & 0xff], b02 = SBOX[(s2 >>> 8) & 0xff], b03 = SBOX[s3 & 0xff];
                const b10 = SBOX[(s1 >>> 24) & 0xff], b11 = SBOX[(s2 >>> 16) & 0xff], b12 = SBOX[(s3 >>> 8) & 0xff], b13 = SBOX[s0 & 0xff];
                const b20 = SBOX[(s2 >>> 24) & 0xff], b21 = SBOX[(s3 >>> 16) & 0xff], b22 = SBOX[(s0 >>> 8) & 0xff], b23 = SBOX[s1 & 0xff];
                const b30 = SBOX[(s3 >>> 24) & 0xff], b31 = SBOX[(s0 >>> 16) & 0xff], b32 = SBOX[(s1 >>> 8) & 0xff], b33 = SBOX[s2 & 0xff];

                s0 = ((xtime(b00) ^ xtime(b01) ^ b01 ^ b02 ^ b03) << 24) | ((b00 ^ xtime(b01) ^ xtime(b02) ^ b02 ^ b03) << 16) | ((b00 ^ b01 ^ xtime(b02) ^ xtime(b03) ^ b03) << 8) | (xtime(b00) ^ b00 ^ b01 ^ b02 ^ xtime(b03)) ^ w[4*r];
                s1 = ((xtime(b10) ^ xtime(b11) ^ b11 ^ b12 ^ b13) << 24) | ((b10 ^ xtime(b11) ^ xtime(b12) ^ b12 ^ b13) << 16) | ((b10 ^ b11 ^ xtime(b12) ^ xtime(b13) ^ b13) << 8) | (xtime(b10) ^ b10 ^ b11 ^ b12 ^ xtime(b13)) ^ w[4*r + 1];
                s2 = ((xtime(b20) ^ xtime(b21) ^ b21 ^ b22 ^ b23) << 24) | ((b20 ^ xtime(b21) ^ xtime(b22) ^ b22 ^ b23) << 16) | ((b20 ^ b21 ^ xtime(b22) ^ xtime(b23) ^ b23) << 8) | (xtime(b20) ^ b20 ^ b21 ^ b22 ^ xtime(b23)) ^ w[4*r + 2];
                s3 = ((xtime(b30) ^ xtime(b31) ^ b31 ^ b32 ^ b33) << 24) | ((b30 ^ xtime(b31) ^ xtime(b32) ^ b32 ^ b33) << 16) | ((b30 ^ b31 ^ xtime(b32) ^ xtime(b33) ^ b33) << 8) | (xtime(b30) ^ b30 ^ b31 ^ b32 ^ xtime(b33)) ^ w[4*r + 3];
            }

            const b00 = SBOX[(s0 >>> 24) & 0xff], b01 = SBOX[(s1 >>> 16) & 0xff], b02 = SBOX[(s2 >>> 8) & 0xff], b03 = SBOX[s3 & 0xff];
            const b10 = SBOX[(s1 >>> 24) & 0xff], b11 = SBOX[(s2 >>> 16) & 0xff], b12 = SBOX[(s3 >>> 8) & 0xff], b13 = SBOX[s0 & 0xff];
            const b20 = SBOX[(s2 >>> 24) & 0xff], b21 = SBOX[(s3 >>> 16) & 0xff], b22 = SBOX[(s0 >>> 8) & 0xff], b23 = SBOX[s1 & 0xff];
            const b30 = SBOX[(s3 >>> 24) & 0xff], b31 = SBOX[(s0 >>> 16) & 0xff], b32 = SBOX[(s1 >>> 8) & 0xff], b33 = SBOX[s2 & 0xff];

            s0 = ((b00 << 24) | (b01 << 16) | (b02 << 8) | b03) ^ w[56];
            s1 = ((b10 << 24) | (b11 << 16) | (b12 << 8) | b13) ^ w[57];
            s2 = ((b20 << 24) | (b21 << 16) | (b22 << 8) | b23) ^ w[58];
            s3 = ((b30 << 24) | (b31 << 16) | (b32 << 8) | b33) ^ w[59];

            output[outOff] = (s0 >>> 24) & 0xff; output[outOff+1] = (s0 >>> 16) & 0xff; output[outOff+2] = (s0 >>> 8) & 0xff; output[outOff+3] = s0 & 0xff;
            output[outOff+4] = (s1 >>> 24) & 0xff; output[outOff+5] = (s1 >>> 16) & 0xff; output[outOff+6] = (s1 >>> 8) & 0xff; output[outOff+7] = s1 & 0xff;
            output[outOff+8] = (s2 >>> 24) & 0xff; output[outOff+9] = (s2 >>> 16) & 0xff; output[outOff+10] = (s2 >>> 8) & 0xff; output[outOff+11] = s2 & 0xff;
            output[outOff+12] = (s3 >>> 24) & 0xff; output[outOff+13] = (s3 >>> 16) & 0xff; output[outOff+14] = (s3 >>> 8) & 0xff; output[outOff+15] = s3 & 0xff;
        }

        function ghashMultiply(x, y) {
            const z = new Uint32Array(4);
            const v = new Uint32Array([
                (y[0]<<24)|(y[1]<<16)|(y[2]<<8)|y[3],
                (y[4]<<24)|(y[5]<<16)|(y[6]<<8)|y[7],
                (y[8]<<24)|(y[9]<<16)|(y[10]<<8)|y[11],
                (y[12]<<24)|(y[13]<<16)|(y[14]<<8)|y[15]
            ]);
            const R = 0xe1000000;
            for (let i = 0; i < 16; i++) {
                for (let j = 7; j >= 0; j--) {
                    if ((x[i] >>> j) & 1) {
                        z[0] ^= v[0]; z[1] ^= v[1]; z[2] ^= v[2]; z[3] ^= v[3];
                    }
                    const lsb = v[3] & 1;
                    v[3] = (v[3] >>> 1) | ((v[2] & 1) << 31);
                    v[2] = (v[2] >>> 1) | ((v[1] & 1) << 31);
                    v[1] = (v[1] >>> 1) | ((v[0] & 1) << 31);
                    v[0] = (v[0] >>> 1);
                    if (lsb) v[0] ^= R;
                }
            }
            const res = new Uint8Array(16);
            for (let i = 0; i < 4; i++) {
                res[i*4] = (z[i] >>> 24) & 0xff; res[i*4+1] = (z[i] >>> 16) & 0xff; res[i*4+2] = (z[i] >>> 8) & 0xff; res[i*4+3] = z[i] & 0xff;
            }
            return res;
        }

        function aesGcmEncrypt(kBytes, ivBytes, ptBytes) {
            const w = expandKey(kBytes);
            const H = new Uint8Array(16);
            aesEncryptBlock(w, new Uint8Array(16), 0, H, 0);

            const J0 = new Uint8Array(16);
            J0.set(ivBytes.slice(0, 12));
            J0[15] = 1;

            const encJ0 = new Uint8Array(16);
            aesEncryptBlock(w, J0, 0, encJ0, 0);

            const numBlocks = Math.ceil(ptBytes.length / 16);
            const ct = new Uint8Array(ptBytes.length);
            const ctr = new Uint8Array(J0);

            for (let i = 0; i < numBlocks; i++) {
                let c = (ctr[12] << 24) | (ctr[13] << 16) | (ctr[14] << 8) | ctr[15];
                c = (c + 1) >>> 0;
                ctr[12] = (c >>> 24) & 0xff; ctr[13] = (c >>> 16) & 0xff; ctr[14] = (c >>> 8) & 0xff; ctr[15] = c & 0xff;

                const pad = new Uint8Array(16);
                aesEncryptBlock(w, ctr, 0, pad, 0);

                const start = i * 16;
                const end = Math.min(start + 16, ptBytes.length);
                for (let j = start; j < end; j++) ct[j] = ptBytes[j] ^ pad[j - start];
            }

            let S = new Uint8Array(16);
            for (let i = 0; i < ct.length; i += 16) {
                const block = new Uint8Array(16);
                block.set(ct.subarray(i, Math.min(i + 16, ct.length)));
                for (let j = 0; j < 16; j++) S[j] ^= block[j];
                S = ghashMultiply(S, H);
            }

            const lenBlock = new Uint8Array(16);
            const bitLen = BigInt(ct.length) * 8n;
            for (let i = 0; i < 8; i++) lenBlock[15 - i] = Number((bitLen >> BigInt(i * 8)) & 0xffn);
            for (let j = 0; j < 16; j++) S[j] ^= lenBlock[j];
            S = ghashMultiply(S, H);

            const tag = new Uint8Array(16);
            for (let i = 0; i < 16; i++) tag[i] = S[i] ^ encJ0[i];

            const result = new Uint8Array(ct.length + 16);
            result.set(ct);
            result.set(tag, ct.length);
            return result.buffer;
        }

        function aesGcmDecrypt(kBytes, ivBytes, ctBytes) {
            if (ctBytes.length < 16) throw new Error("Ciphertext too short");
            const ct = ctBytes.subarray(0, ctBytes.length - 16);
            const tag = ctBytes.subarray(ctBytes.length - 16);

            const w = expandKey(kBytes);
            const H = new Uint8Array(16);
            aesEncryptBlock(w, new Uint8Array(16), 0, H, 0);

            const J0 = new Uint8Array(16);
            J0.set(ivBytes.slice(0, 12));
            J0[15] = 1;

            const encJ0 = new Uint8Array(16);
            aesEncryptBlock(w, J0, 0, encJ0, 0);

            let S = new Uint8Array(16);
            for (let i = 0; i < ct.length; i += 16) {
                const block = new Uint8Array(16);
                block.set(ct.subarray(i, Math.min(i + 16, ct.length)));
                for (let j = 0; j < 16; j++) S[j] ^= block[j];
                S = ghashMultiply(S, H);
            }

            const lenBlock = new Uint8Array(16);
            const bitLen = BigInt(ct.length) * 8n;
            for (let i = 0; i < 8; i++) lenBlock[15 - i] = Number((bitLen >> BigInt(i * 8)) & 0xffn);
            for (let j = 0; j < 16; j++) S[j] ^= lenBlock[j];
            S = ghashMultiply(S, H);

            for (let i = 0; i < 16; i++) {
                if (tag[i] !== (S[i] ^ encJ0[i])) throw new Error("Authentication tag mismatch");
            }

            const pt = new Uint8Array(ct.length);
            const ctr = new Uint8Array(J0);
            const numBlocks = Math.ceil(ct.length / 16);
            for (let i = 0; i < numBlocks; i++) {
                let c = (ctr[12] << 24) | (ctr[13] << 16) | (ctr[14] << 8) | ctr[15];
                c = (c + 1) >>> 0;
                ctr[12] = (c >>> 24) & 0xff; ctr[13] = (c >>> 16) & 0xff; ctr[14] = (c >>> 8) & 0xff; ctr[15] = c & 0xff;

                const pad = new Uint8Array(16);
                aesEncryptBlock(w, ctr, 0, pad, 0);

                const start = i * 16;
                const end = Math.min(start + 16, ct.length);
                for (let j = start; j < end; j++) pt[j] = ct[j] ^ pad[j - start];
            }
            return pt.buffer;
        }

        window.crypto.subtle = {
            async importKey(format, keyData, algo, extractable, usages) {
                const raw = (keyData instanceof ArrayBuffer) ? new Uint8Array(keyData) : new Uint8Array(keyData.buffer, keyData.byteOffset, keyData.byteLength);
                return {
                    type: 'secret',
                    extractable,
                    algorithm: typeof algo === 'string' ? { name: algo } : algo,
                    usages,
                    _rawKey: raw
                };
            },
            async exportKey(format, key) {
                return key._rawKey.buffer;
            },
            async deriveKey(params, baseKey, derivedAlgo, extractable, usages) {
                const salt = (params.salt instanceof ArrayBuffer) ? new Uint8Array(params.salt) : new Uint8Array(params.salt.buffer, params.salt.byteOffset, params.salt.byteLength);
                const derived = pbkdf2Sha256(baseKey._rawKey, salt, params.iterations, derivedAlgo.length / 8);
                return {
                    type: 'secret',
                    extractable,
                    algorithm: derivedAlgo,
                    usages,
                    _rawKey: derived
                };
            },
            async generateKey(algo, extractable, usages) {
                const raw = new Uint8Array(algo.length / 8);
                for (let i = 0; i < raw.length; i++) raw[i] = Math.floor(Math.random() * 256);
                return {
                    type: 'secret',
                    extractable,
                    algorithm: algo,
                    usages,
                    _rawKey: raw
                };
            },
            async sign(algo, key, data) {
                const dataBytes = (data instanceof ArrayBuffer) ? new Uint8Array(data) : new Uint8Array(data.buffer, data.byteOffset, data.byteLength);
                const sig = hmacSha256(key._rawKey, dataBytes);
                return sig.buffer;
            },
            async encrypt(params, key, data) {
                const iv = (params.iv instanceof ArrayBuffer) ? new Uint8Array(params.iv) : new Uint8Array(params.iv.buffer, params.iv.byteOffset, params.iv.byteLength);
                const pt = (data instanceof ArrayBuffer) ? new Uint8Array(data) : new Uint8Array(data.buffer, data.byteOffset, data.byteLength);
                return aesGcmEncrypt(key._rawKey, iv, pt);
            },
            async decrypt(params, key, data) {
                const iv = (params.iv instanceof ArrayBuffer) ? new Uint8Array(params.iv) : new Uint8Array(params.iv.buffer, params.iv.byteOffset, params.iv.byteLength);
                const ct = (data instanceof ArrayBuffer) ? new Uint8Array(data) : new Uint8Array(data.buffer, data.byteOffset, data.byteLength);
                return aesGcmDecrypt(key._rawKey, iv, ct);
            }
        };
    }
})();

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
     * Derivasi Master Key dari Master Password menggunakan PBKDF2 atau Argon2id (WASM)
     */
    const deriveMasterKey = async (password, saltHex, iterations = 600000, algorithm = 'pbkdf2', memory = 65536, parallelism = 4) => {
        if (algorithm === 'argon2id' && typeof hashwasm !== 'undefined' && typeof hashwasm.argon2id === 'function') {
            const saltBuf = hexToBuffer(saltHex);
            const argonIterations = (iterations && iterations <= 100) ? iterations : 3;
            const rawKeyBytes = await hashwasm.argon2id({
                password: password,
                salt: new Uint8Array(saltBuf),
                iterations: argonIterations,
                memorySize: memory || 65536,
                hashLength: 32,
                parallelism: parallelism || 4,
                outputType: 'binary'
            });

            return await window.crypto.subtle.importKey(
                'raw',
                rawKeyBytes,
                { name: 'AES-GCM', length: 256 },
                true,
                ['encrypt', 'decrypt', 'wrapKey', 'unwrapKey']
            );
        }

        // Standar OWASP: PBKDF2-HMAC-SHA256 via WebCrypto API
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
                iterations: iterations || 600000,
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
