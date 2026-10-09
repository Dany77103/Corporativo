<?php
// guardar_usuario.php
require_once 'conexion.php';     // Conexión a la base de datos MySQL (PDO)
require_once 'config_roles.php'; // Matriz de roles y funciones de permisos

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json; charset=utf-8');

// ==========================================
// 1. VALIDACIÓN DE PERMISOS (PUNTO 2)
// ==========================================
// Verificar que el usuario tenga sesión activa y permisos para gestionar/crear usuarios
if (!isset($_SESSION['usuario_id']) || (!tienePermiso('crear_usuarios') && !tienePermiso('gestionar_admins'))) {
    http_response_code(403);
    echo json_encode([
        "status" => "error", 
        "mensaje" => "Acceso denegado: No tienes permisos para crear o modificar usuarios (modo solo lectura o sin privilegios)."
    ]);
    exit();
}

// ==========================================
// 2. CAPTURAR Y LIMPIAR DATOS DEL FORMULARIO
// ==========================================
$id       = !empty($_POST['id']) ? intval($_POST['id']) : null;
$usuario  = trim($_POST['usuario'] ?? '');
$nombre   = trim($_POST['nombre'] ?? '');
$password = $_POST['password'] ?? '';
$rol_id   = !empty($_POST['rol_id']) ? intval($_POST['rol_id']) : 4; // Rol por defecto: 'lector' (ID 4)

// ==========================================
// 3. VALIDACIÓN DE CAMPOS REQUERIDOS
// ==========================================
if (empty($usuario) || empty($nombre) || (is_null($id) && empty($password))) {
    http_response_code(400);
    echo json_encode([
        "status" => "error", 
        "mensaje" => "Los campos 'Usuario' y 'Nombre' son obligatorios. La contraseña es requerida para usuarios nuevos."
    ]);
    exit();
}

// ==========================================
// 4. PROCESAMIENTO EN BASE DE DATOS
// ==========================================
try {
    if ($id) {
        // ACTUALIZACIÓN DE USUARIO EXISTENTE
        if (!empty($password)) {
            // Actualización con nueva contraseña encriptada
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("UPDATE usuarios SET usuario = ?, nombre = ?, password = ?, rol_id = ? WHERE id = ?");
            $stmt->execute([$usuario, $nombre, $hash, $rol_id, $id]);
        } else {
            // Actualización sin modificar la contraseña
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