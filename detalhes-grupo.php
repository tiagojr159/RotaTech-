<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';
requireLogin();

$groupId = (int) ($_GET['id'] ?? 1);
$grupos = readJson('grupos.json');
$roteiros = readJson('roteiros.json');

$group = null;
foreach ($grupos as $g) {
    if ((int) $g['id'] === $groupId) {
        $group = $g;
        break;
    }
}

if (!$group) {
    header('Location: grupos.php');
    exit;
}

$groupRoteiro = ['itens' => []];
foreach ($roteiros as $roteiro) {
    if (($roteiro['tipo'] ?? '') === 'grupo' && (int) ($roteiro['grupo_id'] ?? 0) === $groupId) {
        $groupRoteiro = $roteiro;
        break;
    }
}

$presence = [
    1 => ['local' => 'Palco Multicultural', 'online' => true],
    2 => ['local' => 'Bodega do Zé', 'online' => true],
    3 => ['local' => 'Em casa', 'online' => false],
    4 => ['local' => 'Polo Gastronômico', 'online' => true],
    5 => ['local' => 'No pátio', 'online' => true],
];

$activeTab = 'roteiro';
$pageTitle = $group['nome'];
$backUrl = 'grupos.php';
$rightHtml = '<span class="icon-btn"><i class="fa-solid fa-circle-info"></i></span>';
include __DIR__ . '/includes/header.php';
?>
<section class="group-hero">
    <img src="<?= sanitize($group['capa']); ?>" alt="<?= sanitize($group['nome']); ?>">
    <div class="overlay"></div>
    <div class="group-hero-content">
        <?php if (!empty($group['vip'])): ?><span class="vip-tag">★ VIP</span><?php endif; ?>
        <div class="avatars-inline">
            <?php foreach (array_slice($group['membros'], 0, 4) as $m): ?>
                <img src="<?= sanitize(userAvatarById((int) $m)); ?>" alt="Membro">
            <?php endforeach; ?>
            <span class="avatar-plus">+<?= max(0, count($group['membros']) - 4); ?></span>
        </div>
        <p><strong><?= count($group['membros']); ?></strong> Membros</p>
    </div>
</section>

<section class="section-head">
    <h3>No Pátio Agora</h3>
    <span class="live-tag"><i class="fa-solid fa-leaf"></i> Ao vivo</span>
</section>
<div class="presence-row">
    <?php foreach (array_slice($group['membros'], 0, 4) as $memberId): ?>
        <article class="presence-item">
            <img src="<?= sanitize(userAvatarById((int) $memberId)); ?>" alt="<?= sanitize(userNameById((int) $memberId)); ?>">
            <span class="online-dot <?= !empty($presence[$memberId]['online']) ? 'on' : 'off'; ?>"></span>
            <h4><?= sanitize(explode(' ', userNameById((int) $memberId))[0]); ?></h4>
            <p><?= sanitize($presence[$memberId]['local'] ?? 'Vila'); ?></p>
        </article>
    <?php endforeach; ?>
</div>

<section class="section-head"><h3>Roteiro do Grupo</h3></section>
<div class="timeline">
    <?php foreach ($groupRoteiro['itens'] as $item): ?>
        <article class="timeline-card">
            <div class="timeline-marker"></div>
            <div class="card roteiro-card">
                <p class="time-title"><?= sanitize($item['horario']); ?></p>
                <h3><?= sanitize($item['titulo']); ?></h3>
                <p><?= sanitize($item['local']); ?></p>
                <small>Adicionado por <?= sanitize($item['sugerido_por']); ?></small>
            </div>
        </article>
    <?php endforeach; ?>
</div>

<button class="fab-main" data-open-modal="modal-add-group-item"><i class="fa-solid fa-plus"></i></button>

<div class="modal hidden" id="modal-add-group-item">
    <div class="modal-backdrop" data-close-modal></div>
    <div class="modal-content">
        <h3>Novo item no roteiro do grupo</h3>
        <form id="form-add-group-item">
            <input type="hidden" name="tipo" value="grupo">
            <input type="hidden" name="grupo_id" value="<?= (int) $groupId; ?>">
            <label>Horário</label>
            <input type="time" name="horario" required>
            <label>Título</label>
            <input type="text" name="titulo" required>
            <label>Local</label>
            <input type="text" name="local" required>
            <button class="btn btn-primary btn-xl" type="submit">Adicionar</button>
        </form>
    </div>
</div>

<?php include __DIR__ . '/includes/bottom-nav.php'; ?>
</main>
</div>
</div>
</body>
</html>

