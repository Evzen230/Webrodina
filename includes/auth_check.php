<?php

$maintenance_mode = false; // zapnout / vypnout

// Spustíme session, pokud už neběží
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ===============================
// AUTOMATICKÉ PŘIHLÁŠENÍ PŘES COOKIE
// ===============================

if (!isset($_SESSION['user_id']) && isset($_COOKIE['remember_me'])) {

    $parts = explode(':', $_COOKIE['remember_me']);

    if (count($parts) === 2) {
        $selector = $parts[0];
        $validator = $parts[1];

        if (isset($conn)) {
            try {
                $stmt = $conn->prepare("SELECT user_id, hashed_validator FROM user_tokens WHERE selector = ? AND expires > NOW() LIMIT 1");
                $stmt->execute([$selector]);
                $token = $stmt->fetch();

                if ($token && hash_equals($token['hashed_validator'], hash('sha256', $validator))) {

                    $stmtUser = $conn->prepare("SELECT id, user, is_admin FROM users WHERE id = ?");
                    $stmtUser->execute([$token['user_id']]);
                    $user = $stmtUser->fetch();

                    if ($user) {
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['user'] = $user['user'];
                        $_SESSION['is_admin'] = $user['is_admin'];
                    }
                }
            } catch (PDOException $e) {
                // chyba DB ignorována
            }
        }
    }
}

// ===============================
// MAINTENANCE REŽIM
// ===============================

if ($maintenance_mode) {

    $current_page = basename($_SERVER['PHP_SELF']);

    if (isset($_SESSION['user_id'])) {

        // Pokud NENÍ admin a není na maintenance stránce → přesměrovat
        if (
            (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1)
            && $current_page !== 'maintenance.php'
        ) {
            header("Location: maintenance.php");
            exit();
        }
    }
}