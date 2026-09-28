<?php
session_start();

// Validar que solo el superadmin pueda entrar
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'superadmin') {
    header('Location: index.php'); 
    exit();
}

// Credenciales directas a tu base de datos 'proyecto'
$host     = "localhost";
$user     = "root";
$password = "";
$database = "proyecto"; 

$conexion = new mysqli($host, $user, $password, $database);

if ($conexion->connect_error) {
    die("Error de conexión con la base de datos: " . $conexion->connect_error);
}

$mensaje = "";

// Procesar alta de nuevo usuario / administrador
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['crear_admin'])) {
    $nuevo_usuario  = trim($_POST['nuevo_usuario']);
    $nombre_completo = trim($_POST['nombre_completo']);
    $nueva_password = $_POST['nueva_password'];
    $rol_asignado   = $_POST['rol_asignado']; // 'admin' o 'superadmin'

    if (!empty($nuevo_usuario) && !empty($nueva_password)) {
        // Encriptar la contraseña con BCRYPT
        $hash_pass = password_hash($nueva_password, PASSWORD_BCRYPT);

        $stmt = $conexion->prepare("INSERT INTO usuarios (usuario, password, nombre, rol) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $nuevo_usuario, $hash_pass, $nombre_completo, $rol_asignado);

        if ($stmt->execute()) {
            $mensaje = "<div class='alert alert-success alert-dismissible fade show' role='alert'>
                            <i class='bi bi-check-circle-fill me-2'></i> Usuario <b>$nuevo_usuario</b> registrado con éxito como <b>$rol_asignado</b>.
                            <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
                        </div>";
        } else {
            $mensaje = "<div class='alert alert-danger alert-dismissible fade show' role='alert'>
                            <i class='bi bi-exclamation-triangle-fill me-2'></i> Error al crear usuario (es posible que el nombre de usuario ya exista).
                            <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
                        </div>";
        }
        $stmt->close();
    }
}

// Obtener la lista actualizada de usuarios
$usuarios = $conexion->query("SELECT id, usuario, nombre, rol FROM usuarios");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GSB CORP - Panel de Superadmin</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        :root {
            --brand-primary: #00837B;     
            --brand-dark: #002D5D;        
            --brand-green: #00B451;       
            --app-bg: #F4F8F7;
        }
        body {
            background-color: var(--app-bg);
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            color: var(--brand-dark);
        }
        .navbar-custom {
            background-color: var(--brand-dark);
        }
        .card-custom {
            border-radius: 16px;
            border: none;
            box-shadow: 0 8px 24px rgba(0, 45, 93, 0.08);
        }
        .btn-green {
            background-color: var(--brand-primary);
            color: white;
            border-radius: 10px;
            font-weight: 600;
        }
        .btn-green:hover {
            background-color: var(--brand-dark);
            color: white;
        }
    </style>
</head>
<body>

<!-- Navegación -->
<nav class="navbar navbar-expand-lg navbar-dark navbar-custom mb-4 shadow-sm">
    <div class="container">
        <span class="navbar-brand fw-bold">
            <i class="bi bi-shield-lock-fill text-warning me-2"></i>GSB CORP - Control de Superadmin
        </span>
        <div class="d-flex align-items-center gap-2">
            <span class="text-light small me-2"><i class="bi bi-person-circle"></i> <?= htmlspecialchars($_SESSION['usuario'] ?? 'Superadmin'); ?></span>
            <a href="index.php" class="btn btn-outline-light btn-sm"><i class="bi bi-box-seam me-1"></i> Ir al Inventario</a>
            <a href="logout.php" class="btn btn-danger btn-sm"><i class="bi bi-power"></i></a>
        </div>
    </div>
</nav>

<div class="container pb-5">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold m-0" style="color: var(--brand-dark);">Gestión Global de Usuarios y Administradores</h3>
            <p class="text-muted small mb-0">Asigne roles y cree credenciales de acceso para el personal.</p>
        </div>
    </div>

    <?= $mensaje; ?>

    <div class="row g-4">
        <!-- Formulario para registrar un nuevo Administrador -->
        <div class="col-lg-5">
            <div class="card card-custom p-4">
                <h5 class="fw-bold mb-3" style="color: var(--brand-primary);">
                    <i class="bi bi-person-plus-fill me-2"></i>Registrar Nuevo Usuario
                </h5>
                <form method="POST" autocomplete="off">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">ID / Nombre de Usuario</label>
                        <input type="text" name="nuevo_usuario" class="form-control" required placeholder="Ej: admin_sistemas">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Nombre Completo</label>
                        <input type="text" name="nombre_completo" class="form-control" required placeholder="Ej: Juan Pérez">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Contraseña</label>
                        <input type="password" name="nueva_password" class="form-control" required placeholder="Asigne una contraseña segura">
                    </div>
                    <div class="mb-4">
                        <label class="form-label small fw-bold">Rol en el Sistema</label>
                        <select name="rol_asignado" class="form-select">
                            <option value="admin">admin (Acceso a Inventario)</option>
                            <option value="superadmin">superadmin (Acceso Total + Crear Usuarios)</option>
                            <option value="solo_ver">solo_ver (Únicamente Lectura)</option>
                        </select>
                    </div>
                    <button type="submit" name="crear_admin" class="btn btn-green w-100 py-2 shadow-sm">
                        <i class="bi bi-save-fill me-1"></i> Guardar Usuario
                    </button>
                </form>
            </div>
        </div>

        <!-- Tabla de usuarios registrados -->
        <div class="col-lg-7">
            <div class="card card-custom p-4">
                <h5 class="fw-bold mb-3" style="color: var(--brand-primary);">
                    <i class="bi bi-people-fill me-2"></i>Usuarios Registrados en el Sistema
                </h5>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Usuario</th>
                                <th>Nombre</th>
                                <th>Rol</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($usuarios && $usuarios->num_rows > 0): ?>
                                <?php while ($user = $usuarios->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= $user['id']; ?></td>
                                        <td><b><?= htmlspecialchars($user['usuario']); ?></b></td>
                                        <td><?= htmlspecialchars($user['nombre'] ?? 'Sin nombre'); ?></td>
                                        <td>
                                            <?php if ($user['rol'] === 'superadmin'): ?>
                                                <span class="badge bg-danger"><i class="bi bi-shield-check"></i> superadmin</span>
                                            <?php elseif ($user['rol'] === 'admin'): ?>
                                                <span class="badge bg-primary"><i class="bi bi-person-gear"></i> admin</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary"><?= htmlspecialchars($user['rol']); ?></span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-3">No hay usuarios registrados.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap 5 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>