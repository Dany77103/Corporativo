<?php
session_start();

// ==========================================
// 1. GUARDIÁN DE SEGURIDAD
// ==========================================
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'admin') {
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit();
}

// ==========================================
// 2. CONEXIÓN A LA BASE DE DATOS (PDO)
// ==========================================
$host     = "localhost";      
$user     = "root";           
$password = "";    
$database = "proyecto"; 

try {
    $pdo = new PDO("mysql:host=$host;dbname=$database;charset=utf8mb4", $user, $password, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
} catch (PDOException $e) {
    die("Error de conexión a la base de datos: " . $e->getMessage());
}

// --- LÓGICA: ELIMINAR REGISTRO ---
if (isset($_GET['accion']) && $_GET['accion'] === 'eliminar' && isset($_GET['id'])) {
    $id_eliminar = intval($_GET['id']);
    $stmt = $pdo->prepare("DELETE FROM equipos WHERE id = :id");
    if ($stmt->execute([':id' => $id_eliminar])) {
        header("Location: index.php?vista=registro&status=deleted");
        exit();
    }
}

// --- LÓGICA: EDITAR / ACTUALIZAR REGISTRO ---
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['accion_editar'])) {
    $sql = "UPDATE equipos SET 
                `MARCA` = :marca, `MODELO` = :modelo, `S/N` = :sn, 
                `SISTEMA OPERATIVO` = :sistema_op, `ARQUITECTURA` = :arquitectura, 
                `HOSTNAME` = :hostname, `PROCESADOR` = :procesador, `RAM` = :ram, 
                `GRAFICO` = :grafico, `DISCO MECANICO` = :disco_mecanico, `DISCO SSD` = :disco_ssd, 
                `MAC` = :mac, `OBSERVACIONES` = :observaciones, `ASIGNACION GSB` = :asignacion_gsb, 
                `PAIS` = :pais, `CIUDAD` = :ciudad, `ASIGNACION VP` = :asignacion_vp, 
                `ACTIVE DIRECTORY` = :act_directory, `MFA` = :mfa, `USUARIO ACTUAL` = :usuario, 
                `OBSERVACIONES2` = :observaciones2, `CARGADOR` = :cargador, `CLIENTE AZURE` = :cliente_azure
            WHERE id = :id";
            
    $stmt = $pdo->prepare($sql);
    $params = [
        ':marca'          => trim($_POST['marca'] ?? ''),
        ':modelo'         => trim($_POST['modelo'] ?? ''),
        ':sn'             => trim($_POST['sn'] ?? ''),
        ':sistema_op'     => trim($_POST['sistema_op'] ?? ''),
        ':arquitectura'   => trim($_POST['arquitectura'] ?? ''),
        ':hostname'       => trim($_POST['hostname'] ?? ''),
        ':procesador'     => trim($_POST['procesador'] ?? ''),
        ':ram'            => trim($_POST['ram'] ?? ''),
        ':grafico'        => trim($_POST['grafico'] ?? ''),
        ':disco_mecanico' => trim($_POST['disco_mecanico'] ?? ''),
        ':disco_ssd'      => trim($_POST['disco_ssd'] ?? ''),
        ':mac'            => trim($_POST['mac'] ?? ''),
        ':observaciones'  => trim($_POST['observaciones'] ?? ''),
        ':asignacion_gsb' => trim($_POST['asignacion_gsb'] ?? ''),
        ':pais'           => trim($_POST['pais'] ?? ''),
        ':ciudad'         => trim($_POST['ciudad'] ?? ''),
        ':asignacion_vp'  => trim($_POST['asignacion_vp'] ?? ''),
        ':act_directory'  => trim($_POST['act_directory'] ?? ''),
        ':mfa'            => trim($_POST['mfa'] ?? ''),
        ':usuario'        => trim($_POST['usuario'] ?? ''),
        ':observaciones2' => trim($_POST['observaciones2'] ?? ''),
        ':cargador'       => trim($_POST['cargador'] ?? ''),
        ':cliente_azure'  => trim($_POST['cliente_azure'] ?? ''),
        ':id'             => intval($_POST['id'])
    ];

    if ($stmt->execute($params)) {
        header("Location: index.php?vista=registro&status=updated");
        exit();
    }
}

// --- LÓGICA: PROCESAR FORMULARIO MANUAL (ALTA) ---
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['accion_registrar'])) {
    $sql = "INSERT INTO equipos (
                `MARCA`, `MODELO`, `S/N`, `SISTEMA OPERATIVO`, `ARQUITECTURA`, `HOSTNAME`, 
                `PROCESADOR`, `RAM`, `GRAFICO`, `DISCO MECANICO`, `DISCO SSD`, `MAC`, 
                `OBSERVACIONES`, `ASIGNACION GSB`, `PAIS`, `CIUDAD`, `ASIGNACION VP`, 
                `ACTIVE DIRECTORY`, `MFA`, `USUARIO ACTUAL`, `OBSERVACIONES2`, `CARGADOR`, `CLIENTE AZURE`
            ) VALUES (
                :marca, :modelo, :sn, :sistema_op, :arquitectura, :hostname, 
                :procesador, :ram, :grafico, :disco_mecanico, :disco_ssd, :mac, 
                :observaciones, :asignacion_gsb, :pais, :ciudad, :asignacion_vp, 
                :act_directory, :mfa, :usuario, :observaciones2, :cargador, :cliente_azure
            )";
            
    $stmt = $pdo->prepare($sql);
    $params = [
        ':marca'          => trim($_POST['marca'] ?? ''),
        ':modelo'         => trim($_POST['modelo'] ?? ''),
        ':sn'             => trim($_POST['sn'] ?? ''),
        ':sistema_op'     => trim($_POST['sistema_op'] ?? ''),
        ':arquitectura'   => trim($_POST['arquitectura'] ?? ''),
        ':hostname'       => trim($_POST['hostname'] ?? ''),
        ':procesador'     => trim($_POST['procesador'] ?? ''),
        ':ram'            => trim($_POST['ram'] ?? ''),
        ':grafico'        => trim($_POST['grafico'] ?? ''),
        ':disco_mecanico' => trim($_POST['disco_mecanico'] ?? ''),
        ':disco_ssd'      => trim($_POST['disco_ssd'] ?? ''),
        ':mac'            => trim($_POST['mac'] ?? ''),
        ':observaciones'  => trim($_POST['observaciones'] ?? ''),
        ':asignacion_gsb' => trim($_POST['asignacion_gsb'] ?? ''),
        ':pais'           => trim($_POST['pais'] ?? ''),
        ':ciudad'         => trim($_POST['ciudad'] ?? ''),
        ':asignacion_vp'  => trim($_POST['asignacion_vp'] ?? ''),
        ':act_directory'  => trim($_POST['act_directory'] ?? ''),
        ':mfa'            => trim($_POST['mfa'] ?? ''),
        ':usuario'        => trim($_POST['usuario'] ?? ''),
        ':observaciones2' => trim($_POST['observaciones2'] ?? ''),
        ':cargador'       => trim($_POST['cargador'] ?? ''),
        ':cliente_azure'  => trim($_POST['cliente_azure'] ?? '')
    ];

    if ($stmt->execute($params)) {
        header("Location: index.php?vista=registro&status=success");
        exit();
    }
}

// --- PROCESAR CARGA MASIVA (CSV Y XLSX) ---
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['accion_csv'])) {
    if (isset($_FILES['archivo_excel']) && $_FILES['archivo_excel']['error'] === 0) {
        $filename = $_FILES['archivo_excel']['tmp_name'];
        $original_name = $_FILES['archivo_excel']['name'];
        $extension = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
        
        $headers = [];
        $filas_datos = [];

        if ($extension === 'xlsx') {
            if (!file_exists('SimpleXLSX.php')) {
                die("<script>alert('ERROR: No se encuentra el archivo SimpleXLSX.php.'); window.location.href='index.php?vista=registro';</script>");
            }
            require_once 'SimpleXLSX.php';
            if ($xlsx = Shuchkin\SimpleXLSX::parse($filename)) {
                $todas_las_filas = $xlsx->rows();
                if (!empty($todas_las_filas)) {
                    $headers = array_shift($todas_las_filas); 
                    $filas_datos = $todas_las_filas; 
                }
            } else {
                die("<script>alert('ERROR al leer el archivo de Excel: " . Shuchkin\SimpleXLSX::parseError() . "'); window.location.href='index.php?vista=registro';</script>");
            }
        } 
        else if ($extension === 'csv') {
            $handle = fopen($filename, "r");
            $primera_linea = fgets($handle);
            $separador = (strpos($primera_linea, ';') !== false) ? ';' : ',';
            rewind($handle);

            $headers = fgetcsv($handle, 1000, $separador);
            if ($headers !== FALSE) {
                while (($data = fgetcsv($handle, 1000, $separador)) !== FALSE) {
                    $filas_datos[] = $data;
                }
            }
            fclose($handle);
        } else {
            die("<script>alert('ERROR: Formato no soportado. Sube un archivo .xlsx o .csv'); window.location.href='index.php?vista=registro';</script>");
        }

        if (!empty($headers)) {
            $headers = array_map(function($h) {
                $h = mb_convert_encoding($h, "UTF-8", "UTF-8, ISO-8859-1, Windows-1252");
                $h = preg_replace('/[\x00-\x1F\x7F-\xFF]/', '', $h);
                return strtoupper(trim($h));
            }, $headers);

            $pos = [
                'MARCA'             => array_search('MARCA', $headers),
                'MODELO'            => array_search('MODELO', $headers),
                'SISTEMA_OP'        => array_search('SISTEMA OPERATIVO', $headers),
                'ARQUITECTURA'      => array_search('ARQUITECTURA DEL PROCESADOR', $headers),
                'HOSTNAME'          => array_search('HOSTNAME', $headers),
                'PROCESADOR'        => array_search('PROCESADOR', $headers),
                'RAM'               => array_search('RAM', $headers),
                'GRAFICO'           => array_search('GRAFICO', $headers),
                'DISCO_MECANICO'    => array_search('DISCO MECANICO', $headers),
                'DISCO_SSD'         => array_search('DISCO SSD', $headers),
                'MAC'               => array_search('MAC', $headers),
                'ASIGNACION_GSB'    => array_search('ASIGNACIN GSB', $headers) !== false ? array_search('ASIGNACIN GSB', $headers) : array_search('ASIGNACIÓN GSB', $headers),
                'PAIS'              => array_search('PAIS', $headers) !== false ? array_search('PAIS', $headers) : array_search('PAÍS', $headers),
                'CIUDAD'            => array_search('CIUDAD', $headers),
                'ASIGNACION_VP'     => array_search('ASIGNACIN VP', $headers) !== false ? array_search('ASIGNACIN VP', $headers) : array_search('ASIGNACIÓN VP', $headers),
                'ACTIVE_DIRECTORY'  => array_search('ACTIVE DIRECTORY', $headers),
                'MFA'               => array_search('MFA', $headers),
                'USUARIO_ACTUAL'    => array_search('USUARIO ACTUAL', $headers),
                'CARGADOR'          => array_search('CARGADOR', $headers),
                'CLIENTE_AZURE'     => array_search('CLIENTE AZURE', $headers)
            ];

            $pos['S_N'] = false;
            foreach (['S/N', 'SERIE', 'SN', 'NUMERO DE SERIE', 'NÚMERO DE SERIE'] as $posible_nombre) {
                $idx = array_search($posible_nombre, $headers);
                if ($idx !== false) {
                    $pos['S_N'] = $idx;
                    break;
                }
            }

            $pos_observaciones_1 = false;
            $pos_observaciones_2 = false;
            foreach ($headers as $key => $val) {
                if ($val === 'OBSERVACIONES') {
                    if ($pos_observaciones_1 === false) {
                        $pos_observaciones_1 = $key;
                    } else {
                        $pos_observaciones_2 = $key;
                        break;
                    }
                }
            }

            $stmtCsv = $pdo->prepare("INSERT INTO equipos (
                `MARCA`, `MODELO`, `S/N`, `SISTEMA OPERATIVO`, `ARQUITECTURA`, `HOSTNAME`, 
                `PROCESADOR`, `RAM`, `GRAFICO`, `DISCO MECANICO`, `DISCO SSD`, `MAC`, 
                `OBSERVACIONES`, `ASIGNACION GSB`, `PAIS`, `CIUDAD`, `ASIGNACION VP`, 
                `ACTIVE DIRECTORY`, `MFA`, `USUARIO ACTUAL`, `OBSERVACIONES2`, `CARGADOR`, `CLIENTE AZURE`
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

            $guardados = 0;

            foreach ($filas_datos as $data) {
                $data = array_map(function($d) {
                    return mb_convert_encoding($d, "UTF-8", "UTF-8, ISO-8859-1, Windows-1252");
                }, $data);

                if (empty($data) || count(array_filter($data)) <= 1) {
                    continue;
                }

                $getVal = function($index) use ($data) {
                    return ($index !== false && isset($data[$index])) ? trim($data[$index]) : '';
                };

                $hostname = $getVal($pos['HOSTNAME']);

                if (!empty($hostname)) {
                    $rowParams = [
                        $getVal($pos['MARCA']),
                        $getVal($pos['MODELO']),
                        $getVal($pos['S_N']),
                        $getVal($pos['SISTEMA_OP']),
                        $getVal($pos['ARQUITECTURA']),
                        $hostname,
                        $getVal($pos['PROCESADOR']),
                        $getVal($pos['RAM']),
                        $getVal($pos['GRAFICO']),
                        $getVal($pos['DISCO_MECANICO']),
                        $getVal($pos['DISCO_SSD']),
                        $getVal($pos['MAC']),
                        $getVal($pos_observaciones_1),
                        $getVal($pos['ASIGNACION_GSB']),
                        $getVal($pos['PAIS']),
                        $getVal($pos['CIUDAD']),
                        $getVal($pos['ASIGNACION_VP']),
                        $getVal($pos['ACTIVE_DIRECTORY']),
                        $getVal($pos['MFA']),
                        $getVal($pos['USUARIO_ACTUAL']),
                        $getVal($pos_observaciones_2),
                        $getVal($pos['CARGADOR']),
                        $getVal($pos['CLIENTE_AZURE'])
                    ];

                    if ($stmtCsv->execute($rowParams)) {
                        $guardados++;
                    }
                }
            }
        }

        if ($guardados > 0) {
            header("Location: index.php?vista=registro&status=success_csv");
        } else {
            die("<script>alert('ERROR: No se leyeron datos válidos.'); window.location.href='index.php?vista=registro';</script>");
        }
        exit();
    }
}

$vista = $_GET['vista'] ?? 'dashboard';

// OPTIMIZACIÓN DASHBOARD: Consulta unificada
$stats = $pdo->query("SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN `MARCA` LIKE '%LENOVO%' THEN 1 ELSE 0 END) as lenovo,
    SUM(CASE WHEN `MARCA` LIKE '%HP%' OR `MARCA` LIKE '%HEWLETT%' THEN 1 ELSE 0 END) as hp
FROM equipos")->fetch();

$total_equipos = (int)($stats['total'] ?? 0);
$total_lenovo  = (int)($stats['lenovo'] ?? 0);
$total_hp      = (int)($stats['hp'] ?? 0);
$total_otros   = $total_equipos - ($total_lenovo + $total_hp);

$resMarcas = $pdo->query("SELECT MARCA, COUNT(*) as cantidad FROM equipos GROUP BY MARCA");
$marcasLabels = [];
$marcasData   = [];
while ($m = $resMarcas->fetch()) {
    $marcasLabels[] = $m['MARCA'] ? $m['MARCA'] : 'Sin Especificar';
    $marcasData[]   = (int)$m['cantidad'];
}

$resSO = $pdo->query("SELECT `SISTEMA OPERATIVO` as so, COUNT(*) as cantidad FROM equipos GROUP BY `SISTEMA OPERATIVO`");
$soLabels = [];
$soData   = [];
while ($s = $resSO->fetch()) {
    $soLabels[] = $s['so'] ? $s['so'] : 'Desconocido';
    $soData[]   = (int)$s['cantidad'];
}

// FILTROS DE REPORTES
$filtro_marca = isset($_GET['f_marca']) ? trim($_GET['f_marca']) : '';
$sql_reporte = "SELECT * FROM equipos WHERE 1=1";
$params_reporte = [];

if (!empty($filtro_marca)) {
    $sql_reporte .= " AND `MARCA` LIKE :marca";
    $params_reporte[':marca'] = "%$filtro_marca%";
}

$stmt_reporte = $pdo->prepare($sql_reporte);
$stmt_reporte->execute($params_reporte);
$reportes = $stmt_reporte->fetchAll();

$reporte_total = count($reportes);

$sql_kpi_ad = "SELECT COUNT(*) FROM equipos WHERE `ACTIVE DIRECTORY` = 'SI'";
$sql_kpi_az = "SELECT COUNT(*) FROM equipos WHERE `CLIENTE AZURE` = 'SI'";

if (!empty($filtro_marca)) {
    $sql_kpi_ad .= " AND `MARCA` LIKE :marca";
    $sql_kpi_az .= " AND `MARCA` LIKE :marca";
}

$stmt_ad = $pdo->prepare($sql_kpi_ad);
$stmt_az = $pdo->prepare($sql_kpi_az);
if (!empty($filtro_marca)) {
    $stmt_ad->execute([':marca' => "%$filtro_marca%"]);
    $stmt_az->execute([':marca' => "%$filtro_marca%"]);
} else {
    $stmt_ad->execute();
    $stmt_az->execute();
}

$reporte_ad_si = $stmt_ad->fetchColumn();
$reporte_azure_si = $stmt_az->fetchColumn();

$resultado_equipos = $pdo->query("SELECT * FROM equipos")->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GSB - Sistema de Inventario Corporativo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <style>
        :root {
            --brand-primary: #00837B;      
            --brand-dark: #002D5D;         
            --brand-green: #00B451;        
            --brand-mint: #5CCA8E;         
            --brand-bg: #F4F8F7;           
            --brand-card-bg: #FFFFFF;     
            --brand-text-muted: #6B7C93;   
            --brand-border: #D1E5E3;       
            --gsb-danger: #ef4444;         
        }

        body {
            background-color: var(--brand-bg);
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            color: var(--brand-dark);
            padding: 16px 8px;
        }
        
        .kudy-wrapper {
            max-width: 1200px;
            margin: 0 auto;
            background-color: var(--brand-card-bg);
            border: 2px solid var(--brand-mint);
            border-radius: 28px;
            padding: 16px;
            box-shadow: 0 20px 40px rgba(0, 131, 123, 0.08);
            overflow-x: hidden;
        }

        @media (min-width: 768px) {
            body { padding: 24px 12px; }
            .kudy-wrapper { padding: 24px; }
        }

        .gsb-navbar {
            background-color: var(--brand-card-bg) !important;
            border-radius: 20px;
            padding: 12px 24px;
            border: 1px solid var(--brand-border);
            box-shadow: 0 4px 15px rgba(0, 45, 93, 0.04);
            margin-bottom: 24px;
        }

        .navbar-brand { 
            font-weight: 700; 
            letter-spacing: -0.5px; 
            color: var(--brand-primary) !important;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .navbar-brand i {
            background: rgba(92, 202, 142, 0.2);
            color: var(--brand-primary);
            padding: 8px;
            border-radius: 50%;
            font-size: 1.1rem;
        }

        .nav-link { 
            color: var(--brand-text-muted) !important; 
            font-weight: 600;
            padding: 8px 16px !important;
            border-radius: 12px;
            transition: all 0.2s ease;
        }

        .nav-link:hover { 
            color: var(--brand-primary) !important; 
            background-color: rgba(92, 202, 142, 0.15);
        }

        .nav-link.active { 
            font-weight: 700; 
            color: var(--brand-primary) !important; 
            background-color: rgba(92, 202, 142, 0.25);
        }

        .card-gsb-stat { 
            border: 1px solid var(--brand-border); 
            border-radius: 20px; 
            transition: transform 0.2s ease, box-shadow 0.2s ease; 
            background-color: var(--brand-card-bg);
            color: var(--brand-dark);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.02);
            padding: 12px;
        }

        .card-gsb-stat:hover { 
            transform: translateY(-3px); 
            box-shadow: 0 8px 20px rgba(0, 131, 123, 0.12); 
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background-color: rgba(0, 180, 81, 0.12);
            color: var(--brand-green);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
            margin: 0 auto 12px auto;
        }

        .btn-gsb-primary { 
            background-color: var(--brand-primary); 
            color: white; 
            border: none; 
            border-radius: 50px;
            padding: 10px 24px;
            font-weight: 600;
            font-size: 0.9rem;
            box-shadow: 0 4px 12px rgba(0, 131, 123, 0.25);
            transition: all 0.2s ease;
        }

        .btn-gsb-primary:hover { 
            background-color: var(--brand-dark); 
            color: white; 
            transform: translateY(-1px);
        }

        .btn-gsb-success {
            background-color: var(--brand-green);
            color: white;
            border: none;
            border-radius: 50px;
            padding: 10px 24px;
            font-weight: 600;
            font-size: 0.9rem;
            box-shadow: 0 4px 12px rgba(0, 180, 81, 0.25);
            transition: all 0.2s ease;
        }

        .btn-gsb-success:hover {
            background-color: #009342;
            color: white;
        }

        .btn-outline-secondary {
            border: 1px solid var(--brand-primary);
            color: var(--brand-primary);
            border-radius: 50px;
            padding: 10px 24px;
            font-weight: 600;
            background: transparent;
        }

        .btn-outline-secondary:hover {
            background-color: var(--brand-primary);
            color: white;
        }

        .card-kudy {
            background: var(--brand-card-bg);
            border-radius: 24px;
            border: 1px solid var(--brand-border);
            box-shadow: 0 4px 20px rgba(0, 45, 93, 0.04);
        }

        .table-reportes {
    width: 100% !important;
    table-layout: auto;
}

.table-reportes th, 
.table-reportes td {
    white-space: nowrap;
    padding: 10px 12px !important;
}

        .table-excel-mode th {
            background-color: var(--brand-primary) !important;
            color: white !important;
            border: none;
            font-weight: 600;
            font-size: 0.82rem;
            padding: 14px 12px;
            white-space: nowrap;
        }

        .table-excel-mode td {
            font-size: 0.82rem;
            color: var(--brand-dark);
            padding: 12px;
            border-bottom: 1px solid var(--brand-border);
            vertical-align: middle;
            word-break: break-word;
        }

        /* Centrado de celdas en tablas */
.table td.text-center, .table th.text-center {
    text-align: center !important;
    vertical-align: middle !important;
}

/* Evita que el contenido de los badges rompa la celda o se empalme */
.table td {
    vertical-align: middle !important;
}

.col-fit {
    width: 1%;
    white-space: nowrap;
}

        .qr-container { 
            display: flex; 
            flex-direction: column; 
            justify-content: center; 
            align-items: center; 
            background: #ffffff;
            padding: 8px;
            border-radius: 12px;
            border: 1px solid var(--brand-mint);
            min-width: 90px;
            min-height: 90px;
        }

        [id^="qr_box_"] {
            width: 80px !important;
            height: 80px !important;
            display: flex !important;
            justify-content: center;
            align-items: center;
        }

        .qr-container div canvas, 
        .qr-container div img {
            width: 80px !important;
            height: 80px !important;
        }

        .btn-qr-dim { 
            font-size: 11px; 
            padding: 4px 10px;
            border-radius: 20px;
            background-color: var(--brand-green);
            color: white;
            border: none;
            transition: background 0.2s ease;
        }

        .btn-qr-dim:hover {
            background-color: var(--brand-dark);
            color: white;
        }

        .alert-gsb-success {
            background-color: rgba(92, 202, 142, 0.2);
            border: 1px solid var(--brand-mint);
            color: var(--brand-dark);
            border-radius: 16px;
        }

        .alert-gsb-danger {
            background-color: #fef2f2;
            border: 1px solid #fca5a5;
            color: var(--gsb-danger);
            border-radius: 16px;
        }

        .modal-content {
            border-radius: 24px;
            border: 2px solid var(--brand-mint);
        }

        .modal-header {
            background-color: var(--brand-primary) !important;
            color: white !important;
            border-radius: 22px 22px 0 0;
            padding: 20px 24px;
        }

        .modal-title {
            color: white !important;
        }

        .form-control, .form-select {
            border-radius: 12px;
            border: 1px solid var(--brand-border);
            padding: 10px 14px;
            font-size: 0.9rem;
            background-color: #F8FCFA;
            color: var(--brand-dark);
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--brand-primary);
            box-shadow: 0 0 0 0.25rem rgba(0, 131, 123, 0.2);
        }

        .badge-kudy-success {
            background-color: rgba(0, 180, 81, 0.15);
            color: var(--brand-green);
            border: 1px solid var(--brand-green);
            border-radius: 50px;
            padding: 6px 14px;
            font-weight: 600;
        }

        .badge-kudy-danger {
            background-color: #fef2f2;
            color: var(--gsb-danger);
            border: 1px solid #fca5a5;
            border-radius: 50px;
            padding: 6px 14px;
            font-weight: 600;
        }

        @media print {
            body {
                background-color: #ffffff !important;
                color: #000000 !important;
                padding: 0 !important;
            }
            .kudy-wrapper {
                border: none !important;
                box-shadow: none !important;
                padding: 0 !important;
            }
            .gsb-navbar, .no-print, form, .btn, .alert, .modal {
                display: none !important;
            }
            .print-header {
                display: block !important;
                border-bottom: 2px solid var(--brand-primary);
                padding-bottom: 15px;
                margin-bottom: 25px;
            }
        }

        .print-header {
            display: none;
        }
    </style>
</head>
<body>

<div class="kudy-wrapper">

<div class="print-header text-center">
    <h2 class="fw-bold text-uppercase" style="color: var(--brand-primary); margin: 0;">GSB INVENTARIO CORPORATIVO</h2>
    <p class="text-secondary mb-1">Informe Oficial de Activos Tecnológicos</p>
    <small class="text-muted">Generado el: <?php echo date("d/m/Y H:i"); ?></small>
</div>

<nav class="navbar navbar-expand-lg navbar-light gsb-navbar no-print">
    <div class="container-fluid">
        <a class="navbar-brand" href="index.php?vista=dashboard">
            <i class="bi bi-shield-check"></i> 
            <span>GSB Financials</span>
        </a>
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link <?php echo ($vista == 'dashboard') ? 'active' : ''; ?>" href="index.php?vista=dashboard"><i class="bi bi-grid-1x2-fill me-1"></i> Dashboard</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($vista == 'registro') ? 'active' : ''; ?>" href="index.php?vista=registro"><i class="bi bi-laptop me-1"></i> Registro de Activos</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($vista == 'reportes') ? 'active' : ''; ?>" href="index.php?vista=reportes"><i class="bi bi-file-earmark-pdf-fill me-1"></i> Reportes</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container-fluid px-0 px-md-2 mb-3">

    <?php if ($vista == 'dashboard') { ?>
        <div class="row mb-4 align-items-center">
            <div class="col">
                <h3 class="fw-bold m-0" style="color: var(--brand-primary);">Kudy Hedge Fund</h3>
                <p class="text-muted small m-0">Panel General de Gestión y Control de Activos</p>
            </div>
        </div>
        
        <div class="row text-center mb-4">
            <div class="col-md-3 mb-3">
                <div class="card card-gsb-stat h-100 p-3">
                    <div class="card-body">
                        <div class="stat-icon"><i class="bi bi-pc-display-horizontal"></i></div>
                        <h3 class="fw-bold mb-1" style="color: var(--brand-dark);"><?php echo $total_equipos; ?></h3>
                        <span class="text-muted small">Total de Laptops</span>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card card-gsb-stat h-100 p-3">
                    <div class="card-body">
                        <div class="stat-icon"><i class="bi bi-cpu"></i></div>
                        <h3 class="fw-bold mb-1" style="color: var(--brand-dark);"><?php echo $total_lenovo; ?></h3>
                        <span class="text-muted small">Laptops Lenovo</span>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card card-gsb-stat h-100 p-3">
                    <div class="card-body">
                        <div class="stat-icon"><i class="bi bi-hdd-network"></i></div>
                        <h3 class="fw-bold mb-1" style="color: var(--brand-dark);"><?php echo $total_hp; ?></h3>
                        <span class="text-muted small">Laptops HP</span>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card card-gsb-stat h-100 p-3">
                    <div class="card-body">
                        <div class="stat-icon"><i class="bi bi-tags"></i></div>
                        <h3 class="fw-bold mb-1" style="color: var(--brand-dark);"><?php echo $total_otros; ?></h3>
                        <span class="text-muted small">Otras Marcas</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-2">
            <div class="col-md-6 mb-4">
                <div class="card card-kudy p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-bold m-0" style="color: var(--brand-primary);"><i class="bi bi-pie-chart-fill me-2"></i>Distribución por Fabricante</h6>
                    </div>
                    <canvas id="chartMarcas" height="200"></canvas>
                </div>
            </div>
            <div class="col-md-6 mb-4">
                <div class="card card-kudy p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-bold m-0" style="color: var(--brand-primary);"><i class="bi bi-bar-chart-line-fill me-2"></i>Sistemas Operativos Instalados</h6>
                    </div>
                    <canvas id="chartSO" height="200"></canvas>
                </div>
            </div>
        </div>

        <script>
            document.addEventListener("DOMContentLoaded", function() {
                new Chart(document.getElementById('chartMarcas'), {
                    type: 'doughnut',
                    data: {
                        labels: <?php echo json_encode($marcasLabels); ?>,
                        datasets: [{
                            data: <?php echo json_encode($marcasData); ?>,
                            backgroundColor: ['#00837B', '#002D5D', '#00B451', '#5CCA8E', '#3498DB', '#E74C3C']
                        }]
                    },
                    options: { responsive: true }
                });

                new Chart(document.getElementById('chartSO'), {
                    type: 'bar',
                    data: {
                        labels: <?php echo json_encode($soLabels); ?>,
                        datasets: [{
                            label: 'Cantidad',
                            data: <?php echo json_encode($soData); ?>,
                            backgroundColor: '#00837B',
                            borderRadius: 8
                        }]
                    },
                    options: { responsive: true, plugins: { legend: { display: false } } }
                });
            });
        </script>

        <div class="card card-kudy p-4 mt-2">
            <h5 class="fw-bold" style="color: var(--brand-primary);">Plataforma de Inventario QR - GSB</h5>
            <p class="text-muted small">Este sistema centralizado automatiza la creación de etiquetas QR offline para el control rápido de equipos informáticos.</p>
            <div class="d-flex flex-wrap gap-2">
                <a href="index.php?vista=registro" class="btn btn-gsb-primary"><i class="bi bi-arrow-right-circle me-1"></i> Gestionar Inventario</a>
                <a href="index.php?vista=reportes" class="btn btn-outline-secondary"><i class="bi bi-file-earmark-text me-1"></i> Consultar Reportes</a>
            </div>
        </div>
    <?php } ?>

    <?php if ($vista == 'registro') { ?>
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
            <div>
                <h3 class="fw-bold m-0" style="color: var(--brand-primary);">Inventario y Control QR</h3>
                <p class="text-muted small m-0">Administración de equipos tecnológicos activos y generación de etiquetas.</p>
            </div>
            <button class="btn btn-gsb-primary" data-bs-toggle="modal" data-bs-target="#modalRegistrar"><i class="bi bi-plus-lg me-1"></i> Alta de Equipos / Excel</button>
        </div>

        <?php if(isset($_GET['status'])) { 
            if ($_GET['status'] == 'deleted') { ?>
                <div class="alert alert-gsb-danger alert-dismissible fade show border-0 shadow-sm" role="alert">
                    <i class="bi bi-trash-fill me-2"></i><strong>Activo Eliminado:</strong> El registro seleccionado ha sido borrado de la base de datos de manera definitiva.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php } elseif ($_GET['status'] == 'updated') { ?>
                <div class="alert alert-gsb-success alert-dismissible fade show border-0 shadow-sm" role="alert">
                    <i class="bi bi-pencil-square me-2"></i><strong>Activo Actualizado:</strong> Los cambios del equipo se guardaron correctamente.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php } else { ?>
                <div class="alert alert-gsb-success alert-dismissible fade show border-0 shadow-sm" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i><strong>Operación Exitosa:</strong> Los registros han sido integrados correctamente en la base de datos de GSB.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php }
        } ?>

        <div class="card card-kudy p-3 p-md-4">
            <div class="table-responsive">
                <table class="table align-middle table-excel-mode">
                    <thead>
                        <tr>
                            <th class="text-center">Acciones</th>
                            <th>N°</th>
                            <th>Marca</th>
                            <th>Modelo</th>
                            <th>S/N</th>
                            <th>Hostname</th>
                            <th>Usuario Asignado</th>
                            <th class="text-center" style="min-width: 120px;">Código QR</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if (!empty($resultado_equipos)) {
                            $esc = function($val) {
                                return htmlspecialchars($val ?? '', ENT_QUOTES, 'UTF-8');
                            };

                            foreach($resultado_equipos as $index => $row) {
                                $row_upper = array_change_key_case($row, CASE_UPPER);
                                
                                $id = isset($row['id']) ? $row['id'] : rand(1000, 9999);
                                
                                $marca         = isset($row_upper['MARCA']) ? trim($row_upper['MARCA']) : '';
                                $modelo        = isset($row_upper['MODELO']) ? trim($row_upper['MODELO']) : '';
                                $sn            = isset($row_upper['S/N']) ? trim($row_upper['S/N']) : '';
                                $sistema_op    = isset($row_upper['SISTEMA OPERATIVO']) ? trim($row_upper['SISTEMA OPERATIVO']) : '';
                                $arquitectura  = isset($row_upper['ARQUITECTURA DEL PROCESADOR']) ? trim($row_upper['ARQUITECTURA DEL PROCESADOR']) : (isset($row_upper['ARQUITECTURA']) ? trim($row_upper['ARQUITECTURA']) : '');
                                $hostname      = isset($row_upper['HOSTNAME']) ? trim($row_upper['HOSTNAME']) : '';
                                $procesador    = isset($row_upper['PROCESADOR']) ? trim($row_upper['PROCESADOR']) : '';
                                $ram           = isset($row_upper['RAM']) ? trim($row_upper['RAM']) : '';
                                $grafico       = isset($row_upper['GRAFICO']) ? trim($row_upper['GRAFICO']) : '';
                                $disco_mecanic = isset($row_upper['DISCO MECANICO']) ? trim($row_upper['DISCO MECANICO']) : '';
                                $disco_ssd     = isset($row_upper['DISCO SSD']) ? trim($row_upper['DISCO SSD']) : '';
                                $mac           = isset($row_upper['MAC']) ? trim($row_upper['MAC']) : '';
                                $observaciones = isset($row_upper['OBSERVACIONES']) ? trim($row_upper['OBSERVACIONES']) : '';
                                $asignacion_gsb= isset($row_upper['ASIGNACIÓN GSB']) ? trim($row_upper['ASIGNACIÓN GSB']) : (isset($row_upper['ASIGNACION GSB']) ? trim($row_upper['ASIGNACION GSB']) : '');
                                $pais          = isset($row_upper['PAIS']) ? trim($row_upper['PAIS']) : (isset($row_upper['PAÍS']) ? trim($row_upper['PAÍS']) : '');
                                $ciudad        = isset($row_upper['CIUDAD']) ? trim($row_upper['CIUDAD']) : '';
                                $asignacion_vp = isset($row_upper['ASIGNACIÓN VP']) ? trim($row_upper['ASIGNACIÓN VP']) : (isset($row_upper['ASIGNACION VP']) ? trim($row_upper['ASIGNACION VP']) : '');
                                $act_directory = isset($row_upper['ACTIVE DIRECTORY']) ? trim($row_upper['ACTIVE DIRECTORY']) : '';
                                $mfa           = isset($row_upper['MFA']) ? trim($row_upper['MFA']) : '';
                                $usuario_act   = isset($row_upper['USUARIO ACTUAL']) ? trim($row_upper['USUARIO ACTUAL']) : '';
                                $observaciones2= isset($row_upper['OBSERVACIONES2']) ? trim($row_upper['OBSERVACIONES2']) : '';
                                $cargador      = isset($row_upper['CARGADOR']) ? trim($row_upper['CARGADOR']) : '';
                                $cliente_azure = isset($row_upper['CLIENTE AZURE']) ? trim($row_upper['CLIENTE AZURE']) : '';

                                $id_valido = (!empty($id) && $id > 0) ? $id : 'fila_' . $index;
                                $h_str     = !empty($hostname) ? $hostname : 'N/A';
                                $s_str     = !empty($sn) ? $sn : 'N/A';
                                $m_str     = trim($marca . ' ' . $modelo);
                                $u_str     = !empty($usuario_act) ? $usuario_act : 'N/A';

                                $textoQR = "EQUIPO GSB | Host: {$h_str} | SN: {$s_str} | Mod: {$m_str} | User: {$u_str}";
                                $attrQR = htmlspecialchars($textoQR, ENT_QUOTES, 'UTF-8');
                                $idContenedor = "qr_box_" . $id_valido;

                                echo "<tr>";
                                echo "<td class='text-center'>";
                                echo "  <div class='d-flex justify-content-center gap-1'>";
                                echo "    <button type='button' class='btn btn-light btn-sm rounded-circle text-info' data-bs-toggle='collapse' data-bs-target='#info_{$id}' aria-expanded='false' title='Ver Detalles'><i class='bi bi-eye-fill'></i></button>";
                                echo "    <button type='button' class='btn btn-light btn-sm rounded-circle btn-editar' style='color: var(--brand-primary)' title='Editar'
                                            data-id='{$id}'
                                            data-marca='{$esc($marca)}'
                                            data-modelo='{$esc($modelo)}'
                                            data-sn='{$esc($sn)}'
                                            data-sistema_op='{$esc($sistema_op)}'
                                            data-arquitectura='{$esc($arquitectura)}'
                                            data-hostname='{$esc($hostname)}'
                                            data-procesador='{$esc($procesador)}'
                                            data-ram='{$esc($ram)}'
                                            data-grafico='{$esc($grafico)}'
                                            data-disco_mecanico='{$esc($disco_mecanic)}'
                                            data-disco_ssd='{$esc($disco_ssd)}'
                                            data-mac='{$esc($mac)}'
                                            data-observaciones='{$esc($observaciones)}'
                                            data-asignacion_gsb='{$esc($asignacion_gsb)}'
                                            data-pais='{$esc($pais)}'
                                            data-ciudad='{$esc($ciudad)}'
                                            data-asignacion_vp='{$esc($asignacion_vp)}'
                                            data-act_directory='{$esc($act_directory)}'
                                            data-mfa='{$esc($mfa)}'
                                            data-usuario='{$esc($usuario_act)}'
                                            data-observaciones2='{$esc($observaciones2)}'
                                            data-cargador='{$esc($cargador)}'
                                            data-cliente_azure='{$esc($cliente_azure)}'><i class='bi bi-pencil-fill'></i></button>";
                                echo "    <a href='generar_responsiva.php?id=$id' target='_blank' class='btn btn-light btn-sm rounded-circle' style='color: var(--brand-primary)' title='Carta Responsiva PDF'><i class='bi bi-file-earmark-pdf-fill'></i></a>";
                                echo "    <button onclick='confirmarEliminar($id, \"" . $esc($hostname) . "\")' class='btn btn-light btn-sm rounded-circle text-danger' title='Eliminar'><i class='bi bi-trash-fill'></i></button>";
                                echo "  </div>";
                                echo "</td>";

                                echo "<td>" . $id . "</td>";
                                echo "<td>" . $esc($marca) . "</td>";
                                echo "<td>" . $esc($modelo) . "</td>";
                                echo "<td><span style='color: var(--brand-primary); font-weight: 600; font-family: monospace;'>" . $esc($sn) . "</span></td>";
                                echo "<td><strong style='color: var(--brand-dark)'>" . $esc($hostname) . "</strong></td>";
                                echo "<td><strong>" . $esc($usuario_act) . "</strong></td>";

                                echo "<td class='text-center'>";
                                echo "  <div class='qr-container'>";
                                echo "    <div id='" . $idContenedor . "' data-qr-text='" . $attrQR . "'></div>";
                                echo "    <button type='button' onclick='descargarQR(\"" . $idContenedor . "\", \"" . $esc($hostname) . "\")' class='btn btn-qr-dim btn-sm mt-1 fw-bold' title='Descargar Código QR'><i class='bi bi-download'></i> QR</button>";
                                echo "  </div>";
                                echo "</td>";
                                echo "</tr>";

                                echo "<tr id='info_{$id}' class='collapse bg-light'>";
                                echo "  <td colspan='8' class='p-3'>";
                                echo "    <div class='card card-body border-0 shadow-sm p-3' style='background-color: #f8fafc; border-radius: 12px;'>";
                                echo "      <h6 class='fw-bold mb-3' style='color: var(--brand-primary);'><i class='bi bi-info-circle-fill me-2'></i>Detalles Especificaciones del Activo ID #{$id}</h6>";
                                echo "      <div class='row g-3 text-start' style='font-size: 0.88rem;'>";
                                echo "        <div class='col-md-4'><strong>Sistema Operativo:</strong> " . ($esc($sistema_op) ?: 'N/A') . "</div>";
                                echo "        <div class='col-md-4'><strong>Arquitectura:</strong> " . ($esc($arquitectura) ?: 'N/A') . "</div>";
                                echo "        <div class='col-md-4'><strong>Procesador:</strong> " . ($esc($procesador) ?: 'N/A') . "</div>";
                                echo "        <div class='col-md-4'><strong>RAM:</strong> " . ($esc($ram) ?: 'N/A') . "</div>";
                                echo "        <div class='col-md-4'><strong>Gráfico:</strong> " . ($esc($grafico) ?: 'N/A') . "</div>";
                                echo "        <div class='col-md-4'><strong>MAC Address:</strong> " . ($esc($mac) ?: 'N/A') . "</div>";
                                echo "        <div class='col-md-4'><strong>Disco Mecánico:</strong> " . ($esc($disco_mecanic) ?: 'N/A') . "</div>";
                                echo "        <div class='col-md-4'><strong>Disco SSD:</strong> " . ($esc($disco_ssd) ?: 'N/A') . "</div>";
                                echo "        <div class='col-md-4'><strong>Asignación GSB:</strong> " . ($esc($asignacion_gsb) ?: 'N/A') . "</div>";
                                echo "        <div class='col-md-4'><strong>País:</strong> " . ($esc($pais) ?: 'N/A') . "</div>";
                                echo "        <div class='col-md-4'><strong>Ciudad:</strong> " . ($esc($ciudad) ?: 'N/A') . "</div>";
                                echo "        <div class='col-md-4'><strong>Asignación VP:</strong> " . ($esc($asignacion_vp) ?: 'N/A') . "</div>";
                                echo "        <div class='col-md-4'><strong>Active Directory:</strong> " . ($esc($act_directory) ?: 'N/A') . "</div>";
                                echo "        <div class='col-md-4'><strong>MFA:</strong> " . ($esc($mfa) ?: 'N/A') . "</div>";
                                echo "        <div class='col-md-4'><strong>Cargador:</strong> " . ($esc($cargador) ?: 'N/A') . "</div>";
                                echo "        <div class='col-md-4'><strong>Cliente Azure:</strong> " . ($esc($cliente_azure) ?: 'N/A') . "</div>";
                                echo "        <div class='col-md-12 mt-2'><strong>Observaciones:</strong> " . ($esc($observaciones) ?: 'Sin observaciones') . "</div>";
                                echo "        <div class='col-md-12'><strong>Observaciones Adicionales:</strong> " . ($esc($observaciones2) ?: 'Sin observaciones adicionales') . "</div>";
                                echo "      </div>";
                                echo "    </div>";
                                echo "  </td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='8' class='text-center text-muted py-4'>No se registran activos actualmente en el sistema.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php } ?>

    <?php if ($vista == 'reportes') { ?>
        <div class="row mb-4 no-print">
            <div class="col">
                <h3 class="fw-bold m-0" style="color: var(--brand-primary);">Reportes Ejecutivos GSB</h3>
                <p class="text-muted small m-0">Consulta segmentada y métricas de cumplimiento.</p>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-4 mb-3">
                <div class="card card-kudy p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-uppercase text-muted fw-bold d-block" style="font-size: 11px;">Muestras en Reporte</span>
                            <h3 class="fw-bold m-0" style="color: var(--brand-dark)"><?php echo $reporte_total; ?> <small class="text-muted fs-6">equipos</small></h3>
                        </div>
                        <div class="stat-icon m-0"><i class="bi bi-clipboard2-data"></i></div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card card-kudy p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-uppercase text-muted fw-bold d-block" style="font-size: 11px;">Active Directory Activado</span>
                            <h3 class="fw-bold m-0" style="color: var(--brand-primary)"><?php echo $reporte_ad_si; ?> <small class="text-muted fs-6">equipos</small></h3>
                        </div>
                        <div class="stat-icon m-0"><i class="bi bi-shield-check"></i></div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card card-kudy p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-uppercase text-muted fw-bold d-block" style="font-size: 11px;">Cliente Azure Configurado</span>
                            <h3 class="fw-bold m-0" style="color: var(--brand-primary)"><?php echo $reporte_azure_si; ?> <small class="text-muted fs-6">equipos</small></h3>
                        </div>
                        <div class="stat-icon m-0"><i class="bi bi-cloud-check"></i></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card card-kudy p-3 p-md-4 mb-4 no-print">
            <h6 class="fw-bold mb-3" style="color: var(--brand-primary);"><i class="bi bi-funnel me-1"></i> Filtros de Segmentación</h6>
            <form action="index.php" method="GET" class="row g-3 align-items-center">
                <input type="hidden" name="vista" value="reportes">
                <div class="col-md-5">
                    <label class="form-label fw-bold text-secondary small">Filtrar por Fabricante/Marca:</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0" style="border-color: var(--brand-border); border-radius: 12px 0 0 12px;"><i class="bi bi-tag-fill text-muted"></i></span>
                        <select name="f_marca" class="form-select border-start-0" style="border-radius: 0 12px 12px 0;">
                            <option value="">-- Todos los Activos --</option>
                            <option value="LENOVO" <?php echo ($filtro_marca == 'LENOVO') ? 'selected' : ''; ?>>LENOVO</option>
                            <option value="HP" <?php echo ($filtro_marca == 'HP') ? 'selected' : ''; ?>>HP / Hewlett Packard</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-gsb-primary w-100 fw-bold"><i class="bi bi-search me-1"></i> Consultar Datos</button>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="button" onclick="window.print()" class="btn btn-outline-secondary w-100 fw-bold"><i class="bi bi-file-earmark-pdf me-1"></i> Guardar / Imprimir PDF</button>
                </div>
            </form>
        </div>

        <div class="card card-kudy p-3 p-md-4">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 mb-3">
                <h6 class="fw-bold m-0" style="color: var(--brand-primary);"><i class="bi bi-table me-1"></i> Detalle de Activos</h6>
                <span class="badge bg-light text-dark p-2 border fw-normal text-wrap no-print" style="border-radius: 12px; max-width: 100%;"><i class="bi bi-info-circle-fill text-info me-1"></i> Para ver las especificaciones de un equipo completo vaya a "Registro"</span>
            </div>
            
            <div class="table-responsive">
    <div class="table-responsive">
    <table class="table align-middle table-excel-mode table-reportes">
        <thead>
            <tr>
                <th class="text-center col-fit">ID</th>
                <th class="text-center" style="width: 15%;">Hostname</th> 
                <th class="text-center" style="width: 12%;">Marca</th> 
                <th class="text-center" style="width: 18%;">Modelo</th>
                <th class="text-center" style="width: 18%;">Número de Serie (S/N)</th>
                <th class="text-center col-fit">AD</th>
                <th class="text-center col-fit">Azure</th>
                <th style="width: 25%;">Usuario Asignado</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if (!empty($reportes)) {
                foreach($reportes as $row) {
                    $row_upper = array_change_key_case($row, CASE_UPPER);
                    $sn = isset($row_upper['S/N']) ? $row_upper['S/N'] : (isset($row_upper['SERIE']) ? $row_upper['SERIE'] : 'N/A');
                    $ad = isset($row_upper['ACTIVE DIRECTORY']) ? strtoupper(trim($row_upper['ACTIVE DIRECTORY'])) : '';
                    $azure = isset($row_upper['CLIENTE AZURE']) ? strtoupper(trim($row_upper['CLIENTE AZURE'])) : '';
                    
                    echo "<tr>";
                    echo "<td class='text-center text-muted fw-bold'>" . $row['id'] . "</td>";
                    echo "<td class='text-center'><span class='badge badge-kudy-success font-monospace'>" . htmlspecialchars($row_upper['HOSTNAME'] ?? 'N/A') . "</span></td>";
                    echo "<td class='text-center fw-bold'>" . htmlspecialchars($row_upper['MARCA'] ?? 'N/A') . "</td>";
                    echo "<td class='text-center'>" . htmlspecialchars($row_upper['MODELO'] ?? 'N/A') . "</td>";
                    echo "<td class='text-center'><span class='badge bg-light text-dark border p-2 font-monospace' style='border-radius:10px;'>" . htmlspecialchars(trim($sn)) . "</span></td>";
                    
                    echo "<td class='text-center'>";
                    echo ($ad == 'SI') ? '<span class="badge badge-kudy-success">SI</span>' : '<span class="badge badge-kudy-danger">NO</span>';
                    echo "</td>";

                    echo "<td class='text-center'>";
                    echo ($azure == 'SI') ? '<span class="badge badge-kudy-success">SI</span>' : '<span class="badge badge-kudy-danger">NO</span>';
                    echo "</td>";

                    echo "<td><strong>" . htmlspecialchars($row_upper['USUARIO ACTUAL'] ?? 'N/A') . "</strong></td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='8' class='text-center py-4 text-muted fs-6'><i class='bi bi-exclamation-circle-fill text-warning me-2'></i> No se localizaron registros bajo los criterios elegidos.</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>
</div>
        </div>
    <?php } ?>

</div>

<!-- MODAL REGISTRAR -->
<div class="modal fade" id="modalRegistrar" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title fw-bold"><i class="bi bi-layers-half me-2"></i> Panel de Alta de Activos GSB</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
        
        <h6 class="fw-bold mb-2" style="color: var(--brand-primary);"><i class="bi bi-file-earmark-excel me-1"></i> Opción A: Carga masiva desde Excel (.xlsx / .csv)</h6>
        <p class="text-muted small">Asegúrate de que las columnas coincidan con las del formato de inventario global.</p>
        <form action="index.php" method="POST" enctype="multipart/form-data" class="p-3 rounded-4 mb-4" style="background-color: rgba(92, 202, 142, 0.15);">
            <input type="hidden" name="accion_csv" value="1">
            <div class="mb-3">
                <input type="file" name="archivo_excel" class="form-control" accept=".csv, .xlsx" required>
            </div>
            <button type="submit" class="btn btn-gsb-success w-100 fw-bold"><i class="bi bi-cloud-arrow-up-fill me-1"></i> Importar Archivo de Datos</button>
        </form>

        <hr class="text-muted opacity-25">

        <h6 class="fw-bold mb-3" style="color: var(--brand-primary);"><i class="bi bi-pencil-square me-1"></i> Opción B: Formulario de Registro Individual</h6>
        <form action="index.php" method="POST">
            <input type="hidden" name="accion_registrar" value="1">
            
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label mb-1 small fw-bold text-muted">Hostname</label>
                    <input type="text" name="hostname" class="form-control" placeholder="Ej: GSBLMEXW11R66P" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label mb-1 small fw-bold text-muted">Marca</label>
                    <input type="text" name="marca" class="form-control" placeholder="Ej: LENOVO" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label mb-1 small fw-bold text-muted">Modelo</label>
                    <input type="text" name="modelo" class="form-control" placeholder="Ej: 82C5" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label mb-1 small fw-bold text-muted">Número de Serie (S/N)</label>
                    <input type="text" name="sn" class="form-control" placeholder="Ej: PF31R66P" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label mb-1 small fw-bold text-muted">Sistema Operativo</label>
                    <input type="text" name="sistema_op" class="form-control" placeholder="Ej: Windows 11 Pro">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label mb-1 small fw-bold text-muted">Arquitectura</label>
                    <input type="text" name="arquitectura" class="form-control" placeholder="Ej: x64">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label mb-1 small fw-bold text-muted">Procesador</label>
                    <input type="text" name="procesador" class="form-control" placeholder="Ej: Intel Core i5">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label mb-1 small fw-bold text-muted">RAM</label>
                    <input type="text" name="ram" class="form-control" placeholder="Ej: 16 GB">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label mb-1 small fw-bold text-muted">Gráfico</label>
                    <input type="text" name="grafico" class="form-control" placeholder="Ej: Intel Iris Xe">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label mb-1 small fw-bold text-muted">Disco Mecánico</label>
                    <input type="text" name="disco_mecanico" class="form-control" placeholder="Ej: N/A">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label mb-1 small fw-bold text-muted">Disco SSD</label>
                    <input type="text" name="disco_ssd" class="form-control" placeholder="Ej: 512 GB">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label mb-1 small fw-bold text-muted">MAC Address</label>
                    <input type="text" name="mac" class="form-control" placeholder="Ej: AA:BB:CC:DD:EE:FF">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label mb-1 small fw-bold text-muted">Asignación GSB</label>
                    <select name="asignacion_gsb" class="form-select">
                        <option value="SI">SI</option>
                        <option value="NO" selected>NO</option>
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label mb-1 small fw-bold text-muted">País</label>
                    <input type="text" name="pais" class="form-control" placeholder="Ej: México">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label mb-1 small fw-bold text-muted">Ciudad</label>
                    <input type="text" name="ciudad" class="form-control" placeholder="Ej: Monterrey">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label mb-1 small fw-bold text-muted">Asignación VP</label>
                    <input type="text" name="asignacion_vp" class="form-control" placeholder="Ej: Dirección">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label mb-1 small fw-bold text-muted">Active Directory</label>
                    <select name="act_directory" class="form-select">
                        <option value="SI">SI</option>
                        <option value="NO" selected>NO</option>
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label mb-1 small fw-bold text-muted">MFA</label>
                    <input type="text" name="mfa" class="form-control" placeholder="Ej: Activado">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label mb-1 small fw-bold text-muted">Usuario Actual</label>
                    <input type="text" name="usuario" class="form-control" placeholder="Ej: John Doe">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label mb-1 small fw-bold text-muted">Cargador</label>
                    <input type="text" name="cargador" class="form-control" placeholder="Ej: USB-C 65W">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label mb-1 small fw-bold text-muted">Cliente Azure</label>
                    <select name="cliente_azure" class="form-select">
                        <option value="SI">SI</option>
                        <option value="NO" selected>NO</option>
                    </select>
                </div>
                <div class="col-md-12 mb-3">
                    <label class="form-label mb-1 small fw-bold text-muted">Observaciones</label>
                    <textarea name="observaciones" class="form-control" rows="1" placeholder="Ej: Detalles físicos del equipo..."></textarea>
                </div>
                <div class="col-md-12 mb-3">
                    <label class="form-label mb-1 small fw-bold text-muted">Observaciones Adicionales (OBSERVACIONES2)</label>
                    <textarea name="observaciones2" class="form-control" rows="1" placeholder="Ej: Comentarios extras..."></textarea>
                </div>
            </div>

            <button type="submit" class="btn btn-gsb-primary w-100 mt-2 fw-bold"><i class="bi bi-save-fill me-1"></i> Guardar Activo Único</button>
        </form>

      </div>
    </div>
  </div>
</div>

<!-- MODAL EDITAR -->
<div class="modal fade" id="modalEditar" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title fw-bold"><i class="bi bi-pencil-square me-2"></i> Editar Registro Activo</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
        <form action="index.php" method="POST">
            <input type="hidden" name="accion_editar" value="1">
            <input type="hidden" name="id" id="edit_id"> 
            
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label mb-1 small fw-bold text-muted">Hostname</label>
                    <input type="text" name="hostname" id="edit_hostname" class="form-control" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label mb-1 small fw-bold text-muted">Marca</label>
                    <input type="text" name="marca" id="edit_marca" class="form-control" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label mb-1 small fw-bold text-muted">Modelo</label>
                    <input type="text" name="modelo" id="edit_modelo" class="form-control" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label mb-1 small fw-bold text-muted">Número de Serie (S/N)</label>
                    <input type="text" name="sn" id="edit_sn" class="form-control" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label mb-1 small fw-bold text-muted">Sistema Operativo</label>
                    <input type="text" name="sistema_op" id="edit_sistema_op" class="form-control">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label mb-1 small fw-bold text-muted">Arquitectura</label>
                    <input type="text" name="arquitectura" id="edit_arquitectura" class="form-control">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label mb-1 small fw-bold text-muted">Procesador</label>
                    <input type="text" name="procesador" id="edit_procesador" class="form-control">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label mb-1 small fw-bold text-muted">RAM</label>
                    <input type="text" name="ram" id="edit_ram" class="form-control">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label mb-1 small fw-bold text-muted">Gráfico</label>
                    <input type="text" name="grafico" id="edit_grafico" class="form-control">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label mb-1 small fw-bold text-muted">Disco Mecánico</label>
                    <input type="text" name="disco_mecanico" id="edit_disco_mecanico" class="form-control">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label mb-1 small fw-bold text-muted">Disco SSD</label>
                    <input type="text" name="disco_ssd" id="edit_disco_ssd" class="form-control">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label mb-1 small fw-bold text-muted">MAC Address</label>
                    <input type="text" name="mac" id="edit_mac" class="form-control">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label mb-1 small fw-bold text-muted">Asignación GSB</label>
                    <select name="asignacion_gsb" id="edit_asignacion_gsb" class="form-select">
                        <option value="SI">SI</option>
                        <option value="NO">NO</option>
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label mb-1 small fw-bold text-muted">País</label>
                    <input type="text" name="pais" id="edit_pais" class="form-control">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label mb-1 small fw-bold text-muted">Ciudad</label>
                    <input type="text" name="ciudad" id="edit_ciudad" class="form-control">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label mb-1 small fw-bold text-muted">Asignación VP</label>
                    <input type="text" name="asignacion_vp" id="edit_asignacion_vp" class="form-control">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label mb-1 small fw-bold text-muted">Active Directory</label>
                    <select name="act_directory" id="edit_act_directory" class="form-select">
                        <option value="SI">SI</option>
                        <option value="NO">NO</option>
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label mb-1 small fw-bold text-muted">MFA</label>
                    <input type="text" name="mfa" id="edit_mfa" class="form-control">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label mb-1 small fw-bold text-muted">Usuario Actual</label>
                    <input type="text" name="usuario" id="edit_usuario" class="form-control">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label mb-1 small fw-bold text-muted">Cargador</label>
                    <input type="text" name="cargador" id="edit_cargador" class="form-control">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label mb-1 small fw-bold text-muted">Cliente Azure</label>
                    <select name="cliente_azure" id="edit_cliente_azure" class="form-select">
                        <option value="SI">SI</option>
                        <option value="NO">NO</option>
                    </select>
                </div>
                <div class="col-md-12 mb-3">
                    <label class="form-label mb-1 small fw-bold text-muted">Observaciones</label>
                    <textarea name="observaciones" id="edit_observaciones" class="form-control" rows="1"></textarea>
                </div>
                <div class="col-md-12 mb-3">
                    <label class="form-label mb-1 small fw-bold text-muted">Observaciones Adicionales (OBSERVACIONES2)</label>
                    <textarea name="observaciones2" id="edit_observaciones2" class="form-control" rows="1"></textarea>
                </div>
            </div>

            <button type="submit" class="btn btn-gsb-primary w-100 mt-2 fw-bold"><i class="bi bi-save-fill me-1"></i> Guardar Cambios</button>
        </form>
      </div>
    </div>
  </div>
</div>

</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // 1. GENERACIÓN DE CÓDIGOS QR
    setTimeout(() => {
        if (typeof QRCode === 'undefined') {
            console.error("La librería QRCode.js no está cargada.");
            return;
        }

        document.querySelectorAll('[data-qr-text]').forEach((el) => {
            let texto = el.getAttribute('data-qr-text');

            if (texto && texto.trim() !== '') {
                texto = texto.replace(/[\x00-\x1F\x7F-\x9F]/g, "").trim();
                
                function generarCodigo(stringTexto, nivelCorreccion) {
                    const tempDiv = document.createElement('div');
                    new QRCode(tempDiv, {
                        text: stringTexto,
                        width: 80,
                        height: 80,
                        colorDark : '#002D5D',
                        colorLight : '#ffffff',
                        correctLevel : nivelCorreccion
                    });
                    
                    el.innerHTML = '';
                    while (tempDiv.firstChild) {
                        el.appendChild(tempDiv.firstChild);
                    }
                }

                try {
                    generarCodigo(texto, QRCode.CorrectLevel.L);
                } catch (e) {
                    try {
                        const textoRecortado = texto.substring(0, 100);
                        generarCodigo(textoRecortado, QRCode.CorrectLevel.L);
                    } catch (errRecorte) {
                        try {
                            const textoUltraRecortado = texto.substring(0, 60);
                            generarCodigo(textoUltraRecortado, QRCode.CorrectLevel.L);
                        } catch (errFinal) {
                            console.error("Error final al renderizar QR en #" + el.id, errFinal);
                            el.innerHTML = '<span class="text-danger" style="font-size: 10px;">Error QR</span>';
                        }
                    }
                }
            } else {
                el.innerHTML = '<span class="text-muted" style="font-size: 10px;">Sin datos</span>';
            }
        });
    }, 100);

    // 2. POPULAR MODAL EDITAR
    document.querySelectorAll('.btn-editar').forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('edit_id').value = this.dataset.id || '';
            document.getElementById('edit_marca').value = this.dataset.marca || '';
            document.getElementById('edit_modelo').value = this.dataset.modelo || '';
            document.getElementById('edit_sn').value = this.dataset.sn || '';
            document.getElementById('edit_sistema_op').value = this.dataset.sistema_op || '';
            document.getElementById('edit_arquitectura').value = this.dataset.arquitectura || '';
            document.getElementById('edit_hostname').value = this.dataset.hostname || '';
            document.getElementById('edit_procesador').value = this.dataset.procesador || '';
            document.getElementById('edit_ram').value = this.dataset.ram || '';
            document.getElementById('edit_grafico').value = this.dataset.grafico || '';
            document.getElementById('edit_disco_mecanico').value = this.dataset.disco_mecanico || '';
            document.getElementById('edit_disco_ssd').value = this.dataset.disco_ssd || '';
            document.getElementById('edit_mac').value = this.dataset.mac || '';
            document.getElementById('edit_observaciones').value = this.dataset.observaciones || '';
            document.getElementById('edit_asignacion_gsb').value = this.dataset.asignacion_gsb || 'NO';
            document.getElementById('edit_pais').value = this.dataset.pais || '';
            document.getElementById('edit_ciudad').value = this.dataset.ciudad || '';
            document.getElementById('edit_asignacion_vp').value = this.dataset.asignacion_vp || '';
            document.getElementById('edit_act_directory').value = this.dataset.act_directory || 'NO';
            document.getElementById('edit_mfa').value = this.dataset.mfa || '';
            document.getElementById('edit_usuario').value = this.dataset.usuario || '';
            document.getElementById('edit_observaciones2').value = this.dataset.observaciones2 || '';
            document.getElementById('edit_cargador').value = this.dataset.cargador || '';
            document.getElementById('edit_cliente_azure').value = this.dataset.cliente_azure || 'NO';

            const modalEditar = new bootstrap.Modal(document.getElementById('modalEditar'));
            modalEditar.show();
        });
    });
});

// 3. DESCARGA DE QR COMPATIBLE CON CANVAS E IMÁGENES
function descargarQR(contenedorId, hostname) {
    const contenedor = document.getElementById(contenedorId);
    if (!contenedor) {
        alert("No se encontró el contenedor del código QR.");
        return;
    }

    const canvas = contenedor.querySelector('canvas');
    const img = contenedor.querySelector('img');

    let dataURL = null;

    if (canvas) {
        dataURL = canvas.toDataURL("image/png");
    } else if (img && img.src) {
        dataURL = img.src;
    }

    if (dataURL) {
        const link = document.createElement('a');
        const nombreArchivo = (hostname && hostname.trim() !== '' ? hostname.trim() : 'QR_Equipo') + '.png';
        link.download = nombreArchivo;
        link.href = dataURL;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    } else {
        alert("El código QR aún no se ha generado por completo o no contiene una imagen válida.");
    }
}

// 4. CONFIRMAR ELIMINACIÓN DE REGISTRO
function confirmarEliminar(id, hostname) {
    if (confirm('¿Estás seguro de que deseas eliminar el equipo "' + hostname + '" (ID: ' + id + ')? Esta acción no se puede deshacer.')) {
        window.location.href = 'index.php?vista=registro&accion=eliminar&id=' + id;
    }
}
</script>

</body>
</html>