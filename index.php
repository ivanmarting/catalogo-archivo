<?php
// ===================================================================
// 1. CONFIGURACIÓN Y CONEXIÓN
// ===================================================================
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', ''); 
define('DB_NAME', 'aosch_bd'); 

$conexion = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conexion->connect_error) {
    // Es CRUCIAL que el servidor MySQL esté corriendo para evitar este error.
    die("Fallo de conexión a la BD: " . $conexion->connect_error);
}

// ===================================================================
// 2. PROCESAMIENTO DE FILTROS Y ORDENACIÓN (GET)
// ===================================================================

$clausula_where = [];
$parametros = ''; 
$valores = [];    

// --- 2.1. FILTRO POR CATEGORÍA UNIVERSAL/POPULAR (NUEVO) ---
$categoria_filtro = $_GET['categoria'] ?? 'todo'; // 'universal', 'popular', o 'todo' (default)

if ($categoria_filtro === 'universal') {
    // Filtro Universal: OPUS NO es NULL
    $clausula_where[] = "O.opus IS NOT NULL AND O.opus != ''";
} elseif ($categoria_filtro === 'popular') {
    // Filtro Popular: OPUS es NULL o cadena vacía
    $clausula_where[] = "O.opus IS NULL OR O.opus = ''";
}


// --- 2.2. FILTROS CLÁSICOS (WHERE con múltiples OR) ---

// a) Género
if (!empty($_GET['filtro_genero']) && is_array($_GET['filtro_genero'])) {
    $generos = array_map('intval', $_GET['filtro_genero']);
    if (!empty($generos)) {
        $marcadores = implode(',', array_fill(0, count($generos), '?'));
        $clausula_where[] = "O.id_genero IN ({$marcadores})";
        $parametros .= str_repeat('i', count($generos));
        $valores = array_merge($valores, $generos);
    }
}

// --- 2.3. FILTRO ESPECIAL: INSTRUMENTACIÓN ---

if (!empty($_GET['filtro_instrumento']) && is_array($_GET['filtro_instrumento'])) {
    $categorias_instr = $_GET['filtro_instrumento'];
    
    foreach ($categorias_instr as $categoria) {
        $cat_escapada = $conexion->real_escape_string($categoria); 
        
        $clausula_where[] = "O.id_obra IN (
            SELECT OI.id_obra 
            FROM obras_instrumentos OI
            INNER JOIN instrumentos I ON OI.id_instrumento = I.id_instrumento
            WHERE I.categoria = ?
        )";
        $parametros .= 's';
        $valores[] = $categoria;
    }
}

// --- 2.4. PROCESAMIENTO DE ORDENACIÓN (ÚNICO RADIO GROUP) ---
$ordenacion_seleccionada = $_GET['ordenar_por'] ?? 'autor_asc'; // Default: Autor ASC

$mapa_ordenacion = [
    'autor_asc'      => ['campo' => 'A.apellido',        'direccion' => 'ASC'],
    'autor_desc'     => ['campo' => 'A.apellido',        'direccion' => 'DESC'],
    'editorial_asc'  => ['campo' => 'E.nombre',          'direccion' => 'ASC'],
    'editorial_desc' => ['campo' => 'E.nombre',          'direccion' => 'DESC'],
    'anio_desc'      => ['campo' => 'O.anio_composicion','direccion' => 'DESC'], // Mayor a Menor
    'anio_asc'       => ['campo' => 'O.anio_composicion','direccion' => 'ASC']  // Menor a Mayor
];

$orden = $mapa_ordenacion[$ordenacion_seleccionada] ?? $mapa_ordenacion['autor_asc'];
$campo_orden = $orden['campo'];
$direccion_orden = $orden['direccion'];


// --- 2.5. Búsqueda por Texto ---
$busqueda_texto = $_GET['busqueda_texto'] ?? '';
if (!empty($busqueda_texto)) {
    $texto_busqueda = '%' . $busqueda_texto . '%'; 
    $clausula_where[] = "(O.titulo LIKE ? OR A.nombre LIKE ? OR A.apellido LIKE ?)";
    $parametros .= 'sss';
    $valores = array_merge($valores, [$texto_busqueda, $texto_busqueda, $texto_busqueda]);
}


// --- 2.6. CONSTRUCCIÓN FINAL DE LA CONSULTA SQL ---

$sql = "SELECT 
    O.id_obra, O.titulo, O.anio_composicion, O.nro_inventario, O.ruta_miniatura, O.opus,
    A.nombre AS autor_nombre, A.apellido AS autor_apellido,
    E.nombre AS editorial_nombre, G.nombre AS genero_nombre,
    COUNT(AP.id_obra) AS cantidad_pdfs 
FROM obras O
INNER JOIN autores A ON O.id_autor = A.id_autor
LEFT JOIN editoriales E ON O.id_editorial = E.id_editorial
INNER JOIN generos G ON O.id_genero = G.id_genero
LEFT JOIN archivos_pdf AP ON O.id_obra = AP.id_obra";

// Aplicar la cláusula WHERE
if (!empty($clausula_where)) {
    $sql .= " WHERE " . implode(" AND ", $clausula_where);
}

// Se necesita GROUP BY antes de aplicar ORDER BY
$sql .= " GROUP BY O.id_obra, O.titulo, O.anio_composicion, O.nro_inventario, O.ruta_miniatura, O.opus, A.nombre, A.apellido, E.nombre, G.nombre";

// Aplicar la ordenación
$sql .= " ORDER BY " . $campo_orden . " " . $direccion_orden . ", O.titulo ASC"; 


// ----------------------------------------------------
// 2.7. EJECUCIÓN DE LA CONSULTA PRINCIPAL
// ----------------------------------------------------
$stmt = $conexion->prepare($sql);

if ($stmt) {
    if (!empty($parametros)) {
        // Ejecución con bind_param
        $stmt->bind_param($parametros, ...$valores);
    }
    $stmt->execute();
    $resultado = $stmt->get_result();
} else {
    die("Error al preparar la consulta SQL: " . $conexion->error);
}


// ----------------------------------------------------
// 2.8. CONSULTAS para la barra de FILTROS (sidebar)
// ----------------------------------------------------
$generos_q = $conexion->query("SELECT id_genero, nombre FROM generos ORDER BY nombre");
$categorias_q = $conexion->query("SELECT DISTINCT categoria FROM instrumentos ORDER BY categoria");
$autores_q = $conexion->query("SELECT id_autor, nombre, apellido FROM autores ORDER BY apellido, nombre");
$editoriales_q = $conexion->query("SELECT id_editorial, nombre FROM editoriales ORDER BY nombre");
$anios_q = $conexion->query("SELECT DISTINCT anio_composicion FROM obras WHERE anio_composicion IS NOT NULL AND anio_composicion <> '' ORDER BY anio_composicion DESC");

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Catálogo General de Partituras</title>
    <link rel="stylesheet" href="css/estilos.css"> 
    <script>
        // Script para rellenar automáticamente los filtros después de la recarga
        document.addEventListener('DOMContentLoaded', function() {
            const params = new URLSearchParams(window.location.search);
            
            // Rellenar checkboxes (filtros clásicos)
            params.forEach((value, key) => {
                if (key.endsWith('[]')) { 
                    const name = key.slice(0, -2);
                    const elements = document.querySelectorAll(`input[name="${key}"][value="${value}"]`);
                    elements.forEach(el => el.checked = true);
                    
                    // Asegurar que el collapse (toggle-checkbox) esté abierto
                    const idToggle = `toggle-${name.replace('filtro_', '')}`;
                    const toggleCheckbox = document.getElementById(idToggle);
                    if (toggleCheckbox) {
                        toggleCheckbox.checked = true;
                    }
                }
            });

            // Rellenar campos de Ordenación
            const ordenarPor = params.get('ordenar_por') || 'autor_asc'; // Usar default si no existe
            const radioOrder = document.querySelector(`input[name="ordenar_por"][value="${ordenarPor}"]`);
            if (radioOrder) radioOrder.checked = true;
            
            // Abrir el toggle de la sección de Ordenar
            const toggleOrderCheckbox = document.getElementById('toggle-ordenar');
            if (toggleOrderCheckbox) {
                toggleOrderCheckbox.checked = true;
            }
            

            // Rellenar campo de Búsqueda
            const busquedaTexto = params.get('busqueda_texto');
            if (busquedaTexto) {
                document.querySelector('.barra-busqueda').value = busquedaTexto;
            }
            
            // ⚠️ Script: Detectar el botón de Limpiar Filtros
            const botonLimpiar = document.getElementById('btn-limpiar');
            if (botonLimpiar) {
                botonLimpiar.addEventListener('click', function(e) {
                    e.preventDefault(); 
                    
                    // Al limpiar, redirigir a index.php (vacío de filtros ni categoría)
                    window.location.href = 'index.php'; 
                });
            }
            
            // ⚠️ Script: Marcar botón de categoría activo (usando la nueva clase y estructura)
            const categoriaActiva = params.get('categoria') || 'todo';
            const btnActivo = document.querySelector(`.category-button[data-category="${categoriaActiva}"]`);
            if (btnActivo) {
                btnActivo.classList.add('active');
            }
        });

        // Script para que el input de Búsqueda rápida envíe el formulario principal
        document.addEventListener('DOMContentLoaded', function() {
            // Buscamos la barra dentro del nuevo wrapper
            const barraBusqueda = document.querySelector('#main-search-wrapper .barra-busqueda');
            const formFiltros = document.querySelector('.sidebar-filtros form');

            if (barraBusqueda && formFiltros) {
                barraBusqueda.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault(); 
                        
                        // Creamos un formulario temporal para la búsqueda rápida
                        const tempForm = document.createElement('form');
                        tempForm.action = 'index.php';
                        tempForm.method = 'GET';

                        // 1. Campo de búsqueda
                        const inputBusqueda = document.createElement('input');
                        inputBusqueda.type = 'hidden';
                        inputBusqueda.name = 'busqueda_texto';
                        inputBusqueda.value = this.value;
                        tempForm.appendChild(inputBusqueda);

                        // 2. Campo de categoría (para mantener el filtro activo)
                        const params = new URLSearchParams(window.location.search);
                        const categoria = params.get('categoria');
                        if (categoria) {
                            const hiddenCategoria = document.createElement('input');
                            hiddenCategoria.type = 'hidden';
                            hiddenCategoria.name = 'categoria';
                            hiddenCategoria.value = categoria;
                            tempForm.appendChild(hiddenCategoria);
                        }
                        
                        document.body.appendChild(tempForm);
                        tempForm.submit(); 
                    }
                });
            }
        });
    </script>
    <style>
        /* Estilos básicos para el nuevo botón de limpiar (Asumiendo que 'estilos.css' tiene 'var(--color-acento)') */
        .btn-limpiar {
            display: block;
            width: 100%;
            padding: 8px;
            margin-top: 5px; 
            background-color: #f8f9fa; 
            color: #6c757d; 
            border: 1px solid #ced4da;
            border-radius: 4px;
            cursor: pointer;
            text-align: center;
            font-size: 0.9em;
            transition: background-color 0.2s, color 0.2s;
        }

        .btn-limpiar:hover {
            background-color: #e2e6ea;
            color: #495057;
        }
        
        /* Contenedor principal para centrar botones y búsqueda horizontalmente */
        .search-area-wrapper {
            display: flex; 
            flex-direction: column;
            align-items: center; 
            padding: 10px 0;
            width: 100%; 
            margin-bottom: 30px; 
        }

        /* Estilos para los botones de categoría */
        .category-buttons {
            display: flex;
            justify-content: center; 
            gap: 15px;
            margin-bottom: 10px; 
            width: 100%; 
        }
        .category-button {
            padding: 10px 20px;
            border: 2px solid #ccc;
            background-color: #ffffff;
            color: #333;
            cursor: pointer;
            border-radius: 5px;
            font-weight: bold;
            text-decoration: none; 
            transition: background-color 0.2s, border-color 0.2s;
            text-align: center; 
            flex-grow: 1; 
            max-width: 150px; 
        }
        .category-button.active {
            border-color: var(--color-acento, red);
            background-color: #ffe0e0;
        }
        .category-button:hover {
            background-color: #f0f0f0;
        }
        
        /* La barra de búsqueda ahora usa un ancho flexible */
        .barra-busqueda {
            width: 80%; 
            margin: 0; 
        }
        
        /* 1. ELIMINAR LA CAJA DE FONDO (caja roja/exterior) */
        .sidebar-filtros {
            /* Hacemos transparente el aside */
            background-color: transparent !important;
            padding: 0 !important; 
            box-shadow: none !important;
            margin: 0 !important; 
            width: 100%;
        }

        /* 2. MANTENER LA CAJA DE LOS BOTONES (caja verde/blanca) */
        /* Eliminamos el padding superior y margen negativo que cubría el espacio transparente. */
        .sidebar-filtros form {
            background-color: #fff;
            padding: 0 20px 20px 20px; /* Quitamos el padding superior (top: 0), mantenemos el resto */
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); 
            width: 100%;
            box-sizing: border-box; 
            /* AÑADIDO: Margen negativo para eliminar el recuadro transparente superior */
            margin-top: -15px; /* Ajustar este valor puede ser necesario */
            /* Ajuste el padding-top del formulario para que el botón toque el borde */
            padding-top: 20px;
        }

        /* Si el espacio lateral persiste, es debido al width/padding/margin del aside.
           Añadimos un ajuste final para eliminar el padding lateral del formulario. */
        .sidebar-filtros form {
            /* Reajustamos para pegarlo a los bordes laterales del área de la barra lateral */
            margin: -15px 0 0 0; 
            padding: 20px; 
        }
        
    </style>
</head>
<body>

    <header> 
        <nav>
            <a href="index.php">Inicio</a>
            <a href="Pages/nosotros.php">Nosotros</a>
            <a href="index.php"> <img src="src/images.png" alt="Logo" class="logo"> </a>
            <a href="Pages/contacto.php">Contacto</a>
            <a href="Pages/cargar_obra.php">Subir Archivo</a> 
        </nav>
    </header>
    
    <section class="banner">
        <div class="banner-content">
            <div class="banner-text">
                <h1>Archivo Musical De La Orquesta Sinfónica del Chaco</h1>
                <h2>"Elena Córdoba, Armando Di Doménica"</h2>
                <p>Preservando el patrimonio musical de la región.</p>
            </div>
            <div class="banner-image">
                </div>
        </div>
    </section>
    
    <div class="contenedor-principal">
        
        <aside class="sidebar-filtros">
            
            <form action="index.php" method="GET">
            
                <button type="submit" class="btn-filtrar" style="background-color: var(--color-acento);">Aplicar Filtros</button>
                
                <button type="button" id="btn-limpiar" class="btn-limpiar">Limpiar Filtros</button>
                <div class="filtro-grupo">
                    <input type="checkbox" id="toggle-genero" class="toggle-checkbox">
                    <label for="toggle-genero" class="toggle-label">
                        Género <span class="flecha">▶</span>
                    </label>
                    <div class="toggle-content">
                        <?php 
                        if ($generos_q->num_rows > 0) $generos_q->data_seek(0);
                        while ($gen = $generos_q->fetch_assoc()): ?>
                            <label>
                                <input type="checkbox" name="filtro_genero[]" value="<?php echo $gen['id_genero']; ?>"> 
                                <?php echo htmlspecialchars($gen['nombre']); ?>
                            </label>
                        <?php endwhile; ?>
                    </div>
                </div>

                <div class="filtro-grupo">
                    <input type="checkbox" id="toggle-instrumentacion" class="toggle-checkbox">
                    <label for="toggle-instrumentacion" class="toggle-label">
                        Instrumentación (Categoría) <span class="flecha">▶</span>
                    </label>
                    <div class="toggle-content">
                        <?php 
                        if ($categorias_q->num_rows > 0) $categorias_q->data_seek(0);
                        while ($cat = $categorias_q->fetch_assoc()): ?>
                            <label>
                                <input type="checkbox" name="filtro_instrumento[]" value="<?php echo htmlspecialchars($cat['categoria']); ?>"> 
                                <?php echo htmlspecialchars($cat['categoria']); ?>
                            </label>
                        <?php endwhile; ?>
                    </div>
                </div>
                
                <hr>
                
                <div class="filtro-grupo">
                    <input type="checkbox" id="toggle-ordenar" class="toggle-checkbox">
                    <label for="toggle-ordenar" class="toggle-label">
                        Clasificación y Ordenación <span class="flecha">▶</span>
                    </label>
                    <div class="toggle-content">
                        
                        <h3>Autor (Alfabético)</h3>
                        <label>
                            <input type="radio" name="ordenar_por" value="autor_asc"> Ascendente (A-Z)
                        </label>
                        <label>
                            <input type="radio" name="ordenar_por" value="autor_desc"> Descendente (Z-A)
                        </label>
                        
                        <h3>Editorial (Alfabético)</h3>
                        <label>
                            <input type="radio" name="ordenar_por" value="editorial_asc"> Ascendente (A-Z)
                        </label>
                        <label>
                            <input type="radio" name="ordenar_por" value="editorial_desc"> Descendente (Z-A)
                        </label>

                        <h3>Año de Composición</h3>
                        <label>
                            <input type="radio" name="ordenar_por" value="anio_desc"> Mayor a Menor
                        </label>
                        <label>
                            <input type="radio" name="ordenar_por" value="anio_asc"> Menor a Mayor
                        </label>
                        
                    </div>
                </div>
                
                <?php if ($categoria_filtro !== 'todo'): ?>
                <input type="hidden" name="categoria" value="<?php echo htmlspecialchars($categoria_filtro); ?>">
                <?php endif; ?>
            
            </form>

        </aside>

        <main class="catalogo-contenido">
            
            <div class="search-area-wrapper" id="main-search-wrapper">
                
                <div class="category-buttons">
                    <a href="index.php?categoria=todo" class="category-button" data-category="todo">Todas</a>
                    <a href="index.php?categoria=universal" class="category-button" data-category="universal">Universales</a>
                    <a href="index.php?categoria=popular" class="category-button" data-category="popular">Populares</a>
                </div>
                
                <input type="text" placeholder="Búsqueda rápida por Título o Autor (Presiona Enter)..." class="barra-busqueda" value="<?php echo htmlspecialchars($busqueda_texto); ?>">
                
            </div>
            <h1>Catálogo General de Partituras</h1>
            
            <div class="catalogo-listado">
            
            <?php
            if ($resultado->num_rows > 0) {
                while($fila = $resultado->fetch_assoc()) {
                    
                    $ruta_miniatura_url = htmlspecialchars($fila["ruta_miniatura"]);

                    echo '<div class="obra-card">';
                    
                    // 1. IMAGEN DE PORTADA
                    echo '  <img src="' . $ruta_miniatura_url . '" alt="Miniatura de ' . htmlspecialchars($fila["titulo"]) . '" class="miniatura-catalogo">'; 
                    
                    // 2. NOMBRE DE OBRA
                    // Mostrar OPUS si existe
                    $titulo_display = htmlspecialchars($fila["titulo"]);
                    if (!empty($fila["opus"])) {
                         $titulo_display .= ' (' . htmlspecialchars($fila["opus"]) . ')';
                    }
                    echo '  <h3 class="obra-titulo">' . $titulo_display . '</h3>'; 
                    
                    // 3. AÑO DE COMPOSICIÓN (PEQUEÑO)
                    echo '  <p class="obra-anio-pequeno">' . htmlspecialchars($fila["anio_composicion"] ?: 'Año N/D') . '</p>'; 
                    
                    // 4. APELLIDO AUTOR
                    echo '  <p class="obra-autor-apellido">' . htmlspecialchars($fila["autor_apellido"]) . '</p>'; 
                    
                    // 5. NOMBRE AUTOR
                    echo '  <p class="obra-autor-nombre">' . htmlspecialchars($fila["autor_nombre"]) . '</p>'; 
                    
                    // ENLACE (Mantenido al final)
                    $cantidad_pdfs = (int)$fila["cantidad_pdfs"];
                    $enlace_texto = ($cantidad_pdfs === 1) ? 'Ver Partitura (1 PDF)' : "Ver Partituras ({$cantidad_pdfs} PDFs)";
                    
                    echo '  <p class="obra-enlace"><a href="Pages/detalle_obra.php?id=' . $fila['id_obra'] . '">' . $enlace_texto . '</a></p>';
                    echo '</div>'; 
                }
            } else {
                echo "<p class='mensaje-catalogo-vacio'>No se encontraron obras que coincidan con los filtros seleccionados.</p>";
            }
            
            // Cerrar el statement y la conexión
            if (isset($stmt)) $stmt->close();
            $conexion->close();
            ?>
            
            </div>
        </main>
        
    </div> 
    <button id="chat-button" title="Abrir Chat de IA">
        <img src="src/comunicacion.png" alt="Icono" style="width: 50px; height: auto;">
    </button>

    <div id="chat-container" class="hidden">
        <div class="chat-header">
            Orquestin
            <button id="close-button">✖</button>
        </div>
        <div id="chat-box">
            <div class="message bot-message">
                ¡Hola! Soy Orquestin. Tu asistente IA personal ¿En qué puedo ayudarte hoy?
            </div>
        </div>
        <div class="chat-input-area">
            <input type="text" id="user-input" placeholder="Escribe tu mensaje..." autocomplete="off">
            <button id="send-button" title="Enviar mensaje">
                enter
            </button>
        </div>
    </div>
    <footer>
        <div class="footer-container">   
              <div class="footer-col footer-nav">
                  <h4>Navegación Rápida</h4>
                  <ul>
                      <li><a href="index.php">Inicio</a></li>
                      <li><a href="Pages/nosotros.php">Nosotros</a></li>
                      <li><a href="Pages/contacto.php">Contacto</a></li>
                      <li><a href="Pages/cargar_obra.php">Cargar Archivo</a></li>
                  </ul>
              </div>
              
              <div class="footer-col footer-info">
                  <h4>Información de Contacto</h4>
                  <p>Archivo Orquesta Sinfónica del Chaco</p>
                  <p>Email: <a href="mailto:archivo@orquestadelchaco.org">archivo@orquestadelchaco.org</a></p>
                  <p><a href="#">Política de Acceso</a></p>
              </div>
              
              <div class="footer-col footer-social">
                  <h4>Síguenos</h4>
                  <div class="socials">
                      <a href="#">📘</a>
                      <a href="#">💬</a>
                      <a href="#">▶️</a>
                  </div>
                  <p class="copyright">© 2025. Todos los derechos reservados.</p>
              </div>
      
        </div>
      </footer>
      <script src="js/script.js"></script>
</body>
</html>