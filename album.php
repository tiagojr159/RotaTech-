<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';
requireLogin();

$user = currentUser();
$figurinhas = readJson('figurinhas.json');
$minicolecaoTotal = 6;
$coletadas = count(array_intersect($user['figurinhas'] ?? [], array_column(array_slice($figurinhas, 0, $minicolecaoTotal), 'id')));
$activeTab = 'album';
$pageTitle = 'Álbum';
$pageEyebrow = '';
$rightHtml = '<a href="perfil.php" class="icon-btn soft"><i class="fa-regular fa-user"></i></a>';
include __DIR__ . '/includes/header.php';
?>
<article class="card unlock-banner">
    <small>CONQUISTA DESBLOQUEADA</small>
    <h3>Eu vivi o São João de Arcoverde</h3>
</article>

<section class="sticker-grid" data-sticker-grid>
    <button class="sticker-card camera-card" data-collect-next>
        <span class="camera-icon"><i class="fa-solid fa-camera"></i></span>
        <strong>Tirar foto</strong>
    </button>
    <?php foreach ($figurinhas as $figurinha): ?>
        <?php $locked = !in_array((int) $figurinha['id'], $user['figurinhas'] ?? [], true); ?>
        <button class="sticker-card <?= $locked ? 'locked' : 'unlocked'; ?>" data-sticker-id="<?= (int) $figurinha['id']; ?>">
            <img src="<?= sanitize($figurinha['imagem']); ?>" alt="<?= sanitize($figurinha['titulo']); ?>">
            <?php if ($locked): ?><span class="lock"><i class="fa-solid fa-lock"></i></span><?php endif; ?>
        </button>
    <?php endforeach; ?>
</section>

<section class="album-progress">
    <h4>SEU PROGRESSO</h4>
    <div class="progress"><span style="width: <?= min(100, ($coletadas / $minicolecaoTotal) * 100); ?>%"></span></div>
    <p><strong><?= $coletadas; ?></strong> de <?= $minicolecaoTotal; ?> figurinhas coletadas</p>
</section>

<?php include __DIR__ . '/includes/bottom-nav.php'; ?>
</main>
</div>
</div>
</body>
</html>

