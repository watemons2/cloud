<?php
include "db.php";
$token_valido = false;
$user_email = '';

if (isset($_GET['codigo'])) {
    $token = $_GET['codigo'];
    $stmt = $conn->prepare("SELECT email FROM usuarios WHERE reset_token = ? AND reset_token_expira > NOW()");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($user = $result->fetch_assoc()) {
        $token_valido = true;
        $user_email = $user['email'];
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <script>
    (() => {
      'use strict'
      const storedTheme = localStorage.getItem('theme')
      if (storedTheme) { document.documentElement.setAttribute('data-bs-theme', storedTheme) }
    })()
  </script>
  <title>Restablecer Contraseña - ControlVA</title>
  <link rel="icon" type="image/png" href="logo.png">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <link rel="stylesheet" href="css/styles.css?v=<?php echo time(); ?>">
</head>
<body class="d-flex flex-column justify-content-center align-items-center vh-100">

<div class="card shadow-lg p-4" style="width: 26rem;">
    <div class="card-body text-center">
        <img src="logo.png" alt="Logo" style="width: 100px; margin-bottom: 1rem;">
        <h3 class="card-title mb-3">Restablecer Contraseña</h3>
        
        <?php if ($token_valido): ?>
        <p class="text-muted">Hola, <?= htmlspecialchars($user_email) ?>. Ingresa tu nueva contraseña.</p>
        <form id="resetForm">
            <input type="hidden" name="codigo" value="<?= htmlspecialchars($token) ?>">
            <div class="mb-3 text-start"><label for="password_nueva" class="form-label">Nueva Contraseña</label><input type="password" class="form-control" id="password_nueva" required></div>
            <div class="mb-3 text-start"><label for="password_confirmar" class="form-label">Confirmar Contraseña</label><input type="password" class="form-control" id="password_confirmar" required></div>
            <button type="submit" class="btn btn-primary w-100">Guardar Nueva Contraseña</button>
        </form>
        <?php else: ?>
        <div class="alert alert-danger">
            El código de restablecimiento no es válido o ha expirado. Por favor, solicita uno nuevo.
        </div>
        <a href="recuperar.php" class="btn btn-secondary w-100">Solicitar de nuevo</a>
        <?php endif; ?>
    </div>
</div>

<script>
<?php if ($token_valido): ?>
document.getElementById("resetForm").addEventListener("submit", function(e){
    e.preventDefault();
    const nueva = document.getElementById('password_nueva').value;
    const confirmar = document.getElementById('password_confirmar').value;

    if (nueva !== confirmar) {
        Swal.fire({ icon: 'error', title: 'Error', text: 'Las contraseñas no coinciden.' });
        return;
    }

    let datos = new FormData(this);
    datos.append("accion", "resetearPassword");
    
    fetch("api.php",{ method:"POST", body:datos })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          Swal.fire({ icon: "success", title: "¡Contraseña Restablecida!", text: "Ya puedes iniciar sesión con tu nueva contraseña." })
          .then(() => { window.location = "index.html"; });
        } else {
          Swal.fire({ icon: "error", title: "Error", text: data.message });
        }
      });
});
<?php endif; ?>
</script>
</body>
</html>