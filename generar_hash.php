<?php
// generar_hash.php
// Archivo para generar la contraseña encriptada del Superadmin

$password_plana = "SuperAdmin#2026!KeySecure";
$hash_seguro = password_hash($password_plana, PASSWORD_BCRYPT);

echo "<h2>Generador de Hash Seguro</h2>";
echo "<p><strong>Contraseña:</strong> " . htmlspecialchars($password_plana) . "</p>";
echo "<p><strong>Hash generado:</strong> <code style='background:#eee;padding:5px;'>" . $hash_seguro . "</code></p>";
?>