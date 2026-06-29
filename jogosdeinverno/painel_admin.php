<?php
session_start();
require 'conexao.php';

if (!isset($_SESSION['id_usuario']) || $_SESSION['perfil'] !== 'admin') {
    header("Location: index.php");
    exit;
}

$mensagem = "";

// LÓGICA 1: CADASTRAR NOVA SALA
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['acao']) && $_POST['acao'] == 'cadastrar_sala') {
    $nome_sala = $_POST['nome_sala'];
    
    $sql = "INSERT INTO salas (nome, pontos) VALUES (:nome, 0)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':nome' => $nome_sala]);
    
    $mensagem = "<div style='color: #80DEEA; margin-bottom: 15px; background: rgba(0,0,0,0.3); padding: 10px; border-radius: 5px;'>✅ Turma '$nome_sala' cadastrada com sucesso!</div>";
}

// LÓGICA 2: CADASTRAR ALUNO (PARTICIPAÇÃO SIMPLES = +2 PONTOS)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['acao']) && $_POST['acao'] == 'cadastrar_aluno') {
    $nome_aluno = $_POST['nome_aluno'];
    $sala_id = $_POST['sala_id'];
    
    $sql_aluno = "INSERT INTO alunos (nome, sala_id) VALUES (:nome, :sala_id)";
    $stmt_aluno = $pdo->prepare($sql_aluno);
    $stmt_aluno->execute([':nome' => $nome_aluno, ':sala_id' => $sala_id]);
    
    $sql_pontos = "UPDATE salas SET pontos = pontos + 2 WHERE id = :sala_id";
    $stmt_pontos = $pdo->prepare($sql_pontos);
    $stmt_pontos->execute([':sala_id' => $sala_id]);
    
    $mensagem = "<div style='color: #80DEEA; margin-bottom: 15px; background: rgba(0,0,0,0.3); padding: 10px; border-radius: 5px;'>✅ Participação de '$nome_aluno' registrada! A turma ganhou +2 pontos.</div>";
}

// LÓGICA 3: CADASTRAR COLOCAÇÃO (PÓDIO = +10, +5 ou 0 PONTOS)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['acao']) && $_POST['acao'] == 'cadastrar_colocacao') {
    $nome_aluno = $_POST['nome_aluno'];
    $sala_id = $_POST['sala_id'];
    $colocacao = $_POST['colocacao'];
    
    $pontos_ganhos = 0;
    $texto_colocacao = "";
    
    // Verifica qual foi a colocação para dar os pontos
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
    
    // Junta o nome com a colocação para o histórico (Ex: "João (1º Lugar)")
    $nome_com_medalha = $nome_aluno . " (" . $texto_colocacao . ")";
    
    // Salva o aluno na tabela
    $sql_aluno = "INSERT INTO alunos (nome, sala_id) VALUES (:nome, :sala_id)";
    $stmt_aluno = $pdo->prepare($sql_aluno);
    $stmt_aluno->execute([':nome' => $nome_com_medalha, ':sala_id' => $sala_id]);
    
    // Só atualiza os pontos se ele ganhou alguma coisa (1º ou 2º)
    if ($pontos_ganhos > 0) {
        $sql_pontos = "UPDATE salas SET pontos = pontos + :pontos WHERE id = :sala_id";
        $stmt_pontos = $pdo->prepare($sql_pontos);
        $stmt_pontos->execute([':pontos' => $pontos_ganhos, ':sala_id' => $sala_id]);
    }
    
    $mensagem = "<div style='color: #80DEEA; margin-bottom: 15px; background: rgba(0,0,0,0.3); padding: 10px; border-radius: 5px;'>🏆 '$nome_aluno' registrado como $texto_colocacao! A turma ganhou +$pontos_ganhos pontos.</div>";
}

// Buscar todas as salas para as listas de opções
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

        <!-- Aqui aparece a mensagem de sucesso verde -->
        <?php echo $mensagem; ?>

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
        <div class="box-formulario" style="border: 1px solid #FFD700;"> <!-- Borda dourada para destacar -->
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
                    <option value="1">1º Lugar</option>
                    <option value="2">2º Lugar</option>
                    <option value="3">3º Lugar</option>
                    <option value="demais">Demais</option>
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