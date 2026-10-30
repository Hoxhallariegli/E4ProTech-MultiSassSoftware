import makeWASocket, { useMultiFileAuthState, DisconnectReason, BufferJSON } from '@whiskeysockets/baileys';
import pino from 'pino';
import express from 'express';
import path from 'path';
import fs from 'fs';
import http from 'http';
import https from 'https';
import { fileURLToPath } from 'url';
import qrcodeTerminal from 'qrcode-terminal';
import QRCode from 'qrcode';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

const app = express();
app.use(express.json({ limit: '10mb' }));

// Set logger level to warn for active diagnostic visibility
const logger = pino({ level: 'warn' });

// Auto-Heal Unhandled Signal Session Rejections in Real-Time
process.on('unhandledRejection', (reason) => {
    const errStr = String(reason?.stack || reason?.message || reason);
    if (errStr.includes('SessionError') || errStr.includes('Bad MAC') || errStr.includes('failed to decrypt')) {
        console.warn('⚠️ [AUTO-HEAL] Intercepted Signal session warning:', errStr.substring(0, 120));
    }
});

// Suppress non-fatal Baileys internal background sync noise from console output
const originalConsoleError = console.error;
console.error = function(...args) {
    const msg = args.map(a => String(a)).join(' ');
    if (msg.includes('failed to decrypt message') || msg.includes('Failed to decrypt message') || msg.includes('Session error:')) {
        return; // Suppress non-fatal Baileys internal background sync noise
    }
    originalConsoleError.apply(console, args);
};

// Public Health Check Endpoint (No API key required for UptimeRobot / Keep-Alive Pings)
app.get('/health', (req, res) => {
    return res.json({
        status: 'online',
        uptime: process.uptime(),
        timestamp: new Date().toISOString()
    });
});

// Security Middleware: Protect API endpoints with X-API-KEY
const API_SECRET = process.env.WHATSAPP_API_SECRET || 'E4ProTech_Secret_Key_2026_Secure';
const LARAVEL_API_HOST = process.env.LARAVEL_API_HOST || 'https://app.e4protech.com';

app.use((req, res, next) => {
    const clientKey = req.headers['x-api-key'] || req.query.api_key;
    if (clientKey !== API_SECRET) {
        return res.status(401).json({ success: false, error: 'Unauthorized: Invalid API Key' });
    }
    next();
});

// Helper for HTTP/HTTPS requests to Laravel Cloud API
function fetchCloudApi(endpoint, method = 'GET', data = null) {
    return new Promise((resolve) => {
        try {
            const urlObj = new URL(`${LARAVEL_API_HOST}/api/whatsapp-cloud-sessions/${endpoint}`);
            const client = urlObj.protocol === 'https:' ? https : http;
            const bodyStr = data ? JSON.stringify(data) : null;

            const req = client.request(urlObj, {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                    'X-API-KEY': API_SECRET,
                    ...(bodyStr ? { 'Content-Length': Buffer.byteLength(bodyStr) } : {})
                },
                timeout: 10000
            }, (res) => {
                let resData = '';
                res.on('data', chunk => resData += chunk);
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
                    } else {
                        const shortBody = resData.substring(0, 150).replace(/\s+/g, ' ');
                        console.warn(`⚠️ [CLOUD API FAIL] ${method} ${endpoint} Status: ${res.statusCode} Body: ${shortBody}...`);
                        resolve(null);
                    }
                });
            });

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

// Sync session folder files to Laravel Database Cloud
const syncTimers = new Map();

function scheduleSyncToCloud(cleanKey) {
    if (syncTimers.has(cleanKey)) {
        clearTimeout(syncTimers.get(cleanKey));
    }
    const timer = setTimeout(() => {
        syncSessionToCloud(cleanKey);
        syncTimers.delete(cleanKey);
    }, 5000); // 5 second debounce for real-time key persistence
    syncTimers.set(cleanKey, timer);
}

async function syncSessionToCloud(cleanKey) {
    try {
        const fullKey = cleanKey.startsWith('shop_') || cleanKey.startsWith('barber_') ? cleanKey : `shop_${cleanKey}`;
        const sessionDir = path.join(__dirname, 'whatsapp_sessions', fullKey);

        if (!fs.existsSync(sessionDir)) return;

        const files = {};
        const fileNames = fs.readdirSync(sessionDir);
        for (const name of fileNames) {
            const filePath = path.join(sessionDir, name);
            if (fs.statSync(filePath).isFile()) {
                files[name] = fs.readFileSync(filePath, 'base64');
            }
        }

        if (Object.keys(files).length > 0) {
            console.log(`📤 [CLOUD SYNC START] Syncing ${Object.keys(files).length} files for ${fullKey}...`);
            const res = await fetchCloudApi('save', 'POST', {
                session_key: fullKey,
                files: files
            });
            console.log(`☁️ [CLOUD SYNC RESULT] ${fullKey}:`, JSON.stringify(res));
        }
    } catch (e) {
        console.error('❌ [CLOUD SYNC ERROR]:', e.message);
    }
}

// Delete session from Laravel Database Cloud & Local Storage
async function deleteSessionFromCloud(cleanKey) {
    try {
        const fullKey = cleanKey.startsWith('shop_') || cleanKey.startsWith('barber_') ? cleanKey : `shop_${cleanKey}`;
        await fetchCloudApi('delete', 'POST', { session_key: fullKey });
        console.log(`☁️ [CLOUD DELETE] Seanca ${fullKey} u fshi nga baza e të dhënave të Hostinger.`);
    } catch (_) {}
}

// Purge local session directory completely
function purgeSessionDir(fullKey) {
    try {
        const sessionDir = path.join(__dirname, 'whatsapp_sessions', fullKey);
        if (fs.existsSync(sessionDir)) {
            fs.rmSync(sessionDir, { recursive: true, force: true });
            console.log(`🧹 [PURGE] Directory ${fullKey} completely wiped out and destroyed.`);
        }
    } catch (e) {
        console.warn(`Purge error for ${fullKey}:`, e.message);
    }
}

// Purge ONLY corrupted per-contact Signal session keys so Baileys negotiates a fresh Signal handshake without unlinking WhatsApp!
function purgeContactSignalKeys(fullKey, cleanPhone) {
    try {
        const sessionDir = path.join(__dirname, 'whatsapp_sessions', fullKey);
        if (!fs.existsSync(sessionDir)) return;

        const files = fs.readdirSync(sessionDir);
        let deletedCount = 0;
        for (const file of files) {
            // Delete only session-<phone>.*.json and sender-key-* files, preserving creds.json!
            if ((file.startsWith(`session-${cleanPhone}`) || file.startsWith('sender-key-')) && file !== 'creds.json') {
                try {
                    fs.rmSync(path.join(sessionDir, file), { force: true });
                    deletedCount++;
                } catch (_) {}
            }
        }
        if (deletedCount > 0) {
            console.log(`🧹 [AUTO-HEAL SIGNAL] Purged ${deletedCount} stale Signal session files for contact ${cleanPhone} in ${fullKey}`);
            scheduleSyncToCloud(fullKey);
        }
    } catch (e) {
        console.warn('Error purging contact signal keys:', e.message);
    }
}

// Restore saved sessions from Laravel Database Cloud on server startup
async function restoreSessionsFromCloud() {
    try {
        console.log('📦 [CLOUD RESTORE START] Fetching saved sessions from Hostinger DB...');
        const res = await fetchCloudApi('load', 'GET');
        if (res && res.success && res.sessions) {
            const parentDir = path.join(__dirname, 'whatsapp_sessions');
            if (!fs.existsSync(parentDir)) fs.mkdirSync(parentDir, { recursive: true });

            for (const [key, files] of Object.entries(res.sessions)) {
                if (typeof files === 'object' && files !== null) {
                    const sessionDir = path.join(parentDir, key);

                    // Do NOT overwrite local files if creds.json already exists locally!
                    if (fs.existsSync(path.join(sessionDir, 'creds.json'))) {
                        console.log(`📦 [CLOUD RESTORE SKIP] Seanca ${key} ekziston lokalisht. Po ngarkohet pa e mbishkruar...`);
                        loadSentMessagesFromDisk(key);
                        getOrStartSession(key);
                        continue;
                    }

                    if (!fs.existsSync(sessionDir)) fs.mkdirSync(sessionDir, { recursive: true });

                    for (const [fileName, base64Content] of Object.entries(files)) {
                        const filePath = path.join(sessionDir, fileName);
                        fs.writeFileSync(filePath, Buffer.from(base64Content, 'base64'));
                    }
                    console.log(`📦 [CLOUD RESTORE] Seanca ${key} u rikthye automatikisht nga baza e të dhënave!`);
                    loadSentMessagesFromDisk(key);
                    getOrStartSession(key);
                }
            }
        }
    } catch (e) {
        console.warn('Cloud restore warning:', e.message);
    }
}

// Self-Ping Keep-Alive Timer every 5 minutes to prevent Render Free Tier from sleeping!
setInterval(() => {
    try {
        const myUrl = process.env.RENDER_EXTERNAL_URL || 'https://whatsapp-gateway-k6vv.onrender.com';
        https.get(`${myUrl}/health`, (r) => {
            console.log(`⏰ [KEEP-ALIVE PING] Self-ping status: ${r.statusCode}`);
        }).on('error', () => {});
    } catch (_) {}
}, 5 * 60 * 1000);

// Periodic background sync every 2 minutes for connected sessions
setInterval(() => {
    for (const [key, status] of sessionStatus.entries()) {
        if (status && status.connected) {
            syncSessionToCloud(key);
        }
    }
}, 2 * 60 * 1000);

// Store active WhatsApp sockets, status, in-flight promises, watchers & sent messages cache
const sessions = new Map();
const sessionStatus = new Map();
const startingSessions = new Map();
const watchers = new Map();
const sentMessages = new Map(); // msgId -> Baileys message object for exact getMessage retry decryption

// Load sent messages cache from disk on startup (using Baileys BufferJSON.reviver)
function loadSentMessagesFromDisk(fullKey) {
    try {
        const filePath = path.join(__dirname, 'whatsapp_sessions', fullKey, 'sent_messages.json');
        if (fs.existsSync(filePath)) {
            const raw = fs.readFileSync(filePath, 'utf-8');
            const data = JSON.parse(raw, BufferJSON.reviver);
            for (const [msgId, msgObj] of Object.entries(data)) {
                sentMessages.set(msgId, msgObj);
            }
            console.log(`🔑 [SENT CACHE LOADED] Restored ${Object.keys(data).length} sent messages for decryption retry for ${fullKey}`);
        }
    } catch (e) {
        console.warn('Error loading sent messages cache:', e.message);
    }
}

// Save sent messages cache to disk (using Baileys BufferJSON.replacer)
function saveSentMessageToDisk(fullKey, msgId, msgObj) {
    try {
        sentMessages.set(msgId, msgObj);
        const sessionDir = path.join(__dirname, 'whatsapp_sessions', fullKey);
        if (!fs.existsSync(sessionDir)) fs.mkdirSync(sessionDir, { recursive: true });

        const filePath = path.join(sessionDir, 'sent_messages.json');
        let current = {};
        if (fs.existsSync(filePath)) {
            try { current = JSON.parse(fs.readFileSync(filePath, 'utf-8'), BufferJSON.reviver); } catch (_) {}
        }
        current[msgId] = msgObj;

        // Keep only last 50 sent messages
        const keys = Object.keys(current);
        if (keys.length > 50) {
            const extra = keys.slice(0, keys.length - 50);
            for (const k of extra) delete current[k];
        }

        fs.writeFileSync(filePath, JSON.stringify(current, BufferJSON.replacer));
    } catch (e) {
        console.warn('Error saving sent message cache:', e.message);
    }
}

/**
 * Public Entry Point: Prevents duplicate concurrent socket initializations
 */
async function getOrStartSession(sessionKey = 'default', forceFresh = false) {
    const cleanKey = sessionKey.toString().trim();
    const fullKey = cleanKey.startsWith('shop_') || cleanKey.startsWith('barber_') ? cleanKey : `shop_${cleanKey}`;

    if (forceFresh) {
        const oldSock = sessions.get(fullKey);
        sessions.delete(fullKey);
        try { oldSock?.end(new Error('Force fresh session requested')); } catch (_) {}
        purgeSessionDir(fullKey);
        deleteSessionFromCloud(fullKey);
    }

    if (sessions.has(fullKey)) {
        return sessions.get(fullKey);
    }

    if (startingSessions.has(fullKey)) {
        return startingSessions.get(fullKey);
    }

    const p = startSession(fullKey).finally(() => {
        startingSessions.delete(fullKey);
    });

    startingSessions.set(fullKey, p);
    return p;
}

/**
 * Internal Socket Constructor & Event Binder
 */
async function startSession(fullKey) {
    const sessionDir = path.join(__dirname, 'whatsapp_sessions', fullKey);
    if (!fs.existsSync(sessionDir)) {
        fs.mkdirSync(sessionDir, { recursive: true });
    }

    // Real-time FS Watcher: Syncs immediately when pre-keys/creds change on disk (single watcher per key)
    watchers.get(fullKey)?.close();
    try {
        const w = fs.watch(sessionDir, () => scheduleSyncToCloud(fullKey));
        w.on('error', () => {});
        watchers.set(fullKey, w);
    } catch (_) {}

    loadSentMessagesFromDisk(fullKey);

    const { state, saveCreds } = await useMultiFileAuthState(sessionDir);

    const sock = makeWASocket({
        auth: state,
        logger: logger,
        syncFullHistory: true,
        markOnlineOnConnect: true,
        fireInitQueries: true,
        getMessage: async (key) => {
            if (key?.id && sentMessages.has(key.id)) {
                console.log(`🔑 [GET_MESSAGE] Returning exact cached message for decryption retry ID: ${key.id}`);
                return sentMessages.get(key.id);
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
                sessionStatus.set(fullKey, { connected: false, qr: qr, qrImage: qrImage, phone: null });
            } catch (e) {
                sessionStatus.set(fullKey, { connected: false, qr: qr, qrImage: null, phone: null });
            }
            console.log(`📱 [SESSION ${fullKey}] QR Code i ri u gjenerua te kjo seancë.`);
        }

        if (connection === 'close') {
            // Guard: Ignore close events from stale/old closed sockets!
            if (sessions.get(fullKey) !== sock) return;

            sessionStatus.set(fullKey, { connected: false, qr: null, qrImage: null, phone: null });
            const isLoggedOut = (lastDisconnect?.error?.output?.statusCode === DisconnectReason.loggedOut);

            if (isLoggedOut) {
                console.log(`⚠️ [SESSION ${fullKey}] Përdoruesi bëri Logout. Po fshihen çelësat e vjetër...`);
                purgeSessionDir(fullKey);
                sessions.delete(fullKey);
                deleteSessionFromCloud(fullKey);
            } else {
                const credsExistNow = fs.existsSync(path.join(sessionDir, 'creds.json'));
                if (credsExistNow) {
                    console.log(`🔄 [SESSION ${fullKey}] Lidhja u mbyll përkohësisht. Po ri-lidhet automatikisht...`);
                    try { sock.end(undefined); } catch (_) {}
                    sessions.delete(fullKey);
                    setTimeout(() => getOrStartSession(fullKey), 5000);
                } else {
                    console.log(`⏸️ [SESSION ${fullKey}] Seancë e palidhur. Po pret kërkesën për QR Code...`);
                    try { sock.end(undefined); } catch (_) {}
                    sessions.delete(fullKey);
                }
            }
        } else if (connection === 'open') {
            const userPhone = sock.user?.id ? sock.user.id.split(':')[0] : null;
            sessionStatus.set(fullKey, { connected: true, qr: null, qrImage: null, phone: userPhone });
            console.log(`✅ [SESSION ${fullKey}] WhatsApp Gateway është 100% i Lidhur e Aktiv (${userPhone ?? 'Connected'})!`);
            scheduleSyncToCloud(fullKey);
        }
    });

    return sock;
}

// On Render Cloud Startup: Restore all saved sessions from Laravel DB
restoreSessionsFromCloud();

// Health check status SPECIFIC per session key
app.get('/api/whatsapp/status', async (req, res) => {
    let key = (req.query.session_key || req.query.shop_id || '1').toString().trim();
    const fullKey = key.startsWith('shop_') || key.startsWith('barber_') ? key : `shop_${key}`;

    const status = sessionStatus.get(fullKey);

    if (status && status.connected) {
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

// Get QR Code SPECIFIC per session key (Generates QR ON DEMAND & Wipes stale residue ONLY IF requested with ?fresh=1)
app.get('/api/whatsapp/qr', async (req, res) => {
    let key = (req.query.session_key || req.query.shop_id || '1').toString().trim();
    const fullKey = key.startsWith('shop_') || key.startsWith('barber_') ? key : `shop_${key}`;
    const forceFresh = req.query.fresh === '1';

    await getOrStartSession(fullKey, forceFresh);

    let status = sessionStatus.get(fullKey);
    let attempts = 0;

    while (!status?.connected && !status?.qrImage && attempts < 10) {
        await new Promise(r => setTimeout(r, 500));
        status = sessionStatus.get(fullKey);
        attempts++;
    }

    return res.json({
        success: true,
        session_key: fullKey,
        connected: status?.connected ?? false,
        qr: status?.qrImage || status?.qr || null,
    });
});

// Send WhatsApp Message Endpoint
app.post('/api/whatsapp/send', async (req, res) => {
    try {
        const { phone, message, shop_id, barber_id } = req.body;
        const targetShopKey = `shop_${(shop_id || '1').toString().trim()}`;
        const targetBarberKey = barber_id ? `barber_${barber_id.toString().trim()}` : null;

        if (!phone || !message) {
            return res.status(400).json({ success: false, error: 'Phone and message are required' });
        }

        let sock = null;
        let status = null;

        // 1. Try Barber-specific WhatsApp session
        if (targetBarberKey) {
            sock = sessions.get(targetBarberKey);
            status = sessionStatus.get(targetBarberKey);

            if (!status?.connected) {
                sock = await getOrStartSession(targetBarberKey);
                status = sessionStatus.get(targetBarberKey);
            }
        }

        // 2. Try Shop-specific WhatsApp session
        if (!status?.connected) {
            sock = sessions.get(targetShopKey);
            status = sessionStatus.get(targetShopKey);

            if (!status?.connected) {
                sock = await getOrStartSession(targetShopKey);
                status = sessionStatus.get(targetShopKey);
            }
        }

        // 3. Fallback to default central session
        if (!status?.connected) {
            sock = sessions.get('shop_default');
            status = sessionStatus.get('shop_default');
        }

        if (!status?.connected || !sock) {
            return res.status(503).json({ success: false, error: `WhatsApp for Shop/Barber is not connected yet.` });
        }

        // Format phone number
        let cleanPhone = phone.toString().replace(/[^\d]/g, '').trim();
        if (cleanPhone.startsWith('00355')) {
            cleanPhone = cleanPhone.substring(2);
        } else if (cleanPhone.startsWith('0')) {
            cleanPhone = '355' + cleanPhone.substring(1);
        } else if (!cleanPhone.startsWith('355') && cleanPhone.length === 9) {
            cleanPhone = '355' + cleanPhone;
        }

        const currentActiveKey = targetBarberKey && sessionStatus.get(targetBarberKey)?.connected ? targetBarberKey : targetShopKey;

        // Resolve permanent Meta @lid or primary JID via onWhatsApp
        let targetJid = `${cleanPhone}@s.whatsapp.net`;
        try {
            const results = await sock.onWhatsApp(cleanPhone);
            if (results && results.length > 0 && results[0]?.jid) {
                targetJid = results[0].jid; // Resolves to Meta @lid or primary phone JID
                console.log(`🎯 [META LID RESOLVE] Phone ${cleanPhone} resolved to target JID: ${targetJid}`);
            }
        } catch (e) {
            console.warn('⚠️ Verification check warning:', e.message);
        }

        // Instant Pre-Key Session Handshake: Assert sessions and send composing presence to force Meta pre-key exchange before text!
        try {
            if (typeof sock.assertSessions === 'function') {
                await sock.assertSessions([targetJid]);
            }
            await sock.presenceSubscribe(targetJid);
            await sock.sendPresenceUpdate('composing', targetJid);
            await new Promise(r => setTimeout(r, 200));
            await sock.sendPresenceUpdate('paused', targetJid);
        } catch (_) {}

        const sent = await sock.sendMessage(targetJid, { text: message });

        // Store sent message in memory + persist to sent_messages.json using BufferJSON for exact decryption retry across server restarts!
        if (sent?.key?.id && sent?.message) {
            saveSentMessageToDisk(currentActiveKey, sent.key.id, sent.message);
        }

        scheduleSyncToCloud(currentActiveKey);

        console.log(`📩 [WHATSAPP SENT SUCCESS] Session ${currentActiveKey} sent message to ${targetJid}`);
        return res.json({ success: true, shop_id: shop_id, barber_id: barber_id, message: 'Mesazhi u dërgua me sukses në WhatsApp!' });
    } catch (err) {
        console.error('❌ [WHATSAPP ERROR]:', err);
        return res.status(500).json({ success: false, error: err.toString() });
    }
});

const PORT = process.env.PORT || 3000;
app.listen(PORT, () => {
    console.log(`🚀 Multi-Tenant WhatsApp Gateway Server po punon në portën ${PORT}`);
});
