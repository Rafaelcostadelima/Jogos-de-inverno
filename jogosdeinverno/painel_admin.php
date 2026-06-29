<?php
session_start();
require 'conexao.php';

// Segurança de acesso
if (!isset($_SESSION['id_usuario']) || $_SESSION['perfil'] !== 'admin') {
    header("Location: index.php");
    exit;
}

// ==========================================
// PROCESSAMENTO DOS DADOS (QUANDO CLICA NOS BOTÕES)
// ==========================================
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['acao'])) {
    
    // LÓGICA 1: CADASTRAR NOVA SALA (Esta trava continua para evitar turmas duplicadas)
    if ($_POST['acao'] == 'cadastrar_sala') {
        $nome_sala = trim($_POST['nome_sala']); 
        
        $sql_verifica = "SELECT id FROM salas WHERE nome = :nome";
        $stmt_verifica = $pdo->prepare($sql_verifica);
        $stmt_verifica->execute([':nome' => $nome_sala]);
        
        if ($stmt_verifica->rowCount() > 0) {
            $_SESSION['mensagem'] = "<div style='color: #ff9999; margin-bottom: 15px; background: rgba(255,0,0,0.2); padding: 10px; border-radius: 5px;'>⚠️ A turma '$nome_sala' já está cadastrada!</div>";
        } else {
            $sql = "INSERT INTO salas (nome, pontos) VALUES (:nome, 0)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':nome' => $nome_sala]);
            $_SESSION['mensagem'] = "<div style='color: #80DEEA; margin-bottom: 15px; background: rgba(0,0,0,0.3); padding: 10px; border-radius: 5px;'>✅ Turma '$nome_sala' cadastrada com sucesso!</div>";
        }
    }

    // LÓGICA 2: CADASTRAR ALUNO (PARTICIPAÇÃO) - Trava removida!
    if ($_POST['acao'] == 'cadastrar_aluno') {
        $nome_aluno = trim($_POST['nome_aluno']);
        $sala_id = $_POST['sala_id'];
        
        // Cadastra diretamente no banco de dados
        $sql_aluno = "INSERT INTO alunos (nome, sala_id) VALUES (:nome, :sala_id)";
        $stmt_aluno = $pdo->prepare($sql_aluno);
        $stmt_aluno->execute([':nome' => $nome_aluno, ':sala_id' => $sala_id]);
        
        // Adiciona +2 pontos para a sala dele
        $sql_pontos = "UPDATE salas SET pontos = pontos + 2 WHERE id = :sala_id";
        $stmt_pontos = $pdo->prepare($sql_pontos);
        $stmt_pontos->execute([':sala_id' => $sala_id]);
        
        $_SESSION['mensagem'] = "<div style='color: #80DEEA; margin-bottom: 15px; background: rgba(0,0,0,0.3); padding: 10px; border-radius: 5px;'>✅ Participação de '$nome_aluno' registrada! A turma ganhou +2 pontos.</div>";
    }

    // LÓGICA 3: CADASTRAR COLOCAÇÃO (PÓDIO) - Trava removida!
    if ($_POST['acao'] == 'cadastrar_colocacao') {
        $nome_aluno = trim($_POST['nome_aluno']);
        $sala_id = $_POST['sala_id'];
        $colocacao = $_POST['colocacao'];
        
        $pontos_ganhos = 0;
        $texto_colocacao = "";
        
        if ($colocacao == '1') {
            $pontos_ganhos = 10;
            $texto_colocacao = "1º Lugar 🥇";
        } elseif ($colocacao == '2') {
            $pontos_ganhos = 5;
            $texto_colocacao = "2º Lugar 🥈";
        } elseif ($colocacao == '3') {
            $pontos_ganhos = 0;
            $texto_colocacao = "3º Lugar 🥉";
        } else {
            $pontos_ganhos = 0;
            $texto_colocacao = "Participou";
        }
        
        $nome_com_medalha = $nome_aluno . " (" . $texto_colocacao . ")";
        
        // Cadastra diretamente no banco de dados
        $sql_aluno = "INSERT INTO alunos (nome, sala_id) VALUES (:nome, :sala_id)";
        $stmt_aluno = $pdo->prepare($sql_aluno);
        $stmt_aluno->execute([':nome' => $nome_com_medalha, ':sala_id' => $sala_id]);
        
        // Atualiza os pontos se ele ganhou alguma coisa
        if ($pontos_ganhos > 0) {
            $sql_pontos = "UPDATE salas SET pontos = pontos + :pontos WHERE id = :sala_id";
            $stmt_pontos = $pdo->prepare($sql_pontos);
            $stmt_pontos->execute([':pontos' => $pontos_ganhos, ':sala_id' => $sala_id]);
        }
        
        $_SESSION['mensagem'] = "<div style='color: #80DEEA; margin-bottom: 15px; background: rgba(0,0,0,0.3); padding: 10px; border-radius: 5px;'>🏆 '$nome_aluno' registrado como $texto_colocacao! A turma ganhou +$pontos_ganhos pontos.</div>";
    }

    // Recarrega a página de forma limpa para evitar duplicados ao apertar F5
    header("Location: painel_admin.php");
    exit;
}

// ==========================================
// EXIBIÇÃO DA PÁGINA (HTML)
// ==========================================

$mensagem_html = "";
if (isset($_SESSION['mensagem'])) {
    $mensagem_html = $_SESSION['mensagem'];
    unset($_SESSION['mensagem']); 
}

$sql_busca_salas = "SELECT * FROM salas ORDER BY nome ASC";
$stmt_busca_salas = $pdo->query($sql_busca_salas);
$salas_cadastradas = $stmt_busca_salas->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Painel do Organizador</title>
    <link rel="stylesheet" href="style.css">
    <link rel="shortcut icon" href="Logo-JogosDeInverno2026.png" type="image/x-icon">
    <style>
        .container { max-width: 600px; margin-top: 40px; margin-bottom: 40px;}
        .box-formulario {
            background: rgba(0, 0, 0, 0.2);
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 25px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        select {
            width: 100%;
            padding: 12px;
            margin-top: 5px;
            margin-bottom: 20px;
            border-radius: 8px;
            border: none;
            background: rgba(255, 255, 255, 0.9);
            color: #0F2027;
            outline: none;
            font-size: 16px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Área da Organização ❄️</h1>
        <p>Logado como: <strong><?php echo htmlspecialchars($_SESSION['nome']); ?></strong></p>
        <br>

        <?php echo $mensagem_html; ?>

        <!-- BLOCO 1: CADASTRAR SALA -->
        <div class="box-formulario">
            <h3>1. Cadastrar Nova Turma</h3>
            <form method="POST">
                <input type="hidden" name="acao" value="cadastrar_sala">
                <label>Nome da Turma (Ex: 2º C):</label>
                <input type="text" name="nome_sala" required placeholder="Digite o nome da turma...">
                <button type="submit">Salvar Turma</button>
            </form>
        </div>

        <!-- BLOCO 2: CADASTRAR ALUNO (PARTICIPAÇÃO) -->
        <div class="box-formulario">
            <h3>2. Participação Simples (+2 Pts)</h3>
            <form method="POST">
                <input type="hidden" name="acao" value="cadastrar_aluno">
                
                <label>Nome do Aluno:</label>
                <input type="text" name="nome_aluno" required placeholder="Ex: Lucas Lima">
                
                <label>Selecione a Turma:</label>
                <select name="sala_id" required>
                    <option value="">-- Escolha uma turma --</option>
                    <?php foreach ($salas_cadastradas as $sala): ?>
                        <option value="<?php echo $sala['id']; ?>">
                            <?php echo htmlspecialchars($sala['nome']); ?> (Atual: <?php echo $sala['pontos']; ?> pts)
                        </option>
                    <?php endforeach; ?>
                </select>
                
                <button type="submit" style="background: linear-gradient(to right, #00b09b, #96c93d);">
                    Salvar Participação
                </button>
            </form>
        </div>

        <!-- BLOCO 3: REGISTRAR COLOCAÇÃO (PÓDIO) -->
        <div class="box-formulario" style="border: 1px solid #FFD700;"> 
            <h3 style="color: #FFD700;">3. Registrar Colocação de Pódio 🏆</h3>
            <form method="POST">
                <input type="hidden" name="acao" value="cadastrar_colocacao">
                
                <label>Nome da Pessoa / Equipe:</label>
                <input type="text" name="nome_aluno" required placeholder="Ex: Ana Souza">
                
                <label>Selecione a Turma:</label>
                <select name="sala_id" required>
                    <option value="">-- Escolha uma turma --</option>
                    <?php foreach ($salas_cadastradas as $sala): ?>
                        <option value="<?php echo $sala['id']; ?>">
                            <?php echo htmlspecialchars($sala['nome']); ?> (Atual: <?php echo $sala['pontos']; ?> pts)
                        </option>
                    <?php endforeach; ?>
                </select>

                <label>Colocação:</label>
                <select name="colocacao" required>
                    <option value="">-- Selecione a posição --</option>
                    <option value="1">1º Lugar (+10 pontos)</option>
                    <option value="2">2º Lugar (+5 pontos)</option>
                    <option value="3">3º Lugar (0 pontos extras)</option>
                    <option value="demais">Demais (0 pontos extras)</option>
                </select>
                
                <button type="submit" style="background: linear-gradient(to right, #F7971E, #FFD200); color: #000;">
                    Salvar Colocação
                </button>
            </form>
        </div>

        <a href="logout.php" class="btn-sair">Sair do Sistema</a>
    </div>
</body>
</html>