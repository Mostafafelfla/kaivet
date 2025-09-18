<?php
// logout.php

// بدء الجلسة للوصول إلى متغيرات الجلسة
session_start();

// إلغاء تعيين جميع متغيرات الجلسة
$_SESSION = array();

// تدمير الجلسة
session_destroy();

// التوجيه إلى صفحة تسجيل الدخول
header("Location: login.php");
exit;
?>

