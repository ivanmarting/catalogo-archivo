<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Nosotros</title>
    
    <link rel="stylesheet" href="../css/estilos.css"> 
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
</head>
<body>

    <header>
    <nav>
        <a href="../index.php">Inicio</a>
        <a href="nosotros.php">Nosotros</a>
        <a href="../index.php"> <img src="../src/images.png" alt="Logo" class="logo"> </a>
        <a href="contacto.php">Contacto</a>
        <!-- Enlace actualizado: Lleva al Login en lugar de Cargar Obra -->
        <a href="login.php" style="color: #555;">Acceso Admin</a> 
    </nav>
    </header>

    <section class="nosotros-section">
      <div class="nosotros-header">
          <h2>Nuestra Misión y Visión</h2>
          <p>En nuestra institución, creemos que el acceso a partituras de alta calidad debe ser universal. Conoce los pilares que nos mueven.</p>
      </div>

      <div class="nosotros-cards-container">
          
          <div class="nosotros-card">
              <div class="card-icon">🎼</div>
              <h3>Acceso Universal</h3>
              <p>Trabajamos para que cualquier músico, sin importar su nivel o ubicación, pueda encontrar y descargar las partituras que necesita.</p>
          </div>

          <div class="nosotros-card">
              <div class="card-icon">✨</div>
              <h3>Calidad y Detalle</h3>
              <p>Nos enfocamos en asegurar que cada partitura cargada sea fiel al original y contenga todos los metadatos relevantes (autor, instrumentación, dificultad).</p>
          </div>

          <div class="nosotros-card">
              <div class="card-icon">⭐</div>
              <h3>Comunidad Musical</h3>
              <p>Somos un punto de encuentro para compositores e intérpretes. Nuestro propósito es enriquecer la cultura musical digital.</p>
          </div>
      </div>

      <section class="nosotros-section">
        <div class="destacado-barreto">
            <div class="barreto-imagen">
                <img src="../src/Maria Clara Barreto.jpg" alt="María Clara Barreto">
            </div>
            <div class="barreto-info">
                <h3>María Clara Barreto</h3>
                <h4>Archivista de la Orquesta Sinfónica del Chaco</h4>
                <p>María Clara Barreto es la columna vertebral de nuestro patrimonio. Como Archivista de la Orquesta Sinfónica del Chaco, es la responsable directa del manejo y la preservación física de cada partitura en el archivo de la Orquesta. Esta mención honra su compromiso y su invaluable esfuerzo en el cuidado y catalogación de este legado musical. Su deseo de preservar estas obras es la fuerza que impulsa nuestra misión de digitalización y acceso universal.</p>
                <p class="rol-destacado">"Trabajar con el Archivo es preservar la historia y facilitar el futuro de la música regional."</p>
            </div>
        </div>
        
        <hr class="separator-barreto">
    
        <div class="nosotros-cards-container">
        </div>
      </section>
    </section>

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