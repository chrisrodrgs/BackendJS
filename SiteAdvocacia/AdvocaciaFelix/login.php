<?php
session_start();

// Se já estiver logado, redireciona para área do cliente
if (isset($_SESSION['cliente_id'])) {
    header('Location: area_cliente.php');
    exit;
}

// Configurações do banco
$host = 'localhost';
$dbname = 'felix_advocacia';
$username = 'root';
$password = '';

$error = '';

// Processar login
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $password = $_POST['password'];
        
        $stmt = $pdo->prepare("SELECT * FROM clientes WHERE email = ?");
        $stmt->execute([$email]);
        $cliente = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($cliente && password_verify($password, $cliente['password'])) {
            $_SESSION['cliente_id'] = $cliente['id'];
            $_SESSION['cliente_nome'] = $cliente['fullName'];
            $_SESSION['cliente_email'] = $cliente['email'];
            
            header('Location: area_cliente.php');
            exit;
        } else {
            $error = 'E-mail ou senha incorretos.';
        }
        
    } catch (Exception $e) {
        $error = 'Erro no sistema. Tente novamente.';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Felix Advocacia</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* Reset e estilos gerais */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary-color: #0a1a2d;
            --secondary-color: #c9a96e;
            --accent-color: #2c5282;
            --light-color: #f8f9fa;
            --dark-color: #1a1a1a;
            --text-color: #333;
            --text-light: #6c757d;
            --shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            --transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            --border-radius: 12px;
        }

        body {
            background-color: #fefefe;
            color: var(--text-color);
            line-height: 1.7;
            font-family: 'Inter', sans-serif;
            overflow-x: hidden;
        }

        h1, h2, h3, h4, h5 {
            font-family: 'Playfair Display', serif;
            font-weight: 600;
            line-height: 1.3;
        }

        .container {
            width: 90%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 15px;
        }

        /* Header */
        header {
            background-color: rgba(10, 26, 45, 0.95);
            color: white;
            padding: 1.2rem 0;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
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
            gap: 12px;
        }

        .logo i {
            font-size: 2.2rem;
            color: var(--secondary-color);
            transition: var(--transition);
        }

        .logo:hover i {
            transform: rotate(-10deg);
        }

        .logo h1 {
            font-size: 1.9rem;
            font-weight: 700;
            letter-spacing: -0.5px;
        }

        nav ul {
            display: flex;
            list-style: none;
            gap: 2rem;
        }

        nav a {
            color: white;
            text-decoration: none;
            font-weight: 500;
            transition: var(--transition);
            padding: 0.5rem 0;
            position: relative;
            font-size: 1.05rem;
        }

        nav a:hover {
            color: var(--secondary-color);
        }

        nav a::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 0;
            height: 2px;
            background-color: var(--secondary-color);
            transition: var(--transition);
        }

        nav a:hover::after {
            width: 100%;
        }

        .user-actions {
            display: flex;
            gap: 1rem;
            align-items: center;
        }

        .btn {
            padding: 0.7rem 1.5rem;
            border: none;
            border-radius: 50px;
            cursor: pointer;
            font-weight: 600;
            transition: var(--transition);
            font-size: 1rem;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--secondary-color), #d4b87a);
            color: white;
            box-shadow: 0 4px 15px rgba(201, 169, 110, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(201, 169, 110, 0.4);
        }

        .btn-outline {
            background-color: transparent;
            border: 2px solid var(--secondary-color);
            color: var(--secondary-color);
        }

        .btn-outline:hover {
            background-color: rgba(201, 169, 110, 0.1);
            transform: translateY(-3px);
        }

        /* Hero Section */
        .hero {
            background: linear-gradient(135deg, rgba(10, 26, 45, 0.85) 0%, rgba(10, 26, 45, 0.7) 100%), url('https://images.unsplash.com/photo-1589829545856-d10d557cf95f?ixlib=rb-4.0.3&auto=format&fit=crop&w=1350&q=80');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            color: white;
            padding: 12rem 0 8rem;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle at center, transparent 10%, rgba(10, 26, 45, 0.9) 90%);
        }

        .hero .container {
            position: relative;
            z-index: 2;
        }

        .hero h2 {
            font-size: 3.5rem;
            margin-bottom: 1.5rem;
            font-weight: 700;
            letter-spacing: -1px;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        }

        .hero p {
            font-size: 1.3rem;
            max-width: 700px;
            margin: 0 auto 3rem;
            font-weight: 300;
            opacity: 0.9;
        }

        /* Seção de Login */
        .login-section {
            padding: 6rem 0;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        }

        .login-container {
            max-width: 500px;
            margin: 0 auto;
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            overflow: hidden;
        }

        .login-header {
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
            color: white;
            padding: 2.5rem;
            text-align: center;
        }

        .login-header h2 {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
            font-size: 2.2rem;
            margin: 0;
        }

        .login-header i {
            color: var(--secondary-color);
            font-size: 2.5rem;
        }

        .login-form {
            padding: 3rem;
        }

        .form-group {
            margin-bottom: 2rem;
        }

        label {
            display: block;
            margin-bottom: 0.8rem;
            font-weight: 600;
            color: var(--dark-color);
            font-size: 1.1rem;
        }

        .required::after {
            content: ' *';
            color: #e53e3e;
        }

        input {
            width: 100%;
            padding: 1rem 1.2rem;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 1rem;
            transition: var(--transition);
            font-family: 'Inter', sans-serif;
        }

        input:focus {
            outline: none;
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 3px rgba(201, 169, 110, 0.1);
            transform: translateY(-2px);
        }

        .form-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid #eee;
        }

        .form-actions a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .form-actions a:hover {
            color: var(--accent-color);
            gap: 12px;
        }

        .btn-large {
            padding: 1rem 2.5rem;
            font-size: 1.1rem;
        }

        /* Mensagens de Alerta */
        .alert {
            padding: 1.5rem;
            border-radius: 8px;
            margin: 1.5rem;
            text-align: center;
            font-weight: 500;
        }

        .alert-error {
            background-color: #ffe6e6;
            color: #d63031;
            border: 1px solid #ffb3b3;
        }

        /* Footer */
        footer {
            background: linear-gradient(135deg, var(--dark-color), #0a0a0a);
            color: white;
            padding: 4rem 0 2rem;
        }

        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 2.5rem;
            margin-bottom: 3rem;
        }

        .footer-column h3 {
            margin-bottom: 1.8rem;
            font-size: 1.4rem;
            position: relative;
            padding-bottom: 0.9rem;
        }

        .footer-column h3::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 50px;
            height: 3px;
            background: linear-gradient(to right, var(--secondary-color), #d4b87a);
            border-radius: 2px;
        }

        .footer-column ul {
            list-style: none;
        }

        .footer-column ul li {
            margin-bottom: 0.9rem;
        }

        .footer-column a {
            color: #ccc;
            text-decoration: none;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .footer-column a:hover {
            color: var(--secondary-color);
            padding-left: 5px;
        }

        .copyright {
            text-align: center;
            padding-top: 2rem;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            color: #aaa;
            font-size: 0.95rem;
        }

        /* Responsividade */
        @media (max-width: 992px) {
            .hero h2 {
                font-size: 2.8rem;
            }
        }

        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                gap: 1.2rem;
            }
            
            nav ul {
                flex-wrap: wrap;
                justify-content: center;
                gap: 1.2rem;
            }
            
            .hero {
                padding: 10rem 0 6rem;
                background-attachment: scroll;
            }
            
            .hero h2 {
                font-size: 2.3rem;
            }
            
            .hero p {
                font-size: 1.1rem;
            }
            
            .form-actions {
                flex-direction: column;
                gap: 1.5rem;
                align-items: flex-start;
            }
            
            .login-form {
                padding: 2rem;
            }
        }

        @media (max-width: 576px) {
            .hero h2 {
                font-size: 2rem;
            }
            
            .login-header {
                padding: 2rem 1.5rem;
            }
            
            .login-header h2 {
                font-size: 1.8rem;
            }
            
            .btn {
                padding: 0.6rem 1.2rem;
                font-size: 0.9rem;
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
                    <li><a href="area_cliente.php">Área do Cliente</a></li>
                    <li><a href="index.html#contact">Contato</a></li>
                </ul>
            </nav>
            <div class="user-actions">
                <a href="login.php" class="btn btn-outline">
                    <i class="fas fa-sign-in-alt"></i> Entrar
                </a>
                <a href="cadastro.php" class="btn btn-primary">
                    <i class="fas fa-user-plus"></i> Cadastrar
                </a>
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <h2>Área do Cliente</h2>
            <p>Faça login para acessar sua área personalizada e acompanhar seus processos</p>
        </div>
    </section>

    <!-- Seção de Login -->
    <section class="login-section">
        <div class="container">
            <?php if ($error): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <div class="login-container">
                <div class="login-header">
                    <h2><i class="fas fa-sign-in-alt"></i> Login</h2>
                </div>
                
                <form class="login-form" method="POST" action="login.php">
                    <div class="form-group">
                        <label for="email" class="required">E-mail</label>
                        <input type="email" id="email" name="email" placeholder="seu@email.com" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="password" class="required">Senha</label>
                        <input type="password" id="password" name="password" placeholder="Sua senha" required>
                    </div>
                    
                    <div class="form-actions">
                        <a href="cadastro.php">
                            <i class="fas fa-user-plus"></i> Criar conta
                        </a>
                        <button type="submit" class="btn btn-primary btn-large">
                            <i class="fas fa-sign-in-alt"></i> Entrar
                        </button>
                    </div>
                </form>
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

        // Foco no campo email
        document.getElementById('email').focus();
    </script>
</body>
</html>