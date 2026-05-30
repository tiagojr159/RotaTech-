<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';
requireAdmin();

$users = readJson('users.json');
$programacao = readJson('programacao.json');
$albumFotos = readJson('album_fotos.json');
$roteiros = readJson('roteiros.json');
$grupos = readJson('grupos.json');
$convites = readJson('convites.json');

$report = buildAdminReportData($users, $albumFotos, $programacao, $roteiros, $grupos, $convites);

$lines = [];
$lines[] = 'Relatorio do Publico - RotaTech Arcoverde';
$lines[] = 'Gerado em: ' . date('d/m/Y H:i');
$lines[] = '';
$lines[] = 'Resumo geral';
$lines[] = 'Usuarios cadastrados: ' . (int) $report['usuariosAtivos'];
$lines[] = 'Administradores: ' . (int) $report['usuariosAdmin'];
$lines[] = 'Usuarios engajados na plataforma: ' . (int) $report['usuariosEngajados'];
$lines[] = 'Usuarios com favoritos: ' . (int) $report['usuariosComFavoritos'];
$lines[] = 'Usuarios com roteiro: ' . (int) $report['usuariosComRoteiro'];
$lines[] = 'Usuarios que enviaram fotos: ' . (int) $report['usuariosComFotos'];
$lines[] = 'Fotos no album: ' . (int) $report['fotosAlbumTotal'];
$lines[] = 'Roteiros ativos: ' . (int) $report['roteirosAtivos'];
$lines[] = 'Itens compartilhados de roteiro: ' . (int) $report['roteirosCompartilhados'];
$lines[] = 'Grupos ativos: ' . (int) $report['gruposAtivos'];
$lines[] = 'Convites pendentes: ' . (int) $report['convitesPendentes'];
$lines[] = 'Engajamento medio estimado: ' . (int) $report['engajamentoMedio'] . '%';
$lines[] = 'Figurinhas coletadas pela base: ' . (int) $report['figurinhasColetadas'];
$lines[] = '';
$lines[] = 'Participacao por lotacao';
$lines[] = 'Alta lotacao: ' . (int) ($report['eventosPorLotacao']['alta_lotacao'] ?? 0);
$lines[] = 'Movimento moderado: ' . (int) ($report['eventosPorLotacao']['movimento_moderado'] ?? 0);
$lines[] = 'Pouco movimento: ' . (int) ($report['eventosPorLotacao']['pouco_movimento'] ?? 0);
$lines[] = '';
$lines[] = 'Top palcos';
foreach ($report['topPalcos'] as $palco => $score) {
    $lines[] = '- ' . (string) $palco . ': ' . (int) $score . ' pts';
}

$lines[] = '';
$lines[] = 'Top eventos por interesse';
foreach ($report['topEventos'] as $evento) {
    $lines[] = '- ' . (string) $evento['artista'] . ' | ' . (string) $evento['palco'] . ' | score ' . (int) $evento['score'];
}

$lines[] = '';
$lines[] = 'Usuarios cadastrados';
foreach ($report['usuariosDetalhados'] as $user) {
    $lines[] = (string) $user['nome'] . ' (@' . (string) $user['usuario'] . ')';
    $lines[] = '  Email: ' . (string) $user['email'];
    $lines[] = '  Titulo: ' . ((string) $user['titulo'] !== '' ? (string) $user['titulo'] : 'Sem titulo');
    $lines[] = '  Cadastro: ' . ((string) $user['criado_em'] !== '' ? (string) $user['criado_em'] : 'Nao informado');
    $lines[] = '  Favoritos: ' . (int) $user['favoritos'] . ' | Figurinhas: ' . (int) $user['figurinhas'] . ' | Roteiro: ' . ($user['tem_roteiro'] ? 'Sim' : 'Nao') . ' | Foto: ' . ($user['enviou_foto'] ? 'Sim' : 'Nao') . ' | Score: ' . (int) $user['engajamento'];
    $lines[] = '  Perfil: ' . ($user['is_admin'] ? 'Administrador' : 'Usuario');
}

$lines[] = '';
$lines[] = 'Eventos monitorados';
foreach ($report['eventosRelatorio'] as $evento) {
    $lines[] = (string) $evento['artista'] . ' | ' . (string) $evento['palco'] . ' | favoritos ' . (int) $evento['favoritos'] . ' | roteiros ' . (int) $evento['roteiros'] . ' | ' . (string) $evento['lotacao'] . ' | score ' . (int) $evento['score'];
}

outputSimplePdf('relatorio-publico-rotatech.pdf', $lines);
