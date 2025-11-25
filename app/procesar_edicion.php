<?php
session_start();
// Seguridad
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../Pages/login.php");
    exit;
}

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'aosch_bd');

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conexion = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
} catch (Exception $e) {
    die("Error de conexión: " . $e->getMessage());
}

// Recibir datos de la Obra
$id_obra = (int)$_POST['id_obra'];
$titulo = $_POST['titulo'];
$opus = !empty($_POST['opus']) ? $_POST['opus'] : NULL;
$inventario = $_POST['nro_inventario'];
$anio = !empty($_POST['anio']) ? $_POST['anio'] : NULL;
$id_genero = (int)$_POST['id_genero'];

// Recibir datos del Autor
$autor_nombre = trim($_POST['autor_nombre']);
$autor_apellido = trim($_POST['autor_apellido']);
$autor_orden = (int)$_POST['autor_orden'];

// ===================================================================
// 1. GESTIÓN INTELIGENTE DEL AUTOR (CORRECCIÓN DE ERRORES)
// ===================================================================
$id_autor = 0;

try {
    // A) ¿Existe un autor con este NÚMERO DE ORDEN? (Identificador principal)
    $stmt_orden = $conexion->prepare("SELECT id_autor FROM autores WHERE nro_orden = ?");
    $stmt_orden->bind_param("i", $autor_orden);
    $stmt_orden->execute();
    $res_orden = $stmt_orden->get_result();

    if ($res_orden->num_rows > 0) {
        // --- CASO 1: EL AUTOR EXISTE (Mismo N° Orden) ---
        // Asumimos que estás corrigiendo el nombre o apellido.
        $fila = $res_orden->fetch_assoc();
        $id_autor = $fila['id_autor'];

        // ACTUALIZAMOS los datos del autor para corregir posibles errores
        $stmt_update_autor = $conexion->prepare("UPDATE autores SET nombre = ?, apellido = ? WHERE id_autor = ?");
        $stmt_update_autor->bind_param("ssi", $autor_nombre, $autor_apellido, $id_autor);
        $stmt_update_autor->execute();
        
    } else {
        // --- CASO 2: EL NÚMERO DE ORDEN ES NUEVO ---
        
        // Antes de crear uno nuevo, verifiquemos si la persona ya existe por Nombre
        // (Por si acaso solo le cambiaste el número de orden)
        $stmt_nombre = $conexion->prepare("SELECT id_autor FROM autores WHERE nombre = ? AND apellido = ?");
        $stmt_nombre->bind_param("ss", $autor_nombre, $autor_apellido);
        $stmt_nombre->execute();
        $res_nombre = $stmt_nombre->get_result();

        if ($res_nombre->num_rows > 0) {
            // La persona existe, pero le cambiaste el N° de orden. Actualizamos el orden.
            $fila = $res_nombre->fetch_assoc();
            $id_autor = $fila['id_autor'];
            
            $stmt_upd_orden = $conexion->prepare("UPDATE autores SET nro_orden = ? WHERE id_autor = ?");
            $stmt_upd_orden->bind_param("ii", $autor_orden, $id_autor);
            $stmt_upd_orden->execute();
        } else {
            // No existe ni el orden ni el nombre -> CREAMOS UNO NUEVO
            $stmt_new = $conexion->prepare("INSERT INTO autores (nro_orden, nombre, apellido) VALUES (?, ?, ?)");
            $stmt_new->bind_param("iss", $autor_orden, $autor_nombre, $autor_apellido);
            $stmt_new->execute();
            $id_autor = $conexion->insert_id;
        }
    }

} catch (Exception $e) {
    die("❌ Error al procesar el autor: " . $e->getMessage());
}

// ===================================================================
// 2. ACTUALIZAR LA OBRA
// ===================================================================
try {
    $sql_update = "UPDATE obras SET 
                    id_autor = ?, 
                    id_genero = ?, 
                    titulo = ?, 
                    opus = ?, 
                    nro_inventario = ?, 
                    anio_composicion = ? 
                   WHERE id_obra = ?";

    $stmt = $conexion->prepare($sql_update);
    $stmt->bind_param("iissssi", 
        $id_autor, 
        $id_genero, 
        $titulo, 
        $opus, 
        $inventario, 
        $anio, 
        $id_obra
    );

    if ($stmt->execute()) {
        header("Location: ../Pages/panel.php?msg=✅ Obra y Autor actualizados correctamente.");
    } else {
        throw new Exception($conexion->error);
    }

} catch (Exception $e) {
    die("❌ Error al actualizar la obra: " . $e->getMessage());
}

$conexion->close();
?>