CREATE DATABASE mantenimiento;
USE mantenimiento;

CREATE TABLE usuarios (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) UNIQUE NOT NULL,
  password VARCHAR(255) NOT NULL,
  rol ENUM('comun','pro') NOT NULL
);

CREATE TABLE registros (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(100) NOT NULL,
  serie VARCHAR(50) NOT NULL,
  problema TEXT NOT NULL,
  solucion TEXT,
  estatus ENUM('pendiente','ok') DEFAULT 'pendiente',
  imagen VARCHAR(255),
  imagen_solucion VARCHAR(255)
);

CREATE TABLE bitacora (
  id INT AUTO_INCREMENT PRIMARY KEY,
  registro_id INT NOT NULL,
  usuario_id INT NOT NULL,
  fecha DATETIME NOT NULL,
  FOREIGN KEY (registro_id) REFERENCES registros(id),
  FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);

INSERT INTO usuarios (username,password,rol) VALUES
('comun', MD5('1234'), 'comun'),
('pro', MD5('1234'), 'pro');
