<?php
declare(strict_types=1);

if (!defined('APP_INIT')) {
    http_response_code(403);
    exit('Acesso negado.');
}

function dataFile(string $file): string
{
    return DATA_PATH . DIRECTORY_SEPARATOR . ltrim($file, DIRECTORY_SEPARATOR);
}

function readJson($file): array
{
    $path = str_contains((string) $file, DIRECTORY_SEPARATOR) ? (string) $file : dataFile((string) $file);
    if (!file_exists($path)) {
        return [];
    }

    $content = file_get_contents($path);
    if ($content === false || trim($content) === '') {
        return [];
    }

    // Accept UTF-8 JSON files saved with BOM by Windows editors/PowerShell.
    if (strncmp($content, "\xEF\xBB\xBF", 3) === 0) {
        $content = substr($content, 3);
    }

    $decoded = json_decode($content, true);
    return is_array($decoded) ? $decoded : [];
}

function writeJson($file, $data): bool
{
    $path = str_contains((string) $file, DIRECTORY_SEPARATOR) ? (string) $file : dataFile((string) $file);
    $encoded = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    if ($encoded === false) {
        return false;
    }

    return file_put_contents($path, $encoded, LOCK_EX) !== false;
}

function sanitize($value): string
{
    return htmlspecialchars(trim((string) $value), ENT_QUOTES, 'UTF-8');
}

function appUrl(string $path = '', array $query = []): string
{
    $base = rtrim(APP_BASE_URL, '/');
    $url = $path === '' ? $base . '/' : $base . '/' . ltrim($path, '/');

    if ($query !== []) {
        $url .= '?' . http_build_query($query);
    }

    return $url;
}

function generateId(): int
{
    return (int) ((microtime(true) * 1000) + random_int(1, 999));
}

function currentUser(): ?array
{
    $userId = $_SESSION['user_id'] ?? null;
    if (!$userId) {
        return null;
    }

    $users = readJson('users.json');
    foreach ($users as $user) {
        if ((int) $user['id'] === (int) $userId) {
            return $user;
        }
    }

    return null;
}

function requireLogin(): void
{
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit;
    }
}

function isMasterUser(?array $user): bool
{
    if (!$user) return false;
    return mb_strtolower((string) ($user['email'] ?? '')) === mb_strtolower(MASTER_USER_EMAIL);
}

function isAdminUser(?array $user): bool
{
    if (!$user) return false;
    if (isMasterUser($user)) return true;
    return !empty($user['is_admin']);
}

function requireAdmin(): void
{
    requireLogin();
    if (!isAdminUser(currentUser())) {
        header('Location: ' . appUrl());
        exit;
    }
}

function uploadImage($fieldName): ?string
{
    if (empty($_FILES[$fieldName]['name'])) {
        return null;
    }

    $file = $_FILES[$fieldName];
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        return null;
    }

    if (($file['size'] ?? 0) > 5 * 1024 * 1024) {
        return null;
    }

    $allowed = ['jpg', 'jpeg', 'png', 'webp'];
    $ext = strtolower(pathinfo((string) $file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed, true)) {
        return null;
    }

    $filename = 'upload_' . time() . '_' . random_int(1000, 9999) . '.' . $ext;
    $target = UPLOADS_PATH . DIRECTORY_SEPARATOR . $filename;

    if (!move_uploaded_file($file['tmp_name'], $target)) {
        return null;
    }

    return 'uploads/' . $filename;
}

function jsonResponse($data, $status = 200): void
{
    http_response_code((int) $status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function userNameById(int $id): string
{
    $users = readJson('users.json');
    foreach ($users as $user) {
        if ((int) $user['id'] === $id) {
            return (string) $user['nome'];
        }
    }
    return 'Visitante';
}

function userAvatarById(int $id): string
{
    $users = readJson('users.json');
    foreach ($users as $user) {
        if ((int) $user['id'] === $id) {
            return (string) $user['avatar'];
        }
    }
    return 'assets/img/avatar-default.svg';
}

function statusLabel(string $status): string
{
    if ($status === 'ao_vivo') {
        return 'AO VIVO';
    }
    if ($status === 'proxima_atracao') {
        return 'PROXIMA ATRACAO';
    }
    if ($status === 'finalizado') {
        return 'FINALIZADO';
    }
    return 'EM BREVE';
}

function lotacaoLabel(string $lotacao): string
{
    if ($lotacao === 'pouco_movimento') {
        return 'Pouco movimento';
    }
    if ($lotacao === 'movimento_moderado') {
        return 'Movimento moderado';
    }
    if ($lotacao === 'alta_lotacao') {
        return 'Alta lotacao';
    }
    return 'Fluxo normal';
}

