<?php
session_start();
// Aunque esta página no usa BD directamente, incluimos el config por consistencia
require_once '../config/db.php'; 

$page_title = "Nosotros - Archivo Orquesta Sinfónica del Chaco";
require_once '../includes/header.php'; 
?>

<!-- Sección Principal -->
<section class="nosotros-section">
    <div class="nosotros-header">
        <h2>Nuestra Misión y Visión</h2>
        <p>En nuestra institución, creemos que el acceso a partituras de alta calidad debe ser universal. Conoce los pilares que nos mueven.</p>
    </div>

    <!-- Tarjetas de Valores -->
    <div class="nosotros-cards-container">
        
        <div class="nosotros-card">
            <div class="card-icon">🎼</div>
            <h3>Acceso Universal</h3>
            <p>Trabajamos para que cualquier músico, sin importar su nivel o ubicación, pueda encontrar y descargar las partituras que necesita.</p>
        </div>

        <div class="nosotros-card">
            <div class="card-icon">✨</div>
            <h3>Calidad y Detalle</h3>
            <p>Nos enfocamos en asegurar que cada partitura cargada sea fiel al original y contenga todos los metadatos relevantes.</p>
        </div>

        <div class="nosotros-card">
            <div class="card-icon">⭐</div>
            <h3>Comunidad Musical</h3>
            <p>Somos un punto de encuentro para compositores e intérpretes. Nuestro propósito es enriquecer la cultura musical digital.</p>
        </div>
    </div>

    <!-- Sección Destacada (Separador Visual) -->
    <hr style="border:0; border-top:1px solid #eee; margin: 60px auto; max-width: 80%;">

    <!-- Perfil Destacado -->
    <div class="destacado-barreto">
        <div class="barreto-imagen">
            <img src="../src/Maria Clara Barreto.jpg" alt="María Clara Barreto">
        </div>
        <div class="barreto-info">
            <h3>María Clara Barreto</h3>
            <h4>Archivista de la Orquesta Sinfónica del Chaco</h4>
            <p>María Clara Barreto es la columna vertebral de nuestro patrimonio. Como Archivista de la Orquesta Sinfónica del Chaco, es la responsable directa del manejo y la preservación física de cada partitura en el archivo de la Orquesta. Esta mención honra su compromiso y su invaluable esfuerzo en el cuidado y catalogación de este legado musical.</p>
            <p class="rol-destacado">"Trabajar con el Archivo es preservar la historia y facilitar el futuro de la música regional."</p>
        </div>
    </div>
</section>

<?php require_once '../includes/footer.php'; ?>