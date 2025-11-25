<?php
session_start();
require_once '../config/db.php';

// Seguridad: Solo admin puede acceder
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

$page_title = "Cargar Nueva Obra";
require_once '../includes/header.php';

// Obtener datos para los selectores
$generos_q = $conexion->query("SELECT id_genero, nombre FROM generos ORDER BY nombre");
$instrumentos_q = $conexion->query("SELECT id_instrumento, nombre, categoria FROM instrumentos ORDER BY categoria, nombre");

// Organizar instrumentos por categoría
$instrumentos_por_categoria = [];
while ($instr = $instrumentos_q->fetch_assoc()) {
    $instrumentos_por_categoria[$instr['categoria']][] = $instr;
}
?>

<!-- Importar CSS específico de carga -->
<link rel="stylesheet" href="../css/carga.css">

<div class="form-container">
    <div class="form-header">
        <h1>Subir Nueva Partitura</h1>
        <p>Completa los metadatos y adjunta los archivos correspondientes.</p>
    </div>
    
    <!-- Mensajes de Alerta -->
    <?php if (isset($_SESSION['mensaje_alerta'])): ?>
        <div class="alerta-sistema">
            <?php 
                echo htmlspecialchars($_SESSION['mensaje_alerta']); 
                unset($_SESSION['mensaje_alerta']);
            ?>
        </div>
    <?php endif; ?>

    <form action="../app/procesar_carga.php" method="POST" enctype="multipart/form-data" class="form-carga">
        
        <!-- SECCIÓN 1: Categoría -->
        <div class="seccion-carga">
            <label class="label-titulo">Tipo de Obra</label>
            <div class="category-toggle-container">
                <button type="button" class="cat-btn" id="btn-universal" onclick="setCategory('universal')">
                    🎼 Universal (Clásica)
                </button>
                <button type="button" class="cat-btn" id="btn-popular" onclick="setCategory('popular')">
                    🎸 Popular
                </button>
            </div>
            <!-- Input oculto que guarda la selección -->
            <input type="hidden" name="categoria_obra" id="categoria_obra" required>
        </div>

        <!-- SECCIÓN 2: Autor -->
        <div class="seccion-carga">
            <h3>👤 Datos del Autor</h3>
            <div class="form-grid-2">
                <div class="input-group">
                    <label>Nombre</label>
                    <input type="text" name="autor_nombre" placeholder="Ej: Ludwig van" required>
                </div>
                <div class="input-group">
                    <label>Apellido</label>
                    <input type="text" name="autor_apellido" placeholder="Ej: Beethoven" required>
                </div>
            </div>
            <div class="input-group small">
                <label>N° de Orden</label>
                <input type="number" name="autor_orden" placeholder="0" required>
            </div>
        </div>

        <!-- SECCIÓN 3: Detalles -->
        <div class="seccion-carga">
            <h3>📄 Detalles de la Obra</h3>
            
            <div class="input-group">
                <label>Título de la Obra</label>
                <input type="text" name="obra_titulo" required>
            </div>

            <!-- Campo condicional para Universal -->
            <div class="input-group" id="campo_opus" style="display:none;">
                <label>OPUS (Requerido para Universal)</label>
                <input type="text" name="opus" id="input_opus" placeholder="Ej: Op. 55">
            </div>

            <div class="form-grid-2">
                <div class="input-group">
                    <label>N° Inventario</label>
                    <input type="text" name="obra_inventario" required>
                </div>
                <div class="input-group">
                    <label>Año de Composición</label>
                    <input type="number" name="anio_composicion" min="1000" max="<?php echo date('Y'); ?>" placeholder="Ej: 1950">
                </div>
            </div>

            <div class="input-group">
                <label>Género Musical</label>
                <div class="select-wrapper">
                    <select name="id_genero" required>
                        <option value="">Seleccionar Género...</option>
                        <?php while ($gen = $generos_q->fetch_assoc()): ?>
                            <option value="<?php echo $gen['id_genero']; ?>"><?php echo htmlspecialchars($gen['nombre']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </div>

            <div class="input-group">
                <label>Editorial / Fuente</label>
                <input type="text" name="editorial_nombre" placeholder="Ej: Ricordi, Edición Propia" required>
            </div>
        </div>

        <!-- SECCIÓN 4: Instrumentación (Acordeón) -->
        <div class="seccion-carga">
            <h3>🎺 Instrumentación</h3>
            <p class="hint-text">Despliega las categorías y selecciona los instrumentos que apliquen.</p>
            
            <div class="accordion-container">
                <?php $i=0; foreach ($instrumentos_por_categoria as $categoria => $instrumentos): $i++; ?>
                    <div class="accordion-item">
                        <!-- Checkbox oculto para controlar estado abierto/cerrado -->
                        <input type="checkbox" id="acc-<?php echo $i; ?>" class="accordion-toggle">
                        
                        <label for="acc-<?php echo $i; ?>" class="accordion-header">
                            <span class="acc-title"><?php echo htmlspecialchars($categoria); ?></span>
                            <span class="acc-icon">▼</span>
                        </label>
                        
                        <div class="accordion-content">
                            <div class="checkbox-grid">
                                <?php foreach ($instrumentos as $instr): ?>
                                    <label class="instr-checkbox">
                                        <input type="checkbox" name="instrumentos[]" value="<?php echo $instr['id_instrumento']; ?>">
                                        <?php echo htmlspecialchars($instr['nombre']); ?>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- SECCIÓN 5: Archivos -->
        <div class="seccion-carga file-section">
            <h3>📂 Archivos Adjuntos</h3>
            
            <div class="input-group">
                <label>Imagen de Portada (JPG/PNG)</label>
                <input type="file" name="miniatura_img" accept=".jpg,.jpeg,.png" class="file-control" required>
                <small>Se usará como miniatura en el catálogo.</small>
            </div>

            <div class="input-group">
                <label>PDFs de Partituras (Selección Múltiple)</label>
                <input type="file" name="partituras_pdf[]" accept=".pdf" multiple class="file-control" required>
                <small>Mantén presionado Ctrl (o Cmd) para seleccionar varios archivos.</small>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn-submit-carga">Guardar Obra en Catálogo</button>
            <a href="../index.php" class="btn-cancelar">Cancelar</a>
        </div>

    </form>
</div>

<!-- Lógica JS simple para categorías -->
<script>
    function setCategory(cat) {
        // 1. Actualizar estilos de botones
        document.querySelectorAll('.cat-btn').forEach(b => b.classList.remove('active'));
        document.getElementById('btn-' + cat).classList.add('active');
        
        // 2. Actualizar input oculto
        document.getElementById('categoria_obra').value = cat;
        
        // 3. Mostrar/Ocultar Opus
        const opusDiv = document.getElementById('campo_opus');
        const opusInput = document.getElementById('input_opus');
        
        if (cat === 'universal') {
            opusDiv.style.display = 'block';
            opusInput.required = true;
        } else {
            opusDiv.style.display = 'none';
            opusInput.required = false;
            opusInput.value = ''; // Limpiar si se oculta
        }
    }
    
    // Inicializar en Popular por defecto
    setCategory('popular');
</script>

<?php require_once '../includes/footer.php'; ?>