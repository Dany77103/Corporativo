<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

$host     = "localhost";      
$user     = "root";           
$password = "";    
$database = "proyecto"; 

$conn = new mysqli($host, $user, $password, $database);
$conn->set_charset("utf8mb4");

if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Error de conexión a la base de datos"]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario_input  = trim($_POST['usuario'] ?? '');
    $password_input = trim($_POST['password'] ?? '');

    if (empty($usuario_input) || empty($password_input)) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Por favor llena todos los campos"]);
        exit();
    }

    $sql = "SELECT u.id, u.usuario, u.password, u.nombre, r.nombre_rol 
            FROM usuarios u 
            INNER JOIN roles r ON u.rol_id = r.id 
            WHERE LOWER(u.usuario) = LOWER(?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $usuario_input);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($user = $result->fetch_assoc()) {
        // Validación de contraseña (si usas texto plano; si usas hash cambia a password_verify)
        if ($password_input === $user['password']) {
            $rol_normalizado = strtolower(trim($user['nombre_rol']));

            // 1. Guardar datos principales del usuario
            $_SESSION['id_usuario'] = $user['id'];
            $_SESSION['usuario']    = $user['usuario']; // Requerido por la validación de index.php
            $_SESSION['nombre']     = $user['nombre'];
            $_SESSION['rol']        = $rol_normalizado;

            // 2. Cargar permisos de roles requeridos por config_roles.php / index.php
            $_SESSION['permisos'] = [
                'superadmin' => ($rol_normalizado === 'superadmin'),
                'admin'      => ($rol_normalizado === 'admin' || $rol_normalizado === 'administrador'),
                'soporte'    => ($rol_normalizado === 'soporte')
            ];

            // 3. Determinar la página de destino
            // Nota: Si quieres que TODOS vayan a index.php sin excepción, cambia $redirect a "index.php"
            $redirect = ($rol_normalizado === 'superadmin') ? "superadmin_panel.php" : "index.php";

            // Guardar y cerrar la sesión para evitar pérdida de datos al redirigir mediante JavaScript
            session_write_close();

            http_response_code(200);
            echo json_encode([
                "status"   => "success", 
                "message"  => "Inicio de sesión correcto", 
                "redirect" => $redirect
            ]);
            exit();
        }
    }

    http_response_code(401);
    echo json_encode(["status" => "error", "message" => "Usuario o contraseña incorrectos"]);
    exit();
}
?>