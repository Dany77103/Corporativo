<?php
session_start();
require_once 'config_roles.php';

// 1. Verificar autenticación y permiso base
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

verificarPermiso(['superadmin', 'admin', 'soporte']);
requerirPermiso('crear_usuarios');

// 2. Conexión a Base de Datos
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
    die("Error de conexión a la base de datos: " . $e->getMessage());
}

$mensaje = "";
$error   = "";

// 3. Lógica para Crear / Editar Usuario
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['accion_guardar_usuario'])) {
    $id      = intval($_POST['id'] ?? 0);
    $nombre  = trim($_POST['nombre'] ?? '');
    $usuario = trim($_POST['usuario'] ?? '');
    $pass    = trim($_POST['password'] ?? '');
    $rol_id  = intval($_POST['rol_id'] ?? 4);

    if (empty($nombre) || empty($usuario)) {
        $error = "El Nombre y el Usuario son campos obligatorios.";
    } else {
        // Validar si intenta asignar Superadmin o Admin sin tener permiso
        if (($rol_id === 1 || $rol_id === 2) && !tienePermiso('gestionar_admins')) {
            $error = "No tienes privilegios para asignar roles de Administrador o Superusuario.";
        } else {
            try {
                if ($id > 0) {
                    // --- MODO EDITAR ---
                    if (!empty($pass)) {
                        // Si ingresó nueva contraseña, se actualiza el hash
                        $passHash = password_hash($pass, PASSWORD_BCRYPT);
                        $stmt = $pdo->prepare("UPDATE usuarios SET nombre = :nombre, usuario = :usuario, password = :pass, rol_id = :rol_id WHERE id = :id");
                        $stmt->execute([
                            ':nombre'  => $nombre,
                            ':usuario' => $usuario,
                            ':pass'    => $passHash,
                            ':rol_id'  => $rol_id,
                            ':id'      => $id
                        ]);
                    } else {
                        // Si dejó la contraseña en blanco, se conserva la actual
                        $stmt = $pdo->prepare("UPDATE usuarios SET nombre = :nombre, usuario = :usuario, rol_id = :rol_id WHERE id = :id");
                        $stmt->execute([
                            ':nombre'  => $nombre,
                            ':usuario' => $usuario,
                            ':rol_id'  => $rol_id,
                            ':id'      => $id
                        ]);
                    }
                    $mensaje = "Usuario actualizado correctamente.";
                } else {
                    // --- MODO CREAR ---
                    if (empty($pass)) {
                        $error = "La contraseña es obligatoria para nuevos usuarios.";
                    } else {
                        $passHash = password_hash($pass, PASSWORD_BCRYPT);
                        $stmt = $pdo->prepare("INSERT INTO usuarios (nombre, usuario, password, rol_id) VALUES (:nombre, :usuario, :pass, :rol_id)");
                        $stmt->execute([
                            ':nombre'  => $nombre,
                            ':usuario' => $usuario,
                            ':pass'    => $passHash,
                            ':rol_id'  => $rol_id
                        ]);
                        $mensaje = "Usuario registrado exitosamente.";
                    }
                }
            } catch (PDOException $e) {
                if ($e->getCode() == 23000) {
                    $error = "El nombre de usuario ya se encuentra en uso.";
                } else {
                    $error = "Error al procesar la solicitud: " . $e->getMessage();
                }
            }
        }
    }
}

// 4. Lógica para Eliminar Usuario
if (isset($_GET['eliminar'])) {
    $id_eliminar = intval($_GET['eliminar']);
    
    if (!tienePermiso('gestionar_admins')) {
        $error = "No tienes permiso para eliminar usuarios.";
    } else {
        try {
            $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id = :id AND rol_id != 1");
            $stmt->execute([':id' => $id_eliminar]);

            if ($stmt->rowCount() > 0) {
                $mensaje = "Usuario eliminado correctamente.";
            } else {
                $error = "No se pudo eliminar el usuario seleccionado o es un Superusuario.";
            }
        } catch (PDOException $e) {
            $error = "Error al intentar eliminar el usuario.";
        }
    }
}

// 5. Consultar lista de usuarios
$stmtUsuarios = $pdo->query("
    SELECT u.id, u.nombre, u.usuario, r.nombre AS rol_nombre, r.id AS rol_id
    FROM usuarios u
    INNER JOIN roles r ON u.rol_id = r.id
    ORDER BY u.id DESC
");
$usuarios = $stmtUsuarios->fetchAll();
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
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            padding: 20px; 
        }
        .card-custom { 
            border-radius: 16px; 
            border: 1px solid var(--brand-border); 
            background: #ffffff; 
            padding: 24px; 
        }
        .btn-gsb { 
            background-color: var(--brand-primary); 
            color: #ffffff; 
            border-radius: 50px; 
            transition: all 0.3s ease;
        }
        .btn-gsb:hover { 
            background-color: var(--brand-dark); 
            color: #ffffff; 
        }
    </style>
</head>
<body>

<div class="container my-4" style="max-width: 1100px;">
    <!-- Encabezado -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold m-0" style="color: var(--brand-primary);">
            <i class="bi bi-people-fill me-2"></i>Administración de Usuarios
        </h3>
        <a href="index.php" class="btn btn-outline-secondary rounded-pill">
            <i class="bi bi-arrow-left me-1"></i> Volver al Inventario
        </a>
    </div>

    <!-- Mensajes Feedback -->
    <?php if (!empty($mensaje)): ?>
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i><?php echo htmlspecialchars($mensaje); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo htmlspecialchars($error); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="row g-4">
        <!-- Formulario Agregar / Editar Usuario -->
        <div class="col-lg-4">
            <div class="card card-custom shadow-sm">
                <h6 class="fw-bold mb-3" style="color: var(--brand-primary);" id="formTitle">
                    <i class="bi bi-person-plus-fill me-1"></i> Gestionar Usuario
                </h6>
                <form action="usuarios.php" method="POST" id="formUsuario">
                    <input type="hidden" name="accion_guardar_usuario" value="1">
                    <input type="hidden" id="userId" name="id" value="0">
                    
                    <div class="mb-3">
                        <label class="form-label small text-muted fw-bold">Usuario (NOT NULL)</label>
                        <input type="text" name="usuario" id="userUsername" class="form-control" placeholder="Ej. jperez" required maxlength="100">
                    </div>

                    <div class="mb-3">
                        <label class="form-label small text-muted fw-bold">Nombre Completo</label>
                        <input type="text" name="nombre" id="userName" class="form-control" placeholder="Ej. Juan Pérez" required maxlength="150">
                    </div>

                    <div class="mb-3">
                        <label class="form-label small text-muted fw-bold">Contraseña</label>
                        <input type="password" name="password" id="userPass" class="form-control" placeholder="Dejar en blanco para no cambiar">
                    </div>

                    <div class="mb-3">
                        <label class="form-label small text-muted fw-bold">Rol</label>
                        <select name="rol_id" id="userRol" class="form-select" required>
                            <?php if (tienePermiso('gestionar_admins')): ?>
                                <option value="1">Superusuario (Control Total)</option>
                            <?php endif; ?>
                            <option value="2">Administrador</option>
                            <option value="3">Soporte (Editar, Ver, Borrar)</option>
                            <option value="4" selected>Solo Ver (Lectura)</option>
                        </select>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-gsb fw-bold py-2 mt-2">
                            <i class="bi bi-save me-1"></i> Guardar Usuario
                        </button>
                        <button type="button" id="btnCancelar" class="btn btn-sm btn-light border d-none" onclick="limpiarFormulario()">
                            Cancelar Edición
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tabla de Usuarios Registrados -->
        <div class="col-lg-8">
            <div class="card card-custom shadow-sm">
                <h6 class="fw-bold mb-3" style="color: var(--brand-primary);">
                    <i class="bi bi-list-ul me-1"></i> Usuarios Registrados
                </h6>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Nombre</th>
                                <th>Usuario</th>
                                <th>Rol</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($usuarios) > 0): ?>
                                <?php foreach ($usuarios as $u): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($u['nombre']); ?></strong></td>
                                        <td><code><?php echo htmlspecialchars($u['usuario']); ?></code></td>
                                        <td>
                                            <?php
                                                switch($u['rol_id']) {
                                                    case 1:
                                                        echo '<span class="badge bg-danger">SUPERADMIN</span>';
                                                        break;
                                                    case 2:
                                                        echo '<span class="badge bg-primary">ADMIN</span>';
                                                        break;
                                                    case 3:
                                                        echo '<span class="badge bg-info text-dark">SOPORTE</span>';
                                                        break;
                                                    default:
                                                        echo '<span class="badge bg-secondary">SOLO VER</span>';
                                                        break;
                                                }
                                            ?>
                                        </td>
                                        <td class="text-center">
                                            <!-- Botón Cargar Datos en Formulario para Editar -->
                                            <button type="button" 
                                                    class="btn btn-sm btn-outline-primary border-0 me-1"
                                                    onclick="cargarUsuario(<?php echo htmlspecialchars(json_encode($u)); ?>)"
                                                    title="Editar usuario">
                                                <i class="bi bi-pencil-fill"></i>
                                            </button>

                                            <?php if ($u['rol_id'] != 1 && tienePermiso('gestionar_admins')): ?>
                                                <a href="usuarios.php?eliminar=<?php echo $u['id']; ?>" 
                                                   class="btn btn-sm btn-outline-danger border-0" 
                                                   title="Eliminar usuario"
                                                   onclick="return confirm('¿Seguro que deseas eliminar este usuario?')">
                                                    <i class="bi bi-trash-fill"></i>
                                                </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function cargarUsuario(data) {
        document.getElementById('userId').value = data.id;
        document.getElementById('userUsername').value = data.usuario;
        document.getElementById('userName').value = data.nombre;
        document.getElementById('userRol').value = data.rol_id;
        document.getElementById('userPass').value = ''; // Limpiar campo contraseña
        
        document.getElementById('formTitle').innerHTML = '<i class="bi bi-pencil-square me-1"></i> Editar Usuario #' + data.id;
        document.getElementById('btnCancelar').classList.remove('d-none');
    }

    function limpiarFormulario() {
        document.getElementById('userId').value = '0';
        document.getElementById('formUsuario').reset();
        document.getElementById('formTitle').innerHTML = '<i class="bi bi-person-plus-fill me-1"></i> Gestionar Usuario';
        document.getElementById('btnCancelar').classList.add('d-none');
    }
</script>
</body>
</html>