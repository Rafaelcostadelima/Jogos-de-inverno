<?php
// criar_admin.php - ACESSE ESTA PÁGINA UMA VEZ PARA CRIAR O ADMIN E DEPOIS APAGUE
require 'conexao.php';

$nome = "Organizador Principal";
$login = "Grêmio";
$senha = "MelhoresJogosDeInverno%#(2026";
$perfil = "admin";

// Aqui está a mágica: o PHP criptografa a senha antes de salvar
$senha_criptografada = password_hash($senha, PASSWORD_DEFAULT);

$sql = "INSERT INTO usuarios (nome, login, senha, perfil) VALUES (:nome, :login, :senha, :perfil)";
$stmt = $pdo->prepare($sql);
$stmt->execute([
    ':nome' => $nome,
    ':login' => $login,
    ':senha' => $senha_criptografada,
    ':perfil' => $perfil
]);

echo "Administrador criado com sucesso! Agora você pode deletar este arquivo.";
?>