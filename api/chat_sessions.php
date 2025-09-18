<?php
// We must check for an active session to protect this page.
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Define a robust path to the config and db_connect files
$config_path = __DIR__ . '/../config.php';
$db_connect_path = __DIR__ . '/../db_connect.php';

// Check if files exist before trying to include them
if (!file_exists($config_path) || !file_exists($db_connect_path)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Essential configuration files are missing.']);
    exit;
}

require $config_path;
require $db_connect_path;

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'User not authenticated.']);
    exit;
}

$user_id = $_SESSION['user_id'];

// Handle GET requests for fetching all chat sessions
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
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

// Handle POST requests for creating a new chat session
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $title = $data['title'] ?? 'New Conversation';

    if (empty($title)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Title is required.']);
        exit;
    }

    try {
        $stmt = $conn->prepare("INSERT INTO chat_sessions (user_id, title) VALUES (?, ?)");
        $stmt->bind_param("is", $user_id, $title);
        $stmt->execute();
        $new_session_id = $conn->insert_id;
        $stmt->close();
        echo json_encode(['success' => true, 'message' => 'New session created.', 'data' => ['id' => $new_session_id]]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    }
}

$conn->close();
?>
