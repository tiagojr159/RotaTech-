<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';
requireLogin();

if (empty($renderHomeAsIndex)) {
    header('Location: ' . appUrl());
    exit;
}

$user = currentUser();
$programacao = readJson('programacao.json');
$pontos = readJson('pontos.json');
$collected = count($user['figurinhas'] ?? []);
$totalAlbum = 24;

$pageTitle = 'Sao Joao 2026';
$pageEyebrow = 'festival';
$activeTab = 'home';
$adminShortcut = isAdminUser($user)
    ? '<a href="admin.php" class="icon-btn soft" aria-label="Administrador"><i class="fa-solid fa-shield-halved"></i></a>'
    : '';
$rightHtml = '
    ' . $adminShortcut . '
    <a href="perfil.php" class="avatar-link"><img src="' . sanitize($user['avatar']) . '" alt="Avatar" class="avatar-sm"></a>
';
include __DIR__ . '/includes/header.php';
?>
<section class="hero-card with-image">
    <img src="noite_arcoverde.jpeg" alt="Noite em Arcoverde">
    <div class="overlay"></div>
    <div class="hero-content">
        <div class="hero-topline">
            <div class="hero-brand">
                <img src="logomarca.png" alt="RotaTech Arcoverde" class="hero-logo">
            </div>
            <span class="chip light"><i class="fa-regular fa-sun"></i> 22C</span>
        </div>
        <h2>Hoje no Sao Joao</h2>
        <p>24 de Junho - Noite de Sao Joao</p>
    </div>
</section>

<section class="quick-grid">
    <a href="programacao.php" class="quick-card quick-card-primary"><i class="fa-regular fa-calendar"></i><span>Programacao</span></a>
    <a href="restaurantes.php" class="quick-card"><i class="fa-solid fa-utensils"></i><span>Restaurantes</span></a>
    <a href="hospedagem.php" class="quick-card"><i class="fa-solid fa-bed"></i><span>Hospedagem</span></a>
    <a href="album.php" class="quick-card"><i class="fa-regular fa-map"></i><span>Album</span></a>
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
        <h4>Album de Figurinhas</h4>
        <strong><?= $collected; ?> / <?= $totalAlbum; ?> Colecionadas</strong>
    </div>
    <div class="progress"><span style="width: <?= min(100, ($collected / 6) * 100); ?>%"></span></div>
    <p>Visite mais 2 pontos turisticos para completar seu album!</p>
</article>

<section class="section-head"><h3>Pontos Estrategicos</h3></section>
<div class="stack-list">
    <?php foreach (array_slice($pontos, 0, 3) as $ponto): ?>
        <article class="point-card">
            <span class="point-icon" style="background: <?= sanitize($ponto['cor']); ?>22; color: <?= sanitize($ponto['cor']); ?>">
                <i class="fa-solid <?= sanitize($ponto['icone']); ?>"></i>
            </span>
            <div>
                <h4><?= sanitize($ponto['nome']); ?></h4>
                <p><?= sanitize($ponto['local']); ?> - <?= sanitize($ponto['distancia']); ?></p>
            </div>
            <i class="fa-solid fa-angle-right"></i>
        </article>
    <?php endforeach; ?>
</div>

<article class="card card-soft ai-tip">
    <h4><i class="fa-solid fa-wand-magic-sparkles"></i> Sugestao Inteligente</h4>
    <p>Como voce esta perto do Polo Gastronomico e o show comeca em 40 minutos, recomendamos jantar agora no Casarao do Forro.</p>
</article>

<a href="grupos.php" class="fab-main" aria-label="Ir para grupos"><i class="fa-solid fa-plus"></i></a>

<?php include __DIR__ . '/includes/bottom-nav.php'; ?>
</main>
</div>
</div>
</body>
</html>
