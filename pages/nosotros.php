<?php
session_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nosotros - Orquesta Sinfónica del Chaco</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Lora:ital,wght@0,400;0,500;0,600;0,700;1,400&family=Playfair+Display:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    <link rel="stylesheet" href="../css/estilos.css">
    <link rel="stylesheet" href="../css/responsive.css">
</head>
<body>

    <header> 
    <nav>
        <a href="../index.php">Inicio</a>

        <a href="nosotros.php">Nosotros</a>

        <a href="../index.php"> 
            <img src="../src/images.png" alt="Logo" class="logo"> 
        </a>

        <a href="contacto.php">Contacto</a>
        <a href="login.php" style="color: #555;">Acceso Admin</a> 
    </nav>
</header>

    <section class="seccion-historia">
        
        <div class="contenedor-imagen-historia">
            <img src="../src/placa.jfif" alt="Historia del Archivo">
        </div>

        <div class="contenido-historia">
            <h2>Información del Archivo</h2>
            <p>
                El proceso de archivado realizado por colaboradores y participantes en la organización del archivo OSCH.
                Actualmente resguarda un patrimonio con más de 40 años de historia musical.
                Preservado para el patrimonio cultural bajo la visión de María Clara Barreto.
            </p>
        </div>
    </section>

    <hr class="separador-nosotros">

    <section class="seccion-noticias">
        <div class="noticias-content">
            <h1 class="titulo-principal">El trabajo<br> de Archivado</h1>
            <p class="descripcion-noticias">
                Objetivos y deberes de los archivistas en la organización con los colaboradores. Técnicas de control, restauración y preservación documental.
            </p>
            </div>
        
        <div class="cards-grid">
            <div class="card-nosotros">
                <i class="fa-regular fa-star icon-card"></i>
                <h3 class="titulo-card">Análisis</h3>
                <p class="texto-card">
                    Analizar y describir el fondo documental del Archivo y Biblioteca de la OSCH.
                </p>
            </div>
            <div class="card-nosotros">
                <i class="fa-solid fa-paintbrush icon-card"></i>
                <h3 class="titulo-card">Limpieza</h3>
                <p class="texto-card">
                    Limpieza preventiva de agentes de deterioro y restauración de documentos.
                </p>
            </div>
            <div class="card-nosotros">
                <i class="fa-regular fa-file-lines icon-card"></i>
                <h3 class="titulo-card">Registro</h3>
                <p class="texto-card">
                    Registro de INVENTARIO MANUAL de obras musicales en formularios especialmente diseñados.
                </p>
            </div>
            <div class="card-nosotros">
                <i class="fa-solid fa-shield-virus icon-card"></i>
                <h3 class="titulo-card">Conservación</h3>
                <p class="texto-card">
                    Detección temprana para detener el daño y aislar material afectado por humedad o agentes biológicos.
                </p>
            </div>
        </div>
    </section>

    <hr class="separador-nosotros">

    <section class="seccion-digitalizacion">
        <h2 class="titulo-digitalizacion">Proceso de Digitalización</h2>
        <div class="digitalizacion-grid">
            <div class="dig-card">
                <h3>¿Qué es Digitalizar?</h3>
                <p>
                    Es el trabajo de convertir documentos físicos en formatos digitales para conservarlos, organizarlos y acceder a ellos fácilmente.<br>
                    Asegura la preservación a largo plazo del Patrimonio oficial de la OSCH.
                </p>
                </div>
            <div class="dig-card">
                <h3>El Proceso Implica</h3>
                <p>
                    • Captura (escaneo)<br>
                    • Transformación a archivo digital<br>
                    • Organización (metadatos)<br>
                    • Almacenamiento seguro<br>
                    • Preservación a largo plazo
                </p>
                </div>
            <div class="dig-card">
                <h3>En Archivos Musicales</h3>
                <p>
                    • Escanear partituras antiguas<br>
                    • Fotografiar manuscritos<br>
                    • Convertir grabaciones analógicas<br>
                    • Clasificar por compositor y época<br>
                    • Crear catálogo web accesible
                </p>
                </div>
        </div>
    </section>

    <section class="seccion-archivero">
        <h2 class="titulo-archivero">Información del Archivista y Colaboradores</h2>
        <p class="subtitulo-archivero">
            Conoce a la Lic. María Clara Raquel Barreto y sus colaboradores en acción.
        </p>
        
        <div class="contenedor-carruseles">
            
            <div class="carousel-wrapper">
                <h4 class="carousel-title">Galería Principal</h4>
                <div class="carousel" data-carousel>
                    <div class="carousel-viewport">
                        <div class="carousel-slides">
                            <img src="../src/gallery1.jpg" alt="Foto 1">                            
                            <img src="../src/gallery2.jpg" alt="Foto 2">
                            <img src="../src/gallery3.jpg" alt="Foto 3">
                            <img src="../src/gallery4.jpg" alt="Foto 4">
                        </div>
                    </div>
                    <button class="carousel-prev">‹</button>
                    <button class="carousel-next">›</button>
                </div>
            </div>

            <div class="carousel-wrapper">
                <h4 class="carousel-title">Antes</h4>
                <div class="carousel" data-carousel>
                    <div class="carousel-viewport">
                        <div class="carousel-slides">
                            <img src="../src/before1.png" alt="Antes 1">
                            <img src="../src/before2.png" alt="Antes 2">
                        </div>
                    </div>
                    <button class="carousel-prev">‹</button>
                    <button class="carousel-next">›</button>
                </div>
            </div>

            <div class="carousel-wrapper">
                <h4 class="carousel-title">Después</h4>
                <div class="carousel" data-carousel>
                    <div class="carousel-viewport">
                        <div class="carousel-slides">
                            <img src="../src/after1.png" alt="Después 1">
                            <img src="../src/after2.png" alt="Después 2">
                        </div>
                    </div>
                    <button class="carousel-prev">‹</button>
                    <button class="carousel-next">›</button>
                </div>
            </div>

        </div>
    </section>

    <footer>
        <div class="footer-container">   
              <div class="footer-col footer-nav">
                  <h4>Navegación Rápida</h4>
                  <ul>
                      <li><a href="../index.php">Inicio</a></li>
                      <li><a href="nosotros.php">Nosotros</a></li>
                      <li><a href="contacto.php">Contacto</a></li>
                      <li><a href="login.php">Acceso Admin</a></li>
                  </ul>
              </div>
              <div class="footer-col footer-info">
                  <h4>Información de Contacto</h4>
                  <p>Archivo Orquesta Sinfónica del Chaco</p>
                  <p>Email: <a href="mailto:archivo@orquestadelchaco.org">archivo@orquestadelchaco.org</a></p>
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

    <script src="../js/script.js"></script>
    <script src="../js/carousel.js"></script>

</body>
</html>