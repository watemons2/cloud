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
  <title>Recuperar Contraseña - ControlVA</title>
  <link rel="icon" type="image/png" href="logo.png">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <link rel="stylesheet" href="css/styles.css?v=<?php echo time(); ?>">
</head>
<body class="d-flex flex-column justify-content-center align-items-center vh-100">

<div class="card shadow-lg p-4" style="width: 28rem;">
    <div class="card-body text-center">
        <img src="logo.png" alt="Logo" style="width: 100px; margin-bottom: 1rem;">
        
        <div id="vistaSolicitar">
            <h3 class="card-title mb-3">Recuperar Contraseña</h3>
            <p class="text-muted">Introduce tu correo electrónico y te enviaremos un código de 6 dígitos.</p>
            <form id="solicitarForm">
                <div class="mb-3 text-start">
                    <label for="email" class="form-label">Correo Electrónico</label>
                    <input type="email" class="form-control" id="email" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Enviar Código de Verificación</button>
            </form>
        </div>

        <div id="vistaResetear" style="display: none;">
            <h3 class="card-title mb-3">Ingresa el Código</h3>
            <p class="text-muted">Revisa tu correo e introduce el código junto con tu nueva contraseña.</p>
            <form id="resetearForm">
                <div class="mb-3 text-start"><label for="codigo" class="form-label">Código de Verificación</label><input type="text" class="form-control" id="codigo" required></div>
                <div class="mb-3 text-start"><label for="password_nueva" class="form-label">Nueva Contraseña</label><input type="password" class="form-control" id="password_nueva" required></div>
                <div class="mb-3 text-start"><label for="password_confirmar" class="form-label">Confirmar Nueva Contraseña</label><input type="password" class="form-control" id="password_confirmar" required></div>
                <button type="submit" class="btn btn-warning w-100">Restablecer Contraseña</button>
            </form>
        </div>
        
        <hr class="my-3">
        <a href="index.html" class="btn btn-secondary w-100">Volver a Inicio de Sesión</a>
    </div>
</div>

<script>
    const vistaSolicitar = document.getElementById('vistaSolicitar');
    const vistaResetear = document.getElementById('vistaResetear');
    const emailInput = document.getElementById('email');

    // Manejar el primer formulario (solicitar código)
    document.getElementById("solicitarForm").addEventListener("submit", function(e){
        e.preventDefault();
        let datos = new FormData();
        datos.append("accion", "solicitarReseteoCodigo");
        datos.append("email", emailInput.value);

        fetch("api.php",{ method:"POST", body:datos })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                Swal.fire({ icon: "success", title: "¡Correo Enviado!", text: data.message });
                vistaSolicitar.style.display = 'none';
                vistaResetear.style.display = 'block';
            } else {
                Swal.fire({ icon: "error", title: "Error", text: data.message });
            }
        })
        .catch(err => {
            console.error("Error en fetch (solicitar):", err);
            Swal.fire({ icon: 'error', title: 'Error de Conexión', text: 'No se pudo comunicar con el servidor. Revisa la consola (F12) para más detalles.' });
        });
    });

    // Manejar el segundo formulario (restablecer contraseña)
    document.getElementById("resetearForm").addEventListener("submit", function(e){
        e.preventDefault();
        const nueva = document.getElementById('password_nueva').value;
        const confirmar = document.getElementById('password_confirmar').value;

        if (nueva !== confirmar) {
            Swal.fire({ icon: 'error', title: 'Error', text: 'Las contraseñas no coinciden.' });
            return;
        }

        let datos = new FormData();
        datos.append("accion", "resetearPasswordCodigo");
        datos.append("email", emailInput.value);
        datos.append("codigo", document.getElementById("codigo").value);
        datos.append("password_nueva", nueva);
        
        fetch("api.php",{ method:"POST", body:datos })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                Swal.fire({ icon: "success", title: "¡Contraseña Restablecida!", text: "Ya puedes iniciar sesión con tu nueva contraseña." })
                .then(() => { window.location = "index.html"; });
            } else {
                Swal.fire({ icon: "error", title: "Error", text: data.message });
            }
        })
        // ▼▼▼ BLOQUE AÑADIDO PARA CAPTURAR ERRORES ▼▼▼
        .catch(err => {
            console.error("Error en fetch (resetear):", err);
            Swal.fire({
                icon: 'error',
                title: 'Error de Conexión',
                text: 'No se pudo comunicar con el servidor. Revisa la consola (F12) para más detalles.'
            });
        });
    });
</script>
</body>
</html>
