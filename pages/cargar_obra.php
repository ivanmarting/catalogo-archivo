<?php
// INICIAR SESIÓN (NECESARIO PARA PASAR MENSAJES ENTRE PÁGINAS)
session_start();

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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cargar Nueva Obra Musical</title>
    <link rel="stylesheet" href="../css/estilos.css"> 
    <link rel="stylesheet" href="../css/responsive.css">
    <style>
        /* Estilos para los nuevos botones de selección */
        .select-category-buttons {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            padding: 10px 0;
            border-bottom: 1px solid #ccc;
        }
        .select-category-buttons button {
            padding: 10px 15px;
            border: 2px solid #ccc;
            background-color: #f0f0f0;
            cursor: pointer;
            border-radius: 5px;
            transition: background-color 0.2s, border-color 0.2s;
            font-weight: bold;
        }
        .select-category-buttons button.active {
            border-color: var(--color-acento, red); /* Usar el color de acento si está definido */
            background-color: #ffe0e0;
        }
        /* Ocultar inicialmente el campo OPUS */
        #campo_opus {
            display: none;
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
    
             <button class="menu-toggle-btn">&#9776;</button> 
        </nav>
    </header>
    
    <?php
    if (isset($_SESSION['mensaje_alerta'])) {
        $mensaje = json_encode($_SESSION['mensaje_alerta']);
        echo "<script>
            alert($mensaje);
        </script>";
        // Limpiar la variable de sesión para que la alerta no se muestre al recargar
        unset($_SESSION['mensaje_alerta']);
    }
    ?>

    <form action="../app/procesar_carga.php" method="POST" enctype="multipart/form-data">

        <h1>Subir Partitura y Metadatos</h1>
        
        <div class="select-category-buttons" role="group" aria-label="Selección de Categoría">
            <button type="button" data-category="universal" id="btn-universal">Partituras Universales</button>
            <button type="button" data-category="popular" id="btn-popular">Partituras Populares</button>
        </div>
        
        <input type="hidden" name="categoria_obra" id="categoria_obra" required>
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
        
        <div id="campo_opus">
            <label for="opus">OPUS:</label>
            <input type="text" name="opus" id="opus" placeholder="Ej: Op. 55 / BWV 1007">
        </div>
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
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const btnUniversal = document.getElementById('btn-universal');
            const btnPopular = document.getElementById('btn-popular');
            const campoOpus = document.getElementById('campo_opus');
            const inputOpus = document.getElementById('opus');
            const inputCategoria = document.getElementById('categoria_obra');
            const form = document.querySelector('form');

            function setActiveButton(category) {
                btnUniversal.classList.remove('active');
                btnPopular.classList.remove('active');
                
                if (category === 'universal') {
                    btnUniversal.classList.add('active');
                    campoOpus.style.display = 'block'; // Mostrar OPUS
                    inputOpus.required = true;         // OPUS requerido para Universal
                    inputCategoria.value = 'universal';
                } else if (category === 'popular') {
                    btnPopular.classList.add('active');
                    campoOpus.style.display = 'none';  // Ocultar OPUS
                    inputOpus.required = false;        // OPUS no requerido para Popular
                    inputOpus.value = '';              // Limpiar valor si se oculta
                    inputCategoria.value = 'popular';
                }
            }

            btnUniversal.addEventListener('click', () => setActiveButton('universal'));
            btnPopular.addEventListener('click', () => setActiveButton('popular'));

            // Inicializar el formulario en modo Popular por defecto (o el que prefieras)
            // Esto asegura que el campo oculto 'categoria_obra' siempre tenga un valor.
            setActiveButton('popular'); 
            
            // Opcional: Impedir el envío si la categoría no está seleccionada (aunque el hidden input ya está requerido)
            form.addEventListener('submit', function(e) {
                if (!inputCategoria.value) {
                    alert('Por favor, selecciona si es Partitura Universal o Popular.');
                    e.preventDefault();
                }
            });
        });
    </script>
    <script src="../js/script.js"></script>
</body>
</html>