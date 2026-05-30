<?php
declare(strict_types=1);

if (!defined('APP_INIT')) {
    http_response_code(403);
    exit('Acesso negado.');
}

$activeTab = $activeTab ?? '';
?>
<nav class="bottom-nav" data-bottom-nav>
    <a href="<?= sanitize(appUrl()); ?>" class="nav-item <?= $activeTab === 'home' ? 'active' : ''; ?>" data-tab="home">
        <i class="fa-regular fa-house"></i>
        <span>Início</span>
    </a>
    <a href="programacao.php" class="nav-item <?= $activeTab === 'explorar' ? 'active' : ''; ?>" data-tab="explorar">
        <i class="fa-regular fa-compass"></i>
        <span>Explorar</span>
    </a>
    <a href="album.php" class="nav-item <?= $activeTab === 'album' ? 'active' : ''; ?>" data-tab="album">
        <i class="fa-regular fa-map"></i>
        <span>Álbum</span>
    </a>
    <a href="roteiro.php" class="nav-item <?= $activeTab === 'roteiro' ? 'active' : ''; ?>" data-tab="roteiro">
        <i class="fa-solid fa-map-location-dot"></i>
        <span>Roteiro</span>
    </a>
    <a href="perfil.php" class="nav-item <?= $activeTab === 'perfil' ? 'active' : ''; ?>" data-tab="perfil">
        <i class="fa-regular fa-user"></i>
        <span>Perfil</span>
    </a>
</nav>
