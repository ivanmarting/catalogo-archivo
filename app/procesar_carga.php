<?php
// ===================================================================
// 1. CONFIGURACIÓN
// ===================================================================
// Zona horaria para PHP (Recomendada)
date_default_timezone_set('America/Argentina/Buenos_Aires'); 

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'aosch_bd'); 

// Directorios para guardar archivos (Rutas correctas al subir desde app/)
$directorio_subidas = '../uploads/'; 
$directorio_pdfs = $directorio_subidas . 'pdfs/'; 
$directorio_miniaturas = $directorio_subidas . 'miniaturas/';

// Asegurar la existencia de los directorios
if (!is_dir($directorio_pdfs)) {
    mkdir($directorio_pdfs, 0777, true);
}
if (!is_dir($directorio_miniaturas)) {
    mkdir($directorio_miniaturas, 0777, true);
}

// Iniciar conexión y transacción
$conexion = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}
$conexion->autocommit(FALSE); 
$exito = TRUE; 
$mensaje_adicional = ''; 


// ===================================================================
// 2. OBTENER Y LIMPIAR DATOS DEL FORMULARIO
// ===================================================================

$autor_nombre = $_POST['autor_nombre'] ?? '';
$autor_apellido = $_POST['autor_apellido'] ?? '';
$autor_orden = (int)($_POST['autor_orden'] ?? 0);
$titulo = $_POST['obra_titulo'] ?? ''; 
$obra_inventario = $_POST['obra_inventario'] ?? '';
$anio_composicion = $_POST['anio_composicion'] ?: NULL; 
$editorial_nombre = $_POST['editorial_nombre'] ?? '';
$id_genero = (int)($_POST['id_genero'] ?? 0);
$instrumentos_seleccionados = $_POST['instrumentos'] ?? []; 


// ===================================================================
// 3. PROCESAR Y GUARDAR AUTOR (TABLA AUTORES)
// ===================================================================
$id_autor = 0;
// Buscar autor existente por nombre y apellido
$stmt_busca = $conexion->prepare("SELECT id_autor FROM autores WHERE nombre = ? AND apellido = ?");
$stmt_busca->bind_param("ss", $autor_nombre, $autor_apellido);
$stmt_busca->execute();
$resultado = $stmt_busca->get_result();

if ($resultado->num_rows > 0) {
    $fila = $resultado->fetch_assoc();
    $id_autor = $fila['id_autor'];
} else {
    // Insertar nuevo autor
    $stmt_insert = $conexion->prepare("INSERT INTO autores (nro_orden, nombre, apellido) VALUES (?, ?, ?)");
    $stmt_insert->bind_param("iss", $autor_orden, $autor_nombre, $autor_apellido);
    
    if ($stmt_insert->execute()) {
        $id_autor = $conexion->insert_id; 
    } else {
        $exito = FALSE;
        $mensaje_adicional .= "<br>- Error al insertar el autor: " . $stmt_insert->error;
    }
    $stmt_insert->close();
}
$stmt_busca->close();


// ===================================================================
// 4. PROCESAR Y GUARDAR EDITORIAL (TABLA EDITORIALES)
// ===================================================================
$id_editorial = NULL; 
if ($exito && !empty($editorial_nombre)) {
    // 1. Buscar editorial existente
    $stmt_busca_edit = $conexion->prepare("SELECT id_editorial FROM editoriales WHERE nombre = ?");
    $stmt_busca_edit->bind_param("s", $editorial_nombre);
    $stmt_busca_edit->execute();
    $resultado_edit = $stmt_busca_edit->get_result();

    if ($resultado_edit->num_rows > 0) {
        $fila_edit = $resultado_edit->fetch_assoc();
        $id_editorial = $fila_edit['id_editorial'];
    } else {
        // 2. Insertar nueva editorial
        $stmt_insert_edit = $conexion->prepare("INSERT INTO editoriales (nombre) VALUES (?)");
        $stmt_insert_edit->bind_param("s", $editorial_nombre);
        
        if ($stmt_insert_edit->execute()) {
            $id_editorial = $conexion->insert_id; 
        } else {
            $exito = FALSE;
            $mensaje_adicional .= "<br>- Error al insertar la editorial: " . $stmt_insert_edit->error;
        }
        $stmt_insert_edit->close();
    }
    $stmt_busca_edit->close();
}


// ===================================================================
// 5. PROCESAR Y MOVER MINIATURA (Usa imagen por defecto si falta)
// ===================================================================
$ruta_miniatura = 'uploads/default.png'; // ⬅️ RUTA POR DEFECTO
if ($exito && isset($_FILES['miniatura_img']) && $_FILES['miniatura_img']['error'] === UPLOAD_ERR_OK) {
    $archivo_temporal_img = $_FILES['miniatura_img']['tmp_name'];
    $extension_img = strtolower(pathinfo(basename($_FILES['miniatura_img']['name']), PATHINFO_EXTENSION));

    // Nombre basado en el inventario
    $nombre_final_img = 'miniatura_' . $obra_inventario . '_' . uniqid() . '.' . $extension_img; 
    $ruta_destino_img_absoluta = $directorio_miniaturas . $nombre_final_img; 
    
    if (move_uploaded_file($archivo_temporal_img, $ruta_destino_img_absoluta)) {
        $ruta_miniatura = 'uploads/miniaturas/' . $nombre_final_img; // Ruta relativa para la BD
    } else {
        // Si el archivo *existe* pero falló el movimiento (error grave)
        $exito = FALSE; 
        $mensaje_adicional .= "<br>- Error crítico al mover el archivo de miniatura subido.";
    }
} else {
    // Si la miniatura no se subió, usamos la imagen por defecto y avisamos.
    $mensaje_adicional .= "<br>- Advertencia: No se adjuntó una imagen de miniatura. Se usará la imagen por defecto (default.png).";
}


// ===================================================================
// 6. INSERTAR DATOS DE LA OBRA PRINCIPAL (TABLA OBRAS)
// ===================================================================

$id_obra = 0;
if ($exito && $id_autor > 0 && $id_genero > 0) {
    // ⚠️ Validación crucial: el título NO debe estar vacío
    if (empty($titulo)) { 
        $exito = FALSE;
        $mensaje_adicional .= "<br>- Error: El título de la obra está vacío y es obligatorio.";
    } else {
        
        // 1. Sanitizamos el título para evitar inyección SQL (ya que no usaremos bind_param para este campo)
        $titulo_escapado = $conexion->real_escape_string($titulo); 

        // 2. La sentencia SQL: Notar que 'titulo' ya NO es un '?' sino el valor escapado
        $sql_obra = "INSERT INTO obras (id_autor, id_editorial, id_genero, titulo, anio_composicion, nro_inventario, ruta_miniatura) 
                     VALUES (?, ?, ?, '$titulo_escapado', ?, ?, ?)"; 
                     
        $stmt_obra = $conexion->prepare($sql_obra);
        
        // 3. bind_param ahora solo usa 6 parámetros (i i i s s s), excluyendo el título.
        $stmt_obra->bind_param("iiisss", 
            $id_autor, 
            $id_editorial, 
            $id_genero, 
            $anio_composicion, 
            $obra_inventario, 
            $ruta_miniatura
        );
        
        if ($stmt_obra->execute()) {
            $id_obra = $conexion->insert_id; 
        } else {
            $exito = FALSE;
            $mensaje_adicional .= "<br>- Error al insertar la obra principal: " . $stmt_obra->error;
        }
        $stmt_obra->close();
    }
}


// ===================================================================
// 7. PROCESAR Y GUARDAR MÚLTIPLES ARCHIVOS PDF
// ===================================================================

$archivos_adjuntados = 0;

// Solo procedemos si no ha habido un fallo previo y la obra se insertó
if ($exito && $id_obra > 0 && isset($_FILES['partituras_pdf'])) {
    $total_archivos = count($_FILES['partituras_pdf']['name']);
    
    for ($i = 0; $i < $total_archivos; $i++) {
        // Verificamos que se haya subido un archivo y que sea exitoso
        if (isset($_FILES['partituras_pdf']['error'][$i]) && $_FILES['partituras_pdf']['error'][$i] === UPLOAD_ERR_OK) {
            $archivo_temporal = $_FILES['partituras_pdf']['tmp_name'][$i];
            $nombre_original = basename($_FILES['partituras_pdf']['name'][$i]);
            $extension = strtolower(pathinfo($nombre_original, PATHINFO_EXTENSION));

            $nombre_final = $id_obra . '_' . uniqid() . '.' . $extension;
            $ruta_destino_absoluta = $directorio_pdfs . $nombre_final;
            $ruta_archivo_bd = 'uploads/pdfs/' . $nombre_final; // Ruta relativa para la BD

            if (move_uploaded_file($archivo_temporal, $ruta_destino_absoluta)) {
                // Insertar en la tabla archivos_pdf
                $stmt_pdf = $conexion->prepare("INSERT INTO archivos_pdf (id_obra, ruta_archivo, nombre_archivo) VALUES (?, ?, ?)");
                $stmt_pdf->bind_param("iss", $id_obra, $ruta_archivo_bd, $nombre_original);
                
                if (!$stmt_pdf->execute()) {
                    $exito = FALSE; 
                    $mensaje_adicional .= "<br>- Error al registrar un archivo PDF en la BD.";
                    break; 
                }
                $archivos_adjuntados++;
                $stmt_pdf->close();
            } else {
                $exito = FALSE;
                $mensaje_adicional .= "<br>- Error al mover el archivo PDF: " . htmlspecialchars($nombre_original);
                break;
            }
        }
    }
} 

if ($exito && $archivos_adjuntados === 0) {
    // Si la carga fue un éxito, pero no se subió NINGÚN archivo PDF, solo avisamos.
    $mensaje_adicional .= "<br>- Advertencia: No se adjuntó ningún archivo PDF a la obra.";
}


// ===================================================================
// 8. INSERTAR RELACIÓN DE INSTRUMENTACIÓN (TABLA OBRAS_INSTRUMENTOS)
// ===================================================================

if ($exito && $id_obra > 0 && !empty($instrumentos_seleccionados)) {
    
    $stmt_instr = $conexion->prepare("INSERT INTO obras_instrumentos (id_obra, id_instrumento) VALUES (?, ?)");

    foreach ($instrumentos_seleccionados as $id_instrumento) {
        $id_instrumento_int = (int)$id_instrumento; 
        
        if ($id_instrumento_int > 0) {
            $stmt_instr->bind_param("ii", $id_obra, $id_instrumento_int);
            if (!$stmt_instr->execute()) {
                $exito = FALSE;
                $mensaje_adicional .= "<br>- Error al registrar la instrumentación.";
                break; 
            }
        }
    }
    $stmt_instr->close();
}


// ===================================================================
// 9. FINALIZAR TRANSACCIÓN Y MENSAJE (MODIFICADO)
// ===================================================================

// Preparamos el mensaje de retorno antes de cerrar la conexión.
$mensaje_retorno = '';
$pagina_destino = '../Pages/cargar_obra.php'; 

if ($exito) {
    $conexion->commit();
    
    // Crear el mensaje de éxito para la alerta de JavaScript
    $mensaje_retorno = "✅ ¡Carga Completa! La obra '" . htmlspecialchars($titulo) . "' ha sido guardada con éxito.";
    if (!empty($mensaje_adicional)) {
        // Añadir advertencias (como miniatura no subida o PDF faltante) al mensaje
        $mensaje_retorno .= "\n\n(Detalles/Advertencias: Revise el formulario para más información si corresponde.)";
        
        // ADVERTENCIA: Aquí se podría mejorar el manejo de $mensaje_adicional para que se muestre en cargar_obra.php,
        // pero por simplicidad de usar solo un 'alert', solo indicamos que hay detalles.
    }
    
    // Si queremos redirigir a la misma página del formulario pero indicando el éxito:
    $pagina_destino .= '?status=success'; 
    
} else {
    // Si algo falló, se revierten todos los cambios en la BD
    $conexion->rollback(); 
    
    // Limpiar miniatura subida si el fallo fue después de subirla
    if (!empty($ruta_miniatura) && $ruta_miniatura !== 'uploads/default.png' && file_exists($directorio_miniaturas . basename($ruta_miniatura))) {
        unlink($directorio_miniaturas . basename($ruta_miniatura));
    }
    
    // Crear el mensaje de error para la alerta de JavaScript
    $mensaje_error = $mensaje_adicional ?: "Ocurrió un error inesperado. La transacción fue abortada.";
    $mensaje_retorno = "❌ ¡Error en la Carga! La obra no fue registrada. Revise los detalles: " . strip_tags(str_replace("<br>", " | ", $mensaje_error));

    // Si hubo error, redirigimos a la misma página del formulario pero indicando el error:
    $pagina_destino .= '?status=error';
}

$conexion->close();


// 10. REDIRECCIÓN CON MENSAJE (USANDO SESSION para el mensaje)
// -------------------------------------------------------------
// Nota: Para usar $_SESSION, debe iniciar la sesión.
// Esto es el método MÁS limpio para pasar mensajes de una página a otra.
session_start();
$_SESSION['mensaje_alerta'] = $mensaje_retorno;


// Redireccionar al formulario con el indicador de éxito/error
header("Location: " . $pagina_destino);
exit;

// El código original ha sido removido y reemplazado por la redirección:
/*
if ($exito) {
    // ... código de éxito original ...
    echo '<p><a href="../index.php">Ver Catálogo</a></p>'; 
} else {
    // ... código de error original ...
    echo "<p><a href='../Pages/cargar_obra.php'>Volver al formulario</a></p>";
}
$conexion->close();
*/

?>