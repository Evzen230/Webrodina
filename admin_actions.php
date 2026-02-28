<?php
session_start();
require "includes/db.php";
require "includes/auth.php";
include "includes/auth_check.php";
checkAuth();

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    die("Nepovolený přístup.");
}

$action = $_REQUEST['action'] ?? '';

// 1. PŘEJMENOVÁNÍ
if ($action == 'rename' && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = (int)$_POST['album_id'];
    $new_name = trim($_POST['new_name']);
    
    if (!empty($new_name)) {
        $stmt = $conn->prepare("UPDATE albums SET name = ? WHERE id = ?");
        $stmt->execute([$new_name, $id]);
    }
}

// 2. MAZÁNÍ
if ($action == 'delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    // Nejdřív smažeme záznamy o fotkách v databázi (fyzické soubory by se měly mazat taky, ale pro začátek stačí DB)
    $stmt = $conn->prepare("DELETE FROM media WHERE album_id = ?");
    $stmt->execute([$id]);
    
    // Pak smažeme album
    $stmt = $conn->prepare("DELETE FROM albums WHERE id = ?");
    $stmt->execute([$id]);
}
// 3. PŘIDÁNÍ UŽIVATELE
$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 1. Získání dat a vyčištění (POZOR: jmenuje se to v HTML 'username' nebo 'user'?)
    $username = isset($_POST['username']) ? trim($_POST['username']) : "";
    $password = isset($_POST['password']) ? $_POST['password'] : "";

    // 2. Ladící výpis (pokud to nefunguje, odkomentuj řádek níže a uvidíš, co PHP přijalo)
    // die("Přijaté jméno: '$username'"); 

    if (empty($username)) {
        $error = "Musíš vyplnit uživatelské jméno!";
    } elseif (strlen($password) < 4) {
        $error = "Heslo musí mít aspoň 4 znaky!";
    } else {
        // 3. Kontrola duplicity
        $check = $conn->prepare("SELECT id FROM users WHERE user = ?");
        $check->execute([$username]);
        
        if ($check->fetch()) {
            $error = "Uživatel '$username' už existuje!";
        } else {
            // 4. Samotné vložení
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (user, password) VALUES (?, ?)");
            
            if ($stmt->execute([$username, $hashed_password])) {
                $success = "Uživatel '$username' byl vytvořen!";
            } else {
                $error = "Chyba při zápisu do databáze.";
            }
        }
    }
}

// 4. SMAZÁNÍ UŽIVATELE
if ($action == 'delete_user' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    // Admin sám sebe smazat nesmí (prevence zamknutí webu)
    if ($id != $_SESSION['user_id']) {
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$id]);
    }
}

header("Location: admin.php");
exit();