import makeWASocket, { useMultiFileAuthState, DisconnectReason } from '@whiskeysockets/baileys';
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

const logger = pino({ level: 'silent' });

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

            console.log(`🌐 [CLOUD API REQ] ${method} ${urlObj.toString()}`);

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
                    console.log(`🌐 [CLOUD API RES] ${method} ${endpoint} Status: ${res.statusCode} Body: ${resData}`);
                    try {
                        resolve(JSON.parse(resData));
                    } catch (_) {
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
async function syncSessionToCloud(cleanKey) {
    try {
        const fullKey = cleanKey.startsWith('shop_') || cleanKey.startsWith('barber_') ? cleanKey : `shop_${cleanKey}`;
        const sessionDir = path.join(__dirname, 'whatsapp_sessions', fullKey);

        if (!fs.existsSync(sessionDir)) {
            console.warn(`⚠️ [SYNC CHECK] Session directory does not exist yet: ${sessionDir}`);
            return;
        }

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

// Delete session from Laravel Database Cloud
async function deleteSessionFromCloud(cleanKey) {
    try {
        const fullKey = cleanKey.startsWith('shop_') || cleanKey.startsWith('barber_') ? cleanKey : `shop_${cleanKey}`;
        await fetchCloudApi('delete', 'POST', { session_key: fullKey });
        console.log(`☁️ [CLOUD DELETE] Seanca ${fullKey} u fshi nga baza e të dhënave të Hostinger.`);
    } catch (_) {}
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
                    if (!fs.existsSync(sessionDir)) fs.mkdirSync(sessionDir, { recursive: true });

                    for (const [fileName, base64Content] of Object.entries(files)) {
                        const filePath = path.join(sessionDir, fileName);
                        fs.writeFileSync(filePath, Buffer.from(base64Content, 'base64'));
                    }
                    console.log(`📦 [CLOUD RESTORE] Seanca ${key} u rikthye automatikisht nga baza e të dhënave!`);
                    getOrStartSession(key);
                }
            }
        }
    } catch (e) {
        console.warn('Cloud restore warning:', e.message);
    }
}

// Store active WhatsApp sockets & status per session key
const sessions = new Map();
const sessionStatus = new Map();

/**
 * Initialize or get WhatsApp session for a specific session key
 */
async function getOrStartSession(sessionKey = 'default') {
    const cleanKey = sessionKey.toString().trim();

    if (sessions.has(cleanKey) && sessionStatus.get(cleanKey)?.connected) {
        return sessions.get(cleanKey);
    }

    const sessionDir = path.join(__dirname, 'whatsapp_sessions', cleanKey.startsWith('shop_') || cleanKey.startsWith('barber_') ? cleanKey : `shop_${cleanKey}`);
    if (!fs.existsSync(sessionDir)) {
        fs.mkdirSync(sessionDir, { recursive: true });
    }

    const { state, saveCreds } = await useMultiFileAuthState(sessionDir);

    const sock = makeWASocket({
        auth: state,
        logger: logger,
    });

    sessions.set(cleanKey, sock);
    sessionStatus.set(cleanKey, { connected: false, qr: null, qrImage: null, phone: null });

    sock.ev.on('creds.update', async () => {
        await saveCreds();
        // Backup updated credentials to Laravel DB in background!
        syncSessionToCloud(cleanKey);
    });

    sock.ev.on('connection.update', async (update) => {
        const { connection, lastDisconnect, qr } = update;

        if (qr) {
            try {
                const qrImage = await QRCode.toDataURL(qr);
                sessionStatus.set(cleanKey, { connected: false, qr: qr, qrImage: qrImage, phone: null });
            } catch (e) {
                sessionStatus.set(cleanKey, { connected: false, qr: qr, qrImage: null, phone: null });
            }
            console.log(`📱 [SESSION ${cleanKey}] QR Code i ri u gjenerua te kjo seancë.`);
        }

        if (connection === 'close') {
            sessionStatus.set(cleanKey, { connected: false, qr: null, qrImage: null, phone: null });
            const isLoggedOut = (lastDisconnect?.error?.output?.statusCode === DisconnectReason.loggedOut);

            if (isLoggedOut) {
                console.log(`⚠️ [SESSION ${cleanKey}] Përdoruesi bëri Logout. Po fshihen çelësat e vjetër...`);
                try {
                    fs.rmSync(sessionDir, { recursive: true, force: true });
                } catch (e) {
                    console.warn('Error clearing session dir:', e.message);
                }
                sessions.delete(cleanKey);
                deleteSessionFromCloud(cleanKey);
            } else {
                const credsExistNow = fs.existsSync(path.join(sessionDir, 'creds.json'));
                if (credsExistNow) {
                    console.log(`🔄 [SESSION ${cleanKey}] Lidhja u mbyll përkohësisht. Po ri-lidhet automatikisht...`);
                    setTimeout(() => getOrStartSession(cleanKey), 5000);
                } else {
                    console.log(`⏸️ [SESSION ${cleanKey}] Seancë e palidhur. Po pret kërkesën për QR Code...`);
                    sessions.delete(cleanKey);
                }
            }
        } else if (connection === 'open') {
            const userPhone = sock.user?.id ? sock.user.id.split(':')[0] : null;
            sessionStatus.set(cleanKey, { connected: true, qr: null, qrImage: null, phone: userPhone });
            console.log(`✅ [SESSION ${cleanKey}] WhatsApp Gateway është 100% i Lidhur e Aktiv (${userPhone ?? 'Connected'})!`);
            syncSessionToCloud(cleanKey);
        }
    });

    return sock;
}

// On Render Cloud Startup: Restore all saved sessions from Laravel DB
restoreSessionsFromCloud();

// Health check status SPECIFIC per session key
app.get('/api/whatsapp/status', async (req, res) => {
    let key = (req.query.session_key || req.query.shop_id || '1').toString().trim();
    if (!key.startsWith('shop_') && !key.startsWith('barber_')) {
        key = `shop_${key}`;
    }

    const status = sessionStatus.get(key);

    if (status && status.connected) {
        return res.json({
            success: true,
            session_key: key,
            connected: true,
            phone: status.phone,
            message: `WhatsApp for ${key} is active`
        });
    }

    return res.json({
        success: true,
        session_key: key,
        connected: false,
        phone: null,
        message: `WhatsApp for ${key} is not connected`
    });
});

// Get QR Code SPECIFIC per session key (Generates QR ON DEMAND)
app.get('/api/whatsapp/qr', async (req, res) => {
    let key = (req.query.session_key || req.query.shop_id || '1').toString().trim();
    if (!key.startsWith('shop_') && !key.startsWith('barber_')) {
        key = `shop_${key}`;
    }

    await getOrStartSession(key);

    let status = sessionStatus.get(key);
    let attempts = 0;

    while (!status?.connected && !status?.qrImage && attempts < 10) {
        await new Promise(r => setTimeout(r, 500));
        status = sessionStatus.get(key);
        attempts++;
    }

    return res.json({
        success: true,
        session_key: key,
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
            sock = sessions.get('default');
            status = sessionStatus.get('default');
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

        let targetJid = `${cleanPhone}@s.whatsapp.net`;

        try {
            const results = await sock.onWhatsApp(cleanPhone);
            if (results && results.length > 0 && results[0]?.exists) {
                targetJid = results[0].jid;
            }
        } catch (e) {
            console.warn('⚠️ Verification check warning:', e.message);
        }

        await sock.sendMessage(targetJid, { text: message });

        console.log(`📩 [WHATSAPP SENT SUCCESS] Session ${targetBarberKey && sessionStatus.get(targetBarberKey)?.connected ? targetBarberKey : targetShopKey} sent message to ${targetJid}`);
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
