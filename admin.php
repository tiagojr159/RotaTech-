<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';
requireAdmin();

$current = currentUser();
$isMaster = isMasterUser($current);
$message = '';
$error = '';

$users = readJson('users.json');
$restaurantes = readJson('restaurantes.json');
$figurinhas = readJson('figurinhas.json');
$roteiros = readJson('roteiros.json');
$hospedagens = readJson('hospedagens.json');
$programacao = readJson('programacao.json');

$nextId = static function (array $rows): int {
    $max = 0;
    foreach ($rows as $row) {
        $id = (int) ($row['id'] ?? 0);
        if ($id > $max) $max = $id;
    }
    return $max + 1;
};

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = sanitize($_POST['action'] ?? '');

    if ($action === 'create_user') {
        $nome = sanitize($_POST['nome'] ?? '');
        $usuario = sanitize($_POST['usuario'] ?? '');
        $email = sanitize($_POST['email'] ?? '');
        $senha = (string) ($_POST['senha'] ?? '');
        $setAdmin = $isMaster && !empty($_POST['is_admin']);

        if ($nome === '' || $usuario === '' || $email === '' || $senha === '') {
            $error = 'Preencha todos os dados do usuario.';
        } else {
            $exists = false;
            foreach ($users as $u) {
                if (mb_strtolower((string) $u['email']) === mb_strtolower($email) || mb_strtolower((string) $u['usuario']) === mb_strtolower($usuario)) {
                    $exists = true;
                    break;
                }
            }
            if ($exists) {
                $error = 'Email ou usuario ja cadastrado.';
            } else {
                $users[] = [
                    'id' => generateId(),
                    'nome' => $nome,
                    'usuario' => $usuario,
                    'email' => $email,
                    'senha_hash' => password_hash($senha, PASSWORD_DEFAULT),
                    'avatar' => 'assets/img/avatar-default.svg',
                    'nivel' => 1,
                    'titulo' => 'NOVO USUARIO',
                    'pontos' => 0,
                    'criado_em' => date('Y-m-d'),
                    'favoritos' => [],
                    'figurinhas' => [1],
                    'is_admin' => $setAdmin,
                ];
                writeJson('users.json', $users);
                $message = 'Usuario cadastrado com sucesso.';
            }
        }
    }

    if ($action === 'set_admin' && $isMaster) {
        $targetId = (int) ($_POST['user_id'] ?? 0);
        foreach ($users as &$u) {
            if ((int) ($u['id'] ?? 0) === $targetId) {
                if (mb_strtolower((string) ($u['email'] ?? '')) !== mb_strtolower(MASTER_USER_EMAIL)) {
                    $u['is_admin'] = true;
                }
                break;
            }
        }
        unset($u);
        writeJson('users.json', $users);
        $message = 'Usuario promovido para admin.';
    }

    if ($action === 'add_restaurante') {
        $nome = sanitize($_POST['nome'] ?? '');
        $categoria = sanitize($_POST['categoria'] ?? '');
        $distancia = sanitize($_POST['distancia'] ?? '');
        $avaliacao = (float) ($_POST['avaliacao'] ?? 4.5);
        $faixa = sanitize($_POST['faixa_preco'] ?? '$$');
        $aberto = sanitize($_POST['aberto_ate'] ?? '23:00');
        $prato = sanitize($_POST['prato_destaque'] ?? '');
        $preco = sanitize($_POST['preco_prato'] ?? '');
        $descricao = sanitize($_POST['descricao'] ?? '');
        if ($nome === '' || $categoria === '') {
            $error = 'Informe nome e categoria do restaurante.';
        } else {
            $restaurantes[] = [
                'id' => $nextId($restaurantes),
                'nome' => $nome,
                'categoria' => $categoria,
                'distancia' => $distancia !== '' ? $distancia : '0.0km',
                'avaliacao' => $avaliacao,
                'faixa_preco' => $faixa,
                'aberto_ate' => $aberto,
                'imagem' => 'assets/img/rest-casarao.svg',
                'prato_destaque' => $prato !== '' ? $prato : 'Prato da casa',
                'preco_prato' => $preco !== '' ? $preco : 'R$ 0,00',
                'descricao' => $descricao !== '' ? $descricao : 'Sem descricao.',
                'lotacao' => 'movimento_moderado',
            ];
            writeJson('restaurantes.json', $restaurantes);
            $message = 'Restaurante cadastrado.';
        }
    }

    if ($action === 'add_hospedagem') {
        $nome = sanitize($_POST['nome'] ?? '');
        $categoria = sanitize($_POST['categoria'] ?? 'hotel');
        $endereco = sanitize($_POST['endereco'] ?? '');
        $cidade = sanitize($_POST['cidade'] ?? 'Arcoverde, Pernambuco');
        $lat = (float) ($_POST['latitude'] ?? 0);
        $lng = (float) ($_POST['longitude'] ?? 0);
        if ($nome === '' || $endereco === '') {
            $error = 'Informe nome e endereco da hospedagem.';
        } else {
            $hospedagens[] = [
                'id' => $nextId($hospedagens),
                'nome' => $nome,
                'categoria' => $categoria === 'pousada' ? 'pousada' : 'hotel',
                'endereco' => $endereco,
                'cidade' => $cidade,
                'latitude' => $lat,
                'longitude' => $lng,
            ];
            writeJson('hospedagens.json', $hospedagens);
            $message = 'Hospedagem cadastrada.';
        }
    }

    if ($action === 'add_evento') {
        $artista = sanitize($_POST['artista'] ?? '');
        $palco = sanitize($_POST['palco'] ?? '');
        $data = sanitize($_POST['data'] ?? '');
        $horario = sanitize($_POST['horario'] ?? '');
        $categoria = sanitize($_POST['categoria'] ?? '');
        $descricao = sanitize($_POST['descricao'] ?? '');
        if ($artista === '' || $palco === '' || $data === '' || $horario === '') {
            $error = 'Preencha artista, palco, data e horario.';
        } else {
            $programacao[] = [
                'id' => $nextId($programacao),
                'artista' => $artista,
                'palco' => $palco,
                'data' => $data,
                'horario' => $horario,
                'status' => 'em_breve',
                'imagem' => 'assets/img/atracao-alceu.svg',
                'categoria' => $categoria !== '' ? $categoria : 'Show',
                'descricao' => $descricao !== '' ? $descricao : 'Programacao cadastrada pelo admin.',
                'lotacao' => 'movimento_moderado',
            ];
            writeJson('programacao.json', $programacao);
            $message = 'Evento cadastrado na programacao.';
        }
    }

    if ($action === 'add_album') {
        $titulo = sanitize($_POST['titulo'] ?? '');
        $descricao = sanitize($_POST['descricao'] ?? '');
        $categoria = sanitize($_POST['categoria'] ?? 'conquista');
        if ($titulo === '') {
            $error = 'Informe o titulo do item do album.';
        } else {
            $figurinhas[] = [
                'id' => $nextId($figurinhas),
                'titulo' => $titulo,
                'descricao' => $descricao !== '' ? $descricao : 'Item de album adicionado pelo admin.',
                'imagem' => 'assets/img/sticker-vivi.svg',
                'categoria' => $categoria,
                'desbloqueada' => false,
                'progresso' => 0,
            ];
            writeJson('figurinhas.json', $figurinhas);
            $message = 'Item do album cadastrado.';
        }
    }

    if ($action === 'add_roteiro') {
        $userId = (int) ($_POST['user_id'] ?? 0);
        $horario = sanitize($_POST['horario'] ?? '');
        $titulo = sanitize($_POST['titulo'] ?? '');
        $local = sanitize($_POST['local'] ?? '');
        $tipo = sanitize($_POST['tipo'] ?? 'show');
        if ($userId <= 0 || $horario === '' || $titulo === '' || $local === '') {
            $error = 'Preencha usuario, horario, titulo e local do roteiro.';
        } else {
            $targetIndex = null;
            foreach ($roteiros as $idx => $r) {
                if (($r['tipo'] ?? '') === 'pessoal' && (int) ($r['user_id'] ?? 0) === $userId) {
                    $targetIndex = $idx;
                    break;
                }
            }
            if ($targetIndex === null) {
                $roteiros[] = [
                    'id' => generateId(),
                    'user_id' => $userId,
                    'tipo' => 'pessoal',
                    'grupo_id' => null,
                    'itens' => [],
                ];
                $targetIndex = array_key_last($roteiros);
            }
            $roteiros[$targetIndex]['itens'][] = [
                'id' => generateId(),
                'horario' => $horario,
                'titulo' => $titulo,
                'local' => $local,
                'tipo' => $tipo,
                'sugerido_por' => 'Admin',
                'status' => 'pendente',
            ];
            writeJson('roteiros.json', $roteiros);
            $message = 'Roteiro cadastrado para o usuario.';
        }
    }

    $users = readJson('users.json');
}

$showTopBar = true;
$backUrl = 'home.php';
$pageTitle = 'Administrador';
$pageEyebrow = 'painel';
$contentClass = 'admin-content';
include __DIR__ . '/includes/header.php';
?>
<section class="auth-form-page">
    <article class="card card-soft">
        <h3>Painel Administrativo</h3>
        <p>Gerencie usuarios e conteudos do app em um unico lugar.</p>
        <?php if ($message): ?><p class="alert alert-success"><?= sanitize($message); ?></p><?php endif; ?>
        <?php if ($error): ?><p class="alert alert-danger"><?= sanitize($error); ?></p><?php endif; ?>
    </article>

    <form class="card stacked-form" method="post">
        <h4>Cadastrar Usuario</h4>
        <input type="hidden" name="action" value="create_user">
        <label>Nome</label>
        <input type="text" name="nome" required>
        <label>Usuario</label>
        <input type="text" name="usuario" required>
        <label>Email</label>
        <input type="email" name="email" required>
        <label>Senha</label>
        <input type="password" name="senha" required minlength="6">
        <?php if ($isMaster): ?>
            <label><input type="checkbox" name="is_admin" value="1"> Criar como admin</label>
        <?php endif; ?>
        <button class="btn btn-primary btn-xl" type="submit">Salvar Usuario</button>
    </form>

    <?php if ($isMaster): ?>
        <form class="card stacked-form" method="post">
            <h4>Promover Usuario para Admin</h4>
            <input type="hidden" name="action" value="set_admin">
            <label>Usuario</label>
            <select name="user_id" required>
                <?php foreach ($users as $u): ?>
                    <option value="<?= (int) $u['id']; ?>"><?= sanitize($u['nome']); ?> (<?= sanitize($u['email']); ?>)</option>
                <?php endforeach; ?>
            </select>
            <button class="btn btn-primary btn-xl" type="submit">Tornar Admin</button>
        </form>
    <?php endif; ?>

    <form class="card stacked-form" method="post">
        <h4>Cadastrar Restaurante</h4>
        <input type="hidden" name="action" value="add_restaurante">
        <label>Nome</label><input type="text" name="nome" required>
        <label>Categoria</label><input type="text" name="categoria" required>
        <label>Distancia</label><input type="text" name="distancia" placeholder="1.2km">
        <label>Avaliacao</label><input type="number" name="avaliacao" step="0.1" min="0" max="5" value="4.5">
        <label>Faixa de preco</label><input type="text" name="faixa_preco" value="$$">
        <label>Aberto ate</label><input type="time" name="aberto_ate" value="23:00">
        <label>Prato destaque</label><input type="text" name="prato_destaque">
        <label>Preco do prato</label><input type="text" name="preco_prato" placeholder="R$ 35,00">
        <label>Descricao</label><textarea name="descricao" rows="2"></textarea>
        <button class="btn btn-primary btn-xl" type="submit">Salvar Restaurante</button>
    </form>

    <form class="card stacked-form" method="post">
        <h4>Cadastrar Hospedagem</h4>
        <input type="hidden" name="action" value="add_hospedagem">
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
        <button class="btn btn-primary btn-xl" type="submit">Salvar Hospedagem</button>
    </form>

    <form class="card stacked-form" method="post">
        <h4>Cadastrar Evento</h4>
        <input type="hidden" name="action" value="add_evento">
        <label>Artista / Evento</label><input type="text" name="artista" required>
        <label>Palco</label><input type="text" name="palco" required>
        <label>Data</label><input type="date" name="data" required>
        <label>Horario</label><input type="time" name="horario" required>
        <label>Categoria</label><input type="text" name="categoria" placeholder="Show, Danca, Instrumental">
        <label>Descricao</label><textarea name="descricao" rows="2"></textarea>
        <button class="btn btn-primary btn-xl" type="submit">Salvar Evento</button>
    </form>

    <form class="card stacked-form" method="post">
        <h4>Cadastrar Item de Album</h4>
        <input type="hidden" name="action" value="add_album">
        <label>Titulo</label><input type="text" name="titulo" required>
        <label>Categoria</label><input type="text" name="categoria" value="conquista">
        <label>Descricao</label><textarea name="descricao" rows="2"></textarea>
        <button class="btn btn-primary btn-xl" type="submit">Salvar Item</button>
    </form>

    <form class="card stacked-form" method="post">
        <h4>Cadastrar Roteiro</h4>
        <input type="hidden" name="action" value="add_roteiro">
        <label>Usuario</label>
        <select name="user_id" required>
            <?php foreach ($users as $u): ?>
                <option value="<?= (int) $u['id']; ?>"><?= sanitize($u['nome']); ?> (<?= sanitize($u['email']); ?>)</option>
            <?php endforeach; ?>
        </select>
        <label>Horario</label><input type="time" name="horario" required>
        <label>Titulo</label><input type="text" name="titulo" required>
        <label>Local</label><input type="text" name="local" required>
        <label>Tipo</label>
        <select name="tipo">
            <option value="show">Show</option>
            <option value="gastronomia">Gastronomia</option>
            <option value="ponto">Ponto turistico</option>
        </select>
        <button class="btn btn-primary btn-xl" type="submit">Salvar Roteiro</button>
    </form>
</section>

</main>
</div>
</div>
</body>
</html>
