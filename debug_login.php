<?php
require_once 'conexion.php';

echo "<h2>Prueba de Autenticación Directa</h2>";

function probarLogin($pdo, $usuario, $passwordIngresada) {
    echo "<h3>Probrando usuario: '{$usuario}'</h3>";
    
    $stmt = $pdo->prepare("
        SELECT u.id, u.usuario, u.password, u.nombre, r.nombre AS rol_nombre 
        FROM usuarios u
        INNER JOIN roles r ON u.rol_id = r.id
        WHERE u.usuario = :usuario
    ");
    $stmt->execute(['usuario' => $usuario]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo "<p style='color:red;'>❌ El usuario '{$usuario}' NO existe o la relación con la tabla 'roles' falló (revisa que rol_id exista en la tabla roles).</p>";
        return;
    }

    echo "<strong>Usuario encontrado:</strong> " . htmlspecialchars($user['usuario']) . "<br>";
    echo "<strong>Rol obtenido:</strong> " . htmlspecialchars($user['rol_nombre']) . "<br>";
    echo "<strong>Hash en BD:</strong> <code>" . htmlspecialchars($user['password']) . "</code><br>";
    echo "<strong>Longitud del Hash:</strong> " . strlen($user['password']) . " caracteres<br>";

    if (password_verify($passwordIngresada, $user['password'])) {
        echo "<p style='color:green;'>✅ ¡CONTRASEÑA CORRECTA! password_verify funciona bien.</p>";
    } else {
        echo "<p style='color:red;'>❌ CONTRASEÑA INCORRECTA. El hash guardado no corresponde con '{$passwordIngresada}'.</p>";
    }
}

probarLogin($pdo, 'admin', 'admin123');
echo "<hr>";
probarLogin($pdo, 'superadmin', 'SuperAdmin2026');
?>