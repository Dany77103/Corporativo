<?php
session_start();

// 1. Si ya existe sesión activa, redirigir según el rol
if (isset($_SESSION['usuario'])) {
    $rol = strtolower(trim($_SESSION['rol'] ?? ''));
    if ($rol === 'superadmin') {
        header("Location: superadmin_panel.php");
    } else {
        header("Location: inicio.php");
    }
    exit();
}

// 2. Procesar el inicio de sesión vía POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json; charset=utf-8');
    require_once 'conexion.php';

    $usuario  = trim($_POST['usuario'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($usuario) || empty($password)) {
        http_response_code(400);
        echo json_encode(["error" => "Usuario y contraseña requeridos"]);
        exit();
    }

    try {
        // Consultar con JOIN a roles
        $user = null;
        try {
            $stmt = $pdo->prepare("
                SELECT u.id, u.usuario, u.password, u.nombre, r.nombre AS rol_nombre 
                FROM usuarios u
                INNER JOIN roles r ON u.rol_id = r.id
                WHERE u.usuario = :usuario
            ");
            $stmt->execute(['usuario' => $usuario]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // Fallback en caso de que consulte la columna rol directa
            $stmt = $pdo->prepare("
                SELECT id, usuario, password, nombre, rol AS rol_nombre 
                FROM usuarios 
                WHERE usuario = :usuario
            ");
            $stmt->execute(['usuario' => $usuario]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
        }

        // Validación de credenciales
        if ($user && (password_verify($password, $user['password']) || $password === $user['password'])) {
            session_regenerate_id(true);

            // Obtener y limpiar la cadena del rol
            $rolLimpio = strtolower(trim($user['rol_nombre']));

            $_SESSION['usuario_id'] = $user['id'];
            $_SESSION['usuario']    = $user['usuario'];
            $_SESSION['nombre']     = $user['nombre'];
            $_SESSION['rol']        = $rolLimpio; // Guardamos en minúsculas

            // Redirección estricta por rol
            if ($rolLimpio === 'superadmin') {
                $redirectUrl = 'superadmin_panel.php';
            } else {
                $redirectUrl = 'inicio.php';
            }

            echo json_encode([
                "success"  => true,
                "mensaje"  => "Inicio de sesión exitoso",
                "rol"      => $rolLimpio,
                "redirect" => $redirectUrl
            ]);
            exit();
        } else {
            http_response_code(401);
            echo json_encode(["error" => "Credenciales incorrectas"]);
            exit();
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["error" => "Error en la consulta: " . $e->getMessage()]);
        exit();
    }
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
            border: 1px solid var(--border-color);
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 45, 93, 0.1);
            width: 100%;
            max-width: 420px;
            padding: 35px 30px;
        }
        .btn-gsb {
            background-color: var(--brand-primary);
            color: #ffffff;
            border-radius: 50px;
            font-weight: 600;
            padding: 12px;
            transition: all 0.3s ease;
        }
        .btn-gsb:hover {
            background-color: var(--brand-dark);
            color: #ffffff;
        }
    </style>
</head>
<body>

<div class="login-card">
    <div class="text-center mb-4">
        <h4 class="fw-bold mb-1" style="color: var(--brand-primary);">
            <i class="bi bi-shield-lock-fill me-2"></i>GSB Inventario
        </h4>
        <p class="text-muted small">Ingresa tus credenciales para acceder</p>
    </div>

    <div id="alertBox" class="alert alert-danger d-none" role="alert"></div>

    <form id="loginForm">
        <div class="mb-3">
            <label class="form-label small fw-bold">Usuario</label>
            <div class="input-group">
                <span class="input-group-text bg-light"><i class="bi bi-person"></i></span>
                <input type="text" id="usuario" name="usuario" class="form-control" placeholder="Ej. admin" required autocomplete="username">
            </div>
        </div>

        <div class="mb-4">
            <label class="form-label small fw-bold">Contraseña</label>
            <div class="input-group">
                <span class="input-group-text bg-light"><i class="bi bi-key"></i></span>
                <input type="password" id="password" name="password" class="form-control" placeholder="••••••••" required autocomplete="current-password">
            </div>
        </div>

        <button type="submit" id="btnSubmit" class="btn btn-gsb w-100">
            Iniciar Sesión
        </button>
    </form>
</div>

<script>
document.getElementById('loginForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const alertBox = document.getElementById('alertBox');
    const btnSubmit = document.getElementById('btnSubmit');
    
    alertBox.classList.add('d-none');
    btnSubmit.disabled = true;
    btnSubmit.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Ingresando...';

    const formData = new FormData(this);

    try {
        const response = await fetch('login.php', {
            method: 'POST',
            body: formData
        });

        const data = await response.json();

        if (response.ok && data.success) {
            window.location.href = data.redirect;
        } else {
            alertBox.textContent = data.error || 'Ocurrió un error al iniciar sesión';
            alertBox.classList.remove('d-none');
        }
    } catch (error) {
        alertBox.textContent = 'Error de conexión con el servidor.';
        alertBox.classList.remove('d-none');
    } finally {
        btnSubmit.disabled = false;
        btnSubmit.innerHTML = 'Iniciar Sesión';
    }
});
</script>
</body>
</html>