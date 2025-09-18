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

// Handle GET requests to fetch messages for a specific session
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $session_id = filter_input(INPUT_GET, 'session_id', FILTER_VALIDATE_INT);

    if (!$session_id) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Session ID is required.']);
        exit;
    }

    try {
        // Verify that the session belongs to the user
        $verify_stmt = $conn->prepare("SELECT id FROM chat_sessions WHERE id = ? AND user_id = ?");
        $verify_stmt->bind_param("ii", $session_id, $user_id);
        $verify_stmt->execute();
        if ($verify_stmt->get_result()->num_rows === 0) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Access denied to this chat session.']);
            exit;
        }
        $verify_stmt->close();

        $stmt = $conn->prepare("SELECT role, content, image_path, created_at FROM chat_messages WHERE session_id = ? ORDER BY created_at ASC");
        $stmt->bind_param("i", $session_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        echo json_encode(['success' => true, 'data' => $result]);
        $stmt->close();
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method Not Allowed']);
}

$conn->close();
?>
