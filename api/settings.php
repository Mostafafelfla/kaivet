<?php
// api/settings.php (محدث لدعم الإحداثيات وأرقام الهواتف الجديدة)
require '../db_connect.php'; 
header('Content-Type: application/json; charset=utf-8');

error_reporting(0); 

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'User not authenticated.']);
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    $conn->begin_transaction();
    
    // (كود رفع الصورة يبقى كما هو)
    if (isset($_FILES['profile_pic'])) {
        // ... (كود رفع الصورة)
        $uploadDir = '../uploads/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
        $fileInfo = pathinfo($_FILES['profile_pic']['name']);
        $fileName = 'user_' . $user_id . '_' . time() . '.' . strtolower($fileInfo['extension']);
        if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $uploadDir . $fileName)) {
            $_POST['profile_pic'] = 'uploads/' . $fileName; 
        } else { throw new Exception('فشل رفع الصورة.'); }
    }

    // تحديث اسم المستخدم
    if (isset($_POST['name'])) {
        $stmt = $conn->prepare("UPDATE users SET name = ? WHERE id = ?");
        $stmt->bind_param("si", $_POST['name'], $user_id);
        $stmt->execute();
        $stmt->close();
        $_SESSION['user_name'] = $_POST['name'];
    }

    // القائمة البيضاء الجديدة للحقول التي سيتم حفظها
    $settings_to_update = [
        'clinic_name', 'clinic_address', 'currency', 'profile_pic', 
        'clinic_whatsapp', 'clinic_country_code', 'clinic_phone_2', // <-- الحقول الجديدة
        'clinic_lat', 'clinic_lng'
    ];

    $stmt = $conn->prepare("INSERT INTO user_settings (user_id, setting_key, setting_value) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
    
    foreach ($settings_to_update as $key) {
        if (isset($_POST[$key])) {
             $valueToSave = $_POST[$key];
             if ($key === 'profile_pic' && empty($valueToSave)) {
                 continue;
             }
            $stmt->bind_param("iss", $user_id, $key, $valueToSave);
            $stmt->execute();
        }
    }
    $stmt->close();
    
    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'تم حفظ الإعدادات بنجاح.']);

} catch (Exception $e) {
    if (isset($conn) && method_exists($conn, 'in_transaction') && $conn->in_transaction) {
         $conn->rollback();
    }
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

if (isset($conn)) {
    $conn->close();
}
?>