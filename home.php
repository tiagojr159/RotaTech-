<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';
requireLogin();

$user = currentUser();
$programacao = readJson('programacao.json');
$pontos = readJson('pontos.json');
$notificacoes = readJson('notificacoes.json');
$unread = count(array_filter($notificacoes, fn($n) => empty($n['lida'])));
$collected = count($user['figurinhas'] ?? []);
$totalAlbum = 24;

$pageTitle = 'São João 2026';
$pageEyebrow = 'festival';
$activeTab = 'home';
$rightHtml = '
    <a href="#" class="icon-btn"><i class="fa-regular fa-bell"></i><span class="dot"></span></a>
    <a href="perfil.php" class="avatar-link"><img src="' . sanitize($user['avatar']) . '" alt="Avatar" class="avatar-sm"></a>
';
include __DIR__ . '/includes/header.php';
?>
<section class="hero-card with-image">
    <img src="assets/img/hero-fogueira.svg" alt="Fogueira">
    <div class="overlay"></div>
    <div class="hero-content">
        <p class="hero-location">ARCOVERDE, PE</p>
        <span class="chip light"><i class="fa-regular fa-sun"></i> 22°C</span>
        <h2>Hoje no São João</h2>
        <p>24 de Junho • Noite de São João</p>
    </div>
</section>

<section class="quick-grid">
    <a href="programacao.php" class="quick-card quick-card-primary"><i class="fa-regular fa-calendar"></i><span>Programação</span></a>
    <a href="restaurantes.php" class="quick-card"><i class="fa-solid fa-utensils"></i><span>Restaurantes</span></a>
    <a href="album.php" class="quick-card"><i class="fa-regular fa-map"></i><span>Álbum</span></a>
    <a href="roteiro.php" class="quick-card"><i class="fa-regular fa-map"></i><span>Roteiro</span></a>
</section>

<section class="section-head">
    <h3>Destaques</h3>
    <a href="programacao.php">Ver todos</a>
</section>
<div class="horizontal-cards">
    <?php foreach (array_slice($programacao, 0, 3) as $item): ?>
        <article class="mini-spot">
            <img src="<?= sanitize($item['imagem']); ?>" alt="<?= sanitize($item['artista']); ?>">
            <span class="time-pill"><?= sanitize($item['horario']); ?></span>
            <div class="mini-body">
                <h4><?= sanitize($item['artista']); ?></h4>
                <p><?= sanitize($item['palco']); ?></p>
            </div>
        </article>
    <?php endforeach; ?>
</div>

<article class="card card-soft progress-highlight">
    <div class="row-between">
        <h4>Álbum de Figurinhas</h4>
        <strong><?= $collected; ?> / <?= $totalAlbum; ?> Colecionadas</strong>
    </div>
    <div class="progress"><span style="width: <?= min(100, ($collected / 6) * 100); ?>%"></span></div>
    <p>Visite mais 2 pontos turísticos para completar seu álbum!</p>
</article>

<section class="section-head"><h3>Pontos Estratégicos</h3></section>
<div class="stack-list">
    <?php foreach (array_slice($pontos, 0, 3) as $ponto): ?>
        <article class="point-card">
            <span class="point-icon" style="background: <?= sanitize($ponto['cor']); ?>22; color: <?= sanitize($ponto['cor']); ?>">
                <i class="fa-solid <?= sanitize($ponto['icone']); ?>"></i>
            </span>
            <div>
                <h4><?= sanitize($ponto['nome']); ?></h4>
                <p><?= sanitize($ponto['local']); ?> • <?= sanitize($ponto['distancia']); ?></p>
            </div>
            <i class="fa-solid fa-angle-right"></i>
        </article>
    <?php endforeach; ?>
</div>

<article class="card card-soft ai-tip">
    <h4><i class="fa-solid fa-wand-magic-sparkles"></i> Sugestão Inteligente</h4>
    <p>Como você está perto do Polo Gastronômico e o show começa em 40 minutos, recomendamos jantar agora no Casarão do Forró.</p>
    <div class="alert-mini"><i class="fa-regular fa-bell"></i> <?= $unread; ?> alertas úteis ativos</div>
</article>

<a href="grupos.php" class="fab-main" aria-label="Ir para grupos"><i class="fa-solid fa-plus"></i></a>

<?php include __DIR__ . '/includes/bottom-nav.php'; ?>
</main>
</div>
</div>
</body>
</html>

