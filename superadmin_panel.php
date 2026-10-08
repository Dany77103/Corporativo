<?php
session_start();
require_once 'config_roles.php';
require_once 'conexion.php'; // Define $pdo (PDO) y las constantes DB_*

// 1. Solo el Superusuario puede ver este panel
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}
if (!tienePermiso('gestionar_admins')) {
    header("Location: inicio.php");
    exit();
}

// Devuelve un único valor de una consulta; si falla (p. ej. tabla inexistente) devuelve $porDefecto
function valorUnico(PDO $pdo, $sql, array $params = [], $porDefecto = null) {
    try {
        $st = $pdo->prepare($sql);
        $st->execute($params);
        $v = $st->fetchColumn();
        return ($v === false) ? $porDefecto : $v;
    } catch (PDOException $e) {
        return $porDefecto;
    }
}

$usuario_actual = $_SESSION['usuario'];
$nombre_actual  = $_SESSION['nombre'] ?? $usuario_actual;

$etiquetas = [
    'superadmin' => 'Superusuario',
    'admin'      => 'Administrador',
    'soporte'    => 'Soporte',
    'lector'     => 'Solo ver',
    'empleado'   => 'Empleado',
];
$badges = [
    'superadmin' => ['danger', 'SUPERADMIN'],
    'admin'      => ['primary', 'ADMIN'],
    'soporte'    => ['info text-dark', 'SOPORTE'],
    'lector'     => ['secondary', 'SOLO VER'],
    'empleado'   => ['success', 'EMPLEADO'],
];

// 2. Datos del panel
$total_usuarios = (int) valorUnico($pdo, "SELECT COUNT(*) FROM usuarios", [], 0);
$total_equipos  = (int) valorUnico($pdo, "SELECT COUNT(*) FROM equipos", [], 0);
$sin_asignar    = (int) valorUnico($pdo, "SELECT COUNT(*) FROM equipos WHERE cuenta_id IS NULL", [], 0);

// Usuarios por rol
$porRol = [];
try {
    $consulta = $pdo->query("SELECT r.id, r.nombre, COUNT(u.id) AS total
                             FROM roles r LEFT JOIN usuarios u ON u.rol_id = r.id
                             GROUP BY r.id, r.nombre ORDER BY r.id");
    foreach ($consulta as $fila) {
        $clave = strtolower(trim($fila['nombre']));
        if ($clave === 'solo_ver') {
            $clave = 'lector';
        }
        $porRol[] = ['clave' => $clave, 'total' => (int)$fila['total']];
    }
} catch (PDOException $e) {
    $porRol = [];
}

// Usuarios más recientes
$recientes = [];
try {
    $recientes = $pdo->query("SELECT u.nombre, u.usuario, r.nombre AS rol
                              FROM usuarios u LEFT JOIN roles r ON r.id = u.rol_id
                              ORDER BY u.id DESC LIMIT 6")->fetchAll();
} catch (PDOException $e) {
    $recientes = [];
}

// Intentos de acceso fallidos (tabla login_intentos; puede no existir todavía)
$intentos_24h   = valorUnico($pdo, "SELECT COUNT(*) FROM login_intentos WHERE creado > (NOW() - INTERVAL 1 DAY)", [], null);
$tabla_intentos = ($intentos_24h !== null);
$intentos_recientes = [];
if ($tabla_intentos) {
    try {
        $intentos_recientes = $pdo->query("SELECT usuario, ip, creado FROM login_intentos ORDER BY creado DESC LIMIT 6")->fetchAll();
    } catch (PDOException $e) {
        $intentos_recientes = [];
    }
}

// 3. Controles de seguridad: [cumple, título, qué hacer si no cumple]
$hashes_viejos = (int) valorUnico($pdo, "SELECT COUNT(*) FROM usuarios WHERE password NOT LIKE ?", ['$2y$%'], 0);
$sin_rol       = (int) valorUnico($pdo, "SELECT COUNT(*) FROM usuarios u LEFT JOIN roles r ON r.id = u.rol_id WHERE r.id IS NULL", [], 0);

$controles = [
    [defined('DB_USER') && DB_USER !== 'root',
        'Base de datos con usuario dedicado',
        'La aplicación se conecta como "root". Crea el usuario gsb_app y actualiza config_db.php.'],
    [defined('DB_PASS') && DB_PASS !== '',
        'Contraseña de la base de datos configurada',
        'La conexión a MySQL no usa contraseña. Defínela en config_db.php.'],
    [$tabla_intentos,
        'Límite de intentos de acceso activo',
        'Crea la tabla login_intentos para bloquear ataques de fuerza bruta.'],
    [strtolower($usuario_actual) !== 'superadmin',
        'Usuario del superadmin personalizado',
        'Sigues usando el nombre "superadmin". Cámbialo desde Usuarios.'],
    [$hashes_viejos === 0,
        'Contraseñas encriptadas',
        $hashes_viejos . ' usuario(s) con contraseña sin encriptar. Se convierte cuando inician sesión.'],
    [$sin_rol === 0,
        'Todos los usuarios tienen un rol válido',
        $sin_rol . ' usuario(s) sin rol válido. Asígnales uno desde Usuarios.'],
];
$controles_ok = count(array_filter($controles, function ($c) { return $c[0]; }));
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GSB - Panel del Superusuario</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
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
            --shadow-light: 0 10px 25px rgba(0, 131, 123, 0.08);
        }
        body {
            background-color: var(--app-bg);
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            color: var(--text-dark);
            padding: 20px 0;
        }
        .app-container {
            max-width: 1100px;
            background-color: #FFFFFF;
            border-radius: 24px;
            padding: 30px;
            box-shadow: 0 15px 35px rgba(0, 45, 93, 0.05);
            border: 2px solid var(--brand-mint);
        }
        .navbar-minimal { background: transparent; margin-bottom: 25px; }
        .icon-btn {
            background: #F4FBF8;
            border: 1px solid var(--border-color);
            border-radius: 12px;
            width: 42px;
            height: 42px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-dark);
        }
        .user-pill {
            background: #F4FBF8;
            border: 1px solid var(--border-color);
            border-radius: 20px;
            padding: 6px 16px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .card-custom {
            background: var(--card-bg);
            border-radius: 16px;
            border: 1px solid var(--border-color);
            padding: 24px;
            box-shadow: var(--shadow-light);
            margin-bottom: 20px;
        }
        .green-icon-badge {
            width: 40px;
            height: 40px;
            background-color: var(--brand-primary);
            color: white;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            flex-shrink: 0;
        }
        .btn-green {
            background-color: var(--brand-primary);
            color: white;
            border: none;
            border-radius: 10px;
            padding: 10px 24px;
            font-weight: 600;
            transition: all 0.2s;
            box-shadow: 0 4px 12px rgba(0, 131, 123, 0.25);
        }
        .btn-green:hover { background-color: var(--brand-dark); color: white; }
        .btn-green-outline {
            background-color: rgba(92, 202, 142, 0.15);
            color: var(--brand-primary);
            border: 1px solid var(--brand-primary);
            border-radius: 10px;
            padding: 10px 24px;
            font-weight: 600;
        }
        .btn-green-outline:hover { background-color: var(--brand-primary); color: white; }
        .stat-icon {
            width: 40px;
            height: 40px;
            border: 1px solid var(--brand-green);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--brand-green);
            flex-shrink: 0;
        }
        .stat-tile {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 16px;
            padding: 18px 20px;
            box-shadow: var(--shadow-light);
            height: 100%;
        }
        .stat-number { font-size: 1.8rem; font-weight: 700; line-height: 1.1; color: var(--brand-primary); }
        .stat-label  { font-size: 12px; color: var(--text-muted); }
        .check-row {
            display: flex;
            gap: 12px;
            align-items: flex-start;
            padding: 10px 0;
            border-bottom: 1px solid #EEF4F3;
        }
        .check-row:last-child { border-bottom: none; }
        .check-icon { font-size: 1.15rem; line-height: 1.3; }
        .table > :not(caption) > * > * { border-bottom-color: #EEF4F3; }

        @media (max-width: 991.98px) {
            body { padding: 10px 5px !important; }
            .app-container { border-radius: 16px !important; padding: 16px !important; }
        }
        @media (max-width: 575.98px) {
            h4 { font-size: 1.1rem !important; }
            .btn-responsive-group { display: flex; flex-direction: column; gap: 8px; width: 100%; }
            .btn-responsive-group .btn { width: 100%; }
            .stat-number { font-size: 1.5rem; }
        }
    </style>
</head>
<body>

<div class="container app-container">

    <!-- NAVBAR -->
    <nav class="navbar navbar-expand navbar-light navbar-minimal p-0">
        <div class="container-fluid p-0">
            <div class="d-flex align-items-center gap-3">
                <a href="superadmin_panel.php" class="d-flex align-items-center gap-2 fw-bold text-dark fs-5 text-decoration-none">
                    <div class="green-icon-badge"><i class="bi bi-shield-check"></i></div>
                    <span>GSB Corp</span>
                </a>
            </div>
            <div class="d-flex align-items-center gap-2">
                <div class="user-pill">
                    <i class="bi bi-person-fill text-secondary"></i>
                    <span class="small fw-semibold"><?php echo htmlspecialchars($usuario_actual); ?> (superadmin)</span>
                </div>
                <a href="logout.php" class="icon-btn text-danger text-decoration-none" title="Cerrar Sesión">
                    <i class="bi bi-box-arrow-right"></i>
                </a>
            </div>
        </div>
    </nav>

    <div class="d-flex align-items-center gap-2 mb-4">
        <div class="green-icon-badge"><i class="bi bi-speedometer2"></i></div>
        <h4 class="fw-bold m-0" style="color: var(--brand-dark);">Panel del Superusuario</h4>
    </div>

    <!-- BIENVENIDA Y ACCESOS -->
    <div class="card-custom">
        <div class="row align-items-center">
            <div class="col-md-7 pe-md-4">
                <h6 class="fw-bold text-dark mb-2">Bienvenido, <?php echo htmlspecialchars($nombre_actual); ?></h6>
                <p class="text-muted small mb-4">
                    Desde aquí controlas quién accede a la plataforma de <strong>GSB</strong>: creas cuentas, asignas roles
                    (incluidos los Administradores) y revisas el estado de seguridad del sistema.
                </p>
                <div class="d-flex flex-wrap gap-2 btn-responsive-group">
                    <a href="usuario.php" class="btn btn-green text-decoration-none">
                        <i class="bi bi-people-fill me-1"></i> Administrar usuarios
                    </a>
                </div>
            </div>
            <div class="col-md-5 ps-md-4 mt-4 mt-md-0">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="stat-icon"><i class="bi bi-person-badge fs-5"></i></div>
                    <div>
                        <div class="stat-label">Cuentas registradas</div>
                        <div class="fw-bold fs-5" style="color: var(--brand-green);"><?php echo $total_usuarios; ?></div>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="stat-icon"><i class="bi bi-box-seam fs-5"></i></div>
                    <div>
                        <div class="stat-label">Total de equipos</div>
                        <div class="fw-bold fs-5" style="color: var(--brand-green);"><?php echo $total_equipos; ?></div>
                    </div>
                </div>
                <hr class="my-2">
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <span class="text-muted small">Plataforma Oficial <?php echo date("Y"); ?></span>
                    <i class="bi bi-arrow-right" style="color: var(--brand-green);"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- INDICADORES -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="stat-tile">
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon"><i class="bi bi-people"></i></div>
                    <div>
                        <div class="stat-number"><?php echo $total_usuarios; ?></div>
                        <div class="stat-label">Usuarios</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-tile">
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon"><i class="bi bi-cpu"></i></div>
                    <div>
                        <div class="stat-number"><?php echo $total_equipos; ?></div>
                        <div class="stat-label">Equipos</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-tile">
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon"><i class="bi bi-link-45deg"></i></div>
                    <div>
                        <div class="stat-number"><?php echo $sin_asignar; ?></div>
                        <div class="stat-label">Equipos sin cuenta asignada</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-tile">
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon" style="<?php echo ($tabla_intentos && (int)$intentos_24h > 0) ? 'border-color:#dc3545;color:#dc3545;' : ''; ?>"><i class="bi bi-shield-exclamation"></i></div>
                    <div>
                        <div class="stat-number" style="<?php echo ($tabla_intentos && (int)$intentos_24h > 0) ? 'color:#dc3545;' : ''; ?>"><?php echo $tabla_intentos ? (int)$intentos_24h : '—'; ?></div>
                        <div class="stat-label">Accesos fallidos (24 h)</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- USUARIOS POR ROL -->
        <div class="col-lg-6">
            <div class="card-custom">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <div class="green-icon-badge"><i class="bi bi-diagram-3"></i></div>
                    <h6 class="fw-bold m-0">Usuarios por rol</h6>
                </div>
                <?php if (count($porRol) > 0): ?>
                    <?php foreach ($porRol as $r):
                        $pct = $total_usuarios > 0 ? round($r['total'] * 100 / $total_usuarios) : 0;
                        $b   = $badges[$r['clave']] ?? ['dark', strtoupper($r['clave'])];
                        $nom = $etiquetas[$r['clave']] ?? ucfirst($r['clave']);
                    ?>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between small mb-1">
                                <span class="fw-semibold"><?php echo htmlspecialchars($nom); ?></span>
                                <span class="text-muted"><?php echo $r['total']; ?> (<?php echo $pct; ?>%)</span>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar bg-<?php echo $b[0]; ?>" style="width: <?php echo $pct; ?>%"></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-muted small mb-0">No se pudieron leer los roles.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- ESTADO DE SEGURIDAD -->
        <div class="col-lg-6">
            <div class="card-custom">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div class="d-flex align-items-center gap-2">
                        <div class="green-icon-badge"><i class="bi bi-shield-lock"></i></div>
                        <h6 class="fw-bold m-0">Estado de seguridad</h6>
                    </div>
                    <span class="badge rounded-pill bg-<?php echo ($controles_ok === count($controles)) ? 'success' : 'warning text-dark'; ?>">
                        <?php echo $controles_ok; ?> de <?php echo count($controles); ?> correctos
                    </span>
                </div>
                <?php foreach ($controles as $c): ?>
                    <div class="check-row">
                        <?php if ($c[0]): ?>
                            <i class="bi bi-check-circle-fill check-icon text-success"></i>
                            <div class="small fw-semibold"><?php echo htmlspecialchars($c[1]); ?></div>
                        <?php else: ?>
                            <i class="bi bi-exclamation-triangle-fill check-icon text-warning"></i>
                            <div class="small">
                                <div class="fw-semibold"><?php echo htmlspecialchars($c[1]); ?></div>
                                <div class="text-muted"><?php echo htmlspecialchars($c[2]); ?></div>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- USUARIOS RECIENTES -->
        <div class="col-lg-7">
            <div class="card-custom">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="d-flex align-items-center gap-2">
                        <div class="green-icon-badge"><i class="bi bi-person-plus"></i></div>
                        <h6 class="fw-bold m-0">Usuarios recientes</h6>
                    </div>
                    <a href="usuario.php" class="small fw-semibold text-decoration-none" style="color: var(--brand-primary);">Ver todos <i class="bi bi-arrow-right"></i></a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 small">
                        <thead class="table-light">
                            <tr><th>Nombre</th><th>Usuario</th><th>Rol</th></tr>
                        </thead>
                        <tbody>
                        <?php if (count($recientes) > 0): ?>
                            <?php foreach ($recientes as $u):
                                $clave = strtolower(trim((string)$u['rol']));
                                if ($clave === 'solo_ver') { $clave = 'lector'; }
                                $b = $badges[$clave] ?? ['dark', $clave !== '' ? strtoupper($clave) : 'SIN ROL'];
                            ?>
                                <tr>
                                    <td class="fw-semibold"><?php echo htmlspecialchars($u['nombre'] ?? ''); ?></td>
                                    <td><code><?php echo htmlspecialchars($u['usuario']); ?></code></td>
                                    <td><span class="badge bg-<?php echo $b[0]; ?>"><?php echo htmlspecialchars($b[1]); ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="3" class="text-center text-muted py-3">Aún no hay usuarios.</td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ACCESOS FALLIDOS -->
        <div class="col-lg-5">
            <div class="card-custom">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <div class="green-icon-badge"><i class="bi bi-exclamation-octagon"></i></div>
                    <h6 class="fw-bold m-0">Accesos fallidos recientes</h6>
                </div>
                <?php if (!$tabla_intentos): ?>
                    <p class="text-muted small mb-0">
                        El registro de intentos no está activo. Crea la tabla <code>login_intentos</code> para
                        ver aquí los accesos fallidos y bloquear ataques de fuerza bruta.
                    </p>
                <?php elseif (count($intentos_recientes) === 0): ?>
                    <p class="text-muted small mb-0"><i class="bi bi-check-circle text-success me-1"></i> No hay accesos fallidos recientes.</p>
                <?php else: ?>
                    <?php foreach ($intentos_recientes as $i): ?>
                        <div class="check-row">
                            <i class="bi bi-x-circle-fill check-icon text-danger"></i>
                            <div class="small">
                                <div class="fw-semibold"><?php echo htmlspecialchars($i['usuario']); ?></div>
                                <div class="text-muted"><?php echo htmlspecialchars($i['ip']); ?> · <?php echo htmlspecialchars(date('d/m H:i', strtotime($i['creado']))); ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

</div>

</body>
</html>