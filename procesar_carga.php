<?php
// ===================================================================
// 1. CONFIGURACIÓN (Contraseña: '')
// ===================================================================
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', ''); 
define('DB_NAME', 'biblioteca');

$directorio_subidas = 'uploads/'; 
if (!is_dir($directorio_subidas)) {
    mkdir($directorio_subidas, 0777, true);
}

// Iniciar conexión y transacción
$conexion = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}
$conexion->autocommit(FALSE);
$exito = TRUE; 

// Obtener y limpiar datos
$autor_nombre = $_POST['autor_nombre'] ?? '';
$autor_apellido = $_POST['autor_apellido'] ?? '';
$autor_orden = (int)($_POST['autor_orden'] ?? 0);
$obra_titulo = $_POST['obra_titulo'] ?? '';
$obra_inventario = $_POST['obra_inventario'] ?? '';
$obra_opus = $_POST['obra_opus'] ?: NULL; 
$obra_orquestacion = $_POST['obra_orquestacion'] ?: NULL;
$obra_particellas = (int)($_POST['obra_particellas'] ?: 0);
$obra_estado = $_POST['obra_estado'] ?? 'Desconocido';


// ===================================================================
// 2. PROCESAR Y GUARDAR AUTOR (Obtener o Crear ID)
// ===================================================================

$id_autor = 0;
$stmt_busca = $conexion->prepare("SELECT id_autor FROM autores WHERE nombre = ? AND apellido = ?");
$stmt_busca->bind_param("ss", $autor_nombre, $autor_apellido);
$stmt_busca->execute();
$resultado = $stmt_busca->get_result();

if ($resultado->num_rows > 0) {
    $fila = $resultado->fetch_assoc();
    $id_autor = $fila['id_autor'];
} else {
    $stmt_insert = $conexion->prepare("INSERT INTO autores (nro_orden, nombre, apellido) VALUES (?, ?, ?)");
    $stmt_insert->bind_param("iss", $autor_orden, $autor_nombre, $autor_apellido);
    
    if ($stmt_insert->execute()) {
        $id_autor = $conexion->insert_id; 
    } else {
        $exito = FALSE;
    }
    $stmt_insert->close();
}
$stmt_busca->close();


// ===================================================================
// 3A. PROCESAR Y MOVER ARCHIVO PDF
// ===================================================================

$ruta_pdf = '';
if ($exito && isset($_FILES['partitura_pdf']) && $_FILES['partitura_pdf']['error'] === UPLOAD_ERR_OK) {
    $archivo_temporal = $_FILES['partitura_pdf']['tmp_name'];
    $extension = strtolower(pathinfo(basename($_FILES['partitura_pdf']['name']), PATHINFO_EXTENSION));

    $nombre_final = uniqid() . '_' . $obra_inventario . '.' . $extension;
    $ruta_destino = $directorio_subidas . $nombre_final;

    if (move_uploaded_file($archivo_temporal, $ruta_destino)) {
        $ruta_pdf = $ruta_destino; 
    } else {
        $exito = FALSE;
    }
} else {
     $exito = FALSE;
}

// ===================================================================
// 3B. PROCESAR Y MOVER MINIATURA
// ===================================================================

$ruta_miniatura = '';
if ($exito && isset($_FILES['miniatura_img']) && $_FILES['miniatura_img']['error'] === UPLOAD_ERR_OK) {
    $archivo_temporal_img = $_FILES['miniatura_img']['tmp_name'];
    $extension_img = strtolower(pathinfo(basename($_FILES['miniatura_img']['name']), PATHINFO_EXTENSION));

    $nombre_final_img = uniqid() . '_miniatura_' . $obra_inventario . '.' . $extension_img;
    $ruta_destino_img = $directorio_subidas . $nombre_final_img; 

    if (move_uploaded_file($archivo_temporal_img, $ruta_destino_img)) {
        $ruta_miniatura = $ruta_destino_img;
    } else {
        $exito = FALSE;
    }
} else if ($exito) {
     $exito = FALSE; // Fallo en la subida de la miniatura
}


// ===================================================================
// 4. INSERTAR DATOS DE LA OBRA
// ===================================================================

if ($exito && $id_autor > 0) {
    $sql_obra = "INSERT INTO obras (id_autor, titulo, opus, orquestacion, particellas_cantidad, estado_fisico, nro_inventario, ruta_pdf, ruta_miniatura) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"; 
                 
    $stmt_obra = $conexion->prepare($sql_obra);
    
    $stmt_obra->bind_param("isssissss", 
        $id_autor, 
        $obra_titulo, 
        $obra_opus, 
        $obra_orquestacion, 
        $obra_particellas, 
        $obra_estado, 
        $obra_inventario, 
        $ruta_pdf,
        $ruta_miniatura
    );
    
    if (!$stmt_obra->execute()) {
        $exito = FALSE;
    }
    $stmt_obra->close();
}


// ===================================================================
// 5. FINALIZAR TRANSACCIÓN Y MENSAJE
// ===================================================================

if ($exito) {
    $conexion->commit();
    echo "<h1>✅ Carga Completa!</h1>";
    echo "<p>La obra **$obra_titulo** ha sido guardada.</p>";
    echo '<p><a href="biblioteca.php">Ver Catálogo</a></p>';
} else {
    $conexion->rollback();
    // Limpieza de archivos si algo falló
    if (!empty($ruta_pdf) && file_exists($ruta_pdf)) {
        unlink($ruta_pdf);
    }
    if (!empty($ruta_miniatura) && file_exists($ruta_miniatura)) {
        unlink($ruta_miniatura);
    }
    echo "<h1>❌ Error!</h1>";
    echo "<p>La obra no fue registrada. Verifique la conexión a la BD y los archivos subidos.</p>";
}

$conexion->close();
?>