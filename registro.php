<!DOCTYPE html>
<html lang="es">
<head>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta charset="UTF-8">
  <script>
    (() => {
      'use strict'
      const storedTheme = localStorage.getItem('theme')
      if (storedTheme) { document.documentElement.setAttribute('data-bs-theme', storedTheme) }
    })()
  </script>
  <title>Registro - ControlVA</title>
  <link rel="icon" type="image/png" href="logo.png">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <link rel="stylesheet" href="css/styles.css?v=<?php echo time(); ?>">
</head>
<body class="d-flex flex-column justify-content-center align-items-center vh-100">

<div class="card shadow-lg p-4" style="width: 26rem;">
    <div class="card-body text-center">
        <img src="logo.png" alt="Logo de Comercial VA" style="width: 100px; margin-bottom: 1rem;">
        <h3 class="card-title mb-3">Crear una Cuenta</h3>
        <form id="registroForm">
            <div class="mb-3 text-start"><label for="nombre_completo" class="form-label">Nombre Completo</label><input type="text" class="form-control" id="nombre_completo" required></div>
            <div class="mb-3 text-start"><label for="email" class="form-label">Correo Electrónico</label><input type="email" class="form-control" id="email" required></div>
            <div class="mb-3 text-start"><label for="usuario" class="form-label">Usuario</label><input type="text" class="form-control" id="usuario" required></div>
            <div class="mb-3 text-start"><label for="password" class="form-label">Contraseña</label><input type="password" class="form-control" id="password" required></div>
            <button type="submit" class="btn btn-success w-100">Crear Cuenta</button>
        </form>
        <hr class="my-3">
        <a href="index.html" class="btn btn-secondary w-100">Volver a Inicio de Sesión</a>
    </div>
</div>

<script>
document.getElementById("registroForm").addEventListener("submit", function(e){
    e.preventDefault();
    let datos = new FormData();
    datos.append("accion", "registrarUsuario");
    datos.append("nombre_completo", document.getElementById("nombre_completo").value);
    datos.append("email", document.getElementById("email").value);
    datos.append("usuario", document.getElementById("usuario").value);
    datos.append("password", document.getElementById("password").value);

    fetch("api.php",{ method:"POST", body:datos })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          Swal.fire({ icon: "success", title: "¡Casi listo!", text: data.message })
          .then(() => { window.location = "verificacion.php"; });
        } else {
          Swal.fire({ icon: "error", title: "Error en el Registro", text: data.message });
        }
      });
});
</script>
</body>
</html>
