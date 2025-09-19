<?php
// ===== الإضافة الأساسية: يجب بدء الجلسة في بداية الملف =====
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
// =======================================================

// api/cases.php (محدث لدعم كود الدولة)
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

$conn->begin_transaction();

try {
    if ($method === 'POST') { // إضافة حالة جديدة
        $stmt = $conn->prepare("INSERT INTO cases (user_id, owner_name, animal_name, animal_type, owner_phone, owner_phone_code, notes) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("issssss", $user_id, $input['owner_name'], $input['animal_name'], $input['animal_type'], $input['owner_phone'], $input['owner_phone_code'], $input['notes']);
        $stmt->execute();
        $case_id = $conn->insert_id; 

        // (منطق إضافة التطعيمات والعلاجات يبقى كما هو)
        if (!empty($input['vaccinations'])) {
            $stmt_vac = $conn->prepare("INSERT INTO case_vaccinations (case_id, user_id, name, date, next_due_date) VALUES (?, ?, ?, ?, ?)");
            foreach ($input['vaccinations'] as $vac) {
                $date = !empty($vac['date']) ? $vac['date'] : null;
                $next_date = !empty($vac['next_due_date']) ? $vac['next_due_date'] : null;
                $stmt_vac->bind_param("iisss", $case_id, $user_id, $vac['name'], $date, $next_date);
                $stmt_vac->execute();
            }
            $stmt_vac->close();
        }
        if (!empty($input['treatments'])) {
            $stmt_treat = $conn->prepare("INSERT INTO case_treatments (case_id, user_id, name, date, next_due_date) VALUES (?, ?, ?, ?, ?)");
            foreach ($input['treatments'] as $treat) {
                $date = !empty($treat['date']) ? $treat['date'] : null;
                $next_date = !empty($treat['next_due_date']) ? $treat['next_due_date'] : null;
                $stmt_treat->bind_param("iisss", $case_id, $user_id, $treat['name'], $date, $next_date);
                $stmt_treat->execute();
            }
            $stmt_treat->close();
        }
        
        $message = "تم حفظ الحالة بنجاح.";

    } elseif ($method === 'PUT') { // تعديل حالة موجودة
        $case_id = $input['id'];
        $stmt = $conn->prepare("UPDATE cases SET owner_name = ?, animal_name = ?, animal_type = ?, owner_phone = ?, owner_phone_code = ?, notes = ? WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ssssssii", $input['owner_name'], $input['animal_name'], $input['animal_type'], $input['owner_phone'], $input['owner_phone_code'], $input['notes'], $case_id, $user_id);
        $stmt->execute();

        // (منطق حذف وإعادة إضافة التطعيمات والعلاجات يبقى كما هو)
        $stmt_del_vac = $conn->prepare("DELETE FROM case_vaccinations WHERE case_id = ? AND user_id = ?");
        $stmt_del_vac->bind_param("ii", $case_id, $user_id);
        $stmt_del_vac->execute();
        $stmt_del_treat = $conn->prepare("DELETE FROM case_treatments WHERE case_id = ? AND user_id = ?");
        $stmt_del_treat->bind_param("ii", $case_id, $user_id);
        $stmt_del_treat->execute();
        // ... إعادة الإضافة ...
        if (!empty($input['vaccinations'])) {
            $stmt_vac = $conn->prepare("INSERT INTO case_vaccinations (case_id, user_id, name, date, next_due_date) VALUES (?, ?, ?, ?, ?)");
            foreach ($input['vaccinations'] as $vac) {
                 $date = !empty($vac['date']) ? $vac['date'] : null;
                $next_date = !empty($vac['next_due_date']) ? $vac['next_due_date'] : null;
                $stmt_vac->bind_param("iisss", $case_id, $user_id, $vac['name'], $date, $next_date);
                $stmt_vac->execute();
            }
            $stmt_vac->close();
        }
        if (!empty($input['treatments'])) {
            $stmt_treat = $conn->prepare("INSERT INTO case_treatments (case_id, user_id, name, date, next_due_date) VALUES (?, ?, ?, ?, ?)");
            foreach ($input['treatments'] as $treat) {
                $date = !empty($treat['date']) ? $treat['date'] : null;
                $next_date = !empty($treat['next_due_date']) ? $treat['next_due_date'] : null;
                $stmt_treat->bind_param("iisss", $case_id, $user_id, $treat['name'], $date, $next_date);
                $stmt_treat->execute();
            }
            $stmt_treat->close();
        }

        $message = "تم تحديث الحالة بنجاح.";
    
    } elseif ($method === 'DELETE') {
        // (الحذف يبقى كما هو)
        $input = json_decode(file_get_contents('php://input'), true);
        $stmt = $conn->prepare("DELETE FROM cases WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $input['id'], $user_id);
        $stmt->execute();
        $message = "تم حذف الحالة.";
    }

    $conn->commit();
    echo json_encode(['success' => true, 'message' => $message ?? 'تمت العملية']);

} catch (Exception $e) {
    $conn->rollback();
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

$conn->close();
?>
