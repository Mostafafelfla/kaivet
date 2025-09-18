<?php
// api/test_price_insert.php
require '../db_connect.php'; 
header('Content-Type: application/json; charset=utf-8');

try {
    $price_to_insert = 55.50; 
    
    $stmt = $conn->prepare("INSERT INTO test_prices (price) VALUES (?)");
    
    // **التحقق من وجود خطأ في Prepare**
    if ($stmt === false) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $stmt->bind_param("d", $price_to_insert);
    
    $stmt->execute();
    $stmt->close();
    
    echo json_encode(['success' => true, 'message' => 'تم حفظ السعر بنجاح!']);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'خطأ: ' . $e->getMessage()]);
}

$conn->close();
?>