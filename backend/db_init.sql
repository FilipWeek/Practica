CREATE DATABASE IF NOT EXISTS labvuln CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
CREATE TABLE IF NOT EXISTS challenges (
id INT AUTO_INCREMENT PRIMARY KEY,
name VARCHAR(150),
description TEXT,
points INT DEFAULT 100
);


CREATE TABLE IF NOT EXISTS user_flags (
id INT AUTO_INCREMENT PRIMARY KEY,
team_id INT NOT NULL,
challenge_id INT NOT NULL,
flag_value VARCHAR(255) NOT NULL,
resolved TINYINT DEFAULT 0,
solved_at DATETIME NULL,
UNIQUE(team_id,challenge_id)
);


CREATE TABLE IF NOT EXISTS submissions (
id INT AUTO_INCREMENT PRIMARY KEY,
team_id INT,
challenge_id INT,
flag_submitted VARCHAR(255),
valid TINYINT,
ip VARCHAR(45),
user_agent VARCHAR(255),
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


CREATE TABLE IF NOT EXISTS captures (
id INT AUTO_INCREMENT PRIMARY KEY,
team_id INT,
challenge_id INT,
flag_value VARCHAR(255),
points INT,
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


-- Insertar 6 retos ejemplo
INSERT INTO challenges (name,description,points) VALUES
('Recon (NMAP)','Descubrir servicio web en 8080',10),
('Dir enum (GOBUSTER)','Encontrar carpeta oculta',20),
('SQL Injection (ADMON)','Encontrar nota interna del usuario ADMON',50),
('XSS reflected','Campo de búsqueda que muestra nota con flag',40),
('Auth weak (HYDRA)','Credenciales débiles para panel admin',60),
('File upload (UPLOAD)','Subir fichero que genera meta_usuario.txt',30);