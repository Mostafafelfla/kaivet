<?php
// ===== الإضافة هنا =====
// يجب بدء الجلسة في بداية كل ملف API للتعرف على المستخدم المسجل دخوله
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
// ======================

require '../db_connect.php'; // يوفر المتغير $conn
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'User not authenticated.']);
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    // مصفوفة الاستجابة المبدئية (تبقى كما هي)
    $response = [
        'success' => true,
        'data' => [
            'inventory' => [],
            'sales' => [],
            'expenses' => [],
            'todos' => [],
            'vaccination_reminders' => [],
            'cases' => [],
            'suppliers' => [],
            'doctors' => [],
            'services' => [],
            'promotions' => [],
            'settings' => []
        ]
    ];

    // دالة مساعدة (تبقى كما هي)
    function fetchAll($conn, $sql, $params = [], $types = "") {
        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            error_log("Prepare failed: " . $conn->error);
            throw new Exception("Database query failed.");
        }
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        if (!$stmt->execute()) {
             error_log("Execute failed: " . $stmt->error);
             throw new Exception("Database execute failed.");
        }
        $result = $stmt->get_result();
        if ($result === false) {
             error_log("Get result failed: " . $stmt->error);
             throw new Exception("Database get_result failed.");
        }
        $data = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $data;
    }
    
    // جلب كل البيانات (يبقى كما هو)
    $response['data']['inventory'] = fetchAll($conn, "SELECT * FROM inventory WHERE user_id = ? AND is_deleted = 0 ORDER BY name ASC", [$user_id], "i");
    $response['data']['sales'] = fetchAll($conn, "SELECT * FROM sales WHERE user_id = ? AND is_deleted = 0 ORDER BY created_at DESC", [$user_id], "i");
    $response['data']['expenses'] = fetchAll($conn, "SELECT * FROM expenses WHERE user_id = ? AND is_deleted = 0 ORDER BY created_at DESC", [$user_id], "i");
    $response['data']['todos'] = fetchAll($conn, "SELECT * FROM todos WHERE user_id = ? ORDER BY created_at DESC", [$user_id], "i");
    $response['data']['suppliers'] = fetchAll($conn, "SELECT * FROM suppliers WHERE user_id = ? ORDER BY name ASC", [$user_id], "i");
    $response['data']['doctors'] = fetchAll($conn, "SELECT * FROM doctors WHERE user_id = ? ORDER BY name ASC", [$user_id], "i");
    $response['data']['services'] = fetchAll($conn, "SELECT * FROM clinic_services WHERE user_id = ? ORDER BY service_name ASC", [$user_id], "i");
    $response['data']['promotions'] = fetchAll($conn, "SELECT * FROM clinic_promotions WHERE user_id = ? ORDER BY created_at DESC", [$user_id], "i");
    
    $sql_reminders = "
        SELECT 
            c.owner_name, 
            c.animal_name, 
            c.owner_phone,
            c.owner_phone_code,
            v.name AS vaccination_name, 
            v.next_due_date 
        FROM case_vaccinations v 
        JOIN cases c ON v.case_id = c.id 
        WHERE v.user_id = ? 
          AND v.next_due_date IS NOT NULL
          AND v.next_due_date BETWEEN DATE_SUB(CURDATE(), INTERVAL 3 DAY) AND DATE_ADD(CURDATE(), INTERVAL 7 DAY) 
        ORDER BY v.next_due_date ASC
    ";
    $response['data']['vaccination_reminders'] = fetchAll($conn, $sql_reminders, [$user_id], "i");
    
    // الحالات (تبقى كما هي)
    $cases = fetchAll($conn, "SELECT * FROM cases WHERE user_id = ? ORDER BY created_at DESC", [$user_id], "i");
    $vaccinations = fetchAll($conn, "SELECT * FROM case_vaccinations WHERE user_id = ?", [$user_id], "i");
    $treatments = fetchAll($conn, "SELECT * FROM case_treatments WHERE user_id = ?", [$user_id], "i");
    
    $cases_by_id = [];
    foreach($cases as $case) {
        $case['vaccinations'] = [];
        $case['treatments'] = [];
        $cases_by_id[$case['id']] = $case;
    }
    foreach($vaccinations as $v) {
        if(isset($cases_by_id[$v['case_id']])) {
            $cases_by_id[$v['case_id']]['vaccinations'][] = $v;
        }
    }
    foreach($treatments as $t) {
        if(isset($cases_by_id[$t['case_id']])) {
            $cases_by_id[$t['case_id']]['treatments'][] = $t;
        }
    }
    $response['data']['cases'] = array_values($cases_by_id);

    // الإعدادات (تبقى كما هي)
    $settings_raw = fetchAll($conn, "SELECT setting_key, setting_value FROM user_settings WHERE user_id = ?", [$user_id], "i");
    $settings_formatted = [];
    foreach ($settings_raw as $setting) {
        $settings_formatted[$setting['setting_key']] = $setting['setting_value'];
    }
    $user_data_result = fetchAll($conn, "SELECT name, email FROM users WHERE id = ?", [$user_id], "i");
    if(!empty($user_data_result)) {
        $settings_formatted['name'] = $user_data_result[0]['name'];
        $settings_formatted['email'] = $user_data_result[0]['email'];
    }
    $response['data']['settings'] = $settings_formatted;

    echo json_encode($response);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

$conn->close();
?>
