import makeWASocket, { useMultiFileAuthState, DisconnectReason } from '@whiskeysockets/baileys';
import pino from 'pino';
import express from 'express';
import path from 'path';
import fs from 'fs';
import { fileURLToPath } from 'url';
import qrcodeTerminal from 'qrcode-terminal';
import QRCode from 'qrcode';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

const app = express();
app.use(express.json());

const logger = pino({ level: 'silent' });

// Auto-migrate legacy whatsapp_session folder to shop_1 if shop_1 doesn't exist yet
const legacySessionDir = path.join(__dirname, 'whatsapp_session');
const shop1SessionDir = path.join(__dirname, 'whatsapp_sessions', 'shop_1');

if (fs.existsSync(legacySessionDir) && !fs.existsSync(shop1SessionDir)) {
    try {
        fs.mkdirSync(path.dirname(shop1SessionDir), { recursive: true });
        fs.cpSync(legacySessionDir, shop1SessionDir, { recursive: true });
        console.log('📦 Auto-migrated legacy WhatsApp session to shop_1');
    } catch (err) {
        console.warn('Migration warning:', err.message);
    }
}

// Store active WhatsApp sockets & status per session key (shop_1 or barber_5)
const sessions = new Map();
const sessionStatus = new Map();

/**
 * Initialize or get WhatsApp session for a specific session key (shop_1 or barber_5)
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

    sock.ev.on('creds.update', saveCreds);

    sock.ev.on('connection.update', async (update) => {
        const { connection, lastDisconnect, qr } = update;

        if (qr) {
            try {
                const qrImage = await QRCode.toDataURL(qr);
                sessionStatus.set(cleanKey, { connected: false, qr: qr, qrImage: qrImage, phone: null });
            } catch (e) {
                sessionStatus.set(cleanKey, { connected: false, qr: qr, qrImage: null, phone: null });
            }
            console.log(`\n📱 [SESSION ${cleanKey}] QR Code i ri u gjenerua te kjo seancë.\n`);
            qrcodeTerminal.generate(qr, { small: true });
        }

        if (connection === 'close') {
            sessionStatus.set(cleanKey, { connected: false, qr: null, qrImage: null, phone: null });
            const isLoggedOut = (lastDisconnect?.error?.output?.statusCode === DisconnectReason.loggedOut);

            if (isLoggedOut) {
                console.log(`⚠️ [SESSION ${cleanKey}] Përdoruesi bëri Logout nga telefoni. Po pastrohet seanca e vjetër...`);
                try {
                    fs.rmSync(sessionDir, { recursive: true, force: true });
                } catch (e) {
                    console.warn('Error clearing session dir:', e.message);
                }
                sessions.delete(cleanKey);
                setTimeout(() => getOrStartSession(cleanKey), 1500);
            } else {
                console.log(`🔄 [SESSION ${cleanKey}] Lidhja u mbyll përkohësisht. Po ri-lidhet...`);
                setTimeout(() => getOrStartSession(cleanKey), 4000);
            }
        } else if (connection === 'open') {
            const userPhone = sock.user?.id ? sock.user.id.split(':')[0] : null;
            sessionStatus.set(cleanKey, { connected: true, qr: null, qrImage: null, phone: userPhone });
            console.log(`\n✅ [SESSION ${cleanKey}] WhatsApp Gateway është 100% i Lidhur e Aktiv (${userPhone ?? 'Connected'})!\n`);
        }
    });

    return sock;
}

// Start default session & shop_1 on boot
getOrStartSession('default');
getOrStartSession('shop_1');

// Health check status SPECIFIC per session key (shop_1 or barber_5)
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

// Get QR Code SPECIFIC per session key (shop_1 or barber_5)
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

// Send WhatsApp Message Endpoint (Supports Shop-level & Barber-level Routing)
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

        // 1. Try Barber-specific WhatsApp session if provided
        if (targetBarberKey) {
            sock = sessions.get(targetBarberKey);
            status = sessionStatus.get(targetBarberKey);

            if (!status?.connected) {
                sock = await getOrStartSession(targetBarberKey);
                status = sessionStatus.get(targetBarberKey);
            }
        }

        // 2. Try Shop-specific WhatsApp session if Barber session is not connected
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
