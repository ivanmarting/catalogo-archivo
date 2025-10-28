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
</head>
<body>

    <header>
        <nav>
            <a href="biblioteca.php">Catálogo</a> | 
            <a href="cargar_obra.php">Cargar Obra</a>
        </nav>
    </header>

    <div class="contenedor-principal">
        
        <aside class="sidebar-filtros">
            <h2>Filtros</h2>
            <div class="filtro-grupo">
                <h3>Género</h3>
                <label><input type="radio"> Clásico</label><br>
                <label><input type="radio"> Tango</label>
                </div>
            <h3>Ordenar</h3>
            <label><input type="radio" checked> Más reciente</label>
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
            
            </div> </main>
        
    </div> <footer>
        <p>© 2025 Catálogo de Partituras. Desarrollado con PHP y MySQL.</p>
    </footer>

</body>
</html>
