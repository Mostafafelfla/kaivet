<?php
// api/promotions.php (الإصدار 5.0: يدعم تاريخ البدء ويجلب كود الدولة)
require '../db_connect.php'; // يوفر $conn
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'User not authenticated.']);
    exit;
}

$user_id = $_SESSION['user_id'];
$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        $start_date = !empty($input['start_date']) ? $input['start_date'] : null; 
        $description = !empty($input['description']) ? $input['description'] : null;
        
        $stmt = $conn->prepare("INSERT INTO clinic_promotions (user_id, title, description, start_date, expiry_date, is_active) VALUES (?, ?, ?, ?, ?, 1)");
        $stmt->bind_param("issss", $user_id, $input['title'], $description, $start_date, $input['expiry_date']);
        $message = 'تم حفظ العرض بنجاح!';
        
    } elseif ($method === 'PUT') {
        $input = json_decode(file_get_contents('php://input'), true);
        $start_date = !empty($input['start_date']) ? $input['start_date'] : null;
        $description = !empty($input['description']) ? $input['description'] : null;

        $stmt = $conn->prepare("UPDATE clinic_promotions SET title = ?, description = ?, start_date = ?, expiry_date = ? WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ssssii", $input['title'], $description, $start_date, $input['expiry_date'], $input['promo_id'], $user_id);
        $message = 'تم تحديث العرض!';
        
    } elseif ($method === 'DELETE') {
        $input = json_decode(file_get_contents('php://input'), true);
        $stmt = $conn->prepare("DELETE FROM clinic_promotions WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $input['id'], $user_id);
        $message = 'تم حذف العرض.';
        
    } elseif ($method === 'GET') {
        $action = $_GET['action'] ?? null;
        $promo_id = intval($_GET['promo_id'] ?? 0);

        if ($action === 'get_clients' && $promo_id > 0) {
            
            // هذا هو الجزء المسؤول عن "جلب العملاء"
            $all_clients_stmt = $conn->prepare("SELECT DISTINCT owner_phone, owner_name, owner_phone_code FROM cases WHERE user_id = ? AND owner_phone IS NOT NULL AND owner_phone != ''");
            $all_clients_stmt->bind_param("i", $user_id);
            $all_clients_stmt->execute();
            $all_clients = $all_clients_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $all_clients_stmt->close();

            $sent_log_stmt = $conn->prepare("SELECT owner_phone FROM promotion_log WHERE user_id = ? AND promotion_id = ?");
            $sent_log_stmt->bind_param("ii", $user_id, $promo_id);
            $sent_log_stmt->execute();
            $sent_result = $sent_log_stmt->get_result();
            $sent_phones = [];
            while ($row = $sent_result->fetch_assoc()) {
                $sent_phones[$row['owner_phone']] = true;
            }
            $sent_log_stmt->close();

            $clients_to_send = [];
            foreach ($all_clients as $client) {
                if (!isset($sent_phones[$client['owner_phone']])) { 
                    $clients_to_send[] = $client;
                }
            }

            echo json_encode(['success' => true, 'data' => $clients_to_send, 'sent_count' => count($sent_phones)]);
            $conn->close();
            exit; 
        } else {
             throw new Exception("GET action or Promo ID not specified.");
        }
        
    } else {
         throw new Exception("Method not supported");
    }

    if (isset($stmt)) {
        $stmt->execute();
        $stmt->close();
        echo json_encode(['success' => true, 'message' => $message]);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

$conn->close();
?>