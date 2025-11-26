<?php
session_start();
if (!isset($_SESSION['usuario_id'])) { header("Location: login.php"); exit; }

define('DB_HOST', 'localhost'); define('DB_USER', 'root'); define('DB_PASS', ''); define('DB_NAME', 'aosch_bd');
$conexion = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// ============================================================
// 1. LÓGICA PARA PREPARAR EL CORREO (Si acabamos de aprobar)
// ============================================================
$datos_correo = null;
if (isset($_GET['token'])) {
    $token_seguro = $conexion->real_escape_string($_GET['token']);
    
    // Buscamos quién es el dueño de este token y qué obra pidió
    $sql_info = "SELECT S.email_solicitante, S.nombre_solicitante, O.titulo 
                 FROM solicitudes S
                 INNER JOIN obras O ON S.id_obra = O.id_obra
                 WHERE S.token_acceso = '$token_seguro'";
    
    $res_info = $conexion->query($sql_info);
    if ($res_info->num_rows > 0) {
        $datos = $res_info->fetch_assoc();
        
        // Construimos el enlace 'mailto' con el cuerpo pre-llenado
        $destinatario = $datos['email_solicitante'];
        $asunto = "Acceso Aprobado: " . $datos['titulo'];
        
        $link_acceso = "http://localhost/archivo_app/Pages/detalle_obra.php?t=" . $token_seguro;
        
        $cuerpo = "Hola " . $datos['nombre_solicitante'] . ",\n\n";
        $cuerpo .= "Tu solicitud para acceder a la obra '" . $datos['titulo'] . "' ha sido aprobada.\n\n";
        $cuerpo .= "Puedes acceder a las partituras mediante el siguiente enlace temporal (Válido por 48hs):\n";
        $cuerpo .= $link_acceso . "\n\n";
        $cuerpo .= "Saludos,\nArchivo Orquesta Sinfónica del Chaco";
        
        // Codificar para URL (importante para que funcione el mailto)
        $datos_correo = [
            'href' => "mailto:$destinatario?subject=" . rawurlencode($asunto) . "&body=" . rawurlencode($cuerpo),
            'email' => $destinatario
        ];
    }
}

// ============================================================
// 2. LISTADO DE SOLICITUDES
// ============================================================
$sql = "SELECT S.*, O.titulo 
        FROM solicitudes S 
        INNER JOIN obras O ON S.id_obra = O.id_obra 
        ORDER BY FIELD(S.estado, 'pendiente', 'aprobado', 'rechazado'), S.fecha_solicitud DESC";
$res = $conexion->query($sql);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Solicitudes</title>
    <link rel="stylesheet" href="../css/estilos.css">
    <link rel="stylesheet" href="../css/responsive.css">
</head>
<body>
    <header><nav><a href="panel.php">← Volver al Panel</a></nav></header>
    
    <div class="panel-container">
        <h1>Solicitudes de Acceso</h1>

        <!-- BLOQUE DE RESPUESTA RÁPIDA -->
        <?php if($datos_correo): ?>
            <div class="success-box">
                <h2 style="color:#2e7d32; margin-top:0;">✅ ¡Solicitud Aprobada con Éxito!</h2>
                <p>El token de acceso ha sido generado y guardado.</p>
                <p>Ahora, envía el correo al usuario <strong>(<?php echo htmlspecialchars($datos_correo['email']); ?>)</strong> con un solo clic:</p>
                
                <a href="<?php echo $datos_correo['href']; ?>" target="_blank" class="btn-mailto">
                    📧 Abrir Correo y Enviar Link
                </a>
                
                <div style="margin-top: 20px; font-size: 0.9em; color: #666;">
                    <p>O copia el enlace manualmente:</p>
                    <input type="text" value="http://localhost/archivo_app/Pages/detalle_obra.php?t=<?php echo htmlspecialchars($_GET['token']); ?>" style="width:100%; max-width:600px; padding:10px; text-align:center; border:1px solid #ccc;" readonly onclick="this.select()">
                </div>
            </div>
        <?php endif; ?>

        <!-- LISTA DE SOLICITUDES -->
        <?php while($row = $res->fetch_assoc()): ?>
            <div class="request-card">
                <div class="request-info">
                    <h3><?php echo htmlspecialchars($row['titulo']); ?></h3>
                    <p>
                        <strong>Solicitante:</strong> <?php echo htmlspecialchars($row['nombre_solicitante']); ?> 
                        <a href="mailto:<?php echo htmlspecialchars($row['email_solicitante']); ?>" style="color:#666; text-decoration:none;">
                            (<?php echo htmlspecialchars($row['email_solicitante']); ?>)
                        </a>
                    </p>
                    <p><em>"<?php echo htmlspecialchars($row['motivo']); ?>"</em></p>
                    <p style="font-size:0.8em; color:#999;">Fecha: <?php echo date('d/m/Y H:i', strtotime($row['fecha_solicitud'])); ?></p>
                    
                    <span class="status-badge <?php echo $row['estado']; ?>"><?php echo strtoupper($row['estado']); ?></span>
                </div>
                
                <div class="request-actions">
                    <?php if($row['estado'] == 'pendiente'): ?>
                        <form action="../app/aprobar_solicitud.php" method="POST">
                            <input type="hidden" name="id_solicitud" value="<?php echo $row['id_solicitud']; ?>">
                            <button type="submit" class="btn-aprobar">✅ Aprobar</button>
                        </form>
                    <?php elseif($row['estado'] == 'aprobado'): ?>
                        <!-- Si quieres reenviar el correo de una aprobada anteriormente -->
                        <?php if(!empty($row['token_acceso'])): ?>
                            <a href="solicitudes.php?token=<?php echo $row['token_acceso']; ?>" style="font-size:0.9em; color:#2196F3;">Reenviar Link</a>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
    <script src="../js/script.js"></script>
</body>
</html>