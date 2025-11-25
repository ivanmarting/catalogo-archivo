<?php
session_start();
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'aosch_bd');

$conexion = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conexion->connect_error) die("Error BD");

// --- AUTO-GENERAR ADMIN SI NO EXISTE (SOLO PARA PRIMER USO) ---
// Esto te permitirá entrar con admin/admin123 si la tabla está vacía
$check = $conexion->query("SELECT count(*) as total FROM usuarios");
$row = $check->fetch_assoc();
if ($row['total'] == 0) {
    $pass_hash = password_hash('admin123', PASSWORD_DEFAULT);
    $conexion->query("INSERT INTO usuarios (usuario, password) VALUES ('admin', '$pass_hash')");
}
// --------------------------------------------------------------

$usuario = $_POST['usuario'] ?? '';
$password = $_POST['password'] ?? '';

// Buscar usuario
$stmt = $conexion->prepare("SELECT id_usuario, password FROM usuarios WHERE usuario = ?");
$stmt->bind_param("s", $usuario);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows === 1) {
    $user_data = $resultado->fetch_assoc();
    // Verificar contraseña
    if (password_verify($password, $user_data['password'])) {
        // ¡LOGIN EXITOSO!
        $_SESSION['usuario_id'] = $user_data['id_usuario'];
        $_SESSION['usuario_nombre'] = $usuario;
        header("Location: ../Pages/panel.php");
        exit;
    }
}

// Si falla
header("Location: ../Pages/login.php?error=1");
exit;
?>