<?php
// ===================================================================
// 1. CONEXIÓN Y CONSULTA SQL (Contraseña: '')
// ===================================================================
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', ''); // ¡Contraseña vacía!
define('DB_NAME', 'biblioteca');

$conexion = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conexion->connect_error) {
    die("Fallo de conexión a la BD: " . $conexion->connect_error);
}

// Consulta para obtener todos los datos, incluyendo la miniatura
$sql = "SELECT 
    O.titulo, 
    O.opus,
    O.orquestacion,
    O.estado_fisico,
    O.ruta_pdf,
    O.ruta_miniatura,
    A.nombre AS autor_nombre, 
    A.apellido AS autor_apellido
FROM obras O
INNER JOIN autores A ON O.id_autor = A.id_autor
ORDER BY A.apellido, O.titulo";

$resultado = $conexion->query($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Catálogo General de Partituras</title>
    <link rel="stylesheet" href="estilos.css"> 
    <link rel="stylesheet" href="css/styles.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
</head>
<body>

    <header>
        <a href="" class="logo"></a>
        <nav>
        <a href="biblioteca.php">Inicio</a>
        <a href="nosotros.php">Nosotros</a>
        <a href="biblioteca.php" class="logo">
            <img src="src/images.png" alt="Logo">
        </a>
        <a href="contacto.php">Contacto</a>
        <a href="cargar_obra.php">Subir Archivo</a>
        </nav>
    </header>

    <section class="banner">
    <div class="banner-content">
        
      <div class="banner-text">
          <h1>Archivo Musical de La Orquesta Sinfónica del Chaco</h1>
          <h2>"Elena Córdoba, Armando Di Doménica"</h2>
          <p>Preservando el patrimonio musical de la región.</p>
      </div>

    </div>
    </section>

    <div class="contenedor-principal">
        
        <aside>
        <h2>Filtros</h2>
        
        <h3>Género</h3>
        <ul>
            <li><input type="radio" name="filtro"> Clásico</li>
            <li><input type="radio" name="filtro"> Tango</li>
            <li><input type="radio" name="filtro"> Folclore</li>
            <li><input type="radio" name="filtro"> Contemporaneo</li>
        </ul>
    
        <h3>Instrumentación</h3>
        <ul>
            <li><input type="checkbox"> Violín</li>
            <li><input type="checkbox"> Piano</li>
            <li><input type="checkbox"> Banda Completa</li>
            <li><input type="checkbox"> Cuerdas</li>
            <li><input type="checkbox"> Coro</li>
        </ul>

        <h3>Ordenar</h3>
        <ul>
            <li><input type="radio" name="orden"> Relevancia</li>
            <li><input type="radio" name="orden"> Más antiguo</li>
            <li><input type="radio" name="orden"> Más reciente</li>
            <li><input type="radio" name="orden"> Alfabeticamente / Ascendente </li>
            <li><input type="radio" name="orden"> Alfabeticamente / Descendente </li>
        </ul>

        </aside>

        <main class="catalogo-contenido">
            <input type="text" placeholder="Búsqueda rápida..." class="barra-busqueda">
            <h1>Catálogo General de Partituras</h1>
            
            <div class="catalogo-listado">
            
            <?php
            // Bucle que genera una tarjeta por cada obra
            if ($resultado->num_rows > 0) {
                while($fila = $resultado->fetch_assoc()) {


                    $nombre_completo = htmlspecialchars($fila["autor_nombre"]) . " " . htmlspecialchars($fila["autor_apellido"]);
                    $ruta_pdf_url = htmlspecialchars($fila["ruta_pdf"]);
                    $ruta_miniatura_url = htmlspecialchars($fila["ruta_miniatura"]);

                    echo '<div class="obra-card">';
                    echo '  <img src="' . $ruta_miniatura_url . '" alt="Miniatura de ' . htmlspecialchars($fila["titulo"]) . '" class="miniatura-catalogo">'; 
                    echo '  <h3 class="obra-titulo">' . htmlspecialchars($fila["titulo"]) . '</h3>';        
                    echo '  <p class="obra-autor autor-rojo">' . $nombre_completo . '</p>'; 
                    echo '  <p class="obra-estado">Estado físico: ' . htmlspecialchars($fila["estado_fisico"]) . '</p>';
                    echo '  <p class="obra-enlace"><a href="' . $ruta_pdf_url . '" target="_blank">Ver Partitura (PDF)</a></p>'; 
                    echo '</div>'; 


                }
            } else {
                echo "<p>No hay obras cargadas en el catálogo.</p>";
            }
            
            $conexion->close();
            ?>
            
            </div> 
        </main>
        
    </div> 
    
    <!-- FOOTER -->
  <footer>
    <div class="footer-container">   
          <div class="footer-col footer-nav">
              <h4>Navegación Rápida</h4>
              <ul>
                  <li><a href="/index.html">Inicio</a></li>
                  <li><a href="/pages/aboutus.html">Nosotros</a></li>
                  <li><a href="/pages/contactUs.html">Contacto</a></li>
                  <li><a href="/pages/loadingPage.html">Cargar Archivo</a></li>
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
