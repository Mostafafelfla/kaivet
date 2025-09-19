<?php
// db_connect.php

$host = "db.fr-pari1.bengt.wasmernet.com";
$port = 10272;
$dbname = "dbc3TunoJnM6JPhQfR7ShYkA";
$username = "caf2039c730c80001e8d33114ed9";
$password = "068ccaf2-039c-74cc-8000-d5b9ce7d59dd";

// إنشاء الاتصال
$mysqli = new mysqli($host, $username, $password, $dbname, $port);

// التحقق من الاتصال
if ($mysqli->connect_error) {
    die("فشل الاتصال بقاعدة البيانات: " . $mysqli->connect_error);
}

// تعيين الترميز
$mysqli->set_charset("utf8mb4");
