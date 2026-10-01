<?php
require_once 'db.php'; // Tu archivo de conexión PDO o mysqli
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = trim($_POST['usuario'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($usuario) || empty($password)) {
        echo json_encode(["error" => "Usuario y contraseña requeridos"]);
        exit();
    }

    // Consulta relacionando la tabla usuarios con roles
    $stmt = $pdo->prepare("
        SELECT u.id, u.usuario, u.password, u.nombre, r.nombre AS rol_nombre 
        FROM usuarios u
        INNER JOIN roles r ON u.rol_id = r.id
        WHERE u.usuario = :usuario
    ");
    $stmt->execute(['usuario' => $usuario]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
    // Regenerar ID de sesión por seguridad contra Session Fixation
    session_regenerate_id(true);

    // Variables de sesión
    $_SESSION['usuario_id'] = $user['id'];
    $_SESSION['usuario']    = $user['nombre']; // Usado en index.php
    $_SESSION['nombre']     = $user['nombre'];
    
    // Asignación de Rol
    // Mantenemos ambos para asegurar compatibilidad total con config_roles.php
    $_SESSION['rol']        = $user['rol_nombre']; 
    $_SESSION['rol_nombre'] = $user['rol_nombre'];

    echo json_encode([
        "success" => true,
        "mensaje" => "Inicio de sesión exitoso",
        "rol"     => $user['rol_nombre']
    ]);
} else {
    http_response_code(401);
    echo json_encode(["error" => "Credenciales incorrectas"]);
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
            --brand-green: #00B451;       
            --brand-mint: #5CCA8E;        
            --app-bg: #F4F8F7;
            --card-bg: #FFFFFF;
            --text-dark: #002D5D;
            --text-muted: #6B7C93;
            --border-color: #D1E5E3;
            --shadow-light: 0 15px 35px rgba(0, 45, 93, 0.1);
        }

        body {
            background-color: var(--app-bg);
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            color: var(--text-dark);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 15px;
            margin: 0;
            overflow-x: hidden;
        }

        .login-card {
            background: var(--card-bg);
            border-radius: 24px;
            border: 2px solid var(--brand-mint);
            box-shadow: var(--shadow-light);
            width: 90%;
            max-width: 420px;
            padding: 2.5rem 2rem;
            transition: all 0.3s ease;
        }

        .green-icon-badge {
            width: 56px;
            height: 56px;
            background-color: var(--brand-primary);
            color: white;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 26px;
            box-shadow: 0 4px 12px rgba(0, 131, 123, 0.3);
        }

        .btn-green {
            background-color: var(--brand-primary);
            color: white;
            border: none;
            border-radius: 12px;
            padding: 12px 24px;
            font-weight: 600;
            transition: all 0.2s ease-in-out;
            box-shadow: 0 4px 12px rgba(0, 131, 123, 0.3);
        }

        .btn-green:hover, .btn-green:focus {
            background-color: var(--brand-dark);
            color: white;
        }

        .form-control {
            border-radius: 12px;
            border: 1px solid var(--border-color);
            background-color: #F8FCFA;
            padding: 12px 15px;
            color: var(--brand-dark);
        }

        .form-control:focus {
            background-color: #FFFFFF;
            border-color: var(--brand-primary);
            box-shadow: 0 0 0 0.25rem rgba(0, 131, 123, 0.2);
        }

        .form-floating label {
            padding-left: 15px;
            color: var(--text-muted);
        }
    </style>
</head>
<body>

<div class="login-card text-center">
    
    <div class="mb-4">
        <div class="green-icon-badge mb-3">
            <i class="bi bi-shield-lock-fill"></i>
        </div>
        <h3 class="fw-bold m-0" style="color: var(--brand-dark);">GSB CORP</h3>
        <p class="small fw-bold text-uppercase mt-1 mb-0" style="color: var(--brand-green); letter-spacing: 0.5px;">
            <i class="bi bi-exclamation-octagon"></i> Acceso Sistema
        </p>
    </div>

    <?php if (!empty($error_message)): ?>
        <div class="alert alert-danger d-flex align-items-center justify-content-center py-2 small rounded-3 mb-4" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2 fs-6"></i>
            <div><?php echo $error_message; ?></div>
        </div>
    <?php endif; ?>

    <form action="login.php" method="POST" autocomplete="off">
        <div class="form-floating mb-3 text-start">
            <input type="text" class="form-control" id="usuario" name="usuario" placeholder="Usuario" required>
            <label for="usuario"><i class="bi bi-person-badge-fill text-muted me-1"></i> ID de Usuario</label>
        </div>
        
        <div class="form-floating mb-4 text-start">
            <input type="password" class="form-control" id="password" name="password" placeholder="Contraseña" required>
            <label for="password"><i class="bi bi-key-fill text-muted me-1"></i> Contraseña</label>
        </div>

        <button type="submit" class="btn btn-green w-100 py-2.5 shadow-sm">
            <i class="bi bi-box-arrow-in-right me-1"></i> Autenticar Ingreso
        </button>
    </form>

    <div class="text-center mt-4 border-top pt-3">
        <span class="text-muted" style="font-size: 11px;">Este intento de acceso quedará registrado en las bitácoras del sistema GSB.</span>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>