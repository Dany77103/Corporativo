<?php
session_start();

// Si se recibe una petición POST (autenticación)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (ob_get_length()) ob_clean();
    header('Content-Type: application/json; charset=utf-8');

    $host     = "localhost";
    $user     = "root";
    $password = "";
    $database = "proyecto";

    mysqli_report(MYSQLI_REPORT_OFF);
    $conn = @new mysqli($host, $user, $password, $database);

    if ($conn->connect_error) {
        echo json_encode(["status" => "error", "message" => "Error de conexión a MySQL."]);
        exit();
    }

    $conn->set_charset("utf8mb4");

    $usuario_input  = trim($_POST['usuario'] ?? '');
    $password_input = trim($_POST['password'] ?? '');

    if ($usuario_input === '' || $password_input === '') {
        echo json_encode(["status" => "error", "message" => "Por favor llena todos los campos."]);
        exit();
    }

    // Usuario + su rol real desde la tabla roles
    $sql = "SELECT u.id, u.usuario, u.nombre, u.password, r.nombre AS rol_nombre
            FROM usuarios u
            LEFT JOIN roles r ON u.rol_id = r.id
            WHERE LOWER(u.usuario) = LOWER(?)
            LIMIT 1";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        echo json_encode(["status" => "error", "message" => "Error SQL: " . $conn->error]);
        exit();
    }

    $stmt->bind_param("s", $usuario_input);
    $stmt->execute();
    $result = $stmt->get_result();
    $user   = $result->fetch_assoc();
    $stmt->close();

    $clave_valida = false;

    if ($user) {
        $guardada = (string) $user['password'];
        $info     = password_get_info($guardada);

        if ($info['algo'] !== null && $info['algo'] !== 0) {
            // Contraseña ya encriptada (bcrypt)
            $clave_valida = password_verify($password_input, $guardada);
        } else {
            // Contraseña antigua en texto plano: se acepta UNA vez y se convierte a hash
            $clave_valida = hash_equals($guardada, $password_input);

            if ($clave_valida) {
                // Solo migrar si la columna es lo bastante larga para guardar un hash (60 caracteres)
                $q = $conn->prepare("SELECT CHARACTER_MAXIMUM_LENGTH FROM information_schema.COLUMNS
                                     WHERE TABLE_SCHEMA = ? AND TABLE_NAME = 'usuarios' AND COLUMN_NAME = 'password'");
                $q->bind_param("s", $database);
                $q->execute();
                $col = $q->get_result()->fetch_assoc();
                $q->close();

                if ($col && (int) $col['CHARACTER_MAXIMUM_LENGTH'] >= 60) {
                    $nuevo_hash = password_hash($password_input, PASSWORD_BCRYPT);
                    $up = $conn->prepare("UPDATE usuarios SET password = ? WHERE id = ?");
                    $up->bind_param("si", $nuevo_hash, $user['id']);
                    $up->execute();
                    $up->close();
                }
            }
        }
    }

    if ($user && $clave_valida) {
        // Normalizar el rol que viene de la BD ('solo_ver' se trata como 'lector')
        $rol = strtolower(trim((string) $user['rol_nombre']));
        if ($rol === 'solo_ver') {
            $rol = 'lector';
        }

        $roles_validos = ['superadmin', 'admin', 'soporte', 'lector'];
        if (!in_array($rol, $roles_validos, true)) {
            echo json_encode(["status" => "error", "message" => "Tu usuario no tiene un rol válido asignado. Contacta al administrador."]);
            exit();
        }

        session_regenerate_id(true);

        $_SESSION['usuario_id'] = $user['id'];
        $_SESSION['id_usuario'] = $user['id'];
        $_SESSION['usuario']    = $user['usuario'];
        $_SESSION['nombre']     = $user['nombre'] ?? $user['usuario'];
        $_SESSION['rol']        = $rol;
        $_SESSION['rol_nombre'] = $rol;

        $redirect = ($rol === 'superadmin') ? "superadmin_panel.php" : "inicio.php";

        echo json_encode([
            "status"   => "success",
            "message"  => "Inicio de sesión correcto",
            "redirect" => $redirect
        ]);
        exit();
    }

    echo json_encode(["status" => "error", "message" => "Usuario o contraseña incorrectos."]);
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GSB - Control de Acceso</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        :root {
            --brand-primary: #00837B;
            --brand-dark: #002D5D;
            --app-bg: #F4F8F7;
            --card-bg: #FFFFFF;
            --border-color: #D1E5E3;
        }
        body {
            background-color: var(--app-bg);
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            color: var(--brand-dark);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            padding: 15px;
        }
        .login-card {
            background: var(--card-bg);
            border-radius: 20px;
            border: 1px solid var(--border-color);
            padding: 35px;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 15px 35px rgba(0, 45, 93, 0.08);
        }
        .btn-primary-custom {
            background-color: var(--brand-primary);
            color: white;
            border: none;
            border-radius: 10px;
            padding: 12px;
            font-weight: 600;
            width: 100%;
        }
        .btn-primary-custom:hover {
            background-color: var(--brand-dark);
            color: white;
        }
    </style>
</head>
<body>

<div class="login-card">
    <div class="text-center mb-4">
        <h4 class="fw-bold m-0" style="color: var(--brand-dark);">Iniciar Sesión</h4>
        <small class="text-muted">GSB Control de Inventarios</small>
    </div>

    <div id="alert-box" class="alert d-none" role="alert"></div>

    <form id="loginForm">
        <div class="mb-3">
            <label for="usuario" class="form-label fw-semibold small">Usuario</label>
            <input type="text" class="form-control" id="usuario" name="usuario" required autocomplete="username">
        </div>
        <div class="mb-4">
            <label for="password" class="form-label fw-semibold small">Contraseña</label>
            <input type="password" class="form-control" id="password" name="password" required autocomplete="current-password">
        </div>
        <button type="submit" class="btn btn-primary-custom">Entrar</button>
    </form>
</div>

<script>
document.getElementById('loginForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const alertBox = document.getElementById('alert-box');
    alertBox.classList.add('d-none');

    const formData = new FormData(this);

    try {
        const response = await fetch('login.php', {
            method: 'POST',
            body: formData
        });

        const textResponse = await response.text();
        const data = JSON.parse(textResponse);

        if (data.status === 'success') {
            alertBox.className = 'alert alert-success';
            alertBox.textContent = 'Acceso concedido. Redirigiendo...';
            alertBox.classList.remove('d-none');
            setTimeout(() => {
                window.location.href = data.redirect;
            }, 800);
        } else {
            alertBox.className = 'alert alert-danger';
            alertBox.textContent = data.message || 'Credenciales incorrectas';
            alertBox.classList.remove('d-none');
        }
    } catch (err) {
        alertBox.className = 'alert alert-danger';
        alertBox.textContent = 'Error al procesar la respuesta del servidor.';
        alertBox.classList.remove('d-none');
    }
});
</script>

</body>
</html>