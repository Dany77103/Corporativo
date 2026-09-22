<?php
session_start();

// 1. GUARDIÁN DE SEGURIDAD
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'admin') {
    die("Acceso no autorizado.");
}

// 2. VALIDAR ID DEL EQUIPO
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Error: No se especificó ningún equipo.");
}

$id_equipo = intval($_GET['id']);

// 3. CONEXIÓN A LA BASE DE DATOS
$host     = "localhost";      
$user     = "root";           
$password = "";    
$database = "proyecto"; 

$conn = new mysqli($host, $user, $password, $database);
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// 4. CONSULTAR DATOS DEL EQUIPO
$sql = "SELECT * FROM equipos WHERE id = $id_equipo";
$res = $conn->query($sql);

if (!$res || $res->num_rows === 0) {
    die("Error: Equipo no encontrado.");
}

$equipo = $res->fetch_assoc();
$e = array_change_key_case($equipo, CASE_UPPER);

// Extraer campos de la base de datos
$hostname      = !empty($e['HOSTNAME']) ? $e['HOSTNAME'] : 'N/A';
$marca         = !empty($e['MARCA']) ? $e['MARCA'] : 'N/A';
$modelo        = !empty($e['MODELO']) ? $e['MODELO'] : 'N/A';
$sn            = !empty($e['S/N']) ? $e['S/N'] : (!empty($e['SERIE']) ? $e['SERIE'] : 'N/A');
$procesador    = !empty($e['PROCESADOR']) ? $e['PROCESADOR'] : '';
$ram           = !empty($e['RAM']) ? $e['RAM'] : '';
$disco         = !empty($e['DISCO SSD']) ? $e['DISCO SSD'] : (!empty($e['DISCO MECANICO']) ? $e['DISCO MECANICO'] : '');
$usuario       = !empty($e['USUARIO ACTUAL']) ? $e['USUARIO ACTUAL'] : (!empty($e['USUARIO']) ? $e['USUARIO'] : '___________________________');
$cargador      = !empty($e['CARGADOR']) ? $e['CARGADOR'] : 'Incluido';
$mouse         = !empty($e['MOUSE']) ? $e['MOUSE'] : 'N/A';
$diadema       = !empty($e['DIADEMA']) ? $e['DIADEMA'] : 'N/A';
$observaciones = !empty($e['OBSERVACIONES']) ? $e['OBSERVACIONES'] : (!empty($e['COMENTARIOS']) ? $e['COMENTARIOS'] : '');
$f_fecha       = date("d/m/Y");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carta Responsiva - <?php echo htmlspecialchars($hostname); ?></title>
    <!-- Bootstrap CSS e Iconos -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --dark-teal: #01414e;     
            --bright-teal: #00a89e;   
            --bg-even: #f1f5f8;       
            --bg-odd: #ffffff;        
            --text-dark: #1e293b;      
            --border-gap: 2px;        
        }

        *, *::before, *::after {
            box-sizing: border-box !important;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        body { 
            font-family: 'Roboto', -apple-system, BlinkMacSystemFont, Arial, sans-serif; 
            background-color: #f8fafc; 
            color: var(--text-dark);
            margin: 0;
            padding: 0;
        }

        /* CONTENEDOR AJUSTABLE AL 100% PARA EVITAR RECORTE Y APLANAMIENTO */
        .carta-box { 
            width: 100% !important;
            max-width: 760px !important; 
            margin: 0 auto !important; 
            background: #ffffff; 
            padding: 15px 20px !important; 
            box-shadow: 0 2px 10px rgba(0,0,0,0.05); 
            overflow: hidden !important;
        }

        /* ELIMINACIÓN DE BORDES PARÁSITOS LATERALES */
        .legal-text, .table-card, .border, .p-2, .signature-box, table, td, th {
            border-left: none !important;
            border-right: none !important;
            outline: none !important;
        }

        .table-card {
            margin-bottom: 0.6rem;
            background-color: transparent;
            width: 100%;
        }

        .table-section-title {
            background-color: var(--dark-teal);
            color: #ffffff;
            font-weight: 700;
            font-size: 0.8rem;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            padding: 5px 10px;
            margin-bottom: 0;
            border: none !important;
        }

        .table-custom {
            font-size: 0.78rem;
            margin-bottom: 0;
            table-layout: fixed;
            width: 100%;
            border-collapse: separate !important;
            border-spacing: var(--border-gap) 0 !important;
        }

        .table-custom th {
            background-color: var(--dark-teal);
            color: #ffffff;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 0.72rem;
            padding: 5px 4px;
            text-align: center;
            border: none !important;
        }

        .table-custom th.th-teal {
            background-color: var(--bright-teal);
        }

        .table-custom td {
            word-wrap: break-word;
            vertical-align: middle;
            padding: 5px 6px;
            color: var(--text-dark);
            font-weight: 500;
            text-align: left;
            border: none !important;
        }

        .table-custom tbody tr td:first-child {
            background-color: var(--dark-teal) !important;
            color: #ffffff !important;
            font-weight: 700;
            text-align: left;
            padding-left: 8px;
        }

        .table-custom tbody tr:nth-child(odd) td:not(:first-child) {
            background-color: var(--bg-odd);
        }

        .table-custom tbody tr:nth-child(even) td:not(:first-child) {
            background-color: var(--bg-even);
        }

        .row-observaciones td:first-child {
            background-color: var(--dark-teal) !important;
            color: #ffffff !important;
        }
        .row-observaciones td:not(:first-child) {
            background-color: #e6f4f1 !important;
            color: var(--dark-teal) !important;
            font-weight: 600 !important;
            text-align: left !important;
            padding-left: 8px !important;
        }

        /* TEXTO DECLARATORIO */
        .legal-text {
            font-size: 0.75rem;
            text-align: justify;
            line-height: 1.35;
            background-color: var(--bg-even);
            border: none !important;
            padding: 6px 10px;
            border-radius: 4px;
        }

        .signature-box {
            border-top: 1.5px solid var(--dark-teal) !important;
            width: 85%;
            margin: 0 auto;
            padding-top: 4px;
        }
        
        .editable {
            transition: background-color 0.2s ease;
            border-radius: 2px;
            padding: 1px 3px;
        }
        .editable:hover {
            background-color: rgba(0, 168, 158, 0.15);
            cursor: text;
        }
        .editable:focus {
            outline: 1px solid var(--bright-teal);
            background-color: #ffffff;
            color: #000000 !important;
        }

        .tramite-option {
            cursor: pointer;
            user-select: none;
        }

        /* EVIDENCIAS FOTOGRÁFICAS */
        .box-evidencia {
            background-color: var(--bg-even);
            min-height: 60px;
            padding: 6px;
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            align-items: center;
            gap: 8px;
            border: none !important;
        }

        .foto-preview-container {
            position: relative;
            width: calc(23% - 8px);
            min-width: 80px;
        }

        .foto-preview-container img {
            max-height: 70px;
            width: 100%;
            border: 1px solid #cbd5e1;
            border-radius: 3px;
            object-fit: contain;
            background-color: #ffffff;
            padding: 2px;
        }

        @media print {
            .no-print { display: none !important; }
            body { background-color: #ffffff; }
            .carta-box { box-shadow: none; padding: 0 !important; margin: 0 !important; max-width: 100% !important; }
        }
    </style>
</head>
<body>

    <!-- BOTONES DE ACCIÓN -->
    <div class="container no-print text-center my-3">
        <button onclick="window.print()" class="btn btn-dark fw-bold me-2 shadow-sm" style="background-color: var(--dark-teal);">
            <i class="bi bi-printer me-1"></i> Imprimir
        </button>
        
        <button onclick="descargarPDF()" class="btn btn-info fw-bold text-white shadow-sm" style="background-color: var(--bright-teal); border: none;">
            <i class="bi bi-file-earmark-pdf me-1"></i> Descargar como PDF
        </button>
    </div>

    <!-- DOCUMENTO RESPONSIVA -->
    <div class="carta-box" id="documento-responsiva">
        
        <!-- ENCABEZADO -->
        <div class="row align-items-center mb-2">
            <div class="col-7">
                <h4 class="fw-bold m-0 text-uppercase" style="color: var(--dark-teal); font-size: 1.1rem;">GSB - CARTA RESPONSIVA</h4>
                <small class="text-muted fw-semibold" style="font-size: 0.75rem;">Control de Entregas y Resguardo de Equipos</small>
            </div>
            <div class="col-5 text-end">
                <div class="p-2 rounded bg-light d-inline-block text-start" style="font-size: 0.75rem; border-top: 1px solid #e2e8f0; border-bottom: 1px solid #e2e8f0;">
                    <div><strong>Fecha:</strong> <span class="editable" contenteditable="true"><?php echo $f_fecha; ?></span></div>
                    <div class="mt-1">
                        <span class="me-2 tramite-option" onclick="seleccionarTramite(this)">
                            <i class="bi bi-check-square-fill icono-tramite" style="color: var(--bright-teal);"></i> <strong>Inducción</strong>
                        </span>
                        <span class="tramite-option" onclick="seleccionarTramite(this)">
                            <i class="bi bi-square icono-tramite"></i> <strong>Retiro de equipo</strong>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- DATOS DEL USUARIO -->
        <div class="mb-2 p-2 rounded bg-light d-flex align-items-center" style="font-size: 0.8rem; border-top: 1px solid #e2e8f0; border-bottom: 1px solid #e2e8f0;">
            <i class="bi bi-person-circle fs-6 me-2" style="color: var(--bright-teal);"></i>
            <div>
                <strong>Nombre de Usuario:</strong> 
                <span class="fw-bold ms-1 editable" contenteditable="true" id="campo-usuario" style="color: var(--dark-teal);"><?php echo htmlspecialchars($usuario); ?></span>
            </div>
        </div>

        <!-- TEXTO DECLARATORIO -->
        <div class="legal-text mb-2">
            Por este medio hago constatar que se me entrega(n) el(los) equipo(s) que se enlista(n) en este documento, funcionando de forma óptima. El cual me comprometo a cuidar, mantener en buen estado y utilizarlo única y exclusivamente para asuntos relacionados con mi actividad laboral. En caso de su extravío, daño o uso inadecuado, me responsabilizo a pagar el costo de reparación o la reposición del equipo de ser necesario.
        </div>

        <!-- SECCIÓN 1: LAPTOP / CÓMPUTO -->
        <div class="table-card">
            <div class="table-section-title text-center">
                <i class="bi bi-laptop me-1"></i> Laptop / Equipo de Cómputo
            </div>
            <table class="table table-custom align-middle">
                <colgroup>
                    <col style="width: 20%;">
                    <col style="width: 16%;">
                    <col style="width: 18%;">
                    <col style="width: 16%;">
                    <col style="width: 15%;">
                    <col style="width: 15%;">
                </colgroup>
                <thead>
                    <tr>
                        <th>Equipo</th>
                        <th class="th-teal">Marca</th>
                        <th class="th-teal">Modelo</th>
                        <th class="th-teal">Serie</th>
                        <th class="th-teal">Hostname</th>
                        <th class="th-teal">Detalle / Cap.</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>* Laptop</td>
                        <td class="editable" contenteditable="true"><?php echo htmlspecialchars($marca); ?></td>
                        <td class="editable" contenteditable="true"><?php echo htmlspecialchars($modelo); ?></td>
                        <td class="editable" contenteditable="true"><?php echo htmlspecialchars($sn); ?></td>
                        <td class="editable" contenteditable="true" id="campo-hostname"><?php echo htmlspecialchars($hostname); ?></td>
                        <td class="editable" contenteditable="true">INCLUIDO</td>
                    </tr>
                    <tr>
                        <td>* Disco Duro</td>
                        <td class="editable" contenteditable="true">N/A</td>
                        <td class="editable" contenteditable="true">N/A</td>
                        <td class="editable" contenteditable="true">N/A</td>
                        <td class="editable" contenteditable="true">N/A</td>
                        <td class="editable" contenteditable="true"><?php echo htmlspecialchars($disco); ?></td>
                    </tr>
                    <tr>
                        <td>* RAM</td>
                        <td class="editable" contenteditable="true">N/A</td>
                        <td class="editable" contenteditable="true">N/A</td>
                        <td class="editable" contenteditable="true">N/A</td>
                        <td class="editable" contenteditable="true">N/A</td>
                        <td class="editable" contenteditable="true"><?php echo htmlspecialchars($ram); ?></td>
                    </tr>
                    <tr>
                        <td>* Procesador</td>
                        <td class="editable" contenteditable="true">N/A</td>
                        <td class="editable" contenteditable="true">N/A</td>
                        <td class="editable" contenteditable="true">N/A</td>
                        <td class="editable" contenteditable="true">N/A</td>
                        <td class="editable" contenteditable="true"><?php echo htmlspecialchars($procesador); ?></td>
                    </tr>
                    <tr>
                        <td>* Cargador</td>
                        <td class="editable" contenteditable="true">N/A</td>
                        <td class="editable" contenteditable="true">N/A</td>
                        <td class="editable" contenteditable="true">N/A</td>
                        <td class="editable" contenteditable="true">N/A</td>
                        <td class="editable" contenteditable="true"><?php echo htmlspecialchars($cargador); ?></td>
                    </tr>
                    <tr>
                        <td>* Mouse</td>
                        <td class="editable" contenteditable="true">N/A</td>
                        <td class="editable" contenteditable="true">N/A</td>
                        <td class="editable" contenteditable="true">N/A</td>
                        <td class="editable" contenteditable="true">N/A</td>
                        <td class="editable" contenteditable="true"><?php echo htmlspecialchars($mouse); ?></td>
                    </tr>
                    <tr>
                        <td>* Diadema</td>
                        <td class="editable" contenteditable="true">N/A</td>
                        <td class="editable" contenteditable="true">N/A</td>
                        <td class="editable" contenteditable="true">N/A</td>
                        <td class="editable" contenteditable="true">N/A</td>
                        <td class="editable" contenteditable="true"><?php echo htmlspecialchars($diadema); ?></td>
                    </tr>
                    <tr class="row-observaciones">
                        <td>Observaciones:</td>
                        <td colspan="5" class="editable" contenteditable="true"><?php echo htmlspecialchars($observaciones); ?></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- EVIDENCIA CÓMPUTO -->
        <div class="table-card">
            <div class="table-section-title d-flex justify-content-between align-items-center">
                <span><i class="bi bi-camera me-1"></i> Evidencia Fotográfica (Laptop / Cómputo)</span>
                <button type="button" class="btn btn-sm btn-light py-0 px-2 fw-bold no-print" onclick="document.getElementById('input-fotos-computo').click()" style="font-size: 0.7rem; color: var(--dark-teal);">
                    <i class="bi bi-upload"></i> Agregar Imagen
                </button>
            </div>
            <input type="file" id="input-fotos-computo" accept="image/*" multiple class="d-none" onchange="cargarFotosGenerico(event, 'contenedor-fotos-computo', 'msg-sin-fotos-computo')">
            <div class="box-evidencia" id="contenedor-fotos-computo">
                <span class="text-muted small no-print" id="msg-sin-fotos-computo" style="font-size: 0.72rem;">Sin imágenes adjuntas.</span>
            </div>
        </div>

        <!-- SECCIÓN 2: CELULAR -->
        <div class="table-card">
            <div class="table-section-title text-center">
                <i class="bi bi-phone me-1"></i> Celular
            </div>
            <table class="table table-custom align-middle">
                <colgroup>
                    <col style="width: 20%;">
                    <col style="width: 26%;">
                    <col style="width: 27%;">
                    <col style="width: 27%;">
                </colgroup>
                <thead>
                    <tr>
                        <th>Equipo</th>
                        <th class="th-teal">Marca</th>
                        <th class="th-teal">Modelo</th>
                        <th class="th-teal">Serie / IMEI</th>
                    </tr>
                </thead>
                <tbody>
                    <tr><td>* Celular</td><td class="editable" contenteditable="true">N/A</td><td class="editable" contenteditable="true">N/A</td><td class="editable" contenteditable="true">N/A</td></tr>
                    <tr><td>* Cargador</td><td class="editable" contenteditable="true">N/A</td><td class="editable" contenteditable="true">N/A</td><td class="editable" contenteditable="true">N/A</td></tr>
                    <tr><td>* Manos libres</td><td class="editable" contenteditable="true">N/A</td><td class="editable" contenteditable="true">N/A</td><td class="editable" contenteditable="true">N/A</td></tr>
                    <tr><td>* Memoria ext.</td><td class="editable" contenteditable="true">N/A</td><td class="editable" contenteditable="true">N/A</td><td class="editable" contenteditable="true">N/A</td></tr>
                    <tr class="row-observaciones">
                        <td>Observaciones:</td>
                        <td colspan="3" class="editable" contenteditable="true"></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- EVIDENCIA CELULAR -->
        <div class="table-card">
            <div class="table-section-title d-flex justify-content-between align-items-center">
                <span><i class="bi bi-camera me-1"></i> Evidencia Fotográfica (Celular)</span>
                <button type="button" class="btn btn-sm btn-light py-0 px-2 fw-bold no-print" onclick="document.getElementById('input-fotos-celular').click()" style="font-size: 0.7rem; color: var(--dark-teal);">
                    <i class="bi bi-upload"></i> Agregar Imagen
                </button>
            </div>
            <input type="file" id="input-fotos-celular" accept="image/*" multiple class="d-none" onchange="cargarFotosGenerico(event, 'contenedor-fotos-celular', 'msg-sin-fotos-celular')">
            <div class="box-evidencia" id="contenedor-fotos-celular">
                <span class="text-muted small no-print" id="msg-sin-fotos-celular" style="font-size: 0.72rem;">Sin imágenes adjuntas.</span>
            </div>
        </div>

        <!-- SECCIÓN 3: NO-BREAK / USB / OTROS -->
        <div class="table-card">
            <div class="table-section-title text-center">
               <i class="bi bi-plug me-1"></i> No-Break / USB / Otros
            </div>
            <table class="table table-custom align-middle">
                <colgroup>
                    <col style="width: 20%;">
                    <col style="width: 40%;">
                    <col style="width: 40%;">
                </colgroup>
                <thead>
                    <tr>
                        <th>Equipo</th>
                        <th class="th-teal">Marca</th>
                        <th class="th-teal">Serie / Detalle</th>
                    </tr>
                </thead>
                <tbody>
                    <tr><td>* No Break</td><td class="editable" contenteditable="true">N/A</td><td class="editable" contenteditable="true">N/A</td></tr>
                    <tr><td>* USB</td><td class="editable" contenteditable="true">N/A</td><td class="editable" contenteditable="true">N/A</td></tr>
                    <tr><td>* Otros</td><td class="editable" contenteditable="true">N/A</td><td class="editable" contenteditable="true">N/A</td></tr>
                    <tr class="row-observaciones">
                        <td>Comentarios:</td>
                        <td colspan="2" class="editable" contenteditable="true"></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- EVIDENCIA NO-BREAK / OTROS -->
        <div class="table-card">
            <div class="table-section-title d-flex justify-content-between align-items-center">
                <span><i class="bi bi-camera me-1"></i> Evidencia Fotográfica (No-Break / USB / Otros)</span>
                <button type="button" class="btn btn-sm btn-light py-0 px-2 fw-bold no-print" onclick="document.getElementById('input-fotos-otros').click()" style="font-size: 0.7rem; color: var(--dark-teal);">
                    <i class="bi bi-upload"></i> Agregar Imagen
                </button>
            </div>
            <input type="file" id="input-fotos-otros" accept="image/*" multiple class="d-none" onchange="cargarFotosGenerico(event, 'contenedor-fotos-otros', 'msg-sin-fotos-otros')">
            <div class="box-evidencia" id="contenedor-fotos-otros">
                <span class="text-muted small no-print" id="msg-sin-fotos-otros" style="font-size: 0.72rem;">Sin imágenes adjuntas.</span>
            </div>
        </div>

        <!-- SECCIÓN 4: OTROS -->
        <div class="table-card">
            <div class="table-section-title text-center">
                <i class="bi bi-box-seam me-1"></i> OTROS
            </div>
            <div class="p-2 bg-light rounded-bottom" style="border-top: 1px solid #e2e8f0; border-bottom: 1px solid #e2e8f0;">
                <div class="editable p-1 rounded bg-white" contenteditable="true" style="min-height: 35px; font-size: 0.75rem;">
                </div>
            </div>
        </div>

        <!-- SECCIÓN 5: FIRMAS -->
        <div class="table-card mt-2">
            
            <!-- BLOQUE INDUCCIÓN -->
            <div class="mb-2">
                <div class="fw-bold mb-1 text-uppercase" style="color: var(--dark-teal); font-size: 0.75rem; border-bottom: 1.5px solid var(--bright-teal); padding-bottom: 1px; display: inline-block;">
                    <i class="bi bi-check-circle-fill me-1" style="color: var(--bright-teal);"></i> Inducción
                </div>
                <div class="row text-center pt-1">
                    <div class="col-6">
                        <div class="signature-box">
                            <strong class="d-block text-uppercase editable" style="font-size: 0.72rem; color: var(--dark-teal);">Nombre y Firma</strong>
                            <span class="text-muted d-block" style="font-size: 0.7rem;">Nombre y Firma</span>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="signature-box">
                            <strong class="d-block text-uppercase editable" style="font-size: 0.72rem; color: var(--dark-teal);">Coordinador TI GSB</strong>
                        </div>
                    </div>
                </div>
            </div>

            <!-- BLOQUE RETIRO DE EQUIPO -->
            <div class="mt-2">
                <div class="fw-bold mb-1 text-uppercase" style="color: var(--dark-teal); font-size: 0.75rem; border-bottom: 1.5px solid var(--bright-teal); padding-bottom: 1px; display: inline-block;">
                    <i class="bi bi-box-arrow-right me-1" style="color: var(--bright-teal);"></i> Retiro de equipo
                </div>
                <div class="row text-center pt-1">
                    <div class="col-6">
                        <div class="signature-box">
                            <strong class="d-block text-uppercase editable" style="font-size: 0.72rem; color: var(--dark-teal);">Nombre y Firma</strong>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="signature-box">
                            <strong class="d-block text-uppercase editable" style="font-size: 0.72rem; color: var(--dark-teal);">Área de Soporte Técnico</strong>
                        </div>
                    </div>
                </div>
            </div>

            <!-- LEYENDA DEL PIE DE PÁGINA -->
            <div class="text-center mt-2 pt-1">
                <small class="fw-bold d-block" style="color: var(--dark-teal); font-size: 0.7rem;">
                    *Esta hoja deberá venir acompañada del correo de autorización enviado a Administración y Soporte
                </small>
            </div>
        </div>

    </div>

    <!-- LIBRERÍA HTML2PDF -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <script>
    function seleccionarTramite(elementoClick) {
        const iconoClick = elementoClick.querySelector('.icono-tramite');
        const estaMarcado = iconoClick.classList.contains('bi-check-square-fill');

        document.querySelectorAll('.tramite-option').forEach(opt => {
            const icono = opt.querySelector('.icono-tramite');
            icono.className = 'bi bi-square icono-tramite';
            icono.style.color = '';
        });

        if (!estaMarcado) {
            iconoClick.className = 'bi bi-check-square-fill icono-tramite';
            iconoClick.style.color = 'var(--bright-teal)';
        }
    }

    function cargarFotosGenerico(event, idContenedor, idMensaje) {
        const archivos = event.target.files;
        const contenedor = document.getElementById(idContenedor);
        const msg = document.getElementById(idMensaje);
        const maxFotos = 4;

        if (archivos.length > 0) {
            for (let i = 0; i < archivos.length; i++) {
                const fotosActuales = contenedor.querySelectorAll('.foto-preview-container').length;
                
                if (fotosActuales >= maxFotos) {
                    alert(`Ha alcanzado el límite máximo de ${maxFotos} imágenes de evidencia en esta sección.`);
                    break;
                }

                const reader = new FileReader();
                reader.onload = function(e) {
                    if (msg) msg.style.display = 'none';

                    const div = document.createElement('div');
                    div.className = 'foto-preview-container';
                    div.innerHTML = `
                        <img src="${e.target.result}" alt="Evidencia">
                        <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 m-1 py-0 px-1 no-print shadow-sm" title="Eliminar" onclick="borrarFotoGenerico(this, '${idContenedor}', '${idMensaje}')">×</button>
                    `;
                    contenedor.appendChild(div);
                };
                reader.readAsDataURL(archivos[i]);
            }
        }
        
        event.target.value = '';
    }

    function borrarFotoGenerico(btn, idContenedor, idMensaje) {
        btn.parentElement.remove();
        const contenedor = document.getElementById(idContenedor);
        const msg = document.getElementById(idMensaje);
        if (contenedor.querySelectorAll('.foto-preview-container').length === 0 && msg) {
            msg.style.display = 'inline';
        }
    }

    // GENERACIÓN CORRECTA SIN CORTES LATERALES NI TEXTO BORROSO
    function descargarPDF() {
        const elemento = document.getElementById('documento-responsiva');
        const elemUsuario = document.getElementById('campo-usuario');
        let nombreUsuario = elemUsuario ? elemUsuario.innerText.trim() : "<?php echo htmlspecialchars($usuario); ?>";
        
        if (!nombreUsuario || nombreUsuario === '___________________________') {
            nombreUsuario = "Responsiva_Usuario";
        }
        
        nombreUsuario = nombreUsuario.replace(/[\/\\?%*:|"<>]/g, '_').replace(/\s+/g, '_');

        const elementosNoPrint = elemento.querySelectorAll('.no-print');
        elementosNoPrint.forEach(el => el.style.setProperty('display', 'none', 'important'));
        
        const opciones = {
            margin:       [4, 4, 4, 4],
            filename:     `${nombreUsuario}.pdf`,
            image:        { type: 'jpeg', quality: 0.98 },
            html2canvas:  { 
                scale: 2, 
                useCORS: true, 
                logging: false,
                letterRendering: true,
                scrollX: 0,
                scrollY: 0,
                windowWidth: document.documentElement.offsetWidth
            },
            jsPDF:        { unit: 'mm', format: 'letter', orientation: 'portrait' },
            pagebreak:    { mode: ['avoid-all', 'css', 'legacy'] }
        };

        html2pdf().set(opciones).from(elemento).save().then(() => {
            elementosNoPrint.forEach(el => el.style.removeProperty('display'));
        });
    }
    </script>
</body>
</html>