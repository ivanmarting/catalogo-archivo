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

// Definir las constantes de la interfaz
const chatButton = document.getElementById('chat-button');
const closeButton = document.getElementById('close-button');
const chatContainer = document.getElementById('chat-container');
const chatBox = document.getElementById('chat-box');
const userInput = document.getElementById('user-input');
const sendButton = document.getElementById('send-button');


// URL de tu script de backend PHP
const API_URL = 'app/chat.php'; 

// 1. Funcionalidad de Abrir/Cerrar la Ventana
chatButton.addEventListener('click', () => {
    chatContainer.classList.toggle('hidden');
    userInput.focus(); // Enfocar el input al abrir
});

closeButton.addEventListener('click', () => {
    chatContainer.classList.add('hidden');
});

// 2. Función para añadir un mensaje al chat
function appendMessage(message, sender) {
    const messageDiv = document.createElement('div');
    messageDiv.classList.add('message', `${sender}-message`);
    messageDiv.innerHTML = message; // Usar innerHTML si Gemini devuelve Markdown/HTML
    chatBox.appendChild(messageDiv);
    
    // Desplazar hacia el último mensaje
    chatBox.scrollTop = chatBox.scrollHeight;
}

// 3. Función principal de envío y comunicación con PHP/Gemini
async function sendMessage() {
    const message = userInput.value.trim();
    if (message === '') return;

    // A. Mostrar mensaje del usuario
    appendMessage(message, 'user');
    userInput.value = ''; // Limpiar el input
    sendButton.disabled = true; // Deshabilitar el botón mientras espera
    userInput.disabled = true;

    // B. Mostrar indicador de "escribiendo..." (opcional pero recomendado)
    const thinkingIndicator = document.createElement('div');
    thinkingIndicator.classList.add('message', 'bot-message', 'typing-indicator');
    thinkingIndicator.innerHTML = '...';
    chatBox.appendChild(thinkingIndicator);
    chatBox.scrollTop = chatBox.scrollHeight;

try {
    // A. Añadir la cookie de sesión (CRUCIAL para mantener el historial PHP)
    const fetchOptions = {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        // **Nueva línea:** Envía las cookies (incluyendo PHPSESSID)
        credentials: 'include', 
        body: JSON.stringify({ message: message })
    };

    // B. Llamada al backend PHP
    const response = await fetch(API_URL, fetchOptions);

    // ------------------------------------------------------------------
    // D. PROCESAMIENTO MEJORADO DE LA RESPUESTA (Corrección del problema)
    // ------------------------------------------------------------------
    
    // Leemos la respuesta como TEXTO, no como JSON directamente.
    const responseText = await response.text();
    let data = null;
    let isHtmlError = false;

    // 1. Verificar si la respuesta es HTML (posible error de PHP)
    if (responseText.trim().startsWith('<')) {
        console.error("Respuesta fallida: Se recibió HTML/Error de PHP en lugar de JSON.", responseText);
        isHtmlError = true;
    }

    // 2. Intentar parsear el texto a JSON
    if (!isHtmlError && responseText) {
        try {
            data = JSON.parse(responseText);
        } catch (e) {
            console.error("Fallo al parsear JSON:", responseText, e);
            // Si falla el parseo, tratamos como un error no-JSON
            isHtmlError = true;
        }
    }

    // 3. Evaluar el resultado
    
    // Si hubo un error de conexión HTTP (ej. 404, 500) O error de HTML/parseo
    if (!response.ok || isHtmlError || !data) {
        // Lanza un error para que caiga en el bloque catch, pero con más detalles
        throw new Error(`El servidor respondió con código ${response.status}. Tipo de error: ${isHtmlError ? 'HTML/Parseo' : 'Desconocido'}.`);
    }

    // ------------------------------------------------------------------
    // E. MANEJO DE ÉXITO O FALLO DEL JSON (El JSON sí es válido)
    // ------------------------------------------------------------------

    // Eliminar el indicador de "escribiendo..."
    chatBox.removeChild(thinkingIndicator);

    if (data.success) {
        // Mostrar respuesta exitosa
        // ¡AGREGA AQUÍ TU FUNCIÓN appendMessage para data.response!
        appendMessage(data.response, 'bot'); 

    } else {
        const errorMessage = data.details || 'Intenta de nuevo más tarde.';
        
        // Si el mensaje contiene "overloaded", muestra algo específico:
        if (errorMessage.includes("overloaded")) {
            appendMessage('Asistente Ocupado: El modelo de IA está experimentando alta demanda. Por favor, intenta nuevamente en unos momentos.', 'bot');
        } else {
            appendMessage(`Error de la API: ${errorMessage}`, 'bot');
        }
        console.error('Error de API:', errorMessage);
    }

} catch (error) {
        // En caso de error de red o fallo de fetch
        // Asegúrate de que thinkingIndicator existe antes de intentar eliminarlo
        if(document.querySelector('.typing-indicator')) {
             chatBox.removeChild(document.querySelector('.typing-indicator'));
        }
        appendMessage('**Error de conexión.** No se pudo contactar al servidor. ', error);
        console.error('Error de Fetch:', error);
    } finally {
        // E. Volver a habilitar la interfaz
        sendButton.disabled = false;
        userInput.disabled = false;
        userInput.focus();
    }
}

// 4. Conectar la función de envío al botón y a la tecla Enter
sendButton.addEventListener('click', sendMessage);

userInput.addEventListener('keypress', (event) => {
    // Código 13 es la tecla Enter
    if (event.key === 'Enter') {
        sendMessage();
    }
});
