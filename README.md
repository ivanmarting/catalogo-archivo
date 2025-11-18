Últimas modificaciones:

* Se actualizó la base de datos con los datos necesarios (los que nos pasó Soledad)
* Se cargaron en la misma base de datos un ID para cada género e instrumento. (TENER CUIDADO, NO BORRARLOS)
* Se actualizó el Formulario de carga para funcionar con las nuevas tablas de la bd.
* Dentro del formulario, ahora se puede cargar mas de un PDF.
* De no cargarse una imagen miniatura, debería asignarse una por default llamada "default.png" no borrar nunca esa imagen.
* Se actualizó el Index, nueva ventana de filtros (no funcionales aún, al igual que búsqueda rápida)
* El botón de "Ver Partitura" ahora indica cuantos PDF contiene, y al ingresar, se puede ver un listado con cada uno de ellos, para elegir cual ver/descargar.
* El Archivo detalle_obra.php contiene el HTML, CSS y la lógica PHP para hacer funcionar el menú de PDF's por Obra.

--------------------------------------------------------------------------------------------------------------------------------------------------------

Sentencias para reiniciar la base de datos.
ADVERTENCIA: JAMAS BORRAR LOS DATOS DE LAS TABLAS INSTRUMENTOS (NO "obras_instrumentos") NI DE LA TABLA "generos"
Script para eliminar permanentemente TODOS los datos de las obras, autores, editoriales, archivos PDF e instrumentación.

 1. Desactivar temporalmente las comprobaciones de llaves foráneas
SET FOREIGN_KEY_CHECKS = 0;

 2. Vaciar y resetear las tablas relacionadas a Obras
TRUNCATE TABLE obras_instrumentos;
TRUNCATE TABLE archivos_pdf;
TRUNCATE TABLE obras;

 3. Vaciar y resetear tablas principales (dependiendo del flujo, podríamos querer mantener los Géneros e Instrumentos, pero Autores y Editoriales suelen necesitar un reset si se reinicia todo).
TRUNCATE TABLE autores;
TRUNCATE TABLE editoriales;

-------------------------------------------------------------------------------------------------------------------------------------------------------




