<?php
$showCadastro = isset($_GET['cadastro']);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Garagem Motos - Acesso</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" href="img/logom.png">
    <link rel="stylesheet" href="public/assets/css/style.css?v=<?= @filemtime(__DIR__ . '/../public/assets/css/style.css') ?: 1 ?>">
    <link rel="stylesheet" href="public/assets/css/login.css?v=<?= @filemtime(__DIR__ . '/../public/assets/css/login.css') ?: 1 ?>">
</head>
<body>
    <img class="bg-full" src="img/banner.2.png" alt="Garagem Motos">
    <div class="overlay"></div>

    <div class="modal-wrap">
        <!-- LOGIN -->
        <div class="modal<?= $showCadastro ? '' : ' active' ?>" id="modal-login">
            <div class="brand">
                <img class="logo-icon" src="img/logom.png" alt="Logo">
                <div>
                    <h1>Garagem Motos</h1>
                    <small>Acesso ao sistema</small>
                </div>
            </div>
            <form method="post" action="index.php?controller=auth&action=login">
                <label>E-mail</label>
                <input type="email" name="email" required autocomplete="username">
                <label>Senha</label>
                <input type="password" name="senha" required autocomplete="current-password">
                <div class="actions">
                    <button class="btn btn-primary" type="submit">Entrar</button>
                </div>
            </form>
            <div class="toggle-link">
                Não tem conta? <a onclick="showModal('cadastro')">Cadastre-se</a>
            </div>
        </div>

        <!-- CADASTRO -->
        <div class="modal<?= $showCadastro ? ' active' : '' ?>" id="modal-cadastro">
            <div class="brand">
                <img class="logo-icon" src="img/logom.png" alt="Logo">
                <div>
                    <h1>Garagem Motos</h1>
                    <small>Criar conta</small>
                </div>
            </div>
            <form method="post" action="index.php?controller=usuario&action=store">
                <label>Nome</label>
                <input type="text" name="nome" required>
                <label>E-mail</label>
                <input type="email" name="email" required>
                <label>Senha</label>
                <input type="password" name="senha" required>
                <div class="actions">
                    <button class="btn btn-primary" type="submit">Cadastrar</button>
                </div>
            </form>
            <div class="toggle-link">
                Já tem conta? <a onclick="showModal('login')">Fazer login</a>
            </div>
        </div>
    </div>

    <script>
    function showModal(id){
        document.querySelectorAll('.modal').forEach(function(m){ m.classList.remove('active'); });
        document.getElementById('modal-'+id).classList.add('active');
    }
    <?php if ($showCadastro): ?>
    showModal('cadastro');
    <?php endif; ?>
    </script>
</body>
</html>
