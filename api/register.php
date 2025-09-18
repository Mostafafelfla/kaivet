<?php
// تأكد من أن ملف db_connect.php موجود في المسار الصحيح
// المسار الحالي يفترض أنه موجود في: C:\xampp\htdocs\vet\db_connect.php
require '../db_connect.php'; // <--- تأكد من هذا المسار!

// جلب البيانات من الفورم
$name = $_POST['name'] ?? '';
$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

// التحقق الأساسي
if (empty($name) || empty($email) || empty($password)) {
    header("Location: ../register.php?error=" . urlencode("يرجى ملء جميع الحقول."));
    exit();
}

if ($password !== $confirm_password) {
    header("Location: ../register.php?error=" . urlencode("كلمتا المرور غير متطابقتين."));
    exit();
}

try {
    // التحقق إذا كان الإيميل مستخدماً من قبل
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        header("Location: ../register.php?error=" . urlencode("هذا البريد الإلكتروني مسجل بالفعل."));
        exit();
    }

    // تشفير كلمة المرور (مهم جداً)
    $password_hash = password_hash($password, PASSWORD_BCRYPT);

    // إدخال المستخدم الجديد في قاعدة البيانات
    $stmt = $pdo->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
    $stmt->execute([$name, $email, $password_hash]);

    // إرساله لصفحة تسجيل الدخول مع رسالة نجاح
    header("Location: ../login.php?success=" . urlencode("تم إنشاء حسابك بنجاح! يمكنك تسجيل الدخول الآن."));
    exit();

} catch (PDOException $e) {
    header("Location: ../register.php?error=" . urlencode("خطأ في قاعدة البيانات: " . $e->getMessage()));
    exit();
}
?>