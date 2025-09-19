<?php
// ===== الإضافة الأساسية: يجب بدء الجلسة في بداية الملف =====
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
// =======================================================

// api/inventory.php
require '../db_connect.php'; // يوفر $conn ويبدأ الجلسة
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
        // إضافة صنف جديد
        $stmt = $conn->prepare("INSERT INTO inventory (user_id, name, purchase_price, price, quantity, type, package_size, unit_name, unit_sale_price, current_package_volume) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        $package_size = !empty($input['package_size']) ? $input['package_size'] : null;
        $unit_name = !empty($input['unit_name']) ? $input['unit_name'] : null;
        $unit_sale_price = !empty($input['unit_sale_price']) ? $input['unit_sale_price'] : null;
        // عند إضافة عبوة كاملة لأول مرة، الحجم الحالي هو صفر (أو حجم العبوة إذا تم بيعها بالفرط)
        $current_volume = 0.00; 

        $stmt->bind_param("isddissssd", 
            $user_id, $input['name'], $input['purchase_price'], $input['price'], $input['quantity'], 
            $input['type'], $package_size, $unit_name, $unit_sale_price, $current_volume
        );
        $stmt->execute();
        echo json_encode(['success' => true, 'message' => 'تمت إضافة الصنف بنجاح!']);

    } elseif ($method === 'PUT') {
        // تعديل صنف
        $stmt = $conn->prepare("UPDATE inventory SET name = ?, purchase_price = ?, price = ?, quantity = ?, package_size = ?, unit_name = ?, unit_sale_price = ? WHERE id = ? AND user_id = ?");
        
        $package_size = !empty($input['package_size']) ? $input['package_size'] : null;
        $unit_name = !empty($input['unit_name']) ? $input['unit_name'] : null;
        $unit_sale_price = !empty($input['unit_sale_price']) ? $input['unit_sale_price'] : null;

        $stmt->bind_param("sddisssii", 
            $input['name'], $input['purchase_price'], $input['price'], $input['quantity'], 
            $package_size, $unit_name, $unit_sale_price, 
            $input['id'], $user_id
        );
        $stmt->execute();
        echo json_encode(['success' => true, 'message' => 'تم تحديث الصنف بنجاح!']);

    } elseif ($method === 'DELETE') {
        // حذف ناعم (Soft Delete)
        if (isset($input['id']) && isset($input['soft']) && $input['soft'] === true) {
            $stmt = $conn->prepare("UPDATE inventory SET is_deleted = 1 WHERE id = ? AND user_id = ?");
            $stmt->bind_param("ii", $input['id'], $user_id);
            $stmt->execute();
            echo json_encode(['success' => true, 'message' => 'تم حذف الصنف.']);
        }
    }

    $stmt->close();

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

$conn->close();
?>
