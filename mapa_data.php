<?php
// Vypneme vypisování chyb do výstupu, aby nám nerozbily JSON
ini_set('display_errors', 0); 
error_reporting(E_ALL);

require "includes/db.php";

// Nastavíme hlavičku na JSON
header('Content-Type: application/json');

try {
    // Vybereme jen fotky, které mají souřadnice
    $query = "SELECT id, album_id, lat, lng, filename FROM media WHERE lat IS NOT NULL AND lng IS NOT NULL AND lat != 0";
    $stmt = $conn->query($query);
    $locations = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Vyčistíme případný buffer (smaže mezery před <?php)
    if (ob_get_length()) ob_clean();

    echo json_encode($locations);
} catch (Exception $e) {
    // Pokud je chyba v DB, pošleme ji jako JSON, ne jako text
    echo json_encode(['error' => $e->getMessage()]);
}
exit;