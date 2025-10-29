<!DOCTYPE html>
<html lang="es">
    <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subir Partitura - Archivo</title>
    <link rel="stylesheet" href="estilos.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
  </head>
  <script src="/js/script.js"></script> 
<script src="/js/script.js"></script> 
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


    <section class="contact-section">
        <div class="contact-container">
            
            <div class="contact-info">
                <h2>Hablemos de Música</h2>
                <p>Si tienes consultas específicas sobre nuestro archivo, acceso a partituras, o quieres colaborar, no dudes en contactarnos a través de estos canales.</p>
                
                <div class="info-item">
                    <i class="icon">📧</i>
                    <div class="info-text-group"> 
                        <h3>Correo Electrónico</h3>
                        <p><a href="mailto:archivo@orquestadelchaco.org">archivo@orquestadelchaco.org</a></p>
                    </div>
                </div>
                
                <div class="info-item">
                    <i class="icon">📞</i>
                    <div class="info-text-group"> 
                        <h3>Teléfono</h3>
                        <p>+54 362 445-3054</p>
                    </div>
                </div>
                
                <div class="info-item">
                    <i class="icon">📍</i>
                    <div class="info-text-group"> 
                        <h3>Ubicación del Archivo</h3>
                        <p>Casa de las Culturas, Marcelo T. de Alvear 90, Resistencia, Chaco.</p>
                    </div>
                </div>
            </div>
            
            <div class="contact-form-wrapper">
                <h2>Envíanos un Mensaje</h2>
                <form action="[RUTA DE PROCESAMIENTO DEL SERVIDOR]" method="POST" class="contact-form">
                    
                    <div class="form-group">
                        <label for="nombre">Nombre Completo</label>
                        <input type="text" id="nombre" name="nombre" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Correo Electrónico</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="asunto">Asunto</label>
                        <input type="text" id="asunto" name="asunto" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="mensaje">Mensaje</label>
                        <textarea id="mensaje" name="mensaje" rows="5" required></textarea>
                    </div>
                    
                    <button type="submit" class="submit-btn">Enviar Consulta</button>
                </form>
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