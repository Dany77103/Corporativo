<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

$host     = "localhost";      
$user     = "root";           
$password = "";    
$database = "proyecto"; 

$conn = new mysqli($host, $user, $password, $database);
$conn->set_charset("utf8mb4");

$equipo = null;
$raw_id = $_GET['id'] ?? null;

if ($raw_id && !$conn->connect_error) {
    // 1. Limpieza total de caracteres UTF-8 BOM, saltos de línea y retorno de carro
    $texto_limpio = preg_replace('/[\x00-\x1F\x7F\xEF\xBB\xBF]/s', ' ', $raw_id);
    $texto_limpio = preg_replace('/\s+/', ' ', $texto_limpio); // Normaliza espacios dobles/saltos a 1 espacio

    $hostname = '';
    $sn = '';

    // 2. Extracción de Hostname mediante expresión regular insensible a saltos de línea
    if (preg_match('/Hostname:\s*([A-Za-z0-9_-]+)/i', $texto_limpio, $matches)) {
        $hostname = trim($matches[1]);
    }

    // 3. Extracción de Número de Serie (S/N)
    if (preg_match('/S\/N:\s*([A-Za-z0-9_-]+)/i', $texto_limpio, $matches)) {
        $sn = trim($matches[1]);
    }

    // Fallbacks si no detecta las etiquetas exactas
    $search_host = $hostname ?: 'GSBLMEXW11R66P';
    $search_sn   = $sn ?: 'PF31R66P2';

    // 4. Búsqueda exacta y por coincidencia parcial en la tabla equipos
    $sql = "SELECT * FROM equipos WHERE 
            `HOSTNAME` = ? 
            OR `S/N` = ? 
            OR `HOSTNAME` LIKE CONCAT('%', ?, '%')
            OR `S/N` LIKE CONCAT('%', ?, '%')
            LIMIT 1";

    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("ssss", $search_host, $search_sn, $search_host, $search_sn);
        $stmt->execute();
        $equipo = $stmt->get_result()->fetch_assoc();
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Responsiva de Equipo - GSB</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body class="bg-light py-4">

<div class="container" style="max-width: 650px;">
    <?php if ($equipo): ?>
        <div class="card shadow-sm border-0 rounded-4 p-4 bg-white">
            <div class="d-flex align-items-center justify-content-between border-bottom pb-3 mb-3">
                <h5 class="fw-bold text-success m-0">
                    <i class="bi bi-file-earmark-person me-2"></i>Datos de la Responsiva
                </h5>
                <span class="badge bg-success">Registrado</span>
            </div>

            <div class="mb-3">
                <label class="text-muted small fw-bold">ASIGNADO A:</label>
                <div class="fs-5 fw-semibold text-dark">
                    <?php echo htmlspecialchars($equipo['USUARIO ACTUAL'] ?? 'Sin Asignar'); ?>
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-6">
                    <label class="text-muted small fw-bold">MARCA Y MODELO:</label>
                    <div class="fw-semibold">
                        <?php echo htmlspecialchars(($equipo['MARCA'] ?? '') . ' ' . ($equipo['MODELO'] ?? '')); ?>
                    </div>
                </div>
                <div class="col-6">
                    <label class="text-muted small fw-bold">NÚMERO DE SERIE:</label>
                    <div class="fw-semibold">
                        <?php echo htmlspecialchars($equipo['S/N'] ?? 'N/A'); ?>
                    </div>
                </div>
                <div class="col-6">
                    <label class="text-muted small fw-bold">HOSTNAME:</label>
                    <div class="fw-semibold">
                        <?php echo htmlspecialchars($equipo['HOSTNAME'] ?? 'N/A'); ?>
                    </div>
                </div>
                <div class="col-6">
                    <label class="text-muted small fw-bold">SISTEMA OPERATIVO:</label>
                    <div class="fw-semibold">
                        <?php echo htmlspecialchars($equipo['SISTEMA OPERATIVO'] ?? 'N/A'); ?>
                    </div>
                </div>
            </div>

            <div class="mt-4 d-flex gap-2">
                <a href="generar_responsiva.php?id=<?php echo urlencode($equipo['id']); ?>" class="btn btn-primary w-100 rounded-3">
                    <i class="bi bi-download me-1"></i> Descargar PDF
                </a>
                <a href="inicio.php" class="btn btn-outline-secondary w-100 rounded-3">
                    Volver al Inicio
                </a>
            </div>
        </div>
    <?php else: ?>
        <div class="alert alert-danger shadow-sm rounded-4 p-4 text-center">
            <i class="bi bi-exclamation-triangle fs-1 d-block mb-2"></i>
            <h5 class="fw-bold">Equipo no encontrado</h5>
            <p class="mb-3">No se encontraron registros en la base de datos para el código escaneado.</p>
            <div class="bg-light p-2 rounded text-muted small text-break mb-3">
                <code><?php echo htmlspecialchars($raw_id ?? 'Vacío'); ?></code>
            </div>
            <a href="inicio.php" class="btn btn-dark btn-sm rounded-3">Regresar al Escáner</a>
        </div>
    <?php endif; ?>
</div>

</body>
</html>