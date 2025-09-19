<?php
// db_connect.php (النسخة الكاملة والمحدثة)

// === كود إطالة عمر الجلسة ===
// يضمن أن تظل جلسة المستخدم فعالة لمدة 24 ساعة من عدم النشاط.

// تحديد مدة صلاحية الجلسة بالثواني (86400 ثانية = 24 ساعة)
ini_set('session.gc_maxlifetime', 86400);

// تحديد مدة صلاحية ملف الكوكيز في متصفح المستخدم
ini_set('session.cookie_lifetime', 86400);

// بدء الجلسة **بعد** تحديد الإعدادات
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
// =====================================


// === كود الاتصال بقاعدة البيانات ===

// معلومات الاتصال الجديدة
$host = "db.fr-pari1.bengt.wasmernet.com";
$port = 10272;
$dbname = "dbc3TunoJnM6JPhQfR7ShYkA";
$username = "caf2039c730c80001e8d33114ed9";
$password = "068ccaf2-039c-74cc-8000-d5b9ce7d59dd";

// إنشاء الاتصال
$conn = new mysqli($host, $username, $password, $dbname, $port);

// التحقق من الاتصال
if ($conn->connect_error) {
    // إيقاف التنفيذ وإظهار الخطأ
    die("فشل الاتصال بقاعدة البيانات: " . $conn->connect_error);
}

// ضبط الترميز ليدعم اللغة العربية بشكل كامل
$conn->set_charset("utf8mb4");

// الآن يمكنك استخدام متغير $conn في كل الملفات التي تستدعي هذا الملف
?>
