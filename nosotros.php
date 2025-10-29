<!DOCTYPE html>
<html lang="es">
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Subir Partitura - Archivo</title>
  <link rel="stylesheet" href="estilos.css"> 
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

  <!-- BANNER -->
    
  <!-- SECCIÓN NOSOTROS -->
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
              <img src="src/Maria Clara Barreto.jpg" alt="María Clara Barreto">
          </div>
          <div class="barreto-info">
              <h3>María Clara Barreto</h3>
              <h4>Archivista de la Orquesta Sinfónica del Chaco</h4>
              <p>María Clara Barreto es la columna vertebral de nuestro patrimonio. Como Archivista de la Orquesta Sinfónica del Chaco, es la responsable directa del manejo y la preservación física de cada partitura en el archivo de la Orquesta. Esta mención honra su compromiso y su invaluable esfuerzo en el cuidado y catalogación de este legado musical. Su deseo de preservar estas obras es la fuerza que impulsa nuestra misión de digitalización y acceso universal.</p>
              <p class="rol-destacado">"Trabajar con el Archivo es preservar la historia y facilitar el futuro de la música regional."</p>
          </div>
      </div>
      
      <hr class="separator-barreto">
  
      <div class="nosotros-cards-container"></div>
    </section>

  <!-- SECCIÓN UBICACIÓN -->
  <section class="location-section">
    <div class="location-content">
        
        <div class="location-info">
            <h2>Ubicación del Archivo Físico</h2>
            <p>El Archivo Musical, que alberga el patrimonio físico de la Orquesta Sinfónica, se encuentra en la Casa de las Culturas de Resistencia, Chaco.</p>
            
            <div class="address-details">
                <p><strong>Institución:</strong> Casa de las Culturas</p>
                <p><strong>Dirección:</strong> Marcelo T. de Alvear 90, Resistencia, Chaco</p>
                <p><strong>Horarios del Archivo:</strong> Lunes a Viernes (Consulta previa requerida)</p>
                <a href="mailto:archivo@orquestadelchaco.org" class="contact-link">📧 Solicitar Acceso al Archivo</a>
            </div>
        </div>

        <div class="map-placeholder">
          <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3540.62977241823!2d-58.9886853236703!3d-27.449646615853716!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x94450cf454b9d53f%3A0xdfab227279457afe!2sCasa%20de%20las%20Culturas!5e0!3m2!1ses!2sar!4v1759627642284!5m2!1ses!2sar" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
            </div>
        
    </div>
  </section>
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