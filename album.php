<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';
requireLogin();

$user = currentUser();
$figurinhas = readJson('figurinhas.json');
$albumFotos = array_reverse(array_slice(readJson('album_fotos.json'), -50));
$minicolecaoTotal = 6;
$coletadas = count(array_intersect($user['figurinhas'] ?? [], array_column(array_slice($figurinhas, 0, $minicolecaoTotal), 'id')));
$activeTab = 'album';
$pageTitle = 'Album';
$pageEyebrow = '';
$rightHtml = '<a href="perfil.php" class="icon-btn soft"><i class="fa-regular fa-user"></i></a>';
include __DIR__ . '/includes/header.php';
?>
<article class="card unlock-banner">
    <small>CONQUISTA DESBLOQUEADA</small>
    <h3>Eu vivi o Sao Joao de Arcoverde</h3>
</article>

<section class="sticker-grid" data-sticker-grid>
    <form class="sticker-card camera-card upload-photo-card" id="form-upload-album-photo" enctype="multipart/form-data">
        <input type="hidden" name="action" value="upload_album_photo">
        <label for="album-photo-input" class="album-upload-label">
            <span class="camera-icon"><i class="fa-solid fa-image"></i></span>
            <strong>Enviar foto</strong>
            <small>Escolha uma imagem da galeria</small>
        </label>
        <input type="file" id="album-photo-input" name="album_photo" accept=".jpg,.jpeg,.png,.webp,image/*">
    </form>
    <?php foreach ($figurinhas as $figurinha): ?>
        <?php $locked = !in_array((int) $figurinha['id'], $user['figurinhas'] ?? [], true); ?>
        <button class="sticker-card <?= $locked ? 'locked' : 'unlocked'; ?>" data-sticker-id="<?= (int) $figurinha['id']; ?>">
            <img src="<?= sanitize((string) $figurinha['imagem']); ?>" alt="<?= sanitize((string) $figurinha['titulo']); ?>">
            <?php if ($locked): ?><span class="lock"><i class="fa-solid fa-lock"></i></span><?php endif; ?>
        </button>
    <?php endforeach; ?>
</section>

<section class="album-progress">
    <h4>SEU PROGRESSO</h4>
    <div class="progress"><span style="width: <?= min(100, ($coletadas / $minicolecaoTotal) * 100); ?>%"></span></div>
    <p><strong><?= $coletadas; ?></strong> de <?= $minicolecaoTotal; ?> figurinhas coletadas</p>
</section>

<section class="section-head">
    <h3>Fotos da Galera</h3>
    <span><?= count($albumFotos); ?> / 50</span>
</section>
<section class="album-feed-grid">
    <?php foreach ($albumFotos as $foto): ?>
        <article class="album-photo-card">
            <button
                type="button"
                class="album-photo-trigger"
                data-open-album-photo
                data-album-photo-src="<?= sanitize((string) $foto['imagem']); ?>"
                data-album-photo-alt="<?= sanitize((string) ($foto['user_name'] ?? 'Foto enviada')); ?>"
            >
                <img src="<?= sanitize((string) $foto['imagem']); ?>" alt="<?= sanitize((string) ($foto['user_name'] ?? 'Foto enviada')); ?>">
            </button>
            <div class="album-photo-meta">
                <strong><?= sanitize((string) ($foto['user_name'] ?? 'Visitante')); ?></strong>
                <small><?= sanitize((string) ($foto['created_at'] ?? 'Agora')); ?></small>
            </div>
        </article>
    <?php endforeach; ?>
</section>

<div class="modal hidden" id="modal-album-photo">
    <div class="modal-backdrop" data-close-modal></div>
    <div class="modal-content album-photo-modal-content">
        <div class="album-photo-modal-head">
            <h3>Visualizar foto</h3>
            <button type="button" class="btn btn-light" data-close-modal>Fechar</button>
        </div>
        <img src="" alt="" id="album-photo-preview">
    </div>
</div>

<?php include __DIR__ . '/includes/bottom-nav.php'; ?>
</main>
</div>
</div>
</body>
</html>
