<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

if (currentUser()) {
    $renderHomeAsIndex = true;
    require __DIR__ . '/home.php';
    exit;
}

$showTopBar = false;
$pageTitle = 'RotaTech Arcoverde';
$pageClass = 'splash-screen';
include __DIR__ . '/includes/header.php';
?>
<section class="splash-wrap">
    <div class="splash-hero">
        <img src="noite_arcoverde.jpeg" alt="Noite em Arcoverde" class="splash-hero-bg" loading="eager" fetchpriority="high">
        <div class="splash-hero-overlay"></div>
        <div class="splash-badge">Sao Joao 2026</div>
        <div class="logo-badge logo-lg splash-logo">
            <img src="logomarca.png" alt="RotaTech Arcoverde">
        </div>
        <div class="splash-hero-copy">
            <p class="splash-kicker">Arcoverde • Pernambuco</p>
            <h2 class="splash-title">RotaTech<br>Arcoverde</h2>
            <p class="splash-copy">A tecnologia encontra a tradicao no melhor Sao Joao do mundo, com roteiro, album e experiencias em tempo real.</p>
        </div>
        <div class="splash-hero-footer">
            <span><i class="fa-solid fa-wifi"></i> app em tempo real</span>
            <span><i class="fa-solid fa-camera-retro"></i> filtros e album</span>
        </div>
    </div>

    <div class="splash-actions">
        <a href="login.php" class="btn btn-primary btn-xl">
            <i class="fa-solid fa-store"></i>
            Entrar na festa
        </a>
        <a href="login.php" class="link-inline">Ja tenho conta <i class="fa-solid fa-arrow-right"></i></a>
    </div>

    <p class="footer-mini">ROTATECH • ARCOVERDE 2026</p>
</section>
</main>
</div>
</div>
</body>
</html>
