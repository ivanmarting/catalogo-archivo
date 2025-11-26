document.addEventListener('DOMContentLoaded', () => {

    // ==================================================================
    // 1. LÓGICA DE CARGA DE INSTRUMENTOS (Solo en cargar_obra.php)
    // ==================================================================
    const cantidadInput = document.getElementById('cantidad_instrumentos');
    const detalleContainer = document.getElementById('instrumentos-detalle');

    if (cantidadInput && detalleContainer) {
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
                input.type = 'text';
                input.id = `instrumento_${i}`;
                input.name = `instrumento_${i}`; 
                input.required = true;
                input.placeholder = `Nombre del Instrumento ${i}`;

                formGroup.appendChild(label);
                formGroup.appendChild(input);
                detalleContainer.appendChild(formGroup);
            }
        }
        // Inicializa solo si existen los elementos
        generarCamposInstrumentos(); 
        cantidadInput.addEventListener('input', generarCamposInstrumentos);
    }


    // ==================================================================
    // 2. RENDERIZADO DE PARTITURAS (Solo si existe el contenedor)
    // ==================================================================
    const containerPartituras = document.getElementById('partituras-container');
    
    if (containerPartituras) {
        // Detectar si estamos en carpeta Pages para ajustar la URL
        // Si la URL actual contiene "Pages", subimos un nivel "../"
        const prefix = window.location.pathname.includes('/Pages/') ? '../' : '';
        const API_URL_PARTITURAS = prefix + 'api/partituras'; 

        async function loadPartituras() {
            try {
                const response = await fetch(API_URL_PARTITURAS);
                if (!response.ok) throw new Error(`Error: ${response.status}`);
                const partituras = await response.json(); 

                partituras.forEach(partitura => {
                    const cardHTML = `
                        <div class="card">
                            <div class="image-placeholder"></div>
                            <h4>${partitura.titulo || 'Sin Título'}</h4>
                            <p>${partitura.autor || 'Anónimo'}</p>
                            <div class="buttons">
                                <button>Solicitar Acceso</button>
                                <button onclick="window.location.href='visualizar/${partitura.id}'">Visualizar</button>
                            </div>
                        </div>`;
                    containerPartituras.insertAdjacentHTML('beforeend', cardHTML);
                });
            } catch (error) {
                console.error('Error partituras:', error);
                containerPartituras.innerHTML = '<p>No se pudieron cargar las partituras.</p>';
            }
        }
        loadPartituras();
    }


    // ==================================================================
    // 3. MODAL DE CONTACTO
    // ==================================================================
    const modal = document.getElementById("contactModal");
    const contactLink = document.querySelector('nav a[href="pages/contact.html"]'); 
    const closeBtn = document.querySelector(".close-btn");

    if (modal && contactLink && closeBtn) {
        contactLink.addEventListener('click', function(event) {
            event.preventDefault(); 
            modal.style.display = "block";
        });
        closeBtn.addEventListener('click', function() {
            modal.style.display = "none";
        });
        window.addEventListener('click', function(event) {
            if (event.target == modal) modal.style.display = "none";
        });
    }


    // ==================================================================
    // 4. CHATBOT (Protegido para no romper otras páginas)
    // ==================================================================
    const chatButton = document.getElementById('chat-button');
    
    if (chatButton) {
        const closeButton = document.getElementById('close-button');
        const chatContainer = document.getElementById('chat-container');
        const chatBox = document.getElementById('chat-box');
        const userInput = document.getElementById('user-input');
        const sendButton = document.getElementById('send-button');
        
        // Ajuste dinámico de ruta para el backend del chat
        const prefix = window.location.pathname.includes('/Pages/') ? '../' : '';
        const API_URL_CHAT = prefix + 'app/chat.php';

        chatButton.addEventListener('click', () => {
            chatContainer.classList.toggle('hidden');
            if(!chatContainer.classList.contains('hidden')) userInput.focus();
        });

        if(closeButton) {
            closeButton.addEventListener('click', () => {
                chatContainer.classList.add('hidden');
            });
        }

        function appendMessage(message, sender) {
            const messageDiv = document.createElement('div');
            messageDiv.classList.add('message', `${sender}-message`);
            messageDiv.innerHTML = message;
            chatBox.appendChild(messageDiv);
            chatBox.scrollTop = chatBox.scrollHeight;
        }

        async function sendMessage() {
            const message = userInput.value.trim();
            if (message === '') return;

            appendMessage(message, 'user');
            userInput.value = '';
            sendButton.disabled = true;
            userInput.disabled = true;

            const thinkingIndicator = document.createElement('div');
            thinkingIndicator.classList.add('message', 'bot-message', 'typing-indicator');
            thinkingIndicator.innerHTML = '...';
            chatBox.appendChild(thinkingIndicator);
            chatBox.scrollTop = chatBox.scrollHeight;

            try {
                const response = await fetch(API_URL_CHAT, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    credentials: 'include',
                    body: JSON.stringify({ message: message })
                });

                const responseText = await response.text();
                let data = null;
                let isHtmlError = false;

                if (responseText.trim().startsWith('<')) isHtmlError = true;

                if (!isHtmlError && responseText) {
                    try { data = JSON.parse(responseText); } catch (e) { isHtmlError = true; }
                }

                if(chatBox.contains(thinkingIndicator)) {
                    chatBox.removeChild(thinkingIndicator);
                }

                if (!response.ok || isHtmlError || !data) {
                    throw new Error('Error en respuesta del servidor');
                }

                if (data.success) {
                    appendMessage(data.response, 'bot');
                } else {
                    const errorMsg = data.details || 'Error desconocido';
                    appendMessage(errorMsg.includes("overloaded") ? 'El asistente está ocupado.' : `Error: ${errorMsg}`, 'bot');
                }

            } catch (error) {
                if(document.querySelector('.typing-indicator')) {
                    document.querySelector('.typing-indicator').remove();
                }
                appendMessage('Error de conexión con el servidor.', 'bot');
                console.error(error);
            } finally {
                sendButton.disabled = false;
                userInput.disabled = false;
                userInput.focus();
            }
        }

        if(sendButton) sendButton.addEventListener('click', sendMessage);
        if(userInput) userInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') sendMessage();
        });
    }


    // ==================================================================
    // 5. RESPONSIVE: MENÚ HAMBURGUESA Y FILTROS (CORREGIDO Y FORZADO)
    // ==================================================================
    
    // --- NAVBAR (Menú) ---
    const nav = document.querySelector('nav');
    if(nav) {
        // 1. Buscamos si el botón YA existe (manual o creado previamente)
        let btnMenu = nav.querySelector('.menu-toggle-btn');
        
        // 2. Si NO existe, lo creamos
        if (!btnMenu) {
            btnMenu = document.createElement('button');
            btnMenu.className = 'menu-toggle-btn';
            btnMenu.innerHTML = '&#9776;'; 
            btnMenu.setAttribute('aria-label', 'Abrir menú');
            nav.appendChild(btnMenu);
        }

        // 3. ¡LA CLAVE! Asignamos el evento onclick directamente.
        // Esto sobrescribe cualquier configuración previa y asegura que funcione.
        btnMenu.onclick = function(e) {
            e.preventDefault(); // Prevenir comportamientos extraños
            nav.classList.toggle('menu-activo');
            
            // Cambiar icono
            if (nav.classList.contains('menu-activo')) {
                btnMenu.innerHTML = '&#10005;'; // X
            } else {
                btnMenu.innerHTML = '&#9776;'; // Hamburguesa
            }
        };
    }

    // --- SIDEBAR FILTROS (Solo Mobile) ---
    const sidebar = document.querySelector('.sidebar-filtros');
    const contenedorCatalogo = document.querySelector('.catalogo-contenido');
    
    if (sidebar && contenedorCatalogo) {
        // Botón Abrir
        let btnAbrirFiltros = document.querySelector('.btn-toggle-filtros');
        if (!btnAbrirFiltros) {
            btnAbrirFiltros = document.createElement('button');
            btnAbrirFiltros.className = 'btn-toggle-filtros';
            btnAbrirFiltros.innerHTML = '🔍 Filtrar y Buscar';
            btnAbrirFiltros.type = 'button';
            contenedorCatalogo.insertBefore(btnAbrirFiltros, contenedorCatalogo.firstChild);
        }
        
        btnAbrirFiltros.onclick = function() {
            sidebar.classList.add('filtros-activos');
            document.body.style.overflow = 'hidden'; 
        };

        // Botón Cerrar
        let btnCerrarFiltros = document.querySelector('.btn-cerrar-filtros');
        if (!btnCerrarFiltros) {
            btnCerrarFiltros = document.createElement('button');
            btnCerrarFiltros.className = 'btn-cerrar-filtros';
            btnCerrarFiltros.innerHTML = '✕ Cerrar Filtros';
            btnCerrarFiltros.type = 'button';
            
            const formFiltros = sidebar.querySelector('form');
            if(formFiltros) {
                formFiltros.insertBefore(btnCerrarFiltros, formFiltros.firstChild);
            }
        }
        
        if (btnCerrarFiltros) {
            btnCerrarFiltros.onclick = function() {
                sidebar.classList.remove('filtros-activos');
                document.body.style.overflow = ''; 
            };
        }
    }

}); // Fin del DOMContentLoaded