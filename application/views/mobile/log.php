<div class="wl-log">
    <h1 class="h5 wl-section-title"><?= __('Log QSO') ?></h1>

    <!-- Connection / sync status banner (toggled by offline-log.js) -->
    <div id="wl-status" class="wl-status" hidden></div>

    <form id="wl-qso-form" class="wl-qso-form" autocomplete="off" novalidate>
        <div class="mb-2">
            <label class="form-label" for="qso-call"><?= __('Callsign') ?></label>
            <input type="text" class="form-control form-control-lg text-uppercase" id="qso-call" name="callsign"
                   inputmode="text" autocapitalize="characters" spellcheck="false" required>
        </div>

        <div class="row g-2 mb-2">
            <div class="col-6">
                <label class="form-label" for="qso-band"><?= __('Band') ?></label>
                <select class="form-select" id="qso-band" name="band" required>
                    <?php foreach ($bands as $band): ?>
                        <option value="<?= htmlspecialchars($band) ?>"<?= $band === '20m' ? ' selected' : '' ?>><?= htmlspecialchars($band) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-6">
                <label class="form-label" for="qso-mode"><?= __('Mode') ?></label>
                <select class="form-select" id="qso-mode" name="mode" required>
                    <?php foreach ($modes as $m): $val = $m->submode ?: $m->mode; ?>
                        <option value="<?= htmlspecialchars($val) ?>"<?= $val === 'SSB' ? ' selected' : '' ?>><?= htmlspecialchars($val) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="row g-2 mb-2">
            <div class="col-4">
                <label class="form-label" for="qso-rsts"><?= __('RST Sent') ?></label>
                <input type="text" class="form-control" id="qso-rsts" name="rst_sent" inputmode="numeric" value="59">
            </div>
            <div class="col-4">
                <label class="form-label" for="qso-rstr"><?= __('RST Rcvd') ?></label>
                <input type="text" class="form-control" id="qso-rstr" name="rst_rcvd" inputmode="numeric" value="59">
            </div>
            <div class="col-4">
                <label class="form-label" for="qso-freq"><?= __('Frequency') ?></label>
                <input type="text" class="form-control" id="qso-freq" name="frequency" inputmode="decimal" placeholder="MHz">
            </div>
        </div>

        <div class="row g-2 mb-2">
            <div class="col-6">
                <label class="form-label" for="qso-grid"><?= __('Locator') ?></label>
                <input type="text" class="form-control text-uppercase" id="qso-grid" name="gridsquare" spellcheck="false">
            </div>
            <div class="col-6">
                <label class="form-label" for="qso-name"><?= __('Name') ?></label>
                <input type="text" class="form-control" id="qso-name" name="name">
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label" for="qso-comment"><?= __('Comment') ?></label>
            <input type="text" class="form-control" id="qso-comment" name="comment">
        </div>

        <button type="submit" class="btn btn-primary btn-lg w-100"><?= __('Log QSO') ?></button>
    </form>

    <!-- Queue of QSOs still waiting to reach the server (rendered by offline-log.js) -->
    <section class="wl-queue mt-4">
        <h2 class="h6 wl-section-title">
            <?= __('Pending') ?> <span id="wl-pending-count" class="badge bg-secondary">0</span>
        </h2>
        <ul id="wl-pending-list" class="wl-pending-list list-unstyled mb-0"></ul>
    </section>
</div>

<script src="<?php echo $this->paths->cache_buster('/assets/js/mobile/offline-log.js'); ?>"></script>
<script>
(function () {
    'use strict';

    var form         = document.getElementById('wl-qso-form');
    var statusEl     = document.getElementById('wl-status');
    var listEl       = document.getElementById('wl-pending-list');
    var countEl      = document.getElementById('wl-pending-count');
    var callEl       = document.getElementById('qso-call');
    var modeEl       = document.getElementById('qso-mode');
    var rstSentEl    = document.getElementById('qso-rsts');
    var rstRcvdEl    = document.getElementById('qso-rstr');

    // RST default depends on mode: 59 for phone, 599 for CW / digital.
    var PHONE_MODES = ['SSB', 'USB', 'LSB', 'AM', 'FM', 'DV'];
    function defaultRst() {
        var mode = modeEl.value.toUpperCase();
        return PHONE_MODES.indexOf(mode) !== -1 ? '59' : '599';
    }
    function applyRstDefault() {
        var d = defaultRst();
        rstSentEl.value = d;
        rstRcvdEl.value = d;
    }
    modeEl.addEventListener('change', applyRstDefault);
    applyRstDefault();

    function showStatus(msg, kind) {
        if (!msg) { statusEl.hidden = true; return; }
        statusEl.hidden = false;
        statusEl.textContent = msg;
        statusEl.className = 'wl-status wl-status--' + (kind || 'info');
    }

    function render(records) {
        var pending = records.filter(function (r) { return r.state !== 'synced'; });
        countEl.textContent = pending.length;
        listEl.innerHTML = '';
        pending.forEach(function (r) {
            var li = document.createElement('li');
            li.className = 'wl-pending-item' + (r.state === 'error' ? ' wl-pending-item--error' : '');
            var when = (r.date || '') + ' ' + (r.time || '');
            li.innerHTML =
                '<span class="wl-pending-call">' + (r.callsign || '?') + '</span>' +
                '<span class="wl-pending-meta">' + (r.band || '') + ' ' + (r.mode || '') + '</span>' +
                '<span class="wl-pending-state">' +
                    (r.state === 'error' ? '<i class="fas fa-triangle-exclamation"></i>'
                                         : '<i class="fas fa-clock"></i>') +
                '</span>';
            listEl.appendChild(li);
        });
    }

    // Wire the queue's callbacks.
    WLOfflineLog.onChange(render);
    WLOfflineLog.onStatus(showStatus);

    WLOfflineLog.init().then(function () {
        return WLOfflineLog.trySync();
    });

    form.addEventListener('submit', function (e) {
        e.preventDefault();

        var now  = new Date();
        var pad  = function (n) { return (n < 10 ? '0' : '') + n; };
        var el   = form.elements; // named lookup avoids HTMLFormElement props (e.g. form.name)
        var qso  = {
            callsign:   callEl.value.trim().toUpperCase(),
            band:       el['band'].value,
            mode:       el['mode'].value,
            rst_sent:   rstSentEl.value.trim(),
            rst_rcvd:   rstRcvdEl.value.trim(),
            frequency:  el['frequency'].value.trim(),
            gridsquare: el['gridsquare'].value.trim().toUpperCase(),
            name:       el['name'].value.trim(),
            comment:    el['comment'].value.trim(),
            // UTC date/time — the server parses date as Y-m-d and time as H:i
            date: now.getUTCFullYear() + '-' + pad(now.getUTCMonth() + 1) + '-' + pad(now.getUTCDate()),
            time: pad(now.getUTCHours()) + ':' + pad(now.getUTCMinutes())
        };

        if (!qso.callsign) { callEl.focus(); return; }

        WLOfflineLog.saveQso(qso).then(function () {
            // Reset only the per-QSO fields; keep band/mode/RST for fast serial logging
            callEl.value = '';
            el['gridsquare'].value = '';
            el['name'].value = '';
            el['comment'].value = '';
            callEl.focus();
        });
    });
})();
</script>
