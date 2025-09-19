<?php
// ===== الإضافة الأساسية: يجب بدء الجلسة في بداية الملف =====
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
// =======================================================

require '../db_connect.php';
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
        // **تحسين:** التحقق من وجود البيانات قبل استخدامها
        if (!isset($input['service_name'], $input['price_note'], $input['description'])) {
            throw new Exception("بيانات الخدمة غير مكتملة.");
        }
        $stmt = $conn->prepare("INSERT INTO clinic_services (user_id, service_name, description, price_note) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isss", $user_id, $input['service_name'], $input['description'], $input['price_note']);
        $message = 'تمت إضافة الخدمة بنجاح!';
        
    } elseif ($method === 'PUT') {
        // **تحسين:** التحقق من وجود البيانات قبل استخدامها
        if (!isset($input['service_id'], $input['service_name'], $input['price_note'], $input['description'])) {
            throw new Exception("بيانات تعديل الخدمة غير مكتملة.");
        }
        $stmt = $conn->prepare("UPDATE clinic_services SET service_name = ?, description = ?, price_note = ? WHERE id = ? AND user_id = ?");
        $stmt->bind_param("sssii", $input['service_name'], $input['description'], $input['price_note'], $input['service_id'], $user_id);
        $message = 'تم تحديث الخدمة!';
        
    } elseif ($method === 'DELETE') {
        // **تحسين:** التحقق من وجود البيانات قبل استخدامها
        if (!isset($input['id'])) {
            throw new Exception("معرّف الخدمة مطلوب للحذف.");
        }
        $stmt = $conn->prepare("DELETE FROM clinic_services WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $input['id'], $user_id);
        $message = 'تم حذف الخدمة.';

    } else {
        // إذا كان نوع الطلب غير مدعوم
        http_response_code(405); // Method Not Allowed
        throw new Exception("Method not supported");
    }

    if (isset($stmt)) {
        $stmt->execute();
        $stmt->close();
        echo json_encode(['success' => true, 'message' => $message]);
    }

} catch (Exception $e) {
    // إرجاع خطأ 500 في حالة حدوث أي مشكلة
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'خطأ: ' . $e->getMessage()]);
}

$conn->close();
?>
