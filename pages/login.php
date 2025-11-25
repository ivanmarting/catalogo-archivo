<?php
session_start();
require_once '../config/db.php'; 

// Si ya está logueado, redirigir al panel directamente
if (isset($_SESSION['usuario_id'])) {
    header("Location: panel.php");
    exit;
}

$page_title = "Acceso Administrativo - AOSCH";
require_once '../includes/header.php'; 
?>

<div class="login-wrapper">
    <div class="login-card">
        <h2>Iniciar Sesión</h2>
        <p>Panel de Administración del Archivo</p>
        
        <!-- Mensaje de Error (si existe) -->
        <?php if (isset($_GET['error'])): ?>
            <div style="background:#ffebee; color:#c62828; padding:10px; border-radius:4px; margin-bottom:20px; font-size:0.9em;">
                ⚠️ Usuario o contraseña incorrectos.
            </div>
        <?php endif; ?>

        <!-- Formulario -->
        <form action="../app/auth_login.php" method="POST" class="login-form">
            
            <label for="usuario" style="display:none;">Usuario</label>
            <input type="text" name="usuario" id="usuario" placeholder="Usuario o Email" required autofocus>
            
            <label for="password" style="display:none;">Contraseña</label>
            <input type="password" name="password" id="password" placeholder="Contraseña" required>
            
            <button type="submit" class="login-btn">Ingresar</button>
        </form>

        <div class="login-footer">
            <p><a href="../index.php">← Volver al Catálogo Público</a></p>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>