/**
 * PassVault — Client-Side Web Crypto Engine
 * Zero-Knowledge End-to-End Cryptography (PBKDF2 + AES-256-GCM)
 * Termasuk Pure-JS WebCrypto Polyfill (Audited Noble Ciphers & Hashes) untuk akses HTTP / IP LAN
 */

(()=>{function tt(t){return t instanceof Uint8Array||ArrayBuffer.isView(t)&&t.constructor.name==="Uint8Array"&&"BYTES_PER_ELEMENT"in t&&t.BYTES_PER_ELEMENT===1}var F=t=>t?`"${t}" `:"";function jt(t,e=""){if(typeof t!="boolean")throw new TypeError(F(e)+"expected boolean, got type="+typeof t);return t}function P(t,e=""){if(typeof t!="number")throw new TypeError(F(e)+"expected number, got "+typeof t);if(!Number.isSafeInteger(t)||t<0)throw new RangeError(F(e)+"expected integer >= 0, got "+t);return t}function b(t,e,n=""){if(tt(t)&&(e===void 0||t.length===e))return t;e!==void 0&&P(e,"length");let r=tt(t),o=e!==void 0?` of length ${e}`:"",s=r?`length=${t.length}`:`type=${typeof t}`,f=F(n)+"expected Uint8Array"+o+", got "+s;throw r?new RangeError(f):new TypeError(f)}function $(t,e=!0){if(t.destroyed)throw new Error("hash was destroyed");if(e&&t.finished)throw new Error("digest() was already called")}function Dt(t,e){b(t,void 0,"output");let n=e.outputLen;if(!(t.length>=n))throw new RangeError('"output" expected length >= '+n)}function et(t,e){if(Dt(t,e),!I(t))throw new Error("invalid output, must be aligned")}function xt(t){return new Uint8Array(t.buffer,t.byteOffset,t.byteLength)}function B(t){return new Uint32Array(t.buffer,t.byteOffset,Math.floor(t.byteLength/4))}function L(...t){for(let e=0;e<t.length;e++)t[e].fill(0)}function C(t){return new DataView(t.buffer,t.byteOffset,t.byteLength)}var k=new Uint8Array(new Uint32Array([287454020]).buffer)[0]===68;function nt(t){return t<<24&4278190080|t<<8&16711680|t>>>8&65280|t>>>24&255}var x=k?t=>t:t=>nt(t)>>>0;function Rt(t){for(let e=0;e<t.length;e++)t[e]=nt(t[e]);return t}var D=k?t=>t:Rt;function bt(t,e){if(t=b(t),e=b(e),t.length!==e.length)return!1;let n=0;for(let r=0;r<t.length;r++)n|=t[r]^e[r];return n===0}function rt(t,e,n){let r=e,o=n||(()=>[]),s=(c,i)=>r(i,...o(c)).update(c).digest(),f=r(new Uint8Array(t),...o(new Uint8Array(0)));return s.outputLen=f.outputLen,s.blockLen=f.blockLen,s.create=(c,...i)=>r(c,...i),s}var gt=(t,e)=>{function n(r,...o){if(b(r,void 0,"key"),t.nonceLength!==void 0){let u=o[0];b(u,t.varSizeNonce?void 0:t.nonceLength,"nonce")}let s=t.tagLength,f=t.nonceLength!==void 0?1:0;if(!t.withAAD){for(let u=f;u<o.length;u++)if(tt(o[u]))throw new Error("AAD not supported")}t.withAAD&&o[f]!==void 0&&b(o[f],void 0,"AAD");let c=e(r,...o),i=(u,l)=>{if(l!==void 0){if(u!==2)throw new Error("cipher output not supported");b(l,void 0,"output")}},h=!1;return{encrypt(u,l){if(h)throw new Error("cannot encrypt() twice with same key + nonce");return h=!0,b(u,void 0,"data"),i(c.encrypt.length,l),c.encrypt(u,l)},decrypt(u,l){if(b(u,void 0,"data"),s&&u.length<s)throw new Error('"ciphertext" expected length >= tagLength='+s);return i(c.decrypt.length,l),c.decrypt(u,l)}}}return Object.assign(n,t),n};function wt(t,e,n=!0){if(e===void 0)return new Uint8Array(t);if(b(e,t,"output"),n&&!I(e))throw new Error("invalid output, must be aligned");return e}function At(t,e,n){P(t),P(e),jt(n);let r=new Uint8Array(16),o=C(r);return o.setBigUint64(0,BigInt(e),n),o.setBigUint64(8,BigInt(t),n),r}function I(t){return t.byteOffset%4===0}function _(t){return Uint8Array.from(b(t))}var S=16,Et=new Uint8Array(16),R=B(Et),Nt=225,Ft=(t,e,n,r)=>{let o=r&1;return{s3:n<<31|r>>>1,s2:e<<31|n>>>1,s1:t<<31|e>>>1,s0:t>>>1^Nt<<24&-(o&1)}},z=t=>(t>>>0&255)<<24|(t>>>8&255)<<16|(t>>>16&255)<<8|t>>>24&255|0;var Pt=t=>t>64*1024?8:t>1024?4:2,ot=class{blockLen=S;outputLen=S;s0=0;s1=0;s2=0;s3=0;finished=!1;destroyed=!1;t;W;windowSize;constructor(e,n){b(e,16,"key"),e=_(e);let r=C(e),o=r.getUint32(0,!1),s=r.getUint32(4,!1),f=r.getUint32(8,!1),c=r.getUint32(12,!1),i=[];for(let p=0;p<128;p++)i.push({s0:z(o),s1:z(s),s2:z(f),s3:z(c)}),{s0:o,s1:s,s2:f,s3:c}=Ft(o,s,f,c);let h=Pt(n||1024);if(![1,2,4,8].includes(h))throw new Error("ghash: invalid window size, expected 2, 4 or 8");this.W=h;let u=128/h,l=this.windowSize=2**h,d=[];for(let p=0;p<u;p++)for(let y=0;y<l;y++){let g=0,w=0,A=0,O=0;for(let E=0;E<h;E++){if(!(y>>>h-E-1&1))continue;let{s0:Q,s1:vt,s2:Kt,s3:Mt}=i[h*p+E];g^=Q,w^=vt,A^=Kt,O^=Mt}d.push({s0:g,s1:w,s2:A,s3:O})}this.t=d}_updateBlock(e,n,r,o){e^=this.s0,n^=this.s1,r^=this.s2,o^=this.s3;let{W:s,t:f,windowSize:c}=this,i=0,h=0,a=0,u=0,l=(1<<s)-1,d=0;for(let p of[e,n,r,o])for(let y=0;y<4;y++){let g=p>>>8*y&255;for(let w=8/s-1;w>=0;w--){let A=g>>>s*w&l,{s0:O,s1:E,s2:j,s3:Q}=f[d*c+A];i^=O,h^=E,a^=j,u^=Q,d+=1}}this.s0=i,this.s1=h,this.s2=a,this.s3=u}update(e){$(this),b(e),e=_(e);let n=B(e),r=Math.floor(e.length/S),o=e.length%S;for(let s=0;s<r;s++)this._updateBlock(x(n[s*4+0]),x(n[s*4+1]),x(n[s*4+2]),x(n[s*4+3]));return o&&(Et.set(e.subarray(r*S)),this._updateBlock(x(R[0]),x(R[1]),x(R[2]),x(R[3])),L(R)),this}destroy(){this.destroyed=!0;let{t:e}=this;for(let n of e)n.s0=0,n.s1=0,n.s2=0,n.s3=0}digestInto(e){$(this),et(e,this),this.finished=!0;let{s0:n,s1:r,s2:o,s3:s}=this,f=B(e);f[0]=n,f[1]=r,f[2]=o,f[3]=s,k||D(f.subarray(0,S/4))}digest(){let e=new Uint8Array(S);return this.digestInto(e),this.destroy(),e}};var st=rt(16,(t,e)=>new ot(t,e),t=>[t.length]);var it=16,$t=4,G=new Uint8Array(it);var zt=283;function Gt(t){if(![16,24,32].includes(t.length))throw new Error('"aes key" expected Uint8Array of length 16/24/32, got length='+t.length)}function ft(t){return t<<1^zt&-(t>>7)}function mt(t,e){let n=0;for(;e>0;e>>=1)n^=t&-(e&1),t=ft(t);return n}var Wt=(()=>{let t=new Uint8Array(256);for(let n=0,r=1;n<256;n++,r^=ft(r))t[n]=r;let e=new Uint8Array(256);e[0]=99;for(let n=0;n<255;n++){let r=t[255-n];r|=r<<8,e[t[n]]=(r^r>>4^r>>5^r>>6^r>>7^99)&255}return L(t),e})();var Vt=t=>t<<24|t>>>8,ct=t=>t<<8|t>>>24;function Xt(t,e){if(t.length!==256)throw new Error("wrong sbox length");let n=new Uint32Array(256).map((h,a)=>e(t[a])),r=n.map(ct),o=r.map(ct),s=o.map(ct),f=new Uint32Array(256*256),c=new Uint32Array(256*256),i=new Uint16Array(256*256);for(let h=0;h<256;h++)for(let a=0;a<256;a++){let u=h*256+a;f[u]=n[h]^r[a],c[u]=o[h]^s[a],i[u]=t[h]<<8|t[a]}return{sbox:t,sbox2:i,T0:n,T1:r,T2:o,T3:s,T01:f,T23:c}}var Ut=Xt(Wt,t=>mt(t,3)<<24|t<<16|t<<8|mt(t,2));var Yt=(()=>{let t=new Uint8Array(16);for(let e=0,n=1;e<16;e++,n=ft(n))t[e]=n;return t})();function qt(t){b(t);let e=t.length;Gt(t);let{sbox2:n}=Ut,r=[];(!k||!I(t))&&r.push(t=_(t));let o=D(B(t)),s=o.length,f=i=>N(n,i,i,i,i),c=new Uint32Array(e+28);c.set(o);for(let i=s;i<c.length;i++){let h=c[i-1];i%s===0?h=f(Vt(h))^Yt[i/s-1]:s>6&&i%s===4&&(h=f(h)),c[i]=c[i-s]^h}return L(...r),c}function W(t,e,n,r,o,s){return t[n<<8&65280|r>>>8&255]^e[o>>>8&65280|s>>>24&255]}function N(t,e,n,r,o){return t[e&255|n&65280]|t[r>>>16&255|o>>>16&65280]<<16}function Lt(t,e,n,r,o){let{sbox2:s,T01:f,T23:c}=Ut,i=0;e^=t[i++],n^=t[i++],r^=t[i++],o^=t[i++];let h=t.length/4-2;for(let p=0;p<h;p++){let y=t[i++]^W(f,c,e,n,r,o),g=t[i++]^W(f,c,n,r,o,e),w=t[i++]^W(f,c,r,o,e,n),A=t[i++]^W(f,c,o,e,n,r);e=y,n=g,r=w,o=A}let a=t[i++]^N(s,e,n,r,o),u=t[i++]^N(s,n,r,o,e),l=t[i++]^N(s,r,o,e,n),d=t[i++]^N(s,o,e,n,r);return{s0:a,s1:u,s2:l,s3:d}}function V(t,e,n,r,o){b(n,it,"nonce"),b(r),o=wt(r.length,o);let s=n,f=B(s),c=C(s),i=B(r),h=B(o),a=e?0:12,u=r.length,l=c.getUint32(a,e);for(let p=0;p+4<=i.length;p+=4){let{s0:y,s1:g,s2:w,s3:A}=Lt(t,x(f[0]),x(f[1]),x(f[2]),x(f[3]));h[p+0]=i[p+0]^x(y),h[p+1]=i[p+1]^x(g),h[p+2]=i[p+2]^x(w),h[p+3]=i[p+3]^x(A),l=l+1>>>0,c.setUint32(a,l,e)}let d=it*Math.floor(i.length/$t);if(d<u){let{s0:p,s1:y,s2:g,s3:w}=Lt(t,x(f[0]),x(f[1]),x(f[2]),x(f[3])),A=new Uint32Array([p,y,g,w]);D(A);let O=xt(A);for(let E=d,j=0;E<u;E++,j++)o[E]=r[E]^O[j];L(A)}return o}function Zt(t,e,n,r,o){let s=o?o.length:0,f=t.create(n,r.length+s);o&&f.update(o);let c=At(8*r.length,8*s,e);f.update(r),f.update(c);let i=f.digest();return L(c),i}var ut=gt({blockSize:16,nonceLength:12,tagLength:16,withAAD:!0,varSizeNonce:!0},function(e,n,r){if(n.length<8)throw new Error("aes/gcm: invalid nonce length");let o=16;function s(c,i,h){let a=Zt(st,!1,c,h,r);for(let u=0;u<i.length;u++)a[u]^=i[u];return a}function f(){let c=qt(e),i=G.slice(),h=G.slice();if(V(c,!1,h,h,i),n.length===12)h.set(n);else{let u=G.slice();C(u).setBigUint64(8,BigInt(n.length*8),!1);let d=st.create(i).update(n).update(u);d.digestInto(h),d.destroy()}let a=V(c,!1,h,G);return{xk:c,authKey:i,counter:h,tagMask:a}}return{encrypt(c){let{xk:i,authKey:h,counter:a,tagMask:u}=f(),l=new Uint8Array(c.length+o),d=[i,h,a,u];I(c)||d.push(c=_(c)),V(i,!1,a,c,l.subarray(0,c.length));let p=s(h,u,l.subarray(0,l.length-o));return d.push(p),l.set(p,c.length),L(...d),l},decrypt(c){let{xk:i,authKey:h,counter:a,tagMask:u}=f(),l=[i,h,u,a];I(c)||l.push(c=_(c));let d=c.subarray(0,-o),p=c.subarray(-o),y=s(h,u,d);if(l.push(y),!bt(y,p))throw L(...l),new Error("aes-gcm: invalid tag");let g=V(i,!1,a,d);return L(...l),g}}});function Bt(t){return t instanceof Uint8Array||ArrayBuffer.isView(t)&&t.constructor.name==="Uint8Array"&&"BYTES_PER_ELEMENT"in t&&t.BYTES_PER_ELEMENT===1}var ht=t=>t?`"${t}" `:"";function H(t,e=""){if(typeof t!="number")throw new TypeError(ht(e)+"expected number, got "+typeof t);if(!Number.isSafeInteger(t)||t<0)throw new RangeError(ht(e)+"expected integer >= 0, got "+t);return t}function v(t,e,n=""){if(Bt(t)&&(e===void 0||t.length===e))return t;e!==void 0&&H(e,"length");let r=Bt(t),o=e!==void 0?` of length ${e}`:"",s=r?`length=${t.length}`:`type=${typeof t}`,f=ht(n)+"expected Uint8Array"+o+", got "+s;throw r?new RangeError(f):new TypeError(f)}function X(t){if(typeof t!="function"||typeof t.create!="function")throw new TypeError("expected hash wrapped by utils.createHasher");if(H(t.outputLen),H(t.blockLen),t.outputLen<1||t.blockLen<1)throw new Error("hash blockLen / outputLen must be >= 1")}var Jt=(t,e)=>{if(t===null||typeof t!="object"||Array.isArray(t))throw new TypeError((e==="object"?"":`"${e}" `)+"expected object, got type="+typeof t)},Tt=(t,e)=>{Jt(t,e);let n=Object.getPrototypeOf(t);if(n!==Object.prototype&&n!==null)throw new TypeError(`"${e}" expected plain object`);if(Object.hasOwn(t,"__proto__"))throw new TypeError(`"${e}.__proto__" is not allowed`)};function K(t,e=!0){if(t.destroyed)throw new Error("hash was destroyed");if(e&&t.finished)throw new Error("digest() was already called")}function Y(t,e){v(t,void 0,"output");let n=e.outputLen;if(!(t.length>=n))throw new RangeError('"output" expected length >= '+n)}function U(...t){for(let e=0;e<t.length;e++)t[e].fill(0)}function M(t){return new DataView(t.buffer,t.byteOffset,t.byteLength)}function m(t,e){return t<<32-e|t>>>e}function Qt(t){if(typeof t!="string")throw new TypeError("string expected");let e=new TextEncoder().encode(t);try{return new Uint8Array(e)}finally{U(e)}}function at(t,e=""){return typeof t=="string"?Qt(t):v(t,void 0,e)}function lt(t,e,n="opts"){return Tt(t,"defaults"),e!==void 0&&Tt(e,n),Object.assign(Object.create(null),t,e)}function _t(t,e={}){if(typeof t!="function")throw new TypeError('"hashCons" expected function, got type='+typeof t);e=lt({},e,"info");let n=(o,s)=>t(s).update(o).digest(),r=t(void 0);return n.outputLen=r.outputLen,n.blockLen=r.blockLen,n.canXOF=r.canXOF,n.create=o=>t(o),Object.assign(n,e),Object.freeze(n)}var St=t=>({oid:Uint8Array.from([6,9,96,134,72,1,101,3,4,2,t])});var q=class{oHash;iHash;blockLen;outputLen;canXOF=!1;finished=!1;destroyed=!1;constructor(e,n){if(X(e),v(n,void 0,"key"),this.iHash=e.create(),typeof this.iHash.update!="function")throw new Error("expected Hash instance");this.blockLen=this.iHash.blockLen,this.outputLen=this.iHash.outputLen;let r=this.blockLen,o=new Uint8Array(r);o.set(n.length>r?e.create().update(n).digest():n);for(let s=0;s<o.length;s++)o[s]^=54;this.iHash.update(o),this.oHash=e.create();for(let s=0;s<o.length;s++)o[s]^=106;this.oHash.update(o),U(o)}update(e){return K(this),this.iHash.update(e),this}digestInto(e){K(this),Y(e,this),this.finished=!0;let n=e.subarray(0,this.outputLen);this.iHash.digestInto(n),this.oHash.update(n),this.oHash.digestInto(n),this.destroy()}digest(){let e=new Uint8Array(this.oHash.outputLen);return this.digestInto(e),e}_cloneInto(e){e||=Object.create(Object.getPrototypeOf(this),{});let{oHash:n,iHash:r,finished:o,destroyed:s,blockLen:f,outputLen:c,canXOF:i}=this;return e=e,e.finished=o,e.destroyed=s,e.blockLen=f,e.outputLen=c,e.canXOF=i,e.oHash=n._cloneInto(e.oHash),e.iHash=r._cloneInto(e.iHash),e}clone(){return this._cloneInto()}destroy(){this.destroyed=!0,this.oHash.destroy(),this.iHash.destroy()}},Z=(()=>{let t=((e,n,r)=>new q(e,n).update(r).digest());return t.create=(e,n)=>new q(e,n),t})();function te(t,e,n,r){X(t);let o=lt({dkLen:32,asyncTick:10},r),{c:s,dkLen:f,asyncTick:c}=o;if(H(s,"c"),H(f,"dkLen"),H(c,"asyncTick"),s<1)throw new Error('"c" (iterations) must be >= 1');if(f<1)throw new Error('"dkLen" must be >= 1');if(f>(2**32-1)*t.outputLen)throw new Error("derived key too long");let i=at(e,"password");try{let h=at(n,"salt");try{let a=new Uint8Array(f),{iHash:u,oHash:l,outputLen:d}=Z.create(t,i),p=new Uint8Array(d),y=ee(u,l,h,p);return{c:s,dkLen:f,asyncTick:c,DK:a,outputLen:d,eng:y}}finally{typeof n=="string"&&U(h)}}finally{typeof e=="string"&&U(i)}}function ee(t,e,n,r){let o=new Uint8Array(4),s=M(o),f=t._cloneInto().update(n),c=e._cloneInto(),i=t._cloneInto,h=e._cloneInto;return{u1:(a,u)=>{s.setInt32(0,a,!1),f._cloneInto(c).update(o).digestInto(r),e._cloneInto(c).update(r).digestInto(r),u.set(r.subarray(0,u.length))},rounds:(a,u)=>{for(let l=1;l<a;l++){i.call(t,c).update(r).digestInto(r),h.call(e,c).update(r).digestInto(r);for(let d=0;d<u.length;d++)u[d]^=r[d]}},output:a=>(t.destroy(),e.destroy(),f.destroy(),c.destroy(),U(r),a)}}function Ht(t,e,n,r){let{c:o,dkLen:s,DK:f,outputLen:c,eng:i}=te(t,e,n,r);for(let h=1,a=0;a<s;h++,a+=c){let u=f.subarray(a,a+c);i.u1(h,u),i.rounds(o,u)}return i.output(f)}var ne=t=>t/2**32|0,re=t=>t>>>0;function Ot(t,e,n,r){let o=ne(n),s=re(n);t.setUint32(e,r?s:o,r),t.setUint32(e+4,r?o:s,r)}function Ct(t,e,n){return t&e^~t&n}function kt(t,e,n){return t&e^t&n^e&n}var J=class{blockLen;outputLen;canXOF=!1;padOffset;isLE;buffer;view;finished=!1;length=0;pos=0;destroyed=!1;constructor(e,n,r,o){this.blockLen=e,this.outputLen=n,this.padOffset=r,this.isLE=o,this.buffer=new Uint8Array(e),this.view=M(this.buffer)}update(e){K(this),v(e);let{view:n,buffer:r,blockLen:o}=this,s=e.length,f=!1;for(let c=0;c<s;){let i=Math.min(o-this.pos,s-c);if(i===o){let h=M(e);for(;o<=s-c;c+=o)this.process(h,c);f=!0;continue}r.set(c===0&&i===s?e:e.subarray(c,c+i),this.pos),this.pos+=i,c+=i,this.pos===o&&(this.process(n,0),this.pos=0,f=!0)}return this.length+=e.length,f&&this.roundClean(),this}digestInto(e){K(this),Y(e,this),this.finished=!0;let{buffer:n,view:r,blockLen:o,isLE:s}=this,{pos:f}=this;n[f++]=128,n.fill(0,f),this.padOffset>o-f&&(this.process(r,0),n.fill(0)),Ot(r,o-8,this.length*8,s),this.process(r,0),this.roundClean();let c=e===n?r:M(e),i=this.outputLen,h=i/4,a=this.get();if(i%4||h>a.length)throw new Error("invalid outputLen");for(let u=0;u<h;u++)c.setUint32(4*u,a[u],s)}digest(){let{buffer:e,outputLen:n}=this;this.digestInto(e);let r=e.slice(0,n);return this.destroy(),r}_cloneIntoMeta(e){let{buffer:n,length:r,finished:o,destroyed:s,pos:f}=this;return e.destroyed=s,e.finished=o,e.length=r,e.pos=f,f&&e.buffer.set(n),e}clone(){return this._cloneInto()}},It=Uint32Array.from([1779033703,3144134277,1013904242,2773480762,1359893119,2600822924,528734635,1541459225]);var oe=Uint32Array.from([1116352408,1899447441,3049323471,3921009573,961987163,1508970993,2453635748,2870763221,3624381080,310598401,607225278,1426881987,1925078388,2162078206,2614888103,3248222580,3835390401,4022224774,264347078,604807628,770255983,1249150122,1555081692,1996064986,2554220882,2821834349,2952996808,3210313671,3336571891,3584528711,113926993,338241895,666307205,773529912,1294757372,1396182291,1695183700,1986661051,2177026350,2456956037,2730485921,2820302411,3259730800,3345764771,3516065817,3600352804,4094571909,275423344,430227734,506948616,659060556,883997877,958139571,1322822218,1537002063,1747873779,1955562222,2024104815,2227730452,2361852424,2428436474,2756734187,3204031479,3329325298]),T=new Uint32Array(64),dt=class extends J{A=0;B=0;C=0;D=0;E=0;F=0;G=0;H=0;constructor(e,n){super(64,e,8,!1),this.A=n[0]|0,this.B=n[1]|0,this.C=n[2]|0,this.D=n[3]|0,this.E=n[4]|0,this.F=n[5]|0,this.G=n[6]|0,this.H=n[7]|0}get(){let{A:e,B:n,C:r,D:o,E:s,F:f,G:c,H:i}=this;return[e,n,r,o,s,f,c,i]}set(e,n,r,o,s,f,c,i){this.A=e|0,this.B=n|0,this.C=r|0,this.D=o|0,this.E=s|0,this.F=f|0,this.G=c|0,this.H=i|0}_cloneInto(e){return(e||=new this.constructor).set(...this.get()),this._cloneIntoMeta(e)}process(e,n){for(let u=0;u<16;u++,n+=4)T[u]=e.getUint32(n,!1);for(let u=16;u<64;u++){let l=T[u-15],d=T[u-2],p=m(l,7)^m(l,18)^l>>>3,y=m(d,17)^m(d,19)^d>>>10;T[u]=y+T[u-7]+p+T[u-16]|0}let{A:r,B:o,C:s,D:f,E:c,F:i,G:h,H:a}=this;for(let u=0;u<64;u++){let l=m(c,6)^m(c,11)^m(c,25),d=a+l+Ct(c,i,h)+oe[u]+T[u]|0,y=(m(r,2)^m(r,13)^m(r,22))+kt(r,o,s)|0;a=h,h=i,i=c,c=f+d|0,f=s,s=o,o=r,r=d+y|0}r=r+this.A|0,o=o+this.B|0,s=s+this.C|0,f=f+this.D|0,c=c+this.E|0,i=i+this.F|0,h=h+this.G|0,a=a+this.H|0,this.set(r,o,s,f,c,i,h,a)}roundClean(){U(T)}destroy(){this.destroyed=!0,this.set(0,0,0,0,0,0,0,0),U(this.buffer)}},pt=class extends dt{constructor(){super(32,It)}};var yt=_t(()=>new pt,St(1));(function(){typeof window>"u"||(window.crypto||(window.crypto={}),window.crypto.getRandomValues||(window.crypto.getRandomValues=function(e){for(let n=0;n<e.length;n++)e[n]=Math.floor(Math.random()*256);return e}),window.crypto.subtle||(console.warn("GembokX: window.crypto.subtle tidak tersedia (Konteks HTTP LAN). Mengaktifkan WebCrypto Pure-JS Fallback (Noble)."),window.crypto.subtle={async importKey(e,n,r,o,s){let f=n instanceof ArrayBuffer?new Uint8Array(n):new Uint8Array(n.buffer,n.byteOffset,n.byteLength);return{type:"secret",extractable:o,algorithm:typeof r=="string"?{name:r}:r,usages:s,_rawKey:f}},async exportKey(e,n){return n._rawKey.buffer.slice(n._rawKey.byteOffset,n._rawKey.byteOffset+n._rawKey.byteLength)},async deriveKey(e,n,r,o,s){let f=e.salt instanceof ArrayBuffer?new Uint8Array(e.salt):new Uint8Array(e.salt.buffer,e.salt.byteOffset,e.salt.byteLength),c=(r.length||256)/8,i=Ht(yt,n._rawKey,f,{c:e.iterations,dkLen:c});return{type:"secret",extractable:o,algorithm:r,usages:s,_rawKey:i}},async generateKey(e,n,r){let o=new Uint8Array((e.length||256)/8);return window.crypto.getRandomValues(o),{type:"secret",extractable:n,algorithm:e,usages:r,_rawKey:o}},async sign(e,n,r){let o=r instanceof ArrayBuffer?new Uint8Array(r):new Uint8Array(r.buffer,r.byteOffset,r.byteLength),s=Z(yt,n._rawKey,o);return s.buffer.slice(s.byteOffset,s.byteOffset+s.byteLength)},async encrypt(e,n,r){let o=e.iv instanceof ArrayBuffer?new Uint8Array(e.iv):new Uint8Array(e.iv.buffer,e.iv.byteOffset,e.iv.byteLength),s=r instanceof ArrayBuffer?new Uint8Array(r):new Uint8Array(r.buffer,r.byteOffset,r.byteLength),c=ut(n._rawKey,o).encrypt(s);return c.buffer.slice(c.byteOffset,c.byteOffset+c.byteLength)},async decrypt(e,n,r){let o=e.iv instanceof ArrayBuffer?new Uint8Array(e.iv):new Uint8Array(e.iv.buffer,e.iv.byteOffset,e.iv.byteLength),s=r instanceof ArrayBuffer?new Uint8Array(r):new Uint8Array(r.buffer,r.byteOffset,r.byteLength),c=ut(n._rawKey,o).decrypt(s);return c.buffer.slice(c.byteOffset,c.byteOffset+c.byteLength)}}))})();})();
/*! Bundled license information:

@noble/ciphers/utils.js:
  (*! noble-ciphers - MIT License (c) 2023 Paul Miller (paulmillr.com) *)
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
