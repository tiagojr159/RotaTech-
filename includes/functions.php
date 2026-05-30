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

function normalizeUploadedPhotoOrientation($image, string $filePath, string $ext)
{
    if (!function_exists('exif_read_data')) {
        return $image;
    }

    if (!in_array($ext, ['jpg', 'jpeg'], true)) {
        return $image;
    }

    $exif = @exif_read_data($filePath);
    $orientation = (int) ($exif['Orientation'] ?? 1);

    $rotated = $image;

    switch ($orientation) {
        case 2:
            imageflip($rotated, IMG_FLIP_HORIZONTAL);
            break;
        case 3:
            $candidate = imagerotate($rotated, 180, 0);
            if ($candidate !== false) {
                $rotated = $candidate;
            }
            break;
        case 4:
            imageflip($rotated, IMG_FLIP_VERTICAL);
            break;
        case 5:
            imageflip($rotated, IMG_FLIP_VERTICAL);
            $candidate = imagerotate($rotated, -90, 0);
            if ($candidate !== false) {
                $rotated = $candidate;
            }
            break;
        case 6:
            $candidate = imagerotate($rotated, -90, 0);
            if ($candidate !== false) {
                $rotated = $candidate;
            }
            break;
        case 7:
            imageflip($rotated, IMG_FLIP_HORIZONTAL);
            $candidate = imagerotate($rotated, -90, 0);
            if ($candidate !== false) {
                $rotated = $candidate;
            }
            break;
        case 8:
            $candidate = imagerotate($rotated, 90, 0);
            if ($candidate !== false) {
                $rotated = $candidate;
            }
            break;
    }

    return $rotated;
}

function detectFramePhotoWindow($frameImage): array
{
    $width = imagesx($frameImage);
    $height = imagesy($frameImage);
    $minX = $width;
    $minY = $height;
    $maxX = -1;
    $maxY = -1;

    for ($y = 0; $y < $height; $y++) {
        for ($x = 0; $x < $width; $x++) {
            $rgba = imagecolorat($frameImage, $x, $y);
            $alpha = ($rgba >> 24) & 0x7F;
            if ($alpha >= 110) {
                if ($x < $minX) {
                    $minX = $x;
                }
                if ($y < $minY) {
                    $minY = $y;
                }
                if ($x > $maxX) {
                    $maxX = $x;
                }
                if ($y > $maxY) {
                    $maxY = $y;
                }
            }
        }
    }

    if ($maxX < 0 || $maxY < 0) {
        return ['x' => 0, 'y' => 0, 'width' => $width, 'height' => $height];
    }

    return [
        'x' => $minX,
        'y' => $minY,
        'width' => max(1, $maxX - $minX + 1),
        'height' => max(1, $maxY - $minY + 1),
    ];
}

function encodeCompositeImageToSize($image, string $targetBaseName, int $maxBytes = 204800): ?string
{
    $supportsWebp = function_exists('imagewebp');
    $extension = $supportsWebp ? 'webp' : 'jpg';
    $target = UPLOADS_PATH . DIRECTORY_SEPARATOR . $targetBaseName . '.' . $extension;
    $working = $image;
    $width = imagesx($working);
    $height = imagesy($working);

    while (true) {
        for ($quality = 82; $quality >= 30; $quality -= 6) {
            ob_start();
            $saved = $supportsWebp ? imagewebp($working, null, $quality) : imagejpeg($working, null, $quality);
            $binary = ob_get_clean();

            if (!$saved || $binary === false) {
                continue;
            }

            if (strlen($binary) <= $maxBytes || $quality <= 36) {
                if (file_put_contents($target, $binary, LOCK_EX) === false) {
                    if ($working !== $image) {
                        imagedestroy($working);
                    }
                    return null;
                }

                if ($working !== $image) {
                    imagedestroy($working);
                }

                return 'uploads/' . basename($target);
            }
        }

        if ($width <= 840 || $height <= 1050) {
            break;
        }

        $width = (int) floor($width * 0.88);
        $height = (int) floor($height * 0.88);
        $resized = imagecreatetruecolor($width, $height);
        imagecopyresampled($resized, $working, 0, 0, 0, 0, $width, $height, imagesx($working), imagesy($working));

        if ($working !== $image) {
            imagedestroy($working);
        }
        $working = $resized;
    }

    ob_start();
    $saved = $supportsWebp ? imagewebp($working, null, 28) : imagejpeg($working, null, 28);
    $binary = ob_get_clean();

    if ($working !== $image) {
        imagedestroy($working);
    }

    if (!$saved || $binary === false || file_put_contents($target, $binary, LOCK_EX) === false) {
        return null;
    }

    return 'uploads/' . basename($target);
}

function uploadAlbumPhotoWithRandomFrame(string $fieldName): ?string
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

    $frameFiles = glob(BASE_PATH . DIRECTORY_SEPARATOR . 'filtros' . DIRECTORY_SEPARATOR . '*.png') ?: [];
    if ($frameFiles === []) {
        return uploadImage($fieldName);
    }

    if (!function_exists('imagecreatefromstring')) {
        return uploadImage($fieldName);
    }

    $sourceBinary = @file_get_contents($file['tmp_name']);
    if ($sourceBinary === false) {
        return null;
    }

    $framePath = $frameFiles[array_rand($frameFiles)];
    $frameBinary = @file_get_contents($framePath);
    if ($frameBinary === false) {
        return null;
    }

    $sourceImage = @imagecreatefromstring($sourceBinary);
    $frameImage = @imagecreatefromstring($frameBinary);
    if (!$sourceImage || !$frameImage) {
        if ($sourceImage) {
            imagedestroy($sourceImage);
        }
        if ($frameImage) {
            imagedestroy($frameImage);
        }
        return null;
    }

    $sourceImage = normalizeUploadedPhotoOrientation($sourceImage, (string) $file['tmp_name'], $ext);

    $frameWidth = imagesx($frameImage);
    $frameHeight = imagesy($frameImage);
    $sourceWidth = imagesx($sourceImage);
    $sourceHeight = imagesy($sourceImage);

    if ($frameWidth < 1 || $frameHeight < 1 || $sourceWidth < 1 || $sourceHeight < 1) {
        imagedestroy($sourceImage);
        imagedestroy($frameImage);
        return null;
    }

    $photoWindow = detectFramePhotoWindow($frameImage);
    $canvas = imagecreatetruecolor($frameWidth, $frameHeight);
    $background = imagecolorallocate($canvas, 248, 241, 232);
    imagefill($canvas, 0, 0, $background);

    $destinationRatio = $photoWindow['width'] / $photoWindow['height'];
    $sourceRatio = $sourceWidth / $sourceHeight;

    if ($sourceRatio > $destinationRatio) {
        $cropHeight = $sourceHeight;
        $cropWidth = (int) round($sourceHeight * $destinationRatio);
        $cropX = (int) floor(($sourceWidth - $cropWidth) / 2);
        $cropY = 0;
    } else {
        $cropWidth = $sourceWidth;
        $cropHeight = (int) round($sourceWidth / $destinationRatio);
        $cropX = 0;
        $cropY = (int) floor(($sourceHeight - $cropHeight) / 2);
    }

    imagecopyresampled(
        $canvas,
        $sourceImage,
        $photoWindow['x'],
        $photoWindow['y'],
        $cropX,
        $cropY,
        $photoWindow['width'],
        $photoWindow['height'],
        $cropWidth,
        $cropHeight
    );

    imagealphablending($canvas, true);
    imagecopy($canvas, $frameImage, 0, 0, 0, 0, $frameWidth, $frameHeight);

    $targetBaseName = 'album_' . time() . '_' . random_int(1000, 9999);
    $savedPath = encodeCompositeImageToSize($canvas, $targetBaseName);

    imagedestroy($canvas);
    imagedestroy($sourceImage);
    imagedestroy($frameImage);

    return $savedPath;
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

