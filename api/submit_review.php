<?php
// api/submit_review.php
require '../db_connect.php'; // يوفر $conn
header('Content-Type: application/json; charset=utf-8');

// هذا الملف عام، لا نتحقق من جلسة الأدمن

$input = json_decode(file_get_contents('php://input'), true);

try {
    $doctor_id = $input['doctor_id'] ?? 0;
    $customer_name = $input['name'] ?? 'زائر';
    $rating = $input['rating'] ?? 0;
    $comment = $input['comment'] ?? '';

    if (empty($doctor_id) || $rating < 1 || $rating > 5) {
        throw new Exception("بيانات التقييم غير صالحة.");
    }
    
    // حفظ التقييم في قاعدة البيانات
    $stmt = $conn->prepare("INSERT INTO reviews (doctor_id, customer_name, rating, comment) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isis", $doctor_id, $customer_name, $rating, $comment);
    $stmt->execute();
    $stmt->close();

    echo json_encode(['success' => true, 'message' => 'شكراً لك! تم إرسال تقييمك بنجاح.']);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

$conn->close();
?>