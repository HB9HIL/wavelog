/* Wavelog Mobile — offline QSO queue
 *
 * A deliberately small IndexedDB-backed queue for logging QSOs without a
 * connection. Inspired by the contesting engine's DataStore
 * (assets/js/sections/contesting/contest_engine/core/data-store.js) but stripped
 * to the single-user logging case: one object store, a per-record `state`, and a
 * batch POST to /mobile/log/sync on reconnect. No delta pull, no WebSocket.
 *
 * Flow:
 *   saveQso()  -> write record with state 'pending', notify UI, trigger trySync()
 *   trySync()  -> POST all pending records in one batch; on ack mark 'synced'
 *   offline / session expired -> records stay 'pending', retried on next trigger
 *
 * Exposes a single global: window.WLOfflineLog
 */
window.WLOfflineLog = (function () {
    'use strict';

    var DB_NAME    = 'wavelog_mobile';
    var DB_VERSION = 1;
    var STORE      = 'qso_queue';
    var SYNC_URL   = '/mobile/log/sync';

    var db          = null;
    var records     = [];          // in-memory mirror of the store
    var writeQueue  = Promise.resolve(); // serialises IDB writes so they never overlap
    var syncing     = false;
    var changeCbs   = [];
    var statusCbs   = [];

    // ── IndexedDB plumbing ────────────────────────────────────────
    function openDB() {
        return new Promise(function (resolve, reject) {
            var req = indexedDB.open(DB_NAME, DB_VERSION);
            req.onupgradeneeded = function (e) {
                var d = e.target.result;
                if (!d.objectStoreNames.contains(STORE)) {
                    d.createObjectStore(STORE, { keyPath: 'tmp_id' });
                }
            };
            req.onsuccess = function () { resolve(req.result); };
            req.onerror   = function () { reject(req.error); };
        });
    }

    function tx(mode) {
        return db.transaction(STORE, mode).objectStore(STORE);
    }

    // Fire-and-forget write, serialised through writeQueue.
    function put(record) {
        writeQueue = writeQueue.then(function () {
            return new Promise(function (resolve) {
                var r = tx('readwrite').put(record);
                r.onsuccess = resolve;
                r.onerror   = function () { resolve(); }; // never break the chain
            });
        });
        return writeQueue;
    }

    function del(tmpId) {
        writeQueue = writeQueue.then(function () {
            return new Promise(function (resolve) {
                var r = tx('readwrite').delete(tmpId);
                r.onsuccess = resolve;
                r.onerror   = function () { resolve(); };
            });
        });
        return writeQueue;
    }

    function loadAll() {
        return new Promise(function (resolve) {
            var r = tx('readonly').getAll();
            r.onsuccess = function () { records = r.result || []; resolve(records); };
            r.onerror   = function () { records = []; resolve(records); };
        });
    }

    // ── Notifications ─────────────────────────────────────────────
    function notifyChange() { changeCbs.forEach(function (cb) { cb(records.slice()); }); }
    function notifyStatus(msg, kind) { statusCbs.forEach(function (cb) { cb(msg, kind); }); }

    // ── Public API ────────────────────────────────────────────────
    function init() {
        return openDB().then(function (d) {
            db = d;
            return loadAll();
        }).then(function () {
            notifyChange();
            // Retry pending whenever we regain connectivity.
            window.addEventListener('online', trySync);
        });
    }

    function saveQso(data) {
        var record = Object.assign({}, data, {
            tmp_id:  'tmp_' + (self.crypto && crypto.randomUUID ? crypto.randomUUID()
                                                                : Date.now() + '_' + Math.random().toString(36).slice(2)),
            state:   'pending',
            created: Date.now()
        });
        records.push(record);
        notifyChange();
        return put(record).then(function () {
            trySync();
            return record;
        });
    }

    function getPending() {
        return records.filter(function (r) { return r.state !== 'synced'; });
    }

    // Acknowledged QSOs are dropped from the queue — the server is now the record of truth.
    function markSynced(tmpId) {
        records = records.filter(function (x) { return x.tmp_id !== tmpId; });
        return del(tmpId);
    }

    function markError(tmpId, msg) {
        var r = records.find(function (x) { return x.tmp_id === tmpId; });
        if (!r) return Promise.resolve();
        r.state     = 'error';
        r.error_msg = msg;
        return put(r);
    }

    function trySync() {
        if (syncing || !navigator.onLine) return Promise.resolve();

        var pending = records.filter(function (r) { return r.state === 'pending' || r.state === 'error'; });
        if (pending.length === 0) { notifyStatus(null); return Promise.resolve(); }

        syncing = true;
        notifyStatus(pending.length + ' QSO(s) syncing…', 'info');

        return fetch(SYNC_URL, {
            method:      'POST',
            credentials: 'same-origin',
            headers:     { 'Content-Type': 'application/json' },
            body:        JSON.stringify({ qsos: pending })
        }).then(function (resp) {
            // A redirect to the HTML login page means the session expired:
            // keep everything pending and prompt a re-login. Never lose data.
            var ctype = resp.headers.get('Content-Type') || '';
            if (resp.redirected || ctype.indexOf('application/json') === -1 || !resp.ok) {
                throw { session: resp.redirected || ctype.indexOf('application/json') === -1 };
            }
            return resp.json();
        }).then(function (json) {
            var saved  = (json && json.saved)  || [];
            var errors = (json && json.errors) || [];
            return Promise.all(
                saved.map(function (s) { return markSynced(s.tmp_id); })
                     .concat(errors.map(function (e) { return markError(e.tmp_id, e.error); }))
            ).then(function () {
                notifyChange();
                var left = getPending().length;
                if (errors.length) {
                    notifyStatus(errors.length + ' QSO(s) rejected — check details', 'error');
                } else {
                    notifyStatus(left === 0 ? null : left + ' QSO(s) pending', left === 0 ? null : 'info');
                }
            });
        }).catch(function (err) {
            // Offline or transient failure: records stay pending for the next trigger.
            if (err && err.session) {
                notifyStatus('Session expired — please log in again to sync', 'error');
            } else {
                notifyStatus(getPending().length + ' QSO(s) pending (offline)', 'warn');
            }
        }).then(function () {
            syncing = false;
        });
    }

    function onChange(cb) { changeCbs.push(cb); }
    function onStatus(cb) { statusCbs.push(cb); }

    return {
        init:       init,
        saveQso:    saveQso,
        trySync:    trySync,
        getPending: getPending,
        onChange:   onChange,
        onStatus:   onStatus
    };
})();
