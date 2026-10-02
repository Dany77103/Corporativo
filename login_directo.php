<?php
session_start();
require_once 'conexion.php';

$usuarioPrueba = $_GET['user'] ?? 'admin';
$passwordPrueba = ($usuarioPrueba === 'superadmin') ? 'SuperAdmin2026' : 'admin123';

$stmt = $pdo->prepare("
    SELECT u.id, u.usuario, u.password, u.nombre, r.nombre AS rol_nombre 
    FROM usuarios u
    INNER JOIN roles r ON u.rol_id = r.id
    WHERE u.usuario = :usuario
");
$stmt->execute(['usuario' => $usuarioPrueba]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user && password_verify($passwordPrueba, $user['password'])) {
    session_regenerate_id(true);

    $_SESSION['usuario_id'] = $user['id'];
    $_SESSION['usuario']    = $user['nombre'];
    $_SESSION['nombre']     = $user['nombre'];
    $_SESSION['rol']        = $user['rol_nombre'];
    $_SESSION['rol_nombre'] = $user['rol_nombre'];

    $rolNombre = strtolower(trim($user['rol_nombre']));
    $redirectUrl = ($rolNombre === 'superadmin') ? 'superadmin_panel.php' : 'index.php';

    header("Location: " . $redirectUrl);
    exit();
} else {
    echo "Error al autenticar al usuario $usuarioPrueba";
}
?>