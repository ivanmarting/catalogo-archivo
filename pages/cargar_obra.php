<?php
// ===================================================================
// 1. CONEXIÓN PARA OBTENER DATOS DE FILTROS (GENERAR DROPDOWNS)
// ===================================================================
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'aosch_bd'); 

$conexion = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conexion->connect_error) {
    die("Fallo de conexión a la BD: " . $conexion->connect_error);
}

// Consultas para los selectores
$generos_q = $conexion->query("SELECT id_genero, nombre FROM generos ORDER BY nombre");
$instrumentos_q = $conexion->query("SELECT id_instrumento, nombre, categoria FROM instrumentos ORDER BY categoria, nombre");

// Organizar instrumentos por categoría para agrupar los checkboxes
$instrumentos_por_categoria = [];
while ($instr = $instrumentos_q->fetch_assoc()) {
    $instrumentos_por_categoria[$instr['categoria']][] = $instr;
}
$conexion->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Cargar Nueva Obra Musical</title>
    <link rel="stylesheet" href="../css/estilos.css"> 
</head>
<body>
    <form action="../app/procesar_carga.php" method="POST" enctype="multipart/form-data">

        <h1>Subir Partitura y Metadatos</h1>
        
        <h2>Información del Autor</h2>
        <label for="autor_nombre">Nombre del Autor:</label>
        <input type="text" name="autor_nombre" id="autor_nombre" required>
        
        <label for="autor_apellido">Apellido del Autor:</label>
        <input type="text" name="autor_apellido" id="autor_apellido" required>
        
        <label for="autor_orden">N° de Orden:</label>
        <input type="number" name="autor_orden" id="autor_orden" required>
        
        <hr>

        <h2>Detalles de la Obra</h2>
        
        <label for="obra_titulo">Título de la Obra:</label>
        <input type="text" name="obra_titulo" id="obra_titulo" required>
        
        <label for="obra_inventario">N° de Inventario:</label>
        <input type="text" name="obra_inventario" id="obra_inventario" required>

        <label for="anio_composicion">Año de Composición:</label>
        <input type="number" name="anio_composicion" id="anio_composicion" min="1000" max="<?php echo date('Y'); ?>" placeholder="Ej: 1957">

        <label for="genero">Género:</label>
        <select name="id_genero" id="genero" required>
            <option value="">-- Seleccionar Género --</option>
            <?php while ($gen = $generos_q->fetch_assoc()): ?>
                <option value="<?php echo $gen['id_genero']; ?>">
                    <?php echo htmlspecialchars($gen['nombre']); ?>
                </option>
            <?php endwhile; ?>
        </select>
        
        <label for="editorial_nombre">Editorial / Lugar de Extracción:</label>
        <input type="text" name="editorial_nombre" id="editorial_nombre" placeholder="Ej: Schott, Edición Propia" required>
        
        <hr>
        
        <h2>Instrumentación (Selección Múltiple - Colapsable CSS)</h2>
        <p>Selecciona todos los instrumentos o voces que contiene la partitura:</p>
        
        <div class="instrumentos-grid">
            <?php $i = 0; // Contador para generar IDs únicos ?>
            <?php foreach ($instrumentos_por_categoria as $categoria => $instrumentos): ?>
                <?php $i++; // Incrementar el contador ?>
                <?php $id_checkbox = "toggle-" . $i; ?>

                <div class="categoria-instrumento">
                    
                    <input type="checkbox" id="<?php echo $id_checkbox; ?>" class="toggle-checkbox" role="button">
                    
                    <label for="<?php echo $id_checkbox; ?>" class="toggle-label">
                        <?php echo htmlspecialchars($categoria); ?>
                        <span class="flecha">▶</span>
                    </label>

                    <div class="toggle-content">
                        <?php foreach ($instrumentos as $instr): ?>
                            <label>
                                <input type="checkbox" name="instrumentos[]" value="<?php echo $instr['id_instrumento']; ?>">
                                <?php echo htmlspecialchars($instr['nombre']); ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <hr>

        <h2>Archivos</h2>
        
        <label for="miniatura_img">Miniatura de Visualización Previa (JPG/PNG):</label>
        <input type="file" name="miniatura_img" id="miniatura_img" accept=".jpg, .jpeg, .png" required>

        <label for="partituras_pdf">Archivos PDF (Puedes seleccionar **VARIOS**):</label>
        <input type="file" name="partituras_pdf[]" id="partituras_pdf" accept=".pdf" multiple required> 

        <button type="submit">Guardar Obra Completa en Catálogo</button>
        
        <p><a href="../index.php">Ir a la Biblioteca/Catálogo</a></p>
    </form>
</body>
</html>