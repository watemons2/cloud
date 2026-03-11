<?php
$host = "localhost";
$user = "controlva_user";
$pass = "LaNuevaClaveSegura123!"; 
$db   = "mantenimiento";

// Crear conexión
$conn = new mysqli($host, $user, $pass, $db);

// Verificar conexión
if ($conn->connect_error) {
    die("Error en la conexión a la base de datos: " . $conn->connect_error);
}

// ---------------------------------------------------------
// CONFIGURACIÓN DE ZONA HORARIA (SOLUCIÓN HORA INCORRECTA)
// ---------------------------------------------------------

// 1. Configurar la hora de PHP a Ciudad de México
date_default_timezone_set('America/Mexico_City');

// 2. Calcular la diferencia horaria actual (Offset)
// Esto detecta automáticamente si es horario de verano o invierno
$now = new DateTime();
$mins = $now->getOffset() / 60;
$sgn = ($mins < 0 ? -1 : 1);
$mins = abs($mins);
$hrs = floor($mins / 60);
$mins -= $hrs * 60;
$offset = sprintf('%+d:%02d', $hrs*$sgn, $mins);

// 3. Forzar a la base de datos a usar esa misma hora
$conn->query("SET time_zone = '$offset'");

// ---------------------------------------------------------
// CONFIGURACIÓN DE CARACTERES (SOLUCIÓN ACENTOS Y Ñ)
// ---------------------------------------------------------
$conn->set_charset("utf8mb4");

?>
