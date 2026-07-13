<!doctype html>
<html lang="<?= htmlspecialchars($language['code'] ?? 'en') ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#000000">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="mobile-web-app-capable" content="yes">
    <link rel="manifest" href="/manifest.json">
    <title><?= isset($page_title) ? htmlspecialchars($page_title) . ' — ' : '' ?>Wavelog</title>

    <link rel="stylesheet" href="<?php echo $this->paths->cache_buster('/assets/css/default/bootstrap.min.css'); ?>">
    <link rel="stylesheet" href="<?php echo $this->paths->cache_buster('/assets/fontawesome/css/all.min.css'); ?>">
    <link rel="stylesheet" href="<?php echo $this->paths->cache_buster('/assets/mobile/css/app.css'); ?>">
</head>
<body>

<main class="wl-main">
    <?php $this->load->view($content_view, get_defined_vars()); ?>
</main>

<nav class="wl-bottom-nav" aria-label="<?= __('Main navigation') ?>">
    <a href="/mobile/dashboard" class="wl-tab<?= ($this->uri->segment(2) === 'dashboard' || $this->uri->segment(2) === '') ? ' active' : '' ?>">
        <i class="fas fa-home" aria-hidden="true"></i>
        <span>Home</span>
    </a>
    <a href="/mobile/log" class="wl-tab<?= ($this->uri->segment(2) === 'log') ? ' active' : '' ?>">
        <i class="fas fa-pencil-alt" aria-hidden="true"></i>
        <span>Log</span>
    </a>
    <a href="#" class="wl-tab wl-tab--disabled" aria-disabled="true" tabindex="-1">
        <i class="fas fa-list" aria-hidden="true"></i>
        <span>Contacts</span>
    </a>
    <a href="/mobile/modeswitch/desktop" class="wl-tab">
        <i class="fas fa-desktop" aria-hidden="true"></i>
        <span>Desktop</span>
    </a>
</nav>

<script src="<?php echo $this->paths->cache_buster('/assets/js/bootstrap.bundle.min.js'); ?>"></script>
<script>
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('/service-worker.js');
    }
</script>
</body>
</html>
