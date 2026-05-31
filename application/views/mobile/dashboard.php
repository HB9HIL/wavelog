<div class="wl-dashboard">
    <div class="wl-greeting">
        <h1 class="h4"><?= __('Welcome') ?>, <strong><?= htmlspecialchars($callsign) ?></strong></h1>
    </div>

    <div class="wl-stat-card">
        <div class="wl-stat-card__value"><?= number_format((int)$total_qsos) ?></div>
        <div class="wl-stat-card__label"><?= __('Total QSOs') ?></div>
    </div>

    <section class="wl-recent-qsos">
        <h2 class="h6 wl-section-title"><?= __('Recent QSOs') ?></h2>

        <?php if ($recent_qsos && $recent_qsos->num_rows() > 0): ?>
        <div class="table-responsive">
            <table class="table table-sm table-striped wl-qso-table">
                <thead>
                    <tr>
                        <th><?= __('Callsign') ?></th>
                        <th><?= __('Date / Time') ?></th>
                        <th><?= __('Band') ?></th>
                        <th><?= __('Mode') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_qsos->result() as $qso): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($qso->COL_CALL) ?></strong></td>
                        <td class="text-nowrap"><?= htmlspecialchars(substr($qso->COL_TIME_ON, 0, 16)) ?></td>
                        <td><?= htmlspecialchars($qso->COL_BAND) ?></td>
                        <td><?= htmlspecialchars($qso->COL_MODE) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <p class="text-muted"><?= __('No QSOs logged yet.') ?></p>
        <?php endif; ?>
    </section>
</div>
