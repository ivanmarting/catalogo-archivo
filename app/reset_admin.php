<?php
// ==========================================================
// HERRAMIENTA PARA RESTABLECER CONTRASEÑA DE ADMIN
// ==========================================================

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'aosch_bd');

$conexion = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// 1. Verificar si la tabla usuarios existe, si no, crearla
$sql_tabla = "CREATE TABLE IF NOT EXISTS usuarios (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    usuario VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL
)";
$conexion->query($sql_tabla);

// 2. Borrar cualquier usuario 'admin' viejo o mal configurado
$conexion->query("DELETE FROM usuarios WHERE usuario = 'admin'");

// 3. Generar el hash CORRECTO para 'admin123'
$password_plano = 'admin123';
$password_hash = password_hash($password_plano, PASSWORD_DEFAULT);

// 4. Insertar el nuevo usuario admin limpio
$stmt = $conexion->prepare("INSERT INTO usuarios (usuario, password) VALUES ('admin', ?)");
$stmt->bind_param("s", $password_hash);

if ($stmt->execute()) {
    echo "<div style='font-family: sans-serif; padding: 20px; background: #e8f5e9; border: 1px solid #4caf50; border-radius: 5px; max-width: 500px; margin: 50px auto; text-align: center;'>";
    echo "<h1 style='color: #2e7d32;'>✅ ¡Usuario Reparado!</h1>";
    echo "<p>Se ha eliminado el usuario anterior y se ha creado uno nuevo.</p>";
    echo "<hr>";
    echo "<p><strong>Usuario:</strong> admin<br>";
    echo "<strong>Contraseña:</strong> admin123</p>";
    echo "<a href='../Pages/login.php' style='display: inline-block; padding: 10px 20px; background: #2e7d32; color: white; text-decoration: none; border-radius: 5px; margin-top: 10px;'>Ir a Iniciar Sesión</a>";
    echo "</div>";
} else {
    echo "<h1>❌ Error</h1>";
    echo "<p>No se pudo insertar el usuario: " . $conexion->error . "</p>";
}

$conexion->close();
?>