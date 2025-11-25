<?php
session_start();
// Ajustar ruta del include según estructura de carpetas (estamos en /Pages)
require_once '../config/db.php'; 

// Seguridad
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

$page_title = "Panel de Control - Admin";
require_once '../includes/header.php'; 

// Obtener datos
$sql = "SELECT O.id_obra, O.titulo, O.anio_composicion, A.apellido, A.nombre 
        FROM obras O 
        INNER JOIN autores A ON O.id_autor = A.id_autor 
        ORDER BY O.id_obra DESC LIMIT 50"; // Limitamos a 50 por rendimiento
$resultado = $conexion->query($sql);
?>

<div class="panel-container">
    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px; margin-bottom:20px;">
        <h1>Panel de Administración</h1>
        <div>
            <span style="margin-right:10px;">Hola, <strong><?php echo htmlspecialchars($_SESSION['usuario_nombre'] ?? 'Admin'); ?></strong></span>
            <a href="../app/logout.php" style="color:red; font-weight:bold;">Cerrar Sesión</a>
        </div>
    </div>

    <div class="acciones-bar" style="display:flex; gap:10px; flex-wrap:wrap; margin-bottom:20px;">
        <a href="solicitudes.php" class="btn-accion" style="background:#e65100; color:white; padding:10px;">Ver Solicitudes</a>
        <a href="cargar_obra.php" class="btn-accion" style="background:#2e7d32; color:white; padding:10px;">+ Nueva Obra</a>
    </div>

    <!-- Contenedor Responsive para la tabla -->
    <div class="table-responsive">
        <table class="tabla-gestion">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Título</th>
                    <th>Autor</th>
                    <th>Año</th>
                    <th style="text-align:center;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php while($obra = $resultado->fetch_assoc()): ?>
                <tr>
                    <td>#<?php echo $obra['id_obra']; ?></td>
                    <td><?php echo htmlspecialchars($obra['titulo']); ?></td>
                    <td><?php echo htmlspecialchars($obra['apellido'] . ', ' . $obra['nombre']); ?></td>
                    <td><?php echo $obra['anio_composicion'] ?: '-'; ?></td>
                    <td style="text-align:center; white-space:nowrap;">
                        <a href="editar_obra.php?id=<?php echo $obra['id_obra']; ?>" class="btn-accion btn-editar">Editar</a>
                        <a href="../app/eliminar_obra.php?id=<?php echo $obra['id_obra']; ?>" 
                           class="btn-accion btn-borrar"
                           onclick="return confirm('¿Estás seguro? Esta acción es irreversible.');">
                           Eliminar
                        </a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>