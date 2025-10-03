<?php
// Conexão com o banco de dados
$host = 'localhost';
$usuario = 'root';
$senha = '';
$banco = 'felix_advocacia';

$conn = mysqli_connect($host, $usuario, $senha, $banco);

if (!$conn) {
    die("Erro na conexão: " . mysqli_connect_error());
}

mysqli_set_charset($conn, 'utf8');
date_default_timezone_set('America/Sao_Paulo');

// Iniciar sessão para obter o ID do advogado logado
session_start();

// Supondo que o ID do advogado logado esteja na sessão
$advogado_id = isset($_SESSION['advogado_id']) ? $_SESSION['advogado_id'] : 1;

// Variáveis para controle
$mensagem = '';
$erro = '';

// Verificar se a tabela agendamentos existe, se não, criar
$sql_check_agendamentos = "SHOW TABLES LIKE 'agendamentos'";
$result_check = mysqli_query($conn, $sql_check_agendamentos);
if (mysqli_num_rows($result_check) == 0) {
    // Criar tabela agendamentos
    $sql_create_agendamentos = "CREATE TABLE agendamentos (
        id INT PRIMARY KEY AUTO_INCREMENT,
        cliente_id INT,
        advogado_id INT,
        data_agendamento DATETIME,
        tipo_consulta ENUM('presencial', 'online', 'telefonica'),
        descricao TEXT,
        status ENUM('agendado', 'confirmado', 'cancelado', 'concluido') DEFAULT 'agendado',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    if (!mysqli_query($conn, $sql_create_agendamentos)) {
        $erro = "Erro ao criar tabela agendamentos: " . mysqli_error($conn);
    }
}

// Buscar clientes para o select - usando a estrutura correta
$sql_clientes = "SELECT Id, FullName, phone, email FROM Clientes ORDER BY FullName";
$result_clientes = mysqli_query($conn, $sql_clientes);
$clientes = [];

if ($result_clientes && mysqli_num_rows($result_clientes) > 0) {
    $clientes = mysqli_fetch_all($result_clientes, MYSQLI_ASSOC);
} else {
    // Se não houver clientes, mostrar mensagem
    $erro = "Nenhum cliente cadastrado no sistema. Por favor, cadastre clientes primeiro.";
}

// Processar formulário de agendamento
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['acao'])) {
        $acao = $_POST['acao'];
        
        if ($acao == 'adicionar') {
            // Adicionar novo agendamento
            $cliente_id = intval($_POST['cliente_id']);
            $data_agendamento = mysqli_real_escape_string($conn, $_POST['data_agendamento']);
            $hora_agendamento = mysqli_real_escape_string($conn, $_POST['hora_agendamento']);
            $data_hora = $data_agendamento . ' ' . $hora_agendamento . ':00';
            $tipo_consulta = mysqli_real_escape_string($conn, $_POST['tipo_consulta']);
            $descricao = mysqli_real_escape_string($conn, $_POST['descricao']);
            $status = 'agendado';
            
            $sql = "INSERT INTO agendamentos (cliente_id, advogado_id, data_agendamento, tipo_consulta, descricao, status) 
                    VALUES ($cliente_id, $advogado_id, '$data_hora', '$tipo_consulta', '$descricao', '$status')";
            
            if (mysqli_query($conn, $sql)) {
                $mensagem = "Agendamento adicionado com sucesso!";
            } else {
                $erro = "Erro ao adicionar agendamento: " . mysqli_error($conn);
            }
        }
        elseif ($acao == 'editar') {
            // Editar agendamento existente
            $id = intval($_POST['id']);
            $cliente_id = intval($_POST['cliente_id']);
            $data_agendamento = mysqli_real_escape_string($conn, $_POST['data_agendamento']);
            $hora_agendamento = mysqli_real_escape_string($conn, $_POST['hora_agendamento']);
            $data_hora = $data_agendamento . ' ' . $hora_agendamento . ':00';
            $tipo_consulta = mysqli_real_escape_string($conn, $_POST['tipo_consulta']);
            $descricao = mysqli_real_escape_string($conn, $_POST['descricao']);
            $status = mysqli_real_escape_string($conn, $_POST['status']);
            
            $sql = "UPDATE agendamentos SET 
                    cliente_id = $cliente_id, 
                    data_agendamento = '$data_hora', 
                    tipo_consulta = '$tipo_consulta', 
                    descricao = '$descricao', 
                    status = '$status' 
                    WHERE id = $id AND advogado_id = $advogado_id";
            
            if (mysqli_query($conn, $sql)) {
                $mensagem = "Agendamento atualizado com sucesso!";
            } else {
                $erro = "Erro ao atualizar agendamento: " . mysqli_error($conn);
            }
        }
        elseif ($acao == 'excluir') {
            // Excluir agendamento
            $id = intval($_POST['id']);
            
            $sql = "DELETE FROM agendamentos WHERE id = $id AND advogado_id = $advogado_id";
            
            if (mysqli_query($conn, $sql)) {
                $mensagem = "Agendamento excluído com sucesso!";
            } else {
                $erro = "Erro ao excluir agendamento: " . mysqli_error($conn);
            }
        }
        // Processar envio de mensagem do chat
        elseif ($acao == 'enviar_mensagem') {
            $conversa_id = intval($_POST['conversa_id']);
            $mensagem_texto = mysqli_real_escape_string($conn, $_POST['mensagem']);
            $remetente = 'advogado'; // O advogado está enviando
            
            $sql = "INSERT INTO mensagens (conversa_id, remetente, mensagem, lida, created_at) 
                    VALUES ($conversa_id, '$remetente', '$mensagem_texto', 0, NOW())";
            
            if (mysqli_query($conn, $sql)) {
                // Mensagem enviada com sucesso
                echo json_encode(['success' => true]);
                exit;
            } else {
                echo json_encode(['success' => false, 'error' => mysqli_error($conn)]);
                exit;
            }
        }
    }
}

// Buscar agendamentos do advogado logado com join para pegar dados do cliente
$sql_agendamentos = "SELECT a.*, c.FullName as cliente_nome, c.phone as cliente_telefone, c.email as cliente_email 
                     FROM agendamentos a 
                     LEFT JOIN Clientes c ON a.cliente_id = c.Id 
                     WHERE a.advogado_id = $advogado_id 
                     ORDER BY a.data_agendamento DESC";
$result_agendamentos = mysqli_query($conn, $sql_agendamentos);
$agendamentos = [];
if ($result_agendamentos) {
    $agendamentos = mysqli_fetch_all($result_agendamentos, MYSQLI_ASSOC);
}

// Buscar conversas para o chat
$sql_conversas = "SELECT DISTINCT c.Id as cliente_id, c.FullName, c.phone, c.email,
                 (SELECT COUNT(*) FROM mensagens m WHERE m.conversa_id = c.Id AND m.remetente = 'cliente' AND m.lida = 0) as mensagens_nao_lidas,
                 (SELECT mensagem FROM mensagens WHERE conversa_id = c.Id ORDER BY created_at DESC LIMIT 1) as ultima_mensagem,
                 (SELECT created_at FROM mensagens WHERE conversa_id = c.Id ORDER BY created_at DESC LIMIT 1) as ultima_mensagem_data
                 FROM Clientes c
                 LEFT JOIN mensagens m ON c.Id = m.conversa_id
                 WHERE m.id IS NOT NULL
                 ORDER BY ultima_mensagem_data DESC";
$result_conversas = mysqli_query($conn, $sql_conversas);
$conversas = [];
if ($result_conversas) {
    $conversas = mysqli_fetch_all($result_conversas, MYSQLI_ASSOC);
}

// Se uma conversa específica foi selecionada, buscar suas mensagens
$conversa_selecionada = null;
$mensagens_conversa = [];
if (isset($_GET['conversa_id'])) {
    $conversa_id = intval($_GET['conversa_id']);
    
    // Buscar informações do cliente
    $sql_cliente = "SELECT Id, FullName, phone, email FROM Clientes WHERE Id = $conversa_id";
    $result_cliente = mysqli_query($conn, $sql_cliente);
    if ($result_cliente && mysqli_num_rows($result_cliente) > 0) {
        $conversa_selecionada = mysqli_fetch_assoc($result_cliente);
        
        // Buscar mensagens da conversa
        $sql_mensagens = "SELECT * FROM mensagens WHERE conversa_id = $conversa_id ORDER BY created_at ASC";
        $result_mensagens = mysqli_query($conn, $sql_mensagens);
        if ($result_mensagens) {
            $mensagens_conversa = mysqli_fetch_all($result_mensagens, MYSQLI_ASSOC);
            
            // Marcar mensagens do cliente como lidas
            $sql_update_lidas = "UPDATE mensagens SET lida = 1 WHERE conversa_id = $conversa_id AND remetente = 'cliente' AND lida = 0";
            mysqli_query($conn, $sql_update_lidas);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Área do Advogado - Felix Advocacia</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }

        .admin-container {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            width: 250px;
            background: #2c3e50;
            color: white;
            padding: 20px 0;
        }

        .logo {
            text-align: center;
            padding: 20px;
            border-bottom: 1px solid #34495e;
        }

        .logo h2 {
            color: #3498db;
            font-size: 24px;
        }

        .nav-links {
            list-style: none;
            padding: 20px 0;
        }

        .nav-links li {
            padding: 15px 25px;
            border-left: 4px solid transparent;
            transition: all 0.3s ease;
        }

        .nav-links li:hover {
            background: #34495e;
            border-left-color: #3498db;
        }

        .nav-links a {
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .nav-links i {
            width: 20px;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            padding: 30px;
            background: #ecf0f1;
        }

        .header {
            background: white;
            padding: 20px 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }

        .header h1 {
            color: #2c3e50;
            font-size: 28px;
        }

        /* Agenda Section */
        .agenda-section, .chat-section {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }

        .section-title {
            color: #2c3e50;
            margin-bottom: 30px;
            font-size: 24px;
            border-bottom: 2px solid #3498db;
            padding-bottom: 10px;
        }

        .agenda-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }

        .agenda-form, .agenda-list {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 8px;
            border: 1px solid #e9ecef;
        }

        .agenda-form h3, .agenda-list h3 {
            color: #2c3e50;
            margin-bottom: 20px;
            font-size: 18px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #2c3e50;
        }

        .form-control {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
            transition: border-color 0.3s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: #3498db;
            box-shadow: 0 0 0 2px rgba(52, 152, 219, 0.2);
        }

        .form-buttons {
            display: flex;
            gap: 10px;
            margin-top: 25px;
        }

        .btn {
            padding: 12px 25px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: #3498db;
            color: white;
        }

        .btn-primary:hover {
            background: #2980b9;
            transform: translateY(-2px);
        }

        .btn-secondary {
            background: #95a5a6;
            color: white;
        }

        .btn-secondary:hover {
            background: #7f8c8d;
        }

        .btn-danger {
            background: #e74c3c;
            color: white;
        }

        .btn-danger:hover {
            background: #c0392b;
        }

        /* Table Styles */
        .agenda-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 8px;
            overflow: hidden;
        }

        .agenda-table th,
        .agenda-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e9ecef;
        }

        .agenda-table th {
            background: #34495e;
            color: white;
            font-weight: 600;
            font-size: 14px;
        }

        .agenda-table tr:hover {
            background: #f8f9fa;
        }

        .cliente-info {
            display: flex;
            flex-direction: column;
        }

        .cliente-nome {
            font-weight: 600;
            color: #2c3e50;
        }

        .cliente-contato {
            font-size: 12px;
            color: #7f8c8d;
        }

        .actions {
            display: flex;
            gap: 8px;
        }

        .btn-edit, .btn-delete {
            padding: 8px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            color: white;
            transition: all 0.3s ease;
        }

        .btn-edit {
            background: #f39c12;
        }

        .btn-edit:hover {
            background: #e67e22;
            transform: scale(1.05);
        }

        .btn-delete {
            background: #e74c3c;
        }

        .btn-delete:hover {
            background: #c0392b;
            transform: scale(1.05);
        }

        /* Status Badges */
        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-agendado {
            background: #3498db;
            color: white;
        }

        .status-confirmado {
            background: #27ae60;
            color: white;
        }

        .status-cancelado {
            background: #e74c3c;
            color: white;
        }

        .status-concluido {
            background: #2ecc71;
            color: white;
        }

        /* Tipo Consulta Badges */
        .tipo-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 600;
            color: white;
        }

        .tipo-presencial {
            background: #9b59b6;
        }

        .tipo-online {
            background: #3498db;
        }

        .tipo-telefonica {
            background: #1abc9c;
        }

        /* Alerts */
        .alert {
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            border: 1px solid transparent;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border-color: #c3e6cb;
        }

        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border-color: #f5c6cb;
        }

        .alert-warning {
            background: #fff3cd;
            color: #856404;
            border-color: #ffeaa7;
        }

        .no-data {
            text-align: center;
            color: #7f8c8d;
            font-style: italic;
            padding: 40px;
        }

        /* Modal */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }

        .modal-content {
            background-color: white;
            margin: 15% auto;
            padding: 30px;
            border-radius: 10px;
            width: 400px;
            max-width: 90%;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
        }

        .modal-buttons {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            margin-top: 20px;
        }

        /* Chat Styles - Estilo Felix Advocacia */
        .chat-container {
            display: grid;
            grid-template-columns: 350px 1fr;
            gap: 20px;
            height: 500px;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .chat-contatos {
            background: #f8f9fa;
            border-right: 1px solid #e9ecef;
            overflow-y: auto;
        }

        .chat-contatos-header {
            padding: 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .chat-contatos-header h4 {
            margin: 0;
            font-size: 18px;
        }

        .chat-contato {
            padding: 15px 20px;
            border-bottom: 1px solid #e9ecef;
            cursor: pointer;
            transition: all 0.3s ease;
            background: white;
        }

        .chat-contato:hover {
            background: #e3f2fd;
        }

        .chat-contato.active {
            background: #3498db;
            color: white;
        }

        .chat-contato.active .contato-info h4,
        .chat-contato.active .contato-info p {
            color: white;
        }

        .contato-info h4 {
            margin: 0 0 5px 0;
            font-size: 14px;
            color: #2c3e50;
        }

        .contato-info p {
            margin: 0;
            font-size: 12px;
            color: #7f8c8d;
        }

        .contato-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 8px;
        }

        .badge-mensagens {
            background: #e74c3c;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
            font-weight: bold;
        }

        .chat-area {
            display: flex;
            flex-direction: column;
            background: white;
            height: 100%;
        }

        .chat-header {
            padding: 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-bottom: 1px solid #e9ecef;
        }

        .chat-header h4 {
            margin: 0 0 5px 0;
            font-size: 16px;
        }

        .chat-header small {
            opacity: 0.9;
        }

        .chat-mensagens {
            flex: 1;
            padding: 20px;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 15px;
            background: #f8f9fa;
            min-height: 0;
        }

        .mensagem {
            max-width: 70%;
            padding: 12px 16px;
            border-radius: 18px;
            position: relative;
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .mensagem.advogado {
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
            color: white;
            align-self: flex-end;
            border-bottom-right-radius: 5px;
        }

        .mensagem.cliente {
            background: white;
            color: #2c3e50;
            align-self: flex-start;
            border: 1px solid #e9ecef;
            border-bottom-left-radius: 5px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        .mensagem-hora {
            font-size: 11px;
            opacity: 0.7;
            margin-top: 5px;
            text-align: right;
        }

        /* CORREÇÃO DA CAIXA DE CHAT */
        .chat-input {
            display: flex !important;
            visibility: visible !important;
            opacity: 1 !important;
            padding: 20px;
            border-top: 1px solid #e9ecef;
            background: white;
            position: relative;
            z-index: 10;
        }

        .input-group {
            display: flex;
            gap: 10px;
            align-items: center;
            width: 100%;
        }

        .chat-input-field {
            display: block !important;
            width: 100% !important;
            flex: 1;
            padding: 12px 20px;
            border: 2px solid #e9ecef;
            border-radius: 25px;
            outline: none;
            font-size: 14px;
            transition: border-color 0.3s ease;
            background: white;
        }

        .chat-input-field:focus {
            border-color: #3498db;
            box-shadow: 0 0 0 2px rgba(52, 152, 219, 0.2);
        }

        .btn-enviar {
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
            color: white;
            border: none;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .btn-enviar:hover {
            transform: scale(1.05);
            box-shadow: 0 3px 10px rgba(52, 152, 219, 0.3);
        }

        .btn-enviar:disabled {
            background: #95a5a6;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        .sem-conversa {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100%;
            color: #7f8c8d;
            text-align: center;
            padding: 40px;
            flex-direction: column;
        }

        .sem-conversa i {
            font-size: 48px;
            margin-bottom: 15px;
            opacity: 0.5;
        }

        #form-mensagem {
            width: 100%;
            display: flex;
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .agenda-content {
                grid-template-columns: 1fr;
            }
            
            .chat-container {
                grid-template-columns: 300px 1fr;
            }
        }

        @media (max-width: 768px) {
            .admin-container {
                flex-direction: column;
            }
            
            .sidebar {
                width: 100%;
            }
            
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .agenda-table {
                font-size: 12px;
            }
            
            .agenda-table th,
            .agenda-table td {
                padding: 8px;
            }
            
            .chat-container {
                grid-template-columns: 1fr;
                height: 400px;
            }
            
            .chat-contatos {
                max-height: 200px;
            }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="logo">
                <h2>Felix Advocacia</h2>
                <p>Área do Advogado</p>
            </div>
            <ul class="nav-links">
                <li><a href="#"><i class="fas fa-home"></i> Dashboard</a></li>
                <li><a href="#" class="active"><i class="fas fa-calendar-alt"></i> Agenda</a></li>
                <li><a href="#"><i class="fas fa-users"></i> Clientes</a></li>
                <li><a href="#"><i class="fas fa-file-contract"></i> Processos</a></li>
                <li><a href="#"><i class="fas fa-comments"></i> Chat</a></li>
                <li><a href="#"><i class="fas fa-chart-bar"></i> Relatórios</a></li>
                <li><a href="#"><i class="fas fa-cog"></i> Configurações</a></li>
                <li><a href="#" onclick="confirmarLogoutAdvogado()"><i class="fas fa-sign-out-alt"></i> Sair</a></li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="header">
                <h1><i class="fas fa-calendar-alt"></i> Gestão de Agenda</h1>
                <p>Gerencie seus compromissos e agendamentos</p>
            </div>

            <!-- Seção Agenda -->
            <section class="agenda-section">
                <h2 class="section-title">Agenda de Compromissos</h2>
                
                <!-- Mensagens de feedback -->
                <?php if ($mensagem): ?>
                    <div class="alert alert-success"><?php echo $mensagem; ?></div>
                <?php endif; ?>
                
                <?php if ($erro): ?>
                    <div class="alert alert-danger"><?php echo $erro; ?></div>
                <?php endif; ?>
                
                <div class="agenda-content">
                    <!-- Formulário para adicionar/editar agendamentos -->
                    <div class="agenda-form">
                        <h3 id="form-title"><i class="fas fa-plus-circle"></i> Novo Agendamento</h3>
                        <form id="form-agendamento" method="POST">
                            <input type="hidden" name="acao" id="acao" value="adicionar">
                            <input type="hidden" name="id" id="agendamento_id">
                            
                            <div class="form-group">
                                <label for="cliente_id"><i class="fas fa-user"></i> Cliente:</label>
                                <select id="cliente_id" name="cliente_id" required class="form-control">
                                    <option value="">Selecione um cliente...</option>
                                    <?php foreach ($clientes as $cliente): ?>
                                        <option value="<?php echo $cliente['Id']; ?>">
                                            <?php echo htmlspecialchars($cliente['FullName']); ?> 
                                            - <?php echo htmlspecialchars($cliente['phone']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <?php if (empty($clientes)): ?>
                                    <small style="color: #e74c3c;">Nenhum cliente cadastrado. Cadastre clientes primeiro.</small>
                                <?php endif; ?>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="data_agendamento"><i class="fas fa-calendar"></i> Data:</label>
                                    <input type="date" id="data_agendamento" name="data_agendamento" required class="form-control">
                                </div>
                                
                                <div class="form-group">
                                    <label for="hora_agendamento"><i class="fas fa-clock"></i> Hora:</label>
                                    <input type="time" id="hora_agendamento" name="hora_agendamento" required class="form-control">
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="tipo_consulta"><i class="fas fa-comments"></i> Tipo de Consulta:</label>
                                <select id="tipo_consulta" name="tipo_consulta" required class="form-control">
                                    <option value="">Selecione o tipo...</option>
                                    <option value="presencial">Presencial</option>
                                    <option value="online">Online</option>
                                    <option value="telefonica">Telefônica</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="descricao"><i class="fas fa-file-alt"></i> Descrição:</label>
                                <textarea id="descricao" name="descricao" rows="3" placeholder="Descreva o propósito da consulta..." class="form-control"></textarea>
                            </div>
                            
                            <div class="form-group" id="status-group" style="display: none;">
                                <label for="status"><i class="fas fa-info-circle"></i> Status:</label>
                                <select id="status" name="status" class="form-control">
                                    <option value="agendado">Agendado</option>
                                    <option value="confirmado">Confirmado</option>
                                    <option value="cancelado">Cancelado</option>
                                    <option value="concluido">Concluído</option>
                                </select>
                            </div>
                            
                            <div class="form-buttons">
                                <button type="submit" class="btn btn-primary" <?php echo empty($clientes) ? 'disabled' : ''; ?>>
                                    <i class="fas fa-save"></i> Salvar Agendamento
                                </button>
                                <button type="button" id="btn-cancelar" class="btn btn-secondary" style="display: none;">
                                    <i class="fas fa-times"></i> Cancelar
                                </button>
                            </div>
                        </form>
                    </div>
                    
                    <!-- Lista de agendamentos -->
                    <div class="agenda-list">
                        <h3><i class="fas fa-list"></i> Meus Agendamentos</h3>
                        
                        <?php if (empty($agendamentos)): ?>
                            <p class="no-data">Nenhum agendamento encontrado.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="agenda-table">
                                    <thead>
                                        <tr>
                                            <th>Cliente</th>
                                            <th>Data/Hora</th>
                                            <th>Tipo</th>
                                            <th>Status</th>
                                            <th>Ações</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($agendamentos as $agendamento): 
                                            $data_hora = date('d/m/Y H:i', strtotime($agendamento['data_agendamento']));
                                            $status_class = 'status-' . $agendamento['status'];
                                            $tipo_class = 'tipo-' . $agendamento['tipo_consulta'];
                                        ?>
                                        <tr>
                                            <td>
                                                <div class="cliente-info">
                                                    <span class="cliente-nome"><?php echo htmlspecialchars($agendamento['cliente_nome']); ?></span>
                                                    <?php if ($agendamento['cliente_telefone']): ?>
                                                        <span class="cliente-contato"><?php echo htmlspecialchars($agendamento['cliente_telefone']); ?></span>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td><?php echo $data_hora; ?></td>
                                            <td><span class="tipo-badge <?php echo $tipo_class; ?>"><?php echo ucfirst($agendamento['tipo_consulta']); ?></span></td>
                                            <td><span class="status-badge <?php echo $status_class; ?>"><?php echo ucfirst($agendamento['status']); ?></span></td>
                                            <td class="actions">
                                                <button class="btn-edit" onclick="editarAgendamento(<?php echo $agendamento['id']; ?>)">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn-delete" onclick="excluirAgendamento(<?php echo $agendamento['id']; ?>)">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </section>

            <!-- Seção Chat -->
            <section class="chat-section">
                <h2 class="section-title"><i class="fas fa-comments"></i> Chat com Clientes</h2>
                
                <div class="chat-container">
                    <!-- Lista de contatos/conversas -->
                    <div class="chat-contatos">
                        <div class="chat-contatos-header">
                            <h4><i class="fas fa-users"></i> Conversas com Clientes</h4>
                        </div>
                        <?php if (empty($conversas)): ?>
                            <div class="sem-conversa" style="padding: 20px;">
                                <div>
                                    <i class="fas fa-comments"></i>
                                    <p>Nenhuma conversa iniciada</p>
                                    <small>As conversas aparecerão aqui quando os clientes enviarem mensagens</small>
                                </div>
                            </div>
                        <?php else: ?>
                            <?php foreach ($conversas as $conversa): ?>
                                <div class="chat-contato <?php echo ($conversa_selecionada && $conversa_selecionada['Id'] == $conversa['cliente_id']) ? 'active' : ''; ?>" 
                                     onclick="window.location.href='?conversa_id=<?php echo $conversa['cliente_id']; ?>'">
                                    <div class="contato-info">
                                        <h4><?php echo htmlspecialchars($conversa['FullName']); ?></h4>
                                        <p><?php echo htmlspecialchars($conversa['phone']); ?></p>
                                        <?php if ($conversa['ultima_mensagem']): ?>
                                            <p style="font-size: 11px; margin-top: 5px;">
                                                <?php echo strlen($conversa['ultima_mensagem']) > 30 ? substr($conversa['ultima_mensagem'], 0, 30) . '...' : $conversa['ultima_mensagem']; ?>
                                            </p>
                                        <?php endif; ?>
                                    </div>
                                    <div class="contato-meta">
                                        <small>
                                            <?php if ($conversa['ultima_mensagem_data']): ?>
                                                <?php echo date('d/m H:i', strtotime($conversa['ultima_mensagem_data'])); ?>
                                            <?php endif; ?>
                                        </small>
                                        <?php if ($conversa['mensagens_nao_lidas'] > 0): ?>
                                            <span class="badge-mensagens"><?php echo $conversa['mensagens_nao_lidas']; ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <!-- Área de mensagens -->
                    <div class="chat-area">
                        <?php if ($conversa_selecionada): ?>
                            <div class="chat-header">
                                <h4><i class="fas fa-user"></i> <?php echo htmlspecialchars($conversa_selecionada['FullName']); ?></h4>
                                <small><?php echo htmlspecialchars($conversa_selecionada['phone']); ?> • <?php echo htmlspecialchars($conversa_selecionada['email']); ?></small>
                            </div>
                            
                            <div class="chat-mensagens" id="chat-mensagens">
                                <?php foreach ($mensagens_conversa as $msg): ?>
                                    <div class="mensagem <?php echo $msg['remetente']; ?>">
                                        <div class="mensagem-texto"><?php echo htmlspecialchars($msg['mensagem']); ?></div>
                                        <div class="mensagem-hora">
                                            <?php echo date('H:i', strtotime($msg['created_at'])); ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            
                            <div class="chat-input">
                                <form id="form-mensagem" method="POST">
                                    <input type="hidden" name="acao" value="enviar_mensagem">
                                    <input type="hidden" name="conversa_id" value="<?php echo $conversa_selecionada['Id']; ?>">
                                    <div class="input-group">
                                        <input type="text" name="mensagem" class="chat-input-field" placeholder="Digite sua mensagem..." required id="campo-mensagem">
                                        <button type="submit" class="btn-enviar" id="btn-enviar">
                                            <i class="fas fa-paper-plane"></i>
                                        </button>
                                    </div>
                                </form>
                            </div>
                        <?php else: ?>
                            <div class="sem-conversa">
                                <div>
                                    <i class="fas fa-comment-dots"></i>
                                    <h3>Selecione uma conversa</h3>
                                    <p>Escolha um cliente na lista ao lado para visualizar e enviar mensagens</p>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </section>
        </div>
    </div>

    <!-- Modal de confirmação para exclusão -->
    <div id="modal-excluir" class="modal">
        <div class="modal-content">
            <h3><i class="fas fa-exclamation-triangle"></i> Confirmar Exclusão</h3>
            <p>Tem certeza que deseja excluir este agendamento?</p>
            <div class="modal-buttons">
                <form id="form-excluir" method="POST">
                    <input type="hidden" name="acao" value="excluir">
                    <input type="hidden" name="id" id="excluir_id">
                    <button type="button" class="btn btn-secondary" onclick="fecharModal()">Cancelar</button>
                    <button type="submit" class="btn btn-danger">Excluir</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Função para editar agendamento (versão simplificada sem AJAX)
        function editarAgendamento(id) {
            if (confirm('Deseja editar este agendamento? Será necessário preencher o formulário manualmente.')) {
                document.getElementById('form-title').innerHTML = '<i class="fas fa-edit"></i> Editar Agendamento';
                document.getElementById('acao').value = 'editar';
                document.getElementById('agendamento_id').value = id;
                document.getElementById('status-group').style.display = 'block';
                document.getElementById('btn-cancelar').style.display = 'inline-block';
                
                // Rolagem suave até o formulário
                document.querySelector('.agenda-form').scrollIntoView({ behavior: 'smooth' });
            }
        }

        // Função para excluir agendamento
        function excluirAgendamento(id) {
            if (confirm('Tem certeza que deseja excluir este agendamento?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.style.display = 'none';
                
                const acaoInput = document.createElement('input');
                acaoInput.name = 'acao';
                acaoInput.value = 'excluir';
                
                const idInput = document.createElement('input');
                idInput.name = 'id';
                idInput.value = id;
                
                form.appendChild(acaoInput);
                form.appendChild(idInput);
                document.body.appendChild(form);
                form.submit();
            }
        }

        // Função para fechar modal
        function fecharModal() {
            document.getElementById('modal-excluir').style.display = 'none';
        }

        // Cancelar edição
        document.getElementById('btn-cancelar').addEventListener('click', function() {
            resetForm();
        });

        // Resetar formulário
        function resetForm() {
            document.getElementById('form-title').innerHTML = '<i class="fas fa-plus-circle"></i> Novo Agendamento';
            document.getElementById('acao').value = 'adicionar';
            document.getElementById('form-agendamento').reset();
            document.getElementById('status-group').style.display = 'none';
            document.getElementById('btn-cancelar').style.display = 'none';
            document.getElementById('agendamento_id').value = '';
        }

        // Fechar modal ao clicar fora
        window.onclick = function(event) {
            var modal = document.getElementById('modal-excluir');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }

        // Configurar data mínima para hoje
        document.getElementById('data_agendamento').min = new Date().toISOString().split('T')[0];

        // Auto-scroll para baixo no chat
        function scrollChatParaBaixo() {
            const chatMensagens = document.getElementById('chat-mensagens');
            if (chatMensagens) {
                chatMensagens.scrollTop = chatMensagens.scrollHeight;
            }
        }

        // CORREÇÃO DO REFRESH RÁPIDO
        let refreshInterval;

        function iniciarAtualizacaoChat() {
            // Limpar intervalo anterior se existir
            if (refreshInterval) {
                clearInterval(refreshInterval);
            }
            
            // Configurar novo intervalo apenas se estiver em uma conversa
            if (window.location.search.includes('conversa_id')) {
                refreshInterval = setInterval(function() {
                    window.location.reload();
                }, 30000); // 30 segundos em vez de 10
            }
        }

        // Iniciar a atualização quando a página carregar
        window.addEventListener('load', function() {
            iniciarAtualizacaoChat();
            scrollChatParaBaixo();
            
            // Focar no campo de mensagem quando uma conversa é selecionada
            const campoMensagem = document.getElementById('campo-mensagem');
            if (campoMensagem) {
                campoMensagem.focus();
            }
        });

        // Parar a atualização quando o usuário estiver digitando
        document.addEventListener('DOMContentLoaded', function() {
            const chatInput = document.querySelector('.chat-input-field');
            if (chatInput) {
                chatInput.addEventListener('focus', function() {
                    if (refreshInterval) {
                        clearInterval(refreshInterval);
                    }
                });
                
                chatInput.addEventListener('blur', function() {
                    iniciarAtualizacaoChat();
                });
                
                chatInput.addEventListener('input', function() {
                    // Reiniciar o timer quando o usuário digitar
                    if (refreshInterval) {
                        clearInterval(refreshInterval);
                    }
                    // Aguardar 5 segundos após parar de digitar para recomeçar as atualizações
                    setTimeout(iniciarAtualizacaoChat, 5000);
                });
            }
        });

        // Envio de mensagem via AJAX
        document.getElementById('form-mensagem')?.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const mensagemInput = this.querySelector('input[name="mensagem"]');
            const btnEnviar = this.querySelector('.btn-enviar');
            const mensagemTexto = mensagemInput.value.trim();
            
            if (mensagemTexto === '') return;
            
            // Desabilitar botão durante o envio
            btnEnviar.disabled = true;
            btnEnviar.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            
            fetch('', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Recarregar a página para mostrar a nova mensagem
                    window.location.reload();
                } else {
                    alert('Erro ao enviar mensagem: ' + data.error);
                    // Reabilitar botão
                    btnEnviar.disabled = false;
                    btnEnviar.innerHTML = '<i class="fas fa-paper-plane"></i>';
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                alert('Erro ao enviar mensagem');
                // Reabilitar botão
                btnEnviar.disabled = false;
                btnEnviar.innerHTML = '<i class="fas fa-paper-plane"></i>';
            });
        });
    </script>
    <script>
        function confirmarLogoutAdvogado() {
            if (confirm('Tem certeza que deseja sair da sua conta?')) {
                fazerLogoutAdvogado();
            }
        }

        function fazerLogoutAdvogado() {
            // Limpeza completa dos dados do advogado
            const itensParaRemover = [
                'token_advogado',
                'usuario_advogado', 
                'dados_advogado',
                'advogado_logado',
                'sessao_ativa',
                'token',
                'user',
                'login'
            ];
            
            // Limpar localStorage
            itensParaRemover.forEach(item => {
                localStorage.removeItem(item);
                sessionStorage.removeItem(item);
            });
            
            // Limpar todos os cookies (opcional)
            document.cookie.split(";").forEach(function(c) {
                document.cookie = c.replace(/^ +/, "").replace(/=.*/, "=;expires=" + new Date().toUTCString() + ";path=/");
            });
            
            // Redirecionar para página principal
            window.location.href = 'index.html';
        }
    </script>
</body>
</html>