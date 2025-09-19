<?php
// ===== الإضافة الأساسية: يجب بدء الجلسة في بداية الملف =====
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
// =======================================================

require '../db_connect.php'; // يوفر المتغير $conn
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'User not authenticated.']);
    exit;
}

$user_id = $_SESSION['user_id'];
$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);

try {
    if ($method === 'POST') {
        // إضافة مورد جديد (باستخدام $conn)
        $stmt = $conn->prepare("INSERT INTO suppliers (user_id, name, phone, email, address, notes) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssss", $user_id, $input['name'], $input['phone'], $input['email'], $input['address'], $input['notes']);
        $stmt->execute();
        $stmt->close();
        echo json_encode(['success' => true, 'message' => 'تم إضافة المورد بنجاح!']);
    
    } elseif ($method === 'PUT') {
        // تعديل مورد (باستخدام $conn)
        $stmt = $conn->prepare("UPDATE suppliers SET name = ?, phone = ?, email = ?, address = ?, notes = ? WHERE id = ? AND user_id = ?");
        $stmt->bind_param("sssssii", $input['name'], $input['phone'], $input['email'], $input['address'], $input['notes'], $input['id'], $user_id);
        $stmt->execute();
        $stmt->close();
        echo json_encode(['success' => true, 'message' => 'تم تعديل المورد بنجاح!']);
    
    } elseif ($method === 'DELETE') {
        // حذف مورد (باستخدام $conn)
        $stmt = $conn->prepare("DELETE FROM suppliers WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $input['id'], $user_id);
        $stmt->execute();
        $stmt->close();
        echo json_encode(['success' => true, 'message' => 'تم حذف المورد بنجاح!']);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

$conn->close();
?>
