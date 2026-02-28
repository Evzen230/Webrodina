<?php
session_start();

function checkAuth() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }
}

function checkAdmin() {
    if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
        die("Sem nemáš přístup. Pouze pro administrátory.");
        exit();
    }
}
?>