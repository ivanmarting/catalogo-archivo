<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Cargar Nueva Obra Sinfónica</title>
    <link rel="stylesheet" href="estilos.css"> 
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
</head>
<body>  

    <header>
        <a href="" class="logo"></a>
        <nav>
        <a href="biblioteca.php">Inicio</a>
        <a href="nosotros.php">Nosotros</a>
        <a href="biblioteca.php" class="logo">
            <img src="src/images.png" alt="Logo">
        </a>
        <a href="contacto.php">Contacto</a>
        <a href="cargar_obra.php">Subir Archivo</a>
        </nav>
    </header>

    <form action="procesar_carga.php" method="POST" enctype="multipart/form-data">

        <h1>Subir Obra</h1>
        
        <h2>Información del Autor</h2>
        <label for="autor_nombre">Nombre del Autor:</label>
        <input type="text" name="autor_nombre" id="autor_nombre" required>
        
        <label for="autor_apellido">Apellido del Autor:</label>
        <input type="text" name="autor_apellido" id="autor_apellido" required>
        
        <label for="autor_orden">N° de Orden:</label>
        <input type="number" name="autor_orden" id="autor_orden" required>
        
        <hr>

        <h2>Detalles de la Obra</h2>
        
        <label for="obra_titulo">Título de la Obra:</label>
        <input type="text" name="obra_titulo" id="obra_titulo" required>
        
        <label for="obra_inventario">N° de Inventario:</label>
        <input type="text" name="obra_inventario" id="obra_inventario" required>
        
        <label for="obra_orquestacion">Tipo de Orquestación:</label>
        <input type="text" name="obra_orquestacion" id="obra_orquestacion">
        
        <label for="obra_particellas">Cantidad de Particellas:</label>
        <input type="number" name="obra_particellas" id="obra_particellas">
        
        <label for="obra_estado">Estado Físico:</label>
        <select name="obra_estado" id="obra_estado">
            <option value="Excelente">Excelente</option>
            <option value="Buen estado">Buen estado</option>
            <option value="Deteriorado">Deteriorado</option>
            <option value="Desaparecido">Desaparecido</option>
        </select>
        
        <hr>

        <h2>Archivos</h2>
        <label for="partitura_pdf">Seleccionar Partitura (PDF):</label>
        <input type="file" name="partitura_pdf" id="partitura_pdf" accept=".pdf" required>
        
        <label for="miniatura_img">Seleccionar Miniatura (JPG/PNG):</label>
        <input type="file" name="miniatura_img" id="miniatura_img" accept=".jpg, .jpeg, .png" required>

        <button type="submit">Guardar Obra Completa en Catálogo</button>
        
        <p><a href="biblioteca.php">Ir a la Biblioteca/Catálogo</a></p>
    </form>
    
</body>
</html>