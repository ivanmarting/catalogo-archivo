<?php
session_start();
if (!isset($_SESSION['usuario_id'])) { header("Location: login.php"); exit; }

define('DB_HOST', 'localhost'); define('DB_USER', 'root'); define('DB_PASS', ''); define('DB_NAME', 'aosch_bd');
$conexion = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Obtener solicitudes pendientes
$sql = "SELECT S.*, O.titulo 
        FROM solicitudes S 
        INNER JOIN obras O ON S.id_obra = O.id_obra 
        ORDER BY S.fecha_solicitud DESC";
$res = $conexion->query($sql);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Solicitudes</title>
    <link rel="stylesheet" href="../css/estilos.css">
    <style>
        .panel-container { max-width: 1200px; margin: 40px auto; padding: 20px; }
        .request-card { background: #fff; border: 1px solid #ddd; padding: 15px; margin-bottom: 15px; border-radius: 5px; display: flex; justify-content: space-between; align-items: center; }
        .request-info h3 { margin: 0 0 5px 0; color: var(--color-acento); }
        .status-badge { padding: 5px 10px; border-radius: 15px; font-size: 0.8em; font-weight: bold; }
        .pendiente { background: #ffe0b2; color: #e65100; }
        .aprobado { background: #c8e6c9; color: #2e7d32; }
        .btn-aprobar { background: #2e7d32; color: white; padding: 8px 15px; text-decoration: none; border-radius: 4px; border: none; cursor: pointer; }
    </style>
</head>
<body>
    <header><nav><a href="panel.php">← Volver al Panel</a></nav></header>
    <div class="panel-container">
        <h1>Solicitudes de Acceso</h1>
        <?php if(isset($_GET['token'])): ?>
            <div style="background:#d4edda; color:#155724; padding:20px; border-radius:5px; margin-bottom:20px; border:1px solid #c3e6cb;">
                <h3>✅ ¡Acceso Concedido!</h3>
                <p>Copia y envía este enlace al solicitante:</p>
                <input type="text" value="http://localhost/archivo_app/Pages/detalle_obra.php?t=<?php echo htmlspecialchars($_GET['token']); ?>" style="width:100%; padding:10px;" readonly>
            </div>
        <?php endif; ?>

        <?php while($row = $res->fetch_assoc()): ?>
            <div class="request-card">
                <div class="request-info">
                    <h3><?php echo htmlspecialchars($row['titulo']); ?></h3>
                    <p><strong>Solicitante:</strong> <?php echo htmlspecialchars($row['nombre_solicitante']); ?> (<?php echo htmlspecialchars($row['email_solicitante']); ?>)</p>
                    <p><em>"<?php echo htmlspecialchars($row['motivo']); ?>"</em></p>
                    <span class="status-badge <?php echo $row['estado']; ?>"><?php echo strtoupper($row['estado']); ?></span>
                </div>
                <div class="request-actions">
                    <?php if($row['estado'] == 'pendiente'): ?>
                        <form action="../app/aprobar_solicitud.php" method="POST">
                            <input type="hidden" name="id_solicitud" value="<?php echo $row['id_solicitud']; ?>">
                            <button type="submit" class="btn-aprobar">✅ Aprobar y Generar Link</button>
                        </form>
                    <?php else: ?>
                        <!-- Si ya está aprobado, podrías mostrar el link de nuevo si lo guardaste en BD, pero por seguridad a veces es mejor generarlo una vez -->
                        <span style="color:#999;">Procesado</span>
                    <?php endif; ?>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</body>
</html>