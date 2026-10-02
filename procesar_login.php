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
        if ($password_input === $user['password']) {
            $_SESSION['id_usuario'] = $user['id'];
            $_SESSION['usuario']    = $user['usuario'];
            $_SESSION['nombre']     = $user['nombre'];
            $_SESSION['rol']        = strtolower(trim($user['nombre_rol']));

            $redirect = ($_SESSION['rol'] === 'superadmin') ? "superadmin_panel.php" : "inicio.php";

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