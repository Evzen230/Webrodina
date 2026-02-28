<?php
require "includes/db.php";
require "includes/auth.php";


if (!isset($_SESSION['user_id'])) die("Nepovolený přístup.");

$user_id = $_SESSION['user_id'];
$action = $_POST['action'] ?? '';

if ($action === 'photos') {
    // 1. MAZÁNÍ VŠECH FOTEK UŽIVATELE
    
    // Získáme seznam souborů k smazání z disku
    $stmt = $conn->prepare("SELECT filename FROM media WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $photos = $stmt->fetchAll(PDO::FETCH_COLUMN);

    foreach ($photos as $file) {
        $filePath = "uploads/images/" . $file;
        if (file_exists($filePath)) {
            unlink($filePath); // Smaže fyzický soubor
        }
    }

    // Smažeme záznamy z databáze
    $delete = $conn->prepare("DELETE FROM media WHERE user_id = ?");
    $delete->execute([$user_id]);

    echo "success";
} 

elseif ($action === 'account') {
    // 2. SMAZÁNÍ CELÉHO ÚČTU
    
    // Nejdřív fotky z disku
    $stmt = $conn->prepare("SELECT filename FROM media WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $photos = $stmt->fetchAll(PDO::FETCH_COLUMN);
    foreach ($photos as $file) {
        if (file_exists("uploads/images/" . $file)) unlink("uploads/images/" . $file);
    }

    // Avatar z disku
    $stmtAv = $conn->prepare("SELECT avatar FROM users WHERE id = ?");
    $stmtAv->execute([$user_id]);
    $avatar = $stmtAv->fetchColumn();
    if ($avatar && file_exists("uploads/avatars/" . $avatar)) {
        unlink("uploads/avatars/" . $avatar);
    }

    // Teď smazat vše z DB (díky tvé struktuře s cizími klíči se smažou i lajky/komenty, pokud máš nastaveno ON DELETE CASCADE)
    // Pokud CASCADE nemáš, musíme smazat tabulky postupně:
    $conn->prepare("DELETE FROM likes WHERE user_id = ?")->execute([$user_id]);
    $conn->prepare("DELETE FROM comments WHERE user_id = ?")->execute([$user_id]);
    $conn->prepare("DELETE FROM media WHERE user_id = ?")->execute([$user_id]);
    $conn->prepare("DELETE FROM kronika WHERE user_id = ?")->execute([$user_id]);
    $conn->prepare("DELETE FROM users WHERE id = ?")->execute([$user_id]);

    $_SESSION = array();
    session_destroy();

    echo "success";
    exit;
}