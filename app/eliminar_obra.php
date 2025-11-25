<?php
session_start();
// Seguridad: Solo admin
if (!isset($_SESSION['usuario_id'])) {
    die("Acceso denegado.");
}

if (!isset($_GET['id'])) {
    die("ID no especificado.");
}

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'aosch_bd');

$conexion = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
$id = (int)$_GET['id'];

// 1. (Opcional pero recomendado) Borrar los archivos físicos antes de borrar de la BD
// Para hacer esto bien, primero consultamos las rutas
$q_archivos = $conexion->query("SELECT ruta_archivo FROM archivos_pdf WHERE id_obra = $id");
while($f = $q_archivos->fetch_assoc()) {
    $ruta_fisica = '../' . $f['ruta_archivo'];
    if (file_exists($ruta_fisica)) {
        unlink($ruta_fisica); // Borra el PDF del servidor
    }
}

// Borrar miniatura también
$q_miniatura = $conexion->query("SELECT ruta_miniatura FROM obras WHERE id_obra = $id");
$m = $q_miniatura->fetch_assoc();
if ($m && $m['ruta_miniatura'] != 'uploads/default.png') {
    $ruta_mini = '../' . $m['ruta_miniatura'];
    if (file_exists($ruta_mini)) {
        unlink($ruta_mini);
    }
}

// 2. Borrar registro de la BD (El ON DELETE CASCADE borrará lo demás)
$stmt = $conexion->prepare("DELETE FROM obras WHERE id_obra = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    header("Location: ../Pages/panel.php?msg=Obra eliminada correctamente.");
} else {
    echo "Error al eliminar: " . $conexion->error;
}
?>