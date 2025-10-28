// scripts.js
document.addEventListener('DOMContentLoaded', () => {
    const cantidadInput = document.getElementById('cantidad_instrumentos');
    const detalleContainer = document.getElementById('instrumentos-detalle');

    function generarCamposInstrumentos() {
        const cantidad = parseInt(cantidadInput.value) || 0; 
        
        detalleContainer.innerHTML = ''; 

        if (cantidad < 1) {
            detalleContainer.innerHTML = '<p style="color: red;">La cantidad debe ser 1 o más.</p>';
            return;
        }

        for (let i = 1; i <= cantidad; i++) {
            const formGroup = document.createElement('div');
            formGroup.className = 'form-group';

            const label = document.createElement('label');
            label.setAttribute('for', `instrumento_${i}`);
            label.textContent = `Instrumento #${i}:`;

            const input = document.createElement('input');
            input.setAttribute('type', 'text');
            input.setAttribute('id', `instrumento_${i}`);
            input.setAttribute('name', `instrumento_${i}`); 
            input.setAttribute('required', 'required');
            input.setAttribute('placeholder', `Nombre del Instrumento ${i}`);

            formGroup.appendChild(label);
            formGroup.appendChild(input);
            detalleContainer.appendChild(formGroup);
        }
    }

    // Inicializa los campos y escucha los cambios
    generarCamposInstrumentos(); 
    cantidadInput.addEventListener('input', generarCamposInstrumentos);
});

// js/render_partituras.js

document.addEventListener('DOMContentLoaded', () => {
    const container = document.getElementById('partituras-container');

    // URL a tu API de backend (DEBES REEMPLAZAR ESTA URL REAL)
    const API_URL = '/api/partituras'; 

    // Función para obtener y mostrar los datos
    async function loadPartituras() {
        try {
            // 1. Obtener los datos del backend
            const response = await fetch(API_URL);
            
            // Verifica si la respuesta es exitosa
            if (!response.ok) {
                throw new Error(`Error en el servidor: ${response.status}`);
            }

            // 2. Convertir la respuesta a JSON
            const partituras = await response.json(); 

            // 3. Renderizar cada partitura como una tarjeta
            partituras.forEach(partitura => {
                const cardHTML = createCardHTML(partitura);
                container.insertAdjacentHTML('beforeend', cardHTML);
            });

        } catch (error) {
            console.error('Error al cargar las partituras:', error);
            container.innerHTML = '<p>Lo sentimos, no pudimos cargar las partituras en este momento.</p>';
        }
    }

    // Función que genera el HTML para una sola tarjeta (template)
    function createCardHTML(data) {
        // Usa los datos (título, autor, etc.) del objeto "data"
        return `
            <div class="card">
                <div class="image-placeholder"></div>
                <h4>${data.titulo || 'Sin Título'}</h4>
                <p>${data.autor || 'Anónimo'}</p>
                <div class="buttons">
                    <button>Solicitar Acceso</button>
                    <button onclick="window.location.href='/visualizar/${data.id}'">Visualizar</button>
                </div>
            </div>
        `;
    }

    // Iniciar la carga de partituras
    loadPartituras();
});

// scripts.js
document.addEventListener('DOMContentLoaded', () => {
    // ... Tu código de carga dinámica y formularios ...

    // ------------------------------------------------------------------
    // TAREA 3: LÓGICA DEL POP-UP DE CONTACTO
    // ------------------------------------------------------------------
    const modal = document.getElementById("contactModal");
    
    // El script busca un enlace con este atributo href
    const contactLink = document.querySelector('nav a[href="pages/contact.html"]'); 
    
    const closeBtn = document.querySelector(".close-btn");

    if (modal && contactLink && closeBtn) {
        
        // El event.preventDefault() es la clave: detiene la navegación
        contactLink.addEventListener('click', function(event) {
            event.preventDefault(); 
            modal.style.display = "block";
        });

        // ... lógica para cerrar el modal ...
        closeBtn.addEventListener('click', function() {
            modal.style.display = "none";
        });

        window.addEventListener('click', function(event) {
            if (event.target == modal) {
                modal.style.display = "none";
            }
        });
    }
});