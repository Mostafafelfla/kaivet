<?php
// api/login.php (الإصدار الصحيح - يستخدم MySQLi و $conn)

// ملف الاتصال يبدأ الجلسة ويوفر $conn
require '../db_connect.php'; 

$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

if (empty($email) || empty($password)) {
    header("Location: ../login.php?error=" . urlencode("يرجى إدخال البريد الإلكتروني وكلمة المرور."));
    exit();
}

try {
    // 1. استخدم $conn (وليس $pdo)
    // 2. اقرأ من عمود "password" (كما هو موجود في قاعدة بياناتك)
    $stmt = $conn->prepare("SELECT id, name, password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    // 3. التحقق من الباسوورد المخزن في عمود "password"
    if ($user && password_verify($password, $user['password'])) {
        
        // --- نجح تسجيل الدخول ---
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name']; 

        // إرسال المستخدم إلى لوحة التحكم
        header("Location: ../index.php");
        exit();

    } else {
        // خطأ في الإيميل أو كلمة المرور
        header("Location: ../login.php?error=" . urlencode("البريد الإلكتروني أو كلمة المرور غير صحيحة."));
        exit();
    }

} catch (Exception $e) { // نستخدم Exception العام بدلاً من PDOException
    header("Location: ../login.php?error=" . urlencode("خطأ في قاعدة البيانات: " . $e->getMessage()));
    exit();
}

$conn->close();
?>