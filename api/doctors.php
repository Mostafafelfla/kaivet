<?php
// ===== الإضافة الأساسية: يجب بدء الجلسة في بداية الملف =====
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
// =======================================================

// api/doctors.php (الكود النهائي والمُحسّن)
require '../db_connect.php';
header('Content-Type: application/json; charset=utf-8');

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'User not authenticated.']);
    exit;
}

$user_id = $_SESSION['user_id'];
// استخدام $_POST لأننا نرسل البيانات كـ FormData
$method = $_POST['_method'] ?? $_SERVER['REQUEST_METHOD']; 

try {
    if ($method === 'POST') {
        // البيانات النصية من الفورم
        $doctor_id = isset($_POST['doctor_id']) && !empty($_POST['doctor_id']) ? (int)$_POST['doctor_id'] : null;
        $name = $_POST['name'] ?? '';
        $specialty = $_POST['specialty'] ?? null;
        $phone = $_POST['phone'] ?? null;
        $address = $_POST['address'] ?? null;
        $profile_pic_path = null;

        // --- ⭐ الجزء الأهم: معالجة الصورة المقصوصة (نسخة محسّنة) ---
        if (isset($_POST['profile_pic_cropped']) && !empty($_POST['profile_pic_cropped'])) {
            $imgData = $_POST['profile_pic_cropped'];
            
            // فصل نوع الصورة عن بيانات Base64
            list($type, $imgData) = explode(';', $imgData);
            list(, $imgData)      = explode(',', $imgData);
            $decodedData = base64_decode($imgData);
            
            // تحديد امتداد الملف تلقائيًا (يدعم jpg, png, etc.)
            $extension = strpos($type, 'jpeg') !== false ? '.jpg' : '.png';
            $fileName = 'doctor_' . uniqid() . '_' . time() . $extension;
            
            // مسار آمن ومضمون لمجلد الـ uploads
            $filePath = dirname(__DIR__) . '/uploads/' . $fileName;

            // حفظ الصورة في مجلد uploads
            if (file_put_contents($filePath, $decodedData)) {
                // خزن المسار النسبي في قاعدة البيانات
                $profile_pic_path = 'uploads/' . $fileName;
            } else {
                throw new Exception('فشل حفظ الصورة في المجلد. تأكد من صلاحيات الكتابة.');
            }
        }
        // --- نهاية معالجة الصورة ---

        if ($doctor_id) {
            // تحديث بيانات طبيب حالي
            if ($profile_pic_path) {
                // تحديث الصورة إذا تم رفع صورة جديدة
                $stmt = $conn->prepare("UPDATE doctors SET name=?, specialty=?, phone=?, address=?, profile_pic=? WHERE id=? AND user_id=?");
                $stmt->bind_param("sssssii", $name, $specialty, $phone, $address, $profile_pic_path, $doctor_id, $user_id);
            } else {
                // تحديث البيانات بدون تغيير الصورة
                $stmt = $conn->prepare("UPDATE doctors SET name=?, specialty=?, phone=?, address=? WHERE id=? AND user_id=?");
                $stmt->bind_param("ssssii", $name, $specialty, $phone, $address, $doctor_id, $user_id);
            }
            $message = 'تم تحديث بيانات الطبيب بنجاح!';
        } else {
            // إضافة طبيب جديد
            $stmt = $conn->prepare("INSERT INTO doctors (user_id, name, specialty, phone, address, profile_pic) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("isssss", $user_id, $name, $specialty, $phone, $address, $profile_pic_path);
            $message = 'تمت إضافة الطبيب بنجاح!';
        }

        $stmt->execute();
        $stmt->close();
        echo json_encode(['success' => true, 'message' => $message]);

    } elseif ($method === 'DELETE') {
        $input = json_decode(file_get_contents('php://input'), true);
        $doctor_id = $input['id'] ?? null;
        if (!$doctor_id) throw new Exception("لم يتم تحديد هوية الطبيب.");

        $stmt = $conn->prepare("DELETE FROM doctors WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $doctor_id, $user_id);
        $stmt->execute();
        $stmt->close();
        echo json_encode(['success' => true, 'message' => 'تم حذف الطبيب بنجاح.']);
    } else {
        throw new Exception("Method not supported");
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'خطأ: ' . $e->getMessage()]);
}

$conn->close();
?>
