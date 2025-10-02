<?php
session_start();

// Verifica se o usuário está logado
if (!isset($_SESSION['cliente_id'])) {
    header('Location: login.php');
    exit;
}

// Configurações do banco
$host = 'localhost';
$dbname = 'felix_advocacia';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Busca dados do cliente
    $stmt = $pdo->prepare("SELECT * FROM clientes WHERE id = ?");
    $stmt->execute([$_SESSION['cliente_id']]);
    $cliente = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Busca conversas do cliente
    $stmt = $pdo->prepare("
        SELECT c.*, a.nome as advogado_nome,
               (SELECT COUNT(*) FROM mensagens m WHERE m.conversa_id = c.id AND m.remetente = 'advogado' AND m.lida = FALSE) as mensagens_nao_lidas
        FROM conversas c 
        JOIN advogados a ON c.advogado_id = a.id 
        WHERE c.cliente_id = ? 
        ORDER BY c.updated_at DESC
    ");
    $stmt->execute([$_SESSION['cliente_id']]);
    $conversas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Processar nova mensagem
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['nova_mensagem'])) {
        $conversa_id = $_POST['conversa_id'];
        $mensagem = trim($_POST['mensagem']);
        
        if (!empty($mensagem)) {
            // Verifica se a conversa pertence ao cliente
            $stmt = $pdo->prepare("SELECT id FROM conversas WHERE id = ? AND cliente_id = ?");
            $stmt->execute([$conversa_id, $_SESSION['cliente_id']]);
            
            if ($stmt->fetch()) {
                // Insere a mensagem
                $stmt = $pdo->prepare("INSERT INTO mensagens (conversa_id, remetente, mensagem) VALUES (?, 'cliente', ?)");
                $stmt->execute([$conversa_id, $mensagem]);
                
                // Atualiza timestamp da conversa
                $stmt = $pdo->prepare("UPDATE conversas SET updated_at = NOW() WHERE id = ?");
                $stmt->execute([$conversa_id]);
                
                header("Location: area_cliente.php?conversa=" . $conversa_id);
                exit;
            }
        }
    }
    
    // Criar nova conversa
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['nova_conversa'])) {
        $assunto = trim($_POST['assunto']);
        
        if (!empty($assunto)) {
            // Pega o primeiro advogado (em um sistema real, seria por especialidade, etc)
            $stmt = $pdo->prepare("SELECT id FROM advogados LIMIT 1");
            $stmt->execute();
            $advogado = $stmt->fetch();
            
            if ($advogado) {
                $stmt = $pdo->prepare("INSERT INTO conversas (cliente_id, advogado_id, assunto) VALUES (?, ?, ?)");
                $stmt->execute([$_SESSION['cliente_id'], $advogado['id'], $assunto]);
                $nova_conversa_id = $pdo->lastInsertId();
                
                header("Location: area_cliente.php?conversa=" . $nova_conversa_id);
                exit;
            }
        }
    }
    
    // Processar novo agendamento
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['novo_agendamento'])) {
        $data_agendamento = $_POST['data_agendamento'];
        $hora_agendamento = $_POST['hora_agendamento'];
        $tipo_consulta = $_POST['tipo_consulta'];
        $descricao = trim($_POST['descricao']);
        
        if (!empty($data_agendamento) && !empty($hora_agendamento) && !empty($tipo_consulta)) {
            // Pega o primeiro advogado disponível
            $stmt = $pdo->prepare("SELECT id FROM advogados LIMIT 1");
            $stmt->execute();
            $advogado = $stmt->fetch();
            
            if ($advogado) {
                $data_hora = $data_agendamento . ' ' . $hora_agendamento;
                
                $stmt = $pdo->prepare("INSERT INTO agendamentos (cliente_id, advogado_id, data_agendamento, tipo_consulta, descricao, status) VALUES (?, ?, ?, ?, ?, 'agendado')");
                $stmt->execute([$_SESSION['cliente_id'], $advogado['id'], $data_hora, $tipo_consulta, $descricao]);
                
                header("Location: area_cliente.php?tab=agendamentos&sucesso=1");
                exit;
            }
        }
    }
    
    // Buscar agendamentos do cliente
    $stmt = $pdo->prepare("
        SELECT a.*, adv.nome as advogado_nome 
        FROM agendamentos a 
        JOIN advogados adv ON a.advogado_id = adv.id 
        WHERE a.cliente_id = ? 
        ORDER BY a.data_agendamento DESC
    ");
    $stmt->execute([$_SESSION['cliente_id']]);
    $agendamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Buscar mensagens se houver conversa selecionada
    if (isset($_GET['conversa'])) {
        $conversa_id = $_GET['conversa'];
        
        // Buscar mensagens da conversa
        $stmt = $pdo->prepare("
            SELECT m.*, 
                   CASE 
                       WHEN m.remetente = 'cliente' THEN c.fullName 
                       ELSE a.nome 
                   END as nome_remetente
            FROM mensagens m
            LEFT JOIN clientes c ON m.remetente = 'cliente' AND c.id = ?
            LEFT JOIN advogados a ON m.remetente = 'advogado' AND a.id = (SELECT advogado_id FROM conversas WHERE id = ?)
            WHERE m.conversa_id = ?
            ORDER BY m.created_at ASC
        ");
        $stmt->execute([$_SESSION['cliente_id'], $conversa_id, $conversa_id]);
        $mensagens = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Marcar mensagens do advogado como lidas
        $stmt = $pdo->prepare("UPDATE mensagens SET lida = TRUE WHERE conversa_id = ? AND remetente = 'advogado' AND lida = FALSE");
        $stmt->execute([$conversa_id]);
        
        // Buscar info da conversa
        $stmt = $pdo->prepare("SELECT c.*, a.nome as advogado_nome FROM conversas c JOIN advogados a ON c.advogado_id = a.id WHERE c.id = ? AND c.cliente_id = ?");
        $stmt->execute([$conversa_id, $_SESSION['cliente_id']]);
        $conversa_atual = $stmt->fetch();
    }
    
} catch (Exception $e) {
    die('Erro ao carregar dados: ' . $e->getMessage());
}

// Logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: login.php');
    exit;
}

// Determinar aba ativa
$aba_ativa = isset($_GET['tab']) ? $_GET['tab'] : 'chat';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Área do Cliente - Felix Advocacia</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #1a3a5f;
            --secondary-color: #c9a96e;
            --accent-color: #2c5282;
            --text-dark: #2d3748;
            --text-light: #718096;
            --bg-light: #f7fafc;
            --white: #ffffff;
            --border-radius: 8px;
            --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            line-height: 1.6;
            color: var(--text-dark);
            background-color: var(--bg-light);
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* Header Styles */
        header {
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
            padding: 1.2rem 0;
            box-shadow: var(--shadow);
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
            transition: var(--transition);
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            display: flex;
            align-items: center;
            color: var(--white);
        }

        .logo i {
            font-size: 2rem;
            margin-right: 10px;
            color: var(--secondary-color);
        }

        .logo h1 {
            font-family: 'Playfair Display', serif;
            font-size: 1.8rem;
            font-weight: 600;
        }

        nav ul {
            display: flex;
            list-style: none;
            gap: 2rem;
        }

        nav a {
            color: var(--white);
            text-decoration: none;
            font-weight: 500;
            transition: var(--transition);
            padding: 0.5rem 1rem;
            border-radius: var(--border-radius);
        }

        nav a:hover {
            background-color: rgba(255, 255, 255, 0.1);
            color: var(--secondary-color);
        }

        .user-actions {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.8rem 1.5rem;
            border-radius: var(--border-radius);
            text-decoration: none;
            font-weight: 500;
            transition: var(--transition);
            border: 2px solid transparent;
            cursor: pointer;
        }

        .btn-outline {
            background: transparent;
            color: var(--white);
            border-color: var(--secondary-color);
        }

        .btn-outline:hover {
            background: var(--secondary-color);
            color: var(--primary-color);
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--secondary-color), #d4b87a);
            color: var(--primary-color);
            border: none;
            font-weight: 600;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(201, 169, 110, 0.3);
        }

        .btn-secondary {
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
            color: var(--white);
            border: none;
            font-weight: 600;
        }

        .btn-secondary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(26, 58, 95, 0.3);
        }

        /* Hero Section */
        .hero {
            background: linear-gradient(135deg, rgba(26, 58, 95, 0.9), rgba(44, 82, 130, 0.9)), url('https://images.unsplash.com/photo-1589829545856-d10d557cf95f?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1200&q=80');
            background-size: cover;
            background-position: center;
            color: var(--white);
            padding: 160px 0 80px;
            text-align: center;
            margin-top: 80px;
        }

        .hero h2 {
            font-family: 'Playfair Display', serif;
            font-size: 3rem;
            margin-bottom: 1rem;
            font-weight: 600;
        }

        .hero p {
            font-size: 1.2rem;
            opacity: 0.9;
            max-width: 600px;
            margin: 0 auto;
        }

        /* User Area */
        .user-area {
            padding: 80px 0;
            background: var(--bg-light);
        }

        .user-container {
            display: grid;
            grid-template-columns: 300px 1fr;
            gap: 2rem;
            background: var(--white);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            overflow: hidden;
        }

        .user-sidebar {
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
            color: var(--white);
            padding: 2rem;
        }

        .user-profile {
            text-align: center;
            margin-bottom: 2rem;
            padding-bottom: 2rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .user-avatar {
            width: 80px;
            height: 80px;
            background: var(--secondary-color);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            font-size: 2rem;
        }

        .user-info h3 {
            font-family: 'Playfair Display', serif;
            margin-bottom: 0.5rem;
        }

        .user-menu {
            list-style: none;
        }

        .user-menu li {
            margin-bottom: 0.5rem;
        }

        .user-menu a {
            color: var(--white);
            text-decoration: none;
            padding: 1rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            border-radius: var(--border-radius);
            transition: var(--transition);
        }

        .user-menu a:hover,
        .user-menu a.active {
            background: rgba(255, 255, 255, 0.1);
            color: var(--secondary-color);
        }

        .user-content {
            padding: 2rem;
        }

        .user-content h3 {
            font-family: 'Playfair Display', serif;
            font-size: 2rem;
            margin-bottom: 2rem;
            color: var(--primary-color);
            border-bottom: 2px solid var(--secondary-color);
            padding-bottom: 0.5rem;
        }

        /* Tabs */
        .tabs {
            display: flex;
            border-bottom: 2px solid #e9ecef;
            margin-bottom: 2rem;
        }

        .tab {
            padding: 1rem 2rem;
            cursor: pointer;
            border-bottom: 3px solid transparent;
            transition: var(--transition);
            font-weight: 500;
            color: var(--text-light);
        }

        .tab:hover {
            color: var(--primary-color);
        }

        .tab.active {
            color: var(--primary-color);
            border-bottom-color: var(--secondary-color);
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        /* Chat Styles */
        .nova-conversa-form {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            padding: 2rem;
            border-radius: var(--border-radius);
            margin-bottom: 2rem;
            border-left: 4px solid var(--secondary-color);
        }

        .nova-conversa-form h4 {
            color: var(--primary-color);
            margin-bottom: 1rem;
            font-family: 'Playfair Display', serif;
        }

        .conversas-lista {
            max-height: 400px;
            overflow-y: auto;
            margin-bottom: 2rem;
        }

        .conversa-item {
            padding: 1.5rem;
            border: 1px solid #e9ecef;
            border-radius: var(--border-radius);
            margin-bottom: 1rem;
            cursor: pointer;
            transition: var(--transition);
            background: var(--white);
        }

        .conversa-item:hover,
        .conversa-item.ativa {
            border-color: var(--secondary-color);
            background: linear-gradient(135deg, #fefefe, #f8f9fa);
            transform: translateX(5px);
        }

        .conversa-info {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }

        .badge {
            background: #e53e3e;
            color: white;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .chat-real {
            display: flex;
            flex-direction: column;
            height: 500px;
            border: 1px solid #e9ecef;
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: var(--shadow);
        }

        .chat-header {
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
            color: white;
            padding: 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .chat-messages {
            flex: 1;
            padding: 1.5rem;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 1rem;
            background: var(--bg-light);
        }

        .message {
            max-width: 70%;
            padding: 1rem 1.2rem;
            border-radius: 18px;
            position: relative;
            animation: messageAppear 0.3s ease-out;
        }

        .message.cliente {
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
            color: white;
            align-self: flex-end;
            border-bottom-right-radius: 5px;
        }

        .message.advogado {
            background-color: var(--white);
            align-self: flex-start;
            border-bottom-left-radius: 5px;
            box-shadow: var(--shadow);
            border: 1px solid #e9ecef;
        }

        .message-info {
            font-size: 0.8rem;
            opacity: 0.7;
            margin-top: 0.5rem;
        }

        .chat-input {
            display: flex;
            padding: 1.5rem;
            border-top: 1px solid #e9ecef;
            background-color: var(--white);
        }

        .chat-input input {
            flex: 1;
            padding: 1rem 1.5rem;
            border: 1px solid #ddd;
            border-radius: 50px;
            outline: none;
            font-size: 1rem;
            transition: var(--transition);
        }

        .chat-input input:focus {
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 3px rgba(201, 169, 110, 0.1);
        }

        .chat-input button {
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
            color: white;
            border: none;
            border-radius: 50px;
            padding: 1rem 2rem;
            margin-left: 1rem;
            cursor: pointer;
            transition: var(--transition);
            font-weight: 600;
        }

        .chat-input button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(26, 58, 95, 0.3);
        }

        .refresh-controls {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 1.5rem;
            background: #f8f9fa;
            border-top: 1px solid #e9ecef;
        }

        .auto-refresh-toggle {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.9rem;
            color: var(--text-light);
        }

        .switch {
            position: relative;
            display: inline-block;
            width: 50px;
            height: 24px;
        }

        .switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: .4s;
            border-radius: 24px;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 16px;
            width: 16px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }

        input:checked + .slider {
            background-color: var(--secondary-color);
        }

        input:checked + .slider:before {
            transform: translateX(26px);
        }

        /* Agendamentos Styles */
        .novo-agendamento-form {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            padding: 2rem;
            border-radius: var(--border-radius);
            margin-bottom: 2rem;
            border-left: 4px solid var(--secondary-color);
        }

        .novo-agendamento-form h4 {
            color: var(--primary-color);
            margin-bottom: 1rem;
            font-family: 'Playfair Display', serif;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--primary-color);
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 0.8rem 1rem;
            border: 1px solid #ddd;
            border-radius: var(--border-radius);
            font-size: 1rem;
            transition: var(--transition);
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 3px rgba(201, 169, 110, 0.1);
            outline: none;
        }

        .agendamentos-lista {
            margin-top: 2rem;
        }

        .agendamento-item {
            padding: 1.5rem;
            border: 1px solid #e9ecef;
            border-radius: var(--border-radius);
            margin-bottom: 1rem;
            background: var(--white);
            transition: var(--transition);
        }

        .agendamento-item:hover {
            border-color: var(--secondary-color);
            transform: translateY(-2px);
            box-shadow: var(--shadow);
        }

        .agendamento-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1rem;
        }

        .agendamento-info h4 {
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }

        .status {
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .status.agendado {
            background: #e6fffa;
            color: #234e52;
        }

        .status.confirmado {
            background: #f0fff4;
            color: #22543d;
        }

        .status.cancelado {
            background: #fed7d7;
            color: #742a2a;
        }

        .status.concluido {
            background: #ebf8ff;
            color: #1a365d;
        }

        .agendamento-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            color: var(--text-light);
            font-size: 0.9rem;
        }

        .alert {
            padding: 1rem;
            border-radius: var(--border-radius);
            margin-bottom: 1rem;
            border-left: 4px solid;
        }

        .alert.success {
            background: #f0fff4;
            border-color: #38a169;
            color: #22543d;
        }

        /* Footer */
        footer {
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
            color: var(--white);
            padding: 3rem 0 1rem;
        }

        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .footer-column h3 {
            font-family: 'Playfair Display', serif;
            margin-bottom: 1.5rem;
            color: var(--secondary-color);
        }

        .footer-column ul {
            list-style: none;
        }

        .footer-column li {
            margin-bottom: 0.8rem;
        }

        .footer-column a {
            color: var(--white);
            text-decoration: none;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .footer-column a:hover {
            color: var(--secondary-color);
            transform: translateX(5px);
        }

        .copyright {
            text-align: center;
            padding-top: 2rem;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            color: rgba(255, 255, 255, 0.7);
        }

        /* Animations */
        @keyframes messageAppear {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Responsive */
        @media (max-width: 768px) {
            .user-container {
                grid-template-columns: 1fr;
            }
            
            .user-sidebar {
                order: 2;
            }
            
            .user-content {
                order: 1;
            }
            
            .header-content {
                flex-direction: column;
                gap: 1rem;
            }
            
            nav ul {
                gap: 1rem;
            }
            
            .hero h2 {
                font-size: 2rem;
            }
            
            .message {
                max-width: 85%;
            }
            
            .tabs {
                flex-direction: column;
            }
            
            .tab {
                padding: 0.8rem 1rem;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header>
        <div class="container header-content">
            <div class="logo">
                <i class="fas fa-balance-scale"></i>
                <h1>Felix Advocacia</h1>
            </div>
            <nav>
                <ul>
                    <li><a href="index.html">Início</a></li>
                    <li><a href="index.html#services">Serviços</a></li>
                    <li><a href="area_cliente.php" style="color: var(--secondary-color);">Área do Cliente</a></li>
                    <li><a href="index.html#contact">Contato</a></li>
                </ul>
            </nav>
            <div class="user-actions">
                <span style="color: white; margin-right: 1rem;">
                    <i class="fas fa-user"></i> Olá, <?php echo htmlspecialchars($cliente['fullName']); ?>
                </span>
                <a href="area_cliente.php?logout=1" class="btn btn-outline">
                    <i class="fas fa-sign-out-alt"></i> Sair
                </a>
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <h2>Bem-vindo à sua Área</h2>
            <p>Acompanhe seus processos e converse com nosso time jurídico</p>
        </div>
    </section>

    <!-- Área do Cliente -->
    <section class="user-area">
        <div class="container">
            <div class="user-container">
                <!-- Sidebar -->
                <div class="user-sidebar">
                    <div class="user-profile">
                        <div class="user-avatar">
                            <i class="fas fa-user"></i>
                        </div>
                        <div class="user-info">
                            <h3><?php echo htmlspecialchars($cliente['fullName']); ?></h3>
                            <p>Cliente desde <?php echo date('m/Y', strtotime($cliente['created_at'])); ?></p>
                        </div>
                    </div>
                    <ul class="user-menu">
                        <li><a href="#" class="active"><i class="fas fa-home"></i> Dashboard</a></li>
                        <li><a href="#"><i class="fas fa-file-contract"></i> Meus Processos</a></li>
                        <li><a href="#"><i class="fas fa-file-pdf"></i> Documentos</a></li>
                        <li><a href="area_cliente.php?tab=chat" class="<?php echo $aba_ativa == 'chat' ? 'active' : ''; ?>"><i class="fas fa-comments"></i> Chat com Advogado</a></li>
                        <li><a href="area_cliente.php?tab=agendamentos" class="<?php echo $aba_ativa == 'agendamentos' ? 'active' : ''; ?>"><i class="fas fa-calendar-alt"></i> Agendamentos</a></li>
                    </ul>
                </div>

                <!-- Conteúdo Principal -->
                <div class="user-content">
                    <h3>Minha Área</h3>
                    
                    <!-- Tabs -->
                    <div class="tabs">
                        <div class="tab <?php echo $aba_ativa == 'chat' ? 'active' : ''; ?>" data-tab="chat">Chat com Advogado</div>
                        <div class="tab <?php echo $aba_ativa == 'agendamentos' ? 'active' : ''; ?>" data-tab="agendamentos">Agendamentos</div>
                    </div>

                    <!-- Tab Content - Chat -->
                    <div class="tab-content <?php echo $aba_ativa == 'chat' ? 'active' : ''; ?>" id="chat-tab">
                        <!-- Nova Conversa -->
                        <div class="nova-conversa-form">
                            <h4><i class="fas fa-plus-circle"></i> Iniciar Nova Conversa</h4>
                            <form method="POST" style="display: flex; gap: 1rem; margin-top: 1rem;">
                                <input type="text" name="assunto" placeholder="Assunto da conversa..." required style="flex: 1; padding: 1rem; border: 1px solid #ddd; border-radius: var(--border-radius); font-size: 1rem;">
                                <button type="submit" name="nova_conversa" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> Nova Conversa
                                </button>
                            </form>
                        </div>

                        <!-- Lista de Conversas -->
                        <?php if (count($conversas) > 0): ?>
                        <div style="margin-bottom: 2rem;">
                            <h4 style="color: var(--primary-color); margin-bottom: 1rem; font-family: 'Playfair Display', serif;">Suas Conversas</h4>
                            <div class="conversas-lista">
                                <?php foreach ($conversas as $conversa): ?>
                                <div class="conversa-item <?php echo (isset($_GET['conversa']) && $_GET['conversa'] == $conversa['id']) ? 'ativa' : ''; ?>" 
                                     onclick="location.href='area_cliente.php?tab=chat&conversa=<?php echo $conversa['id']; ?>'">
                                    <div class="conversa-info">
                                        <div>
                                            <strong style="color: var(--primary-color);"><?php echo htmlspecialchars($conversa['assunto']); ?></strong>
                                            <div style="font-size: 0.9rem; color: var(--text-light); margin: 0.5rem 0;">
                                                Com <?php echo htmlspecialchars($conversa['advogado_nome']); ?>
                                            </div>
                                            <small style="color: var(--text-light);">Última atualização: <?php echo date('d/m/Y H:i', strtotime($conversa['updated_at'])); ?></small>
                                        </div>
                                        <?php if ($conversa['mensagens_nao_lidas'] > 0): ?>
                                        <div class="badge"><?php echo $conversa['mensagens_nao_lidas']; ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Chat -->
                        <?php if (isset($_GET['conversa'])): ?>
                        
                        <?php if ($conversa_atual): ?>
                        <div class="chat-real">
                            <div class="chat-header">
                                <div>
                                    <h4 style="margin: 0; font-family: 'Playfair Display', serif;"><?php echo htmlspecialchars($conversa_atual['assunto']); ?></h4>
                                    <small>Com <?php echo htmlspecialchars($conversa_atual['advogado_nome']); ?></small>
                                </div>
                                <div>
                                    <?php if ($conversa_atual['status'] == 'aberta'): ?>
                                    <span style="background: #38a169; color: white; padding: 0.5rem 1rem; border-radius: 20px; font-size: 0.8rem; font-weight: 600;">
                                        <i class="fas fa-circle" style="font-size: 0.6rem; margin-right: 0.3rem;"></i> Aberta
                                    </span>
                                    <?php else: ?>
                                    <span style="background: #e53e3e; color: white; padding: 0.5rem 1rem; border-radius: 20px; font-size: 0.8rem; font-weight: 600;">
                                        <i class="fas fa-circle" style="font-size: 0.6rem; margin-right: 0.3rem;"></i> Fechada
                                    </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="chat-messages" id="chatMessages">
                                <?php if (count($mensagens) == 0): ?>
                                <div style="text-align: center; color: var(--text-light); padding: 3rem;">
                                    <i class="fas fa-comments" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.5;"></i>
                                    <p>Nenhuma mensagem ainda. Inicie a conversa!</p>
                                </div>
                                <?php else: ?>
                                    <?php foreach ($mensagens as $mensagem): ?>
                                    <div class="message <?php echo $mensagem['remetente']; ?>">
                                        <div><?php echo htmlspecialchars($mensagem['mensagem']); ?></div>
                                        <div class="message-info">
                                            <?php echo $mensagem['remetente'] == 'cliente' ? 'Você' : htmlspecialchars($mensagem['nome_remetente']); ?> • 
                                            <?php echo date('d/m/Y H:i', strtotime($mensagem['created_at'])); ?>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                            
                            <?php if ($conversa_atual['status'] == 'aberta'): ?>
                            <form method="POST" class="chat-input" id="chatForm">
                                <input type="hidden" name="conversa_id" value="<?php echo $conversa_id; ?>">
                                <input type="text" name="mensagem" placeholder="Digite sua mensagem..." required id="mensagemInput">
                                <button type="submit" name="nova_mensagem">
                                    <i class="fas fa-paper-plane"></i> Enviar
                                </button>
                            </form>
                            <div class="refresh-controls">
                                <button type="button" onclick="location.reload()" class="btn btn-outline" style="color: var(--primary-color); border-color: var(--primary-color); padding: 0.5rem 1rem;">
                                    <i class="fas fa-sync-alt"></i> Atualizar Chat
                                </button>
                                <div class="auto-refresh-toggle">
                                    <label class="switch">
                                        <input type="checkbox" id="autoRefreshToggle">
                                        <span class="slider"></span>
                                    </label>
                                    <span>Atualização automática</span>
                                </div>
                            </div>
                            <?php else: ?>
                            <div style="padding: 1.5rem; text-align: center; background: #f8f9fa; color: var(--text-light);">
                                <i class="fas fa-lock" style="margin-right: 0.5rem;"></i> Esta conversa foi encerrada
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php else: ?>
                        <div style="text-align: center; padding: 3rem; color: var(--text-light); background: var(--white); border-radius: var(--border-radius);">
                            <i class="fas fa-exclamation-triangle" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.5;"></i>
                            <p>Conversa não encontrada ou você não tem acesso.</p>
                        </div>
                        <?php endif; ?>
                        
                        <?php else: ?>
                        <div style="text-align: center; padding: 4rem; color: var(--text-light); background: var(--white); border-radius: var(--border-radius);">
                            <i class="fas fa-comments" style="font-size: 4rem; margin-bottom: 1rem; opacity: 0.5;"></i>
                            <h4 style="color: var(--primary-color); margin-bottom: 1rem;">Selecione uma conversa ou inicie uma nova</h4>
                            <p>Escolha uma conversa da lista acima para visualizar as mensagens</p>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Tab Content - Agendamentos -->
                    <div class="tab-content <?php echo $aba_ativa == 'agendamentos' ? 'active' : ''; ?>" id="agendamentos-tab">
                        <?php if (isset($_GET['sucesso'])): ?>
                        <div class="alert success">
                            <i class="fas fa-check-circle"></i> Agendamento realizado com sucesso!
                        </div>
                        <?php endif; ?>

                        <!-- Novo Agendamento -->
                        <div class="novo-agendamento-form">
                            <h4><i class="fas fa-calendar-plus"></i> Novo Agendamento</h4>
                            <form method="POST">
                                <div class="form-grid">
                                    <div class="form-group">
                                        <label for="data_agendamento"><i class="fas fa-calendar-day"></i> Data</label>
                                        <input type="date" id="data_agendamento" name="data_agendamento" required min="<?php echo date('Y-m-d'); ?>">
                                    </div>
                                    <div class="form-group">
                                        <label for="hora_agendamento"><i class="fas fa-clock"></i> Hora</label>
                                        <input type="time" id="hora_agendamento" name="hora_agendamento" required min="09:00" max="18:00">
                                    </div>
                                    <div class="form-group">
                                        <label for="tipo_consulta"><i class="fas fa-briefcase"></i> Tipo de Consulta</label>
                                        <select id="tipo_consulta" name="tipo_consulta" required>
                                            <option value="">Selecione o tipo</option>
                                            <option value="presencial">Presencial</option>
                                            <option value="online">Online (Videoconferência)</option>
                                            <option value="telefonica">Telefônica</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="descricao"><i class="fas fa-file-alt"></i> Descrição do Assunto</label>
                                    <textarea id="descricao" name="descricao" rows="3" placeholder="Descreva brevemente o assunto que deseja tratar..." required></textarea>
                                </div>
                                <button type="submit" name="novo_agendamento" class="btn btn-secondary">
                                    <i class="fas fa-calendar-check"></i> Agendar Consulta
                                </button>
                            </form>
                        </div>

                        <!-- Lista de Agendamentos -->
                        <div class="agendamentos-lista">
                            <h4 style="color: var(--primary-color); margin-bottom: 1rem; font-family: 'Playfair Display', serif;">Meus Agendamentos</h4>
                            
                            <?php if (count($agendamentos) > 0): ?>
                                <?php foreach ($agendamentos as $agendamento): ?>
                                <div class="agendamento-item">
                                    <div class="agendamento-header">
                                        <div class="agendamento-info">
                                            <h4><?php echo htmlspecialchars($agendamento['tipo_consulta']); ?></h4>
                                            <p>Com <?php echo htmlspecialchars($agendamento['advogado_nome']); ?></p>
                                        </div>
                                        <div class="status <?php echo $agendamento['status']; ?>">
                                            <?php 
                                            $status_text = [
                                                'agendado' => 'Agendado',
                                                'confirmado' => 'Confirmado',
                                                'cancelado' => 'Cancelado',
                                                'concluido' => 'Concluído'
                                            ];
                                            echo $status_text[$agendamento['status']] ?? $agendamento['status'];
                                            ?>
                                        </div>
                                    </div>
                                    <div class="agendamento-details">
                                        <div>
                                            <strong><i class="fas fa-calendar"></i> Data:</strong><br>
                                            <?php echo date('d/m/Y', strtotime($agendamento['data_agendamento'])); ?>
                                        </div>
                                        <div>
                                            <strong><i class="fas fa-clock"></i> Hora:</strong><br>
                                            <?php echo date('H:i', strtotime($agendamento['data_agendamento'])); ?>
                                        </div>
                                        <div>
                                            <strong><i class="fas fa-sticky-note"></i> Assunto:</strong><br>
                                            <?php echo htmlspecialchars($agendamento['descricao']); ?>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                            <div style="text-align: center; padding: 3rem; color: var(--text-light); background: var(--white); border-radius: var(--border-radius);">
                                <i class="fas fa-calendar-times" style="font-size: 4rem; margin-bottom: 1rem; opacity: 0.5;"></i>
                                <h4 style="color: var(--primary-color); margin-bottom: 1rem;">Nenhum agendamento encontrado</h4>
                                <p>Realize seu primeiro agendamento usando o formulário acima</p>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-column">
                    <h3>Felix Advocacia</h3>
                    <p>Excelência jurídica com compromisso, ética e dedicação para defender seus direitos e interesses.</p>
                </div>
                <div class="footer-column">
                    <h3>Links Rápidos</h3>
                    <ul>
                        <li><a href="index.html"><i class="fas fa-chevron-right"></i> Início</a></li>
                        <li><a href="index.html#services"><i class="fas fa-chevron-right"></i> Serviços</a></li>
                        <li><a href="area_cliente.php"><i class="fas fa-chevron-right"></i> Área do Cliente</a></li>
                        <li><a href="index.html#contact"><i class="fas fa-chevron-right"></i> Contato</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h3>Áreas de Atuação</h3>
                    <ul>
                        <li><a href="direitocivil.html"><i class="fas fa-chevron-right"></i> Direito Civil</a></li>
                        <li><a href="direitotributario.html"><i class="fas fa-chevron-right"></i> Direito Tributário</a></li>
                        <li><a href="direitoimobiliario.html"><i class="fas fa-chevron-right"></i> Direito Imobiliário</a></li>
                        <li><a href="direitotrabalhista.html"><i class="fas fa-chevron-right"></i> Direito Trabalhista</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h3>Contato</h3>
                    <ul>
                        <li><a href="#"><i class="fas fa-map-marker-alt"></i> Av. Paulista, 1000 - SP</a></li>
                        <li><a href="#"><i class="fas fa-phone"></i> (11) 3456-7890</a></li>
                        <li><a href="#"><i class="fas fa-envelope"></i> contato@felixadvocacia.com.br</a></li>
                    </ul>
                </div>
            </div>
            <div class="copyright">
                <p>&copy; 2023 Felix Advocacia. Todos os direitos reservados.</p>
            </div>
        </div>
    </footer>

    <script>
        // Sistema de Tabs
        document.querySelectorAll('.tab').forEach(tab => {
            tab.addEventListener('click', function() {
                const tabId = this.getAttribute('data-tab');
                
                // Remove active class de todas as tabs
                document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
                document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
                
                // Adiciona active class na tab clicada
                this.classList.add('active');
                document.getElementById(tabId + '-tab').classList.add('active');
                
                // Atualiza URL sem recarregar a página
                const url = new URL(window.location);
                url.searchParams.set('tab', tabId);
                window.history.pushState({}, '', url);
            });
        });

        // Rolagem automática para o final do chat
        const chatMessages = document.getElementById('chatMessages');
        if (chatMessages) {
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }

        // Controle de auto-refresh
        let autoRefreshInterval;
        const autoRefreshToggle = document.getElementById('autoRefreshToggle');
        const mensagemInput = document.getElementById('mensagemInput');

        function startAutoRefresh() {
            autoRefreshInterval = setInterval(function() {
                if (window.location.search.includes('conversa=')) {
                    window.location.reload();
                }
            }, 10000); // 10 segundos
        }

        function stopAutoRefresh() {
            if (autoRefreshInterval) {
                clearInterval(autoRefreshInterval);
            }
        }

        // Iniciar auto-refresh por padrão
        if (window.location.search.includes('conversa=')) {
            startAutoRefresh();
            if (autoRefreshToggle) {
                autoRefreshToggle.checked = true;
            }
        }

        // Controlar auto-refresh pelo toggle
        if (autoRefreshToggle) {
            autoRefreshToggle.addEventListener('change', function() {
                if (this.checked) {
                    startAutoRefresh();
                } else {
                    stopAutoRefresh();
                }
            });
        }

        // Parar auto-refresh quando o usuário estiver digitando
        if (mensagemInput) {
            mensagemInput.addEventListener('focus', function() {
                stopAutoRefresh();
                if (autoRefreshToggle) {
                    autoRefreshToggle.checked = false;
                }
            });

            mensagemInput.addEventListener('blur', function() {
                if (autoRefreshToggle && autoRefreshToggle.checked) {
                    startAutoRefresh();
                }
            });
        }

        // Efeito de header ao rolar
        window.addEventListener('scroll', function() {
            const header = document.querySelector('header');
            if (window.scrollY > 100) {
                header.style.padding = '0.8rem 0';
                header.style.boxShadow = '0 4px 20px rgba(0, 0, 0, 0.15)';
            } else {
                header.style.padding = '1.2rem 0';
                header.style.boxShadow = '0 4px 20px rgba(0, 0, 0, 0.1)';
            }
        });

        // Focar no input de mensagem quando a página carregar
        if (mensagemInput) {
            mensagemInput.focus();
        }

        // Configuração de data mínima para agendamentos
        const dataInput = document.getElementById('data_agendamento');
        if (dataInput) {
            const today = new Date().toISOString().split('T')[0];
            dataInput.min = today;
        }
    </script>
</body>
</html>