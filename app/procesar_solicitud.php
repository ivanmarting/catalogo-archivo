<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'aosch_bd');

$conexion = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_obra = (int)$_POST['id_obra'];
    $nombre = $conexion->real_escape_string($_POST['nombre']);
    $email = $conexion->real_escape_string($_POST['email']);
    $motivo = $conexion->real_escape_string($_POST['motivo']);

    $sql = "INSERT INTO solicitudes (id_obra, nombre_solicitante, email_solicitante, motivo) VALUES (?, ?, ?, ?)";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("isss", $id_obra, $nombre, $email, $motivo);

    if ($stmt->execute()) {
        // Redirigir al inicio con mensaje de éxito (podrías crear una página de gracias)
        echo "<script>
            alert('¡Solicitud enviada! El administrador revisará tu petición y te contactará por email.');
            window.location.href = '../index.php';
        </script>";
    } else {
        die("Error al enviar solicitud: " . $conexion->error);
    }
}
?>