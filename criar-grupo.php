<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';
requireLogin();

$user = currentUser();
$users = readJson('users.json');
$friends = array_values(array_filter($users, fn($u) => (int) $u['id'] !== (int) $user['id']));

$activeTab = 'roteiro';
$pageTitle = 'Criar Novo Grupo';
$backUrl = 'grupos.php';
$rightHtml = '<img src="' . sanitize($user['avatar']) . '" alt="Avatar" class="avatar-sm">';
include __DIR__ . '/includes/header.php';
?>
<form id="form-criar-grupo" class="stacked-form" enctype="multipart/form-data">
    <div class="upload-card">
        <label for="capaUpload">
            <input type="file" id="capaUpload" name="capa" accept=".png,.jpg,.jpeg,.webp">
            <span class="camera-icon"><i class="fa-solid fa-camera"></i></span>
            <strong>Adicionar Capa do Grupo</strong>
            <small>PNG ou JPG (Até 5MB)</small>
        </label>
    </div>

    <label>Nome do Grupo</label>
    <input type="text" name="nome" placeholder="Ex: Turma do Forró 2026" required>

    <label>Privacidade</label>
    <div class="privacy-grid" data-privacy-picker>
        <button type="button" class="privacy-card active" data-value="publico"><i class="fa-solid fa-earth-americas"></i> Público</button>
        <button type="button" class="privacy-card" data-value="privado"><i class="fa-solid fa-lock"></i> Privado</button>
    </div>
    <input type="hidden" name="privacidade" value="publico">

    <div class="row-between">
        <h3 class="section-title">Convidar Amigos</h3>
        <span class="muted"><strong data-selected-count>0</strong> selecionados</span>
    </div>
    <div class="input-icon">
        <i class="fa-solid fa-magnifying-glass"></i>
        <input type="search" placeholder="Buscar por nome ou @id" data-friend-search>
    </div>
    <input type="hidden" name="convidados" value="" data-selected-input>

    <div class="stack-list" data-friends-list>
        <?php foreach ($friends as $friend): ?>
            <article class="friend-item" data-friend-card data-name="<?= sanitize(mb_strtolower($friend['nome'] . ' ' . $friend['usuario'])); ?>">
                <div class="friend-info">
                    <img src="<?= sanitize($friend['avatar']); ?>" alt="<?= sanitize($friend['nome']); ?>">
                    <div>
                        <h4><?= sanitize($friend['nome']); ?></h4>
                        <p>@<?= sanitize($friend['usuario']); ?></p>
                    </div>
                </div>
                <button type="button" class="select-friend-btn" data-friend-id="<?= (int) $friend['id']; ?>">
                    <i class="fa-solid fa-plus"></i>
                </button>
            </article>
        <?php endforeach; ?>
    </div>

    <button class="btn btn-primary btn-xl" type="submit">Criar Grupo <i class="fa-solid fa-wand-magic-sparkles"></i></button>
</form>

<?php include __DIR__ . '/includes/bottom-nav.php'; ?>
</main>
</div>
</div>
</body>
</html>

