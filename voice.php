<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';
requireLogin();

use Afaya\EdgeTTS\Service\EdgeTTS;

if (!file_exists(__DIR__ . '/vendor/autoload.php')) {
    http_response_code(500);
    exit('Modulo de voz nao encontrado.');
}

require_once __DIR__ . '/vendor/autoload.php';

function voiceSafeText(string $text): string
{
    $text = trim(preg_replace('/\s+/', ' ', $text));
    return html_entity_decode($text, ENT_QUOTES, 'UTF-8');
}

function buildProgramacaoVoiceText(): string
{
    $programacao = array_slice(readJson('programacao.json'), 0, 3);
    if (empty($programacao)) {
        return 'Ainda nao ha eventos cadastrados na programacao.';
    }

    $partes = [];
    foreach ($programacao as $evento) {
        $partes[] = trim(($evento['artista'] ?? 'Atracao') . ' as ' . ($evento['horario'] ?? '--:--') . ' no ' . ($evento['palco'] ?? 'palco principal'));
    }

    return 'Agora na programacao do Sao Joao de Arcoverde: ' . implode('. ', $partes) . '.';
}

function buildRoteiroVoiceText(array $user, string $scope): string
{
    $roteiros = readJson('roteiros.json');
    $grupos = readJson('grupos.json');
    $myGroupId = 0;

    foreach ($grupos as $grupo) {
        if (in_array((int) $user['id'], $grupo['membros'] ?? [], true)) {
            $myGroupId = (int) ($grupo['id'] ?? 0);
            break;
        }
    }

    foreach ($roteiros as $roteiro) {
        $tipo = (string) ($roteiro['tipo'] ?? '');
        if ($scope === 'grupo' && $tipo === 'grupo' && (int) ($roteiro['grupo_id'] ?? 0) === $myGroupId) {
            $itens = $roteiro['itens'] ?? [];
            if (empty($itens)) {
                return 'O roteiro do grupo ainda nao possui paradas cadastradas.';
            }

            $partes = [];
            foreach ($itens as $item) {
                $partes[] = trim(($item['titulo'] ?? 'Parada') . ' as ' . ($item['horario'] ?? '--:--') . ' em ' . ($item['local'] ?? 'Arcoverde'));
            }

            return 'Este e o roteiro do grupo: ' . implode('. ', $partes) . '.';
        }

        if ($scope !== 'grupo' && $tipo === 'pessoal' && (int) ($roteiro['user_id'] ?? 0) === (int) $user['id']) {
            $itens = $roteiro['itens'] ?? [];
            if (empty($itens)) {
                return 'Seu roteiro ainda nao possui paradas cadastradas.';
            }

            $partes = [];
            foreach ($itens as $item) {
                $partes[] = trim(($item['titulo'] ?? 'Parada') . ' as ' . ($item['horario'] ?? '--:--') . ' em ' . ($item['local'] ?? 'Arcoverde'));
            }

            return 'Este e o seu roteiro: ' . implode('. ', $partes) . '.';
        }
    }

    return $scope === 'grupo'
        ? 'O roteiro do grupo ainda nao foi criado.'
        : 'Voce ainda nao criou um roteiro pessoal.';
}

$context = (string) ($_GET['context'] ?? '');
$scope = (string) ($_GET['scope'] ?? 'pessoal');
$user = currentUser();

if (!$user) {
    http_response_code(401);
    exit('Sessao expirada.');
}

if ($context === 'programacao') {
    $text = buildProgramacaoVoiceText();
} elseif ($context === 'roteiro') {
    $text = buildRoteiroVoiceText($user, $scope);
} else {
    http_response_code(400);
    exit('Contexto invalido.');
}

$text = voiceSafeText($text);
$ttsDir = UPLOADS_PATH . DIRECTORY_SEPARATOR . 'tts';
if (!is_dir($ttsDir)) {
    mkdir($ttsDir, 0775, true);
}

$cacheKey = md5($context . '|' . $scope . '|' . (string) $user['id'] . '|' . $text);
$baseFile = $ttsDir . DIRECTORY_SEPARATOR . $cacheKey;
$audioFile = $baseFile . '.mp3';

if (!file_exists($audioFile)) {
    $tts = new EdgeTTS();
    $tts->synthesize($text, 'pt-BR-FranciscaNeural', [
        'rate' => '-6%',
        'volume' => '+0%',
        'pitch' => '+0Hz',
    ]);
    $tts->toFile($baseFile);
}

if (!file_exists($audioFile)) {
    http_response_code(500);
    exit('Nao foi possivel gerar o audio.');
}

header('Content-Type: audio/mpeg');
header('Content-Length: ' . (string) filesize($audioFile));
header('Cache-Control: public, max-age=86400');
readfile($audioFile);
exit;
