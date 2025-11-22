<?php
ob_start();
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

// --- CABECERAS ---
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

session_start();

// --- 1. CONEXIÓN A LA BASE DE DATOS (NUEVO) ---
$dbHost = 'localhost';
$dbName = 'aosch_bd'; // El nombre exacto de tu base de datos
$dbUser = 'root';     // Tu usuario (cambiar si es necesario)
$dbPass = '';         // Tu contraseña (cambiar si es necesario)

$inventario_texto = "";

try {
    $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4", $dbUser, $dbPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Consultamos las obras, uniendo con autores y géneros para darle contexto a Orquestin
    // Limitamos a 50 para no saturar el prompt si tienes miles de obras
    $sql = "SELECT 
                o.titulo, 
                CONCAT(a.nombre, ' ', a.apellido) as autor_completo,
                g.nombre as genero,
                o.anio_composicion
            FROM obras o
            LEFT JOIN autores a ON o.id_autor = a.id_autor
            LEFT JOIN generos g ON o.id_genero = g.id_genero
            LIMIT 50";
            
    $stmt = $pdo->query($sql);
    $obras = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Convertimos los datos de la DB en texto legible para Gemini
    if ($obras) {
        $inventario_texto = "ESTE ES EL INVENTARIO ACTUAL DE LA BIBLIOTECA (Úsalo para responder si el usuario pregunta qué tenemos):\n";
        foreach ($obras as $obra) {
            $inventario_texto .= "- Obra: '{$obra['titulo']}' de {$obra['autor_completo']} ({$obra['genero']}, {$obra['anio_composicion']})\n";
        }
    } else {
        $inventario_texto = "Actualmente la biblioteca está vacía.";
    }

} catch (PDOException $e) {
    // Si falla la base de datos, seguimos funcionando pero sin datos
    $inventario_texto = "Nota: No pude acceder al inventario de la base de datos por un error técnico.";
    error_log("Error DB: " . $e->getMessage());
}

// --- 2. CONFIGURACIÓN DE GEMINI ---
$apiKey = 'AIzaSyCvX1Rk2xuDlTXwKKCRUiYBhvUUt41_4dQ'; // ¡Recuerda poner tu Key real!
$model = 'gemini-2.5-flash';
$apiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/' . $model . ':generateContent?key=' . $apiKey;

// --- 3. PERSONALIDAD + DATOS DE MYSQL (LA FUSIÓN) ---
// Aquí es donde ocurre la magia: Le damos la personalidad Y los datos de la DB
$personalidad = "Tu nombre es Orquestin, eres un asistente experto en partituras y obras sinfonicas con un tono agradable y humorístico. 
Te gusta hacer bromas sobre lo complejo que es tocar el piano.
- No usas mas de 50 palabras por mensaje.
- No inicias ninguna conversación repitiendo lo que te preguntan, hablas natural.
- No usas el caracter * en tus textos.
- Si te preguntan por redes sociales, vas a responder que por el momento no hay redes sociales del Archivo.
- Si te preguntan como subir archivos, indicales que solo los administradores pueden hacerlo, que no podes ayudar con eso.

INFORMACIÓN DE LA BASE DE DATOS:
$inventario_texto
";

// --- 4. MANEJO DE SOLICITUD ---
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

// Inicializar historial
if (!isset($_SESSION['chat_history'])) {
    $_SESSION['chat_history'] = [];
}

// Añadir mensaje usuario
$_SESSION['chat_history'][] = [
    'role' => 'user',
    'parts' => [['text' => $userMessage]]
];

// Preparar Body para Gemini
$requestBody = [
    'system_instruction' => [
        'parts' => [
            ['text' => $personalidad]
        ]
    ],
    'contents' => $_SESSION['chat_history'],
];

// --- 5. ENVIAR A GEMINI (CURL) ---
$ch = curl_init($apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($requestBody));

$response = curl_exec($ch);
if (curl_errno($ch)) {
    $error_msg = curl_error($ch);
    curl_close($ch);
    ob_clean();
    echo json_encode(['success' => false, 'error' => $error_msg]);
    exit;
}
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$responseData = json_decode($response, true);

// --- 6. PROCESAR RESPUESTA ---
if ($httpCode === 200 && isset($responseData['candidates'][0]['content']['parts'][0]['text'])) {
    $geminiResponseText = $responseData['candidates'][0]['content']['parts'][0]['text'];

    $_SESSION['chat_history'][] = [
        'role' => 'model',
        'parts' => [['text' => $geminiResponseText]]
    ];

    ob_clean();
    echo json_encode([
        'success' => true,
        'response' => $geminiResponseText
    ]);
    exit;
} else {
    ob_clean();
    // Loguear el error real para ti, pero mostrar algo genérico al usuario
    error_log("Gemini Error: " . print_r($responseData, true));
    echo json_encode([
        'success' => false, 
        'error' => 'Orquestin está afinando sus instrumentos (Error API).',
        'debug' => $responseData // Quitar esto en producción
    ]);
    exit;
}
?>