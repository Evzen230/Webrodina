<?php
require "includes/db.php";

$token = $_GET['token'] ?? '';
$isValid = false;

// 1. Validace tokenu
if (!empty($token)) {
    $stmt = $conn->prepare("SELECT * FROM registration_tokens WHERE token = ? AND is_used = 0 AND expires_at > NOW()");
    $stmt->execute([$token]);
    $tokenData = $stmt->fetch();

    if ($tokenData) {
        $isValid = true;
    }
}

// 2. Zpracování registrace
if ($isValid && isset($_POST['register_user'])) {
    $user = $_POST['user'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Vložení uživatele
    $stmt = $conn->prepare("INSERT INTO users (user, password) VALUES (?, ?)");
    if ($stmt->execute([$user, $password])) {
        // OZNAČENÍ TOKENU JAKO POUŽITÉHO (Klíčový krok!)
        $update = $conn->prepare("UPDATE registration_tokens SET is_used = 1 WHERE token = ?");
        $update->execute([$token]);
        
        die("Registrace úspěšná! Nyní se můžete přihlásit.");
    }
}
?>
<head>
    <meta charset="UTF-8">
    <srcript src="includes/respons.js"></script>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="css/style2.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <title>Registrace|Web Rodina</title>
</head>

<div class="container">
    <?php if (!$isValid): ?>
        <div class="info-callout error">
            <i class="fas fa-times-circle"></i>
            <div>
                <strong>Neplatný nebo expirovaný odkaz.</strong>
                Požádejte administrátora o novou pozvánku.
            </div>
        </div>
    <?php else: ?>
        <h2>Dokončení registrace</h2>
        <form method="POST">
            <input type="text" name="user" placeholder="Uživatelské jméno" required class="form-control"><br>
            <div class="password-wrapper">
                <input type="password" name="password" id="password" placeholder="Heslo" required class="form-control">
                <i class="fas fa-eye toggle-password" id="togglePasswordIcon"></i>
            </div>
            <div class="consent-wrapper" style="margin: 15px 0; text-align: left;">
                <label class="checkbox-container">
                    <input type="checkbox" name="privacy_agree" required>
                    <span class="checkmark"></span>
                    Souhlasím se <a href="zasady.php" target="_blank">Zásadami ochrany údajů</a> a <a href="podminky.php" target="_blank">podmínkami.</a>
                </label>
            </div>
            <button type="submit" name="register_user" class="btn btn-success">Vytvořit účet</button>
        </form>
    <?php endif; ?>
</div>
<script>
document.getElementById('togglePasswordIcon').addEventListener('click', function () {
    const passwordInput = document.getElementById('password');
    
    // Přepnutí typu inputu
    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
    passwordInput.setAttribute('type', type);
    
    // Přepnutí ikony (oko / přeškrtnuté oko)
    this.classList.toggle('fa-eye');
    this.classList.toggle('fa-eye-slash');
});
</script>