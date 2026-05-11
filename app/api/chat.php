<?php
/**
 * Gemini AI Chat API Endpoint
 */

require_once __DIR__ . '/../../app/config/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$message = isset($input['message']) ? trim($input['message']) : '';
$history = isset($input['history']) ? $input['history'] : [];

if (empty($message)) {
    http_response_code(400);
    echo json_encode(['error' => 'Message is required']);
    exit;
}

// Build conversation for Gemini
$systemInstruction = "You are HiveBot, StreamHive's friendly movie and TV series assistant. You help users with:
- Movie and TV series recommendations based on genres, moods, or similar titles
- Information about movies and TV shows (plots, cast, ratings, seasons, trivia)
- How to use the StreamHive website (search, watchlist, ratings, profile)
- General movie and TV discussions and fun facts

Keep responses concise (2-4 sentences usually). Be enthusiastic about movies and series!
Use emoji occasionally. If asked about non-entertainment topics, politely redirect to movies and series.
When recommending, mention the title, year, and a brief reason why.";

// Build contents array with history
$contents = [];

foreach ($history as $msg) {
    $contents[] = [
        'role' => $msg['role'],
        'parts' => [['text' => $msg['text']]]
    ];
}

// Add current message
$contents[] = [
    'role' => 'user',
    'parts' => [['text' => $message]]
];

$payload = json_encode([
    'system_instruction' => [
        'parts' => [['text' => $systemInstruction]]
    ],
    'contents' => $contents,
    'generationConfig' => [
        'temperature' => 0.8,
        'topP' => 0.95,
        'maxOutputTokens' => 500
    ]
]);

$url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash-lite:generateContent?key=' . GEMINI_API_KEY;

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
// Fix SSL certificate issues on Windows/XAMPP
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);

$response = curl_exec($ch);
$curlError = curl_error($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($response === false) {
    http_response_code(500);
    echo json_encode(['error' => 'cURL request failed', 'details' => $curlError]);
    exit;
}

if ($httpCode !== 200) {
    http_response_code(500);
    echo json_encode(['error' => 'Gemini API returned error', 'http_code' => $httpCode, 'details' => $response]);
    exit;
}

$data = json_decode($response, true);

if (isset($data['candidates'][0]['content']['parts'][0]['text'])) {
    $reply = $data['candidates'][0]['content']['parts'][0]['text'];
    echo json_encode(['reply' => $reply]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Unexpected AI response format', 'raw' => $data]);
}
?>
