<?php
// ===================================================================
// 1. CONEXIÓN Y CONSULTA SQL (BD y filtros)
// ===================================================================
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', ''); 
define('DB_NAME', 'aosch_bd'); 

$conexion = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conexion->connect_error) {
    die("Fallo de conexión a la BD: " . $conexion->connect_error);
}

// ----------------------------------------------------
// 1.1. CONSULTA PRINCIPAL: Usa JOIN y GROUP BY para contar los PDFs
// ----------------------------------------------------
$sql = "SELECT 
    O.id_obra,
    O.titulo, 
    O.anio_composicion,
    O.nro_inventario,
    O.ruta_miniatura,
    A.nombre AS autor_nombre, 
    A.apellido AS autor_apellido,
    E.nombre AS editorial_nombre,
    G.nombre AS genero_nombre,
    COUNT(AP.id_obra) AS cantidad_pdfs 
FROM obras O
INNER JOIN autores A ON O.id_autor = A.id_autor
LEFT JOIN editoriales E ON O.id_editorial = E.id_editorial
INNER JOIN generos G ON O.id_genero = G.id_genero
LEFT JOIN archivos_pdf AP ON O.id_obra = AP.id_obra 
GROUP BY O.id_obra, O.titulo, O.anio_composicion, O.nro_inventario, O.ruta_miniatura, A.nombre, A.apellido, E.nombre, G.nombre
ORDER BY A.apellido, O.titulo";

$resultado = $conexion->query($sql);

// ----------------------------------------------------
// 1.2. CONSULTAS para la barra de FILTROS (sidebar) - AHORA CON MÁS CAMPOS
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
            <h2>Filtros</h2>
            <form action="" method="GET">
            
                <div class="filtro-grupo">
                    <input type="checkbox" id="toggle-autor" class="toggle-checkbox">
                    <label for="toggle-autor" class="toggle-label">
                        Autor <span class="flecha">▶</span>
                    </label>
                    <div class="toggle-content">
                        <?php while ($aut = $autores_q->fetch_assoc()): ?>
                            <label>
                                <input type="checkbox" name="filtro_autor[]" value="<?php echo $aut['id_autor']; ?>"> 
                                <?php echo htmlspecialchars($aut['apellido']) . ", " . htmlspecialchars($aut['nombre']); ?>
                            </label>
                        <?php endwhile; ?>
                    </div>
                </div>

                <div class="filtro-grupo">
                    <input type="checkbox" id="toggle-genero" class="toggle-checkbox">
                    <label for="toggle-genero" class="toggle-label">
                        Género <span class="flecha">▶</span>
                    </label>
                    <div class="toggle-content">
                        <?php 
                        // Mover el puntero al inicio si ya se usó la consulta
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
                    <input type="checkbox" id="toggle-editorial" class="toggle-checkbox">
                    <label for="toggle-editorial" class="toggle-label">
                        Editorial <span class="flecha">▶</span>
                    </label>
                    <div class="toggle-content">
                        <?php while ($edit = $editoriales_q->fetch_assoc()): ?>
                            <label>
                                <input type="checkbox" name="filtro_editorial[]" value="<?php echo $edit['id_editorial']; ?>"> 
                                <?php echo htmlspecialchars($edit['nombre']); ?>
                            </label>
                        <?php endwhile; ?>
                    </div>
                </div>

                <div class="filtro-grupo">
                    <input type="checkbox" id="toggle-instrumentacion" class="toggle-checkbox">
                    <label for="toggle-instrumentacion" class="toggle-label">
                        Instrumentación <span class="flecha">▶</span>
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

                <div class="filtro-grupo">
                    <input type="checkbox" id="toggle-anio" class="toggle-checkbox">
                    <label for="toggle-anio" class="toggle-label">
                        Año de Composición <span class="flecha">▶</span>
                    </label>
                    <div class="toggle-content">
                        <?php while ($anio = $anios_q->fetch_assoc()): ?>
                            <label>
                                <input type="checkbox" name="filtro_anio[]" value="<?php echo $anio['anio_composicion']; ?>"> 
                                <?php echo htmlspecialchars($anio['anio_composicion']); ?>
                            </label>
                        <?php endwhile; ?>
                    </div>
                </div>
            
                <button type="submit" class="btn-filtrar">Aplicar Filtros</button>

            </form>

            <hr>
            <h3>Ordenar</h3>
            <label><input type="radio" checked> Autor (Apellido)</label>
        </aside>

        <main class="catalogo-contenido">
            
            <input type="text" placeholder="Búsqueda rápida..." class="barra-busqueda">
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
                    echo '  <h3 class="obra-titulo">' . htmlspecialchars($fila["titulo"]) . '</h3>'; 
                    
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
                echo "<p class='mensaje-catalogo-vacio'>No hay obras cargadas en el catálogo. ¡Sube tu primera obra!</p>";
            }
            
            $conexion->close();
            ?>
            
            </div>
        </main>
        
    </div> 
    
    <footer>
        <div class="footer-container">   
              <div class="footer-col footer-nav">
                  <h4>Navegación Rápida</h4>
                  <ul>
                      <li><a href="../index.php">Inicio</a></li>
                      <li><a href="nosotros.php">Nosotros</a></li>
                      <li><a href="contacto.php">Contacto</a></li>
                      <li><a href="cargar_obra.php">Cargar Archivo</a></li>
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

</body>
</html>