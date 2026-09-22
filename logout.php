<?php
session_start();

// Borrar todas las variables de sesión
$_SESSION = array();

// Si se desea destruir la cookie de sesión, también se puede borrar
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destruir completamente la sesión en el servidor
session_destroy();

// Redireccionar de inmediato al login limpio
header("Location: login.php");
exit();