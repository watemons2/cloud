<?php
// Se asegura que la respuesta siempre sea de tipo JSON
header('Content-Type: application/json');

session_start();
// Asegúrate de que db.php tenga la corrección de zona horaria que hicimos antes
include "db.php"; 

// Incluir los archivos de PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
// Ajusta estas rutas si tus archivos están en otra carpeta, 
// pero basado en tu código anterior, parece que están en la raíz o carpeta PHPMailer/
require 'PHPMailer/Exception.php';
require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';


// --- ACCIÓN PARA REGISTRAR USUARIO ---
if (isset($_POST['accion']) && $_POST['accion'] == "registrarUsuario") {
    $nombre = $_POST['nombre_completo'];
    $email = $_POST['email'];
    $user = $_POST['usuario'];
    $pass = md5($_POST['password']);
    $rol = 'Usuario';
    $codigo = rand(100000, 999999);
    $verificado = 0;

    $stmt_check = $conn->prepare("SELECT id FROM usuarios WHERE username = ? OR email = ?");
    $stmt_check->bind_param("ss", $user, $email);
    $stmt_check->execute();
    if ($stmt_check->get_result()->num_rows > 0) {
        echo json_encode(["success"=>false, "message"=>"El nombre de usuario o el email ya están registrados."]);
        exit;
    }

    $stmt_insert = $conn->prepare("INSERT INTO usuarios (nombre_completo, email, username, password, rol, codigo_verificacion, verificado) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt_insert->bind_param("ssssssi", $nombre, $email, $user, $pass, $rol, $codigo, $verificado);
    
    if ($stmt_insert->execute()) {
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'sismtocomercialva@gmail.com';
            $mail->Password   = 'mjslrvvaflnnrzth';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port       = 465;
            $mail->setFrom('sismtocomercialva@gmail.com', 'ControlVA');
            $mail->addAddress($email, $nombre);
            $mail->isHTML(true);
            $mail->Subject = 'Codigo de Verificacion - ControlVA';
            $mail->Body    = "Hola <b>$nombre</b>,<br><br>Gracias por registrarte en ControlVA. Tu codigo de verificacion es: <h1>$codigo</h1><br>Usa este codigo para activar tu cuenta.<br><br>Saludos,<br>El equipo de ControlVA.";
            $mail->send();
            echo json_encode(["success"=>true, "message"=>"Revisa tu correo electrónico para obtener tu código de verificación."]);
        } catch (Exception $e) {
            echo json_encode(["success"=>false, "message"=>"El usuario fue creado, pero no se pudo enviar el correo. Error de Mailer: " . $mail->ErrorInfo]);
        }
    } else {
        echo json_encode(["success"=>false, "message"=>"Ocurrió un error durante el registro."]);
    }
    exit;
}

// --- ACCIÓN PARA VERIFICAR CÓDIGO DE REGISTRO ---
if (isset($_POST['accion']) && $_POST['accion'] == "verificarCodigo") {
    $email = $_POST['email'];
    $codigo = $_POST['codigo'];
    $stmt = $conn->prepare("SELECT id, codigo_verificacion FROM usuarios WHERE email = ? AND verificado = 0");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($user = $result->fetch_assoc()) {
        if ($user['codigo_verificacion'] == $codigo) {
            $stmt_update = $conn->prepare("UPDATE usuarios SET verificado = 1, codigo_verificacion = NULL WHERE id = ?");
            $stmt_update->bind_param("i", $user['id']);
            $stmt_update->execute();
            echo json_encode(["success"=>true]);
        } else {
            echo json_encode(["success"=>false, "message"=>"El código de verificación es incorrecto."]);
        }
    } else {
        echo json_encode(["success"=>false, "message"=>"El correo no fue encontrado o la cuenta ya está verificada."]);
    }
    exit;
}

// --- ACCIÓN DE LOGIN ---
if (isset($_POST['accion']) && $_POST['accion'] == "login") {
    $user = $_POST['usuario'];
    $pass = md5($_POST['password']);
    $stmt = $conn->prepare("SELECT id, username, rol, foto_perfil FROM usuarios WHERE username=? AND password=? AND verificado = 1 LIMIT 1");
    $stmt->bind_param("ss", $user, $pass);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $_SESSION['user_id'] = $row['id'];
        $_SESSION['usuario'] = $row['username'];
        $_SESSION['rol'] = $row['rol'];
        $_SESSION['foto_perfil'] = $row['foto_perfil'];
        echo json_encode(["success"=>true]);
    } else {
        echo json_encode(["success"=>false, "message"=>"Usuario o contraseña incorrectos, o la cuenta no ha sido verificada."]);
    }
    exit;
}

// --- ACCIÓN PARA SOLICITAR RESETEO DE CONTRASEÑA ---
if (isset($_POST['accion']) && $_POST['accion'] == "solicitarReseteoCodigo") {
    $email = $_POST['email'];
    $stmt = $conn->prepare("SELECT id, nombre_completo FROM usuarios WHERE email = ? AND verificado = 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($user = $result->fetch_assoc()) {
        $codigo = rand(100000, 999999);
        $expira = date("Y-m-d H:i:s", time() + 600);
        $stmt_update = $conn->prepare("UPDATE usuarios SET codigo_verificacion = ?, codigo_expira = ? WHERE id = ?");
        $stmt_update->bind_param("ssi", $codigo, $expira, $user['id']);
        $stmt_update->execute();
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'sismtocomercialva@gmail.com';
            $mail->Password   = 'mjslrvvaflnnrzth';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port       = 465;
            $mail->setFrom('sismtocomercialva@gmail.com', 'Soporte ControlVA');
            $mail->addAddress($email, $user['nombre_completo']);
            $mail->isHTML(true);
            $mail->Subject = 'Tu Codigo para Restablecer Contraseña - ControlVA';
            $mail->Body    = "Hola <b>{$user['nombre_completo']}</b>,<br><br>Hemos recibido una solicitud para restablecer tu contraseña. Tu código de verificación es: <h1>$codigo</h1><br>Si no solicitaste esto, puedes ignorar este correo. El código es válido por 10 minutos.";
            $mail->send();
            echo json_encode(["success"=>true, "message"=>"Si tu correo está registrado, te hemos enviado un código de 6 dígitos."]);
        } catch (Exception $e) {
            echo json_encode(["success"=>false, "message"=>"No se pudo enviar el correo. Error: " . $mail->ErrorInfo]);
        }
    } else {
        echo json_encode(["success"=>true, "message"=>"Si tu correo está registrado, te hemos enviado un código de 6 dígitos."]);
    }
    exit;
}

// --- ACCIÓN PARA PROCESAR EL RESETEO DE CONTRASEÑA ---
if (isset($_POST['accion']) && $_POST['accion'] == "resetearPasswordCodigo") {
    $email = $_POST['email'];
    $codigo = $_POST['codigo'];
    $nuevaPassword = md5($_POST['password_nueva']);
    $stmt = $conn->prepare("SELECT id, email, codigo_verificacion, username, nombre_completo FROM usuarios WHERE email = ? AND codigo_expira > NOW()");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($user = $result->fetch_assoc()) {
        if ($user['codigo_verificacion'] == $codigo) {
            $stmt_update = $conn->prepare("UPDATE usuarios SET password = ?, codigo_verificacion = NULL, codigo_expira = NULL WHERE id = ?");
            $stmt_update->bind_param("si", $nuevaPassword, $user['id']);
            $stmt_update->execute();
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP(); $mail->Host = 'smtp.gmail.com'; $mail->SMTPAuth = true;
                $mail->Username = 'sismtocomercialva@gmail.com'; $mail->Password = 'mjslrvvaflnnrzth';
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; $mail->Port = 465;
                $mail->setFrom('sismtocomercialva@gmail.com', 'Seguridad ControlVA');
                $mail->addAddress($user['email'], $user['nombre_completo']);
                $mail->isHTML(true); $mail->Subject = 'Confirmacion de Cambio de Contraseña - ControlVA';
                $mail->Body    = "Hola <b>{$user['nombre_completo']}</b>,<br><br>Te confirmamos que la contraseña para tu usuario <b>{$user['username']}</b> ha sido restablecida exitosamente.";
                $mail->send();
            } catch (Exception $e) {}
            echo json_encode(["success"=>true]);
        } else {
            echo json_encode(["success"=>false, "message"=>"El código de verificación es incorrecto."]);
        }
    } else {
        echo json_encode(["success"=>false, "message"=>"El código no es válido o ha expirado."]);
    }
    exit;
}

// --- ACCIÓN PARA ACTUALIZAR PERFIL ---
if (isset($_POST['accion']) && $_POST['accion'] == "actualizarPerfil") { if (!isset($_SESSION['user_id'])) { echo json_encode(["success" => false, "message" => "Sesión no válida."]); exit; } $userId = $_SESSION['user_id']; $newUsername = $_POST['usuario']; $fotoActual = $_SESSION['foto_perfil']; if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] === UPLOAD_ERR_OK) { if (!empty($fotoActual) && file_exists($fotoActual)) { unlink($fotoActual); } $directorioUploads = 'uploads/'; $nombreArchivo = 'perfil_' . $userId . '_' . time() . '_' . basename($_FILES['foto_perfil']['name']); $rutaCompleta = $directorioUploads . $nombreArchivo; if (move_uploaded_file($_FILES['foto_perfil']['tmp_name'], $rutaCompleta)) { $fotoActual = $rutaCompleta; } } $stmt = $conn->prepare("UPDATE usuarios SET username = ?, foto_perfil = ? WHERE id = ?"); $stmt->bind_param("ssi", $newUsername, $fotoActual, $userId); if ($stmt->execute()) { $_SESSION['usuario'] = $newUsername; $_SESSION['foto_perfil'] = $fotoActual; echo json_encode(["success" => true, "message" => "Perfil actualizado.", "newUsername" => $newUsername, "newPhoto" => $fotoActual]); } else { echo json_encode(["success" => false, "message" => "Error al actualizar el perfil."]); } $stmt->close(); exit; }

// --- ACCIÓN PARA CAMBIAR CONTRASEÑA ---
if (isset($_POST['accion']) && $_POST['accion'] == "cambiarPassword") { if (!isset($_SESSION['user_id'])) { echo json_encode(["success" => false, "message" => "Sesión no válida."]); exit; } $userId = $_SESSION['user_id']; $passActual = md5($_POST['password_actual']); $passNueva = md5($_POST['password_nueva']); $stmt = $conn->prepare("SELECT password FROM usuarios WHERE id = ?"); $stmt->bind_param("i", $userId); $stmt->execute(); $user = $stmt->get_result()->fetch_assoc(); if ($user && $user['password'] === $passActual) { $stmt_update = $conn->prepare("UPDATE usuarios SET password = ? WHERE id = ?"); $stmt_update->bind_param("si", $passNueva, $userId); if ($stmt_update->execute()) { echo json_encode(["success" => true, "message" => "Contraseña actualizada con éxito."]); } else { echo json_encode(["success" => false, "message" => "Error al actualizar la contraseña."]); } $stmt_update->close(); } else { echo json_encode(["success" => false, "message" => "La contraseña actual es incorrecta."]); } $stmt->close(); exit; }


// =========================================================================
// NUEVA FUNCIONALIDAD: ELIMINAR HISTORIAL (SOLO ADMINISTRADORES)
// =========================================================================
if (isset($_POST['accion']) && $_POST['accion'] == "eliminarHistorial") {
    // 1. Verificar si es Administrador
    if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'Administrador') {
        echo json_encode(["success" => false, "message" => "Acceso denegado. Solo administradores pueden eliminar historial."]);
        exit;
    }

    // 2. Obtener el ID de la bitácora
    $id_bitacora = intval($_POST['id_bitacora']);

    // 3. Eliminar de la base de datos
    $stmt = $conn->prepare("DELETE FROM bitacora WHERE id = ?");
    $stmt->bind_param("i", $id_bitacora);
    
    if ($stmt->execute()) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "message" => "Error en base de datos al eliminar."]);
    }
    $stmt->close();
    exit;
}
// =========================================================================


// --- ACCIÓN PARA REPORTAR INCIDENTE ---
if (isset($_POST['accion']) && $_POST['accion'] == "reportarIncidente") { 
    if (!isset($_SESSION['user_id'])) { echo json_encode(["success" => false, "message" => "Sesión no válida."]); exit; } 
    $id = intval($_POST['id']); 
    $nuevoProblema = $_POST['nuevo_problema']; 
    $usuarioId = $_SESSION['user_id']; 
    
    // Obtener imagen actual
    $rutaImagen = null; 
    $stmt_select = $conn->prepare("SELECT imagen FROM registros WHERE id = ?"); 
    $stmt_select->bind_param("i", $id); 
    $stmt_select->execute(); 
    $result = $stmt_select->get_result(); 
    if ($row = $result->fetch_assoc()) { $rutaImagen = $row['imagen']; } 
    $stmt_select->close(); 
    
    // Manejo de nueva imagen
    if (isset($_FILES['nueva_imagen_problema']) && $_FILES['nueva_imagen_problema']['error'] === UPLOAD_ERR_OK) { 
        if (!empty($rutaImagen) && file_exists($rutaImagen)) { unlink($rutaImagen); } 
        $directorioUploads = 'uploads/'; 
        $nombreArchivo = uniqid() . '-' . basename($_FILES['nueva_imagen_problema']['name']); 
        $rutaImagen = $directorioUploads . $nombreArchivo; 
        if (!move_uploaded_file($_FILES['nueva_imagen_problema']['tmp_name'], $rutaImagen)) { $rutaImagen = null; } 
    } 
    
    // Actualizar registro
    $stmt_update = $conn->prepare("UPDATE registros SET problema = ?, imagen = ?, estatus = 'con falla' WHERE id = ?"); 
    $stmt_update->bind_param("ssi", $nuevoProblema, $rutaImagen, $id); 
    $stmt_update->execute(); 
    $stmt_update->close(); 
    
    // GUARDAR EN BITACORA (ACTUALIZADO: AHORA GUARDA TEXTO EN 'accion')
    $fecha_actual = date("Y-m-d H:i:s"); 
    $accion_texto = "Reporte Falla: " . $nuevoProblema; // Guardamos el texto

    $stmt_bitacora = $conn->prepare("INSERT INTO bitacora (registro_id, usuario_id, accion, fecha) VALUES (?, ?, ?, ?)"); 
    $stmt_bitacora->bind_param("iiss", $id, $usuarioId, $accion_texto, $fecha_actual); // 'ss' para accion y fecha
    
    if ($stmt_bitacora->execute()) { echo json_encode(["success" => true]); } 
    else { echo json_encode(["success" => false, "message" => "No se pudo registrar el incidente en la bitácora."]); } 
    $stmt_bitacora->close(); 
    exit; 
}

// --- ACCIÓN PARA GUARDAR REPARACIÓN ---
if (isset($_POST['accion']) && $_POST['accion'] == "guardarReparacion") {
    if (!isset($_SESSION['user_id']) || ($_SESSION['rol'] !== 'Tecnico' && $_SESSION['rol'] !== 'Administrador')) {
        echo json_encode(["success" => false, "message" => "Acción no autorizada."]);
        exit;
    }
    $registro_id = intval($_POST['id']);
    $solucion = $_POST['solucion'];
    $usuario_id = $_SESSION['user_id'];
    
    // Imagen de solución
    $ruta_imagen_solucion = null;
    if (isset($_FILES['imagen_solucion']) && $_FILES['imagen_solucion']['error'] === UPLOAD_ERR_OK) {
        $directorioUploads = 'uploads/';
        $nombreArchivo = 'solucion_' . uniqid() . '-' . basename($_FILES['imagen_solucion']['name']);
        $rutaCompleta = $directorioUploads . $nombreArchivo;
        if (move_uploaded_file($_FILES['imagen_solucion']['tmp_name'], $rutaCompleta)) { $ruta_imagen_solucion = $rutaCompleta; }
    }
    
    // Actualizar registro
    $stmt_update = $conn->prepare("UPDATE registros SET solucion = ?, imagen_solucion = ?, estatus = 'sin falla' WHERE id = ?");
    $stmt_update->bind_param("ssi", $solucion, $ruta_imagen_solucion, $registro_id);
    $stmt_update->execute();
    $stmt_update->close();
    
    // GUARDAR EN BITACORA (ACTUALIZADO: AHORA GUARDA TEXTO EN 'accion')
    $fecha_actual = date("Y-m-d H:i:s");
    $accion_texto = "Reparación: " . $solucion; // Guardamos el texto

    $stmt_bitacora = $conn->prepare("INSERT INTO bitacora (registro_id, usuario_id, accion, fecha) VALUES (?, ?, ?, ?)");
    $stmt_bitacora->bind_param("iiss", $registro_id, $usuario_id, $accion_texto, $fecha_actual);
    
    if ($stmt_bitacora->execute()) { echo json_encode(["success" => true]); } 
    else { echo json_encode(["success" => false, "message" => "Error al guardar en la bitácora."]); }
    $stmt_bitacora->close();
    exit;
}

// --- ACCIÓN PARA BORRAR EQUIPO ---
if (isset($_POST['accion']) && $_POST['accion'] == "borrarEquipo") {
    if (!isset($_SESSION['usuario']) || ($_SESSION['rol'] !== 'Tecnico' && $_SESSION['rol'] !== 'Administrador')) {
        echo json_encode(["success" => false, "message" => "Acción no autorizada."]);
        exit;
    }
    $id = intval($_POST['id']);
    
    // Borrar imágenes físicas primero
    $stmt_select = $conn->prepare("SELECT imagen, imagen_solucion FROM registros WHERE id = ?");
    $stmt_select->bind_param("i", $id);
    $stmt_select->execute();
    $result = $stmt_select->get_result();
    if ($row = $result->fetch_assoc()) {
        if (!empty($row['imagen']) && file_exists($row['imagen'])) { unlink($row['imagen']); }
        if (!empty($row['imagen_solucion']) && file_exists($row['imagen_solucion'])) { unlink($row['imagen_solucion']); }
    }
    $stmt_select->close();
    
    // Borrar historial asociado
    $stmt_bitacora = $conn->prepare("DELETE FROM bitacora WHERE registro_id = ?");
    $stmt_bitacora->bind_param("i", $id);
    $stmt_bitacora->execute();
    $stmt_bitacora->close();
    
    // Borrar el equipo
    $stmt_registro = $conn->prepare("DELETE FROM registros WHERE id = ?");
    $stmt_registro->bind_param("i", $id);
    if ($stmt_registro->execute()) { echo json_encode(["success" => true]); } 
    else { echo json_encode(["success" => false, "message" => "Error al borrar el equipo."]); }
    $stmt_registro->close();
    exit;
}

// --- ACCIONES DE ADMINISTRADOR ---
if (isset($_POST['accion']) && $_POST['accion'] == "actualizarRol") {
    if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'Administrador') {
        echo json_encode(["success" => false, "message" => "Acción no autorizada."]);
        exit;
    }
    $userId = intval($_POST['user_id']);
    $nuevoRol = $_POST['rol'];
    if (!in_array($nuevoRol, ['Usuario', 'Tecnico', 'Administrador'])) {
        echo json_encode(["success" => false, "message" => "Rol no válido."]);
        exit;
    }
    $stmt = $conn->prepare("UPDATE usuarios SET rol = ? WHERE id = ?");
    $stmt->bind_param("si", $nuevoRol, $userId);
    if ($stmt->execute()) { echo json_encode(["success" => true]); } 
    else { echo json_encode(["success" => false, "message" => "No se pudo actualizar el rol."]); }
    $stmt->close();
    exit;
}

if (isset($_POST['accion']) && $_POST['accion'] == "eliminarUsuario") {
    if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'Administrador') {
        echo json_encode(["success" => false, "message" => "Acción no autorizada."]);
        exit;
    }
    $id = intval($_POST['id']);
    if ($id == $_SESSION['user_id']) {
        echo json_encode(["success" => false, "message" => "No puedes eliminar tu propia cuenta."]);
        exit;
    }
    $stmt_bitacora = $conn->prepare("DELETE FROM bitacora WHERE usuario_id = ?");
    $stmt_bitacora->bind_param("i", $id);
    $stmt_bitacora->execute();
    $stmt_bitacora->close();
    $stmt = $conn->prepare("DELETE FROM usuarios WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) { echo json_encode(["success" => true]); } 
    else { echo json_encode(["success" => false, "message" => "No se pudo eliminar el usuario."]); }
    $stmt->close();
    exit;
}

// --- ACCIÓN PARA BUSCAR POR CÓDIGO DE BARRAS ---
if (isset($_POST['accion']) && $_POST['accion'] == "buscarPorCodigo") {
    if (!isset($_SESSION['user_id'])) { echo json_encode(["success" => false, "message" => "Sesión no válida."]); exit; }
    $codigo_barras = $_POST['codigo_barras'];
    $stmt = $conn->prepare("SELECT id FROM registros WHERE codigo_barras = ? LIMIT 1");
    $stmt->bind_param("s", $codigo_barras);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        echo json_encode(["success" => true, "id" => $row['id']]);
    } else {
        echo json_encode(["success" => false]);
    }
    $stmt->close();
    exit;
}

// Si no coincide ninguna acción
echo json_encode(["success" => false, "message" => "Acción no reconocida."]);
?>