<?php
require "includes/auth.php";
require "includes/db.php";
include "includes/auth_check.php";
checkAuth();
checkAdmin(); // Jen admin může mazat z kroniky


if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    // Nejdříve zjistíme, jestli zápis existuje a jestli má fotku
    $stmt = $conn->prepare("SELECT image_path FROM kronika WHERE id = ?");
    $stmt->execute([$id]);
    $entry = $stmt->fetch();

    if ($entry) {
        // Pokud byla v URL potvrzena žádost o smazání i s fotkou
        // (např. delete_kronika.php?id=10&delete_file=1)
        if (isset($_GET['delete_file']) && $_GET['delete_file'] == '1') {
            if (!empty($entry['image_path'])) {
                $filePath = "uploads/kronika/" . $entry['image_path'];
                if (file_exists($filePath)) {
                    unlink($filePath); // Smaže fyzický soubor z disku
                }
            }
        }

        // Smažeme záznam z databáze
        $deleteStmt = $conn->prepare("DELETE FROM kronika WHERE id = ?");
        $deleteStmt->execute([$id]);
    }
}

header("Location: kronika.php?msg=smazano");
exit();