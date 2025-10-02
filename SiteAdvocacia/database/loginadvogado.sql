-- Criação do Banco de Dados para a Advocacia Felix
CREATE DATABASE IF NOT EXISTS felix_advocacia;
USE felix_advocacia;

-- Tabela de advogados (para login)
CREATE TABLE IF NOT EXISTS advogados (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    telefone VARCHAR(20),
    especialidade VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabela de usuários/clientes (já existente nos outros chats)
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    telefone VARCHAR(20),
    cpf VARCHAR(14) UNIQUE,
    endereco TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela de casos/processos (já existente)
CREATE TABLE IF NOT EXISTS casos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(200) NOT NULL,
    descricao TEXT,
    numero_processo VARCHAR(50) UNIQUE,
    status ENUM('aberto', 'em_andamento', 'concluido', 'arquivado') DEFAULT 'aberto',
    id_cliente INT NOT NULL,
    id_advogado INT NOT NULL,
    data_abertura DATE,
    data_vencimento DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_cliente) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (id_advogado) REFERENCES advogados(id) ON DELETE CASCADE
);

-- Tabela de audiências (já existente)
CREATE TABLE IF NOT EXISTS audiencias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_caso INT NOT NULL,
    data_audiencia DATETIME NOT NULL,
    local VARCHAR(200),
    descricao TEXT,
    resultado TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_caso) REFERENCES casos(id) ON DELETE CASCADE
);

-- Tabela de documentos (já existente)
CREATE TABLE IF NOT EXISTS documentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_caso INT NOT NULL,
    titulo VARCHAR(200) NOT NULL,
    descricao TEXT,
    arquivo VARCHAR(255),
    tipo VARCHAR(50),
    data_upload TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_caso) REFERENCES casos(id) ON DELETE CASCADE
);

-- Tabela de agendamentos (já existente)
CREATE TABLE IF NOT EXISTS agendamentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_cliente INT NOT NULL,
    id_advogado INT NOT NULL,
    data_agendamento DATETIME NOT NULL,
    descricao TEXT,
    status ENUM('agendado', 'confirmado', 'cancelado', 'realizado') DEFAULT 'agendado',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_cliente) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (id_advogado) REFERENCES advogados(id) ON DELETE CASCADE
);

-- Inserir advogado administrador padrão
-- A senha é "Admin123@" criptografada
INSERT IGNORE INTO advogados (nome, email, password, telefone, especialidade) 
VALUES (
    'Dr. Felix Administrador', 
    'admin@felixadvocacia.com.br', 
    '$2y$10$rS3C6X8AQzMkfWYpJk9q.eL7N1sZxV2T8BwKdP4HvQmRcFgYhDnCt', 
    '(11) 9999-9999', 
    'Direito Civil e Administrativo'
);

-- Inserir mais alguns advogados de exemplo
INSERT IGNORE INTO advogados (nome, email, password, telefone, especialidade) 
VALUES 
(
    'Dra. Ana Silva', 
    'ana.silva@felixadvocacia.com.br', 
    '$2y$10$rS3C6X8AQzMkfWYpJk9q.eL7N1sZxV2T8BwKdP4HvQmRcFgYhDnCt', 
    '(11) 8888-8888', 
    'Direito Trabalhista'
),
(
    'Dr. Carlos Oliveira', 
    'carlos.oliveira@felixadvocacia.com.br', 
    '$2y$10$rS3C6X8AQzMkfWYpJk9q.eL7N1sZxV2T8BwKdP4HvQmRcFgYhDnCt', 
    '(11) 7777-7777', 
    'Direito de Família'
),
(
    'Dra. Mariana Santos', 
    'mariana.santos@felixadvocacia.com.br', 
    '$2y$10$rS3C6X8AQzMkfWYpJk9q.eL7N1sZxV2T8BwKdP4HvQmRcFgYhDnCt', 
    '(11) 6666-6666', 
    'Direito Criminal'
);

-- Inserir alguns usuários/clientes de exemplo
INSERT IGNORE INTO usuarios (nome, email, telefone, cpf, endereco) 
VALUES 
(
    'João da Silva',
    'joao.silva@email.com',
    '(11) 1111-1111',
    '123.456.789-00',
    'Rua das Flores, 123 - Centro - São Paulo/SP'
),
(
    'Ana Oliveira',
    'ana.oliveira@email.com',
    '(11) 2222-2222',
    '987.654.321-00',
    'Av. Paulista, 1000 - Bela Vista - São Paulo/SP'
),
(
    'Pedro Costa',
    'pedro.costa@email.com',
    '(11) 3333-3333',
    '456.789.123-00',
    'Rua Augusta, 500 - Consolação - São Paulo/SP'
);

-- Inserir alguns casos de exemplo
INSERT IGNORE INTO casos (titulo, descricao, numero_processo, status, id_cliente, id_advogado, data_abertura, data_vencimento)
VALUES 
(
    'Processo Trabalhista - Horas Extras',
    'Processo relacionado ao não pagamento de horas extras trabalhadas',
    '0012345-68.2023.5.02.0001',
    'em_andamento',
    1,
    2,
    '2023-01-15',
    '2024-01-15'
),
(
    'Divórcio Consensual',
    'Processo de divórcio consensual com partilha de bens',
    '0023456-79.2023.8.26.0001',
    'aberto',
    2,
    3,
    '2023-02-20',
    '2023-12-20'
),
(
    'Defesa Criminal - Roubo',
    'Processo de defesa em caso de acusação de roubo',
    '0034567-89.2023.7.02.0001',
    'em_andamento',
    3,
    4,
    '2023-03-10',
    '2024-03-10'
);

-- Inserir algumas audiências de exemplo
INSERT IGNORE INTO audiencias (id_caso, data_audiencia, local, descricao)
VALUES 
(
    1,
    '2023-11-15 09:00:00',
    'Fórum Trabalhista Central - Sala 205',
    'Audiência de conciliação inicial'
),
(
    2,
    '2023-11-20 14:30:00',
    'Fórum da Família - Sala 103',
    'Audiência de instrução e julgamento'
);

-- Inserir alguns documentos de exemplo
INSERT IGNORE INTO documentos (id_caso, titulo, descricao, tipo)
VALUES 
(
    1,
    'Petição Inicial',
    'Petição inicial do processo trabalhista',
    'peticao'
),
(
    1,
    'Contrato de Trabalho',
    'Cópia do contrato de trabalho do reclamante',
    'contrato'
);

-- Inserir alguns agendamentos de exemplo
INSERT IGNORE INTO agendamentos (id_cliente, id_advogado, data_agendamento, descricao, status)
VALUES 
(
    1,
    2,
    '2023-11-10 10:00:00',
    'Reunião para análise de documentos do processo trabalhista',
    'confirmado'
),
(
    2,
    3,
    '2023-11-12 15:30:00',
    'Consulta sobre divórcio e partilha de bens',
    'agendado'
);

-- Mostrar os dados inseridos
SELECT '=== ADVOGADOS CADASTRADOS ===' as '';
SELECT id, nome, email, especialidade FROM advogados;

SELECT '=== USUÁRIOS/CLIENTES CADASTRADOS ===' as '';
SELECT id, nome, email, telefone FROM usuarios;

SELECT '=== CASOS CADASTRADOS ===' as '';
SELECT id, titulo, numero_processo, status FROM casos;

SELECT '=== CREDENCIAIS PARA LOGIN ===' as '';
SELECT 'Email: admin@felixadvocacia.com.br' as '';
SELECT 'Senha: Admin123@' as '';
SELECT 'OU use qualquer outro email de advogado com a mesma senha' as '';