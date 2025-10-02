CREATE TABLE agendamentos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    cliente_id INT,
    advogado_id INT,
    data_agendamento DATETIME,
    tipo_consulta ENUM('presencial', 'online', 'telefonica'),
    descricao TEXT,
    status ENUM('agendado', 'confirmado', 'cancelado', 'concluido') DEFAULT 'agendado',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id),
    FOREIGN KEY (advogado_id) REFERENCES advogados(id)
);