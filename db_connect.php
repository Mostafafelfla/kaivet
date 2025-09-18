<?php
// db_connect.php

// بدء الجلسة لتخزين معلومات تسجيل الدخول
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// إعدادات الاتصال بقاعدة البيانات
$db_host = 'localhost'; // عادة ما يكون 'localhost' في XAMPP
$db_user = 'root';      // اسم المستخدم الافتراضي في XAMPP
$db_pass = '';          // كلمة المرور الافتراضية في XAMPP فارغة
$db_name = 'vet_nour_db'; // اسم قاعدة البيانات الجديد الذي حددته

// إنشاء اتصال جديد بقاعدة البيانات
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

// التحقق من وجود أخطاء في الاتصال
if ($conn->connect_error) {
    //
    die("Connection failed: " . $conn->connect_error);
}

// ضبط الترميز لضمان دعم اللغة العربية بشكل صحيح
$conn->set_charset("utf8mb4");
?>

