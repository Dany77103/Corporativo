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

$conn = new mysqli($host, $user, $password, $database);
$conn->set_charset("utf8mb4");
$total_equipos = 0;

if (!$conn->connect_error) {
    $res = $conn->query("SELECT COUNT(*) as total FROM equipos");
    if ($res) {
        $total_equipos = $res->fetch_assoc()['total'];
    }
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
    <!-- Librería para lectura de código QR mediante la cámara -->
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
    
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

        /* ESTILOS PERSONALIZADOS DEL ESCÁNER QR */
        .qr-scanner-wrapper {
            border: 1px solid rgba(0, 0, 0, 0.08);
        }
        #reader button, #reader input, #reader select, #reader img {
            display: none !important;
        }
        #reader video {
            object-fit: cover !important;
            width: 100% !important;
            border-radius: 1rem;
        }
        .qr-target-box {
            position: absolute;
            width: 190px;
            height: 190px;
            z-index: 2;
            pointer-events: none;
        }
        .qr-target-box .corner {
            position: absolute;
            width: 22px;
            height: 22px;
            border: 3.5px solid var(--brand-primary);
        }
        .top-left { top: 0; left: 0; border-right: none; border-bottom: none; border-top-left-radius: 8px; }
        .top-right { top: 0; right: 0; border-left: none; border-bottom: none; border-top-right-radius: 8px; }
        .bottom-left { bottom: 0; left: 0; border-right: none; border-top: none; border-bottom-left-radius: 8px; }
        .bottom-right { bottom: 0; right: 0; border-left: none; border-top: none; border-bottom-right-radius: 8px; }

        .scan-laser {
            width: 100%;
            height: 2px;
            background: linear-gradient(90deg, rgba(0,131,123,0) 0%, var(--brand-primary) 50%, rgba(0,131,123,0) 100%);
            box-shadow: 0 0 10px var(--brand-primary);
            animation: scanning 2s infinite ease-in-out;
            position: absolute;
        }
        @keyframes scanning {
            0% { top: 0%; }
            50% { top: 100%; }
            100% { top: 0%; }
        }

        /* RESPONSIVE BREAKPOINTS */
        @media (max-width: 991.98px) {
            body {
                padding: 10px 5px !important;
            }
            .app-container {
                border-radius: 16px !important;
                padding: 16px !important;
            }
            .chart-container {
                height: 250px !important;
            }
        }

        @media (max-width: 575.98px) {
            h3 { font-size: 1.25rem !important; }
            h4 { font-size: 1.1rem !important; }
            .stat-icon { width: 38px; height: 38px; font-size: 1.1rem; }
            .btn-responsive-group {
                display: flex;
                flex-direction: column;
                gap: 8px;
                width: 100%;
            }
            .btn-responsive-group .btn {
                width: 100%;
            }
            .modal-dialog {
                margin: 0.5rem;
            }
            .modal-body {
                padding: 1rem !important;
            }
        }
    </style>
</head>
<body>

<div class="container app-container">

    <nav class="navbar navbar-expand navbar-light navbar-minimal p-0">
        <div class="container-fluid p-0">
            <div class="d-flex align-items-center gap-3">
                <div class="d-flex align-items-center gap-2 fw-bold text-dark fs-5">
                    <div class="green-icon-badge"><i class="bi bi-shield-check"></i></div>
                    <span>GSB Corp</span>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <div class="user-pill">
                    <i class="bi bi-person-fill text-secondary"></i>
                    <span class="small fw-semibold">Admin</span>
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
                <div class="d-flex flex-wrap gap-2 btn-responsive-group">
                    <a href="index.php?vista=dashboard" class="btn btn-green text-decoration-none">
                        Acceder <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                    <button type="button" class="btn btn-green-outline" data-bs-toggle="modal" data-bs-target="#qrModal">
                        <i class="bi bi-qr-code-scan me-1"></i> Escanear QR
                    </button>
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
                    <span class="text-muted small">Plataforma Oficial 2026</span>
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

<!-- Modal Escáner QR Re-diseñado -->
<div class="modal fade" id="qrModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 440px;">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            
            <div class="modal-header border-0 pb-0 pt-4 px-4 position-relative">
                <div class="d-flex align-items-center gap-3">
                    <div class="green-icon-badge" style="width: 42px; height: 42px;">
                        <i class="bi bi-qr-code-scan fs-5"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-dark m-0">Escanear Código QR</h5>
                        <small class="text-muted">Apunta la cámara al código del equipo</small>
                    </div>
                </div>
                <button type="button" class="btn-close position-absolute top-0 end-0 m-3" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-4">
                <div class="qr-scanner-wrapper position-relative rounded-4 overflow-hidden bg-dark d-flex align-items-center justify-content-center shadow-inner" style="min-height: 270px;">
                    
                    <div class="qr-target-box" id="target-box" style="display: none;">
                        <span class="corner top-left"></span>
                        <span class="corner top-right"></span>
                        <span class="corner bottom-left"></span>
                        <span class="corner bottom-right"></span>
                        <div class="scan-laser"></div>
                    </div>

                    <div id="reader" class="w-100"></div>

                    <div id="qr-custom-ui" class="text-center p-4 text-white z-3">
                        <i class="bi bi-camera fs-1 mb-2 d-block" style="color: var(--brand-mint);"></i>
                        <p class="small text-light mb-3">Se requiere acceso a la cámara para escanear.</p>
                        <button id="start-scan-btn" class="btn btn-green px-4 py-2 rounded-pill fw-semibold shadow-sm">
                            <i class="bi bi-camera-video me-2"></i> Activar Cámara
                        </button>
                    </div>
                </div>

                <div class="text-center mt-3">
                    <label for="qr-file-input" class="btn btn-link text-decoration-none text-secondary small fw-semibold p-0">
                        <i class="bi bi-image me-1"></i> Subir o escanear desde archivo
                    </label>
                    <input type="file" id="qr-file-input" accept="image/*" class="d-none">
                </div>
            </div>

            <div class="modal-footer border-0 pt-0 px-4 pb-4">
                <button type="button" class="btn btn-light w-100 rounded-3 text-secondary fw-semibold" data-bs-dismiss="modal">
                    Cerrar
                </button>
            </div>

        </div>
    </div>
</div>

<script>
// Gráfica de rendimiento
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

// Lógica de Escáner QR Personalizada
let html5QrCode = null;
const qrModal = document.getElementById('qrModal');

document.getElementById('start-scan-btn').addEventListener('click', function () {
    iniciarCamara();
});

function iniciarCamara() {
    document.getElementById('qr-custom-ui').style.display = 'none';
    document.getElementById('target-box').style.display = 'block';

    if (!html5QrCode) {
        html5QrCode = new Html5Qrcode("reader");
    }

    html5QrCode.start(
        { facingMode: "environment" },
        { fps: 10, qrbox: { width: 190, height: 190 } },
        (decodedText) => {
            html5QrCode.stop().then(() => {
                procesarCodigo(decodedText);
            });
        },
        (errorMessage) => { /* Escaneando... */ }
    ).catch(err => {
        alert("No se pudo acceder a la cámara. Por favor verifica los permisos.");
        document.getElementById('qr-custom-ui').style.display = 'block';
        document.getElementById('target-box').style.display = 'none';
    });
}

function procesarCodigo(decodedText) {
    let codigoLimpio = decodedText.trim();
    if (codigoLimpio.startsWith("http://") || codigoLimpio.startsWith("https://")) {
        window.location.href = codigoLimpio;
    } else {
        window.location.href = "ver_responsiva.php?id=" + encodeURIComponent(codigoLimpio);
    }
}

// Subir imagen para escanear
document.getElementById('qr-file-input').addEventListener('change', e => {
    if (e.target.files.length === 0) return;
    const imageFile = e.target.files[0];
    
    if (!html5QrCode) {
        html5QrCode = new Html5Qrcode("reader");
    }
    
    html5QrCode.scanFile(imageFile, true)
        .then(decodedText => {
            procesarCodigo(decodedText);
        })
        .catch(err => alert("No se detectó ningún código QR en la imagen seleccionada."));
});

// Detener la cámara al cerrar el modal
qrModal.addEventListener('hidden.bs.modal', function () {
    if (html5QrCode && html5QrCode.isScanning) {
        html5QrCode.stop().then(() => {
            resetUI();
        }).catch(err => console.error(err));
    } else {
        resetUI();
    }
});

function resetUI() {
    document.getElementById('qr-custom-ui').style.display = 'block';
    document.getElementById('target-box').style.display = 'none';
}
</script>

</body>
</html>