<?php
// Inicia o reanuda la sesión actual.
session_start();

// Desvincula todas las variables de la sesión.
session_unset();

// Destruye toda la información registrada de una sesión.
session_destroy();

// Redirige al usuario a la página de login (index.html).
header("Location: index.html");
exit; // Asegura que el script se detenga después de la redirección.
?>