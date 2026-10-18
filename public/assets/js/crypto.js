/**
 * PassVault — Client-Side Web Crypto Engine
 * Zero-Knowledge End-to-End Cryptography (PBKDF2 + AES-256-GCM)
 * Termasuk Pure-JS WebCrypto Polyfill (Audited Noble Ciphers & Hashes) untuk akses HTTP / IP LAN
 */

(()=>{function it(t){return t instanceof Uint8Array||ArrayBuffer.isView(t)&&t.constructor.name==="Uint8Array"&&"BYTES_PER_ELEMENT"in t&&t.BYTES_PER_ELEMENT===1}var W=t=>t?`"${t}" `:"";function Pt(t,e=""){if(typeof t!="boolean")throw new TypeError(W(e)+"expected boolean, got type="+typeof t);return t}function G(t,e=""){if(typeof t!="number")throw new TypeError(W(e)+"expected number, got "+typeof t);if(!Number.isSafeInteger(t)||t<0)throw new RangeError(W(e)+"expected integer >= 0, got "+t);return t}function b(t,e,n=""){if(it(t)&&(e===void 0||t.length===e))return t;e!==void 0&&G(e,"length");let r=it(t),o=e!==void 0?` of length ${e}`:"",s=r?`length=${t.length}`:`type=${typeof t}`,f=W(n)+"expected Uint8Array"+o+", got "+s;throw r?new RangeError(f):new TypeError(f)}function z(t,e=!0){if(t.destroyed)throw new Error("hash was destroyed");if(e&&t.finished)throw new Error("digest() was already called")}function $t(t,e){b(t,void 0,"output");let n=e.outputLen;if(!(t.length>=n))throw new RangeError('"output" expected length >= '+n)}function ft(t,e){if($t(t,e),!K(t))throw new Error("invalid output, must be aligned")}function Lt(t){return new Uint8Array(t.buffer,t.byteOffset,t.byteLength)}function B(t){return new Uint32Array(t.buffer,t.byteOffset,Math.floor(t.byteLength/4))}function U(...t){for(let e=0;e<t.length;e++)t[e].fill(0)}function O(t){return new DataView(t.buffer,t.byteOffset,t.byteLength)}var k=new Uint8Array(new Uint32Array([287454020]).buffer)[0]===68;function ht(t){return t<<24&4278190080|t<<8&16711680|t>>>8&65280|t>>>24&255}var x=k?t=>t:t=>ht(t)>>>0;function Wt(t){for(let e=0;e<t.length;e++)t[e]=ht(t[e]);return t}var F=k?t=>t:Wt;function Ut(t,e){if(t=b(t),e=b(e),t.length!==e.length)return!1;let n=0;for(let r=0;r<t.length;r++)n|=t[r]^e[r];return n===0}function ut(t,e,n){let r=e,o=n||(()=>[]),s=(i,c)=>r(c,...o(i)).update(i).digest(),f=r(new Uint8Array(t),...o(new Uint8Array(0)));return s.outputLen=f.outputLen,s.blockLen=f.blockLen,s.create=(i,...c)=>r(i,...c),s}var Bt=(t,e)=>{function n(r,...o){if(b(r,void 0,"key"),t.nonceLength!==void 0){let h=o[0];b(h,t.varSizeNonce?void 0:t.nonceLength,"nonce")}let s=t.tagLength,f=t.nonceLength!==void 0?1:0;if(!t.withAAD){for(let h=f;h<o.length;h++)if(it(o[h]))throw new Error("AAD not supported")}t.withAAD&&o[f]!==void 0&&b(o[f],void 0,"AAD");let i=e(r,...o),c=(h,l)=>{if(l!==void 0){if(h!==2)throw new Error("cipher output not supported");b(l,void 0,"output")}},u=!1;return{encrypt(h,l){if(u)throw new Error("cannot encrypt() twice with same key + nonce");return u=!0,b(h,void 0,"data"),c(i.encrypt.length,l),i.encrypt(h,l)},decrypt(h,l){if(b(h,void 0,"data"),s&&h.length<s)throw new Error('"ciphertext" expected length >= tagLength='+s);return c(i.decrypt.length,l),i.decrypt(h,l)}}}return Object.assign(n,t),n};function _t(t,e,n=!0){if(e===void 0)return new Uint8Array(t);if(b(e,t,"output"),n&&!K(e))throw new Error("invalid output, must be aligned");return e}function St(t,e,n){G(t),G(e),Pt(n);let r=new Uint8Array(16),o=O(r);return o.setBigUint64(0,BigInt(e),n),o.setBigUint64(8,BigInt(t),n),r}function K(t){return t.byteOffset%4===0}function T(t){return Uint8Array.from(b(t))}var H=16,Tt=new Uint8Array(16),N=B(Tt),Gt=225,zt=(t,e,n,r)=>{let o=r&1;return{s3:n<<31|r>>>1,s2:e<<31|n>>>1,s1:t<<31|e>>>1,s0:t>>>1^Gt<<24&-(o&1)}},V=t=>(t>>>0&255)<<24|(t>>>8&255)<<16|(t>>>16&255)<<8|t>>>24&255|0;var Vt=t=>t>64*1024?8:t>1024?4:2,at=class{blockLen=H;outputLen=H;s0=0;s1=0;s2=0;s3=0;finished=!1;destroyed=!1;t;W;windowSize;constructor(e,n){b(e,16,"key"),e=T(e);let r=O(e),o=r.getUint32(0,!1),s=r.getUint32(4,!1),f=r.getUint32(8,!1),i=r.getUint32(12,!1),c=[];for(let p=0;p<128;p++)c.push({s0:V(o),s1:V(s),s2:V(f),s3:V(i)}),{s0:o,s1:s,s2:f,s3:i}=zt(o,s,f,i);let u=Vt(n||1024);if(![1,2,4,8].includes(u))throw new Error("ghash: invalid window size, expected 2, 4 or 8");this.W=u;let h=128/u,l=this.windowSize=2**u,d=[];for(let p=0;p<h;p++)for(let y=0;y<l;y++){let w=0,A=0,m=0,I=0;for(let E=0;E<u;E++){if(!(y>>>u-E-1&1))continue;let{s0:ct,s1:jt,s2:Ft,s3:Nt}=c[u*p+E];w^=ct,A^=jt,m^=Ft,I^=Nt}d.push({s0:w,s1:A,s2:m,s3:I})}this.t=d}_updateBlock(e,n,r,o){e^=this.s0,n^=this.s1,r^=this.s2,o^=this.s3;let{W:s,t:f,windowSize:i}=this,c=0,u=0,a=0,h=0,l=(1<<s)-1,d=0;for(let p of[e,n,r,o])for(let y=0;y<4;y++){let w=p>>>8*y&255;for(let A=8/s-1;A>=0;A--){let m=w>>>s*A&l,{s0:I,s1:E,s2:j,s3:ct}=f[d*i+m];c^=I,u^=E,a^=j,h^=ct,d+=1}}this.s0=c,this.s1=u,this.s2=a,this.s3=h}update(e){z(this),b(e),e=T(e);let n=B(e),r=Math.floor(e.length/H),o=e.length%H;for(let s=0;s<r;s++)this._updateBlock(x(n[s*4+0]),x(n[s*4+1]),x(n[s*4+2]),x(n[s*4+3]));return o&&(Tt.set(e.subarray(r*H)),this._updateBlock(x(N[0]),x(N[1]),x(N[2]),x(N[3])),U(N)),this}destroy(){this.destroyed=!0;let{t:e}=this;for(let n of e)n.s0=0,n.s1=0,n.s2=0,n.s3=0}digestInto(e){z(this),ft(e,this),this.finished=!0;let{s0:n,s1:r,s2:o,s3:s}=this,f=B(e);f[0]=n,f[1]=r,f[2]=o,f[3]=s,k||F(f.subarray(0,H/4))}digest(){let e=new Uint8Array(H);return this.digestInto(e),this.destroy(),e}};var lt=ut(16,(t,e)=>new at(t,e),t=>[t.length]);var pt=16,Xt=4,X=new Uint8Array(pt);var Yt=283;function qt(t){if(![16,24,32].includes(t.length))throw new Error('"aes key" expected Uint8Array of length 16/24/32, got length='+t.length)}function yt(t){return t<<1^Yt&-(t>>7)}function Ht(t,e){let n=0;for(;e>0;e>>=1)n^=t&-(e&1),t=yt(t);return n}var Zt=(()=>{let t=new Uint8Array(256);for(let n=0,r=1;n<256;n++,r^=yt(r))t[n]=r;let e=new Uint8Array(256);e[0]=99;for(let n=0;n<255;n++){let r=t[255-n];r|=r<<8,e[t[n]]=(r^r>>4^r>>5^r>>6^r>>7^99)&255}return U(t),e})();var Jt=t=>t<<24|t>>>8,dt=t=>t<<8|t>>>24;function Qt(t,e){if(t.length!==256)throw new Error("wrong sbox length");let n=new Uint32Array(256).map((u,a)=>e(t[a])),r=n.map(dt),o=r.map(dt),s=o.map(dt),f=new Uint32Array(256*256),i=new Uint32Array(256*256),c=new Uint16Array(256*256);for(let u=0;u<256;u++)for(let a=0;a<256;a++){let h=u*256+a;f[h]=n[u]^r[a],i[h]=o[u]^s[a],c[h]=t[u]<<8|t[a]}return{sbox:t,sbox2:c,T0:n,T1:r,T2:o,T3:s,T01:f,T23:i}}var It=Qt(Zt,t=>Ht(t,3)<<24|t<<16|t<<8|Ht(t,2));var te=(()=>{let t=new Uint8Array(16);for(let e=0,n=1;e<16;e++,n=yt(n))t[e]=n;return t})();function ee(t){b(t);let e=t.length;qt(t);let{sbox2:n}=It,r=[];(!k||!K(t))&&r.push(t=T(t));let o=F(B(t)),s=o.length,f=c=>P(n,c,c,c,c),i=new Uint32Array(e+28);i.set(o);for(let c=s;c<i.length;c++){let u=i[c-1];c%s===0?u=f(Jt(u))^te[c/s-1]:s>6&&c%s===4&&(u=f(u)),i[c]=i[c-s]^u}return U(...r),i}function Y(t,e,n,r,o,s){return t[n<<8&65280|r>>>8&255]^e[o>>>8&65280|s>>>24&255]}function P(t,e,n,r,o){return t[e&255|n&65280]|t[r>>>16&255|o>>>16&65280]<<16}function Ct(t,e,n,r,o){let{sbox2:s,T01:f,T23:i}=It,c=0;e^=t[c++],n^=t[c++],r^=t[c++],o^=t[c++];let u=t.length/4-2;for(let p=0;p<u;p++){let y=t[c++]^Y(f,i,e,n,r,o),w=t[c++]^Y(f,i,n,r,o,e),A=t[c++]^Y(f,i,r,o,e,n),m=t[c++]^Y(f,i,o,e,n,r);e=y,n=w,r=A,o=m}let a=t[c++]^P(s,e,n,r,o),h=t[c++]^P(s,n,r,o,e),l=t[c++]^P(s,r,o,e,n),d=t[c++]^P(s,o,e,n,r);return{s0:a,s1:h,s2:l,s3:d}}function q(t,e,n,r,o){b(n,pt,"nonce"),b(r),o=_t(r.length,o);let s=n,f=B(s),i=O(s),c=B(r),u=B(o),a=e?0:12,h=r.length,l=i.getUint32(a,e);for(let p=0;p+4<=c.length;p+=4){let{s0:y,s1:w,s2:A,s3:m}=Ct(t,x(f[0]),x(f[1]),x(f[2]),x(f[3]));u[p+0]=c[p+0]^x(y),u[p+1]=c[p+1]^x(w),u[p+2]=c[p+2]^x(A),u[p+3]=c[p+3]^x(m),l=l+1>>>0,i.setUint32(a,l,e)}let d=pt*Math.floor(c.length/Xt);if(d<h){let{s0:p,s1:y,s2:w,s3:A}=Ct(t,x(f[0]),x(f[1]),x(f[2]),x(f[3])),m=new Uint32Array([p,y,w,A]);F(m);let I=Lt(m);for(let E=d,j=0;E<h;E++,j++)o[E]=r[E]^I[j];U(m)}return o}function ne(t,e,n,r,o){let s=o?o.length:0,f=t.create(n,r.length+s);o&&f.update(o);let i=St(8*r.length,8*s,e);f.update(r),f.update(i);let c=f.digest();return U(i),c}var xt=Bt({blockSize:16,nonceLength:12,tagLength:16,withAAD:!0,varSizeNonce:!0},function(e,n,r){if(n.length<8)throw new Error("aes/gcm: invalid nonce length");let o=16;function s(i,c,u){let a=ne(lt,!1,i,u,r);for(let h=0;h<c.length;h++)a[h]^=c[h];return a}function f(){let i=ee(e),c=X.slice(),u=X.slice();if(q(i,!1,u,u,c),n.length===12)u.set(n);else{let h=X.slice();O(h).setBigUint64(8,BigInt(n.length*8),!1);let d=lt.create(c).update(n).update(h);d.digestInto(u),d.destroy()}let a=q(i,!1,u,X);return{xk:i,authKey:c,counter:u,tagMask:a}}return{encrypt(i){let{xk:c,authKey:u,counter:a,tagMask:h}=f(),l=new Uint8Array(i.length+o),d=[c,u,a,h];K(i)||d.push(i=T(i)),q(c,!1,a,i,l.subarray(0,i.length));let p=s(u,h,l.subarray(0,l.length-o));return d.push(p),l.set(p,i.length),U(...d),l},decrypt(i){let{xk:c,authKey:u,counter:a,tagMask:h}=f(),l=[c,u,h,a];K(i)||l.push(i=T(i));let d=i.subarray(0,-o),p=i.subarray(-o),y=s(u,h,d);if(l.push(y),!Ut(y,p))throw U(...l),new Error("aes-gcm: invalid tag");let w=q(c,!1,a,d);return U(...l),w}}});function Ot(t){return t instanceof Uint8Array||ArrayBuffer.isView(t)&&t.constructor.name==="Uint8Array"&&"BYTES_PER_ELEMENT"in t&&t.BYTES_PER_ELEMENT===1}var bt=t=>t?`"${t}" `:"";function C(t,e=""){if(typeof t!="number")throw new TypeError(bt(e)+"expected number, got "+typeof t);if(!Number.isSafeInteger(t)||t<0)throw new RangeError(bt(e)+"expected integer >= 0, got "+t);return t}function v(t,e,n=""){if(Ot(t)&&(e===void 0||t.length===e))return t;e!==void 0&&C(e,"length");let r=Ot(t),o=e!==void 0?` of length ${e}`:"",s=r?`length=${t.length}`:`type=${typeof t}`,f=bt(n)+"expected Uint8Array"+o+", got "+s;throw r?new RangeError(f):new TypeError(f)}function Z(t){if(typeof t!="function"||typeof t.create!="function")throw new TypeError("expected hash wrapped by utils.createHasher");if(C(t.outputLen),C(t.blockLen),t.outputLen<1||t.blockLen<1)throw new Error("hash blockLen / outputLen must be >= 1")}var re=(t,e)=>{if(t===null||typeof t!="object"||Array.isArray(t))throw new TypeError((e==="object"?"":`"${e}" `)+"expected object, got type="+typeof t)},kt=(t,e)=>{re(t,e);let n=Object.getPrototypeOf(t);if(n!==Object.prototype&&n!==null)throw new TypeError(`"${e}" expected plain object`);if(Object.hasOwn(t,"__proto__"))throw new TypeError(`"${e}.__proto__" is not allowed`)};function M(t,e=!0){if(t.destroyed)throw new Error("hash was destroyed");if(e&&t.finished)throw new Error("digest() was already called")}function J(t,e){v(t,void 0,"output");let n=e.outputLen;if(!(t.length>=n))throw new RangeError('"output" expected length >= '+n)}function g(...t){for(let e=0;e<t.length;e++)t[e].fill(0)}function D(t){return new DataView(t.buffer,t.byteOffset,t.byteLength)}function L(t,e){return t<<32-e|t>>>e}function Q(t,e){return t<<e|t>>>32-e>>>0}function oe(t){if(typeof t!="string")throw new TypeError("string expected");let e=new TextEncoder().encode(t);try{return new Uint8Array(e)}finally{g(e)}}function gt(t,e=""){return typeof t=="string"?oe(t):v(t,void 0,e)}function wt(t,e,n="opts"){return kt(t,"defaults"),e!==void 0&&kt(e,n),Object.assign(Object.create(null),t,e)}function tt(t,e={}){if(typeof t!="function")throw new TypeError('"hashCons" expected function, got type='+typeof t);e=wt({},e,"info");let n=(o,s)=>t(s).update(o).digest(),r=t(void 0);return n.outputLen=r.outputLen,n.blockLen=r.blockLen,n.canXOF=r.canXOF,n.create=o=>t(o),Object.assign(n,e),Object.freeze(n)}var Kt=t=>({oid:Uint8Array.from([6,9,96,134,72,1,101,3,4,2,t])});var et=class{oHash;iHash;blockLen;outputLen;canXOF=!1;finished=!1;destroyed=!1;constructor(e,n){if(Z(e),v(n,void 0,"key"),this.iHash=e.create(),typeof this.iHash.update!="function")throw new Error("expected Hash instance");this.blockLen=this.iHash.blockLen,this.outputLen=this.iHash.outputLen;let r=this.blockLen,o=new Uint8Array(r);o.set(n.length>r?e.create().update(n).digest():n);for(let s=0;s<o.length;s++)o[s]^=54;this.iHash.update(o),this.oHash=e.create();for(let s=0;s<o.length;s++)o[s]^=106;this.oHash.update(o),g(o)}update(e){return M(this),this.iHash.update(e),this}digestInto(e){M(this),J(e,this),this.finished=!0;let n=e.subarray(0,this.outputLen);this.iHash.digestInto(n),this.oHash.update(n),this.oHash.digestInto(n),this.destroy()}digest(){let e=new Uint8Array(this.oHash.outputLen);return this.digestInto(e),e}_cloneInto(e){e||=Object.create(Object.getPrototypeOf(this),{});let{oHash:n,iHash:r,finished:o,destroyed:s,blockLen:f,outputLen:i,canXOF:c}=this;return e=e,e.finished=o,e.destroyed=s,e.blockLen=f,e.outputLen=i,e.canXOF=c,e.oHash=n._cloneInto(e.oHash),e.iHash=r._cloneInto(e.iHash),e}clone(){return this._cloneInto()}destroy(){this.destroyed=!0,this.oHash.destroy(),this.iHash.destroy()}},nt=(()=>{let t=((e,n,r)=>new et(e,n).update(r).digest());return t.create=(e,n)=>new et(e,n),t})();function se(t,e,n,r){Z(t);let o=wt({dkLen:32,asyncTick:10},r),{c:s,dkLen:f,asyncTick:i}=o;if(C(s,"c"),C(f,"dkLen"),C(i,"asyncTick"),s<1)throw new Error('"c" (iterations) must be >= 1');if(f<1)throw new Error('"dkLen" must be >= 1');if(f>(2**32-1)*t.outputLen)throw new Error("derived key too long");let c=gt(e,"password");try{let u=gt(n,"salt");try{let a=new Uint8Array(f),{iHash:h,oHash:l,outputLen:d}=nt.create(t,c),p=new Uint8Array(d),y=ce(h,l,u,p);return{c:s,dkLen:f,asyncTick:i,DK:a,outputLen:d,eng:y}}finally{typeof n=="string"&&g(u)}}finally{typeof e=="string"&&g(c)}}function ce(t,e,n,r){let o=new Uint8Array(4),s=D(o),f=t._cloneInto().update(n),i=e._cloneInto(),c=t._cloneInto,u=e._cloneInto;return{u1:(a,h)=>{s.setInt32(0,a,!1),f._cloneInto(i).update(o).digestInto(r),e._cloneInto(i).update(r).digestInto(r),h.set(r.subarray(0,h.length))},rounds:(a,h)=>{for(let l=1;l<a;l++){c.call(t,i).update(r).digestInto(r),u.call(e,i).update(r).digestInto(r);for(let d=0;d<h.length;d++)h[d]^=r[d]}},output:a=>(t.destroy(),e.destroy(),f.destroy(),i.destroy(),g(r),a)}}function vt(t,e,n,r){let{c:o,dkLen:s,DK:f,outputLen:i,eng:c}=se(t,e,n,r);for(let u=1,a=0;a<s;u++,a+=i){let h=f.subarray(a,a+i);c.u1(u,h),c.rounds(o,h)}return c.output(f)}var ie=t=>t/2**32|0,fe=t=>t>>>0;function Mt(t,e,n,r){let o=ie(n),s=fe(n);t.setUint32(e,r?s:o,r),t.setUint32(e+4,r?o:s,r)}function rt(t,e,n){return t&e^~t&n}function ot(t,e,n){return t&e^t&n^e&n}var R=class{blockLen;outputLen;canXOF=!1;padOffset;isLE;buffer;view;finished=!1;length=0;pos=0;destroyed=!1;constructor(e,n,r,o){this.blockLen=e,this.outputLen=n,this.padOffset=r,this.isLE=o,this.buffer=new Uint8Array(e),this.view=D(this.buffer)}update(e){M(this),v(e);let{view:n,buffer:r,blockLen:o}=this,s=e.length,f=!1;for(let i=0;i<s;){let c=Math.min(o-this.pos,s-i);if(c===o){let u=D(e);for(;o<=s-i;i+=o)this.process(u,i);f=!0;continue}r.set(i===0&&c===s?e:e.subarray(i,i+c),this.pos),this.pos+=c,i+=c,this.pos===o&&(this.process(n,0),this.pos=0,f=!0)}return this.length+=e.length,f&&this.roundClean(),this}digestInto(e){M(this),J(e,this),this.finished=!0;let{buffer:n,view:r,blockLen:o,isLE:s}=this,{pos:f}=this;n[f++]=128,n.fill(0,f),this.padOffset>o-f&&(this.process(r,0),n.fill(0)),Mt(r,o-8,this.length*8,s),this.process(r,0),this.roundClean();let i=e===n?r:D(e),c=this.outputLen,u=c/4,a=this.get();if(c%4||u>a.length)throw new Error("invalid outputLen");for(let h=0;h<u;h++)i.setUint32(4*h,a[h],s)}digest(){let{buffer:e,outputLen:n}=this;this.digestInto(e);let r=e.slice(0,n);return this.destroy(),r}_cloneIntoMeta(e){let{buffer:n,length:r,finished:o,destroyed:s,pos:f}=this;return e.destroyed=s,e.finished=o,e.length=r,e.pos=f,f&&e.buffer.set(n),e}clone(){return this._cloneInto()}},Dt=Uint32Array.from([1779033703,3144134277,1013904242,2773480762,1359893119,2600822924,528734635,1541459225]);var he=Uint32Array.from([1116352408,1899447441,3049323471,3921009573,961987163,1508970993,2453635748,2870763221,3624381080,310598401,607225278,1426881987,1925078388,2162078206,2614888103,3248222580,3835390401,4022224774,264347078,604807628,770255983,1249150122,1555081692,1996064986,2554220882,2821834349,2952996808,3210313671,3336571891,3584528711,113926993,338241895,666307205,773529912,1294757372,1396182291,1695183700,1986661051,2177026350,2456956037,2730485921,2820302411,3259730800,3345764771,3516065817,3600352804,4094571909,275423344,430227734,506948616,659060556,883997877,958139571,1322822218,1537002063,1747873779,1955562222,2024104815,2227730452,2361852424,2428436474,2756734187,3204031479,3329325298]),_=new Uint32Array(64),At=class extends R{A=0;B=0;C=0;D=0;E=0;F=0;G=0;H=0;constructor(e,n){super(64,e,8,!1),this.A=n[0]|0,this.B=n[1]|0,this.C=n[2]|0,this.D=n[3]|0,this.E=n[4]|0,this.F=n[5]|0,this.G=n[6]|0,this.H=n[7]|0}get(){let{A:e,B:n,C:r,D:o,E:s,F:f,G:i,H:c}=this;return[e,n,r,o,s,f,i,c]}set(e,n,r,o,s,f,i,c){this.A=e|0,this.B=n|0,this.C=r|0,this.D=o|0,this.E=s|0,this.F=f|0,this.G=i|0,this.H=c|0}_cloneInto(e){return(e||=new this.constructor).set(...this.get()),this._cloneIntoMeta(e)}process(e,n){for(let h=0;h<16;h++,n+=4)_[h]=e.getUint32(n,!1);for(let h=16;h<64;h++){let l=_[h-15],d=_[h-2],p=L(l,7)^L(l,18)^l>>>3,y=L(d,17)^L(d,19)^d>>>10;_[h]=y+_[h-7]+p+_[h-16]|0}let{A:r,B:o,C:s,D:f,E:i,F:c,G:u,H:a}=this;for(let h=0;h<64;h++){let l=L(i,6)^L(i,11)^L(i,25),d=a+l+rt(i,c,u)+he[h]+_[h]|0,y=(L(r,2)^L(r,13)^L(r,22))+ot(r,o,s)|0;a=u,u=c,c=i,i=f+d|0,f=s,s=o,o=r,r=d+y|0}r=r+this.A|0,o=o+this.B|0,s=s+this.C|0,f=f+this.D|0,i=i+this.E|0,c=c+this.F|0,u=u+this.G|0,a=a+this.H|0,this.set(r,o,s,f,i,c,u,a)}roundClean(){g(_)}destroy(){this.destroyed=!0,this.set(0,0,0,0,0,0,0,0),g(this.buffer)}},mt=class extends At{constructor(){super(32,Dt)}};var st=tt(()=>new mt,Kt(1));var $=Uint32Array.from([1732584193,4023233417,2562383102,271733878,3285377520]),S=new Uint32Array(80),Et=class extends R{A=$[0]|0;B=$[1]|0;C=$[2]|0;D=$[3]|0;E=$[4]|0;constructor(){super(64,20,8,!1)}get(){let{A:e,B:n,C:r,D:o,E:s}=this;return[e,n,r,o,s]}set(e,n,r,o,s){this.A=e|0,this.B=n|0,this.C=r|0,this.D=o|0,this.E=s|0}_cloneInto(e){return(e||=new this.constructor).set(...this.get()),this._cloneIntoMeta(e)}process(e,n){for(let c=0;c<16;c++,n+=4)S[c]=e.getUint32(n,!1);for(let c=16;c<80;c++)S[c]=Q(S[c-3]^S[c-8]^S[c-14]^S[c-16],1);let{A:r,B:o,C:s,D:f,E:i}=this;for(let c=0;c<80;c++){let u,a;c<20?(u=rt(o,s,f),a=1518500249):c<40?(u=o^s^f,a=1859775393):c<60?(u=ot(o,s,f),a=2400959708):(u=o^s^f,a=3395469782);let h=Q(r,5)+u+i+a+S[c]|0;i=f,f=s,s=Q(o,30),o=r,r=h}r=r+this.A|0,o=o+this.B|0,s=s+this.C|0,f=f+this.D|0,i=i+this.E|0,this.set(r,o,s,f,i)}roundClean(){g(S)}destroy(){this.destroyed=!0,this.set(0,0,0,0,0),g(this.buffer)}},Rt=tt(()=>new Et);(function(){typeof window>"u"||(window.crypto||(window.crypto={}),window.crypto.getRandomValues||(window.crypto.getRandomValues=function(e){for(let n=0;n<e.length;n++)e[n]=Math.floor(Math.random()*256);return e}),window.crypto.subtle||(console.warn("GembokX: window.crypto.subtle tidak tersedia (Konteks HTTP LAN). Mengaktifkan WebCrypto Pure-JS Fallback (Noble)."),window.crypto.subtle={async importKey(e,n,r,o,s){let f=n instanceof ArrayBuffer?new Uint8Array(n):new Uint8Array(n.buffer,n.byteOffset,n.byteLength);return{type:"secret",extractable:o,algorithm:typeof r=="string"?{name:r}:r,usages:s,_rawKey:f}},async exportKey(e,n){return n._rawKey.buffer.slice(n._rawKey.byteOffset,n._rawKey.byteOffset+n._rawKey.byteLength)},async deriveKey(e,n,r,o,s){let f=e.salt instanceof ArrayBuffer?new Uint8Array(e.salt):new Uint8Array(e.salt.buffer,e.salt.byteOffset,e.salt.byteLength),i=(r.length||256)/8,c=vt(st,n._rawKey,f,{c:e.iterations,dkLen:i});return{type:"secret",extractable:o,algorithm:r,usages:s,_rawKey:c}},async generateKey(e,n,r){let o=new Uint8Array((e.length||256)/8);return window.crypto.getRandomValues(o),{type:"secret",extractable:n,algorithm:e,usages:r,_rawKey:o}},async sign(e,n,r){let o=r instanceof ArrayBuffer?new Uint8Array(r):new Uint8Array(r.buffer,r.byteOffset,r.byteLength),s=nt(st,n._rawKey,o);return s.buffer.slice(s.byteOffset,s.byteOffset+s.byteLength)},async digest(e,n){let r=(typeof e=="string"?e:e.name||"").toUpperCase(),o=n instanceof ArrayBuffer?new Uint8Array(n):new Uint8Array(n.buffer,n.byteOffset,n.byteLength),s;if(r==="SHA-1")s=Rt(o);else if(r==="SHA-256")s=st(o);else throw new Error("Unsupported digest algorithm: "+r);return s.buffer.slice(s.byteOffset,s.byteOffset+s.byteLength)},async encrypt(e,n,r){let o=e.iv instanceof ArrayBuffer?new Uint8Array(e.iv):new Uint8Array(e.iv.buffer,e.iv.byteOffset,e.iv.byteLength),s=r instanceof ArrayBuffer?new Uint8Array(r):new Uint8Array(r.buffer,r.byteOffset,r.byteLength),i=xt(n._rawKey,o).encrypt(s);return i.buffer.slice(i.byteOffset,i.byteOffset+i.byteLength)},async decrypt(e,n,r){let o=e.iv instanceof ArrayBuffer?new Uint8Array(e.iv):new Uint8Array(e.iv.buffer,e.iv.byteOffset,e.iv.byteLength),s=r instanceof ArrayBuffer?new Uint8Array(r):new Uint8Array(r.buffer,r.byteOffset,r.byteLength),i=xt(n._rawKey,o).decrypt(s);return i.buffer.slice(i.byteOffset,i.byteOffset+i.byteLength)}}))})();})();
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
