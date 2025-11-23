<?php
session_start();
if (!isset($_SESSION['usuario_id'])) { header("Location: ../Pages/login.php"); exit; }

define('DB_HOST', 'localhost'); define('DB_USER', 'root'); define('DB_PASS', ''); define('DB_NAME', 'aosch_bd');
$conexion = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_solicitud = (int)$_POST['id_solicitud'];
    
    // 1. Generar Token Seguro
    $token = bin2hex(random_bytes(32));
    
    // 2. Calcular Vencimiento: 48 HORAS desde ahora
    $fecha_vencimiento = date('Y-m-d H:i:s', strtotime('+48 hours'));
    
    // 3. Guardar en BD
    $sql = "UPDATE solicitudes SET estado = 'aprobado', token_acceso = ?, fecha_expiracion = ? WHERE id_solicitud = ?";
    $stmt = $conexion->prepare($sql);
    // "ssi" = string (token), string (fecha), int (id)
    $stmt->bind_param("ssi", $token, $fecha_vencimiento, $id_solicitud);
    
    if ($stmt->execute()) {
        header("Location: ../Pages/solicitudes.php?token=" . $token);
    } else {
        echo "Error: " . $conexion->error;
    }
}
?>