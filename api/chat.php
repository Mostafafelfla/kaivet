<?php
// api/chat.php

// --- Basic Setup & Security ---
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: application/json; charset=utf-8');

// --- Load Config & DB ---
$config_path = __DIR__ . '/../config.php';
$db_connect_path = __DIR__ . '/../db_connect.php';

if (!file_exists($config_path) || !file_exists($db_connect_path)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'ملفات الإعدادات الأساسية مفقودة.']);
    exit;
}
require $config_path;
require $db_connect_path;

// --- Authentication Check ---
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'المستخدم غير مسجل دخوله.']);
    exit;
}
$user_id = $_SESSION['user_id'];

// --- Main Router (GET vs POST) ---
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    handle_get_request($conn, $user_id);
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    handle_post_request($conn, $user_id);
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method Not Allowed']);
}

$conn->close();

// --- Request Handlers ---

function handle_get_request($conn, $user_id) {
    $action = $_GET['action'] ?? '';
    switch ($action) {
        case 'get_sessions':
            get_sessions($conn, $user_id);
            break;
        case 'get_messages':
            get_messages($conn, $user_id);
            break;
        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Invalid GET action.']);
            break;
    }
}

function handle_post_request($conn, $user_id) {
    // Check for multipart/form-data for audio uploads
    if (isset($_POST['action']) && $_POST['action'] === 'generate_response') {
        generate_response($conn, $user_id, $_POST, $_FILES);
    } 
    // Handle JSON payloads for other actions
    else {
        $data = json_decode(file_get_contents('php://input'), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Invalid JSON body.']);
            return;
        }

        $action = $data['action'] ?? '';
        switch ($action) {
            case 'new_session':
                new_session($conn, $user_id, $data);
                break;
            case 'generate_response': // Handles text/image from JSON
                generate_response($conn, $user_id, $data, null);
                break;
            case 'rename_session':
                rename_session($conn, $user_id, $data);
                break;
            case 'delete_session':
                delete_session($conn, $user_id, $data);
                break;
            case 'text_to_speech': // New action for Text-to-Speech
                text_to_speech($data);
                break;
            default:
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Invalid POST action in JSON body.']);
                break;
        }
    }
}

// --- GET Functions ---

function get_sessions($conn, $user_id) {
    try {
        $stmt = $conn->prepare("SELECT id, title, created_at FROM chat_sessions WHERE user_id = ? ORDER BY created_at DESC");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        echo json_encode(['success' => true, 'data' => $result]);
        $stmt->close();
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    }
}

function get_messages($conn, $user_id) {
    $session_id = filter_input(INPUT_GET, 'session_id', FILTER_VALIDATE_INT);
    if (!$session_id) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Session ID is required.']);
        return;
    }

    try {
        // Updated to fetch audio_path if it exists
        $stmt = $conn->prepare("SELECT role, content, image_path, audio_path, created_at FROM chat_messages WHERE session_id = ? AND user_id = ? ORDER BY created_at ASC");
        $stmt->bind_param("ii", $session_id, $user_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        echo json_encode(['success' => true, 'data' => $result]);
        $stmt->close();
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    }
}

// --- POST Functions ---

function new_session($conn, $user_id, $data) {
    $title = !empty(trim($data['title'])) ? htmlspecialchars(trim($data['title'])) : 'محادثة جديدة';
    try {
        $stmt = $conn->prepare("INSERT INTO chat_sessions (user_id, title) VALUES (?, ?)");
        $stmt->bind_param("is", $user_id, $title);
        $stmt->execute();
        $new_session_id = $conn->insert_id;
        $stmt->close();
        echo json_encode(['success' => true, 'message' => 'New session created.', 'data' => ['id' => $new_session_id, 'title' => $title]]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    }
}

function rename_session($conn, $user_id, $data) {
    $session_id = $data['session_id'] ?? null;
    $new_title = trim($data['title'] ?? '');

    if (empty($session_id) || empty($new_title)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Session ID and new title are required.']);
        return;
    }

    try {
        $stmt = $conn->prepare("UPDATE chat_sessions SET title = ? WHERE id = ? AND user_id = ?");
        $stmt->bind_param("sii", $new_title, $session_id, $user_id);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            echo json_encode(['success' => true, 'message' => 'Session renamed successfully.']);
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Session not found or permission denied.']);
        }
        $stmt->close();
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    }
}

function delete_session($conn, $user_id, $data) {
    $session_id = $data['session_id'] ?? null;

    if (empty($session_id)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Session ID is required.']);
        return;
    }

    $conn->begin_transaction();

    try {
        // Get file paths to delete from server
        $stmt_files = $conn->prepare("SELECT image_path, audio_path FROM chat_messages WHERE session_id = ? AND user_id = ?");
        if ($stmt_files) {
            $stmt_files->bind_param("ii", $session_id, $user_id);
            $stmt_files->execute();
            $result_files = $stmt_files->get_result();
            while ($row = $result_files->fetch_assoc()) {
                if (!empty($row['image_path']) && file_exists(__DIR__ . '/../' . $row['image_path'])) {
                    unlink(__DIR__ . '/../' . $row['image_path']);
                }
                if (isset($row['audio_path']) && !empty($row['audio_path']) && file_exists(__DIR__ . '/../' . $row['audio_path'])) {
                    unlink(__DIR__ . '/../' . $row['audio_path']);
                }
            }
            $stmt_files->close();
        }
        
        // Delete messages associated with the session
        $stmt_messages = $conn->prepare("DELETE FROM chat_messages WHERE session_id = ? AND user_id = ?");
        $stmt_messages->bind_param("ii", $session_id, $user_id);
        $stmt_messages->execute();
        $stmt_messages->close();

        // Delete the session itself
        $stmt_session = $conn->prepare("DELETE FROM chat_sessions WHERE id = ? AND user_id = ?");
        $stmt_session->bind_param("ii", $session_id, $user_id);
        $stmt_session->execute();
        
        $affected_rows = $stmt_session->affected_rows;
        $stmt_session->close();
        
        $conn->commit();

        if ($affected_rows > 0) {
            echo json_encode(['success' => true, 'message' => 'Session deleted successfully.']);
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Session not found or permission denied.']);
        }

    } catch (Exception $e) {
        $conn->rollback();
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    }
}

function generate_response($conn, $user_id, $data, $files) {
    $api_key = defined('GEMINI_API_KEY') ? trim(GEMINI_API_KEY) : '';
    if (empty($api_key)) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'خطأ فادح: مفتاح Gemini API غير مهيأ في ملف config.php.']);
        return;
    }

    $session_id = $data['session_id'] ?? null;
    $prompt = $data['prompt'] ?? '';
    $base64Image = $data['image'] ?? null;
    $audioFile = $files['audio'] ?? null;

    if (empty($session_id) || (empty($prompt) && empty($base64Image) && empty($audioFile))) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Session ID and a prompt, image, or audio file are required.']);
        return;
    }

    try {
        $image_db_path = null;
        $audio_db_path = null;
        $prompt_for_db = $prompt;
        $prompt_for_gemini = $prompt;

        if ($base64Image) {
            if (preg_match('/data:(image\/(\w+));base64,(.*)/', $base64Image, $matches)) {
                $imageExt = $matches[2];
                $imageData = base64_decode($matches[3]);
                $upload_dir = __DIR__ . '/../uploads/chat_images/';
                if (!is_dir($upload_dir)) mkdir($upload_dir, 0775, true);
                $filename = uniqid('chat_', true) . '.' . $imageExt;
                $filepath = $upload_dir . $filename;
                if (file_put_contents($filepath, $imageData)) {
                    $image_db_path = 'uploads/chat_images/' . $filename;
                } else {
                    throw new Exception("Failed to save uploaded image.");
                }
            }
        }
        
        if ($audioFile && $audioFile['error'] === UPLOAD_ERR_OK) {
            $upload_dir = __DIR__ . '/../uploads/chat_audio/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0775, true);
            $filename = uniqid('audio_', true) . '.webm';
            $filepath = $upload_dir . $filename;
            if (move_uploaded_file($audioFile['tmp_name'], $filepath)) {
                $audio_db_path = 'uploads/chat_audio/' . $filename;
                $prompt_for_gemini = "حلل هذه الرسالة الصوتية وقدم استشارة بيطرية بناءً عليها.";
                $prompt_for_db = '[رسالة صوتية]';
            } else {
                throw new Exception("Failed to save uploaded audio file.");
            }
        }

        // Save user message to database
        $sql = "INSERT INTO chat_messages (session_id, user_id, role, content, image_path, audio_path) VALUES (?, ?, 'user', ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
             $sql_fallback = "INSERT INTO chat_messages (session_id, user_id, role, content, image_path) VALUES (?, ?, 'user', ?, ?)";
             $stmt = $conn->prepare($sql_fallback);
             if($stmt === false) throw new Exception("Database prepare failed for text message: " . $conn->error);
             $stmt->bind_param("iiss", $session_id, $user_id, $prompt_for_db, $image_db_path);
        } else {
            $stmt->bind_param("iisss", $session_id, $user_id, $prompt_for_db, $image_db_path, $audio_db_path);
        }
        $stmt->execute();
        $stmt->close();
        
        // Retrieve conversation history
        $history_stmt = $conn->prepare("SELECT role, content, image_path FROM chat_messages WHERE session_id = ? ORDER BY created_at ASC");
        $history_stmt->bind_param("i", $session_id);
        $history_stmt->execute();
        $history_result = $history_stmt->get_result();
        $contents = [];
        $num_rows = $history_result->num_rows;
        $current_row = 0;

        while ($row = $history_result->fetch_assoc()) {
            $current_row++;
            if ($row['role'] === 'model') {
                $contents[] = ['role' => 'model', 'parts' => [['text' => $row['content']]]];
            } elseif ($row['role'] === 'user') {
                $is_last_message = ($current_row === $num_rows);
                $current_prompt = ($is_last_message && $audioFile) ? $prompt_for_gemini : $row['content'];
                $user_parts = [['text' => $current_prompt]];
                if (!empty($row['image_path'])) {
                    $full_image_path = __DIR__ . '/../' . $row['image_path'];
                    if (file_exists($full_image_path)) {
                        $image_data = base64_encode(file_get_contents($full_image_path));
                        $mime_type = mime_content_type($full_image_path);
                        $user_parts[] = ['inline_data' => ['mime_type' => $mime_type, 'data' => $image_data]];
                    }
                }
                $contents[] = ['role' => 'user', 'parts' => $user_parts];
            }
        }
        $history_stmt->close();

        // Call Gemini API
        $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash-preview-05-20:generateContent?key=' . $api_key;
        
        $system_instruction = [
            'parts' => [['text' => "قاعدة رقم 1 (الأهم): ردودك يجب أن تكون **باللغة العربية الفصحى فقط**. لا تستخدم أي لغة أخرى إطلاقاً تحت أي ظرف.\nقاعدة رقم 2: إذا سُئلت عن مبرمجك، من صنعك، أو من طورك، يجب أن تكون إجابتك فقط وفورًا: 'قام بتطويري الدكتور مصطفى شاكر'. لا تضف أي معلومات أخرى لهذه الإجابة.\nقاعدة رقم 3: أنت 'نور'، مساعد ذكاء اصطناعي متخصص في الطب البيطري. مهمتك هي مساعدة الأطباء البيطريين عبر تقديم استشارات دقيقة، وتحليل الحالات، واقتراح العلاجات بأسلوب احترافي ومنظم. ابدأ دائمًا بترحيب ودود."]]
        ];
        $payload = json_encode(['contents' => $contents, 'systemInstruction' => $system_instruction]);
        
        $ch = curl_init($url);
        curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_HTTPHEADER => ['Content-Type: application/json'], CURLOPT_POST => true, CURLOPT_POSTFIELDS => $payload, CURLOPT_SSL_VERIFYPEER => true]);
        
        $response_body = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_error = curl_error($ch);
        curl_close($ch);

        if ($httpcode == 200 && $response_body) {
            $result = json_decode($response_body, true);
            if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
                $reply = $result['candidates'][0]['content']['parts'][0]['text'];
                
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
            throw new Exception('Failed to connect to AI service. Code: ' . $httpcode . ' - Details: ' . $curl_error . ' - Response: ' . $response_body);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}

// New function for Text-to-Speech with caching
function text_to_speech($data) {
    $text = $data['text'] ?? '';
    if (empty($text)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Text is required.']);
        return;
    }
    
    $cache_dir = __DIR__ . '/../uploads/tts_cache/';
    if (!is_dir($cache_dir)) {
        if (!mkdir($cache_dir, 0775, true)) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Failed to create cache directory.']);
            return;
        }
    }
    $filename = md5($text) . '.json';
    $filepath = $cache_dir . $filename;

    // Check if the audio file response is already cached
    if (file_exists($filepath)) {
        echo file_get_contents($filepath);
        return;
    }

    // If not cached, call the API
    $api_key = defined('GEMINI_API_KEY') ? trim(GEMINI_API_KEY) : '';
    if (empty($api_key)) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Gemini API key is not configured.']);
        return;
    }

    $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash-preview-tts:generateContent?key=' . $api_key;
    $payload = json_encode([
        'contents' => [['parts' => [['text' => $text]]]],
        'generationConfig' => [
            'responseModalities' => ["AUDIO"],
            'speechConfig' => [
                'voiceConfig' => [
                    'prebuiltVoiceConfig' => ['voiceName' => 'Kore']
                ]
            ]
        ],
        'model' => "gemini-2.5-flash-preview-tts"
    ]);

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $payload,
        CURLOPT_SSL_VERIFYPEER => true
    ]);

    $response_body = curl_exec($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    curl_close($ch);

    if ($httpcode == 200 && $response_body) {
        $result = json_decode($response_body, true);
        $audio_base64 = $result['candidates'][0]['content']['parts'][0]['inlineData']['data'] ?? null;
        $mime_type = $result['candidates'][0]['content']['parts'][0]['inlineData']['mimeType'] ?? 'audio/L16;rate=24000';

        if ($audio_base64) {
            $response_to_send = json_encode(['success' => true, 'data' => ['audio_base64' => $audio_base64, 'mime_type' => $mime_type]]);
            
            // Save the successful response to the cache file
            file_put_contents($filepath, $response_to_send);

            echo $response_to_send;
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Failed to extract audio from API response.']);
        }
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Failed to connect to TTS service. Code: ' . $httpcode . ' - Details: ' . $curl_error]);
    }
}
?>

