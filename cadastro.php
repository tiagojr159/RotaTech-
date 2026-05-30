<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

if (currentUser()) {
    header('Location: ' . appUrl());
    exit;
}

$message = '';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = sanitize($_POST['nome'] ?? '');
    $usuario = sanitize($_POST['usuario'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $senha = (string) ($_POST['senha'] ?? '');

    if ($nome === '' || $usuario === '' || $email === '' || $senha === '') {
        $error = 'Preencha todos os campos.';
    } else {
        $users = readJson('users.json');
        $exists = false;
        foreach ($users as $item) {
            if (mb_strtolower($item['email']) === mb_strtolower($email) || mb_strtolower($item['usuario']) === mb_strtolower($usuario)) {
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
                'titulo' => 'NOVO FORROZEIRO',
                'pontos' => 0,
                'criado_em' => date('Y-m-d'),
                'favoritos' => [],
                'figurinhas' => [1],
                'is_admin' => mb_strtolower($email) === mb_strtolower(MASTER_USER_EMAIL),
            ];
            writeJson('users.json', $users);
            $message = 'Cadastro concluido! Agora faca login.';
        }
    }
}

$showTopBar = true;
$backUrl = 'login.php';
$pageTitle = 'Criar Cadastro';
$pageEyebrow = 'festival';
$rightHtml = '<span class="icon-btn soft"><i class="fa-solid fa-user-plus"></i></span>';
include __DIR__ . '/includes/header.php';
?>
<section class="auth-form-page">
    <?php if ($message): ?><p class="alert alert-success"><?= sanitize($message); ?></p><?php endif; ?>
    <?php if ($error): ?><p class="alert alert-danger"><?= sanitize($error); ?></p><?php endif; ?>
    <form class="card stacked-form" method="post">
        <label>Nome completo</label>
        <input type="text" name="nome" placeholder="Ex: Ana Pereira" required>
        <label>Usuario</label>
        <input type="text" name="usuario" placeholder="@anapereira" required>
        <label>Email</label>
        <input type="email" name="email" placeholder="ana@email.com" required>
        <label>Senha</label>
        <div class="input-icon">
            <i class="fa-solid fa-lock"></i>
            <input type="password" name="senha" placeholder="minimo 6 caracteres" required minlength="6" data-password-toggle>
            <button type="button" class="password-toggle" data-password-button aria-label="Mostrar senha" aria-pressed="false">
                <i class="fa-regular fa-eye"></i>
            </button>
        </div>
        <button class="btn btn-primary btn-xl" type="submit">Criar Conta</button>
    </form>
    <p class="center muted">Ja possui conta? <a href="login.php" class="text-link strong">Entrar</a></p>
</section>
</main>
</div>
</div>
</body>
</html>
