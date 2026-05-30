<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

if (currentUser()) {
    header('Location: ' . appUrl());
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = sanitize($_POST['login'] ?? '');
    $senha = (string) ($_POST['senha'] ?? '');

    $users = readJson('users.json');
    foreach ($users as $user) {
        $matchLogin = mb_strtolower($user['email']) === mb_strtolower($login)
            || mb_strtolower($user['usuario']) === mb_strtolower($login);
        if ($matchLogin && password_verify($senha, $user['senha_hash'])) {
            $_SESSION['user_id'] = $user['id'];
            header('Location: ' . appUrl());
            exit;
        }
    }
    $error = 'Dados invalidos. Tente novamente.';
}

$showTopBar = false;
$pageTitle = 'Login';
$pageClass = 'auth-screen';
include __DIR__ . '/includes/header.php';
?>
<section class="auth-wrap">
    <div class="auth-hero">
        <div class="auth-hero-overlay"></div>
        <div class="flags-top">
            <span class="flag c1"></span><span class="flag c2"></span><span class="flag c3"></span><span class="flag c4"></span><span class="flag c5"></span>
        </div>
        <div class="logo-badge auth-logo-badge">
            <img src="logomarca.png" alt="RotaTech Arcoverde">
        </div>
        <h2 class="auth-title">SAO JOAO 2026</h2>
        <p class="auth-sub">ARCOVERDE • PERNAMBUCO</p>
    </div>

    <form class="card auth-card auth-card-floating" method="post" autocomplete="on">
        <?php if ($error): ?>
            <p class="alert alert-danger"><?= sanitize($error); ?></p>
        <?php endif; ?>
        <label>Email ou Usuario</label>
        <div class="input-icon">
            <i class="fa-regular fa-user"></i>
            <input type="text" name="login" placeholder="cordel@arcoverde.com" required>
        </div>
        <div class="label-row">
            <label>Senha</label>
            <a href="#" class="text-link">Esqueceu?</a>
        </div>
        <div class="input-icon">
            <i class="fa-solid fa-lock"></i>
            <input type="password" name="senha" placeholder="********" required data-password-toggle>
            <button type="button" class="password-toggle" data-password-button aria-label="Mostrar senha" aria-pressed="false">
                <i class="fa-regular fa-eye"></i>
            </button>
        </div>
        <button class="btn btn-primary btn-xl" type="submit">Entrar</button>
        <div class="divider"><span>OU ENTRE COM</span></div>
        <div class="social-grid">
            <button class="btn btn-light" type="button"><i class="fa-brands fa-google"></i> Google</button>
            <button class="btn btn-light" type="button"><i class="fa-brands fa-apple"></i> Apple</button>
        </div>
    </form>
    <p class="signup-note">Ainda nao tem conta?</p>
    <a href="cadastro.php" class="signup-link">Criar Cadastro</a>
    <div class="icons-foot">
        <i class="fa-solid fa-leaf"></i><i class="fa-solid fa-fire"></i><i class="fa-solid fa-music"></i>
    </div>
</section>
</main>
</div>
</div>
</body>
</html>
