</div> <!-- Cierra page-wrapper -->

<footer class="main-footer">
    <div class="footer-container"> 
        <div class="footer-col">
            <h4>Navegación</h4>
            <ul>
                <li><a href="<?php echo $base_path; ?>index.php">Inicio</a></li>
                <li><a href="<?php echo $base_path; ?>Pages/nosotros.php">Nosotros</a></li>
                <li><a href="<?php echo $base_path; ?>Pages/contacto.php">Contacto</a></li>
            </ul>
        </div>
        
        <div class="footer-col">
            <h4>Contacto</h4>
            <p>Archivo Orquesta Sinfónica del Chaco</p>
            <p><a href="mailto:archivo@orquestadelchaco.org">archivo@orquestadelchaco.org</a></p>
        </div>
        
        <div class="footer-col">
            <h4>Social</h4>
            <div class="socials">
                <a href="#">📘</a> <a href="#">💬</a> <a href="#">▶️</a>
            </div>
            <p class="copyright">© <?php echo date('Y'); ?>. Todos los derechos reservados.</p>
        </div>
    </div>
</footer>

<!-- JS Global para menú móvil -->
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const btn = document.getElementById('mobile-menu-btn');
        const nav = document.getElementById('main-nav');
        
        if(btn && nav) {
            btn.addEventListener('click', () => {
                nav.classList.toggle('active');
                btn.innerHTML = nav.classList.contains('active') ? '✕' : '☰';
            });
        }
    });
</script>
</body>
</html>
<?php
// Cerrar conexión si sigue abierta (opcional pero buena práctica)
if (isset($conexion) && $conexion instanceof mysqli) {
    $conexion->close();
}
?>