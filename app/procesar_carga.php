<?php
// ===================================================================
// 1. CONFIGURACIÓN (Contraseña: '')
// ===================================================================
error_reporting(E_ALL & ~E_NOTICE); 

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', ''); 
define('DB_NAME', 'biblioteca');

// CORRECCIÓN 1: Directorio de subidas. 
// Ruta ajustada para salir de /app/ y acceder a /uploads/ en la raíz.
$directorio_subidas = '../uploads/'; 

if (!is_dir($directorio_subidas)) {
    if (!mkdir($directorio_subidas, 0777, true)) {
        die("Error: No se pudo crear el directorio de subidas.");
    }
}

// Iniciar conexión y transacción
$conexion = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}
$conexion->set_charset("utf8");
$conexion->autocommit(FALSE);
$exito = TRUE; 
$error_mensaje = '';

// --- Obtener y limpiar datos ---
$autor_nombre = trim($_POST['autor_nombre'] ?? '');
$autor_apellido = trim($_POST['autor_apellido'] ?? '');
$autor_orden = (int)($_POST['autor_orden'] ?? 0);
$obra_titulo = trim($_POST['obra_titulo'] ?? '');
$obra_inventario = trim($_POST['obra_inventario'] ?? '');
$obra_opus = trim($_POST['obra_opus'] ?? '');
$obra_opus = ($obra_opus === '') ? NULL : $obra_opus;
$obra_orquestacion = trim($_POST['obra_orquestacion'] ?? '');
$obra_orquestacion = ($obra_orquestacion === '') ? NULL : $obra_orquestacion;
$obra_particellas = (int)($_POST['obra_particellas'] ?? 0);
$obra_estado = trim($_POST['obra_estado'] ?? 'Desconocido');

// ===================================================================
// 2. PROCESAR Y GUARDAR AUTOR (Obtener o Crear ID)
// ===================================================================

$id_autor = 0;
if ($exito) {
    $stmt_busca = $conexion->prepare("SELECT id_autor FROM autores WHERE nombre = ? AND apellido = ?");
    if (!$stmt_busca) {
        $exito = FALSE; $error_mensaje = "Error al preparar búsqueda de autor: " . $conexion->error;
    } else {
        $stmt_busca->bind_param("ss", $autor_nombre, $autor_apellido);
        $stmt_busca->execute();
        $resultado = $stmt_busca->get_result();

        if ($resultado->num_rows > 0) {
            $fila = $resultado->fetch_assoc();
            $id_autor = $fila['id_autor'];
        } else {
            $stmt_insert = $conexion->prepare("INSERT INTO autores (nro_orden, nombre, apellido) VALUES (?, ?, ?)");
            if (!$stmt_insert) {
                $exito = FALSE; $error_mensaje = "Error al preparar inserción de autor: " . $conexion->error;
            } else {
                $stmt_insert->bind_param("iss", $autor_orden, $autor_nombre, $autor_apellido);
                
                if ($stmt_insert->execute()) {
                    $id_autor = $conexion->insert_id; 
                } else {
                    $exito = FALSE;
                    $error_mensaje = "Error al guardar autor: " . $stmt_insert->error;
                }
                $stmt_insert->close();
            }
        }
        $stmt_busca->close();
    }
}


// ===================================================================
// 3A. PROCESAR Y MOVER ARCHIVO PDF
// ===================================================================
$ruta_pdf = '';
if ($exito) {
    if (isset($_FILES['partitura_pdf']) && $_FILES['partitura_pdf']['error'] === UPLOAD_ERR_OK) {
        $archivo_temporal = $_FILES['partitura_pdf']['tmp_name'];
        $extension = strtolower(pathinfo(basename($_FILES['partitura_pdf']['name']), PATHINFO_EXTENSION));

        $nombre_final = uniqid() . '_' . $obra_inventario . '.' . $extension;
        $ruta_destino = $directorio_subidas . $nombre_final;

        $tipo_mime = mime_content_type($archivo_temporal);
        if ($tipo_mime !== 'application/pdf') {
            $exito = FALSE;
            $error_mensaje = "Error: El archivo subido no es un PDF válido.";
        } else if (move_uploaded_file($archivo_temporal, $ruta_destino)) {
            $ruta_pdf = $ruta_destino; 
        } else {
            $exito = FALSE;
            $error_mensaje = "Error al mover el archivo PDF. Revise permisos de carpeta.";
        }
    } else {
        $exito = FALSE; 
        $error_mensaje = "Error: Archivo PDF obligatorio no subido o falló la subida. Código: " . $_FILES['partitura_pdf']['error'];
    }
}


// ===================================================================
// 3B. PROCESAR Y MOVER MINIATURA
// ===================================================================
$ruta_miniatura = NULL;
if ($exito && isset($_FILES['miniatura_img']) && $_FILES['miniatura_img']['error'] === UPLOAD_ERR_OK) {
    $archivo_temporal_img = $_FILES['miniatura_img']['tmp_name'];
    $extension_img = strtolower(pathinfo(basename($_FILES['miniatura_img']['name']), PATHINFO_EXTENSION));

    $extensiones_permitidas = ['jpg', 'jpeg', 'png', 'gif'];
    if (!in_array($extension_img, $extensiones_permitidas)) {
    } else {
        $nombre_final_img = uniqid() . '_miniatura_' . $obra_inventario . '.' . $extension_img;
        $ruta_destino_img = $directorio_subidas . $nombre_final_img;

        if (move_uploaded_file($archivo_temporal_img, $ruta_destino_img)) {
            $ruta_miniatura = $ruta_destino_img;
        } else {
            $error_mensaje .= "Advertencia: Falló al mover la miniatura, pero se ignora. ";
        }
    }
}


// ===================================================================
// 4. INSERTAR DATOS DE LA OBRA
// ===================================================================
if ($exito && $id_autor > 0) {
    $sql_obra = "INSERT INTO obras (id_autor, titulo, opus, orquestacion, particellas_cantidad, estado_fisico, nro_inventario, ruta_pdf, ruta_miniatura) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"; 
                 
    $stmt_obra = $conexion->prepare($sql_obra);

    if (!$stmt_obra) {
        $exito = FALSE; $error_mensaje = "Error al preparar inserción de obra: " . $conexion->error;
    } else {
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
            $error_mensaje = "Error al insertar la obra: " . $stmt_obra->error;
        }
        $stmt_obra->close();
    }
} else if ($id_autor == 0) {
     $error_mensaje = "Error: No se pudo obtener o crear el ID del autor.";
}


// ===================================================================
// 5. FINALIZAR TRANSACCIÓN Y MENSAJE
// ===================================================================

if ($exito) {
    $conexion->commit();
    echo '<script>';
    echo 'alert("✅ Carga Completa! La obra se ha guardado.");';
    // CORRECCIÓN 2: Redirección. Desde /app/, para ir a /index.php, usamos '../index.php'
    echo 'window.location.href = "../index.php";';
    echo '</script>';
} else {
    $conexion->rollback();
    // Limpieza de archivos si algo falló
    if (!empty($ruta_pdf) && file_exists($ruta_pdf)) {
        unlink($ruta_pdf);
    }
    if (!empty($ruta_miniatura) && file_exists($ruta_miniatura)) {
        unlink($ruta_miniatura);
    }
    
    $mensaje_alerta = "❌ ERROR EN LA CARGA!\n\nLa obra no fue registrada.\n\nRazón: " . ($error_mensaje ?: "Error desconocido. Revise los logs del servidor.");

    echo '<script>';
    echo 'alert("' . str_replace(["\n", '"'], ["\\n", '\\"'], $mensaje_alerta) . '");';
    echo 'history.back();'; 
    echo '</script>';
}

$conexion->close();
?>