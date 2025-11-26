<?php
session_start();
// Si ya está logueado, mandar al panel directo
if (isset($_SESSION['usuario_id'])) {
    header("Location: panel.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Acceso Administrativo</title>
    <link rel="stylesheet" href="../css/estilos.css">
    <link rel="stylesheet" href="../css/responsive.css">
</head>
<body>
    <!-- Header simplificado -->
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

    <div class="login-container">
        <h2>Panel de Control</h2>
        
        <?php if(isset($_GET['error'])): ?>
            <div class="alerta-error">Usuario o contraseña incorrectos.</div>
        <?php endif; ?>

        <form action="../app/auth_login.php" method="POST">
            <label for="usuario">Usuario:</label>
            <input type="text" id="usuario" name="usuario" required autofocus>
            
            <label for="password">Contraseña:</label>
            <input type="password" id="password" name="password" required>
            
            <button type="submit" class="btn-login">Ingresar</button>
        </form>
        
        <p style="text-align:center; margin-top:20px; font-size:0.8em; color:#666;">
            Acceso exclusivo para personal autorizado.
        </p>
    </div>
    <script src="../js/script.js"></script>
</body>
</html>