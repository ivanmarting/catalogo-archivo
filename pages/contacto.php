    <?php
    session_start();
    // Incluimos configuración de base de datos (por si se necesita en el futuro)
    require_once '../config/db.php'; 

    // Definimos el título de la página para que el header lo muestre
    $page_title = "Contacto - Orquesta Sinfónica del Chaco";
    require_once '../includes/header.php'; 
    ?>

    <!-- Sección de Contacto -->
    <section class="contact-section">
        <div class="contact-container">
            
            <!-- Columna Izquierda: Información -->
            <div class="contact-info">
                <h2>Hablemos de Música</h2>
                <p>Si tienes consultas específicas sobre nuestro archivo, acceso a partituras, o quieres colaborar, no dudes en contactarnos a través de estos canales.</p>
                
                <div class="info-item">
                    <div class="icon">📧</div>
                    <div class="info-text-group"> 
                        <h3>Correo Electrónico</h3>
                        <p><a href="mailto:archivo@orquestadelchaco.org">archivo@orquestadelchaco.org</a></p>
                    </div>
                </div>
                
                <div class="info-item">
                    <div class="icon">📞</div>
                    <div class="info-text-group"> 
                        <h3>Teléfono</h3>
                        <p>+54 362 445-3054</p>
                        <p><small>Lun a Vie, 8:00 - 13:00</small></p>
                    </div>
                </div>
                
                <div class="info-item">
                    <div class="icon">📍</div>
                    <div class="info-text-group"> 
                        <h3>Ubicación del Archivo</h3>
                        <p>Casa de las Culturas, 4to Piso.</p>
                        <p>Marcelo T. de Alvear 90, Resistencia, Chaco.</p>
                    </div>
                </div>
            </div>
            
            <!-- Columna Derecha: Formulario -->
            <div class="contact-form-wrapper">
                <h2>Envíanos un Mensaje</h2>
                
                <!-- Nota: Configura el 'action' a tu script de procesamiento real -->
                <form action="../app/procesar_contacto.php" method="POST" class="contact-form">
                    
                    <div class="form-group">
                        <label for="nombre">Nombre Completo</label>
                        <input type="text" id="nombre" name="nombre" placeholder="Tu nombre" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Correo Electrónico</label>
                        <input type="email" id="email" name="email" placeholder="tu@email.com" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="asunto">Asunto</label>
                        <input type="text" id="asunto" name="asunto" placeholder="Motivo de consulta" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="mensaje">Mensaje</label>
                        <input id="mensaje" name="mensaje" rows="5" placeholder="Escribe tu consulta aquí..." required></input>
                    </div>
                    
                    <button type="submit" class="submit-btn">Enviar Consulta</button>
                </form>
            </div>
            
        </div>
    </section>

    <?php require_once '../includes/footer.php'; ?>