<?php
require_once 'conexion.php';

// Generar hashes compatibles con tu versión exacta de PHP
$passAdmin = password_hash('admin123', PASSWORD_BCRYPT);
$passSuper = password_hash('SuperAdmin2026', PASSWORD_BCRYPT);

try {
    // Asegurar tamaño de columna
    $pdo->exec("ALTER TABLE usuarios MODIFY password VARCHAR(255) NOT NULL;");

    // Actualizar contraseñas
    $stmt1 = $pdo->prepare("UPDATE usuarios SET password = :pass WHERE usuario = 'admin'");
    $stmt1->execute([':pass' => $passAdmin]);

    $stmt2 = $pdo->prepare("UPDATE usuarios SET password = :pass WHERE usuario = 'superadmin'");
    $stmt2->execute([':pass' => $passSuper]);

    echo "<div style='font-family: sans-serif; padding: 20px; border: 2px solid green; background: #e6ffe6;'>";
    echo "<h2>✅ Hashes sincronizados con éxito</h2>";
    echo "<p>Las contraseñas han sido regeneradas desde tu servidor local:</p>";
    echo "<ul>";
    echo "<li><strong>admin:</strong> <code>admin123</code></li>";
    echo "<li><strong>superadmin:</strong> <code>SuperAdmin2026</code></li>";
    echo "</ul>";
    echo "<p><a href='login_directo.php?user=admin'>👉 Haz clic aquí para probar el login con ADMIN</a></p>";
    echo "<p><a href='login_directo.php?user=superadmin'>👉 Haz clic aquí para probar el login con SUPERADMIN</a></p>";
    echo "</div>";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>