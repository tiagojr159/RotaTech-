<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

if (currentUser()) {
    header('Location: home.php');
    exit;
}

$showTopBar = false;
$pageTitle = 'RotaTech Arcoverde';
$pageClass = 'splash-screen';
include __DIR__ . '/includes/header.php';
?>
<section class="splash-wrap">
    <div class="logo-badge logo-lg">
        <img src="assets/img/logo-rotatech.svg" alt="RotaTech Arcoverde">
    </div>
    <h2 class="splash-title">ROTATECH<br>ARCOVERDE</h2>
    <p class="splash-copy">A tecnologia encontra a tradição no melhor São João do mundo.</p>
    <div class="hero-preview">
        <img src="assets/img/hero-fogueira.svg" alt="Fogueira de São João">
    </div>
    <a href="login.php" class="btn btn-primary btn-xl">
        <i class="fa-solid fa-store"></i>
        Entrar na festa
    </a>
    <a href="login.php" class="link-inline">Entrar com conta <i class="fa-solid fa-arrow-right"></i></a>
    <p class="footer-mini">ROTATECH • ARCOVERDE 2026</p>
</section>
</main>
</div>
</div>
</body>
</html>

