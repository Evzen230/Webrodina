<?php
$maintenance_mode = false; 

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
                    // PŘIDÁNO: agreed_at do SELECTu
                    $stmtUser = $conn->prepare("SELECT id, user, is_admin, agreed_at FROM users WHERE id = ?");
                    $stmtUser->execute([$token['user_id']]);
                    $user = $stmtUser->fetch();

                    if ($user) {
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['user'] = $user['user'];
                        $_SESSION['is_admin'] = $user['is_admin'];
                        
                        // OPĚT: Uložíme do session, aby auth_check věděl, že uživatel už souhlasil
                        $_SESSION['agreed_at'] = $user['agreed_at']; 
                    }
                }
            } catch (PDOException $e) {}
        }
    }
}

// ===============================
// KONTROLA SOUHLASU (GDPR GATEKEEPER)
// ===============================
// Pokud je uživatel přihlášen, ale nemá v session datum souhlasu
if (isset($_SESSION['user_id']) && empty($_SESSION['agreed_at'])) {
    $current_page = basename($_SERVER['PHP_SELF']);
    
    // Seznam stránek, kam smí jít, i když nesouhlasil (aby nenastala smyčka přesměrování)
    $allowed_pages = ['force_consent.php', 'logout.php', 'zasady.php', 'podminky.php', 'login.php'];
    
    if (!in_array($current_page, $allowed_pages)) {
        header("Location: force_consent.php");
        exit();
    }
}

// ===============================
// MAINTENANCE REŽIM
// ===============================
if ($maintenance_mode) {
    $current_page = basename($_SERVER['PHP_SELF']);
    if (isset($_SESSION['user_id'])) {
        if ((!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) && $current_page !== 'maintenance.php') {
            header("Location: maintenance.php");
            exit();
        }
    }
}