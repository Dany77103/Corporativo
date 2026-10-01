<?php
// guardar_usuario.php
require_once 'db.php'; // Cambia 'db.php' por el nombre exacto de tu archivo de conexión a MySQL
session_start();

// 1. Validar que el usuario que intenta guardar sea Superusuario
if (!isset($_SESSION['rol_nombre']) || $_SESSION['rol_nombre'] !== 'superadmin') {
    http_response_code(403);
    echo json_encode(["status" => "error", "mensaje" => "Acceso denegado: Solo el Superusuario puede gestionar usuarios y asignar roles"]);
    exit();
}

// 2. Capturar y limpiar datos del formulario
$id = !empty($_POST['id']) ? intval($_POST['id']) : null;
$usuario = trim($_POST['usuario'] ?? '');
$nombre = trim($_POST['nombre'] ?? '');
$password = $_POST['password'] ?? '';
$rol_id = !empty($_POST['rol_id']) ? intval($_POST['rol_id']) : 4; // Rol por defecto: 'solo_ver' (ID 4)

// 3. Validación de campos NOT NULL
if (empty($usuario) || empty($nombre) || (is_null($id) && empty($password))) {
    http_response_code(400);
    echo json_encode(["status" => "error", "mensaje" => "Los campos 'Usuario' y 'Nombre' son obligatorios (NOT NULL)"]);
    exit();
}

try {
    if ($id) {
        // ACTUALIZACIÓN DE USUARIOS
        if (!empty($password)) {
            // Se actualizan todos los datos incluyendo nueva contraseña encriptada
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("UPDATE usuarios SET usuario = ?, nombre = ?, password = ?, rol_id = ? WHERE id = ?");
            $stmt->execute([$usuario, $nombre, $hash, $rol_id, $id]);
        } else {
            // Se actualizan datos sin cambiar la contraseña
            $stmt = $pdo->prepare("UPDATE usuarios SET usuario = ?, nombre = ?, rol_id = ? WHERE id = ?");
            $stmt->execute([$usuario, $nombre, $rol_id, $id]);
        }
        $mensaje = "Usuario actualizado correctamente.";
    } else {
        // REGISTRO DE NUEVO USUARIO
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("INSERT INTO usuarios (usuario, nombre, password, rol_id) VALUES (?, ?, ?, ?)");
        $stmt->execute([$usuario, $nombre, $hash, $rol_id]);
        $mensaje = "Usuario creado exitosamente.";
    }

    echo json_encode(["status" => "success", "mensaje" => $mensaje]);

} catch (PDOException $e) {
    http_response_code(500);
    // Controlar duplicados de nombre de usuario
    if ($e->getCode() == 23000) {
        echo json_encode(["status" => "error", "mensaje" => "El nombre de usuario ya está registrado en el sistema."]);
    } else {
        echo json_encode(["status" => "error", "mensaje" => "Error en la base de datos: " . $e->getMessage()]);
    }
}
?>