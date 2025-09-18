<?php
// Start session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Load config & DB connection
$config_path = __DIR__ . '/../config.php';
$db_connect_path = __DIR__ . '/../db_connect.php';

if (!file_exists($config_path) || !file_exists($db_connect_path)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Essential configuration files are missing.']);
    exit;
}

require $config_path;
require $db_connect_path;

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method Not Allowed']);
    exit;
}

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'User not authenticated.']);
    exit;
}

$user_id = $_SESSION['user_id'];

// ✅ Fix API key check
$api_key = defined('GEMINI_API_KEY') ? trim(GEMINI_API_KEY) : '';
if (empty($api_key)) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'خطأ فادح: مفتاح Gemini API غير مهيأ في ملف config.php.'
    ]);
    exit;
}

// Get request data
$data = json_decode(file_get_contents('php://input'), true);
$session_id = $data['session_id'] ?? null;
$prompt = $data['message'] ?? '';
$base64Image = $data['image'] ?? null;

if (empty($session_id) || (empty($prompt) && empty($base64Image))) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Session ID and a prompt or image are required.']);
    exit;
}

$image_db_path = null;

try {
    // Step 1: Save user message and image (if any)
    if ($base64Image) {
        if (preg_match('/data:(image\/(\w+));base64,(.*)/', $base64Image, $matches)) {
            $imageExt = $matches[2];
            $imageData = base64_decode($matches[3]);
            $upload_dir = __DIR__ . '/../uploads/chat_images/';
            if (!is_dir($upload_dir)) {
                if (!mkdir($upload_dir, 0775, true)) {
                    throw new Exception("Failed to create chat image directory.");
                }
            }
            $filename = uniqid('chat_', true) . '.' . $imageExt;
            $filepath = $upload_dir . $filename;
            if (file_put_contents($filepath, $imageData)) {
                $image_db_path = 'uploads/chat_images/' . $filename;
            } else {
                throw new Exception("Failed to save uploaded image.");
            }
        }
    }

    $stmt = $conn->prepare("INSERT INTO chat_messages (session_id, user_id, role, content, image_path) VALUES (?, ?, 'user', ?, ?)");
    $stmt->bind_param("iiss", $session_id, $user_id, $prompt, $image_db_path);
    if (!$stmt->execute()) {
        throw new Exception("Database Error: Failed to save user message. " . $stmt->error);
    }
    $stmt->close();

    // Step 2: Retrieve conversation history
    $history_stmt = $conn->prepare("SELECT role, content, image_path FROM chat_messages WHERE session_id = ? ORDER BY created_at ASC");
    $history_stmt->bind_param("i", $session_id);
    $history_stmt->execute();
    $history_result = $history_stmt->get_result();
    $contents = [];
    while ($row = $history_result->fetch_assoc()) {
        $parts = [];
        if (!empty($row['content'])) {
            $parts[] = ['text' => $row['content']];
        }
        if (!empty($row['image_path']) && file_exists(__DIR__ . '/../' . $row['image_path'])) {
            $parts[] = [
                'inline_data' => [
                    'mime_type' => mime_content_type(__DIR__ . '/../' . $row['image_path']),
                    'data' => base64_encode(file_get_contents(__DIR__ . '/../' . $row['image_path']))
                ]
            ];
        }
        if (!empty($parts)) {
            $contents[] = ['role' => $row['role'], 'parts' => $parts];
        }
    }
    $history_stmt->close();
    
    // Step 3: Call Gemini API
    $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash-latest:generateContent?key=' . $api_key;
    $system_instruction = [
        'parts' => [[
            'text' => "أنت طبيب بيطري خبير. مهمتك تحليل الحالات البيطرية بناءً على النصوص والصور المقدمة. قدم تشخيص شامل، تشخيص تفريقي، خطة علاج، وجرعات الأدوية."
        ]]
    ];
    $payload = json_encode(['contents' => $contents, 'systemInstruction' => $system_instruction]);
    
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $payload,
        CURLOPT_SSL_VERIFYPEER => false // Use true in production
    ]);

    $response_body = curl_exec($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    curl_close($ch);

    if ($httpcode == 200 && $response_body) {
        $result = json_decode($response_body, true);
        if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
            $reply = $result['candidates'][0]['content']['parts'][0]['text'];
            
            // Step 4: Save AI reply
            $stmt = $conn->prepare("INSERT INTO chat_messages (session_id, user_id, role, content) VALUES (?, ?, 'model', ?)");
            $stmt->bind_param("iis", $session_id, $user_id, $reply);
            $stmt->execute();
            $stmt->close();
            
            echo json_encode(['success' => true, 'data' => ['reply' => $reply]]);
        } else {
            $errorInfo = $result['error']['message'] ?? ($result['candidates'][0]['finishReason'] ?? 'UNKNOWN');
            throw new Exception('Invalid response from AI API. Reason: ' . $errorInfo);
        }
    } else {
        throw new Exception('Failed to connect to AI service. Code: ' . $httpcode . ' - Details: ' . $curl_error);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

$conn->close();
?>
