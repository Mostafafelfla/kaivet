<?php
// ===== الإضافة الأساسية: يجب بدء الجلسة في بداية الملف =====
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
// =======================================================

require '../db_connect.php';
header('Content-Type: application/json; charset=utf-8');
error_reporting(0); // Prevent PHP notices from breaking JSON output

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'User not authenticated.']);
    exit;
}

$user_id = $_SESSION['user_id'];
$method = $_SERVER['REQUEST_METHOD'];
$data = json_decode(file_get_contents('php://input'), true);

try {
    switch ($method) {
        case 'POST':
            $stmt = $conn->prepare("INSERT INTO todos (user_id, text) VALUES (?, ?)");
            $stmt->bind_param("is", $user_id, $data['text']);
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'تمت إضافة المهمة.']);
            } else {
                throw new Exception("فشل في إضافة المهمة.");
            }
            break;
        case 'PUT':
            $stmt = $conn->prepare("UPDATE todos SET completed = ? WHERE id = ? AND user_id = ?");
            $stmt->bind_param("iii", $data['completed'], $data['id'], $user_id);
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'تم تحديث المهمة.']);
            } else {
                throw new Exception("فشل في تحديث المهمة.");
            }
            break;
        case 'DELETE':
            $stmt = $conn->prepare("DELETE FROM todos WHERE id = ? AND user_id = ?");
            $stmt->bind_param("ii", $data['id'], $user_id);
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'تم حذف المهمة.']);
            } else {
                throw new Exception("فشل في حذف المهمة.");
            }
            break;
        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Method Not Allowed']);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
$conn->close();
?>

