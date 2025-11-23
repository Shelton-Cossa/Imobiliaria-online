CREATE DATABASE IF NOT EXISTS imobiliaria_online CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE imobiliaria_online;

CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(150),
    email VARCHAR(150) UNIQUE,
    telefone VARCHAR(50),
    pais VARCHAR(100) DEFAULT 'Moçambique',
    provincia VARCHAR(100),
    senha VARCHAR(255),
    foto_perfil VARCHAR(255) DEFAULT 'Uploads/perfil/default.jpg',
    tipo ENUM('admin','usuario') DEFAULT 'usuario',
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE imoveis (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    titulo VARCHAR(150) NOT NULL,
    descricao TEXT,
    tipo_imovel ENUM('Casa','Apartamento','Terreno','Loja','Armazém','Escritório','Vivenda','Outro') NOT NULL,
    finalidade ENUM('Venda','Aluguel') NOT NULL,
    preco DECIMAL(12,2) NOT NULL,
    endereco VARCHAR(255),
    provincia VARCHAR(100),
    cidade VARCHAR(100),
    imagem_principal VARCHAR(255),
    data_publicacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE imagens_imoveis (
    id INT AUTO_INCREMENT PRIMARY KEY,
    imovel_id INT NOT NULL,
    caminho_imagem VARCHAR(255) NOT NULL,
    FOREIGN KEY (imovel_id) REFERENCES imoveis(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 🔹 Tabela de conversas (uma conversa entre 2 usuários)
CREATE TABLE conversas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario1_id INT NOT NULL,
    usuario2_id INT NOT NULL,
    data_criacao DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario1_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario2_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- 🔹 Tabela de mensagens
CREATE TABLE mensagens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    conversa_id INT NOT NULL,
    remetente_id INT NOT NULL,
    mensagem TEXT NOT NULL,
    data_envio DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (conversa_id) REFERENCES conversas(id) ON DELETE CASCADE,
    FOREIGN KEY (remetente_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- 🔹 View opcional para buscas mais rápidas (facilita listagem)
CREATE VIEW view_conversas AS
SELECT 
    c.id AS conversa_id,
    CASE 
        WHEN c.usuario1_id = u.id THEN c.usuario2_id 
        ELSE c.usuario1_id 
    END AS outro_usuario_id
FROM conversas c
JOIN usuarios u ON c.usuario1_id = u.id OR c.usuario2_id = u.id;


INSERT INTO usuarios (nome, email, senha, tipo, foto_perfil) VALUES
('Administrador','admin@admin.com', SHA2('admin123',256), 'admin', 'Uploads/perfil/default.jpg');