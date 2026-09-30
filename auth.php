<?php
session_start();

// Validar que el usuario haya iniciado sesión
function verificarSesion() {
    if (!isset($_SESSION['usuario_id'])) {
        http_response_code(401);
        echo json_encode(["error" => "Sesión no iniciada"]);
        exit();
    }
}

// Validar permisos según el rol
function verificarPermiso($rolesPermitidos = []) {
    verificarSesion();
    
    // Superadmin siempre tiene acceso absoluto
    if ($_SESSION['rol_nombre'] === 'superadmin') {
        return true;
    }

    if (!in_array($_SESSION['rol_nombre'], $rolesPermitidos)) {
        http_response_code(403);
        echo json_encode(["error" => "No tienes permisos suficientes para realizar esta acción"]);
        exit();
    }
}
?>