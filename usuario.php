<?php
session_start();
require_once 'config_roles.php';
require_once 'conexion.php'; // Define $pdo (PDO)

// 1. Solo usuarios con sesión y permiso para crear usuarios (superadmin y admin)
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}
$puedoModificar = tienePermiso('crear_usuarios');
requerirPermiso('crear_usuarios');

$esSuper = tienePermiso('gestionar_admins');   // Solo el superadmin
$miId    = (int)($_SESSION['usuario_id'] ?? 0);

// 2. Token CSRF para los formularios de este módulo
if (empty($_SESSION['csrf_usuarios'])) {
    $_SESSION['csrf_usuarios'] = bin2hex(random_bytes(32));
}
$csrf = $_SESSION['csrf_usuarios'];

// Guarda un mensaje, recarga la página y evita reenvíos del formulario
function flash($tipo, $msg) {
    $_SESSION['flash_usuarios'] = ['tipo' => $tipo, 'msg' => $msg];
    header("Location: usuario.php");
    exit();
}

// 3. Roles tomados de la BD (sin depender de números fijos)
//    'solo_ver' se trata como 'lector', igual que en login.php
$rolesPorId = [];
foreach ($pdo->query("SELECT id, nombre FROM roles ORDER BY id") as $r) {
    $n = strtolower(trim($r['nombre']));
    if ($n === 'solo_ver') {
        $n = 'lector';
    }
    $rolesPorId[(int)$r['id']] = $n;
}

// Roles que el usuario actual puede asignar:
//  - superadmin: todos (incluye asignar Administradores)
//  - admin: solo soporte y lector
$rolesBajos = ['soporte', 'lector', 'empleado'];
$asignables = [];
foreach ($rolesPorId as $idRol => $nombreRol) {
    if ($esSuper || in_array($nombreRol, $rolesBajos, true)) {
        $asignables[$idRol] = $nombreRol;
    }
}

// Política de contraseñas: 10+ caracteres para todos; 14+ y combinada para Admin y Superusuario
function validarFortalezaPassword($pass, $usuario, $nombreRol) {
    $privilegiado = in_array($nombreRol, ['superadmin', 'admin'], true);
    $min = $privilegiado ? 14 : 10;

    if (mb_strlen($pass) < $min) {
        return "La contraseña debe tener al menos $min caracteres" . ($privilegiado ? " (cuentas de administración)." : ".");
    }
    if ($privilegiado && !(preg_match('/[a-z]/', $pass) && preg_match('/[A-Z]/', $pass)
                           && preg_match('/\d/', $pass) && preg_match('/[^A-Za-z0-9]/', $pass))) {
        return "La contraseña de administración debe combinar mayúsculas, minúsculas, números y símbolos.";
    }
    if ($usuario !== '' && stripos($pass, $usuario) !== false) {
        return "La contraseña no puede contener el nombre de usuario.";
    }
    return null;
}

function puedeGestionarRol($nombreRol, $esSuper) {
    return $esSuper || in_array($nombreRol, ['soporte', 'lector', 'empleado'], true);
}

// 4. Procesar acciones (solo POST + token válido)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf']) || !hash_equals($csrf, (string)$_POST['csrf'])) {
        flash('danger', 'El formulario expiró. Intenta de nuevo.');
    }

    $accion = $_POST['accion'] ?? '';

    try {
        // ---------- CREAR / EDITAR ----------
        if ($accion === 'guardar') {
            $id      = (int)($_POST['id'] ?? 0);
            $nombre  = trim($_POST['nombre'] ?? '');
            $usuario = trim($_POST['usuario'] ?? '');
            $pass    = (string)($_POST['password'] ?? '');
            $rolId   = (int)($_POST['rol_id'] ?? 0);

            if ($nombre === '' || $usuario === '') {
                flash('danger', 'El nombre y el usuario son obligatorios.');
            }
            if (mb_strlen($nombre) > 150) {
                flash('danger', 'El nombre no puede pasar de 150 caracteres.');
            }
            if (!preg_match('/^[A-Za-z0-9._-]{3,100}$/', $usuario)) {
                flash('danger', 'El usuario debe tener de 3 a 100 caracteres: letras, números, punto, guion o guion bajo.');
            }
            if ($id === 0 && $pass === '') {
                flash('danger', 'La contraseña es obligatoria para usuarios nuevos.');
            }
            if ($pass !== '' && mb_strlen($pass) < 10) {
                flash('danger', 'La contraseña debe tener al menos 10 caracteres.');
            }

            if ($id > 0) {
                // Edición: validar que exista y que el actor pueda gestionarlo
                $st = $pdo->prepare("SELECT id, rol_id FROM usuarios WHERE id = :id");
                $st->execute([':id' => $id]);
                $actual = $st->fetch();

                if (!$actual) {
                    flash('danger', 'El usuario que intentas editar no existe.');
                }
                $rolActual = $rolesPorId[(int)$actual['rol_id']] ?? '';
                if (!puedeGestionarRol($rolActual, $esSuper)) {
                    flash('danger', 'No tienes permiso para modificar a ese usuario.');
                }
                // Nadie puede cambiarse su propio rol
                if ($id === $miId) {
                    $rolId = (int)$actual['rol_id'];
                }
            }

            if (!isset($asignables[$rolId])) {
                flash('danger', 'Rol no válido o no tienes permiso para asignarlo.');
            }

            if ($pass !== '') {
                $errPass = validarFortalezaPassword($pass, $usuario, $asignables[$rolId]);
                if ($errPass !== null) {
                    flash('danger', $errPass);
                }
            }

            // Usuario duplicado (sin distinguir mayúsculas, como hace el login)
            $st = $pdo->prepare("SELECT id FROM usuarios WHERE LOWER(usuario) = LOWER(:u) AND id <> :id");
            $st->execute([':u' => $usuario, ':id' => $id]);
            if ($st->fetch()) {
                flash('danger', 'Ese nombre de usuario ya está en uso.');
            }

            if ($id > 0) {
                if ($pass !== '') {
                    $st = $pdo->prepare("UPDATE usuarios SET nombre = :n, usuario = :u, password = :p, rol_id = :r WHERE id = :id");
                    $st->execute([
                        ':n' => $nombre, ':u' => $usuario,
                        ':p' => password_hash($pass, PASSWORD_BCRYPT),
                        ':r' => $rolId, ':id' => $id
                    ]);
                } else {
                    $st = $pdo->prepare("UPDATE usuarios SET nombre = :n, usuario = :u, rol_id = :r WHERE id = :id");
                    $st->execute([':n' => $nombre, ':u' => $usuario, ':r' => $rolId, ':id' => $id]);
                }
                flash('success', 'Usuario actualizado correctamente.');
            } else {
                $st = $pdo->prepare("INSERT INTO usuarios (nombre, usuario, password, rol_id) VALUES (:n, :u, :p, :r)");
                $st->execute([
                    ':n' => $nombre, ':u' => $usuario,
                    ':p' => password_hash($pass, PASSWORD_BCRYPT),
                    ':r' => $rolId
                ]);
                flash('success', 'Usuario creado correctamente.');
            }
        }

        // ---------- ELIMINAR (solo superadmin) ----------
        elseif ($accion === 'eliminar') {
            if (!$esSuper) {
                flash('danger', 'Solo el Superusuario puede eliminar usuarios.');
            }
            $id = (int)($_POST['id'] ?? 0);
            if ($id === $miId) {
                flash('danger', 'No puedes eliminar tu propia cuenta.');
            }
            // Liberar los equipos que tenía asignados esa cuenta
            $pdo->prepare("UPDATE equipos SET cuenta_id = NULL WHERE cuenta_id = :id")->execute([':id' => $id]);
            $st = $pdo->prepare("DELETE FROM usuarios WHERE id = :id");
            $st->execute([':id' => $id]);
            if ($st->rowCount() > 0) {
                flash('success', 'Usuario eliminado correctamente.');
            }
            flash('danger', 'No se encontró el usuario a eliminar.');
        }
    } catch (PDOException $e) {
        flash('danger', 'No se pudo completar la operación en la base de datos.');
    }

    flash('danger', 'Acción no reconocida.');
}

// 5. Datos para mostrar
$flash = $_SESSION['flash_usuarios'] ?? null;
unset($_SESSION['flash_usuarios']);

$usuarios = $pdo->query("SELECT id, nombre, usuario, rol_id FROM usuarios ORDER BY id ASC")->fetchAll();

$etiquetas = [
    'superadmin' => 'Superusuario (control total)',
    'admin'      => 'Administrador (editar, ver, borrar y crear usuarios)',
    'soporte'    => 'Soporte (editar, ver y borrar)',
    'lector'     => 'Solo ver (lectura)',
    'empleado'   => 'Empleado (solo ve sus equipos asignados)',
];
$badges = [
    'superadmin' => ['danger', 'SUPERADMIN'],
    'admin'      => ['primary', 'ADMIN'],
    'soporte'    => ['info text-dark', 'SOPORTE'],
    'lector'     => ['secondary', 'SOLO VER'],
    'empleado'   => ['success', 'EMPLEADO'],
];
$rolPorDefecto = array_search('lector', $asignables, true);
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
            color: var(--brand-dark);
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
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <h3 class="fw-bold m-0" style="color: var(--brand-primary);">
                <i class="bi bi-people-fill me-2"></i>Administración de Usuarios
            </h3>
            <small class="text-muted">
                Sesión: <?php echo htmlspecialchars($_SESSION['nombre'] ?? $_SESSION['usuario']); ?>
                (<?php echo htmlspecialchars($_SESSION['rol']); ?>)
            </small>
        </div>
        <div class="d-flex gap-2">
            <a href="<?php echo $esSuper ? 'superadmin_panel.php' : 'inicio.php'; ?>" class="btn btn-outline-secondary rounded-pill">
                <i class="bi bi-speedometer2 me-1"></i> Panel
            </a>
        </div>
    </div>

    <?php if ($flash): ?>
        <div class="alert alert-<?php echo $flash['tipo'] === 'success' ? 'success' : 'danger'; ?> alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
            <i class="bi bi-<?php echo $flash['tipo'] === 'success' ? 'check-circle-fill' : 'exclamation-triangle-fill'; ?> me-2"></i>
            <?php echo htmlspecialchars($flash['msg']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="row g-4">
        <!-- Formulario Crear / Editar -->
        <?php if ($puedoModificar): ?>
        <div class="col-lg-4">
            <div class="card card-custom shadow-sm">
                <h6 class="fw-bold mb-3" style="color: var(--brand-primary);" id="formTitle">
                    <i class="bi bi-person-plus-fill me-1"></i> Nuevo Usuario
                </h6>
                <form action="usuario.php" method="POST" id="formUsuario" autocomplete="off">
                    <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($csrf); ?>">
                    <input type="hidden" name="accion" value="guardar">
                    <input type="hidden" id="userId" name="id" value="0">

                    <div class="mb-3">
                        <label class="form-label small text-muted fw-bold" for="userUsername">Usuario</label>
                        <input type="text" name="usuario" id="userUsername" class="form-control"
                               placeholder="Ej. jperez" required minlength="3" maxlength="100"
                               pattern="[A-Za-z0-9._\-]+" autocomplete="off">
                    </div>

                    <div class="mb-3">
                        <label class="form-label small text-muted fw-bold" for="userName">Nombre completo</label>
                        <input type="text" name="nombre" id="userName" class="form-control"
                               placeholder="Ej. Juan Pérez" required maxlength="150" autocomplete="off">
                    </div>

                    <div class="mb-3">
                        <label class="form-label small text-muted fw-bold" for="userPass">Contraseña</label>
                        <input type="password" name="password" id="userPass" class="form-control"
                               placeholder="Mínimo 10 caracteres" minlength="10" required autocomplete="new-password">
                        <div class="form-text" id="passHelp">Obligatoria para usuarios nuevos. Mínimo 10 caracteres; para Admin y Superusuario, 14 con mayúsculas, minúsculas, números y símbolos.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small text-muted fw-bold" for="userRol">Rol</label>
                        <select name="rol_id" id="userRol" class="form-select" required>
                            <?php foreach ($asignables as $idRol => $nombreRol): ?>
                                <option value="<?php echo $idRol; ?>" <?php echo ($idRol === $rolPorDefecto) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($etiquetas[$nombreRol] ?? $nombreRol); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text d-none" id="rolHelp">No puedes cambiar tu propio rol.</div>
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
        <?php endif; ?>

        <!-- Tabla de usuarios -->
        <div class="col-lg-<?php echo $puedoModificar ? '8' : '12'; ?>">
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
                                <?php if ($puedoModificar): ?>
                                    <th class="text-center">Acciones</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (count($usuarios) > 0): ?>
                            <?php foreach ($usuarios as $u):
                                $rolNombre = $rolesPorId[(int)$u['rol_id']] ?? 'sin rol';
                                $badge     = $badges[$rolNombre] ?? ['dark', strtoupper($rolNombre)];
                                $esMio     = ((int)$u['id'] === $miId);
                                $puedeEditar = puedeGestionarRol($rolNombre, $esSuper);
                            ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($u['nombre'] ?? ''); ?></strong>
                                        <?php if ($esMio): ?><span class="badge bg-light text-dark border ms-1">tú</span><?php endif; ?>
                                    </td>
                                    <td><code><?php echo htmlspecialchars($u['usuario']); ?></code></td>
                                    <td><span class="badge bg-<?php echo $badge[0]; ?>"><?php echo htmlspecialchars($badge[1]); ?></span></td>
                                    <?php if ($puedoModificar): ?>
                                    <td class="text-center">
                                        <?php if ($puedeEditar): ?>
                                            <button type="button"
                                                    class="btn btn-sm btn-outline-primary border-0 me-1 btn-editar-usuario"
                                                    onclick="abrirModalEditar(<?php echo (int)$u['id']; ?>)"
                                                    data-id="<?php echo (int)$u['id']; ?>"
                                                    data-usuario="<?php echo htmlspecialchars($u['usuario'], ENT_QUOTES); ?>"
                                                    data-nombre="<?php echo htmlspecialchars($u['nombre'] ?? '', ENT_QUOTES); ?>"
                                                    data-rol-id="<?php echo (int)$u['rol_id']; ?>"
                                                    data-es-mio="<?php echo $esMio ? '1' : '0'; ?>"
                                                    title="Editar usuario">
                                                <i class="bi bi-pencil-fill"></i>
                                            </button>
                                        <?php else: ?>
                                            <span class="text-muted small" title="Solo un Superusuario puede modificar este usuario"><i class="bi bi-lock-fill"></i></span>
                                        <?php endif; ?>

                                        <?php if ($esSuper && !$esMio): ?>
                                              <form action="usuario.php" method="POST" class="d-inline"
                                                  data-eliminar-usuario="<?php echo (int)$u['id']; ?>">
                                                <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($csrf); ?>">
                                                <input type="hidden" name="accion" value="eliminar">
                                                <input type="hidden" name="id" value="<?php echo (int)$u['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger border-0"
                                                    onclick="return eliminarUsuario(<?php echo (int)$u['id']; ?>)"
                                                    title="Eliminar usuario">
                                                    <i class="bi bi-trash-fill"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </td>
                                    <?php endif; ?>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="<?php echo $puedoModificar ? '4' : '3'; ?>" class="text-center text-muted py-3">No hay usuarios registrados.</td></tr>
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
    const PUEDO_MODIFICAR = <?php echo json_encode($puedoModificar); ?>;
    const ROL_POR_DEFECTO = "<?php echo (int)$rolPorDefecto; ?>";
</script>
<script src="usuario.js"></script>
</body>
</html>