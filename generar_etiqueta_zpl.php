<?php
session_start();
// Solo Tecnicos y Administradores pueden acceder a esta función
if (!isset($_SESSION['usuario']) || ($_SESSION['rol'] !== 'Tecnico' && $_SESSION['rol'] !== 'Administrador')) {
    http_response_code(403); // Forbidden
    echo "Acceso denegado.";
    exit;
}

if (!isset($_GET['serie']) || empty($_GET['serie'])) {
    http_response_code(400); // Bad Request
    echo "Número de serie no proporcionado.";
    exit;
}

$numero_serie = htmlspecialchars($_GET['serie']);
$nombre_equipo = isset($_GET['nombre']) ? htmlspecialchars($_GET['nombre']) : 'Equipo'; // Opcional, para el texto

// Dimensiones de la etiqueta 4x2 pulgadas (102x48 mm)
// Unidades: puntos (dots). Para una impresora de 203 DPI, 1 pulgada = 203 dots.
// Ancho de la etiqueta: 4 pulgadas * 203 DPI = 812 dots
// Alto de la etiqueta: 2 pulgadas * 203 DPI = 406 dots

// Calculando el punto de inicio para el centro
$ancho_etiqueta = 812; // 4 pulgadas
$alto_etiqueta = 406;  // 2 pulgadas

// Margen desde el borde superior e izquierdo (ej: 10 dots)
$margin_x = 30;
$margin_y = 30;

// Tamaño de fuente (ej: 30 dots de alto)
$font_height = 30;
$font_width = 30;

// Generar el código ZPL
// ^XA - Inicio del formato de etiqueta
// ^MMT - Modo de impresión térmica (Tear Off)
// ^PW812 - Ancho de la etiqueta (812 dots)
// ^LL406 - Largo de la etiqueta (406 dots)
// ^LS0 - Ajuste de la posición de la etiqueta (cero)
// ^FOx,y - Origen de campo (posición x, y)
// ^AAN,h,w - Fuente Arial (h=alto, w=ancho)
// ^FOD - Campo de datos
// ^BQN,2,10 - Código QR (N=normal, 2=magnificación, 10=tamaño)
// ^BYw,r,h - Código de barras (w=ancho de módulo, r=relación de ancho/estrecho, h=altura)
// ^BCN,100,N,N,N,A - Código de barras 128 (N=orientación normal, 100=alto, N=no imprimir caracteres, N=sin barra de inicio/parada, N=no legible, A=automático)
// ^FD - Datos del campo
// ^FS - Fin del campo
// ^XZ - Fin del formato de etiqueta

$zpl = "^XA\n";
$zpl .= "^MMT\n"; // Modo de desgarro (Tear Off)
$zpl .= "^PW812\n"; // Ancho del papel 4 pulgadas (812 dots @ 203 DPI)
$zpl .= "^LL406\n"; // Longitud de la etiqueta 2 pulgadas (406 dots @ 203 DPI)
$zpl .= "^LS0\n"; // Ajuste del margen izquierdo

// Título "Activo Fijo" o Nombre del Equipo (opcional)
if (!empty($nombre_equipo)) {
    $zpl .= "^FO{$margin_x},{$margin_y}^A0N,35,35^FD".strtoupper($nombre_equipo)."^FS\n";
    $margin_y += 45; // Mueve hacia abajo para el siguiente elemento
}


// Número de Serie (texto legible)
// Centrar el texto en el ancho disponible de la etiqueta
$x_text_serie = $margin_x; // Posición inicial
$zpl .= "^FO{$x_text_serie},{$margin_y}^A0N,40,40^FDSERIE: {$numero_serie}^FS\n";
$margin_y += 60; // Mueve hacia abajo para el código de barras

// Código de Barras (Code 128)
// ^BY anchos de modulo, relacion, altura. EJ: ^BY2,3,100 (ancho barra delgada 2dots, relacion 3:1, altura 100dots)
// La etiqueta es 4x2 pulgadas, un buen alto de barra puede ser 100-150.
$barcode_height = 120; // Altura del código de barras
$barcode_width_unit = 2; // Ancho del módulo (dot) para las barras delgadas
$barcode_ratio = 3; // Relación de ancho/estrecho (3:1)

// Calcular posición X para centrar el código de barras
// Estimación del ancho del código de barras: aproximadamente 11 * (ancho_unidad * relacion) + (ancho_unidad * 10) por caracter
// Esto es complejo de centrar perfectamente sin saber la longitud final del Code128.
// Una aproximación simple es dejar un margen.
$barcode_x = 50; // Ajusta según sea necesario
$zpl .= "^FO{$barcode_x},{$margin_y}^BY{$barcode_width_unit},{$barcode_ratio},{$barcode_height}^BCN,{$barcode_height},N,N,N,A^FD{$numero_serie}^FS\n";
$margin_y += $barcode_height + 20; // Mueve hacia abajo para el texto debajo del código de barras

// Texto del número de serie debajo del código de barras
// Centrar texto del número de serie
$x_text_below_barcode = $margin_x;
$zpl .= "^FO{$x_text_below_barcode},{$margin_y}^A0N,30,30^FD{$numero_serie}^FS\n";

$zpl .= "^XZ\n";

// Configurar cabeceras para descargar el archivo o mostrarlo en el navegador
header('Content-Type: text/plain');
header('Content-Disposition: attachment; filename="etiqueta_'. $numero_serie .'.zpl"'); // Descargar
// header('Content-Disposition: inline; filename="etiqueta_'. $numero_serie .'.zpl"'); // Mostrar en navegador

echo $zpl;
exit;