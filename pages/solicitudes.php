<?php
session_start();
require_once '../config/db.php'; 

// Seguridad: Solo admin
if (!isset($_SESSION['usuario_id'])) { 
    header("Location: login.php"); 
    exit; 
}

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
        
        // Construimos el enlace 'mailto'
        $destinatario = $datos['email_solicitante'];
        $asunto = "Acceso Aprobado: " . $datos['titulo'];
        
        // Detectar URL base automáticamente para no depender de "localhost" fijo
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
        $host = $_SERVER['HTTP_HOST'];
        // Ajustamos la ruta asumiendo que este script está en /Pages/
        $path = dirname($_SERVER['PHP_SELF']); 
        $base_url = $protocol . "://" . $host . $path;
        $link_acceso = $base_url . "/detalle_obra.php?t=" . $token_seguro;
        
        $cuerpo = "Hola " . $datos['nombre_solicitante'] . ",\n\n";
        $cuerpo .= "Tu solicitud para acceder a la obra '" . $datos['titulo'] . "' ha sido aprobada.\n\n";
        $cuerpo .= "Puedes acceder a las partituras mediante el siguiente enlace temporal (Válido por 48hs):\n";
        $cuerpo .= $link_acceso . "\n\n";
        $cuerpo .= "Saludos,\nArchivo Orquesta Sinfónica del Chaco";
        
        $datos_correo = [
            'href' => "mailto:$destinatario?subject=" . rawurlencode($asunto) . "&body=" . rawurlencode($cuerpo),
            'email' => $destinatario,
            'link_raw' => $link_acceso
        ];
    }
}

// ============================================================
// 2. LISTADO DE SOLICITUDES
// ============================================================
// Ordenamos: Pendientes primero, luego por fecha
$sql = "SELECT S.*, O.titulo 
        FROM solicitudes S 
        INNER JOIN obras O ON S.id_obra = O.id_obra 
        ORDER BY FIELD(S.estado, 'pendiente', 'aprobado', 'rechazado'), S.fecha_solicitud DESC";
$res = $conexion->query($sql);

$page_title = "Gestión de Solicitudes - Admin";
require_once '../includes/header.php'; 
?>

<div class="panel-container">
    
    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; margin-bottom:20px;">
        <h1>Solicitudes de Acceso</h1>
        <a href="panel.php" class="btn-accion" style="background:#555; color:white; text-decoration:none;">← Volver al Panel</a>
    </div>

    <!-- BLOQUE DE ÉXITO (APROBACIÓN) -->
    <?php if($datos_correo): ?>
        <div class="success-box">
            <h2>✅ ¡Solicitud Aprobada con Éxito!</h2>
            <p>El token de acceso ha sido generado y guardado en la base de datos.</p>
            <p>Ahora, envía el correo al usuario <strong>(<?php echo htmlspecialchars($datos_correo['email']); ?>)</strong>:</p>
            
            <a href="<?php echo $datos_correo['href']; ?>" target="_blank" class="btn-mailto">
                📧 Abrir Correo y Enviar Link
            </a>
            
            <div style="margin-top: 25px; font-size: 0.9em; color: #666;">
                <p>O copia el enlace manualmente:</p>
                <input type="text" value="<?php echo htmlspecialchars($datos_correo['link_raw']); ?>" class="link-copiar" readonly onclick="this.select()">
            </div>
        </div>
    <?php endif; ?>

    <!-- LISTA DE SOLICITUDES -->
    <div class="requests-grid">
        <?php while($row = $res->fetch_assoc()): ?>
            <div class="request-card">
                
                <div class="request-info">
                    <h3><?php echo htmlspecialchars($row['titulo']); ?></h3>
                    
                    <p>
                        <strong>Solicitante:</strong> <?php echo htmlspecialchars($row['nombre_solicitante']); ?> 
                        <a href="mailto:<?php echo htmlspecialchars($row['email_solicitante']); ?>" style="color:#2196F3;">
                            (<?php echo htmlspecialchars($row['email_solicitante']); ?>)
                        </a>
                    </p>
                    
                    <p style="font-size:0.85em; color:#999;">
                        Fecha: <?php echo date('d/m/Y H:i', strtotime($row['fecha_solicitud'])); ?>
                    </p>

                    <!-- Motivo con estilo de cita -->
                    <em>"<?php echo htmlspecialchars($row['motivo']); ?>"</em>
                    
                    <!-- Badge de estado -->
                    <span class="status-badge <?php echo $row['estado']; ?>">
                        <?php echo strtoupper($row['estado']); ?>
                    </span>
                </div>
                
                <div class="request-actions">
                    <?php if($row['estado'] == 'pendiente'): ?>
                        <form action="../app/aprobar_solicitud.php" method="POST">
                            <input type="hidden" name="id_solicitud" value="<?php echo $row['id_solicitud']; ?>">
                            <button type="submit" class="btn-aprobar">✅ Aprobar y Generar Link</button>
                        </form>
                        <!-- Aquí podrías agregar un botón de rechazar si tu backend lo soporta -->
                        
                    <?php elseif($row['estado'] == 'aprobado'): ?>
                        <?php if(!empty($row['token_acceso'])): ?>
                            <a href="solicitudes.php?token=<?php echo $row['token_acceso']; ?>" style="font-size:0.9em; color:#2196F3; font-weight:bold;">
                                📧 Reenviar Correo
                            </a>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>

            </div>
        <?php endwhile; ?>
    </div>

</div>

<?php require_once '../includes/footer.php'; ?>