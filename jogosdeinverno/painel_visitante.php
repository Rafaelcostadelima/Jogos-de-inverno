<?php
// Iniciamos a sessão apenas caso a gente precise verificar se um Admin está espiando a página,
// mas NÃO bloqueamos niguém de entrar.
session_start();
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
        }
        .container h1 { 
            margin-bottom: 20px; 
        }
        /* Estilo para a futura tabela de pontos */
        .tabela-pontos {
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
        }
        .tabela-pontos th, .tabela-pontos td {
            padding: 15px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
            text-align: center;
        }
        .tabela-pontos th {
            background-color: rgba(0, 0, 0, 0.2);
            color: #80DEEA;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Tabela de Pontuação 🧊</h1>
        <p>Acompanhe a pontuação das salas em tempo real!</p>
        
        <!-- Aqui nós vamos colocar a tabela de verdade no próximo passo -->
        <table class="tabela-pontos">
            <thead>
                <tr>
                    <th>Posição</th>
                    <th>Sala</th>
                    <th>Pontos</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>1º</td>
                    <td>Exemplo Sala A</td>
                    <td>150 pts</td>
                </tr>
                <tr>
                    <td>2º</td>
                    <td>Exemplo Sala B</td>
                    <td>120 pts</td>
                </tr>
            </tbody>
        </table>
        
        <br>
        <a href="index.php" class="btn-sair">Voltar para o Início</a>
    </div>
</body>
</html>