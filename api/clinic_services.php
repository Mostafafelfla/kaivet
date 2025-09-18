<?php
// api/clinic_services.php (الإصدار المبسط: يستخدم price_note فقط)
require '../db_connect.php'; // يوفر $conn
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
        // إضافة خدمة (بدون عمود السعر الرقمي)
        $stmt = $conn->prepare("INSERT INTO clinic_services (user_id, service_name, description, price_note) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isss", $user_id, $input['service_name'], $input['description'], $input['price_note']);
        $message = 'تمت إضافة الخدمة بنجاح!';
        
    } elseif ($method === 'PUT') {
        // تعديل خدمة
        $stmt = $conn->prepare("UPDATE clinic_services SET service_name = ?, description = ?, price_note = ? WHERE id = ? AND user_id = ?");
        $stmt->bind_param("sssii", $input['service_name'], $input['description'], $input['price_note'], $input['service_id'], $user_id);
        $message = 'تم تحديث الخدمة!';
        
    } elseif ($method === 'DELETE') {
        // حذف خدمة
        $stmt = $conn->prepare("DELETE FROM clinic_services WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $input['id'], $user_id);
        $message = 'تم حذف الخدمة.';
    }

    if (isset($stmt)) {
        $stmt->execute();
        $stmt->close();
        echo json_encode(['success' => true, 'message' => $message]);
    } else {
        throw new Exception("Method not supported");
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'خطأ: ' . $e->getMessage()]);
}

$conn->close();
?>