<?php
session_start();
require "includes/db.php";
// include "includes/auth_check.php"; // Poznámka: na login.php auth_check většinou nepotřebuješ, mohl by zacyklit přesměrování

$maintenance_mode = false; // zapnout / vypnout
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $input_user = trim($_POST['user_input']);
    $password = $_POST['password'];

    if (!empty($input_user) && !empty($password)) {

        // PŘIDÁNO: agreed_at do SELECTu, abychom věděli, zda uživatel souhlasil
        $stmt = $conn->prepare("SELECT id, user, password, is_admin, agreed_at FROM users WHERE user = ?");
        $stmt->execute([$input_user]);
        $user_data = $stmt->fetch();

        if ($user_data && password_verify($password, $user_data['password'])) {

            // Nastavení SESSION
            $_SESSION['user_id'] = $user_data['id'];
            $_SESSION['user'] = $user_data['user'];
            $_SESSION['is_admin'] = $user_data['is_admin'];
            
            // OPRAVENO: Použití správné proměnné $user_data místo $user
            $_SESSION['agreed_at'] = $user_data['agreed_at'];

            // MAINTENANCE kontrola – admin má výjimku
            if ($maintenance_mode && $user_data['is_admin'] != 1) {
                header("Location: maintenance.php");
                exit();
            }

            // Pokud prošel vším, pošli ho na index. 
            // auth_check.php na indexu si pak přečte $_SESSION['agreed_at'] 
            // a buď ho pustí, nebo hodí na force_consent.php
            header("Location: index.php");
            exit();

        } else {
            $error = "Nesprávné jméno nebo heslo!";
        }

    } else {
        $error = "Vyplň všechna pole!";
    }
}
?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <srcript src="includes/respons.js"></script>
    <title>Přihlášení | Rodinný web</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body style="background: #f0f2f5;">

<div class="login-wrapper">
    <div class="login-card">
        <div class="login-icon">
            <i class="fas fa-house-user" style="font-size: 3rem; color: #2c3e50;"></i>
        </div>
        <h2>Vítejte doma!</h2>
        <p>Pro vstup se prosím přihlaste.</p>

        <form method="POST" action="login.php">
            <div class="form-group">
                <label>Uživatelské jméno</label>
                <input type="text" name="user_input" class="form-control" placeholder="Tvé jméno" required autofocus>
            </div>
            <div class="form-group" style="text-align: left; margin-top: 15px;">
                <label>Heslo</label>
                <div style="position: relative; width: 100%;">
                    <input type="password" name="password" id="password_input" class="form-control" 
                        placeholder="••••••••" required 
                        style="padding-right: 45px; width: 100%; box-sizing: border-box;">
                    
                    <span id="toggle_password" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); cursor: pointer; color: #666; z-index: 10; display: flex; align-items: center;">
                        <i class="fas fa-eye" id="eye_icon"></i>
                    </span>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">
                <i class="fas fa-sign-in-alt"></i> Přihlásit se
            </button>
        </form>
    </div>
</div>

</body>
</html>

<script>
    const togglePassword = document.getElementById('toggle_password');
    const passwordInput = document.getElementById('password_input');
    const eyeIcon = document.getElementById('eye_icon');

    togglePassword.addEventListener('click', function () {
        // Přepnutí typu inputu
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);
        
        // Přepnutí ikonky (eye / eye-slash)
        eyeIcon.classList.toggle('fa-eye');
        eyeIcon.classList.toggle('fa-eye-slash');
    });
</script>