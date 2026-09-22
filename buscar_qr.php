<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit();
}

$host     = "localhost";      
$user     = "root";           
$password = "";    
$database = "proyecto"; 

try {
    $pdo = new PDO("mysql:host=$host;dbname=$database;charset=utf8mb4", $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error de conexión']);
    exit();
}

$codigo = trim($_GET['codigo'] ?? '');

if (empty($codigo)) {
    echo json_encode(['success' => false, 'message' => 'Código QR vacío']);
    exit();
}

// Extraer Hostname o S/N si el QR contiene texto multilínea
$hostname_busqueda = $codigo;
if (preg_match('/Hostname:\s*([^\n\r]+)/i', $codigo, $matches)) {
    $hostname_busqueda = trim($matches[1]);
}

// Buscar en la base de datos por HOSTNAME o S/N
$stmt = $pdo->prepare("SELECT * FROM equipos WHERE `HOSTNAME` = :query OR `S/N` = :query LIMIT 1");
$stmt->execute([':query' => $hostname_busqueda]);
$equipo = $stmt->fetch();

if ($equipo) {
    echo json_encode(['success' => true, 'data' => $equipo]);
} else {
    echo json_encode(['success' => false, 'message' => 'Equipo no encontrado en el sistema.']);
}