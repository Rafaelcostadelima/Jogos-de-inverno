<?php
session_start();
require 'conexao.php';

// Se já tiver logado, redireciona para o painel correto
if (isset($_SESSION['id_usuario'])) {
    if ($_SESSION['perfil'] == 'admin') {
        header("Location: painel_admin.php");
    } else {
        header("Location: painel_visitante.php");
    }
    exit;
}

// Lógica de Login
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $login = $_POST['login'];
    $senha = $_POST['senha'];

    $sql = "SELECT * FROM usuarios WHERE login = :login";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':login' => $login]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuario && password_verify($senha, $usuario['senha'])) {
        $_SESSION['id_usuario'] = $usuario['id'];
        $_SESSION['nome'] = $usuario['nome'];
        $_SESSION['perfil'] = $usuario['perfil'];

        // Redireciona após login bem-sucedido
        if ($usuario['perfil'] == 'admin') {
            header("Location: painel_admin.php");
        } else {
            header("Location: painel_visitante.php");
        }
        exit;
    } else {
        $erro = "Login ou senha incorretos!";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Entrada - Pontuação de Inverno</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* Estilos específicos para esta página de entrada */
        .main-options {
            margin-top: 30px;
            display: flex;
            flex-direction: column;
            gap: 15px; /* Espaço entre os botões */
        }

        .btn-large {
            padding: 18px 25px;
            font-size: 18px;
            background: linear-gradient(to right, #0083B0, #00B4DB);
            border-radius: 10px;
        }

        .btn-link-admin {
            font-size: 12px;
            color: #B2EBF2;
            text-decoration: none;
            margin-top: 10px;
            display: block; /* Para que o margin-top funcione */
        }
        .btn-link-admin:hover {
            color: #ffffff;
            text-decoration: underline;
        }

        /* Ajustes para a área de login quando ela aparecer */
        .login-form-section {
            margin-top: 40px;
            border-top: 1px solid rgba(255,255,255,0.2);
            padding-top: 30px;
        }
        
        .login-form-section h3 {
            color: #E0F7FA;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Sistema de Pontuação</h1>
        
        <div class="main-options">
            <!-- Botão grande para visitantes -->
            <button class="btn-large" onclick="mostrarOpcaoLogin('visitante')">
                Visitar Tabela de Pontos 🧊
            </button>
            
            <!-- Link pequeno para organizadores -->
            <a href="#" class="btn-link-admin" onclick="mostrarOpcaoLogin('admin'); return false;">
                Sou Organizador(a) ❄️
            </a>
        </div>

        <!-- Área de Login (inicialmente oculta para visitantes) -->
        <div id="loginSection" class="login-form-section" style="display: none;">
            <h3>Acesse sua conta de Organizador</h3>
            <?php if(isset($erro)) echo "<div class='erro'>$erro</div>"; ?>
            
            <form method="POST">
                <label>Login:</label>
                <input type="text" name="login" required placeholder="Digite seu usuário...">
                
                <label>Senha:</label>
                <input type="password" name="senha" required placeholder="Digite sua senha...">
                
                <button type="submit" class="btn-large" style="background: linear-gradient(to right, #2C5364, #203A43);">Entrar como Organizador</button>
                <br>
                <a href="#" class="btn-link-admin" onclick="esconderLogin()" style="margin-top: 15px;">Voltar</a>
            </form>
        </div>
    </div>

    <script>
        function mostrarOpcaoLogin(tipo) {
            const loginSection = document.getElementById('loginSection');
            const h3Element = loginSection.querySelector('h3');
            const formButton = loginSection.querySelector('button[type="submit"]');

            if (tipo === 'admin') {
                loginSection.style.display = 'block'; // Mostra a área de login
                h3Element.innerText = 'Acesse sua conta de Organizador';
                formButton.style.background = 'linear-gradient(to right, #2C5364, #203A43)'; // Cor mais escura para adm
                loginSection.querySelector('input[name="login"]').value = ''; // Limpa campos
                loginSection.querySelector('input[name="senha"]').value = '';
                if (document.querySelector('.erro')) { // Limpa erro antigo
                    document.querySelector('.erro').remove();
                }
            } else { // tipo === 'visitante'
                // Para visitantes, o botão já os leva direto para o painel_visitante.php
                window.location.href = 'painel_visitante.php';
            }
        }

        function esconderLogin() {
            document.getElementById('loginSection').style.display = 'none';
            // Limpa os campos do formulário ao esconder
            document.querySelector('#loginSection input[name="login"]').value = '';
            document.querySelector('#loginSection input[name="senha"]').value = '';
             if (document.querySelector('.erro')) {
                document.querySelector('.erro').remove();
            }
        }
    </script>
</body>
</html>