<?php
// Aseguramos que la respuesta sea JSON y no HTML
header('Content-Type: application/json');

// Desactivar impresión de errores en pantalla para no ensuciar el JSON
error_reporting(0); 
ini_set('display_errors', 0);

session_start();
include "db.php";

$response = ["success" => false, "message" => "Error desconocido"];

// Seguridad
if (!isset($_SESSION['usuario']) || ($_SESSION['rol'] !== 'Tecnico' && $_SESSION['rol'] !== 'Administrador')) {
    echo json_encode(["success" => false, "message" => "No autorizado"]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    if (!isset($_POST['id']) || empty($_POST['id'])) {
        echo json_encode(["success" => false, "message" => "ID no proporcionado"]);
        exit;
    }

    $id = intval($_POST['id']);
    $nombre = $_POST['nombre'];
    $serie = $_POST['serie'];
    $codigo_barras = !empty($_POST['codigo_barras']) ? $_POST['codigo_barras'] : $serie;
    $categoria = $_POST['categoria'];
    $ubicacion = $_POST['ubicacion'];

    // Usamos TRY-CATCH para capturar el error de duplicado
    try {
        $sql = "UPDATE registros SET nombre = ?, serie = ?, codigo_barras = ?, categoria = ?, ubicacion = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        
        if ($stmt === false) { throw new Exception("Error en preparación SQL: " . $conn->error); }

        $stmt->bind_param("sssssi", $nombre, $serie, $codigo_barras, $categoria, $ubicacion, $id);

        if ($stmt->execute()) {
            $response = ["success" => true, "message" => "Equipo actualizado correctamente"];
        } else {
            throw new Exception($stmt->error);
        }
        
        $stmt->close();

    } catch (mysqli_sql_exception $e) {
        // ERROR 1062 = DUPLICATE ENTRY
        if ($e->getCode() == 1062) {
            $response = [
                "success" => false, 
                "type" => "duplicate", // Marca especial para identificar este error
                "message" => "El Código de Barras o Serie ya existe en otro equipo."
            ];
        } else {
            $response = ["success" => false, "message" => "Error de BD: " . $e->getMessage()];
        }
    } catch (Exception $e) {
        $response = ["success" => false, "message" => "Error: " . $e->getMessage()];
    }

    $conn->close();
}

echo json_encode($response);
?>