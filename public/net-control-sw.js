// RAYNET-OS Net Control Service Worker
// Intercepts net control API calls when offline and queues them for sync

const SW_VERSION   = 'v3';
const CACHE_NAME   = 'raynet-net-control-' + SW_VERSION;
const DB_NAME      = 'raynet-offline';
const DB_VERSION   = 1;
const QUEUE_STORE  = 'sync_queue';
const TOKEN_STORE  = 'offline_token';

// Exact POST routes we intercept for offline queuing
const OFFLINE_ROUTES = [
    '/admin/events/station-log',
    '/admin/events/net-status',
    '/admin/events/station-log/archive-and-clear',
    '/admin/events/station-log/clear',
];

// ── IndexedDB helpers ──────────────────────────────────────────────────────
function openDb() {
    return new Promise((resolve, reject) => {
        const req = indexedDB.open(DB_NAME, DB_VERSION);
        req.onupgradeneeded = e => {
            const db = e.target.result;
            if (!db.objectStoreNames.contains(QUEUE_STORE)) {
                const store = db.createObjectStore(QUEUE_STORE, {keyPath:'id', autoIncrement:true});
                store.createIndex('status', 'status', {unique:false});
            }
            if (!db.objectStoreNames.contains(TOKEN_STORE)) {
                db.createObjectStore(TOKEN_STORE, {keyPath:'key'});
            }
        };
        req.onsuccess = e => resolve(e.target.result);
        req.onerror   = e => reject(e.target.error);
    });
}

async function dbGet(storeName, key) {
    const db = await openDb();
    return new Promise((resolve, reject) => {
        const tx  = db.transaction(storeName, 'readonly');
        const req = tx.objectStore(storeName).get(key);
        req.onsuccess = e => resolve(e.target.result);
        req.onerror   = e => reject(e.target.error);
    });
}

async function dbPut(storeName, value) {
    const db = await openDb();
    return new Promise((resolve, reject) => {
        const tx  = db.transaction(storeName, 'readwrite');
        const req = tx.objectStore(storeName).put(value);
        req.onsuccess = e => resolve(e.target.result);
        req.onerror   = e => reject(e.target.error);
    });
}

async function dbGetAll(storeName) {
    const db = await openDb();
    return new Promise((resolve, reject) => {
        const tx  = db.transaction(storeName, 'readonly');
        const req = tx.objectStore(storeName).getAll();
        req.onsuccess = e => resolve(e.target.result);
        req.onerror   = e => reject(e.target.error);
    });
}

async function dbDelete(storeName, key) {
    const db = await openDb();
    return new Promise((resolve, reject) => {
        const tx  = db.transaction(storeName, 'readwrite');
        const req = tx.objectStore(storeName).delete(key);
        req.onsuccess = e => resolve(e.target.result);
        req.onerror   = e => reject(e.target.error);
    });
}

// ── Service Worker lifecycle ───────────────────────────────────────────────
self.addEventListener('install',  () => self.skipWaiting());
self.addEventListener('activate', e  => e.waitUntil(clients.claim()));

// Fetch interception removed — offline queuing now uses localStorage

async function handlePost(request) {
    // Try online first
    try {
        const response = await fetch(request.clone(), {signal: AbortSignal.timeout(5000)});
        if (response.ok) {
            notifyClients({type:'SYNC_STATUS', online:true, queued:0});
            return response;
        }
    } catch (err) {
        // Network failure — queue it
    }

    // Queue for later sync
    const body   = await request.text();
    const token  = await getStoredToken();
    const entry  = {
        url:       request.url,
        method:    'POST',
        headers:   Object.fromEntries(request.headers.entries()),
        body:      body,
        timestamp: Date.now(),
        status:    'pending',
        token:     token,
    };

    const id = await dbPut(QUEUE_STORE, entry);
    const queue = await dbGetAll(QUEUE_STORE);
    const pending = queue.filter(q => q.status === 'pending').length;

    notifyClients({type:'SYNC_STATUS', online:false, queued:pending, lastQueued: entry.url});

    // Register background sync if supported
    if (self.registration.sync) {
        await self.registration.sync.register('net-control-sync');
    }

    // Return optimistic success so UI doesn't break
    return new Response(JSON.stringify({success:true, queued:true, offline:true}), {
        status:  200,
        headers: {'Content-Type':'application/json'},
    });
}

// ── Background Sync ────────────────────────────────────────────────────────
self.addEventListener('sync', event => {
    if (event.tag === 'net-control-sync') {
        event.waitUntil(replayQueue());
    }
});



async function tryReplayIfOnline() {
    try {
        const test = await fetch('/net-status-json', {cache:'no-store', signal:AbortSignal.timeout(3000)});
        if (test.ok) await replayQueue();
    } catch(e) {}
}

async function replayQueue() {
    const queue = await dbGetAll(QUEUE_STORE);
    const pending = queue.filter(q => q.status === 'pending').sort((a,b) => a.timestamp - b.timestamp);
    if (!pending.length) return;

    const token = await getStoredToken();
    let synced = 0, failed = 0;

    for (const item of pending) {
        try {
            const headers = new Headers(item.headers);
            // Use offline token if available, strip CSRF (server exempts these)
            if (token) {
                headers.set('Authorization', 'Bearer ' + token);
                headers.delete('X-CSRF-TOKEN');
            }
            headers.set('X-Offline-Replay', '1');

            const response = await fetch(item.url, {
                method:  'POST',
                headers: headers,
                body:    item.body,
            });

            if (response.ok || response.status === 422) {
                // Check for server-side soft failure
                try {
                    const clone = response.clone();
                    const json  = await clone.json();
                    if (json && json.success === false && !json.queued) {
                        // Server rejected — mark failed so we don't lose it silently
                        await dbPut(QUEUE_STORE, {...item, status:'server_rejected', error: json.error || 'rejected'});
                        failed++;
                        notifyClients({type:'SYNC_ERROR', message: 'Entry rejected by server: ' + (json.error||'unknown')});
                        continue;
                    }
                } catch(e) {}
                await dbDelete(QUEUE_STORE, item.id);
                synced++;
            } else if (response.status === 401) {
                // Token expired — mark failed, notify user
                await dbPut(QUEUE_STORE, {...item, status:'auth_failed'});
                failed++;
                notifyClients({type:'AUTH_FAILED', message:'Offline token expired — please log in again to sync'});
                break;
            } else {
                await dbPut(QUEUE_STORE, {...item, status:'failed', error: response.status});
                failed++;
            }
        } catch (err) {
            // Still offline
            break;
        }
    }

    const remaining = (await dbGetAll(QUEUE_STORE)).filter(q => q.status === 'pending').length;
    notifyClients({type:'SYNC_COMPLETE', synced, failed, remaining});
}

async function getStoredToken() {
    try {
        const rec = await dbGet(TOKEN_STORE, 'offline_token');
        return rec ? rec.token : null;
    } catch(e) { return null; }
}

function notifyClients(message) {
    clients.matchAll({includeUncontrolled:true}).then(cls => {
        cls.forEach(c => c.postMessage(message));
    });
}

// Handle messages from page
self.addEventListener('message', async event => {
    if (!event.data) return;
    if (event.data.type === 'STORE_TOKEN') {
        await dbPut(TOKEN_STORE, {key:'offline_token', token: event.data.token, expires_at: event.data.expires_at});
    }
    if (event.data.type === 'SYNC_NOW') {
        await replayQueue();
    }
    if (event.data.type === 'CLEAR_QUEUE') {
        const all = await dbGetAll(QUEUE_STORE);
        for (const item of all) { await dbDelete(QUEUE_STORE, item.id); }
        notifyClients({type:'QUEUE_CLEARED', count: all.length});
    }
});
