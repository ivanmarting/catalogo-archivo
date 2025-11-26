<?php
// 1. Validar ID de obra
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Error: Obra no especificada.");
}
$id_obra = (int)$_GET['id'];

// 2. Conexión para obtener el título de la obra
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'aosch_bd');
$conexion = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
$sql = "SELECT titulo FROM obras WHERE id_obra = $id_obra";
$res = $conexion->query($sql);
$obra = $res->fetch_assoc();

if (!$obra) die("Obra no encontrada.");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Solicitar Acceso</title>
    <link rel="stylesheet" href="../css/estilos.css">
    <link rel="stylesheet" href="../css/responsive.css">
    <style>
        .solicitud-container {
            max-width: 600px;
            margin: 50px auto;
            padding: 30px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            border-top: 5px solid var(--color-acento);
        }
        .info-obra {
            background: #f9f9f9;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid #333;
        }
        input, textarea { width: 100%; padding: 10px; margin-top: 5px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px; }
        button { width: 100%; padding: 12px; background: var(--color-acento); color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 1.1em; }
        button:hover { background: #a00; }
    </style>
</head>
<body>
    <header>
        <nav>
            <a href="../index.php">← Volver al Catálogo</a>
        </nav>
    </header>

    <div class="solicitud-container">
        <h1 style="margin-top:0;">🔒 Acceso Restringido</h1>
        <p>Para visualizar y descargar esta partitura, debes solicitar acceso al Archivo.</p>
        
        <div class="info-obra">
            <strong>Obra solicitada:</strong><br>
            <?php echo htmlspecialchars($obra['titulo']); ?>
        </div>

        <form action="../app/procesar_solicitud.php" method="POST">
            <input type="hidden" name="id_obra" value="<?php echo $id_obra; ?>">
            
            <label>Tu Nombre Completo:</label>
            <input type="text" name="nombre" required placeholder="Ej: Juan Pérez">
            
            <label>Tu Correo Electrónico:</label>
            <input type="email" name="email" required placeholder="nombre@email.com">
            
            <label>Motivo de la solicitud:</label>
            <textarea name="motivo" rows="4" required placeholder="Ej: Soy estudiante del conservatorio y necesito esta obra para un examen..."></textarea>
            
            <button type="submit">Enviar Solicitud</button>
        </form>
    </div>
    <script src="../js/script.js"></script>
</body>
</html>