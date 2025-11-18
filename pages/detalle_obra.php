<?php
// ===================================================================
// 1. CONEXIÓN A LA BASE DE DATOS
// ===================================================================
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', ''); 
define('DB_NAME', 'aosch_bd'); 

$conexion = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conexion->connect_error) {
    die("Fallo de conexión a la BD: " . $conexion->connect_error);
}

// ===================================================================
// 2. LÓGICA DE DETALLE Y CONSULTAS
// ===================================================================

// A. Obtener el ID de la obra de la URL. Si no hay ID, salir.
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("ID de obra no válido.");
}
$id_obra = $conexion->real_escape_string($_GET['id']);


// B. Consulta principal de la obra (para mostrar el título, autor, etc.)
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
if ($resultado_obra->num_rows == 0) {
    die("Obra no encontrada.");
}
$obra = $resultado_obra->fetch_assoc();


// C. Consulta de los archivos PDF asociados (usa la tabla archivos_pdf)
$sql_pdfs = "SELECT ruta_archivo, nombre_archivo 
FROM archivos_pdf 
WHERE id_obra = $id_obra";

$resultado_pdfs = $conexion->query($sql_pdfs);

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Detalle de Obra: <?php echo htmlspecialchars($obra['titulo']); ?></title>
    <link rel="stylesheet" href="../css/estilos.css"> 
    <style>
        /* Estilos básicos para la página de detalle, puedes moverlos a estilos.css si quieres */
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
            color: var(--color-acento);
            border-bottom: 2px solid var(--color-gris-claro);
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .pdf-list a {
            display: flex; /* Para mejor alineación */
            justify-content: space-between;
            align-items: center;
            margin: 10px 0;
            padding: 10px 15px;
            background: #f0f0f0;
            border-radius: 4px;
            text-decoration: none;
            color: var(--color-principal);
            transition: background 0.2s;
        }
        .pdf-list a::after {
            content: "↓ Descargar / Ver"; /* Indicador de acción */
            font-size: 0.8em;
            color: var(--color-acento);
        }
        .pdf-list a:hover {
            background: var(--color-gris-claro);
            color: var(--color-acento);
        }
        .info-label {
            font-weight: bold;
            color: var(--color-principal);
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
            <a href="cargar_obra.php">Subir Archivo</a> 
        </nav>
    </header>

    <div class="detalle-container">
        <p><a href="../index.php">← Volver al Catálogo</a></p>
        
        <h1><?php echo htmlspecialchars($obra['titulo']); ?></h1>
        
        <p><span class="info-label">Autor:</span> <?php echo htmlspecialchars($obra['autor_apellido']) . ", " . htmlspecialchars($obra['autor_nombre']); ?></p>
        <p><span class="info-label">Género:</span> <?php echo htmlspecialchars($obra['genero_nombre']); ?></p>
        
        <hr>
        <h2>Archivos Disponibles:</h2>
        
        <?php if ($resultado_pdfs->num_rows > 0): ?>
            <div class="pdf-list">
                <?php while($pdf = $resultado_pdfs->fetch_assoc()): ?>
                    <?php 
                        // CORRECCIÓN CLAVE: Agregamos '../' para subir un nivel desde la carpeta 'Pages/'
                        // Esto permite acceder a la carpeta 'uploads/pdfs/'
                        $ruta_completa = '../' . htmlspecialchars($pdf['ruta_archivo']); 
                        $nombre_visible = htmlspecialchars($pdf['nombre_archivo'] ?: 'Archivo PDF sin nombre');
                        
                        // Enlace que intenta mostrar el PDF. El usuario luego puede descargarlo
                    ?>
                    <a href="<?php echo $ruta_completa; ?>" target="_blank" title="Ver archivo PDF">
                        <?php echo $nombre_visible; ?>
                    </a>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <p>No se encontraron archivos PDF para esta obra.</p>
        <?php endif; ?>
        
    </div>
    
    <footer>
        <p>© 2025 Catálogo de Partituras. Desarrollado con PHP y MySQL.</p>
    </footer>

    <?php $conexion->close(); ?>

</body>
</html>