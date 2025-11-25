<?php
// Detectar ruta base para assets (CSS/JS/Imgs)
// Si estamos en la raíz (index.php), la ruta es vacía, si estamos en Pages/, es "../"
$current_script = basename($_SERVER['PHP_SELF']);
$in_pages_dir = (strpos($_SERVER['PHP_SELF'], '/Pages/') !== false);
$base_path = $in_pages_dir ? '../' : '';

// Lógica simple para marcar menú activo
function isActive($page_name) {
    global $current_script;
    return $current_script === $page_name ? 'active' : '';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <!-- Meta Viewport CRÍTICO para Responsive Design -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title : 'AOSCH'; ?></title>
    
    <link rel="stylesheet" href="<?php echo $base_path; ?>css/estilos.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
</head>
<body>

<header class="main-header">
    <div class="header-container">
        <!-- Logo -->
        <a href="<?php echo $base_path; ?>index.php" class="logo-link">
            <img src="<?php echo $base_path; ?>src/images.png" alt="Logo AOSCH" class="logo-img">
        </a>

        <!-- Botón Menú Móvil -->
        <button class="mobile-menu-btn" id="mobile-menu-btn" aria-label="Abrir menú">
            ☰
        </button>

        <!-- Navegación -->
        <nav class="main-nav" id="main-nav">
            <a href="<?php echo $base_path; ?>index.php" class="<?php echo isActive('index.php'); ?>">Inicio</a>
            <a href="<?php echo $base_path; ?>Pages/nosotros.php" class="<?php echo isActive('nosotros.php'); ?>">Nosotros</a>
            <a href="<?php echo $base_path; ?>Pages/contacto.php" class="<?php echo isActive('contacto.php'); ?>">Contacto</a>
            <?php if(isset($_SESSION['usuario_id'])): ?>
                <a href="<?php echo $base_path; ?>Pages/panel.php" class="btn-nav-admin">Panel Admin</a>
            <?php else: ?>
                <a href="<?php echo $base_path; ?>Pages/login.php" class="btn-nav-login">Acceso Admin</a>
            <?php endif; ?>
        </nav>
    </div>
</header>
<!-- Contenedor principal abre aquí -->
<div class="page-wrapper">