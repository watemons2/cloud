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
  <title>Verificar Cuenta - ControlVA</title>
  <link rel="icon" type="image/png" href="logo.png">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <link rel="stylesheet" href="css/styles.css?v=<?php echo time(); ?>">
</head>
<body class="d-flex flex-column justify-content-center align-items-center vh-100">

<div class="card shadow-lg p-4" style="width: 26rem;">
    <div class="card-body text-center">
        <img src="logo.png" alt="Logo" style="width: 100px; margin-bottom: 1rem;">
        <h3 class="card-title mb-3">Verificar tu Cuenta</h3>
        <p class="text-muted">Introduce tu correo y el código de 6 dígitos que te enviamos.</p>
        <form id="verificacionForm">
            <div class="mb-3 text-start"><label for="email" class="form-label">Correo Electrónico</label><input type="email" class="form-control" id="email" required></div>
            <div class="mb-3 text-start"><label for="codigo" class="form-label">Código de Verificación</label><input type="text" class="form-control" id="codigo" required></div>
            <button type="submit" class="btn btn-primary w-100">Verificar Cuenta</button>
        </form>
        <hr class="my-3">
        <a href="index.html" class="btn btn-secondary w-100">Volver a Inicio de Sesión</a>
    </div>
</div>

<script>
document.getElementById("verificacionForm").addEventListener("submit", function(e){
    e.preventDefault();
    let datos = new FormData();
    datos.append("accion", "verificarCodigo");
    datos.append("email", document.getElementById("email").value);
    datos.append("codigo", document.getElementById("codigo").value);

    fetch("api.php",{ method:"POST", body:datos })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          Swal.fire({ icon: "success", title: "¡Cuenta Verificada!", text: "Ahora puedes iniciar sesión." })
          .then(() => { window.location = "index.html"; });
        } else {
          Swal.fire({ icon: "error", title: "Error de Verificación", text: data.message });
        }
      });
});
</script>
</body>
</html>
