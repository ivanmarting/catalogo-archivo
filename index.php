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
    die("Fallo de conexión a la BD: " . $conexion->connect_error);
}

// ===================================================================
// 2. PROCESAMIENTO DE FILTROS Y ORDENACIÓN (GET)
// ===================================================================

$clausula_where = [];
$parametros = ''; 
$valores = [];    

// --- 2.1. FILTRO POR CATEGORÍA UNIVERSAL/POPULAR ---
$categoria_filtro = $_GET['categoria'] ?? 'todo'; 

if ($categoria_filtro === 'universal') {
    $clausula_where[] = "O.opus IS NOT NULL AND O.opus != ''";
} elseif ($categoria_filtro === 'popular') {
    $clausula_where[] = "O.opus IS NULL OR O.opus = ''";
}

// --- 2.2. FILTROS CLÁSICOS (Género) ---
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

// --- 2.4. PROCESAMIENTO DE ORDENACIÓN ---
$ordenacion_seleccionada = $_GET['ordenar_por'] ?? 'autor_asc'; 
$mapa_ordenacion = [
    'autor_asc'      => ['campo' => 'A.apellido',        'direccion' => 'ASC'],
    'autor_desc'     => ['campo' => 'A.apellido',        'direccion' => 'DESC'],
    'editorial_asc'  => ['campo' => 'E.nombre',          'direccion' => 'ASC'],
    'editorial_desc' => ['campo' => 'E.nombre',          'direccion' => 'DESC'],
    'anio_desc'      => ['campo' => 'O.anio_composicion','direccion' => 'DESC'], 
    'anio_asc'       => ['campo' => 'O.anio_composicion','direccion' => 'ASC']  
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

if (!empty($clausula_where)) {
    $sql .= " WHERE " . implode(" AND ", $clausula_where);
}

$sql .= " GROUP BY O.id_obra, O.titulo, O.anio_composicion, O.nro_inventario, O.ruta_miniatura, O.opus, A.nombre, A.apellido, E.nombre, G.nombre";
$sql .= " ORDER BY " . $campo_orden . " " . $direccion_orden . ", O.titulo ASC"; 

$stmt = $conexion->prepare($sql);
if ($stmt) {
    if (!empty($parametros)) {
        $stmt->bind_param($parametros, ...$valores);
    }
    $stmt->execute();
    $resultado = $stmt->get_result();
} else {
    die("Error SQL: " . $conexion->error);
}

// Consultas Auxiliares para los filtros laterales
$generos_q = $conexion->query("SELECT id_genero, nombre FROM generos ORDER BY nombre");
$categorias_q = $conexion->query("SELECT DISTINCT categoria FROM instrumentos ORDER BY categoria");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Catálogo General de Partituras</title>
    <link rel="stylesheet" href="css/estilos.css"> 
    <link rel="stylesheet" href="css/responsive.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Lora:ital,wght@0,400;0,500;0,600;0,700;1,400;1,500;1,700&display=swap" rel="stylesheet">
    
    <script>
        // Script para mantener los filtros activos al recargar
        document.addEventListener('DOMContentLoaded', function() {
            const params = new URLSearchParams(window.location.search);
            
            params.forEach((value, key) => {
                if (key.endsWith('[]')) { 
                    const name = key.slice(0, -2);
                    const elements = document.querySelectorAll(`input[name="${key}"][value="${value}"]`);
                    elements.forEach(el => el.checked = true);
                    const idToggle = `toggle-${name.replace('filtro_', '')}`;
                    const toggle = document.getElementById(idToggle);
                    if (toggle) toggle.checked = true;
                }
            });

            const ordenarPor = params.get('ordenar_por') || 'autor_asc'; 
            const radioOrder = document.querySelector(`input[name="ordenar_por"][value="${ordenarPor}"]`);
            if (radioOrder) radioOrder.checked = true;
            
            const toggleOrder = document.getElementById('toggle-ordenar');
            if (toggleOrder) toggleOrder.checked = true;

            const busquedaTexto = params.get('busqueda_texto');
            if (busquedaTexto) document.querySelector('.barra-busqueda').value = busquedaTexto;
            
            const btnLimpiar = document.getElementById('btn-limpiar');
            if (btnLimpiar) btnLimpiar.addEventListener('click', (e) => { e.preventDefault(); window.location.href = 'index.php'; });
            
            const cat = params.get('categoria') || 'todo';
            const btnActivo = document.querySelector(`.category-button[data-category="${cat}"]`);
            if (btnActivo) btnActivo.classList.add('active');
        });

        // Búsqueda con Enter
        document.addEventListener('DOMContentLoaded', function() {
            const barra = document.querySelector('#main-search-wrapper .barra-busqueda');
            if (barra) {
                barra.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault(); 
                        const f = document.createElement('form');
                        f.action = 'index.php'; f.method = 'GET';
                        const i = document.createElement('input');
                        i.type = 'hidden'; i.name = 'busqueda_texto'; i.value = this.value;
                        f.appendChild(i);
                        const p = new URLSearchParams(window.location.search);
                        const c = p.get('categoria');
                        if (c) {
                            const ic = document.createElement('input');
                            ic.type = 'hidden'; ic.name = 'categoria'; ic.value = c;
                            f.appendChild(ic);
                        }
                        document.body.appendChild(f);
                        f.submit(); 
                    }
                });
            }
        });
    </script>
    <style>
        /* ESTILOS EXTRA PARA ELEMENTOS DINÁMICOS */
        .search-area-wrapper { display: flex; flex-direction: column; align-items: center; padding: 10px 0; width: 100%; margin-bottom: 30px; }
        .category-buttons { display: flex; justify-content: center; gap: 15px; margin-bottom: 10px; width: 100%; }
        .category-button { padding: 10px 20px; border: 2px solid #ccc; background-color: #ffffff; color: #333; cursor: pointer; border-radius: 5px; font-weight: bold; text-decoration: none; text-align: center; flex-grow: 1; max-width: 150px; }
        .category-button.active { border-color: var(--color-acento, #c00); background-color: #ffe0e0; }
        .barra-busqueda { width: 80%; margin: 0; }
        .sidebar-filtros { background-color: transparent !important; padding: 0 !important; box-shadow: none !important; margin: 0 !important; width: 100%; }
        .sidebar-filtros form { background-color: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); width: 100%; box-sizing: border-box; margin-top: -15px; }
        .btn-limpiar { display: block; width: 100%; padding: 8px; margin-top: 5px; background-color: #f8f9fa; color: #6c757d; border: 1px solid #ced4da; border-radius: 4px; cursor: pointer; text-align: center; font-size: 0.9em; }
        .btn-limpiar:hover { background-color: #e2e6ea; }

        /* === ESTILO PARA EL BOTÓN DE SOLICITAR ACCESO (Estilo Original) === */
        .btn-solicitar-acceso {
            display: inline-block;
            background-color: #fff;           /* Fondo Blanco */
            color: var(--color-acento, #c00); /* Texto Rojo */
            border: 2px solid var(--color-acento, #c00); /* Borde Rojo */
            padding: 8px 12px;
            border-radius: 4px;
            text-decoration: none;
            font-size: 0.9em;
            font-weight: bold;
            transition: all 0.3s ease;
            margin-top: 10px;
        }
        
        .btn-solicitar-acceso:hover {
            background-color: var(--color-acento, #c00); /* Fondo Rojo al pasar mouse */
            color: white;                                /* Texto Blanco al pasar mouse */
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
            transform: translateY(-2px);
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
            <!-- CAMBIO: Enlace al Login -->
            <a href="Pages/login.php" style="color: #555;">Acceso Admin</a> 
        </nav>
    </header>
    
    <section class="banner">
        <div class="banner-content">
            <div class="banner-text">
                <h1>Archivo Musical De La Orquesta Sinfónica del Chaco</h1>
                <h2>"Elena Córdoba, Armando Di Doménica"</h2>
                <p>Preservando el patrimonio musical de la región.</p>
            </div>
            <div class="banner-image"></div>
        </div>
    </section>
    
    <div class="contenedor-principal">
        <aside class="sidebar-filtros">
            <form action="index.php" method="GET">
                <button type="submit" class="btn-filtrar" style="background-color: var(--color-acento);">Aplicar Filtros</button>
                <button type="button" id="btn-limpiar" class="btn-limpiar">Limpiar Filtros</button>
                
                <!-- Filtro de Género Dinámico -->
                <div class="filtro-grupo">
                    <input type="checkbox" id="toggle-genero" class="toggle-checkbox">
                    <label for="toggle-genero" class="toggle-label">Género <span class="flecha">▶</span></label>
                    <div class="toggle-content">
                        <?php if ($generos_q->num_rows > 0) $generos_q->data_seek(0);
                        while ($gen = $generos_q->fetch_assoc()): ?>
                            <label><input type="checkbox" name="filtro_genero[]" value="<?php echo $gen['id_genero']; ?>"> <?php echo htmlspecialchars($gen['nombre']); ?></label>
                        <?php endwhile; ?>
                    </div>
                </div>

                <!-- Filtro de Instrumentación Dinámico -->
                <div class="filtro-grupo">
                    <input type="checkbox" id="toggle-instrumentacion" class="toggle-checkbox">
                    <label for="toggle-instrumentacion" class="toggle-label">Instrumentación <span class="flecha">▶</span></label>
                    <div class="toggle-content">
                        <?php if ($categorias_q->num_rows > 0) $categorias_q->data_seek(0);
                        while ($cat = $categorias_q->fetch_assoc()): ?>
                            <label><input type="checkbox" name="filtro_instrumento[]" value="<?php echo htmlspecialchars($cat['categoria']); ?>"> <?php echo htmlspecialchars($cat['categoria']); ?></label>
                        <?php endwhile; ?>
                    </div>
                </div>
                
                
                <!-- Clasificación y Orden -->
                <div class="filtro-grupo">
                    <input type="checkbox" id="toggle-ordenar" class="toggle-checkbox">
                    <label for="toggle-ordenar" class="toggle-label">Ordenación <span class="flecha">▶</span></label>
                    <div class="toggle-content">
                        <h3>Autor</h3>
                        <label><input type="radio" name="ordenar_por" value="autor_asc"> A-Z</label>
                        <label><input type="radio" name="ordenar_por" value="autor_desc"> Z-A</label>
                        <h3>Editorial</h3>
                        <label><input type="radio" name="ordenar_por" value="editorial_asc"> A-Z</label>
                        <label><input type="radio" name="ordenar_por" value="editorial_desc"> Z-A</label>
                        <h3>Año</h3>
                        <label><input type="radio" name="ordenar_por" value="anio_desc"> Reciente</label>
                        <label><input type="radio" name="ordenar_por" value="anio_asc"> Antiguo</label>
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
                <input type="text" placeholder="Búsqueda rápida por Título o Autor..." class="barra-busqueda" value="<?php echo htmlspecialchars($busqueda_texto); ?>">
            </div>
            
            <h1>Catálogo General de Partituras</h1>
            
            <div class="catalogo-listado">
            <?php
            if ($resultado->num_rows > 0) {
                while($fila = $resultado->fetch_assoc()) {
                    $ruta_miniatura_url = htmlspecialchars($fila["ruta_miniatura"]);
                    echo '<div class="obra-card">';
                    echo '  <img src="' . $ruta_miniatura_url . '" alt="Miniatura" class="miniatura-catalogo">'; 
                    
                    $titulo_display = htmlspecialchars($fila["titulo"]);
                    if (!empty($fila["opus"])) $titulo_display .= ' (' . htmlspecialchars($fila["opus"]) . ')';
                    
                    echo '  <h3 class="obra-titulo">' . $titulo_display . '</h3>'; 
                    echo '  <p class="obra-anio-pequeno">' . htmlspecialchars($fila["anio_composicion"] ?: 'Año N/D') . '</p>'; 
                    echo '  <p class="obra-autor-apellido">' . htmlspecialchars($fila["autor_apellido"]) . '</p>'; 
                    echo '  <p class="obra-autor-nombre">' . htmlspecialchars($fila["autor_nombre"]) . '</p>';
                    
                    // === BOTÓN "SOLICITAR ACCESO" (BLANCO Y ROJO) ===
                    echo '  <p class="obra-enlace">';
                    echo '    <a href="Pages/solicitar_acceso.php?id=' . $fila['id_obra'] . '" class="btn-solicitar-acceso">Solicitar Acceso</a>';
                    echo '  </p>';
                    echo '</div>'; 
                }
            } else {
                echo "<p class='mensaje-catalogo-vacio'>No se encontraron obras.</p>";
            }
            if (isset($stmt)) $stmt->close();
            $conexion->close();
            ?>
            </div>
        </main>
    </div> 

    <!-- Botón Chatbot -->
    <button id="chat-button" title="Abrir Chat de IA">
        <img src="src/comunicacion.png" alt="Icono" style="width: 50px; height: auto;">
    </button>

    <div id="chat-container" class="hidden">
        <div class="chat-header">Orquestin <button id="close-button">✖</button></div>
        <div id="chat-box"><div class="message bot-message">¡Hola! Soy Orquestin. ¿En qué puedo ayudarte hoy?</div></div>
        <div class="chat-input-area">
            <input type="text" id="user-input" placeholder="Escribe tu mensaje..." autocomplete="off">
            <button id="send-button">enter</button>
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
                      <!-- CAMBIO: Enlace al Login -->
                      <li><a href="Pages/login.php">Acceso Admin</a></li>
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