<?php
session_start();
require_once 'config_roles.php';

// Verificar login y acceso al módulo
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

requerirPermiso('crear_usuarios');

// Conexión a BD
$host     = "localhost";
$user     = "root";
$password = "";
$database = "proyecto";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$database;charset=utf8mb4", $user, $password, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}

$mensaje = "";
$error   = "";

// Lógica para Crear Usuario
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['accion_crear_usuario'])) {
    $nombre  = trim($_POST['nombre'] ?? '');
    $usuario = trim($_POST['usuario'] ?? '');
    $pass    = trim($_POST['password'] ?? '');
    $rol     = trim($_POST['rol'] ?? 'lector');

    if (empty($_POST['usuario']) || empty($_POST['password']) || empty($_POST['nombre'])) {
    die("Error: Ningún campo puede quedar vacío.");

    } else {
        // Validar si el usuario actual puede asignar el rol solicitado
        if (($rol === ROL_SUPERADMIN || $rol === ROL_ADMIN) && !tienePermiso('gestionar_admins')) {
            $error = "No tienes privilegios para asignar roles de Administrador o Superadmin.";
        } else {
            $passHash = password_hash($pass, PASSWORD_BCRYPT);
            $stmt     = $pdo->prepare("INSERT INTO usuarios (nombre, usuario, password, rol) VALUES (:nombre, :usuario, :pass, :rol)");
            try {
                $stmt->execute([
                    ':nombre'  => $nombre,
                    ':usuario' => $usuario,
                    ':pass'    => $passHash,
                    ':rol'     => $rol
                ]);
                $mensaje = "Usuario creado exitosamente.";
            } catch (PDOException $e) {
                $error = "El nombre de usuario ya se encuentra registrado.";
            }
        }
    }
}

// Lógica para Eliminar Usuario
if (isset($_GET['eliminar'])) {
    $id_eliminar = intval($_GET['eliminar']);
    if (!tienePermiso('gestionar_admins')) {
        $error = "No tienes permiso para eliminar usuarios.";
    } else {
        $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id = :id AND rol != 'superadmin'");
        $stmt->execute([':id' => $id_eliminar]);
        $mensaje = "Usuario eliminado correctamente.";
    }
}

$usuarios = $pdo->query("SELECT id, nombre, usuario, rol, creado_en FROM usuarios ORDER BY id DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GSB - Gestión de Usuarios y Roles</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        :root {
            --brand-primary: #00837B;
            --brand-dark: #002D5D;
            --brand-bg: #F4F8F7;
            --brand-border: #D1E5E3;
        }
        body { 
            background-color: var(--brand-bg); 
            font-family: 'Segoe UI', sans-serif; 
            padding: 20px; 
        }
        .card-custom { 
            border-radius: 20px; 
            border: 1px solid var(--brand-border); 
            background: white; 
            padding: 20px; 
        }
        .btn-gsb { 
            background-color: var(--brand-primary); 
            color: white; 
            border-radius: 50px; 
        }
        .btn-gsb:hover { 
            background-color: var(--brand-dark); 
            color: white; 
        }
    </style>
</head>
<body>

<div class="container max-width-1000">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold" style="color: var(--brand-primary);">
            <i class="bi bi-people-fill me-2"></i>Administración de Usuarios
        </h3>
        <a href="index.php" class="btn btn-outline-secondary rounded-pill">
            <i class="bi bi-arrow-left me-1"></i> Volver al Inventario
        </a>
    </div>

    <?php if (!empty($mensaje)): ?>
        <div class="alert alert-success border-0 shadow-sm mb-3">
            <?php echo htmlspecialchars($mensaje); ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger border-0 shadow-sm mb-3">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <div class="row">
        <!-- Formulario Registro -->
        <div class="col-md-4 mb-4">
            <div class="card card-custom shadow-sm">
                <h6 class="fw-bold mb-3" style="color: var(--brand-primary);">
                    <i class="bi bi-person-plus-fill me-1"></i> Nuevo Usuario
                </h6>
                <form action="usuarios.php" method="POST">
                    <input type="hidden" name="accion_crear_usuario" value="1">
                    
                    <div class="mb-3">
                        <label class="form-label small text-muted fw-bold">Nombre Completo</label>
                        <input type="text" name="nombre" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small text-muted fw-bold">Usuario</label>
                        <input type="text" name="usuario" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small text-muted fw-bold">Contraseña</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small text-muted fw-bold">Rol</label>
                        <select name="rol" class="form-select" required>
                            <option value="lector">Solo Ver (Lector)</option>
                            <option value="soporte">Soporte</option>
                            <option value="admin">Administrador</option>
                            <?php if (tienePermiso('gestionar_admins')): ?>
                                <option value="superadmin">Superusuario</option>
                            <?php endif; ?>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-gsb w-100 fw-bold mt-2">
                        <i class="bi bi-save me-1"></i> Guardar Usuario
                    </button>
                </form>
            </div>
        </div>

        <!-- Tabla Usuarios -->
        <div class="col-md-8">
            <div class="card card-custom shadow-sm">
                <h6 class="fw-bold mb-3" style="color: var(--brand-primary);">
                    <i class="bi bi-list-ul me-1"></i> Usuarios Registrados
                </h6>
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Nombre</th>
                                <th>Usuario</th>
                                <th>Rol</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($usuarios as $u): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($u['nombre']); ?></strong></td>
                                    <td><code><?php echo htmlspecialchars($u['usuario']); ?></code></td>
                                    <td><span class="badge bg-secondary"><?php echo strtoupper($u['rol']); ?></span></td>
                                    <td class="text-center">
                                        <?php if ($u['rol'] !== 'superadmin' && tienePermiso('gestionar_admins')): ?>
                                            <a href="usuarios.php?eliminar=<?php echo $u['id']; ?>" class="btn btn-sm btn-outline-danger border-0" onclick="return confirm('¿Eliminar usuario?')">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted small">N/A</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>