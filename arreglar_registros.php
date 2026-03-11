<?php
include 'db.php';

// Ver errores
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    echo "<h2>Reparando tabla 'registros'...</h2>";

    // 1. Agregar columna fecha_registro
    // Intentamos agregarla. Si ya existe, el catch lo detectará.
    $sql = "ALTER TABLE registros ADD COLUMN fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP";
    $conn->query($sql);
    
    echo "<h3 style='color: green;'>✅ ÉXITO: Se agregó la columna 'fecha_registro'.</h3>";

} catch (mysqli_sql_exception $e) {
    if ($e->getCode() == 1060) {
        echo "<h3 style='color: orange;'>⚠️ La columna 'fecha_registro' ya existía.</h3>";
    } else {
        echo "<h3 style='color: red;'>❌ Error: " . $e->getMessage() . "</h3>";
    }
}

// Opcional: Agregar columna 'solucion' por si acaso falta también
try {
    $sql2 = "ALTER TABLE registros ADD COLUMN solucion TEXT NULL";
    $conn->query($sql2);
    echo "<h3 style='color: green;'>✅ ÉXITO: Se agregó la columna 'solucion'.</h3>";
} catch (mysqli_sql_exception $e) {
    // Ignorar si ya existe
}

echo "<hr>";
echo "<p>Base de datos actualizada. Ahora intenta registrar el equipo de nuevo.</p>";
echo "<a href='nuevo_equipo.php'><button>Ir a Registrar Equipo</button></a>";

$conn->close();
?>