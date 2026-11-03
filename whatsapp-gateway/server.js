import makeWASocket, { useMultiFileAuthState, DisconnectReason, BufferJSON } from '@whiskeysockets/baileys';
import pino from 'pino';
import express from 'express';
import path from 'path';
import fs from 'fs';
import http from 'http';
import https from 'https';
import crypto from 'crypto';
import { fileURLToPath } from 'url';
import QRCode from 'qrcode';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);
const SESSIONS_ROOT = path.join(__dirname, 'whatsapp_sessions');

const app = express();
app.use(express.json({ limit: '10mb' }));

const logger = pino({
    level: 'warn',
    hooks: {
        logMethod(inputArgs, method) {
            const msg = inputArgs.map((a) => (typeof a === 'object' ? JSON.stringify(a) : String(a))).join(' ');
            if (
                msg.includes('failed to decrypt') ||
                msg.includes('Closing session') ||
                msg.includes('Removing old closed session') ||
                msg.includes('Session error') ||
                msg.includes('Bad MAC')
            ) {
                return;
            }
            return method.apply(this, inputArgs);
        }
    }
});

const sleep = (ms) => new Promise((r) => setTimeout(r, ms));

/* ------------------------------------------------------------------ *
 *  LOG HYGIENE
 *  libsignal printon sesionet e plota (me privKey!) në console.
 *  Këtu i heqim nga logjet që të mos rrjedhin çelësat në Render logs.
 * ------------------------------------------------------------------ */
const NOISY_PREFIXES = [
    'Closing session',
    'Removing old closed session',
    'Opening session',
    'Migrating session',
    'Session already closed',
    'Session already open',
    'Closing open session',
    'failed to decrypt message',
    'Failed to decrypt message'
];
for (const method of ['log', 'info', 'warn']) {
    const orig = console[method].bind(console);
    console[method] = (...args) => {
        const first = typeof args[0] === 'string' ? args[0] : '';
        if (NOISY_PREFIXES.some((p) => first.startsWith(p))) return;
        orig(...args);
    };
}

const originalConsoleError = console.error;
console.error = function (...args) {
    const msg = args.map((a) => String(a)).join(' ');
    if (
        msg.includes('failed to decrypt message') ||
        msg.includes('Failed to decrypt message') ||
        msg.includes('Session error:') ||
        msg.includes('error in handling message') ||
        msg.includes('init queries')
    ) {
        return;
    }
    originalConsoleError.apply(console, args);
};

process.on('unhandledRejection', (reason) => {
    const errStr = String(reason?.stack || reason?.message || reason);
    console.warn('⚠️ [UNHANDLED REJECTION]', errStr.substring(0, 200));
});

/* ------------------------------------------------------------------ *
 *  CONFIG
 * ------------------------------------------------------------------ */
// VENDOSE këto në Render si Environment Variables!
const API_SECRET = process.env.WHATSAPP_API_SECRET || 'E4ProTech_Secret_Key_2026_Secure';
const LARAVEL_API_HOST = process.env.LARAVEL_API_HOST || 'https://app.e4protech.com';

const KEY_PATTERN = /^(shop|barber)_[\w-]+$/;
const toFullKey = (k) => {
    const s = String(k).trim();
    return s.startsWith('shop_') || s.startsWith('barber_') ? s : `shop_${s}`;
};
const sessionDirOf = (fullKey) => path.join(SESSIONS_ROOT, fullKey);

let shuttingDown = false;

/* ------------------------------------------------------------------ *
 *  PUBLIC HEALTH + AUTH
 * ------------------------------------------------------------------ */
app.get('/health', (req, res) => {
    return res.json({ status: 'online', uptime: process.uptime(), timestamp: new Date().toISOString() });
});

app.use((req, res, next) => {
    const clientKey = req.headers['x-api-key'] || req.query.api_key;
    if (clientKey !== API_SECRET) {
        return res.status(401).json({ success: false, error: 'Unauthorized: Invalid API Key' });
    }
    next();
});

/* ------------------------------------------------------------------ *
 *  LARAVEL CLOUD API
 * ------------------------------------------------------------------ */
function fetchCloudApi(endpoint, method = 'GET', data = null, isFallback = false) {
    return new Promise((resolve) => {
        try {
            const basePath = (isFallback && endpoint === 'webhook')
                ? `${LARAVEL_API_HOST}/api/whatsapp/${endpoint}`
                : `${LARAVEL_API_HOST}/api/whatsapp-cloud-sessions/${endpoint}`;

            const urlObj = new URL(basePath);
            const client = urlObj.protocol === 'https:' ? https : http;
            const bodyStr = data ? JSON.stringify(data) : null;

            const req = client.request(
                urlObj,
                {
                    method,
                    headers: {
                        'Content-Type': 'application/json',
                        'X-API-KEY': API_SECRET,
                        ...(bodyStr ? { 'Content-Length': Buffer.byteLength(bodyStr) } : {})
                    },
                    timeout: 30000
                },
                (res) => {
                    let resData = '';
                    res.on('data', (chunk) => (resData += chunk));
                    res.on('end', () => {
                        if (res.statusCode >= 200 && res.statusCode < 300) {
                            try {
                                const parsed = JSON.parse(resData);
                                console.log(`🌐 [CLOUD API SUCCESS] ${method} ${endpoint} Status: ${res.statusCode}`);
                                resolve(parsed);
                            } catch (e) {
                                console.warn(`⚠️ [CLOUD API PARSE ERROR] ${method} ${endpoint}: Not valid JSON`);
                                resolve(null);
                            }
                        } else if (res.statusCode === 404 && !isFallback && endpoint === 'webhook') {
                            console.warn(`⚠️ [CLOUD API RE-TRY] ${endpoint} returned 404. Trying fallback /api/whatsapp/webhook...`);
                            resolve(fetchCloudApi(endpoint, method, data, true));
                        } else {
                            const shortBody = resData.substring(0, 150).replace(/\s+/g, ' ');
                            console.warn(`⚠️ [CLOUD API FAIL] ${method} ${endpoint} Status: ${res.statusCode} Body: ${shortBody}...`);
                            if (res.statusCode === 404) {
                                console.warn(`💡 [DEPLOYMENT TIP] Status 404 means Hostinger production server needs 'git pull' and 'php artisan route:clear'!`);
                            }
                            resolve(null);
                        }
                    });
                }
            );

            req.on('error', (e) => {
                console.error(`❌ [CLOUD API ERROR] ${method} ${endpoint}:`, e.message);
                resolve(null);
            });
            req.on('timeout', () => {
                console.error(`❌ [CLOUD API TIMEOUT] ${method} ${endpoint}`);
                req.destroy();
                resolve(null);
            });

            if (bodyStr) req.write(bodyStr);
            req.end();
        } catch (e) {
            console.error(`❌ [CLOUD API CATCH] ${method} ${endpoint}:`, e.message);
            resolve(null);
        }
    });
}

/* ------------------------------------------------------------------ *
 *  STATE
 * ------------------------------------------------------------------ */
const sessions = new Map();          // fullKey -> sock
const sessionStatus = new Map();     // fullKey -> {connected, qr, qrImage, phone}
const startingSessions = new Map();  // fullKey -> Promise
const watchers = new Map();          // fullKey -> fs.FSWatcher
const syncTimers = new Map();        // fullKey -> Timeout
const syncing = new Set();           // fullKey currently uploading
const lastSyncHash = new Map();      // fullKey -> sha1 of last uploaded snapshot
const reconnectAttempts = new Map(); // fullKey -> n
const sentMessages = new Map();      // fullKey -> Map(msgId -> message)
const sentLoaded = new Set();        // fullKey whose disk cache was loaded

/* ------------------------------------------------------------------ *
 *  CREDS HELPERS
 * ------------------------------------------------------------------ */
function isRegisteredFile(filePath) {
    try {
        return !!JSON.parse(fs.readFileSync(filePath, 'utf-8'))?.me; // 'me' vendoset pas çiftimit me QR
    } catch (_) {
        return false;
    }
}

function isRegisteredBase64(b64) {
    try {
        if (!b64) return false;
        return !!JSON.parse(Buffer.from(b64, 'base64').toString('utf-8'))?.me;
    } catch (_) {
        return false;
    }
}

const hasRegisteredSession = (fullKey) => isRegisteredFile(path.join(sessionDirOf(fullKey), 'creds.json'));

/* ------------------------------------------------------------------ *
 *  CLOUD SYNC (vetëm kur ka ndryshim real)
 * ------------------------------------------------------------------ */
function scheduleSyncToCloud(cleanKey) {
    if (shuttingDown) return;
    const fullKey = toFullKey(cleanKey);
    if (syncTimers.has(fullKey)) clearTimeout(syncTimers.get(fullKey));
    const timer = setTimeout(() => {
        syncTimers.delete(fullKey);
        syncSessionToCloud(fullKey);
    }, 5000);
    syncTimers.set(fullKey, timer);
}

async function syncSessionToCloud(cleanKey, force = false) {
    const fullKey = toFullKey(cleanKey);
    try {
        const sessionDir = sessionDirOf(fullKey);
        if (!fs.existsSync(sessionDir)) return;

        if (syncing.has(fullKey)) {
            if (!force) scheduleSyncToCloud(fullKey);
            return;
        }

        const files = {};
        const hash = crypto.createHash('sha1');
        const names = fs.readdirSync(sessionDir).sort();
        for (const name of names) {
            const filePath = path.join(sessionDir, name);
            try {
                if (!fs.statSync(filePath).isFile()) continue;
                const buf = fs.readFileSync(filePath);
                hash.update(name);
                hash.update(buf);
                files[name] = buf.toString('base64');
            } catch (_) {
                // file u fshi/po shkruhet gjatë leximit - e kapërcejmë
            }
        }

        if (Object.keys(files).length === 0) return;

        const digest = hash.digest('hex');
        if (!force && lastSyncHash.get(fullKey) === digest) return; // asnjë ndryshim

        syncing.add(fullKey);
        try {
            console.log(`📤 [CLOUD SYNC START] Syncing ${Object.keys(files).length} files for ${fullKey}...`);
            const res = await fetchCloudApi('save', 'POST', { session_key: fullKey, files });
            if (res) lastSyncHash.set(fullKey, digest);
            console.log(`☁️ [CLOUD SYNC RESULT] ${fullKey}:`, JSON.stringify(res));
        } finally {
            syncing.delete(fullKey);
        }
    } catch (e) {
        syncing.delete(fullKey);
        console.error('❌ [CLOUD SYNC ERROR]:', e.message);
    }
}

async function deleteSessionFromCloud(cleanKey) {
    try {
        const fullKey = toFullKey(cleanKey);
        await fetchCloudApi('delete', 'POST', { session_key: fullKey });
        console.log(`☁️ [CLOUD DELETE] Seanca ${fullKey} u fshi nga baza e të dhënave.`);
    } catch (_) {}
}

function purgeSessionDir(fullKey) {
    try {
        if (syncTimers.has(fullKey)) {
            clearTimeout(syncTimers.get(fullKey));
            syncTimers.delete(fullKey);
        }
        try { watchers.get(fullKey)?.close(); } catch (_) {}
        watchers.delete(fullKey);
        lastSyncHash.delete(fullKey);
        sentMessages.delete(fullKey);
        sentLoaded.delete(fullKey);

        const dir = sessionDirOf(fullKey);
        if (fs.existsSync(dir)) {
            fs.rmSync(dir, { recursive: true, force: true });
            console.log(`🧹 [PURGE] Directory ${fullKey} completely wiped out and destroyed.`);
        }
    } catch (e) {
        console.warn(`Purge error for ${fullKey}:`, e.message);
    }
}

/* ------------------------------------------------------------------ *
 *  SENT MESSAGES CACHE (për retry të dekriptimit)
 * ------------------------------------------------------------------ */
function getSentCache(fullKey) {
    if (!sentMessages.has(fullKey)) sentMessages.set(fullKey, new Map());
    return sentMessages.get(fullKey);
}

function loadSentMessagesFromDisk(fullKey) {
    if (sentLoaded.has(fullKey)) return;
    try {
        const filePath = path.join(sessionDirOf(fullKey), 'sent_messages.json');
        if (fs.existsSync(filePath)) {
            const data = JSON.parse(fs.readFileSync(filePath, 'utf-8'), BufferJSON.reviver);
            const cache = getSentCache(fullKey);
            for (const [msgId, msgObj] of Object.entries(data)) cache.set(msgId, msgObj);
            console.log(`🔑 [SENT CACHE LOADED] Restored ${Object.keys(data).length} sent messages for ${fullKey}`);
        }
        sentLoaded.add(fullKey);
    } catch (e) {
        console.warn('Error loading sent messages cache:', e.message);
    }
}

function saveSentMessageToDisk(fullKey, msgId, msgObj) {
    try {
        const cache = getSentCache(fullKey);
        cache.set(msgId, msgObj);
        while (cache.size > 50) cache.delete(cache.keys().next().value);

        const dir = sessionDirOf(fullKey);
        if (!fs.existsSync(dir)) fs.mkdirSync(dir, { recursive: true });
        fs.writeFileSync(path.join(dir, 'sent_messages.json'), JSON.stringify(Object.fromEntries(cache), BufferJSON.replacer));
    } catch (e) {
        console.warn('Error saving sent message cache:', e.message);
    }
}

/* ------------------------------------------------------------------ *
 *  RESTORE NGA CLOUD (vetëm seanca të çiftuara, me radhë)
 * ------------------------------------------------------------------ */
async function restoreSessionsFromCloud() {
    try {
        console.log('📦 [CLOUD RESTORE START] Fetching saved sessions from DB...');
        const res = await fetchCloudApi('load', 'GET');
        if (!res || !res.success || !res.sessions) return;

        if (!fs.existsSync(SESSIONS_ROOT)) fs.mkdirSync(SESSIONS_ROOT, { recursive: true });

        const toStart = [];

        for (const [key, files] of Object.entries(res.sessions)) {
            if (!KEY_PATTERN.test(key)) {
                console.warn(`⚠️ [CLOUD RESTORE] Çelës i pavlefshëm u kapërcye: ${key}`);
                continue;
            }
            if (typeof files !== 'object' || files === null) continue;

            const dir = sessionDirOf(key);
            const localCreds = path.join(dir, 'creds.json');

            if (fs.existsSync(localCreds)) {
                console.log(`📦 [CLOUD RESTORE SKIP] Seanca ${key} ekziston lokalisht. Po ngarkohet pa e mbishkruar...`);
                if (isRegisteredFile(localCreds)) toStart.push(key);
                continue;
            }

            if (!isRegisteredBase64(files['creds.json'])) {
                console.log(`📦 [CLOUD RESTORE SKIP] ${key} nuk është çiftuar (pa QR të skanuar). Nuk niset.`);
                continue;
            }

            fs.mkdirSync(dir, { recursive: true });
            for (const [fileName, base64Content] of Object.entries(files)) {
                fs.writeFileSync(path.join(dir, path.basename(fileName)), Buffer.from(base64Content, 'base64'));
            }
            console.log(`📦 [CLOUD RESTORE] Seanca ${key} u rikthye automatikisht nga baza e të dhënave!`);
            toStart.push(key);
        }

        // Nisim një nga një që të mos godasim memorien e Render-it
        for (const key of toStart) {
            if (shuttingDown) break;
            loadSentMessagesFromDisk(key);
            getOrStartSession(key).catch((e) => console.warn(`Start error ${key}:`, e.message));
            await sleep(2000);
        }
    } catch (e) {
        console.warn('Cloud restore warning:', e.message);
    }
}

/* ------------------------------------------------------------------ *
 *  TIMERS
 * ------------------------------------------------------------------ */
setInterval(() => {
    try {
        const myUrl = process.env.RENDER_EXTERNAL_URL || 'https://whatsapp-gateway-k6vv.onrender.com';
        https.get(`${myUrl}/health`, (r) => {
            console.log(`⏰ [KEEP-ALIVE PING] Self-ping status: ${r.statusCode}`);
            r.resume();
        }).on('error', () => {});
    } catch (_) {}
}, 5 * 60 * 1000);

// Sync periodik, por dërgon vetëm nëse ka ndryshuar diçka (hash)
setInterval(() => {
    for (const [key, status] of sessionStatus.entries()) {
        if (status?.connected) syncSessionToCloud(key);
    }
}, 2 * 60 * 1000);

/* ------------------------------------------------------------------ *
 *  SESSION LIFECYCLE
 * ------------------------------------------------------------------ */
async function getOrStartSession(sessionKey = 'default', forceFresh = false) {
    const fullKey = toFullKey(sessionKey);

    if (forceFresh) {
        const oldSock = sessions.get(fullKey);
        sessions.delete(fullKey);
        try { oldSock?.end(new Error('Force fresh session requested')); } catch (_) {}
        purgeSessionDir(fullKey);
        deleteSessionFromCloud(fullKey);
    }

    if (sessions.has(fullKey)) return sessions.get(fullKey);
    if (startingSessions.has(fullKey)) return startingSessions.get(fullKey);

    const p = startSession(fullKey).finally(() => startingSessions.delete(fullKey));
    startingSessions.set(fullKey, p);
    return p;
}

async function startSession(fullKey) {
    const sessionDir = sessionDirOf(fullKey);
    if (!fs.existsSync(sessionDir)) fs.mkdirSync(sessionDir, { recursive: true });

    try { watchers.get(fullKey)?.close(); } catch (_) {}
    try {
        const w = fs.watch(sessionDir, () => scheduleSyncToCloud(fullKey));
        w.on('error', () => {});
        watchers.set(fullKey, w);
    } catch (_) {}

    loadSentMessagesFromDisk(fullKey);

    const { state, saveCreds } = await useMultiFileAuthState(sessionDir);

    const sock = makeWASocket({
        auth: state,
        logger,
        syncFullHistory: false,
        markOnlineOnConnect: true,
        fireInitQueries: false,
        getMessage: async (key) => {
            const cached = key?.id ? sentMessages.get(fullKey)?.get(key.id) : undefined;
            if (cached) {
                console.log(`🔑 [GET_MESSAGE] Returning cached message for retry ID: ${key.id}`);
                return cached;
            }
            return undefined;
        }
    });

    sessions.set(fullKey, sock);
    sessionStatus.set(fullKey, { connected: false, qr: null, qrImage: null, phone: null });

    sock.ev.on('creds.update', async () => {
        await saveCreds();
        scheduleSyncToCloud(fullKey);
    });

    sock.ev.on('connection.update', async (update) => {
        const { connection, lastDisconnect, qr } = update;

        if (qr) {
            try {
                const qrImage = await QRCode.toDataURL(qr);
                sessionStatus.set(fullKey, { connected: false, qr, qrImage, phone: null });
            } catch (e) {
                sessionStatus.set(fullKey, { connected: false, qr, qrImage: null, phone: null });
            }
            console.log(`📱 [SESSION ${fullKey}] QR Code i ri u gjenerua te kjo seancë.`);
        }

        if (connection === 'close') {
            if (shuttingDown) return;
            if (sessions.get(fullKey) !== sock) return; // socket i vjetër

            sessionStatus.set(fullKey, { connected: false, qr: null, qrImage: null, phone: null });

            const statusCode = lastDisconnect?.error?.output?.statusCode;
            const errorMsg = String(lastDisconnect?.error?.output?.payload?.message || lastDisconnect?.error?.message || '');

            const isExplicitLogout =
                statusCode === DisconnectReason.loggedOut &&
                (errorMsg.includes('Logged Out') || errorMsg.includes('logged out'));

            const dropSock = () => {
                sessions.delete(fullKey);
                try { sock.end(undefined); } catch (_) {}
            };

            if (isExplicitLogout) {
                console.log(`⚠️ [SESSION ${fullKey}] Përdoruesi bëri Logout zyrtar. Po fshihen çelësat e vjetër...`);
                sessions.delete(fullKey);
                purgeSessionDir(fullKey);
                deleteSessionFromCloud(fullKey);
                return;
            }

            // Një instancë tjetër (p.sh. deploy i ri në Render ose server lokal) mori seancën.
            // NUK ri-lidhemi: ping-pong mes dy instancave prish çelësat Signal (Bad MAC).
            if (statusCode === DisconnectReason.connectionReplaced) {
                console.warn(`🛑 [SESSION ${fullKey}] Seanca u zëvendësua nga një instancë tjetër (440). Po ndalet këtu.`);
                dropSock();
                return;
            }

            // Seancë e paçiftuar (QR i skaduar): mos u ri-lidh pafund. Rikrijohet kur kërkohet /qr.
            if (!state.creds.me && statusCode !== DisconnectReason.restartRequired) {
                console.log(`🧹 [SESSION ${fullKey}] QR skadoi pa u çiftuar. Seanca u mbyll deri në kërkesën e radhës.`);
                dropSock();
                try { watchers.get(fullKey)?.close(); } catch (_) {}
                watchers.delete(fullKey);
                return;
            }

            // Shkëputje e përkohshme: ri-lidhje me backoff
            const attempt = reconnectAttempts.get(fullKey) ?? 0;
            reconnectAttempts.set(fullKey, attempt + 1);
            const delay = Math.min(3000 * 2 ** attempt, 60000);
            console.log(`🔄 [SESSION ${fullKey}] Shkëputje (Code: ${statusCode ?? 'Unknown'}). Ri-lidhje pas ${Math.round(delay / 1000)}s...`);
            dropSock();
            setTimeout(() => {
                if (!shuttingDown) getOrStartSession(fullKey).catch((e) => console.warn(`Reconnect error ${fullKey}:`, e.message));
            }, delay);
        } else if (connection === 'open') {
            reconnectAttempts.set(fullKey, 0);
            const userPhone = sock.user?.id ? sock.user.id.split(':')[0] : null;
            sessionStatus.set(fullKey, { connected: true, qr: null, qrImage: null, phone: userPhone });
            console.log(`✅ [SESSION ${fullKey}] WhatsApp Gateway është 100% i Lidhur e Aktiv (${userPhone ?? 'Connected'})!`);
            scheduleSyncToCloud(fullKey);
        }
    });

    // Handle Incoming Interactive Button Responses & Text Replies
    sock.ev.on('messages.upsert', async (m) => {
        try {
            if (m.type !== 'notify') return;
            for (const msg of m.messages) {
                if (msg.key.fromMe) continue;

                const senderJid = msg.key.remoteJid;
                if (!senderJid || senderJid.endsWith('@g.us')) continue;

                const cleanPhone = senderJid.split('@')[0].split(':')[0].replace(/[^\d]/g, '');

                const buttonId = msg.message?.buttonsResponseMessage?.selectedButtonId
                    || msg.message?.templateButtonReplyMessage?.selectedId
                    || msg.message?.interactiveResponseMessage?.nativeFlowResponseMessage?.paramsJson
                    || null;

                const textContent = msg.message?.conversation
                    || msg.message?.extendedTextMessage?.text
                    || msg.message?.buttonsResponseMessage?.selectedDisplayText
                    || '';

                if (!buttonId && !textContent) continue;

                console.log(`📩 [INCOMING WA REPLY] Session: ${fullKey}, Phone: ${cleanPhone}, Button: ${buttonId}, Text: "${textContent}"`);

                const shopIdMatch = fullKey.match(/\d+/);
                const shopId = shopIdMatch ? shopIdMatch[0] : '1';

                fetchCloudApi('webhook', 'POST', {
                    shop_id: shopId,
                    session_key: fullKey,
                    phone: cleanPhone,
                    button_id: buttonId,
                    text: textContent,
                    timestamp: msg.messageTimestamp
                });
            }
        } catch (err) {
            console.warn('Error handling incoming WhatsApp message:', err.message);
        }
    });

    return sock;
}

/**
 * Kthen sock të lidhur ose null. NUK krijon kurrë seancë të re të paçiftuar nga /send.
 */
async function getConnectedSock(fullKey) {
    let sock = sessions.get(fullKey);
    if (sock && sessionStatus.get(fullKey)?.connected) return sock;

    if (!hasRegisteredSession(fullKey)) return null;

    sock = await getOrStartSession(fullKey);
    for (let i = 0; i < 16 && !sessionStatus.get(fullKey)?.connected; i++) {
        await sleep(500);
    }
    return sessionStatus.get(fullKey)?.connected ? sock : null;
}

restoreSessionsFromCloud();

/* ------------------------------------------------------------------ *
 *  ROUTES
 * ------------------------------------------------------------------ */
app.get('/api/whatsapp/status', async (req, res) => {
    const key = (req.query.session_key || req.query.shop_id || '1').toString().trim();
    const fullKey = toFullKey(key);
    const status = sessionStatus.get(fullKey);

    if (status?.connected) {
        return res.json({
            success: true,
            session_key: fullKey,
            connected: true,
            phone: status.phone,
            message: `WhatsApp for ${fullKey} is active`
        });
    }

    return res.json({
        success: true,
        session_key: fullKey,
        connected: false,
        phone: null,
        message: `WhatsApp for ${fullKey} is not connected`
    });
});

app.get('/api/whatsapp/qr', async (req, res) => {
    const key = (req.query.session_key || req.query.shop_id || '1').toString().trim();
    const fullKey = toFullKey(key);
    const forceFresh = req.query.fresh === '1';

    await getOrStartSession(fullKey, forceFresh);

    let status = sessionStatus.get(fullKey);
    let attempts = 0;
    while (!status?.connected && !status?.qrImage && attempts < 20) {
        await sleep(500);
        status = sessionStatus.get(fullKey);
        attempts++;
    }

    return res.json({
        success: true,
        session_key: fullKey,
        connected: status?.connected ?? false,
        qr: status?.qrImage || status?.qr || null
    });
});

        app.post('/api/whatsapp/send', async (req, res) => {
    try {
        const { phone, message, shop_id, barber_id, buttons, image_url, document_url, audio_url, file_name } = req.body;

        if (!phone || (!message && !image_url && !document_url && !audio_url)) {
            return res.status(400).json({ success: false, error: 'Phone and message or media payload are required' });
        }

        const targetShopKey = `shop_${(shop_id || '1').toString().trim()}`;
        const targetBarberKey = barber_id ? `barber_${barber_id.toString().trim()}` : null;

        // 1) barber -> 2) shop -> 3) default
        let activeKey = null;
        let sock = null;
        for (const candidate of [targetBarberKey, targetShopKey, 'shop_default']) {
            if (!candidate) continue;
            sock = await getConnectedSock(candidate);
            if (sock) {
                activeKey = candidate;
                break;
            }
        }

        if (!sock || !activeKey) {
            return res.status(503).json({ success: false, error: 'WhatsApp for Shop/Barber is not connected yet.' });
        }

        // Formatimi i numrit
        let cleanPhone = phone.toString().replace(/[^\d]/g, '').trim();
        if (cleanPhone.startsWith('00355')) {
            cleanPhone = cleanPhone.substring(2);
        } else if (cleanPhone.startsWith('0')) {
            cleanPhone = '355' + cleanPhone.substring(1);
        } else if (!cleanPhone.startsWith('355') && cleanPhone.length === 9) {
            cleanPhone = '355' + cleanPhone;
        }

        // Zgjidh JID-in e saktë dhe kontrollo që numri ka WhatsApp
        let targetJid = `${cleanPhone}@s.whatsapp.net`;
        try {
            const results = await sock.onWhatsApp(cleanPhone);
            if (results && results.length > 0) {
                if (results[0]?.exists === false) {
                    return res.status(404).json({ success: false, error: `Numri ${cleanPhone} nuk ka WhatsApp.` });
                }
                if (results[0]?.jid) {
                    targetJid = results[0].jid;
                    console.log(`🎯 [JID RESOLVE] Phone ${cleanPhone} resolved to target JID: ${targetJid}`);
                }
            }
        } catch (e) {
            console.warn('⚠️ Verification check warning:', e.message);
        }

        // Anti-Ban Humanizer Presence Delay (Dynamic scaling based on text length)
        try {
            if (typeof sock.assertSessions === 'function') {
                await sock.assertSessions([targetJid]);
            }
            await sock.presenceSubscribe(targetJid);
            const isAudio = Boolean(audio_url);
            await sock.sendPresenceUpdate(isAudio ? 'recording' : 'composing', targetJid);
            const humanDelay = Math.min(2500, Math.max(500, (message || '').length * 15));
            await sleep(humanDelay);
            await sock.sendPresenceUpdate('paused', targetJid);
        } catch (_) {}

        // Prepare message content (Text, Images, Documents, Audio, or Interactive Buttons)
        let messagePayload = { text: message || '' };

        if (image_url) {
            messagePayload = { image: { url: image_url }, caption: message || '' };
        } else if (document_url) {
            messagePayload = {
                document: { url: document_url },
                fileName: file_name || 'Dokument.pdf',
                mimetype: 'application/pdf',
                caption: message || ''
            };
        } else if (audio_url) {
            messagePayload = { audio: { url: audio_url }, ptt: true, mimetype: 'audio/ogg; codecs=opus' };
        } else if (Array.isArray(buttons) && buttons.length > 0) {
            const formattedButtons = buttons.map((b, i) => ({
                buttonId: b.id || `btn_${i}`,
                buttonText: { displayText: b.text || b.label || 'Zgjidh' },
                type: 1
            }));

            messagePayload = {
                text: message,
                footer: 'E4ProTech Sallon Management',
                buttons: formattedButtons,
                headerType: 1
            };
        }

        const sent = await sock.sendMessage(targetJid, messagePayload);

        if (sent?.key?.id && sent?.message) {
            saveSentMessageToDisk(activeKey, sent.key.id, sent.message);
        }
        scheduleSyncToCloud(activeKey);

        console.log(`📩 [WHATSAPP SENT SUCCESS] Session ${activeKey} sent message to ${targetJid}`);
        return res.json({
            success: true,
            shop_id,
            barber_id,
            session_key: activeKey,
            message: 'Mesazhi u dërgua me sukses në WhatsApp!'
        });
    } catch (err) {
        console.error('❌ [WHATSAPP ERROR]:', err);
        return res.status(500).json({ success: false, error: err.toString() });
    }
});

/* ------------------------------------------------------------------ *
 *  START + GRACEFUL SHUTDOWN (Render dërgon SIGTERM në çdo deploy/restart)
 *  Para daljes ndalojmë socket-et dhe ngarkojmë çelësat e fundit në cloud,
 *  që seanca të mos kthehet prapa në një gjendje të vjetër (Bad MAC).
 * ------------------------------------------------------------------ */
const PORT = process.env.PORT || 3000;
const server = app.listen(PORT, () => {
    console.log(`🚀 Multi-Tenant WhatsApp Gateway Server po punon në portën ${PORT}`);
});

async function gracefulShutdown(signal) {
    if (shuttingDown) return;
    shuttingDown = true;
    console.log(`🛑 [SHUTDOWN] ${signal} i marrë. Po ruhen seancat...`);

    const hardExit = setTimeout(() => process.exit(0), 20000);
    hardExit.unref();

    try { server.close(); } catch (_) {}

    for (const timer of syncTimers.values()) clearTimeout(timer);
    syncTimers.clear();

    const keys = [...sessions.keys()];
    for (const key of keys) {
        try { sessions.get(key)?.end(undefined); } catch (_) {}
    }
    await sleep(500); // lejo shkrimin e fundit të creds në disk

    await Promise.allSettled(keys.filter(hasRegisteredSession).map((k) => syncSessionToCloud(k, true)));

    console.log('✅ [SHUTDOWN] Gati. Duke dalë.');
    process.exit(0);
}

process.on('SIGTERM', () => gracefulShutdown('SIGTERM'));
process.on('SIGINT', () => gracefulShutdown('SIGINT'));
