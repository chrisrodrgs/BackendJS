<?php
// Script para corrigir a senha dos advogados
$host = 'localhost';
$dbname = 'felix_advocacia';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Senha que você quer usar (Admin123@)
    $senha_plana = 'Admin123@';
    $senha_hash = password_hash($senha_plana, PASSWORD_DEFAULT);
    
    echo "Senha plana: " . $senha_plana . "<br>";
    echo "Senha criptografada: " . $senha_hash . "<br><br>";
    
    // Atualizar a senha de todos os advogados
    $stmt = $pdo->prepare("UPDATE advogados SET password = ?");
    $stmt->execute([$senha_hash]);
    
    echo "Senhas atualizadas com sucesso!<br>";
    echo "Agora use: <strong>Email:</strong> admin@felixadvocacia.com.br <strong>Senha:</strong> Admin123@";
    
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
}
?>