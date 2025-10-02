<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro - Felix Advocacia</title>
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

        /* Formulário de Cadastro */
        .registration-section {
            padding: 6rem 0;
        }

        .registration-container {
            max-width: 900px;
            margin: 0 auto;
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            overflow: hidden;
        }

        .registration-header {
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
            color: white;
            padding: 2.5rem;
            text-align: center;
        }

        .registration-header h2 {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
            font-size: 2.2rem;
            margin: 0;
        }

        .registration-header i {
            color: var(--secondary-color);
            font-size: 2.5rem;
        }

        .registration-form {
            padding: 3rem;
        }

        .form-group {
            margin-bottom: 2rem;
        }

        .form-row {
            display: flex;
            gap: 1.5rem;
            flex-wrap: wrap;
        }

        .form-row .form-group {
            flex: 1;
            min-width: 250px;
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

        input, select {
            width: 100%;
            padding: 1rem 1.2rem;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 1rem;
            transition: var(--transition);
            font-family: 'Inter', sans-serif;
        }

        input:focus, select:focus {
            outline: none;
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 3px rgba(201, 169, 110, 0.1);
            transform: translateY(-2px);
        }

        .form-hint {
            font-size: 0.9rem;
            color: var(--text-light);
            margin-top: 0.5rem;
        }

        .password-strength {
            margin-top: 0.5rem;
            font-size: 0.9rem;
        }

        .strength-weak { color: #e74c3c; }
        .strength-medium { color: #f39c12; }
        .strength-strong { color: #27ae60; }

        .form-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 3rem;
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
            margin: 1.5rem 3rem 0;
            text-align: center;
            font-weight: 500;
        }

        .alert-success {
            background-color: #d1edff;
            color: var(--primary-color);
            border: 1px solid #b3d9ff;
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
            
            .form-row {
                flex-direction: column;
                gap: 0;
            }
            
            .form-actions {
                flex-direction: column;
                gap: 1.5rem;
                align-items: flex-start;
            }
            
            .registration-form {
                padding: 2rem;
            }
        }

        @media (max-width: 576px) {
            .hero h2 {
                font-size: 2rem;
            }
            
            .registration-header {
                padding: 2rem 1.5rem;
            }
            
            .registration-header h2 {
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
            <h2>Cadastro de Cliente</h2>
            <p>Preencha o formulário abaixo para se cadastrar em nosso escritório e ter acesso a todos os nossos serviços</p>
        </div>
    </section>

    <!-- Formulário de Cadastro -->
    <section class="registration-section">
        <div class="container">
            
            <!-- Mensagens de Sucesso/Erro -->
            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> Cadastro realizado com sucesso! <a href="login.php" style="color: var(--primary-color); font-weight: bold;">Faça login aqui</a>.
                </div>
            <?php endif; ?>
            
            <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i> 
                    <?php 
                        echo isset($_GET['message']) ? 
                        htmlspecialchars($_GET['message']) : 
                        'Erro no cadastro. Tente novamente.';
                    ?>
                </div>
            <?php endif; ?>

            <div class="registration-container">
                <div class="registration-header">
                    <h2><i class="fas fa-user-plus"></i> Dados Pessoais e de Acesso</h2>
                </div>
                
                <form class="registration-form" method="POST" action="processa_cadastro.php">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="fullName" class="required">Nome Completo</label>
                            <input type="text" id="fullName" name="fullName" required placeholder="Digite seu nome completo">
                        </div>
                        <div class="form-group">
                            <label for="birthDate" class="required">Data de Nascimento</label>
                            <input type="date" id="birthDate" name="birthDate" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="cpf" class="required">CPF</label>
                            <input type="text" id="cpf" name="cpf" placeholder="000.000.000-00" required>
                            <div class="form-hint">Apenas números e pontos</div>
                        </div>
                        <div class="form-group">
                            <label for="city" class="required">Cidade</label>
                            <input type="text" id="city" name="city" required placeholder="Cidade onde reside">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="phone" class="required">Telefone Principal</label>
                            <input type="tel" id="phone" name="phone" placeholder="(11) 99999-9999" required>
                        </div>
                        <div class="form-group">
                            <label for="secondaryPhone">Telefone Secundário</label>
                            <input type="tel" id="secondaryPhone" name="secondaryPhone" placeholder="(11) 99999-9999">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="email" class="required">E-mail</label>
                            <input type="email" id="email" name="email" placeholder="seu@email.com" required>
                        </div>
                        <div class="form-group">
                            <label for="confirmEmail" class="required">Confirmar E-mail</label>
                            <input type="email" id="confirmEmail" name="confirmEmail" placeholder="seu@email.com" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="password" class="required">Senha</label>
                            <input type="password" id="password" name="password" placeholder="Crie uma senha segura" required minlength="6">
                            <div class="form-hint">Mínimo 6 caracteres</div>
                            <div id="passwordStrength" class="password-strength"></div>
                        </div>
                        <div class="form-group">
                            <label for="confirmPassword" class="required">Confirmar Senha</label>
                            <input type="password" id="confirmPassword" name="confirmPassword" placeholder="Digite a senha novamente" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="howFound">Como nos conheceu?</label>
                        <select id="howFound" name="howFound">
                            <option value="">Selecione uma opção</option>
                            <option value="search">Busca na Internet</option>
                            <option value="recommendation">Indicação</option>
                            <option value="social">Rede Social</option>
                            <option value="ad">Publicidade</option>
                            <option value="other">Outro</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>
                            <input type="checkbox" id="agreeTerms" name="agreeTerms" required>
                            Concordo com os <a href="#" style="color: var(--primary-color);">Termos de Uso</a> e <a href="#" style="color: var(--primary-color);">Política de Privacidade</a>
                        </label>
                    </div>
                    
                    <div class="form-group">
                        <label>
                            <input type="checkbox" id="newsletter" name="newsletter">
                            Desejo receber novidades e informações jurídicas por e-mail
                        </label>
                    </div>
                    
                    <div class="form-actions">
                        <a href="index.html">
                            <i class="fas fa-arrow-left"></i> Voltar para o site
                        </a>
                        <button type="submit" class="btn btn-primary btn-large">
                            <i class="fas fa-user-check"></i> Finalizar Cadastro
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
        // Formatação do CPF
        document.getElementById('cpf').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            
            if (value.length > 11) {
                value = value.substring(0, 11);
            }
            
            if (value.length > 9) {
                value = value.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, '$1.$2.$3-$4');
            } else if (value.length > 6) {
                value = value.replace(/(\d{3})(\d{3})(\d{1,3})/, '$1.$2.$3');
            } else if (value.length > 3) {
                value = value.replace(/(\d{3})(\d{1,3})/, '$1.$2');
            }
            
            e.target.value = value;
        });
        
        // Formatação do telefone
        function formatPhone(input) {
            let value = input.value.replace(/\D/g, '');
            
            if (value.length > 11) {
                value = value.substring(0, 11);
            }
            
            if (value.length > 10) {
                value = value.replace(/(\d{2})(\d{5})(\d{4})/, '($1) $2-$3');
            } else if (value.length > 6) {
                value = value.replace(/(\d{2})(\d{4})(\d{0,4})/, '($1) $2-$3');
            } else if (value.length > 2) {
                value = value.replace(/(\d{2})(\d{0,5})/, '($1) $2');
            } else if (value.length > 0) {
                value = value.replace(/(\d{0,2})/, '($1');
            }
            
            input.value = value;
        }
        
        document.getElementById('phone').addEventListener('input', function(e) {
            formatPhone(e.target);
        });
        
        document.getElementById('secondaryPhone').addEventListener('input', function(e) {
            formatPhone(e.target);
        });
        
        // Validação de idade mínima (18 anos)
        document.getElementById('birthDate').addEventListener('change', function(e) {
            const birthDate = new Date(e.target.value);
            const today = new Date();
            let age = today.getFullYear() - birthDate.getFullYear();
            const monthDiff = today.getMonth() - birthDate.getMonth();
            
            if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
                age--;
            }
            
            if (age < 18) {
                alert('É necessário ter pelo menos 18 anos para se cadastrar.');
                e.target.value = '';
            }
        });

        // Validação de força da senha
        document.getElementById('password').addEventListener('input', function(e) {
            const password = e.target.value;
            const strengthText = document.getElementById('passwordStrength');
            
            if (password.length === 0) {
                strengthText.textContent = '';
                return;
            }
            
            let strength = 'weak';
            let strengthClass = 'strength-weak';
            let message = 'Senha fraca';
            
            if (password.length >= 8 && /[A-Z]/.test(password) && /[0-9]/.test(password)) {
                strength = 'strong';
                strengthClass = 'strength-strong';
                message = 'Senha forte';
            } else if (password.length >= 6) {
                strength = 'medium';
                strengthClass = 'strength-medium';
                message = 'Senha média';
            }
            
            strengthText.textContent = message;
            strengthText.className = 'password-strength ' + strengthClass;
        });

        // Validação do formulário antes do envio
        document.querySelector('form').addEventListener('submit', function(e) {
            const email = document.getElementById('email').value;
            const confirmEmail = document.getElementById('confirmEmail').value;
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirmPassword').value;
            
            if (email !== confirmEmail) {
                e.preventDefault();
                alert('Os e-mails informados não coincidem. Por favor, verifique.');
                return false;
            }
            
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('As senhas não coincidem. Por favor, verifique.');
                return false;
            }
            
            if (password.length < 6) {
                e.preventDefault();
                alert('A senha deve ter pelo menos 6 caracteres.');
                return false;
            }
            
            if (!document.getElementById('agreeTerms').checked) {
                e.preventDefault();
                alert('Você precisa aceitar os Termos de Uso e Política de Privacidade para continuar.');
                return false;
            }
            
            return true;
        });

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
    </script>
</body>
</html>