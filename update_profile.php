<?php
require "includes/db.php";
require "includes/auth.php";

// Bezpečný start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Kontrola přihlášení
if (!isset($_SESSION['user_id'])) {
    die("Nepovolený přístup.");
}

$user_id = $_SESSION['user_id'];
$action = $_POST['action'] ?? '';

// --- 1. AKTUALIZACE OBECNÝCH ÚDAJŮ (Jméno, Bio, Avatar) ---
if ($action === 'update_general') {
    $newName = trim($_POST['new_username']);
    $newBio = trim($_POST['new_bio']);

    try {
        $conn->beginTransaction();

        // Aktualizace jména (pokud není prázdné)
        if (!empty($newName)) {
            $stmt = $conn->prepare("UPDATE users SET user = ? WHERE id = ?");
            $stmt->execute([$newName, $user_id]);
            $_SESSION['user'] = $newName; // Refresh jména v navigaci
        }

        // Aktualizace Bio
        $stmt = $conn->prepare("UPDATE users SET bio = ? WHERE id = ?");
        $stmt->execute([$newBio, $user_id]);

        // Zpracování Avatara
        if (!empty($_FILES['avatar']['name']) && $_FILES['avatar']['error'] === 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'webp'];
            $ext = strtolower(pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION));

            if (in_array($ext, $allowed)) {
                $filename = "avatar_" . $user_id . "_" . time() . "." . $ext;
                $uploadDir = "uploads/avatars/";

                if (move_uploaded_file($_FILES['avatar']['tmp_name'], $uploadDir . $filename)) {
                    // Smazání starého souboru z disku
                    $old = $conn->prepare("SELECT avatar FROM users WHERE id = ?");
                    $old->execute([$user_id]);
                    $oldFile = $old->fetchColumn();

                    if ($oldFile && file_exists($uploadDir . $oldFile)) {
                        unlink($uploadDir . $oldFile);
                    }

                    // Zápis nového jména souboru do DB
                    $stmt = $conn->prepare("UPDATE users SET avatar = ? WHERE id = ?");
                    $stmt->execute([$filename, $user_id]);
                }
            }
        }

        $conn->commit();
        header("Location: nastaveni.php?status=success");
    } catch (Exception $e) {
        $conn->rollBack();
        header("Location: nastaveni.php?status=error");
    }
    exit;
}

// --- 2. ZMĚNA HESLA ---
if ($action === 'update_password') {
    $currentPass = $_POST['current_pass'];
    $newPass = $_POST['new_pass'];
    $confirmPass = $_POST['confirm_pass'];

    // Získání aktuálního hashe z DB
    $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();

    // Validace
    if (!$user || !password_verify($currentPass, $user['password'])) {
        header("Location: nastaveni.php?status=wrong_pass");
        exit;
    }

    if ($newPass !== $confirmPass) {
        header("Location: nastaveni.php?status=mismatch");
        exit;
    }

    if (strlen($newPass) < 6) {
        header("Location: nastaveni.php?status=too_short");
        exit;
    }

    // Hashování a uložení
    $newHash = password_hash($newPass, PASSWORD_DEFAULT);
    $update = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
    $update->execute([$newHash, $user_id]);

    header("Location: nastaveni.php?status=password_ok");
    exit;
}
// --- 3. SMAZÁNÍ AVATARU (Přidej do update_profile.php) ---
if ($action === 'delete_avatar') {
    $uploadDir = "uploads/avatars/";
    $stmt = $conn->prepare("SELECT avatar FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $oldFile = $stmt->fetchColumn();

    if ($oldFile && file_exists($uploadDir . $oldFile)) {
        unlink($uploadDir . $oldFile);
    }

    $stmt = $conn->prepare("UPDATE users SET avatar = NULL WHERE id = ?");
    $stmt->execute([$user_id]);
    exit; // U fetch volání stačí skončit bez přesměrování
}
// Pokud někdo přistoupí přímo bez akce
header("Location: nastaveni.php");
exit;   