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
        <div class="splash-hero-overlay"></div>
        <div class="splash-top">
            <div class="logo-badge logo-lg splash-logo">
                <img src="logomarca.png" alt="RotaTech Arcoverde">
            </div>
            <span class="splash-badge">Sao Joao 2026</span>
        </div>
        <div class="splash-hero-copy">
            <p class="splash-kicker">Arcoverde • Pernambuco</p>
            <h2 class="splash-title">Sao Joao<br>2026</h2>
            <p class="splash-copy">Roteiros, album e experiencias em tempo real no melhor Sao Joao de Arcoverde.</p>
            <div class="splash-actions">
                <a href="login.php" class="btn btn-primary btn-xl">
                    <i class="fa-solid fa-store"></i>
                    Entrar na festa
                </a>
                <a href="login.php" class="link-inline">Entrar com conta <i class="fa-solid fa-arrow-right"></i></a>
            </div>
        </div>
    </div>
</section>
</main>
</div>
</div>
</body>
</html>
