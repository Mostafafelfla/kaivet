<?php
// db_connect.php

// بدء الجلسة لتخزين معلومات تسجيل الدخول
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// إعدادات الاتصال بقاعدة البيانات الجديدة على Wasmer
$db_host = 'db.fr-pari1.bengt.wasmernet.com';
$db_port = 10272;
$db_user = 'c41729a076d38000c7e0a7a3ffdb';
$db_pass = '068cc417-29a0-789b-8000-d39dfa5d69a7';
$db_name = 'dbnrpGyXBAQiLNPFWkedQS7T';

// إنشاء اتصال جديد بقاعدة البيانات
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name, $db_port);

// التحقق من وجود أخطاء في الاتصال
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// ضبط الترميز لضمان دعم اللغة العربية بشكل صحيح
$conn->set_charset("utf8mb4");
?>
