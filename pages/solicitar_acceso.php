<?php
session_start();
require_once '../config/db.php'; 

// 1. Validar ID de obra (Seguridad básica)
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    // Si no hay ID, redirigir al catálogo
    header("Location: ../index.php");
    exit;
}
$id_obra = (int)$_GET['id'];

// 2. Obtener datos básicos de la obra para mostrar al usuario qué está pidiendo
$sql = "SELECT O.titulo, A.nombre, A.apellido 
        FROM obras O
        INNER JOIN autores A ON O.id_autor = A.id_autor
        WHERE O.id_obra = ?";
        
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $id_obra);
$stmt->execute();
$resultado = $stmt->get_result();
$obra = $resultado->fetch_assoc();

if (!$obra) {
    die("Error: La obra solicitada no existe.");
}

$page_title = "Solicitar Acceso - " . $obra['titulo'];
require_once '../includes/header.php'; 
?>

<div class="solicitud-wrapper">
    <div class="solicitud-container">
        <h1>🔒 Acceso Restringido</h1>
        <p>El material de esta obra está protegido. Para visualizar y descargar las partituras, por favor completa este formulario.</p>
        
        <!-- Bloque destacado con la info de la obra -->
        <div class="info-obra-destacada">
            <h3>Estás solicitando:</h3>
            <p><strong><?php echo htmlspecialchars($obra['titulo']); ?></strong></p>
            <p><em><?php echo htmlspecialchars($obra['apellido'] . ', ' . $obra['nombre']); ?></em></p>
        </div>

        <form action="../app/procesar_solicitud.php" method="POST" class="form-solicitud">
            <input type="hidden" name="id_obra" value="<?php echo $id_obra; ?>">
            
            <label for="nombre">Tu Nombre Completo:</label>
            <input type="text" id="nombre" name="nombre" required placeholder="Ej: Juan Pérez">
            
            <label for="email">Tu Correo Electrónico:</label>
            <input type="email" id="email" name="email" required placeholder="nombre@institucion.com">
            
            <label for="motivo">Motivo de la solicitud:</label>
            <textarea id="motivo" name="motivo" rows="4" required placeholder="Ej: Soy estudiante del conservatorio y necesito esta obra para un examen..."></textarea>
            
            <button type="submit" class="btn-enviar-solicitud">Enviar Solicitud</button>
        </form>

        <a href="../index.php" class="volver-link">← Cancelar y volver al Catálogo</a>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>