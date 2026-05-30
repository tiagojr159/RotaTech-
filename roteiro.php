<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';
requireLogin();

$user = currentUser();
$roteiros = readJson('roteiros.json');
$grupos = readJson('grupos.json');
$users = readJson('users.json');
$myGroup = null;
foreach ($grupos as $grupo) {
    if (in_array((int) $user['id'], $grupo['membros'] ?? [], true)) {
        $myGroup = $grupo;
        break;
    }
}

$meuRoteiro = ['itens' => []];
$roteiroGrupo = ['itens' => []];
foreach ($roteiros as $roteiro) {
    if (($roteiro['tipo'] ?? '') === 'pessoal' && (int) ($roteiro['user_id'] ?? 0) === (int) $user['id']) {
        $meuRoteiro = $roteiro;
    }
    if (($roteiro['tipo'] ?? '') === 'grupo' && (int) ($roteiro['grupo_id'] ?? 0) === (int) ($myGroup['id'] ?? 0)) {
        $roteiroGrupo = $roteiro;
    }
}

$usuariosParaCompartilhar = array_values(array_filter(
    $users,
    fn(array $item): bool => (int) ($item['id'] ?? 0) !== (int) $user['id']
));

$roteiroCompartilhado = array_values(array_filter(
    $meuRoteiro['itens'] ?? [],
    fn(array $item): bool => !empty($item['compartilhado_com_id'])
));

$textoRoteiroPessoal = '';
if (!empty($meuRoteiro['itens'])) {
    $partesRoteiro = [];
    foreach ($meuRoteiro['itens'] as $item) {
        $partesRoteiro[] = trim(($item['titulo'] ?? 'Parada') . ' as ' . ($item['horario'] ?? '--:--') . ' em ' . ($item['local'] ?? 'Arcoverde'));
    }
    $textoRoteiroPessoal = 'Este e o seu roteiro: ' . implode('. ', $partesRoteiro) . '.';
}

$textoRoteiroGrupo = '';
if (!empty($roteiroGrupo['itens'])) {
    $partesGrupo = [];
    foreach ($roteiroGrupo['itens'] as $item) {
        $partesGrupo[] = trim(($item['titulo'] ?? 'Parada') . ' as ' . ($item['horario'] ?? '--:--') . ' em ' . ($item['local'] ?? 'Arcoverde'));
    }
    $textoRoteiroGrupo = 'Este e o roteiro do grupo: ' . implode('. ', $partesGrupo) . '.';
}

$activeTab = 'roteiro';
$pageTitle = 'Roteiro';
$pageEyebrow = 'festival';
include __DIR__ . '/includes/header.php';
?>
<section class="toggle-wrap roteiro-toggle" data-roteiro-toggle>
    <button class="toggle-btn active" data-target="pessoal">Meu roteiro</button>
    <button class="toggle-btn" data-target="grupo">Grupo</button>
</section>
<p class="date-line"><i class="fa-regular fa-calendar"></i> Sexta, 24 de Junho</p>

<?php if ($textoRoteiroPessoal !== '' || $textoRoteiroGrupo !== ''): ?>
    <article class="card voice-guide-card">
        <div>
            <p class="eyebrow">Assistente de voz</p>
            <h3>Ouvir roteiro</h3>
            <p>O app consegue ler a trilha do seu roteiro e tambem o roteiro do grupo.</p>
        </div>
        <div class="voice-guide-actions">
            <?php if ($textoRoteiroPessoal !== ''): ?>
                <button
                    type="button"
                    class="btn btn-primary"
                    data-voice-trigger
                    data-voice-context="roteiro"
                    data-voice-scope="pessoal"
                    data-voice-autoplay="true"
                    data-voice-text="<?= sanitize($textoRoteiroPessoal); ?>"
                >
                    <i class="fa-solid fa-volume-high"></i>
                    Ouvir meu roteiro
                </button>
            <?php endif; ?>
            <?php if ($textoRoteiroGrupo !== ''): ?>
                <button
                    type="button"
                    class="btn btn-light"
                    data-voice-trigger
                    data-voice-context="roteiro"
                    data-voice-scope="grupo"
                    data-voice-text="<?= sanitize($textoRoteiroGrupo); ?>"
                >
                    <i class="fa-solid fa-users"></i>
                    Ouvir roteiro do grupo
                </button>
            <?php endif; ?>
        </div>
    </article>
<?php endif; ?>

<section data-roteiro-panel="pessoal">
    <div class="stack-list">
        <?php foreach ($meuRoteiro['itens'] ?? [] as $item): ?>
            <article class="card roteiro-card">
                <div class="star-marker"><i class="fa-solid fa-star"></i></div>
                <div>
                    <p class="time-title"><?= sanitize($item['horario']); ?> <?= ($item['status'] ?? '') === 'agora' ? '• AGORA' : ''; ?></p>
                    <h3><?= sanitize($item['titulo']); ?></h3>
                    <p><i class="fa-solid fa-location-dot"></i> <?= sanitize($item['local']); ?></p>
                    <?php if (!empty($item['sugerido_por'])): ?>
                        <small>Sugerido por <?= sanitize($item['sugerido_por']); ?></small>
                    <?php endif; ?>
                    <?php if (!empty($item['compartilhado_com_nome'])): ?>
                        <small>Compartilhado com <?= sanitize((string) $item['compartilhado_com_nome']); ?></small>
                    <?php endif; ?>
                </div>
                <button class="icon-btn soft" data-remove-roteiro data-item-id="<?= (int) $item['id']; ?>" data-roteiro-id="<?= (int) ($meuRoteiro['id'] ?? 0); ?>">
                    <i class="fa-solid fa-trash"></i>
                </button>
            </article>
        <?php endforeach; ?>
    </div>
    <article class="empty-card">
        <i class="fa-regular fa-compass"></i>
        <h4>Nada para as 23:00?</h4>
        <p>Confira o que está rolando nos outros palcos da Vila.</p>
        <a href="programacao.php" class="btn btn-soft"><i class="fa-solid fa-magnifying-glass"></i> Explorar atrações</a>
    </article>
</section>

<section data-roteiro-panel="grupo" class="hidden">
    <?php if (!empty($myGroup)): ?>
        <a class="card group-link-card" href="detalhes-grupo.php?id=<?= (int) $myGroup['id']; ?>">
            <img src="<?= sanitize($myGroup['capa']); ?>" alt="<?= sanitize($myGroup['nome']); ?>">
            <div>
                <h3><?= sanitize($myGroup['nome']); ?></h3>
                <p><?= count($myGroup['membros'] ?? []); ?> membros • roteiro compartilhado</p>
            </div>
            <i class="fa-solid fa-angle-right"></i>
        </a>
    <?php endif; ?>
    <article class="card">
        <h3>Montar roteiro com outro usuario</h3>
        <p class="muted">Pesquise a pessoa e compartilhe um item para ele aparecer no roteiro dos dois.</p>
        <?php if (!empty($usuariosParaCompartilhar)): ?>
            <form id="form-add-group-item" class="group-share-form">
                <div class="group-user-picker">
                    <label for="group-user-search">Pesquisar usuario</label>
                    <div class="group-user-search-wrap">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <input
                            type="text"
                            id="group-user-search"
                            placeholder="Digite o nome ou @usuario"
                            autocomplete="off"
                            required
                        >
                    </div>
                    <div class="group-user-results" id="group-user-results">
                        <?php foreach ($usuariosParaCompartilhar as $shareUser): ?>
                            <button
                                type="button"
                                class="group-user-option"
                                data-group-user-option
                                data-user-id="<?= (int) $shareUser['id']; ?>"
                                data-user-name="<?= sanitize((string) $shareUser['nome']); ?>"
                                data-user-handle="<?= sanitize((string) $shareUser['usuario']); ?>"
                            >
                                <img src="<?= sanitize((string) ($shareUser['avatar'] ?? 'assets/img/avatar-default.svg')); ?>" alt="<?= sanitize((string) $shareUser['nome']); ?>">
                                <span>
                                    <strong><?= sanitize((string) $shareUser['nome']); ?></strong>
                                    <small>@<?= sanitize((string) $shareUser['usuario']); ?></small>
                                </span>
                            </button>
                        <?php endforeach; ?>
                    </div>
                    <div class="group-user-selected hidden" id="group-user-selected">
                        <span class="group-user-selected-copy">
                            <strong data-selected-user-name></strong>
                            <small data-selected-user-handle></small>
                        </span>
                        <button type="button" class="icon-btn soft" data-clear-group-user>
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>
                </div>
                <input type="hidden" name="partner_user_id" id="group-user-id">
                <input type="hidden" name="tipo" value="grupo">
                <div class="group-share-grid">
                    <div>
                        <label>Horario</label>
                        <input type="time" name="horario" required>
                    </div>
                    <div>
                        <label>Titulo</label>
                        <input type="text" name="titulo" placeholder="Ex: Show de Forro" required>
                    </div>
                    <div>
                        <label>Local</label>
                        <input type="text" name="local" placeholder="Ex: Palco Multicultural" required>
                    </div>
                    <div>
                        <label>Tipo</label>
                        <select name="categoria">
                            <option value="show">Show</option>
                            <option value="gastronomia">Gastronomia</option>
                            <option value="ponto">Ponto turistico</option>
                        </select>
                    </div>
                </div>
                <button class="btn btn-primary btn-xl" type="submit">Compartilhar roteiro</button>
            </form>
        <?php else: ?>
            <p class="muted">Nenhum outro usuario disponivel para compartilhar no momento.</p>
        <?php endif; ?>
    </article>
    <?php if (!empty($roteiroCompartilhado)): ?>
        <div class="stack-list">
            <?php foreach ($roteiroCompartilhado as $item): ?>
                <article class="card roteiro-card">
                    <div>
                        <p class="time-title"><?= sanitize((string) ($item['horario'] ?? '')); ?></p>
                        <h3><?= sanitize((string) ($item['titulo'] ?? '')); ?></h3>
                        <p><i class="fa-solid fa-location-dot"></i> <?= sanitize((string) ($item['local'] ?? '')); ?></p>
                        <small>Compartilhado com <?= sanitize((string) ($item['compartilhado_com_nome'] ?? 'outro usuario')); ?></small>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <div class="stack-list">
        <?php foreach ($roteiroGrupo['itens'] ?? [] as $item): ?>
            <article class="card roteiro-card">
                <div>
                    <p class="time-title"><?= sanitize($item['horario']); ?></p>
                    <h3><?= sanitize($item['titulo']); ?></h3>
                    <p><i class="fa-solid fa-location-dot"></i> <?= sanitize($item['local']); ?></p>
                    <small>Adicionado por <?= sanitize($item['sugerido_por'] ?? 'membro'); ?></small>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</section>

<button class="btn btn-primary btn-xl export-btn" onclick="window.print()">
    <i class="fa-solid fa-arrow-up-from-bracket"></i>
    Exportar em PDF
</button>

<button class="fab-main" data-open-modal="modal-add-roteiro"><i class="fa-solid fa-plus"></i></button>

<div class="modal hidden" id="modal-add-roteiro">
    <div class="modal-backdrop" data-close-modal></div>
    <div class="modal-content">
        <h3>Adicionar ao roteiro</h3>
        <form id="form-add-roteiro">
            <input type="hidden" name="tipo" value="pessoal">
            <label>Horário</label>
            <input type="time" name="horario" required>
            <label>Título</label>
            <input type="text" name="titulo" placeholder="Ex: Show de Forró" required>
            <label>Local</label>
            <input type="text" name="local" placeholder="Ex: Palco Multicultural" required>
            <label>Tipo</label>
            <select name="categoria">
                <option value="show">Show</option>
                <option value="gastronomia">Gastronomia</option>
                <option value="ponto">Ponto turístico</option>
            </select>
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
