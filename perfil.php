<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';
requireLogin();

$user = currentUser();
$figurinhas = readJson('figurinhas.json');
$grupos = readJson('grupos.json');
$myGroups = array_values(array_filter($grupos, fn($g) => in_array((int) $user['id'], $g['membros'] ?? [], true)));
$collected = $user['figurinhas'] ?? [];

$activeTab = 'perfil';
$pageTitle = 'Perfil';
$rightHtml = '<button class="icon-btn" data-open-modal="modal-edit-profile"><i class="fa-solid fa-gear"></i></button>';
include __DIR__ . '/includes/header.php';
?>
<section class="profile-top">
    <div class="avatar-xl-wrap">
        <img src="<?= sanitize($user['avatar']); ?>" alt="<?= sanitize($user['nome']); ?>" class="avatar-xl">
        <span class="lvl-badge">LVL <?= (int) $user['nivel']; ?></span>
    </div>
    <h2><?= sanitize($user['nome']); ?></h2>
    <span class="title-badge"><?= sanitize($user['titulo']); ?></span>
</section>

<?php if (isAdminUser($user)): ?>
    <a href="admin.php" class="btn btn-light btn-xl"><i class="fa-solid fa-shield-halved"></i> Ir para o administrador</a>
<?php endif; ?>

<section class="section-head">
    <h3>Album de Figurinhas</h3>
    <span><?= count($collected); ?> / 24</span>
</section>
<div class="mini-sticker-grid">
    <?php foreach (array_slice($figurinhas, 0, 8) as $st): ?>
        <div class="mini-sticker <?= in_array((int) $st['id'], $collected, true) ? 'on' : 'off'; ?>">
            <?php if (in_array((int) $st['id'], $collected, true)): ?>
                <img src="<?= sanitize($st['imagem']); ?>" alt="<?= sanitize($st['titulo']); ?>">
            <?php else: ?>
                <i class="fa-solid fa-lock"></i>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
</div>

<section class="section-head"><h3>Meus Grupos</h3></section>
<div class="stack-list">
    <?php foreach ($myGroups as $g): ?>
        <a class="card group-inline" href="detalhes-grupo.php?id=<?= (int) $g['id']; ?>">
            <span class="icon-square"><i class="fa-solid fa-users"></i></span>
            <div>
                <h4><?= sanitize($g['nome']); ?></h4>
                <p><?= count($g['membros'] ?? []); ?> participantes</p>
            </div>
            <i class="fa-solid fa-angle-right"></i>
        </a>
    <?php endforeach; ?>
</div>

<div class="stats-grid">
    <article class="card stat-box"><strong>15</strong><span>SHOWS VISTOS</span></article>
    <article class="card stat-box"><strong>3</strong><span>ROTEIROS</span></article>
</div>

<a href="logout.php" class="btn btn-light btn-xl logout-btn"><i class="fa-solid fa-arrow-right-from-bracket"></i> Sair da conta</a>

<div class="modal hidden" id="modal-edit-profile">
    <div class="modal-backdrop" data-close-modal></div>
    <div class="modal-content">
        <h3>Editar Perfil</h3>
        <div class="theme-switcher" data-theme-switcher>
            <button type="button" class="theme-option active" data-theme-option="normal">
                <i class="fa-regular fa-sun"></i>
                <span>Normal</span>
            </button>
            <button type="button" class="theme-option" data-theme-option="dark">
                <i class="fa-regular fa-moon"></i>
                <span>Dark</span>
            </button>
        </div>
        <div class="theme-switcher" data-voice-switcher>
            <button type="button" class="theme-option active" data-voice-option="on">
                <i class="fa-solid fa-volume-high"></i>
                <span>Voz ligada</span>
            </button>
            <button type="button" class="theme-option" data-voice-option="off">
                <i class="fa-solid fa-volume-xmark"></i>
                <span>Voz desligada</span>
            </button>
        </div>
        <form id="form-edit-profile" class="stacked-form profile-edit-form">
            <div class="profile-avatar-editor">
                <img src="<?= sanitize($user['avatar']); ?>" alt="<?= sanitize($user['nome']); ?>" class="profile-avatar-preview">
                <label class="btn btn-light" for="avatar">Escolher foto</label>
                <input type="file" id="avatar" name="avatar" accept=".jpg,.jpeg,.png,.webp">
            </div>
            <label>Nome</label>
            <input type="text" name="nome" value="<?= sanitize($user['nome']); ?>" required>
            <label>Usuario</label>
            <input type="text" name="usuario" value="<?= sanitize($user['usuario']); ?>" required>
            <label>Titulo</label>
            <input type="text" name="titulo" value="<?= sanitize($user['titulo']); ?>" required>
            <button type="submit" class="btn btn-primary btn-xl">Salvar alteracoes</button>
        </form>
    </div>
</div>

<?php include __DIR__ . '/includes/bottom-nav.php'; ?>
</main>
</div>
</div>
</body>
</html>
