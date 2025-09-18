<?php
// We must check for an active session to protect this page.
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// If user_id is not set in the session, redirect to the login page.
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// تحميل القالب الرئيسي الذي سيقوم بتجميع باقي الأجزاء
include 'layout.php';
?>