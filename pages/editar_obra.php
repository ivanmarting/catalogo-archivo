<?php
session_start();
// 1. SEGURIDAD: Si no es admin, al login.
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

// 2. VALIDAR ID
if (!isset($_GET['id'])) {
    die("Error: No se seleccionó ninguna obra para editar.");
}

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'aosch_bd');
$conexion = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

$id = (int)$_GET['id'];

// 3. OBTENER DATOS ACTUALES DE LA OBRA
$sql = "SELECT O.*, A.nombre as nombre_autor, A.apellido as apellido_autor, A.nro_orden 
        FROM obras O 
        INNER JOIN autores A ON O.id_autor = A.id_autor 
        WHERE O.id_obra = $id";
$res = $conexion->query($sql);
$obra = $res->fetch_assoc();

if (!$obra) die("Obra no encontrada en la base de datos.");

// Obtener lista de géneros para el selector
$generos_q = $conexion->query("SELECT * FROM generos ORDER BY nombre");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar: <?php echo htmlspecialchars($obra['titulo']); ?></title>
    <link rel="stylesheet" href="../css/estilos.css">
    <style>
        /* Estilos específicos para el formulario de edición */
        .edit-container {
            max-width: 800px;
            margin: 40px auto;
            padding: 30px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            border-top: 5px solid var(--color-acento);
        }
        .btn-volver {
            display: inline-block;
            margin-bottom: 20px;
            color: #666;
            text-decoration: none;
            font-weight: bold;
        }
        .btn-volver:hover { color: #000; }
        
        .form-section-title {
            color: var(--color-principal);
            border-bottom: 1px solid #eee;
            padding-bottom: 5px;
            margin-top: 20px;
            margin-bottom: 15px;
        }
        
        /* Estilo para los inputs */
        .edit-container label { display: block; margin-top: 10px; font-weight: bold; }
        .edit-container input, .edit-container select { 
            width: 100%; padding: 8px; margin-top: 5px; border: 1px solid #ccc; border-radius: 4px; 
        }
    </style>
</head>
<body>
    <header>
        <nav>
            <a href="../index.php" target="_blank">Ver Sitio Público ↗</a>
            <a href="panel.php">Volver al Panel</a>
        </nav>
    </header>

    <div class="edit-container">
        <a href="panel.php" class="btn-volver">← Cancelar y Volver</a>
        
        <h1 style="margin-top:0;">Editar Obra</h1>
        <p style="color:#666;">Editando: <strong><?php echo htmlspecialchars($obra['titulo']); ?></strong></p>

        <form action="../app/procesar_edicion.php" method="POST">
            <!-- CAMPO OCULTO: Importante para saber qué ID estamos editando -->
            <input type="hidden" name="id_obra" value="<?php echo $obra['id_obra']; ?>">
            
            <!-- SECCIÓN AUTOR -->
            <h3 class="form-section-title">Datos del Autor</h3>
            
            <label>Nombre:</label>
            <input type="text" name="autor_nombre" value="<?php echo htmlspecialchars($obra['nombre_autor']); ?>" required>
            
            <label>Apellido:</label>
            <input type="text" name="autor_apellido" value="<?php echo htmlspecialchars($obra['apellido_autor']); ?>" required>
            
            <label>N° Orden Autor:</label>
            <input type="number" name="autor_orden" value="<?php echo htmlspecialchars($obra['nro_orden']); ?>" required>

            <!-- SECCIÓN OBRA -->
            <h3 class="form-section-title">Datos de la Partitura</h3>

            <label>Título de la Obra:</label>
            <input type="text" name="titulo" value="<?php echo htmlspecialchars($obra['titulo']); ?>" required>

            <label>OPUS (Opcional):</label>
            <input type="text" name="opus" value="<?php echo htmlspecialchars($obra['opus']); ?>" placeholder="Ej: Op. 55">

            <label>N° Inventario:</label>
            <input type="text" name="nro_inventario" value="<?php echo htmlspecialchars($obra['nro_inventario']); ?>" required>

            <label>Año Composición:</label>
            <input type="number" name="anio" value="<?php echo htmlspecialchars($obra['anio_composicion']); ?>">

            <label>Género:</label>
            <select name="id_genero" required>
                <?php while($g = $generos_q->fetch_assoc()): ?>
                    <option value="<?php echo $g['id_genero']; ?>" 
                        <?php if($g['id_genero'] == $obra['id_genero']) echo 'selected'; ?>>
                        <?php echo htmlspecialchars($g['nombre']); ?>
                    </option>
                <?php endwhile; ?>
            </select>

            <button type="submit" style="margin-top:30px; background: #2196F3; color: white; padding: 12px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; width: 100%;">💾 Guardar Cambios</button>
        </form>
    </div>
</body>
</html>