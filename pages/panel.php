<?php
session_start();
// SEGURIDAD: Si no está logueado, mandar al login
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

// Conexión para listar obras
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'aosch_bd');
$conexion = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Obtener obras
$sql = "SELECT O.id_obra, O.titulo, O.anio_composicion, A.apellido, A.nombre 
        FROM obras O 
        INNER JOIN autores A ON O.id_autor = A.id_autor 
        ORDER BY O.id_obra DESC";
$resultado = $conexion->query($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel de Control - AOSCH</title>
    <link rel="stylesheet" href="../css/estilos.css">
    <link rel="stylesheet" href="../css/responsive.css">
</head>
<body>
    <header> 
        <nav>
             <a href="../index.php">Inicio</a>
             <a href="nosotros.php">Nosotros</a>
             <a href="../index.php"> <img src="../src/images.png" alt="Logo" class="logo"> </a>
             <a href="contacto.php">Contacto</a>
             <a href="cargar_obra.php">Subir Archivo</a>
    
             <button class="menu-toggle-btn">&#9776;</button> 
        </nav>
    </header>

    <div class="panel-header">
        <h1>Panel de Administración</h1>
        <div class="user-info">
            Hola, <strong><?php echo htmlspecialchars($_SESSION['usuario_nombre']); ?></strong>
            <a href="../app/logout.php" class="btn-logout">Cerrar Sesión</a>
        </div>
    </div>

    <div class="panel-container">

        <div class="acciones-bar">
            <a href="solicitudes.php" class="btn-accion btn-alerta">
                <span class="icono">📋</span> Ver Solicitudes
            </a>
            
            <a href="cargar_obra.php" class="btn-accion btn-primario">
                <span class="icono">✚</span> Subir Nueva Obra
            </a>
            
            <a href="../index.php" class="btn-accion btn-neutro">
                <span class="icono">🌐</span> Ver Sitio Web
            </a>
        </div>

        <h2>Gestión de Obras Existentes</h2>
        
        <?php if(isset($_GET['msg'])): ?>
            <div style="padding:10px; background:#e8f5e9; color:#2e7d32; border-radius:4px; margin-bottom:20px;">
                <?php echo htmlspecialchars($_GET['msg']); ?>
            </div>
        <?php endif; ?>

        <table class="tabla-gestion">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Título</th>
                    <th>Autor</th>
                    <th>Año</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php while($obra = $resultado->fetch_assoc()): ?>
                <tr>
                    <td>#<?php echo $obra['id_obra']; ?></td>
                    <td><?php echo htmlspecialchars($obra['titulo']); ?></td>
                    <td><?php echo htmlspecialchars($obra['apellido'] . ', ' . $obra['nombre']); ?></td>
                    <td><?php echo $obra['anio_composicion'] ?: '-'; ?></td>
                    <td class="acciones-row">
                        <!-- Botón Editar ACTUALIZADO -->
                        <a href="editar_obra.php?id=<?php echo $obra['id_obra']; ?>" class="btn-editar">✎ Editar</a>
                        
                        <!-- Botón Eliminar -->
                        <a href="../app/eliminar_obra.php?id=<?php echo $obra['id_obra']; ?>" 
                           class="btn-eliminar"
                           onclick="return confirm('¿Estás seguro de eliminar esta obra?');">
                           🗑 Eliminar
                        </a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    <script src="../js/script.js"></script>

</body>
</html>