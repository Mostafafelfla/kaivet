<?php
// db_connect.php

// بدء الجلسة لتخزين معلومات تسجيل الدخول
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// إعدادات الاتصال بقاعدة البيانات الجديدة على Wasmer
$db_host = 'db.fr-pari1.bengt.wasmernet.com';
$db_port = 10272;
$db_user = 'c32d66c47d8480003fef9c4454fb';
$db_pass = '068cc32d-66c5-7409-8000-73946aec74a9';
$db_name = 'dbiY5uGVifo5TpLKqCqQ3rVq';

// إنشاء اتصال جديد بقاعدة البيانات مع البورت
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name, $db_port);

// التحقق من وجود أخطاء في الاتصال
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// ضبط الترميز لدعم اللغة العربية
$conn->set_charset("utf8mb4");
?>
