<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';
requireAdmin();

$message = '';
$error = '';
$activeAdminTab = sanitize($_POST['admin_tab'] ?? $_GET['tab'] ?? 'hospedagem');
if (!in_array($activeAdminTab, ['usuarios', 'hospedagem', 'restaurantes', 'eventos', 'relatorios'], true)) {
    $activeAdminTab = 'hospedagem';
}

$users = readJson('users.json');
$hospedagens = readJson('hospedagens.json');
$restaurantes = readJson('restaurantes.json');
$programacao = readJson('programacao.json');
$albumFotos = readJson('album_fotos.json');
$roteiros = readJson('roteiros.json');
$grupos = readJson('grupos.json');
$convites = readJson('convites.json');

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

    if ($action === 'save_user') {
        $id = (int) ($_POST['id'] ?? 0);
        $nome = sanitize($_POST['nome'] ?? '');
        $usuario = sanitize($_POST['usuario'] ?? '');
        $email = sanitize($_POST['email'] ?? '');
        $senha = (string) ($_POST['senha'] ?? '');
        $titulo = sanitize($_POST['titulo'] ?? 'NOVO USUARIO');
        $imagemAtual = sanitize($_POST['avatar_atual'] ?? '');
        $avatar = uploadImage('avatar') ?? ($imagemAtual !== '' ? $imagemAtual : 'assets/img/avatar-default.svg');
        $isAdmin = !empty($_POST['is_admin']);

        if ($nome === '' || $usuario === '' || $email === '') {
            $error = 'Preencha nome, usuario e email.';
        } else {
            $duplicado = false;
            foreach ($users as $item) {
                if ((int) ($item['id'] ?? 0) === $id) {
                    continue;
                }
                if (mb_strtolower((string) ($item['email'] ?? '')) === mb_strtolower($email) || mb_strtolower((string) ($item['usuario'] ?? '')) === mb_strtolower($usuario)) {
                    $duplicado = true;
                    break;
                }
            }

            if ($duplicado) {
                $error = 'Email ou usuario ja cadastrado.';
            } else {
                $updated = false;
                foreach ($users as &$item) {
                    if ((int) ($item['id'] ?? 0) === $id && $id > 0) {
                        $item['nome'] = $nome;
                        $item['usuario'] = $usuario;
                        $item['email'] = $email;
                        $item['titulo'] = $titulo;
                        $item['avatar'] = $avatar;
                        $item['is_admin'] = $isAdmin;
                        if ($senha !== '') {
                            $item['senha_hash'] = password_hash($senha, PASSWORD_DEFAULT);
                        }
                        $updated = true;
                        break;
                    }
                }
                unset($item);

                if (!$updated) {
                    $users[] = [
                        'id' => generateId(),
                        'nome' => $nome,
                        'usuario' => $usuario,
                        'email' => $email,
                        'senha_hash' => password_hash($senha !== '' ? $senha : '123456', PASSWORD_DEFAULT),
                        'avatar' => $avatar,
                        'nivel' => 1,
                        'titulo' => $titulo,
                        'pontos' => 0,
                        'criado_em' => date('Y-m-d'),
                        'favoritos' => [],
                        'figurinhas' => [1],
                        'is_admin' => $isAdmin,
                    ];
                }

                writeJson('users.json', $users);
                $message = $updated ? 'Usuario atualizado.' : 'Usuario cadastrado.';
            }
        }
    }

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
    $users = readJson('users.json');
}

$usuariosAtivos = count($users);
$fotosAlbumTotal = count($albumFotos);
$gruposAtivos = count($grupos);
$convitesPendentes = count(array_filter($convites, fn(array $invite): bool => ($invite['status'] ?? '') === 'pendente'));
$roteirosAtivos = count(array_filter($roteiros, fn(array $roteiro): bool => !empty($roteiro['itens'] ?? [])));
$roteirosCompartilhados = 0;
$favoritosPorEvento = [];
$usuariosComFavoritos = 0;
$figurinhasColetadas = 0;

foreach ($users as $userRow) {
    $favoritosUsuario = $userRow['favoritos'] ?? [];
    if (!empty($favoritosUsuario)) {
        $usuariosComFavoritos++;
    }
    foreach ($favoritosUsuario as $eventoId) {
        $eventoId = (int) $eventoId;
        $favoritosPorEvento[$eventoId] = ($favoritosPorEvento[$eventoId] ?? 0) + 1;
    }
    $figurinhasColetadas += count($userRow['figurinhas'] ?? []);
}

foreach ($roteiros as $roteiro) {
    foreach (($roteiro['itens'] ?? []) as $item) {
        if (!empty($item['compartilhado_com_id'])) {
            $roteirosCompartilhados++;
        }
    }
}

$eventosPorLotacao = [
    'alta_lotacao' => 0,
    'movimento_moderado' => 0,
    'pouco_movimento' => 0,
];
$participacaoPorPalco = [];
$eventosRelatorio = [];

foreach ($programacao as $evento) {
    $eventoId = (int) ($evento['id'] ?? 0);
    $palco = (string) ($evento['palco'] ?? 'Palco nao informado');
    $artista = (string) ($evento['artista'] ?? 'Evento');
    $lotacao = (string) ($evento['lotacao'] ?? 'movimento_moderado');
    $favoritos = (int) ($favoritosPorEvento[$eventoId] ?? 0);
    $matchesRoteiro = 0;

    foreach ($roteiros as $roteiro) {
        foreach (($roteiro['itens'] ?? []) as $item) {
            $titulo = mb_strtolower((string) ($item['titulo'] ?? ''));
            $local = mb_strtolower((string) ($item['local'] ?? ''));
            if (
                $titulo !== '' && str_contains($titulo, mb_strtolower($artista))
                || $local !== '' && str_contains($local, mb_strtolower($palco))
            ) {
                $matchesRoteiro++;
            }
        }
    }

    $pesoLotacao = match ($lotacao) {
        'alta_lotacao' => 22,
        'movimento_moderado' => 14,
        'pouco_movimento' => 8,
        default => 10,
    };

    if (isset($eventosPorLotacao[$lotacao])) {
        $eventosPorLotacao[$lotacao]++;
    }

    $scoreParticipacao = ($favoritos * 4) + ($matchesRoteiro * 3) + $pesoLotacao;
    $participacaoPorPalco[$palco] = ($participacaoPorPalco[$palco] ?? 0) + $scoreParticipacao;

    $eventosRelatorio[] = [
        'artista' => $artista,
        'palco' => $palco,
        'data' => (string) ($evento['data'] ?? ''),
        'horario' => (string) ($evento['horario'] ?? ''),
        'favoritos' => $favoritos,
        'roteiros' => $matchesRoteiro,
        'lotacao' => lotacaoLabel($lotacao),
        'score' => $scoreParticipacao,
    ];
}

usort($eventosRelatorio, fn(array $a, array $b): int => $b['score'] <=> $a['score']);
arsort($participacaoPorPalco);

$engajamentoMedio = $usuariosAtivos > 0 ? (int) round((($usuariosComFavoritos + $fotosAlbumTotal + $roteirosAtivos) / $usuariosAtivos) * 100) : 0;
$topPalcos = array_slice($participacaoPorPalco, 0, 3, true);
$topEventos = array_slice($eventosRelatorio, 0, 5);

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
        <a href="admin.php?tab=usuarios" class="admin-tab-btn <?= $activeAdminTab === 'usuarios' ? 'active' : ''; ?>"><i class="fa-solid fa-users"></i><span>Usuarios</span></a>
        <a href="admin.php?tab=hospedagem" class="admin-tab-btn <?= $activeAdminTab === 'hospedagem' ? 'active' : ''; ?>"><i class="fa-solid fa-bed"></i><span>Hoteis e pousadas</span></a>
        <a href="admin.php?tab=restaurantes" class="admin-tab-btn <?= $activeAdminTab === 'restaurantes' ? 'active' : ''; ?>"><i class="fa-solid fa-utensils"></i><span>Restaurantes</span></a>
        <a href="admin.php?tab=eventos" class="admin-tab-btn <?= $activeAdminTab === 'eventos' ? 'active' : ''; ?>"><i class="fa-solid fa-calendar-days"></i><span>Eventos</span></a>
        <a href="admin.php?tab=relatorios" class="admin-tab-btn <?= $activeAdminTab === 'relatorios' ? 'active' : ''; ?>"><i class="fa-solid fa-chart-line"></i><span>Relatorio</span></a>
    </nav>

    <section class="admin-section <?= $activeAdminTab === 'usuarios' ? '' : 'hidden'; ?>">
        <div class="section-head admin-section-head">
            <h3>Usuarios</h3>
            <button type="button" class="btn btn-primary" data-open-modal="modal-user-create">Novo cadastro</button>
        </div>
        <div class="stack-list admin-list">
            <?php foreach ($users as $item): ?>
                <article class="card admin-item">
                    <div class="admin-item-media">
                        <img src="<?= sanitize((string) ($item['avatar'] ?? 'assets/img/avatar-default.svg')); ?>" alt="<?= sanitize((string) $item['nome']); ?>" class="admin-thumb">
                    </div>
                    <div class="admin-item-copy">
                        <h4><?= sanitize((string) $item['nome']); ?></h4>
                        <p>@<?= sanitize((string) $item['usuario']); ?> - <?= sanitize((string) $item['email']); ?></p>
                        <small><?= !empty($item['is_admin']) || isMasterUser($item) ? 'Administrador' : 'Usuario comum'; ?></small>
                    </div>
                    <div class="admin-actions">
                        <button
                            type="button"
                            class="icon-btn soft"
                            data-open-modal="modal-user-edit"
                            data-edit-user
                            data-id="<?= (int) $item['id']; ?>"
                            data-nome="<?= sanitize((string) $item['nome']); ?>"
                            data-usuario="<?= sanitize((string) $item['usuario']); ?>"
                            data-email="<?= sanitize((string) $item['email']); ?>"
                            data-titulo="<?= sanitize((string) ($item['titulo'] ?? 'NOVO USUARIO')); ?>"
                            data-avatar="<?= sanitize((string) ($item['avatar'] ?? 'assets/img/avatar-default.svg')); ?>"
                            data-is-admin="<?= !empty($item['is_admin']) || isMasterUser($item) ? '1' : '0'; ?>"
                        >
                            <i class="fa-solid fa-pen"></i>
                        </button>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="admin-section <?= $activeAdminTab === 'hospedagem' ? '' : 'hidden'; ?>">
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

    <section class="admin-section <?= $activeAdminTab === 'restaurantes' ? '' : 'hidden'; ?>">
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

    <section class="admin-section <?= $activeAdminTab === 'eventos' ? '' : 'hidden'; ?>">
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

    <section class="admin-section <?= $activeAdminTab === 'relatorios' ? '' : 'hidden'; ?> admin-report-section" data-admin-report>
        <div class="section-head admin-section-head">
            <div>
                <h3>Relatorio do publico</h3>
                <p class="muted">Indicadores estimados com base em usuarios, favoritos, roteiros, grupos e fotos do album.</p>
            </div>
            <button type="button" class="btn btn-primary" data-print-report><i class="fa-solid fa-file-pdf"></i> Baixar PDF</button>
        </div>

        <div class="admin-report-grid">
            <article class="card admin-report-card">
                <small>Usuarios ativos</small>
                <strong><?= $usuariosAtivos; ?></strong>
                <span><?= $usuariosComFavoritos; ?> com favoritos salvos</span>
            </article>
            <article class="card admin-report-card">
                <small>Fotos enviadas</small>
                <strong><?= $fotosAlbumTotal; ?></strong>
                <span>Album compartilhado pela galera</span>
            </article>
            <article class="card admin-report-card">
                <small>Roteiros ativos</small>
                <strong><?= $roteirosAtivos; ?></strong>
                <span><?= $roteirosCompartilhados; ?> itens compartilhados</span>
            </article>
            <article class="card admin-report-card">
                <small>Grupos e convites</small>
                <strong><?= $gruposAtivos; ?></strong>
                <span><?= $convitesPendentes; ?> convites pendentes</span>
            </article>
        </div>

        <article class="card admin-report-highlight">
            <h4>Resumo executivo</h4>
            <p>Engajamento medio estimado: <strong><?= $engajamentoMedio; ?>%</strong></p>
            <p>Figurinhas coletadas pela base: <strong><?= $figurinhasColetadas; ?></strong></p>
            <p>Eventos com alta lotacao: <strong><?= $eventosPorLotacao['alta_lotacao']; ?></strong></p>
            <p>Eventos com movimento moderado: <strong><?= $eventosPorLotacao['movimento_moderado']; ?></strong></p>
        </article>

        <div class="admin-report-columns">
            <article class="card">
                <h4>Palcos com maior participacao</h4>
                <div class="stack-list">
                    <?php foreach ($topPalcos as $palco => $score): ?>
                        <div class="admin-report-row">
                            <span><?= sanitize((string) $palco); ?></span>
                            <strong><?= (int) $score; ?> pts</strong>
                        </div>
                    <?php endforeach; ?>
                </div>
            </article>

            <article class="card">
                <h4>Top eventos por interesse</h4>
                <div class="stack-list">
                    <?php foreach ($topEventos as $evento): ?>
                        <div class="admin-report-row admin-report-row-block">
                            <div>
                                <strong><?= sanitize((string) $evento['artista']); ?></strong>
                                <small><?= sanitize((string) $evento['palco']); ?> · <?= sanitize((string) $evento['horario']); ?></small>
                            </div>
                            <span><?= (int) $evento['score']; ?> pts</span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </article>
        </div>

        <article class="card">
            <h4>Detalhamento dos eventos</h4>
            <div class="admin-report-table">
                <table>
                    <thead>
                        <tr>
                            <th>Evento</th>
                            <th>Palco</th>
                            <th>Favoritos</th>
                            <th>Roteiros</th>
                            <th>Lotacao</th>
                            <th>Score</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($eventosRelatorio as $evento): ?>
                            <tr>
                                <td><?= sanitize((string) $evento['artista']); ?></td>
                                <td><?= sanitize((string) $evento['palco']); ?></td>
                                <td><?= (int) $evento['favoritos']; ?></td>
                                <td><?= (int) $evento['roteiros']; ?></td>
                                <td><?= sanitize((string) $evento['lotacao']); ?></td>
                                <td><?= (int) $evento['score']; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </article>
    </section>
</section>

<div class="modal hidden" id="modal-user-create">
    <div class="modal-backdrop" data-close-modal></div>
    <div class="modal-content">
        <h3>Novo Usuario</h3>
        <form class="stacked-form" method="post" enctype="multipart/form-data">
            <input type="hidden" name="action" value="save_user">
            <input type="hidden" name="admin_tab" value="usuarios">
            <input type="hidden" name="id" value="0">
            <input type="hidden" name="avatar_atual" value="assets/img/avatar-default.svg">
            <label>Foto</label><input type="file" name="avatar" accept=".jpg,.jpeg,.png,.webp">
            <label>Nome</label><input type="text" name="nome" required>
            <label>Usuario</label><input type="text" name="usuario" required>
            <label>Email</label><input type="email" name="email" required>
            <label>Senha</label><input type="password" name="senha" minlength="6">
            <label>Titulo</label><input type="text" name="titulo" value="NOVO USUARIO">
            <label><input type="checkbox" name="is_admin" value="1"> Administrador</label>
            <button class="btn btn-primary btn-xl" type="submit">Salvar usuario</button>
        </form>
    </div>
</div>

<div class="modal hidden" id="modal-user-edit">
    <div class="modal-backdrop" data-close-modal></div>
    <div class="modal-content">
        <h3>Editar Usuario</h3>
        <form class="stacked-form" method="post" enctype="multipart/form-data" id="form-edit-user">
            <input type="hidden" name="action" value="save_user">
            <input type="hidden" name="admin_tab" value="usuarios">
            <input type="hidden" name="id" value="0">
            <input type="hidden" name="avatar_atual" value="">
            <img src="assets/img/avatar-default.svg" alt="Avatar do usuario" class="admin-thumb admin-user-preview" data-user-preview>
            <label>Foto</label><input type="file" name="avatar" accept=".jpg,.jpeg,.png,.webp">
            <label>Nome</label><input type="text" name="nome" required>
            <label>Usuario</label><input type="text" name="usuario" required>
            <label>Email</label><input type="email" name="email" required>
            <label>Nova senha</label><input type="password" name="senha" minlength="6" placeholder="Deixe em branco para manter">
            <label>Titulo</label><input type="text" name="titulo" value="NOVO USUARIO">
            <label><input type="checkbox" name="is_admin" value="1"> Administrador</label>
            <button class="btn btn-primary btn-xl" type="submit">Atualizar usuario</button>
        </form>
    </div>
</div>

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
