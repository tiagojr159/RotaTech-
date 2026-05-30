<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['ok' => false, 'message' => 'Método não permitido'], 405);
}

$action = sanitize($_POST['action'] ?? '');

if ($action === 'login') {
    $login = sanitize($_POST['login'] ?? '');
    $senha = (string) ($_POST['senha'] ?? '');
    $users = readJson('users.json');

    foreach ($users as $user) {
        $matchLogin = mb_strtolower($user['email']) === mb_strtolower($login)
            || mb_strtolower($user['usuario']) === mb_strtolower($login);
        if ($matchLogin && password_verify($senha, $user['senha_hash'])) {
            $_SESSION['user_id'] = $user['id'];
            jsonResponse(['ok' => true, 'redirect' => appUrl(), 'user' => $user]);
        }
    }

    jsonResponse(['ok' => false, 'message' => 'Credenciais inválidas'], 401);
}

if (!isset($_SESSION['user_id'])) {
    jsonResponse(['ok' => false, 'message' => 'Não autenticado'], 401);
}

$current = currentUser();
if (!$current) {
    jsonResponse(['ok' => false, 'message' => 'Sessão inválida'], 401);
}

$users = readJson('users.json');
$userIndex = null;
foreach ($users as $i => $u) {
    if ((int) $u['id'] === (int) $current['id']) {
        $userIndex = $i;
        break;
    }
}

if ($userIndex === null) {
    jsonResponse(['ok' => false, 'message' => 'Usuário não encontrado'], 404);
}

switch ($action) {
    case 'criar_grupo':
        $nome = sanitize($_POST['nome'] ?? '');
        $privacidade = sanitize($_POST['privacidade'] ?? 'publico');
        $convidadosRaw = sanitize($_POST['convidados'] ?? '');
        $convidados = array_values(array_filter(array_map('intval', explode(',', $convidadosRaw))));

        if ($nome === '') {
            jsonResponse(['ok' => false, 'message' => 'Nome do grupo é obrigatório'], 422);
        }

        $grupos = readJson('grupos.json');
        $novoId = generateId();
        $capa = uploadImage('capa') ?? 'assets/img/group-default.svg';
        $codigo = strtoupper(substr(md5((string) $novoId), 0, 7));

        $group = [
            'id' => $novoId,
            'nome' => $nome,
            'capa' => $capa,
            'privacidade' => $privacidade === 'privado' ? 'privado' : 'publico',
            'codigo' => $codigo,
            'vip' => false,
            'membros' => [(int) $current['id']],
            'roteiro' => [],
            'criado_por' => (int) $current['id'],
        ];
        $grupos[] = $group;
        writeJson('grupos.json', $grupos);

        if (!empty($convidados)) {
            $convites = readJson('convites.json');
            foreach ($convidados as $friendId) {
                $convites[] = [
                    'id' => generateId(),
                    'group_id' => $novoId,
                    'from_user_id' => (int) $current['id'],
                    'to_user_id' => (int) $friendId,
                    'status' => 'pendente',
                ];
            }
            writeJson('convites.json', $convites);
        }

        jsonResponse(['ok' => true, 'message' => 'Grupo criado com sucesso!', 'group_id' => $novoId, 'redirect' => 'detalhes-grupo.php?id=' . $novoId]);
        break;

    case 'aceitar_convite':
    case 'recusar_convite':
        $inviteId = (int) ($_POST['invite_id'] ?? 0);
        $status = $action === 'aceitar_convite' ? 'aceito' : 'recusado';
        $convites = readJson('convites.json');
        $grupos = readJson('grupos.json');
        $found = false;

        foreach ($convites as &$convite) {
            if ((int) $convite['id'] === $inviteId && (int) $convite['to_user_id'] === (int) $current['id']) {
                $convite['status'] = $status;
                $found = true;

                if ($status === 'aceito') {
                    foreach ($grupos as &$grupo) {
                        if ((int) $grupo['id'] === (int) $convite['group_id']) {
                            if (!in_array((int) $current['id'], $grupo['membros'], true)) {
                                $grupo['membros'][] = (int) $current['id'];
                            }
                            break;
                        }
                    }
                    unset($grupo);
                }
                break;
            }
        }
        unset($convite);

        if (!$found) {
            jsonResponse(['ok' => false, 'message' => 'Convite não encontrado'], 404);
        }

        writeJson('convites.json', $convites);
        writeJson('grupos.json', $grupos);
        jsonResponse(['ok' => true, 'message' => $status === 'aceito' ? 'Convite aceito!' : 'Convite recusado!']);
        break;

    case 'adicionar_roteiro':
        $tipo = sanitize($_POST['tipo'] ?? 'pessoal');
        $horario = sanitize($_POST['horario'] ?? '');
        $titulo = sanitize($_POST['titulo'] ?? '');
        $local = sanitize($_POST['local'] ?? '');
        $categoria = sanitize($_POST['categoria'] ?? 'show');
        $grupoId = (int) ($_POST['grupo_id'] ?? 0);

        if ($horario === '' || $titulo === '' || $local === '') {
            jsonResponse(['ok' => false, 'message' => 'Preencha horário, título e local'], 422);
        }

        $roteiros = readJson('roteiros.json');
        $targetIdx = null;
        foreach ($roteiros as $i => $roteiro) {
            if ($tipo === 'grupo' && (int) ($roteiro['grupo_id'] ?? 0) === $grupoId && ($roteiro['tipo'] ?? '') === 'grupo') {
                $targetIdx = $i;
                break;
            }
            if ($tipo !== 'grupo' && ($roteiro['tipo'] ?? '') === 'pessoal' && (int) ($roteiro['user_id'] ?? 0) === (int) $current['id']) {
                $targetIdx = $i;
                break;
            }
        }

        if ($targetIdx === null) {
            $roteiros[] = [
                'id' => generateId(),
                'user_id' => $tipo === 'grupo' ? null : (int) $current['id'],
                'tipo' => $tipo === 'grupo' ? 'grupo' : 'pessoal',
                'grupo_id' => $tipo === 'grupo' ? $grupoId : null,
                'itens' => [],
            ];
            $targetIdx = array_key_last($roteiros);
        }

        $roteiros[$targetIdx]['itens'][] = [
            'id' => generateId(),
            'horario' => $horario,
            'titulo' => $titulo,
            'local' => $local,
            'tipo' => $categoria,
            'sugerido_por' => explode(' ', (string) $current['nome'])[0] ?? 'Você',
            'status' => 'pendente',
        ];
        writeJson('roteiros.json', $roteiros);
        jsonResponse(['ok' => true, 'message' => 'Item adicionado ao roteiro']);
        break;

    case 'remover_roteiro':
        $roteiroId = (int) ($_POST['roteiro_id'] ?? 0);
        $itemId = (int) ($_POST['item_id'] ?? 0);
        $roteiros = readJson('roteiros.json');
        $done = false;

        foreach ($roteiros as &$roteiro) {
            if ((int) $roteiro['id'] === $roteiroId && (int) ($roteiro['user_id'] ?? 0) === (int) $current['id']) {
                $roteiro['itens'] = array_values(array_filter($roteiro['itens'], fn($item) => (int) $item['id'] !== $itemId));
                $done = true;
                break;
            }
        }
        unset($roteiro);

        if (!$done) {
            jsonResponse(['ok' => false, 'message' => 'Item não encontrado'], 404);
        }

        writeJson('roteiros.json', $roteiros);
        jsonResponse(['ok' => true, 'message' => 'Item removido']);
        break;

    case 'coletar_figurinha':
        $stickerId = (int) ($_POST['sticker_id'] ?? 0);
        $figurinhas = readJson('figurinhas.json');
        $myStickers = $users[$userIndex]['figurinhas'] ?? [];

        if ($stickerId === 0) {
            foreach ($figurinhas as $fig) {
                if (!in_array((int) $fig['id'], $myStickers, true)) {
                    $stickerId = (int) $fig['id'];
                    break;
                }
            }
        }

        if ($stickerId === 0) {
            jsonResponse(['ok' => false, 'message' => 'Todas as figurinhas já foram coletadas']);
        }

        if (!in_array($stickerId, $myStickers, true)) {
            $myStickers[] = $stickerId;
            $users[$userIndex]['figurinhas'] = $myStickers;
            $users[$userIndex]['pontos'] = (int) ($users[$userIndex]['pontos'] ?? 0) + 40;
            writeJson('users.json', $users);
        }

        foreach ($figurinhas as &$fig) {
            if ((int) $fig['id'] === $stickerId) {
                $fig['desbloqueada'] = true;
                $fig['progresso'] = 100;
                break;
            }
        }
        unset($fig);
        writeJson('figurinhas.json', $figurinhas);

        jsonResponse(['ok' => true, 'message' => 'Figurinha coletada!', 'sticker_id' => $stickerId]);
        break;

    case 'upload_album_photo':
        $imagem = uploadImage('album_photo');
        if ($imagem === null) {
            jsonResponse(['ok' => false, 'message' => 'Selecione uma imagem valida para enviar.'], 422);
        }

        $albumFotos = readJson('album_fotos.json');
        $albumFotos[] = [
            'id' => generateId(),
            'user_id' => (int) $current['id'],
            'user_name' => (string) ($current['nome'] ?? 'Visitante'),
            'user_avatar' => (string) ($current['avatar'] ?? 'assets/img/avatar-default.svg'),
            'imagem' => $imagem,
            'created_at' => date('d/m/Y H:i'),
        ];
        if (count($albumFotos) > 50) {
            $albumFotos = array_slice($albumFotos, -50);
        }
        writeJson('album_fotos.json', $albumFotos);
        jsonResponse(['ok' => true, 'message' => 'Foto enviada para o album!', 'imagem' => $imagem]);
        break;

    case 'atualizar_perfil':
        $nome = sanitize($_POST['nome'] ?? $users[$userIndex]['nome']);
        $usuario = sanitize($_POST['usuario'] ?? $users[$userIndex]['usuario']);
        $titulo = sanitize($_POST['titulo'] ?? $users[$userIndex]['titulo']);
        $avatar = uploadImage('avatar');

        $users[$userIndex]['nome'] = $nome;
        $users[$userIndex]['usuario'] = $usuario;
        $users[$userIndex]['titulo'] = $titulo;
        if ($avatar !== null) {
            $users[$userIndex]['avatar'] = $avatar;
        }
        writeJson('users.json', $users);
        jsonResponse(['ok' => true, 'message' => 'Perfil atualizado com sucesso']);
        break;

    case 'entrar_com_codigo':
        $codigo = strtoupper(sanitize($_POST['codigo'] ?? ''));
        $grupos = readJson('grupos.json');
        $found = false;

        foreach ($grupos as &$grupo) {
            if (strtoupper((string) $grupo['codigo']) === $codigo) {
                if (!in_array((int) $current['id'], $grupo['membros'], true)) {
                    $grupo['membros'][] = (int) $current['id'];
                }
                $found = true;
                $foundId = (int) $grupo['id'];
                break;
            }
        }
        unset($grupo);

        if (!$found) {
            jsonResponse(['ok' => false, 'message' => 'Código inválido'], 404);
        }
        writeJson('grupos.json', $grupos);
        jsonResponse(['ok' => true, 'message' => 'Você entrou no grupo!', 'redirect' => 'detalhes-grupo.php?id=' . $foundId]);
        break;

    case 'favoritar_atracao':
        $atracaoId = (int) ($_POST['atracao_id'] ?? 0);
        $favoritos = $users[$userIndex]['favoritos'] ?? [];

        if (in_array($atracaoId, $favoritos, true)) {
            $favoritos = array_values(array_filter($favoritos, fn($id) => (int) $id !== $atracaoId));
            $state = 'removido';
        } else {
            $favoritos[] = $atracaoId;
            $state = 'adicionado';
        }

        $users[$userIndex]['favoritos'] = $favoritos;
        writeJson('users.json', $users);
        jsonResponse(['ok' => true, 'message' => $state === 'adicionado' ? 'Atração favoritada!' : 'Atração removida dos favoritos', 'state' => $state]);
        break;

    default:
        jsonResponse(['ok' => false, 'message' => 'Ação inválida'], 400);
}
