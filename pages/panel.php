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
    <style>
        /* Estilos del Panel */
        .panel-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #333;
            color: white;
            padding: 15px 40px;
        }
        .panel-header h1 { margin: 0; font-size: 1.5em; color: white; border: none; }
        .user-info { font-size: 0.9em; }
        .btn-logout {
            background: #555;
            color: white;
            text-decoration: none;
            padding: 5px 10px;
            border-radius: 4px;
            margin-left: 10px;
            font-size: 0.8em;
        }
        .btn-logout:hover { background: #777; }

        .panel-container { max-width: 1200px; margin: 40px auto; padding: 20px; }
        
        .acciones-bar {
            display: flex;
            gap: 20px;
            margin-bottom: 30px;
        }
        .btn-accion {
            display: inline-block;
            padding: 15px 25px;
            background: var(--color-acento);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: bold;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .btn-accion:hover { background: #a00; transform: translateY(-2px); }
        
        .tabla-gestion {
            width: 100%;
            border-collapse: collapse;
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .tabla-gestion th, .tabla-gestion td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        .tabla-gestion th {
            background-color: #f4f4f4;
            color: #333;
        }
        .tabla-gestion tr:hover { background-color: #f9f9f9; }
        
        .acciones-row a {
            margin-right: 10px;
            text-decoration: none;
            font-size: 0.9em;
        }
        .btn-editar { color: #2196F3; font-weight: bold; }
        .btn-eliminar { color: #F44336; font-weight: bold; }
    </style>
</head>
<body>

    <div class="panel-header">
        <h1>Panel de Administración</h1>
        <div class="user-info">
            Hola, <strong><?php echo htmlspecialchars($_SESSION['usuario_nombre']); ?></strong>
            <a href="../app/logout.php" class="btn-logout">Cerrar Sesión</a>
        </div>
    </div>

    <div class="panel-container">
        
        <div class="acciones-bar">
            <a href="cargar_obra.php" class="btn-accion">✚ Subir Nueva Obra</a>
            <a href="../index.php" class="btn-accion" style="background:#555;">Ver Sitio Web</a>
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

</body>
</html>