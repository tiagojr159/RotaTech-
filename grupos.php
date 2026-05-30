<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';
requireLogin();

$user = currentUser();
$grupos = readJson('grupos.json');
$convites = readJson('convites.json');
$myGroups = array_values(array_filter($grupos, fn($g) => in_array((int) $user['id'], $g['membros'] ?? [], true)));
$pending = array_values(array_filter($convites, fn($c) => (int) $c['to_user_id'] === (int) $user['id'] && ($c['status'] ?? '') === 'pendente'));

$activeTab = 'roteiro';
$pageTitle = 'Grupos';
$pageEyebrow = 'festival';
include __DIR__ . '/includes/header.php';
?>
<section class="toggle-wrap">
    <a href="roteiro.php" class="toggle-btn">Meu Roteiro</a>
    <button class="toggle-btn active">Grupos</button>
</section>

<section class="section-head">
    <h3>Seus Grupos</h3>
    <i class="fa-solid fa-users"></i>
</section>
<div class="stack-list">
    <?php foreach ($myGroups as $grupo): ?>
        <a href="detalhes-grupo.php?id=<?= (int) $grupo['id']; ?>" class="group-card">
            <img src="<?= sanitize($grupo['capa']); ?>" alt="<?= sanitize($grupo['nome']); ?>" class="group-thumb">
            <div>
                <h4><?= sanitize($grupo['nome']); ?></h4>
                <p><i class="fa-regular fa-user"></i> <?= count($grupo['membros'] ?? []); ?> membros</p>
            </div>
            <div class="avatars-inline">
                <?php foreach (array_slice($grupo['membros'], 0, 2) as $idM): ?>
                    <img src="<?= sanitize(userAvatarById((int) $idM)); ?>" alt="Membro">
                <?php endforeach; ?>
            </div>
            <?php if (!empty($grupo['vip'])): ?><span class="vip-tag">VIP</span><?php endif; ?>
        </a>
    <?php endforeach; ?>
</div>

<section class="section-head"><h3>Explorar</h3></section>
<div class="explore-grid">
    <a href="criar-grupo.php" class="explore-card filled">
        <i class="fa-solid fa-plus"></i>
        <span>Criar Novo Grupo</span>
    </a>
    <button class="explore-card" data-open-modal="modal-codigo">
        <i class="fa-solid fa-qrcode"></i>
        <span>Entrar com Código</span>
    </button>
</div>

<section class="section-head">
    <h3>Convites</h3>
    <span class="badge-soft"><?= count($pending); ?> NOVOS</span>
</section>
<div class="stack-list">
    <?php foreach ($pending as $convite): ?>
        <?php
        $groupName = 'Grupo';
        foreach ($grupos as $g) {
            if ((int) $g['id'] === (int) $convite['group_id']) {
                $groupName = $g['nome'];
                break;
            }
        }
        ?>
        <article class="invite-card">
            <img src="<?= sanitize(userAvatarById((int) $convite['from_user_id'])); ?>" alt="Convidou">
            <div>
                <h4><?= sanitize(userNameById((int) $convite['from_user_id'])); ?> convidou você</h4>
                <p>Grupo: <?= sanitize($groupName); ?></p>
            </div>
            <button class="icon-btn success" data-invite-action="aceitar" data-invite-id="<?= (int) $convite['id']; ?>"><i class="fa-solid fa-check"></i></button>
            <button class="icon-btn danger" data-invite-action="recusar" data-invite-id="<?= (int) $convite['id']; ?>"><i class="fa-solid fa-xmark"></i></button>
        </article>
    <?php endforeach; ?>
</div>

<article class="card card-soft friend-near">
    <img src="assets/img/avatar-marta.svg" alt="Marta">
    <div>
        <h4>Marta está por perto</h4>
        <p>Amiga em comum no festival</p>
    </div>
    <button class="btn btn-light btn-sm" data-toast="Convite enviado para Marta!">Convidar</button>
</article>

<div class="modal hidden" id="modal-codigo">
    <div class="modal-backdrop" data-close-modal></div>
    <div class="modal-content">
        <h3>Entrar com Código</h3>
        <form id="form-enter-code">
            <label>Código do grupo</label>
            <input type="text" name="codigo" placeholder="Ex: FORRO26" required>
            <button class="btn btn-primary btn-xl" type="submit">Entrar</button>
        </form>
    </div>
</div>

<?php include __DIR__ . '/includes/bottom-nav.php'; ?>
</main>
</div>
</div>
</body>
</html>

