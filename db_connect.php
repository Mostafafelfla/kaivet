<?php
// db_connect.php

// معلومات الاتصال بالقاعدة
$host = "db.fr-pari1.bengt.wasmernet.com";
$port = 10272;
$dbname = "dbc3TunoJnM6JPhQfR7ShYkA";
$username = "caf2039c730c80001e8d33114ed9";
$password = "068ccaf2-039c-74cc-8000-d5b9ce7d59dd";

// إنشاء الاتصال
$conn = new mysqli($host, $username, $password, $dbname, $port);

// التحقق من الاتصال
if ($conn->connect_error) {
    die("فشل الاتصال بقاعدة البيانات: " . $conn->connect_error);
}

// ضبط الترميز
$conn->set_charset("utf8mb4");

// يمكنك الآن استخدام $conn في كل الملفات
