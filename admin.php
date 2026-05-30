<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';
requireAdmin();

$message = '';
$error = '';
$activeAdminTab = sanitize($_POST['admin_tab'] ?? $_GET['tab'] ?? 'hospedagem');
if (!in_array($activeAdminTab, ['hospedagem', 'restaurantes', 'eventos'], true)) {
    $activeAdminTab = 'hospedagem';
}

$hospedagens = readJson('hospedagens.json');
$restaurantes = readJson('restaurantes.json');
$programacao = readJson('programacao.json');

$nextId = static function (array $rows): int {
    $max = 0;
    foreach ($rows as $row) {
        $id = (int) ($row['id'] ?? 0);
        if ($id > $max) {
            $max = $id;
        }
    }
    return $max + 1;
};

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = sanitize($_POST['action'] ?? '');

    if ($action === 'save_hospedagem') {
        $id = (int) ($_POST['id'] ?? 0);
        $nome = sanitize($_POST['nome'] ?? '');
        $categoria = sanitize($_POST['categoria'] ?? 'hotel');
        $endereco = sanitize($_POST['endereco'] ?? '');
        $cidade = sanitize($_POST['cidade'] ?? 'Arcoverde, Pernambuco');
        $latitude = (float) ($_POST['latitude'] ?? 0);
        $longitude = (float) ($_POST['longitude'] ?? 0);
        $currentImage = sanitize($_POST['imagem_atual'] ?? '');
        $imagem = uploadImage('foto_hospedagem') ?? ($currentImage !== '' ? $currentImage : null);

        if ($nome === '' || $endereco === '') {
            $error = 'Informe nome e endereco da hospedagem.';
        } else {
            $payload = [
                'nome' => $nome,
                'categoria' => $categoria === 'pousada' ? 'pousada' : 'hotel',
                'endereco' => $endereco,
                'cidade' => $cidade,
                'latitude' => $latitude,
                'longitude' => $longitude,
                'imagem' => $imagem,
            ];

            $updated = false;
            foreach ($hospedagens as &$item) {
                if ((int) ($item['id'] ?? 0) === $id && $id > 0) {
                    $item = array_merge($item, $payload);
                    $updated = true;
                    break;
                }
            }
            unset($item);

            if (!$updated) {
                $payload['id'] = $nextId($hospedagens);
                $hospedagens[] = $payload;
            }

            writeJson('hospedagens.json', $hospedagens);
            $message = $updated ? 'Hospedagem atualizada.' : 'Hospedagem cadastrada.';
        }
    }

    if ($action === 'save_restaurante') {
        $id = (int) ($_POST['id'] ?? 0);
        $nome = sanitize($_POST['nome'] ?? '');
        $categoria = sanitize($_POST['categoria'] ?? '');
        $distancia = sanitize($_POST['distancia'] ?? '0.0km');
        $avaliacao = (float) ($_POST['avaliacao'] ?? 4.5);
        $faixaPreco = sanitize($_POST['faixa_preco'] ?? '$$');
        $abertoAte = sanitize($_POST['aberto_ate'] ?? '23:00');
        $pratoDestaque = sanitize($_POST['prato_destaque'] ?? 'Prato da casa');
        $precoPrato = sanitize($_POST['preco_prato'] ?? 'R$ 0,00');
        $descricao = sanitize($_POST['descricao'] ?? 'Sem descricao.');

        if ($nome === '' || $categoria === '') {
            $error = 'Informe nome e categoria do restaurante.';
        } else {
            $payload = [
                'nome' => $nome,
                'categoria' => $categoria,
                'distancia' => $distancia,
                'avaliacao' => $avaliacao,
                'faixa_preco' => $faixaPreco,
                'aberto_ate' => $abertoAte,
                'imagem' => 'assets/img/rest-casarao.svg',
                'prato_destaque' => $pratoDestaque,
                'preco_prato' => $precoPrato,
                'descricao' => $descricao,
                'lotacao' => 'movimento_moderado',
            ];

            $updated = false;
            foreach ($restaurantes as &$item) {
                if ((int) ($item['id'] ?? 0) === $id && $id > 0) {
                    $item = array_merge($item, $payload);
                    $updated = true;
                    break;
                }
            }
            unset($item);

            if (!$updated) {
                $payload['id'] = $nextId($restaurantes);
                $restaurantes[] = $payload;
            }

            writeJson('restaurantes.json', $restaurantes);
            $message = $updated ? 'Restaurante atualizado.' : 'Restaurante cadastrado.';
        }
    }

    if ($action === 'save_evento') {
        $id = (int) ($_POST['id'] ?? 0);
        $artista = sanitize($_POST['artista'] ?? '');
        $palco = sanitize($_POST['palco'] ?? '');
        $data = sanitize($_POST['data'] ?? '');
        $horario = sanitize($_POST['horario'] ?? '');
        $categoria = sanitize($_POST['categoria'] ?? 'Show');
        $descricao = sanitize($_POST['descricao'] ?? 'Programacao cadastrada pelo admin.');
        $status = sanitize($_POST['status'] ?? 'em_breve');

        if ($artista === '' || $palco === '' || $data === '' || $horario === '') {
            $error = 'Preencha artista, palco, data e horario.';
        } else {
            $payload = [
                'artista' => $artista,
                'palco' => $palco,
                'data' => $data,
                'horario' => $horario,
                'status' => in_array($status, ['ao_vivo', 'proxima_atracao', 'finalizado', 'em_breve'], true) ? $status : 'em_breve',
                'imagem' => 'assets/img/atracao-alceu.svg',
                'categoria' => $categoria,
                'descricao' => $descricao,
                'lotacao' => 'movimento_moderado',
            ];

            $updated = false;
            foreach ($programacao as &$item) {
                if ((int) ($item['id'] ?? 0) === $id && $id > 0) {
                    $item = array_merge($item, $payload);
                    $updated = true;
                    break;
                }
            }
            unset($item);

            if (!$updated) {
                $payload['id'] = $nextId($programacao);
                $programacao[] = $payload;
            }

            writeJson('programacao.json', $programacao);
            $message = $updated ? 'Evento atualizado.' : 'Evento cadastrado.';
        }
    }

    $hospedagens = readJson('hospedagens.json');
    $restaurantes = readJson('restaurantes.json');
    $programacao = readJson('programacao.json');
}

$showTopBar = true;
$backUrl = 'home.php';
$pageTitle = 'Administrador';
$pageEyebrow = 'painel';
$contentClass = 'admin-content';
include __DIR__ . '/includes/header.php';
?>
<section class="auth-form-page admin-page" data-admin-panel data-admin-active-tab="<?= sanitize($activeAdminTab); ?>">
    <article class="card card-soft admin-intro">
        <h3>Painel Administrativo</h3>
        <p>Escolha a categoria, veja o que ja foi cadastrado e abra o cadastro ou a edicao quando precisar.</p>
        <?php if ($message): ?><p class="alert alert-success"><?= sanitize($message); ?></p><?php endif; ?>
        <?php if ($error): ?><p class="alert alert-danger"><?= sanitize($error); ?></p><?php endif; ?>
    </article>

    <nav class="admin-tab-menu">
        <button type="button" class="admin-tab-btn" data-admin-tab-button="hospedagem"><i class="fa-solid fa-bed"></i><span>Hoteis e pousadas</span></button>
        <button type="button" class="admin-tab-btn" data-admin-tab-button="restaurantes"><i class="fa-solid fa-utensils"></i><span>Restaurantes</span></button>
        <button type="button" class="admin-tab-btn" data-admin-tab-button="eventos"><i class="fa-solid fa-calendar-days"></i><span>Eventos</span></button>
    </nav>

    <section class="admin-section" data-admin-tab-panel="hospedagem">
        <div class="section-head admin-section-head">
            <h3>Hoteis e Pousadas</h3>
            <button type="button" class="btn btn-primary" data-open-modal="modal-hospedagem-create">Novo cadastro</button>
        </div>
        <div class="stack-list admin-list">
            <?php foreach ($hospedagens as $item): ?>
                <article class="card admin-item">
                    <div class="admin-item-media">
                        <?php if (!empty($item['imagem'])): ?>
                            <img src="<?= sanitize((string) $item['imagem']); ?>" alt="<?= sanitize((string) $item['nome']); ?>" class="admin-thumb">
                        <?php else: ?>
                            <span class="point-icon"><i class="fa-solid fa-bed"></i></span>
                        <?php endif; ?>
                    </div>
                    <div class="admin-item-copy">
                        <h4><?= sanitize((string) $item['nome']); ?></h4>
                        <p><?= sanitize(ucfirst((string) $item['categoria'])); ?> - <?= sanitize((string) $item['endereco']); ?></p>
                        <small><?= sanitize((string) ($item['cidade'] ?? 'Arcoverde, Pernambuco')); ?></small>
                    </div>
                    <div class="admin-actions">
                        <button
                            type="button"
                            class="icon-btn soft"
                            data-open-modal="modal-hospedagem-edit"
                            data-edit-hospedagem
                            data-id="<?= (int) $item['id']; ?>"
                            data-nome="<?= sanitize((string) $item['nome']); ?>"
                            data-categoria="<?= sanitize((string) $item['categoria']); ?>"
                            data-endereco="<?= sanitize((string) $item['endereco']); ?>"
                            data-cidade="<?= sanitize((string) ($item['cidade'] ?? 'Arcoverde, Pernambuco')); ?>"
                            data-latitude="<?= sanitize((string) ($item['latitude'] ?? 0)); ?>"
                            data-longitude="<?= sanitize((string) ($item['longitude'] ?? 0)); ?>"
                            data-imagem="<?= sanitize((string) ($item['imagem'] ?? '')); ?>"
                        >
                            <i class="fa-solid fa-pen"></i>
                        </button>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="admin-section hidden" data-admin-tab-panel="restaurantes">
        <div class="section-head admin-section-head">
            <h3>Restaurantes</h3>
            <button type="button" class="btn btn-primary" data-open-modal="modal-restaurante-create">Novo cadastro</button>
        </div>
        <div class="stack-list admin-list">
            <?php foreach ($restaurantes as $item): ?>
                <article class="card admin-item">
                    <div class="admin-item-media">
                        <span class="point-icon"><i class="fa-solid fa-utensils"></i></span>
                    </div>
                    <div class="admin-item-copy">
                        <h4><?= sanitize((string) $item['nome']); ?></h4>
                        <p><?= sanitize((string) $item['categoria']); ?> - <?= sanitize((string) $item['distancia']); ?></p>
                        <small><?= sanitize((string) $item['aberto_ate']); ?> - <?= sanitize((string) $item['faixa_preco']); ?></small>
                    </div>
                    <div class="admin-actions">
                        <button
                            type="button"
                            class="icon-btn soft"
                            data-open-modal="modal-restaurante-edit"
                            data-edit-restaurante
                            data-id="<?= (int) $item['id']; ?>"
                            data-nome="<?= sanitize((string) $item['nome']); ?>"
                            data-categoria="<?= sanitize((string) $item['categoria']); ?>"
                            data-distancia="<?= sanitize((string) $item['distancia']); ?>"
                            data-avaliacao="<?= sanitize((string) $item['avaliacao']); ?>"
                            data-faixa="<?= sanitize((string) $item['faixa_preco']); ?>"
                            data-aberto="<?= sanitize((string) $item['aberto_ate']); ?>"
                            data-prato="<?= sanitize((string) $item['prato_destaque']); ?>"
                            data-preco="<?= sanitize((string) $item['preco_prato']); ?>"
                            data-descricao="<?= sanitize((string) $item['descricao']); ?>"
                        >
                            <i class="fa-solid fa-pen"></i>
                        </button>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="admin-section hidden" data-admin-tab-panel="eventos">
        <div class="section-head admin-section-head">
            <h3>Eventos</h3>
            <button type="button" class="btn btn-primary" data-open-modal="modal-evento-create">Novo cadastro</button>
        </div>
        <div class="stack-list admin-list">
            <?php foreach ($programacao as $item): ?>
                <article class="card admin-item">
                    <div class="admin-item-media">
                        <span class="point-icon"><i class="fa-solid fa-calendar-days"></i></span>
                    </div>
                    <div class="admin-item-copy">
                        <h4><?= sanitize((string) $item['artista']); ?></h4>
                        <p><?= sanitize((string) $item['palco']); ?> - <?= sanitize((string) $item['horario']); ?></p>
                        <small><?= sanitize((string) $item['data']); ?> - <?= sanitize((string) $item['status']); ?></small>
                    </div>
                    <div class="admin-actions">
                        <button
                            type="button"
                            class="icon-btn soft"
                            data-open-modal="modal-evento-edit"
                            data-edit-evento
                            data-id="<?= (int) $item['id']; ?>"
                            data-artista="<?= sanitize((string) $item['artista']); ?>"
                            data-palco="<?= sanitize((string) $item['palco']); ?>"
                            data-data="<?= sanitize((string) $item['data']); ?>"
                            data-horario="<?= sanitize((string) $item['horario']); ?>"
                            data-categoria="<?= sanitize((string) $item['categoria']); ?>"
                            data-descricao="<?= sanitize((string) $item['descricao']); ?>"
                            data-status="<?= sanitize((string) $item['status']); ?>"
                        >
                            <i class="fa-solid fa-pen"></i>
                        </button>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </section>
</section>

<div class="modal hidden" id="modal-hospedagem-create">
    <div class="modal-backdrop" data-close-modal></div>
    <div class="modal-content">
        <h3>Novo Hotel ou Pousada</h3>
        <form class="stacked-form" method="post" enctype="multipart/form-data">
            <input type="hidden" name="action" value="save_hospedagem">
            <input type="hidden" name="admin_tab" value="hospedagem">
            <input type="hidden" name="id" value="0">
            <label>Nome</label><input type="text" name="nome" required>
            <label>Categoria</label>
            <select name="categoria">
                <option value="hotel">Hotel</option>
                <option value="pousada">Pousada</option>
            </select>
            <label>Endereco</label><input type="text" name="endereco" required>
            <label>Cidade</label><input type="text" name="cidade" value="Arcoverde, Pernambuco">
            <label>Latitude</label><input type="number" step="0.000001" name="latitude">
            <label>Longitude</label><input type="number" step="0.000001" name="longitude">
            <label>Foto</label><input type="file" name="foto_hospedagem" accept=".jpg,.jpeg,.png,.webp">
            <button class="btn btn-primary btn-xl" type="submit">Salvar hospedagem</button>
        </form>
    </div>
</div>

<div class="modal hidden" id="modal-hospedagem-edit">
    <div class="modal-backdrop" data-close-modal></div>
    <div class="modal-content">
        <h3>Editar Hotel ou Pousada</h3>
        <form class="stacked-form" method="post" enctype="multipart/form-data" id="form-edit-hospedagem">
            <input type="hidden" name="action" value="save_hospedagem">
            <input type="hidden" name="admin_tab" value="hospedagem">
            <input type="hidden" name="id" value="0">
            <input type="hidden" name="imagem_atual" value="">
            <label>Nome</label><input type="text" name="nome" required>
            <label>Categoria</label>
            <select name="categoria">
                <option value="hotel">Hotel</option>
                <option value="pousada">Pousada</option>
            </select>
            <label>Endereco</label><input type="text" name="endereco" required>
            <label>Cidade</label><input type="text" name="cidade" value="Arcoverde, Pernambuco">
            <label>Latitude</label><input type="number" step="0.000001" name="latitude">
            <label>Longitude</label><input type="number" step="0.000001" name="longitude">
            <label>Foto</label><input type="file" name="foto_hospedagem" accept=".jpg,.jpeg,.png,.webp">
            <button class="btn btn-primary btn-xl" type="submit">Atualizar hospedagem</button>
        </form>
    </div>
</div>

<div class="modal hidden" id="modal-restaurante-create">
    <div class="modal-backdrop" data-close-modal></div>
    <div class="modal-content">
        <h3>Novo Restaurante</h3>
        <form class="stacked-form" method="post">
            <input type="hidden" name="action" value="save_restaurante">
            <input type="hidden" name="admin_tab" value="restaurantes">
            <input type="hidden" name="id" value="0">
            <label>Nome</label><input type="text" name="nome" required>
            <label>Categoria</label><input type="text" name="categoria" required>
            <label>Distancia</label><input type="text" name="distancia" value="0.0km">
            <label>Avaliacao</label><input type="number" name="avaliacao" step="0.1" min="0" max="5" value="4.5">
            <label>Faixa de preco</label><input type="text" name="faixa_preco" value="$$">
            <label>Aberto ate</label><input type="time" name="aberto_ate" value="23:00">
            <label>Prato destaque</label><input type="text" name="prato_destaque">
            <label>Preco do prato</label><input type="text" name="preco_prato" placeholder="R$ 0,00">
            <label>Descricao</label><textarea name="descricao" rows="2"></textarea>
            <button class="btn btn-primary btn-xl" type="submit">Salvar restaurante</button>
        </form>
    </div>
</div>

<div class="modal hidden" id="modal-restaurante-edit">
    <div class="modal-backdrop" data-close-modal></div>
    <div class="modal-content">
        <h3>Editar Restaurante</h3>
        <form class="stacked-form" method="post" id="form-edit-restaurante">
            <input type="hidden" name="action" value="save_restaurante">
            <input type="hidden" name="admin_tab" value="restaurantes">
            <input type="hidden" name="id" value="0">
            <label>Nome</label><input type="text" name="nome" required>
            <label>Categoria</label><input type="text" name="categoria" required>
            <label>Distancia</label><input type="text" name="distancia" value="0.0km">
            <label>Avaliacao</label><input type="number" name="avaliacao" step="0.1" min="0" max="5" value="4.5">
            <label>Faixa de preco</label><input type="text" name="faixa_preco" value="$$">
            <label>Aberto ate</label><input type="time" name="aberto_ate" value="23:00">
            <label>Prato destaque</label><input type="text" name="prato_destaque">
            <label>Preco do prato</label><input type="text" name="preco_prato" placeholder="R$ 0,00">
            <label>Descricao</label><textarea name="descricao" rows="2"></textarea>
            <button class="btn btn-primary btn-xl" type="submit">Atualizar restaurante</button>
        </form>
    </div>
</div>

<div class="modal hidden" id="modal-evento-create">
    <div class="modal-backdrop" data-close-modal></div>
    <div class="modal-content">
        <h3>Novo Evento</h3>
        <form class="stacked-form" method="post">
            <input type="hidden" name="action" value="save_evento">
            <input type="hidden" name="admin_tab" value="eventos">
            <input type="hidden" name="id" value="0">
            <label>Artista / Evento</label><input type="text" name="artista" required>
            <label>Palco</label><input type="text" name="palco" required>
            <label>Data</label><input type="date" name="data" required>
            <label>Horario</label><input type="time" name="horario" required>
            <label>Categoria</label><input type="text" name="categoria" value="Show">
            <label>Status</label>
            <select name="status">
                <option value="em_breve">Em breve</option>
                <option value="proxima_atracao">Proxima atracao</option>
                <option value="ao_vivo">Ao vivo</option>
                <option value="finalizado">Finalizado</option>
            </select>
            <label>Descricao</label><textarea name="descricao" rows="2"></textarea>
            <button class="btn btn-primary btn-xl" type="submit">Salvar evento</button>
        </form>
    </div>
</div>

<div class="modal hidden" id="modal-evento-edit">
    <div class="modal-backdrop" data-close-modal></div>
    <div class="modal-content">
        <h3>Editar Evento</h3>
        <form class="stacked-form" method="post" id="form-edit-evento">
            <input type="hidden" name="action" value="save_evento">
            <input type="hidden" name="admin_tab" value="eventos">
            <input type="hidden" name="id" value="0">
            <label>Artista / Evento</label><input type="text" name="artista" required>
            <label>Palco</label><input type="text" name="palco" required>
            <label>Data</label><input type="date" name="data" required>
            <label>Horario</label><input type="time" name="horario" required>
            <label>Categoria</label><input type="text" name="categoria" value="Show">
            <label>Status</label>
            <select name="status">
                <option value="em_breve">Em breve</option>
                <option value="proxima_atracao">Proxima atracao</option>
                <option value="ao_vivo">Ao vivo</option>
                <option value="finalizado">Finalizado</option>
            </select>
            <label>Descricao</label><textarea name="descricao" rows="2"></textarea>
            <button class="btn btn-primary btn-xl" type="submit">Atualizar evento</button>
        </form>
    </div>
</div>

</main>
</div>
</div>
</body>
</html>
