<link rel="stylesheet" href="style.css">
<!-- Google tag (gtag.js) 
<script async src="https://www.googletagmanager.com/gtag/js?id=G-GGN9Y19FYQ"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'G-GGN9Y19FYQ');
</script>
-->
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
