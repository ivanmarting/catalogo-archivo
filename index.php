<?php
session_start();
require_once 'config/db.php'; // Usa la nueva conexión

// Título para el header
$page_title = "Archivo Sinfonico del Chaco";

// ---------------------------------------------------------
// 1. CARGA DE DATOS PARA EL SIDEBAR (Necesario para el Frontend)
// ---------------------------------------------------------
// Estas consultas llenan las listas desplegables del sidebar
$generos      = $conexion->query("SELECT * FROM generos ORDER BY nombre");
$autores      = $conexion->query("SELECT * FROM autores ORDER BY apellido, nombre");
$instrumentos = $conexion->query("SELECT * FROM instrumentos ORDER BY nombre");
$editoriales  = $conexion->query("SELECT * FROM editoriales ORDER BY nombre");


// ---------------------------------------------------------
// 2. LÓGICA DE FILTRADO (Adaptada a tu sistema mysqli)
// ---------------------------------------------------------
$clausula_where = [];
$tipos = ''; 
$params = [];    

// A. Filtro Categoría
$cat = $_GET['categoria'] ?? 'todo'; 
if ($cat === 'universal') {
    $clausula_where[] = "O.opus IS NOT NULL AND O.opus != ''";
} elseif ($cat === 'popular') {
    $clausula_where[] = "O.opus IS NULL OR O.opus = ''";
}

// B. Búsqueda Texto
if (!empty($_GET['busqueda_texto'])) {
    $txt = '%' . $_GET['busqueda_texto'] . '%'; 
    $clausula_where[] = "(O.titulo LIKE ? OR A.nombre LIKE ? OR A.apellido LIKE ?)";
    $tipos .= 'sss';
    array_push($params, $txt, $txt, $txt);
}

// C. Filtro Género
if (!empty($_GET['filtro_genero'])) {
    $ids_gen = array_map('intval', $_GET['filtro_genero']);
    $in  = str_repeat('?,', count($ids_gen) - 1) . '?';
    $clausula_where[] = "O.id_genero IN ($in)";
    $tipos .= str_repeat('i', count($ids_gen));
    $params = array_merge($params, $ids_gen);
}

// D. Filtro Autores (NUEVO)
if (!empty($_GET['filtro_autor'])) {
    $ids_aut = array_map('intval', $_GET['filtro_autor']);
    $in  = str_repeat('?,', count($ids_aut) - 1) . '?';
    $clausula_where[] = "O.id_autor IN ($in)";
    $tipos .= str_repeat('i', count($ids_aut));
    $params = array_merge($params, $ids_aut);
}

// E. Filtro Editoriales (NUEVO)
if (!empty($_GET['filtro_editorial'])) {
    $ids_ed = array_map('intval', $_GET['filtro_editorial']);
    $in  = str_repeat('?,', count($ids_ed) - 1) . '?';
    $clausula_where[] = "O.id_editorial IN ($in)";
    $tipos .= str_repeat('i', count($ids_ed));
    $params = array_merge($params, $ids_ed);
}

// F. Filtro Instrumentos (NUEVO - Relación N:M)
// Usamos inyección directa sanitizada para la subconsulta por simplicidad en mysqli
if (!empty($_GET['filtro_instrumento'])) {
    $ids_inst = array_map('intval', $_GET['filtro_instrumento']);
    $ids_string = implode(',', $ids_inst);
    // Buscamos obras que tengan AL MENOS uno de los instrumentos seleccionados
    $clausula_where[] = "O.id_obra IN (SELECT id_obra FROM obras_instrumentos WHERE id_instrumento IN ($ids_string))";
}


// ---------------------------------------------------------
// 3. CONSTRUCCIÓN Y EJECUCIÓN DE LA QUERY PRINCIPAL
// ---------------------------------------------------------
$sql = "SELECT DISTINCT O.*, A.nombre as nom_aut, A.apellido as ape_aut, G.nombre as genero
        FROM obras O
        JOIN autores A ON O.id_autor = A.id_autor
        JOIN generos G ON O.id_genero = G.id_genero";

if (!empty($clausula_where)) {
    $sql .= " WHERE " . implode(" AND ", $clausula_where);
}

$sql .= " ORDER BY O.titulo ASC"; // Ordenar alfabéticamente suele ser mejor para catálogos

$stmt = $conexion->prepare($sql);

// Solo hacemos bind_param si hay parámetros dinámicos
if (!empty($params)) {
    $stmt->bind_param($tipos, ...$params);
}

$stmt->execute();
$resultado = $stmt->get_result();

// INICIO DEL HTML
require_once 'includes/header.php'; 
?>

<!-- Banner Principal -->
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
    
    <!-- Sidebar de Filtros (Responsive) -->
    <aside class="sidebar-filtros">
        <form action="index.php" method="GET">
            <h3>Filtrar Catálogo</h3>
            
            <!-- BUSCADOR -->
            <div class="filtro-grupo">
                <input type="text" name="busqueda_texto" class="form-control" placeholder="Buscar título o autor..." 
                       value="<?php echo htmlspecialchars($_GET['busqueda_texto'] ?? ''); ?>">
            </div>

            <!-- CATEGORÍA -->
            <div class="filtro-grupo">
                <span class="filtro-titulo">Categoría</span>
                <select name="categoria" style="width:100%; padding:8px;">
                    <option value="todo">Todas</option>
                    <option value="universal" <?php echo $cat=='universal'?'selected':''; ?>>Universal (Con Opus)</option>
                    <option value="popular" <?php echo $cat=='popular'?'selected':''; ?>>Popular</option>
                </select>
            </div>

            <!-- GÉNEROS -->
            <div class="filtro-grupo">
                <span class="filtro-titulo">Géneros</span> 
                <div class="scroll-box">
                    <?php while($g = $generos->fetch_assoc()): ?>
                        <label class="checkbox-label">
                            <input type="checkbox" name="filtro_genero[]" value="<?php echo $g['id_genero'];?>"
                            <?php if(isset($_GET['filtro_genero']) && in_array($g['id_genero'], $_GET['filtro_genero'])) echo 'checked'; ?>>
                            <?php echo htmlspecialchars($g['nombre']); ?>
                        </label>
                    <?php endwhile; ?>
                </div>
            </div>

            <!-- INSTRUMENTOS (Nuevo) -->
            <div class="filtro-grupo">
                <span class="filtro-titulo">Instrumentación</span>
                <div class="scroll-box">
                    <?php while($inst = $instrumentos->fetch_assoc()): ?>
                        <label class="checkbox-label">
                            <input type="checkbox" name="filtro_instrumento[]" value="<?php echo $inst['id_instrumento'];?>"
                            <?php if(isset($_GET['filtro_instrumento']) && in_array($inst['id_instrumento'], $_GET['filtro_instrumento'])) echo 'checked'; ?>>
                            <?php echo htmlspecialchars($inst['nombre']); ?>
                        </label>
                    <?php endwhile; ?>
                </div>
            </div>

            <!-- AUTORES (Nuevo) -->
            <div class="filtro-grupo">
                <span class="filtro-titulo">Compositores</span>
                <div class="scroll-box">
                    <?php while($a = $autores->fetch_assoc()): ?>
                        <label class="checkbox-label">
                            <input type="checkbox" name="filtro_autor[]" value="<?php echo $a['id_autor'];?>"
                            <?php if(isset($_GET['filtro_autor']) && in_array($a['id_autor'], $_GET['filtro_autor'])) echo 'checked'; ?>>
                            <?php echo htmlspecialchars($a['apellido'] . ', ' . $a['nombre']); ?>
                        </label>
                    <?php endwhile; ?>
                </div>
            </div>

             <!-- EDITORIALES (Nuevo) -->
             <div class="filtro-grupo">
                <span class="filtro-titulo">Editoriales</span>
                <div class="scroll-box">
                    <?php while($e = $editoriales->fetch_assoc()): ?>
                        <label class="checkbox-label">
                            <input type="checkbox" name="filtro_editorial[]" value="<?php echo $e['id_editorial'];?>"
                            <?php if(isset($_GET['filtro_editorial']) && in_array($e['id_editorial'], $_GET['filtro_editorial'])) echo 'checked'; ?>>
                            <?php echo htmlspecialchars($e['nombre']); ?>
                        </label>
                    <?php endwhile; ?>
                </div>
            </div>

            <button type="submit" class="btn-filtrar" style="margin-top:15px;">Aplicar Filtros</button>
            <a href="index.php" style="display:block; text-align:center; margin-top:10px; font-size:0.9em; color:#666;">Limpiar Filtros</a>
        </form>
    </aside>

    <!-- Listado de Obras -->
    <main class="catalogo-contenido">
        <?php if ($resultado->num_rows > 0): ?>
            <div class="catalogo-listado">
                <?php while($fila = $resultado->fetch_assoc()): ?>
                    <article class="obra-card">
                        <!-- Miniatura con fallback por si no hay imagen -->
                        <img src="<?php echo !empty($fila['ruta_miniatura']) ? htmlspecialchars($fila['ruta_miniatura']) : 'src/default-score.jpg'; ?>" 
                             alt="Partitura" class="miniatura-catalogo" 
                             onerror="this.src='src/default_score.png'">
                        
                        <div class="card-body">
                            <h3 class="obra-titulo"><?php echo htmlspecialchars($fila['titulo']); ?></h3>
                            <div class="obra-meta">
                                <p>👤 <?php echo htmlspecialchars($fila['ape_aut'] . ', ' . $fila['nom_aut']); ?></p>
                                <p>🎼 <?php echo htmlspecialchars($fila['genero']); ?></p>
                                <?php if($fila['opus']): ?>
                                    <p><small><?php echo htmlspecialchars($fila['opus']); ?></small></p>
                                <?php endif; ?>
                            </div>
                            <a href="Pages/solicitar_acceso.php?id=<?php echo $fila['id_obra']; ?>" class="btn-solicitar">
                                Solicitar Acceso
                            </a>
                        </div>
                    </article>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div style="text-align:center; padding: 40px; width: 100%;">
                <h3>No se encontraron obras con esos criterios.</h3>
                <p>Intenta desmarcar algunos filtros para ver más resultados.</p>
            </div>
        <?php endif; ?>
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

<script src="js/script.js"></script>

<?php require_once 'includes/footer.php'; ?>