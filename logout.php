<link rel="stylesheet" href="style.css">

<?php
session_start();
require "includes/db.php"; // Potřebujeme $conn pro smazání tokenu

// 1. Smazání tokenu z databáze
if (isset($_COOKIE['remember_me'])) {
    $parts = explode(':', $_COOKIE['remember_me']);
    if (count($parts) === 2) {
        $selector = $parts[0];
        
        // Smažeme záznam z DB podle selectoru
        $stmt = $conn->prepare("DELETE FROM user_tokens WHERE selector = ?");
        $stmt->execute([$selector]);
    }

    // 2. Zrušení cookie v prohlížeči (nastavíme čas do minulosti)
    setcookie('remember_me', '', time() - 3600, '/');
}

// 3. Zničení session
$_SESSION = array(); // Vymaže data ze session
session_destroy();   // Zničí session jako takovou

// 4. Přesměrování na login
header("Location: login.php");
exit();
