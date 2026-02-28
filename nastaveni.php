<?php
require "includes/db.php";
require "includes/auth.php";
include "includes/auth_check.php";
checkAuth();

$user_id = $_SESSION['user_id'];
$message = "";

// Získání aktuálních dat uživatele
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$u = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];

    if (isset($_POST['update_general'])) {
        $newName = trim($_POST['new_username']);
        $newBio = trim($_POST['new_bio']);
        $success = true;

        // 1. Změna jména (pokud není prázdné)
        if (!empty($newName)) {
            $update = $conn->prepare("UPDATE users SET user = ? WHERE id = ?");
            $update->execute([$newName, $user_id]);
            $_SESSION['user'] = $newName; // Aktualizujeme i session, aby se jméno změnilo v menu
        }

        // 2. Změna Bia
        $updateBio = $conn->prepare("UPDATE users SET bio = ? WHERE id = ?");
        $updateBio->execute([$newBio, $user_id]);

        // 3. Změna Avataru (pokud byl vybrán soubor)
        if (!empty($_FILES['avatar']['name'])) {
            $ext = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
            $filename = "avatar_" . $user_id . "_" . time() . "." . $ext;
            $path = "uploads/avatars/" . $filename;

            if (move_uploaded_file($_FILES['avatar']['tmp_name'], $path)) {
                // Smazat starý, pokud existuje
                if ($u['avatar'] && file_exists("uploads/avatars/" . $u['avatar'])) {
                    unlink("uploads/avatars/" . $u['avatar']);
                }
                $updateAv = $conn->prepare("UPDATE users SET avatar = ? WHERE id = ?");
                $updateAv->execute([$filename, $user_id]);
            }
        }

        $message = "Profil byl úspěšně aktualizován.";
        // Znovu načteme data uživatele, aby se v polích zobrazily nové hodnoty
        $stmt->execute([$user_id]);
        $u = $stmt->fetch();
    }
}
?>

<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <title>Nastavení účtu</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="css/nastav.css">
    <link rel="stylesheet" href="css/style2.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

</head>
<body>
    <div class="container">
        <?php include "includes/navbar.php"; ?>
        <?php if (isset($_GET['status'])): ?>
    <div class="status-alert" id="status-alert">
        <?php
        $status = $_GET['status'];
        switch ($status) {
            case 'success': echo '<div class="alert success"><i class="fas fa-check-circle"></i> Profil byl úspěšně aktualizován!</div>'; break;
            case 'password_ok': echo '<div class="alert success"><i class="fas fa-key"></i> Heslo bylo úspěšně změněno.</div>'; break;
            case 'wrong_pass': echo '<div class="alert error"><i class="fas fa-exclamation-triangle"></i> Původní heslo není správné.</div>'; break;
            case 'mismatch': echo '<div class="alert error"><i class="fas fa-times-circle"></i> Nová hesla se neshodují!</div>'; break;
            case 'too_short': echo '<div class="alert error"><i class="fas fa-info-circle"></i> Heslo musí mít alespoň 6 znaků.</div>'; break;
            case 'error': echo '<div class="alert error"><i class="fas fa-bug"></i> Došlo k chybě při ukládání.</div>'; break;
        }
        ?>
    </div>
    <script>
        // Automaticky schová zprávu po 5 sekundách
        setTimeout(() => { 
            const alert = document.getElementById('status-alert');
            if(alert) alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 600);
        }, 5000);
    </script>
<?php endif; ?> 
        <?php if($message): ?>
            <div class="alert" style="padding: 15px; background: #d4edda; color: #155724; border-radius: 8px; margin-bottom: 20px;">
                <?= $message ?>
            </div>
        <?php endif; ?>

        <div class="settings-container">
            <nav class="settings-nav">
                <div class="nav-item active" onclick="openTab(event, 'general')">
                    <i class="fas fa-user-edit"></i> Obecné
                </div>
                <div class="nav-item" onclick="openTab(event, 'security')">
                    <i class="fas fa-lock"></i> Bezpečnost
                </div>
                <div class="nav-item" onclick="openTab(event, 'content')">
                    <i class="fas fa-server"></i> Můj obsah
                </div>
            </nav>
                <div id="general" class="tab-content active">
                    <h2>Obecné informace</h2>
                    <div class="form-group avatar-settings-section">
                        <label>Profilová fotografie</label>
                        <div class="avatar-preview-container">
                            <div class="preview-box">
                                <span>Současná</span>
                                <img src="<?= !empty($u['avatar']) ? 'uploads/avatars/'.$u['avatar'] : 'https://api.dicebear.com/7.x/thumbs/svg?seed='.urlencode($u['user']) ?>" class="avatar-main">
                            </div>

                            <div class="preview-arrow">
                                <i class="fas fa-arrow-right"></i>
                            </div>

                            <div class="preview-box">
                                <span>Nová</span>
                                <div id="new-avatar-wrapper">
                                    <img id="img-preview" src="img/placeholder-avatar.png" class="avatar-main" style="opacity: 0.5;">
                                </div>
                            </div>
                        </div>
                        
                        <input type="file" name="avatar" id="avatar-input" accept="image/*" style="display: none;">
                        <button type="button" class="btn-secondary" onclick="document.getElementById('avatar-input').click()">
                            Vybrat novou fotku
                        </button>
                    </div>
                    <form action="update_profile.php" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="update_general">
                        
                        <input type="text" name="new_username" value="<?= htmlspecialchars($u['user']) ?>">
                        <textarea name="new_bio"><?= htmlspecialchars($u['bio'] ?? '') ?></textarea>
                        
                        <button type="submit" class="btn-save">Uložit změny</button>
                    </form>
                </div>
                <div id="security" class="tab-content">
                    <h2>Změna hesla</h2>
                    <form action="update_profile.php" method="POST">
                        <input type="hidden" name="action" value="update_password">
                        
                            <div class="form-group password-wrapper">
                                <label>Současné heslo</label>
                                <div style="position: relative;">
                                    <input type="password" name="current_pass" id="current_pass" autocomplete="current-password" placeholder="Současné heslo">
                                    <i class="fas fa-eye toggle-password" onclick="togglePassword(event, 'current_pass')"></i>
                                </div>
                            </div>

                            <div class="form-group password-wrapper">
                                <label>Nové heslo</label>
                                <div style="position: relative;">
                                    <input type="password" name="new_pass" id="new_pass" autocomplete="new-password" required>
                                    <i class="fas fa-eye toggle-password" onclick="togglePassword(event, 'new_pass')"></i>
                                </div>
                            </div>

                            <div class="form-group password-wrapper">
                                <label>Potvrďte nové heslo</label>
                                <div style="position: relative;">
                                    <input type="password" name="confirm_pass" id="confirm_pass" autocomplete="new-password" placeholder="Potvrďte nové heslo">
                                    <i class="fas fa-eye toggle-password" onclick="togglePassword(event, 'confirm_pass')"></i>
                                </div>
                            </div>
                        
                        <button type="submit" class="btn-save">Změnit heslo</button>
                    </form>
                </div>

                <div id="content" class="tab-content">
                    <h2 style="color: #e74c3c;">Nebezpečná zóna</h2>
                    <p>Zde můžete spravovat svá data. Tyto akce jsou nevratné.</p>
                    <div style="border: 1px solid #ffcccc; padding: 20px; border-radius: 10px;">
                        <h4>Smazat všechny mé fotografie</h4>
                        <p>Tímto odstraníte všechny nahrané soubory z galerie.</p>
                        <button class="btn-danger" onclick="confirmDelete('photos')">Smazat fotky</button>
                        
                        <hr style="margin: 20px 0; border: 0; border-top: 1px solid #eee;">
                        
                        <h4>Zrušit celý účet</h4>
                        <button class="btn-danger" onclick="confirmDelete('account')">Smazat účet</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

<script>
window.addEventListener('load', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const status = urlParams.get('status');

    // Seznam statusů, které patří ke kartě Bezpečnost
    const securityStatuses = ['password_ok', 'wrong_pass', 'mismatch', 'too_short'];

    if (securityStatuses.includes(status)) {
        // Najdeme tlačítko navigace pro Bezpečnost (druhá položka)
        const securityTab = document.querySelector('.nav-item:nth-child(2)');
        if (securityTab) {
            securityTab.click();
        }
    }
});
        function openTab(evt, tabName) {
            var i, tabcontent, tablinks;
            tabcontent = document.getElementsByClassName("tab-content");
            for (i = 0; i < tabcontent.length; i++) { tabcontent[i].style.display = "none"; }
            tablinks = document.getElementsByClassName("nav-item");
            for (i = 0; i < tablinks.length; i++) { tablinks[i].classList.remove("active"); }
            document.getElementById(tabName).style.display = "block";
            evt.currentTarget.classList.add("active");
        }

async function confirmDelete(type) {
    let msg = type === 'photos' ? "Smazat všechny fotky?" : "Smazat celý účet a odhlásit?";
    if (!confirm(msg)) return;

    const formData = new FormData();
    formData.append('action', type);

    const response = await fetch('delete_handler.php', {
        method: 'POST',
        body: formData
    });
    
    const result = await response.text();

    if (result.trim() === 'success') {
        if (type === 'account') {
            // Totální odhlášení - pošleme ho na login nebo index
            window.location.href = 'index.php'; 
        } else {
            // Jen refresh nastavení
            window.location.href = 'nastaveni.php?status=success';
        }
    } else {
        alert("Chyba ze serveru: " + result);
    }
}
        function previewImage(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('preview-avatar').src = e.target.result;
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
function togglePassword(event, inputId) {
    const input = document.getElementById(inputId);
    const icon = event.target; // Vezmeme ikonu, na kterou se kliklo
    
    if (input && input.type === "password") {
        input.type = "text";
        icon.classList.remove("fa-eye");
        icon.classList.add("fa-eye-slash");
    } else if (input) {
        input.type = "password";
        icon.classList.remove("fa-eye-slash");
        icon.classList.add("fa-eye");
    }
}   
document.getElementById('avatar-input').onchange = function (evt) {
    const [file] = this.files;
    if (file) {
        const preview = document.getElementById('img-preview');
        preview.src = URL.createObjectURL(file);
        preview.style.opacity = "1"; // Zrušíme průhlednost placeholderu
        
        // Přidáme jemný efekt záře na nový náhled
        preview.style.boxShadow = "0 0 15px rgba(52, 152, 219, 0.5)";
    }
}
</script>
</body>
</html>