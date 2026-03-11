<?php
// Validar y obtener los datos de la URL
$serie = isset($_GET['serie']) ? htmlspecialchars($_GET['serie']) : 'SERIE-INVALIDA';
$nombre = isset($_GET['nombre']) ? htmlspecialchars($_GET['nombre']) : '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Imprimir Etiqueta: <?= $serie ?></title>
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            
            /* Usamos Flexbox para centrar la fila de etiquetas */
            display: flex;
            justify-content: space-around; /* Distribuye las etiquetas */
            align-items: center;
            
            /* Ancho total aproximado de la fila de 3 etiquetas (30mm * 3 + márgenes) */
            width: 100mm; 
            height: 14mm;
        }
        
        /* Contenedor para CADA etiqueta individual */
        .etiqueta {
            width: 30mm;
            height: 14mm;
            text-align: center;
            padding-top: 1.5mm;
            box-sizing: border-box;
            overflow: hidden;
        }
        
        .nombre-equipo {
            font-size: 6pt;
            font-weight: bold;
            margin: 0 0 1mm 0;
            overflow: hidden;
            white-space: nowrap;
            text-overflow: ellipsis;
        }
        .serie-text {
            font-size: 7pt;
            font-weight: bold;
            margin-top: 0.5mm;
            letter-spacing: -0.5px;
        }
        
        /* Reglas especiales para la impresión */
        @media print {
            @page {
                /* Tamaño del papel = ancho de la tira de 3 etiquetas */
                size: 100mm 14mm; 
                margin: 0; 
            }
            body {
                margin: 0;
                padding: 0;
            }
            .etiqueta {
                page-break-after: always;
            }
        }
    </style>
</head>
<body>
    
    <div class="etiqueta">
        <?php if(!empty($nombre)): ?><p class="nombre-equipo"><?= $nombre ?></p><?php endif; ?>
        <svg id="barcode1"></svg>
        <p class="serie-text"><?= $serie ?></p>
    </div>

    <div class="etiqueta">
        <?php if(!empty($nombre)): ?><p class="nombre-equipo"><?= $nombre ?></p><?php endif; ?>
        <svg id="barcode2"></svg>
        <p class="serie-text"><?= $serie ?></p>
    </div>

    <div class="etiqueta">
        <?php if(!empty($nombre)): ?><p class="nombre-equipo"><?= $nombre ?></p><?php endif; ?>
        <svg id="barcode3"></svg>
        <p class="serie-text"><?= $serie ?></p>
    </div>

    <script>
        // Generar el código de barras 3 veces, uno para cada etiqueta
        var options = {
            format: "CODE128",
            displayValue: false,
            width: 1,
            height: 25,
            margin: 0
        };

        JsBarcode("#barcode1", "<?= $serie ?>", options);
        JsBarcode("#barcode2", "<?= $serie ?>", options);
        JsBarcode("#barcode3", "<?= $serie ?>", options);

        // Iniciar la impresión automáticamente
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>