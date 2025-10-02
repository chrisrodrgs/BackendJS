<?php
// config.php - Configuração do banco de dados

$host = 'localhost';
$usuario = 'root'; // Altere conforme seu ambiente
$senha = ''; // Altere conforme seu ambiente
$banco = 'felix_advocacia'; // Altere conforme seu banco de dados

// Conexão com o banco de dados
$conn = mysqli_connect($host, $usuario, $senha, $banco);

// Verificar conexão
if (!$conn) {
    die("Erro na conexão: " . mysqli_connect_error());
}

// Definir charset
mysqli_set_charset($conn, 'utf8');

// Timezone
date_default_timezone_set('America/Sao_Paulo');
?>