<?php
session_start();

if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'admin') {
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit();
}

$host     = "localhost";      
$user     = "root";           
$password = "";    
$database = "proyecto"; 

$total_equipos = 0;

try {
    $pdo = new PDO("mysql:host=$host;dbname=$database;charset=utf8mb4", $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    $total_equipos = $pdo->query("SELECT COUNT(*) FROM equipos")->fetchColumn();
} catch (PDOException $e) {
    // Si falla la conexión, la variable se mantiene en 0
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GSB - Inicio Corporativo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
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
        .navbar-minimal {
            background: transparent;
            margin-bottom: 25px;
        }
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
        .btn-green:hover {
            background-color: var(--brand-dark);
            color: white;
        }
        .btn-green-outline {
            background-color: rgba(92, 202, 142, 0.15);
            color: var(--brand-primary);
            border: 1px solid var(--brand-primary);
            border-radius: 10px;
            padding: 10px 24px;
            font-weight: 600;
        }
        .btn-green-outline:hover {
            background-color: var(--brand-primary);
            color: white;
        }
        .stat-icon {
            width: 40px;
            height: 40px;
            border: 1px solid var(--brand-green);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--brand-green);
        }
        .chart-container {
            position: relative;
            height: 220px;
            width: 100%;
        }
        .filter-link {
            color: var(--text-muted);
            text-decoration: none;
            font-size: 14px;
            margin-left: 15px;
        }
        .filter-link.active {
            color: var(--brand-primary);
            font-weight: bold;
        }

        @media (max-width: 991.98px) {
            body { padding: 10px 5px !important; }
            .kudy-wrapper, .app-container { border-radius: 16px !important; padding: 16px !important; }
            .gsb-navbar { padding: 10px 15px !important; }
            .chart-container { height: 250px !important; }
        }

        @media (max-width: 575.98px) {
            .login-card { padding: 1.5rem 1rem !important; border-radius: 18px !important; }
            h3 { font-size: 1.25rem !important; }
            h4 { font-size: 1.1rem !important; }
            .stat-icon { width: 38px; height: 38px; font-size: 1.1rem; }
            .table-responsive { font-size: 0.75rem; }
            .btn-responsive-group { display: flex; flex-direction: column; gap: 8px; width: 100%; }
            .btn-responsive-group .btn { width: 100%; }
            .modal-dialog { margin: 0.5rem; }
            .modal-body { padding: 1rem !important; max-height: 75vh; overflow-y: auto; }
        }
    </style>
</head>
<body>

<div class="container app-container">

    <nav class="navbar navbar-expand navbar-light navbar-minimal p-0">
        <div class="container-fluid p-0">
            <div class="d-flex align-items-center gap-3">
                <div class="icon-btn">
                    <i class="bi bi-list fs-5"></i>
                </div>
                <div class="d-flex align-items-center gap-2 fw-bold text-dark fs-5">
                    <div class="green-icon-badge"><i class="bi bi-shield-check"></i></div>
                    <span>GSB Corp</span>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <div class="user-pill">
                    <i class="bi bi-person-fill text-secondary"></i>
                    <span class="small fw-semibold"><?php echo htmlspecialchars($_SESSION['usuario'] ?? 'Admin'); ?></span>
                    <i class="bi bi-chevron-down small text-muted"></i>
                </div>
                <a href="logout.php" class="icon-btn text-danger text-decoration-none" title="Cerrar Sesión">
                    <i class="bi bi-box-arrow-right"></i>
                </a>
            </div>
        </div>
    </nav>

    <div class="d-flex align-items-center gap-2 mb-4">
        <div class="green-icon-badge"><i class="bi bi-layers-half"></i></div>
        <h4 class="fw-bold m-0" style="color: var(--brand-dark);">Sistemas Globales de Control</h4>
    </div>

    <div class="card-custom">
        <div class="row align-items-center">
            <div class="col-md-7 border-end-md pe-md-4">
                <h6 class="fw-bold text-dark mb-2">Sobre este sistema</h6>
                <p class="text-muted small mb-4">
                    Bienvenido al portal institucional de inventario de <strong>GSB</strong>. Una infraestructura centralizada diseñada para la administración, auditoría y trazabilidad offline de activos tecnológicos mediante codificación QR dinámica.
                </p>
                <div class="d-flex gap-3">
                    <a href="index.php?vista=dashboard" class="btn btn-green text-decoration-none">
                        Acceder <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                    <a href="index.php?vista=reportes" class="btn btn-green-outline text-decoration-none">
                        Reportes <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
            <div class="col-md-5 ps-md-4 mt-4 mt-md-0">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="stat-icon">
                        <i class="bi bi-cpu fs-5"></i>
                    </div>
                    <div>
                        <div class="text-muted extra-small" style="font-size: 12px;">Estado del sistema</div>
                        <div class="fw-bold">Activos Registrados</div>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="stat-icon">
                        <i class="bi bi-box-seam fs-5"></i>
                    </div>
                    <div>
                        <div class="text-muted extra-small" style="font-size: 12px;">Total de Equipos</div>
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

    <div class="card-custom">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="d-flex align-items-center gap-2">
                <div class="green-icon-badge"><i class="bi bi-graph-up-arrow"></i></div>
                <h6 class="fw-bold m-0" style="color: var(--brand-dark);">Gobernanza y Gestión Tecnológica</h6>
            </div>
            <div>
                <a href="#" class="filter-link">3 Meses</a>
                <a href="#" class="filter-link active">6 Meses</a>
                <a href="#" class="filter-link">1 Año</a>
            </div>
        </div>
        <div class="chart-container">
            <canvas id="performanceChart"></canvas>
        </div>
    </div>

    <div class="row g-3 mt-1">
        <div class="col-md-4">
            <div class="card-custom h-100 p-3">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <i class="bi bi-bullseye fs-5" style="color: var(--brand-green);"></i>
                    <h6 class="fw-bold m-0" style="color: var(--brand-dark);">Control Total</h6>
                </div>
                <p class="text-muted small m-0">Garantizamos el registro minucioso de cada laptop corporativa (Lenovo, HP y más), asegurando la documentación exacta de componentes.</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card-custom h-100 p-3">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <i class="bi bi-shield-lock fs-5" style="color: var(--brand-green);"></i>
                    <h6 class="fw-bold m-0" style="color: var(--brand-dark);">Seguridad</h6>
                </div>
                <p class="text-muted small m-0">Monitoreamos de forma estricta los indicadores de seguridad, auditando la integración con Active Directory y Azure.</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card-custom h-100 p-3">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <i class="bi bi-qr-code fs-5" style="color: var(--brand-green);"></i>
                    <h6 class="fw-bold m-0" style="color: var(--brand-dark);">Identificación QR</h6>
                </div>
                <p class="text-muted small m-0">Implementamos tecnologías de etiquetado QR offline. Cada activo cuenta con una firma única para auditorías físicas inmediatas.</p>
            </div>
        </div>
    </div>

    <footer class="text-center mt-4 pt-3 border-top">
        <p class="text-muted small m-0">&copy; <?php echo date("Y"); ?> GSB Corporation. Todos los derechos reservados.</p>
    </footer>

</div>

<script>
const ctx = document.getElementById('performanceChart').getContext('2d');
const gradient = ctx.createLinearGradient(0, 0, 0, 200);
gradient.addColorStop(0, 'rgba(0, 180, 81, 0.35)');
gradient.addColorStop(1, 'rgba(0, 180, 81, 0.0)');

new Chart(ctx, {
    type: 'line',
    data: {
        labels: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun'],
        datasets: [{
            label: 'Rendimiento',
            data: [120, 70, 280, 110, 240, 420],
            borderColor: '#00B451',
            borderWidth: 2,
            fill: true,
            backgroundColor: gradient,
            tension: 0.3,
            pointRadius: 0
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            x: { grid: { display: false }, ticks: { color: '#6B7C93' } },
            y: { grid: { color: '#EAF2F1' }, ticks: { color: '#6B7C93' } }
        }
    }
});
</script>

</body>
</html>