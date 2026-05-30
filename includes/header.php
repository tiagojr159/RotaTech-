<?php
declare(strict_types=1);

if (!defined('APP_INIT')) {
    http_response_code(403);
    exit('Acesso negado.');
}

$pageTitle = $pageTitle ?? 'RotaTech Arcoverde';
$pageEyebrow = $pageEyebrow ?? '';
$bodyClass = $bodyClass ?? '';
$pageClass = $pageClass ?? '';
$showTopBar = $showTopBar ?? true;
$backUrl = $backUrl ?? null;
$rightHtml = $rightHtml ?? '';
$contentClass = $contentClass ?? '';
$current = currentUser();
$avatar = $current['avatar'] ?? 'assets/img/avatar-default.svg';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#c96f2b">
    <title><?= sanitize($pageTitle); ?> - RotaTech</title>
    <base href="<?= sanitize(APP_BASE_URL); ?>">
    <link rel="manifest" href="<?= sanitize(APP_BASE_URL); ?>manifest.json?v=1.0.3">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@600;700;800&family=Nunito+Sans:opsz,wght@6..12,400;6..12,500;6..12,600;6..12,700;6..12,800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer">
    <link rel="stylesheet" href="assets/css/style.css?v=1.0.2">
    <script>
        window.APP_BASE_URL = "<?= sanitize(APP_BASE_URL); ?>";
        window.APP_ABSOLUTE_URL = "<?= sanitize(APP_ABSOLUTE_URL); ?>";
    </script>
    <script defer src="assets/js/app.js?v=1.0.1"></script>
</head>
<body class="<?= sanitize($bodyClass); ?>">
<div class="app-shell">
    <div class="mobile-app <?= sanitize($pageClass); ?>">
        <?php if ($showTopBar): ?>
            <header class="top-bar">
                <div class="top-bar-left">
                    <?php if ($backUrl): ?>
                        <a href="<?= sanitize($backUrl); ?>" class="icon-btn" aria-label="Voltar">
                            <i class="fa-solid fa-arrow-left"></i>
                        </a>
                    <?php endif; ?>
                    <div>
                        <?php if ($pageEyebrow !== ''): ?>
                            <p class="eyebrow"><?= sanitize($pageEyebrow); ?></p>
                        <?php endif; ?>
                        <h1 class="page-title"><?= sanitize($pageTitle); ?></h1>
                    </div>
                </div>
                <div class="top-bar-right">
                    <?php if ($rightHtml !== ''): ?>
                        <?= $rightHtml; ?>
                    <?php else: ?>
                        <?php if (isAdminUser($current)): ?>
                            <a href="admin.php" class="icon-btn soft" aria-label="Administrador">
                                <i class="fa-solid fa-shield-halved"></i>
                            </a>
                        <?php endif; ?>
                        <a href="perfil.php" class="avatar-link" aria-label="Perfil">
                            <img src="<?= sanitize($avatar); ?>" alt="Avatar" class="avatar-sm">
                        </a>
                    <?php endif; ?>
                </div>
            </header>
        <?php endif; ?>
        <main class="screen-content <?= sanitize($contentClass); ?>">
