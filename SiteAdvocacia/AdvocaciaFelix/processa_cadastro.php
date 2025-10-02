<?php
// Configurações do banco de dados
$host = 'localhost';
$dbname = 'felix_advocacia';
$username = 'root';
$password = '';

try {
    // Conexão com o banco de dados
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Verifica se o formulário foi submetido
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        
        // Coleta e sanitiza os dados
        $fullName = filter_input(INPUT_POST, 'fullName', FILTER_SANITIZE_STRING);
        $birthDate = $_POST['birthDate'];
        $cpf = preg_replace('/[^0-9]/', '', $_POST['cpf']);
        $city = filter_input(INPUT_POST, 'city', FILTER_SANITIZE_STRING);
        $phone = preg_replace('/[^0-9]/', '', $_POST['phone']);
        $secondaryPhone = !empty($_POST['secondaryPhone']) ? preg_replace('/[^0-9]/', '', $_POST['secondaryPhone']) : null;
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $password = $_POST['password'];
        $howFound = $_POST['howFound'];
        $newsletter = isset($_POST['newsletter']) ? 1 : 0;
        
        // Validações
        if (empty($fullName) || empty($birthDate) || empty($cpf) || empty($city) || empty($phone) || empty($email) || empty($password)) {
            throw new Exception('Todos os campos obrigatórios devem ser preenchidos.');
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('E-mail inválido.');
        }
        
        if (strlen($password) < 6) {
            throw new Exception('A senha deve ter pelo menos 6 caracteres.');
        }
        
        // Verifica se já existe
        $stmt = $pdo->prepare("SELECT id FROM clientes WHERE cpf = ? OR email = ?");
        $stmt->execute([$cpf, $email]);
        
        if ($stmt->rowCount() > 0) {
            throw new Exception('CPF ou E-mail já cadastrado.');
        }
        
        // Hash da senha
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        
        // Insere no banco
        $sql = "INSERT INTO clientes (
            fullName, birthDate, cpf, city, phone, secondaryPhone, 
            email, password, howFound, newsletter, created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $fullName, $birthDate, $cpf, $city, $phone, $secondaryPhone,
            $email, $passwordHash, $howFound, $newsletter
        ]);
        
        // Sucesso
        header('Location: cadastro.php?success=1');
        exit;
        
    }
    
} catch (Exception $e) {
    // Erro
    header('Location: cadastro.php?error=1&message=' . urlencode($e->getMessage()));
    exit;
}
?>