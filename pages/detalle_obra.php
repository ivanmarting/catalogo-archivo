<?php
// ===================================================================
// 1. SEGURIDAD: VERIFICACIÓN DE TOKEN Y VENCIMIENTO ⏳
// ===================================================================
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', ''); 
define('DB_NAME', 'aosch_bd'); 
$conexion = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conexion->connect_error) die("Error BD");

// Obtenemos el token de la URL (ej: detalle_obra.php?t=xyz123...)
$token = $_GET['t'] ?? '';

if (empty($token)) {
    die("⛔ <h1>Acceso Denegado</h1><p>Esta página requiere un enlace de acceso válido (Token faltante).</p><a href='../index.php'>Volver al inicio</a>");
}

// Buscamos si el token es válido, aprobado Y SI AÚN ESTÁ EN FECHA (48hs)
// La condición (fecha_expiracion IS NULL OR fecha_expiracion > NOW()) permite que 
// los tokens viejos sin fecha sigan funcionando si así lo deseas, o solo los nuevos vigentes.
$sql_check = "SELECT id_obra FROM solicitudes 
              WHERE token_acceso = ? 
              AND estado = 'aprobado' 
              AND (fecha_expiracion IS NULL OR fecha_expiracion > NOW())";

$stmt_check = $conexion->prepare($sql_check);
$stmt_check->bind_param("s", $token);
$stmt_check->execute();
$res_check = $stmt_check->get_result();

if ($res_check->num_rows === 0) {
    // Mensaje personalizado de error
    die("
        <div style='font-family: sans-serif; text-align: center; margin-top: 50px;'>
            <h1 style='color: #c00;'>⛔ Enlace Caducado o Inválido</h1>
            <p>Este enlace de acceso temporal ha expirado (límite de 48 horas) o no es correcto.</p>
            <p>Por favor, solicita acceso nuevamente desde el catálogo.</p>
            <br>
            <a href='../index.php' style='padding: 10px 20px; background: #333; color: white; text-decoration: none; border-radius: 5px;'>Volver al Catálogo</a>
        </div>
    ");
}

// ¡Acceso Concedido! Obtenemos el ID de la obra del token
$fila_solicitud = $res_check->fetch_assoc();
$id_obra = $fila_solicitud['id_obra'];

// ===================================================================
// 2. CARGAR DATOS DE LA OBRA (Usando $id_obra seguro)
// ===================================================================

$sql_obra = "SELECT 
    O.titulo, 
    A.nombre AS autor_nombre, 
    A.apellido AS autor_apellido,
    G.nombre AS genero_nombre
FROM obras O
INNER JOIN autores A ON O.id_autor = A.id_autor
INNER JOIN generos G ON O.id_genero = G.id_genero
WHERE O.id_obra = $id_obra";

$resultado_obra = $conexion->query($sql_obra);
$obra = $resultado_obra->fetch_assoc();

// Carga de PDFs
$sql_pdfs = "SELECT ruta_archivo, nombre_archivo FROM archivos_pdf WHERE id_obra = $id_obra";
$resultado_pdfs = $conexion->query($sql_pdfs);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Detalle de Obra: <?php echo htmlspecialchars($obra['titulo']); ?></title>
    <link rel="stylesheet" href="../css/estilos.css"> 
    <style>
        /* Estilos integrados para asegurar visualización correcta */
        .detalle-container { 
            max-width: 800px; 
            margin: 50px auto; 
            padding: 30px; 
            background: #fff; 
            border: 1px solid #ddd; 
            border-radius: 8px; 
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); 
        }
        .detalle-container h1 { 
            color: var(--color-acento, #c00); 
            border-bottom: 2px solid #ddd; 
            padding-bottom: 10px; 
            margin-bottom: 20px; 
        }
        .info-label { 
            font-weight: bold; 
            color: #333; 
        }
        
        /* Lista de PDFs */
        .pdf-list a { 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            margin: 10px 0; 
            padding: 12px 15px; 
            background: #f9f9f9; 
            border: 1px solid #eee;
            border-radius: 4px; 
            text-decoration: none; 
            color: #333; 
            transition: background 0.2s; 
        }
        .pdf-list a:hover { 
            background: #f0f0f0; 
            border-color: #ccc;
            color: var(--color-acento, #c00); 
        }
        .pdf-list a span {
            font-size: 0.9em;
            color: var(--color-acento, #c00);
            font-weight: bold;
        }
        
        /* Aviso de Privacidad */
        .aviso-privado { 
            background: #e3f2fd; /* Azul muy claro */
            color: #0d47a1;      /* Azul oscuro */
            padding: 15px; 
            border-radius: 5px; 
            margin-bottom: 25px; 
            text-align: center; 
            border: 1px solid #bbdefb; 
            font-size: 0.95em; 
        }
    </style>
</head>
<body>

    <header> 
        <nav>
            <a href="../index.php">Inicio</a>
            <a href="nosotros.php">Nosotros</a>
            <a href="../index.php"> <img src="../src/images.png" alt="Logo" class="logo"> </a>
            <a href="contacto.php">Contacto</a>
            <a href="login.php" style="color:#555;">🔒 Acceso Admin</a> 
        </nav>
    </header>

    <div class="detalle-container">
        <!-- Aviso amigable sobre la duración del enlace -->
        <div class="aviso-privado">
            <strong>🔓 Acceso Temporal Concedido</strong><br>
            Este enlace es seguro y válido por 48 horas. Te recomendamos descargar los archivos ahora.
        </div>
        
        <p><a href="../index.php" style="text-decoration:none; color:#666;">← Volver al Catálogo</a></p>
        
        <h1><?php echo htmlspecialchars($obra['titulo']); ?></h1>
        
        <p><span class="info-label">Autor:</span> <?php echo htmlspecialchars($obra['autor_apellido']) . ", " . htmlspecialchars($obra['autor_nombre']); ?></p>
        <p><span class="info-label">Género:</span> <?php echo htmlspecialchars($obra['genero_nombre']); ?></p>
        
        <hr style="border:0; border-top:1px solid #eee; margin:20px 0;">
        
        <h2>Archivos Disponibles para Descarga:</h2>
        
        <?php if ($resultado_pdfs->num_rows > 0): ?>
            <div class="pdf-list">
                <?php while($pdf = $resultado_pdfs->fetch_assoc()): ?>
                    <?php 
                        // Ruta relativa para subir un nivel desde Pages/ a la raíz
                        $ruta_completa = '../' . htmlspecialchars($pdf['ruta_archivo']); 
                        $nombre_visible = htmlspecialchars($pdf['nombre_archivo'] ?: 'Archivo PDF');
                    ?>
                    <a href="<?php echo $ruta_completa; ?>" target="_blank" title="Descargar archivo PDF">
                        📄 <?php echo $nombre_visible; ?> 
                        <span>⬇ Descargar</span>
                    </a>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <p style="color:#666; font-style:italic;">No se encontraron archivos PDF adjuntos para esta obra.</p>
        <?php endif; ?>
        
    </div>
    
    <footer>
        <div class="footer-container" style="justify-content:center; text-align:center; padding:20px;"> 
            <p style="color:#666; font-size:0.9em;">© 2025 Archivo Orquesta Sinfónica del Chaco. Todos los derechos reservados.</p>
        </div>
    </footer>

</body>
</html>