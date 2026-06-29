<?php
session_start();
require 'conexao.php'; // Conecta ao banco de dados

// Busca todas as salas cadastradas, ordenando pelos pontos (do maior para o menor).
// Se houver empate em pontos, ele organiza por ordem alfabética do nome da sala.
$sql = "SELECT * FROM salas ORDER BY pontos DESC, nome ASC";
$stmt = $pdo->query($sql);
$salas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Tabela de Pontuação</title>
    <link rel="stylesheet" href="style.css">
    <style> 
        .container {
            max-width: 800px;
            margin-top: 40px;
            margin-bottom: 40px;
        }
        .container h1 { 
            margin-bottom: 10px; 
        }
        
        /* Estilização da tabela de pontos */
        .tabela-pontos {
            width: 100%;
            margin-top: 30px;
            border-collapse: collapse;
            background: rgba(0, 0, 0, 0.15);
            border-radius: 10px;
            overflow: hidden; /* Garante que os cantos arredondados apareçam */
        }
        .tabela-pontos th, .tabela-pontos td {
            padding: 18px 15px;
            text-align: center;
        }
        .tabela-pontos th {
            background-color: rgba(0, 0, 0, 0.35);
            color: #80DEEA;
            font-size: 16px;
            text-transform: uppercase;
            letter-spacing: 1px;
            border-bottom: 2px solid rgba(255, 255, 255, 0.1);
        }
        .tabela-pontos td {
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            font-size: 18px;
        }
        /* Destaque para a linha do primeiro colocado */
        .linha-primeiro {
            background: rgba(255, 215, 0, 0.1); /* Um leve brilho dourado */
        }
        
        /* Botão para atualizar a página manualmente */
        .btn-atualizar {
            display: inline-block;
            margin-top: 25px;
            margin-right: 10px;
            padding: 10px 20px;
            background: rgba(128, 222, 234, 0.2);
            color: #80DEEA;
            text-decoration: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: bold;
            transition: 0.3s;
            border: 1px solid rgba(128, 222, 234, 0.3);
        }
        .btn-atualizar:hover {
            background: rgba(128, 222, 234, 0.4);
            color: #ffffff;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Classificação Geral</h1>
        <p>Acompanhe a pontuação das turmas em tempo real!</p>
        
        <?php if (count($salas) > 0): ?>
            <table class="tabela-pontos">
                <thead>
                    <tr>
                        <th>Classificação</th>
                        <th>Turma</th>
                        <th>Pontuação</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $posicao = 1;
                    foreach ($salas as $sala): 
                        // Define medalhas para os três primeiros
                        $medalha = "";
                        $classe_linha = "";
                        
                        if ($posicao == 1) {
                            $medalha = " 🥇";
                            $classe_linha = "class='linha-primeiro'";
                        } elseif ($posicao == 2) {
                            $medalha = " 🥈";
                        } elseif ($posicao == 3) {
                            $medalha = " 🥉";
                        }
                    ?>
                        <tr <?php echo $classe_linha; ?>>
                            <td>
                                <strong><?php echo $posicao; ?>º</strong><?php echo $medalha; ?>
                            </td>
                            <td>
                                <strong><?php echo htmlspecialchars($sala['nome']); ?></strong>
                            </td>
                            <td>
                                <span style="color: #80DEEA; font-weight: bold;"><?php echo $sala['pontos']; ?></span> pts
                            </td>
                        </tr>
                    <?php 
                        $posicao++;
                    endforeach; 
                    ?>
                </tbody>
            </table>
        <?php else: ?>
            <!-- Caso o organizador ainda não tenha cadastrado nenhuma sala no painel -->
            <div style="margin-top: 40px; padding: 20px; background: rgba(255,255,255,0.05); border-radius: 10px;">
                <p style="color: #B2EBF2;">Nenhuma turma foi registrada no sistema até o momento. Aguarde o início do evento!</p>
            </div>
        <?php endif; ?>
        
        <br><br>
        <!-- Botão para o visitante atualizar o placar -->
        <a href="painel_visitante.php" class="btn-atualizar">🔄 Atualizar Placar</a>
        
        <!-- Botão para voltar ao menu inicial -->
        <a href="index.php" class="btn-sair">Voltar ao Início</a>
    </div>
</body>
</html>