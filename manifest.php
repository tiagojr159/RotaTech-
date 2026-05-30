<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/config.php';

header('Content-Type: application/manifest+json; charset=utf-8');

echo json_encode([
    'name' => APP_NAME,
    'short_name' => 'RotaTech',
    'id' => APP_BASE_URL . '?source=pwa-v4',
    'start_url' => APP_BASE_URL . '?source=pwa-v4',
    'scope' => APP_BASE_URL,
    'display' => 'standalone',
    'background_color' => '#f8f1e8',
    'theme_color' => '#c96f2b',
    'orientation' => 'portrait',
    'icons' => [
        [
            'src' => APP_BASE_URL . 'icon.png',
            'sizes' => '192x192',
            'type' => 'image/png',
        ],
        [
            'src' => APP_BASE_URL . 'icon.png',
            'sizes' => '512x512',
            'type' => 'image/png',
        ],
    ],
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
