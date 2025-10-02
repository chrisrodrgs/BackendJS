<?php
session_start();

// Verificar se o advogado está logado
if (!isset($_SESSION['advogado_id'])) {
    header('Location: login_advogado.php');
    exit;
}

$host = 'localhost';
$dbname = 'felix_advocacia';
$username = 'root';
$password = '';

// Variáveis para o chat
$mensagem_chat = '';
$chat_visivel = false;

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Buscar dados do advogado logado
    $stmt = $pdo->prepare("SELECT * FROM advogados WHERE id = ?");
    $stmt->execute([$_SESSION['advogado_id']]);
    $advogado = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$advogado) {
        session_destroy();
        header('Location: login_advogado.php');
        exit;
    }
    
    // Buscar estatísticas
    $stmt_casos = $pdo->prepare("SELECT COUNT(*) as total FROM casos WHERE id_advogado = ?");
    $stmt_casos->execute([$_SESSION['advogado_id']]);
    $total_casos = $stmt_casos->fetch(PDO::FETCH_ASSOC)['total'];
    
    $stmt_casos_abertos = $pdo->prepare("SELECT COUNT(*) as abertos FROM casos WHERE id_advogado = ? AND status = 'aberto'");
    $stmt_casos_abertos->execute([$_SESSION['advogado_id']]);
    $casos_abertos = $stmt_casos_abertos->fetch(PDO::FETCH_ASSOC)['abertos'];
    
    $stmt_casos_andamento = $pdo->prepare("SELECT COUNT(*) as andamento FROM casos WHERE id_advogado = ? AND status = 'em_andamento'");
    $stmt_casos_andamento->execute([$_SESSION['advogado_id']]);
    $casos_andamento = $stmt_casos_andamento->fetch(PDO::FETCH_ASSOC)['andamento'];
    
    // Buscar últimos casos
    $stmt_ultimos_casos = $pdo->prepare("
        SELECT c.*, u.nome as cliente_nome 
        FROM casos c 
        LEFT JOIN usuarios u ON c.id_cliente = u.id 
        WHERE c.id_advogado = ? 
        ORDER BY c.created_at DESC 
        LIMIT 5
    ");
    $stmt_ultimos_casos->execute([$_SESSION['advogado_id']]);
    $ultimos_casos = $stmt_ultimos_casos->fetchAll(PDO::FETCH_ASSOC);
    
    // Buscar próximas audiências
    $stmt_audiencias = $pdo->prepare("
        SELECT a.*, c.titulo as caso_titulo, u.nome as cliente_nome
        FROM audiencias a
        LEFT JOIN casos c ON a.id_caso = c.id
        LEFT JOIN usuarios u ON c.id_cliente = u.id
        WHERE c.id_advogado = ? AND a.data_audiencia >= NOW()
        ORDER BY a.data_audiencia ASC
        LIMIT 5
    ");
    $stmt_audiencias->execute([$_SESSION['advogado_id']]);
    $proximas_audiencias = $stmt_audiencias->fetchAll(PDO::FETCH_ASSOC);
    
    // Processar mensagem do chat se enviada
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['mensagem_chat'])) {
        $mensagem = trim($_POST['mensagem_chat']);
        if (!empty($mensagem)) {
            $mensagem_chat = $mensagem;
            $chat_visivel = true;
        }
    }
    
} catch (Exception $e) {
    $error = "Erro ao conectar com o banco de dados: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Área do Advogado - Felix Advocacia</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Montserrat', sans-serif;
            background-color: #f8f9fa;
            color: #333;
            line-height: 1.6;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* Header Styles */
        header {
            background-color: #1a3a5f;
            color: white;
            padding: 15px 0;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .header-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            display: flex;
            align-items: center;
        }

        .logo h1 {
            font-family: 'Playfair Display', serif;
            font-size: 28px;
            margin-left: 10px;
        }

        .logo i {
            font-size: 32px;
            color: #d4af37;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .user-info span {
            font-weight: 500;
        }

        .btn-logout {
            background-color: #d4af37;
            color: #1a3a5f;
            border: none;
            padding: 8px 16px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
            transition: background-color 0.3s;
        }

        .btn-logout:hover {
            background-color: #b8941f;
        }

        /* Main Content */
        .dashboard {
            padding: 40px 0;
        }

        .welcome-section {
            background: linear-gradient(135deg, #1a3a5f, #2c5282);
            color: white;
            padding: 30px;
            border-radius: 10px;
            margin-bottom: 30px;
        }

        .welcome-section h2 {
            font-family: 'Playfair Display', serif;
            font-size: 32px;
            margin-bottom: 10px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .stat-card i {
            font-size: 40px;
            color: #1a3a5f;
            margin-bottom: 15px;
        }

        .stat-number {
            font-size: 36px;
            font-weight: 700;
            color: #1a3a5f;
            margin-bottom: 5px;
        }

        .stat-label {
            color: #666;
            font-weight: 500;
        }

        .content-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 40px;
        }

        @media (max-width: 768px) {
            .content-grid {
                grid-template-columns: 1fr;
            }
        }

        .section-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 25px;
        }

        .section-card h3 {
            font-family: 'Playfair Display', serif;
            color: #1a3a5f;
            margin-bottom: 20px;
            font-size: 24px;
            border-bottom: 2px solid #d4af37;
            padding-bottom: 10px;
        }

        .case-list, .audience-list {
            list-style: none;
        }

        .case-item, .audience-item {
            padding: 15px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .case-item:last-child, .audience-item:last-child {
            border-bottom: none;
        }

        .case-info, .audience-info {
            flex: 1;
        }

        .case-title, .audience-title {
            font-weight: 600;
            color: #1a3a5f;
            margin-bottom: 5px;
        }

        .case-client, .audience-case {
            color: #666;
            font-size: 14px;
        }

        .case-status {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .status-aberto {
            background-color: #e3f2fd;
            color: #1976d2;
        }

        .status-em_andamento {
            background-color: #fff3e0;
            color: #f57c00;
        }

        .status-concluido {
            background-color: #e8f5e8;
            color: #388e3c;
        }

        .audience-date {
            background-color: #1a3a5f;
            color: white;
            padding: 8px 12px;
            border-radius: 5px;
            font-size: 14px;
            font-weight: 600;
        }

        .empty-message {
            text-align: center;
            color: #666;
            padding: 40px;
            font-style: italic;
        }

        /* Quick Actions */
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 30px;
            margin-bottom: 40px;
        }

        .action-btn {
            background-color: #1a3a5f;
            color: white;
            border: none;
            padding: 15px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: background-color 0.3s;
            text-align: center;
            text-decoration: none;
            display: block;
        }

        .action-btn:hover {
            background-color: #152a45;
        }

        /* Chat Section */
        .chat-section {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 25px;
            margin-top: 30px;
        }

        .chat-section h3 {
            font-family: 'Playfair Display', serif;
            color: #1a3a5f;
            margin-bottom: 20px;
            font-size: 24px;
            border-bottom: 2px solid #d4af37;
            padding-bottom: 10px;
        }

        .chat-container {
            max-height: 400px;
            overflow-y: auto;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            background-color: #f9f9f9;
        }

        .chat-message {
            margin-bottom: 15px;
            padding: 10px;
            border-radius: 8px;
            background: white;
            border-left: 4px solid #1a3a5f;
        }

        .chat-message.user {
            background: #e3f2fd;
            border-left-color: #1976d2;
        }

        .chat-message.assistant {
            background: #f3e5f5;
            border-left-color: #7b1fa2;
        }

        .chat-input-form {
            display: flex;
            gap: 10px;
        }

        .chat-input {
            flex: 1;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }

        .chat-submit {
            background-color: #1a3a5f;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
        }

        .chat-submit:hover {
            background-color: #152a45;
        }

        /* Footer Styles */
        footer {
            background-color: #1a3a5f;
            color: white;
            padding: 40px 0 20px;
            margin-top: 60px;
        }

        .footer-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
        }

        .footer-column {
            flex: 1;
            min-width: 250px;
            margin-bottom: 30px;
        }

        .footer-column h3 {
            font-family: 'Playfair Display', serif;
            margin-bottom: 20px;
            font-size: 22px;
            color: #d4af37;
        }

        .footer-column p, .footer-column a {
            color: #ddd;
            margin-bottom: 10px;
            display: block;
            text-decoration: none;
        }

        .footer-column a:hover {
            color: white;
        }

        .social-icons {
            display: flex;
            margin-top: 15px;
        }

        .social-icons a {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            background-color: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            margin-right: 10px;
            transition: background-color 0.3s;
        }

        .social-icons a:hover {
            background-color: #d4af37;
        }

        .copyright {
            text-align: center;
            padding-top: 20px;
            margin-top: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            color: #aaa;
            font-size: 14px;
        }

        /* Responsive Styles */
        @media (max-width: 768px) {
            .header-container {
                flex-direction: column;
                text-align: center;
                gap: 15px;
            }

            .user-info {
                flex-direction: column;
                gap: 10px;
            }

            .chat-input-form {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header>
        <div class="container header-container">
            <div class="logo">
                <i class="fas fa-balance-scale"></i>
                <h1>Felix Advocacia</h1>
            </div>
            <div class="user-info">
                <span>Bem-vindo, <?php echo htmlspecialchars($advogado['nome']); ?></span>
                <form method="POST" action="logout_advogado.php" style="display: inline;">
                    <button type="submit" class="btn-logout">
                        <i class="fas fa-sign-out-alt"></i> Sair
                    </button>
                </form>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="dashboard">
        <div class="container">
            <!-- Welcome Section -->
            <section class="welcome-section">
                <h2>Área do Advogado</h2>
                <p>Gerencie seus casos, audiências e clientes de forma eficiente</p>
            </section>

            <!-- Statistics -->
            <div class="stats-grid">
                <div class="stat-card">
                    <i class="fas fa-briefcase"></i>
                    <div class="stat-number"><?php echo $total_casos; ?></div>
                    <div class="stat-label">Total de Casos</div>
                </div>
                <div class="stat-card">
                    <i class="fas fa-folder-open"></i>
                    <div class="stat-number"><?php echo $casos_abertos; ?></div>
                    <div class="stat-label">Casos Abertos</div>
                </div>
                <div class="stat-card">
                    <i class="fas fa-tasks"></i>
                    <div class="stat-number"><?php echo $casos_andamento; ?></div>
                    <div class="stat-label">Em Andamento</div>
                </div>
                <div class="stat-card">
                    <i class="fas fa-calendar-alt"></i>
                    <div class="stat-number"><?php echo count($proximas_audiencias); ?></div>
                    <div class="stat-label">Audiências Agendadas</div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="quick-actions">
                <a href="gerenciar_casos.php" class="action-btn">
                    <i class="fas fa-plus"></i> Novo Caso
                </a>
                <a href="lista_casos.php" class="action-btn">
                    <i class="fas fa-list"></i> Ver Todos os Casos
                </a>
                <a href="agendamentos.php" class="action-btn">
                    <i class="fas fa-calendar-plus"></i> Nova Audiência
                </a>
                <a href="clientes.php" class="action-btn">
                    <i class="fas fa-users"></i> Gerenciar Clientes
                </a>
            </div>

            <!-- Main Content Grid -->
            <div class="content-grid">
                <!-- Últimos Casos -->
                <section class="section-card">
                    <h3>Últimos Casos</h3>
                    <?php if (!empty($ultimos_casos)): ?>
                        <ul class="case-list">
                            <?php foreach ($ultimos_casos as $caso): ?>
                                <li class="case-item">
                                    <div class="case-info">
                                        <div class="case-title"><?php echo htmlspecialchars($caso['titulo']); ?></div>
                                        <div class="case-client">Cliente: <?php echo htmlspecialchars($caso['cliente_nome']); ?></div>
                                    </div>
                                    <span class="case-status status-<?php echo $caso['status']; ?>">
                                        <?php 
                                        $status_text = [
                                            'aberto' => 'Aberto',
                                            'em_andamento' => 'Em Andamento',
                                            'concluido' => 'Concluído',
                                            'arquivado' => 'Arquivado'
                                        ];
                                        echo $status_text[$caso['status']];
                                        ?>
                                    </span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <div class="empty-message">
                            <i class="fas fa-inbox" style="font-size: 48px; margin-bottom: 15px; color: #ddd;"></i>
                            <p>Nenhum caso encontrado</p>
                        </div>
                    <?php endif; ?>
                </section>

                <!-- Próximas Audiências -->
                <section class="section-card">
                    <h3>Próximas Audiências</h3>
                    <?php if (!empty($proximas_audiencias)): ?>
                        <ul class="audience-list">
                            <?php foreach ($proximas_audiencias as $audiencia): ?>
                                <li class="audience-item">
                                    <div class="audience-info">
                                        <div class="audience-title"><?php echo htmlspecialchars($audiencia['caso_titulo']); ?></div>
                                        <div class="audience-case">Cliente: <?php echo htmlspecialchars($audiencia['cliente_nome']); ?></div>
                                        <div class="audience-case">Local: <?php echo htmlspecialchars($audiencia['local']); ?></div>
                                    </div>
                                    <div class="audience-date">
                                        <?php echo date('d/m/Y H:i', strtotime($audiencia['data_audiencia'])); ?>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <div class="empty-message">
                            <i class="fas fa-calendar-times" style="font-size: 48px; margin-bottom: 15px; color: #ddd;"></i>
                            <p>Nenhuma audiência agendada</p>
                        </div>
                    <?php endif; ?>
                </section>
            </div>

            <!-- Chat Section -->
            <section class="chat-section">
                <h3><i class="fas fa-comments"></i> Chat de Suporte</h3>
                <div class="chat-container" id="chatContainer">
                    <?php if ($chat_visivel && !empty($mensagem_chat)): ?>
                        <div class="chat-message user">
                            <strong>Você:</strong> <?php echo htmlspecialchars($mensagem_chat); ?>
                        </div>
                        <div class="chat-message assistant">
                            <strong>Assistente:</strong> Obrigado pela sua mensagem! Em breve nossa equipe entrará em contato para ajudá-lo com: "<?php echo htmlspecialchars($mensagem_chat); ?>"
                        </div>
                    <?php else: ?>
                        <div class="chat-message assistant">
                            <strong>Assistente:</strong> Olá <?php echo htmlspecialchars($advogado['nome']); ?>! Como posso ajudá-lo hoje? Digite sua mensagem abaixo.
                        </div>
                    <?php endif; ?>
                </div>
                <form method="POST" class="chat-input-form">
                    <input type="text" name="mensagem_chat" class="chat-input" placeholder="Digite sua mensagem..." required>
                    <button type="submit" class="chat-submit">
                        <i class="fas fa-paper-plane"></i> Enviar
                    </button>
                </form>
            </section>
        </div>
    </main>

    <!-- Footer -->
    <footer>
        <div class="container footer-container">
            <div class="footer-column">
                <h3>Felix Advocacia</h3>
                <p>Oferecendo soluções jurídicas de excelência há mais de 15 anos. Nossa equipe está pronta para defender seus direitos com ética e competência.</p>
                <div class="social-icons">
                    <a href="#"><i class="fab fa-facebook-f"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                    <a href="#"><i class="fab fa-linkedin-in"></i></a>
                </div>
            </div>
            <div class="footer-column">
                <h3>Contato</h3>
                <p><i class="fas fa-map-marker-alt"></i> Rua Jurídica, 123 - Centro</p>
                <p><i class="fas fa-phone"></i> (11) 3456-7890</p>
                <p><i class="fas fa-envelope"></i> contato@felixadvocacia.com.br</p>
            </div>
            <div class="footer-column">
                <h3>Links Rápidos</h3>
                <a href="index.html">Início</a>
                <a href="sobre.html">Sobre Nós</a>
                <a href="areas-atuacao.html">Áreas de Atuação</a>
                <a href="advogados.html">Nossos Advogados</a>
                <a href="contato.html">Contato</a>
            </div>
        </div>
        <div class="container">
            <div class="copyright">
                <p>&copy; 2023 Felix Advocacia. Todos os direitos reservados.</p>
            </div>
        </div>
    </footer>

    <script>
        // Rolagem automática para o final do chat
        document.addEventListener('DOMContentLoaded', function() {
            const chatContainer = document.getElementById('chatContainer');
            if (chatContainer) {
                chatContainer.scrollTop = chatContainer.scrollHeight;
            }
        });
    </script>
</body>
</html>