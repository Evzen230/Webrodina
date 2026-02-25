<?php
require "includes/auth.php";
require "includes/db.php";
include "includes/auth_check.php";
checkAuth();

$user_id = $_SESSION['user_id'];
$message = "";
$error = "";

// Načtení aktuálních údajů uživatele
$stmt = $conn->prepare("SELECT user FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$current_user = $stmt->fetch();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new_username = trim($_POST['username']);
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    try {
        // 1. Aktualizace jména (pokud se změnilo)
        if (!empty($new_username) && $new_username !== $current_user['user']) {
            $update_name = $conn->prepare("UPDATE users SET user = ? WHERE id = ?");
            $update_name->execute([$new_username, $user_id]);
            $_SESSION['user'] = $new_username; // Aktualizace jména v session
            $message = "Uživatelské jméno bylo aktualizováno. ";
        }

        // 2. Aktualizace hesla (pokud bylo vyplněno)
        if (!empty($new_password)) {
            if ($new_password === $confirm_password) {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $update_pw = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                $update_pw->execute([$hashed_password, $user_id]);
                $message .= "Heslo bylo úspěšně změněno.";
            } else {
                $error = "Hesla se neshodují!";
            }
        }
    } catch (PDOException $e) {
        $error = "Chyba při ukládání: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <srcript src="includes/respons.js"></script>
    <title>Můj Profil</title>
    <link rel="stylesheet" href="style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <style>
        .profile-container { max-width: 400px; margin: 50px auto; padding: 20px; background: white; border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
        .form-control { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 8px; box-sizing: border-box; }
        .btn-save { background: #3498db; color: white; border: none; padding: 10px 20px; border-radius: 8px; cursor: pointer; width: 100%; font-size: 16px; }
        .alert { padding: 10px; border-radius: 8px; margin-bottom: 15px; }
        .alert-success { background: #d4edda; color: #3498db; }
        .alert-danger { background: #f8d7da; color: #721c24; }
    </style>
</head>
<!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-GGN9Y19FYQ"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'G-GGN9Y19FYQ');
</script>
<body>
    <div class="profile-container">
        <h2>Nastavení profilu</h2>
        
        <?php if ($message): ?> <div class="alert alert-success"><?= $message ?></div> <?php endif; ?>
        <?php if ($error): ?> <div class="alert alert-danger"><?= $error ?></div> <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Uživatelské jméno:</label>
                <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($current_user['user']) ?>" required>
            </div>
            
            <hr>
            <p style="font-size: 0.9rem; color: #666;">Ponechte prázdné, pokud heslo nechcete měnit:</p>
            
            <div class="form-group">
                <label>Nové heslo:</label>
                <input type="password" name="new_password" class="form-control">
            </div>
            
            <div class="form-group">
                <label>Potvrzení nového hesla:</label>
                <input type="password" name="confirm_password" class="form-control">
            </div>
            
            <button type="submit" class="btn-save">Uložit změny</button>
            <p style="text-align: center; margin-top: 15px;"><a href="index.php">Zpět na hlavní stránku</a></p>
        </form>
    </div>
</body>
</html>