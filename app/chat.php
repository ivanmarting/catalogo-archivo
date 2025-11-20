<?php
ob_start(); // <--- 1. INICIA EL BÚFER (Captura todo lo que se imprima)
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);
// ... el resto del código ...
// Configuración de la respuesta HTTP y cabeceras CORS
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // Permite solicitudes desde el frontend
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Puedes cambiar este texto por lo que quieras
$personalidad = "Tu nombre es Orquestin, eres un asistente experto en partituras y obras sinfonicas con un tono agradable y humorístico. 
Te gusta hacer bromas sobre lo difícil que es tocar el piano, pero prefieres tocar la guitarra.";

// Iniciar sesión de PHP para mantener el historial del chat
session_start();

if (session_status() === PHP_SESSION_NONE) {
    // Esto solo se ejecuta si la sesión no pudo iniciarse
    die('Error: La sesión de PHP no se pudo iniciar.');
}

// --- CONFIGURACIÓN ---
// ¡IMPORTANTE! Reemplaza esto con tu clave.
// Usar getenv() es recomendado para producción.
$apiKey = 'AIzaSyCnJUuedKMSISec3bKPJpymyh_Bquf816c'; 
$model = 'gemini-2.5-flash';
$apiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/' . $model . ':generateContent?key=' . $apiKey;

// Verificar método y obtener datos del frontend
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido. Use POST.']);
    exit;
}

$input = file_get_contents('php://input');
$data = json_decode($input, true);
$userMessage = $data['message'] ?? '';

if (empty($userMessage)) {
    http_response_code(400);
    echo json_encode(['error' => 'El mensaje de usuario está vacío.']);
    exit;
}

// 1. Inicializar o cargar el historial de la sesión
// El historial se almacena como un array de objetos "Content"
if (!isset($_SESSION['chat_history'])) {
    $_SESSION['chat_history'] = [];
}

// 2. Añadir el nuevo mensaje del usuario al historial
$_SESSION['chat_history'][] = [
    'role' => 'user',
    'parts' => [['text' => $userMessage]]
];

// 3. Preparar la estructura de la solicitud a la API de Gemini
$requestBody = [
    'system_instruction' => [
        'parts' => [
            ['text' => $personalidad]
        ]
    ],
    'contents' => $_SESSION['chat_history'],
];

// --- 4. Enviar la solicitud usando cURL ---
$ch = curl_init($apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($requestBody));

$response = curl_exec($ch);
if (curl_errno($ch)) {
    $curl_error_message = curl_error($ch);
    $curl_error_code = curl_errno($ch);
    curl_close($ch);
    http_response_code(500);
    
    // Devuelve el error de cURL al frontend
    echo json_encode([
        'success' => false, 
        'error' => 'Error de conexión cURL.',
        'details' => "Código: {$curl_error_code}. Mensaje: {$curl_error_message}"
    ]);
    exit;
}
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);



$responseData = json_decode($response, true);

// 5. Manejar la respuesta
if ($httpCode === 200 && isset($responseData['candidates'][0]['content']['parts'][0]['text'])) {
    $geminiResponseText = $responseData['candidates'][0]['content']['parts'][0]['text'];

    // Guardar en sesión...
    $_SESSION['chat_history'][] = [
        'role' => 'model',
        'parts' => [['text' => $geminiResponseText]]
    ];

    // --- LIMPIEZA CRÍTICA ANTES DE ENVIAR ---
    ob_clean(); // <--- 2. Borra cualquier HTML/Espacio previo acumulado
    echo json_encode([
        'success' => true,
        'response' => $geminiResponseText
    ]);
    exit; // <--- 3. Detiene el script inmediatamente
} else {
    // Manejar errores
    // ... lógica de array_pop ...
    
    ob_clean(); // <--- 2. Borra cualquier HTML/Espacio previo acumulado
    echo json_encode([
        'success' => false,
        'error' => 'Error al comunicarse con la API de Gemini.',
        'details' => $responseData['error']['message'] ?? 'Error desconocido.'
    ]);
    exit; // <--- 3. Detiene el script
}