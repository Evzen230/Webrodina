<?php
// Trik: Zjistíme, jestli běžíme na localhostu nebo na Czechia
if ($_SERVER['HTTP_HOST'] == 'localhost' || $_SERVER['REMOTE_ADDR'] == '127.0.0.1') {
    // NASTAVENÍ PRO LARAGON
    $host = "localhost";
    $dbname = "webrodina"; // Název databáze, kterou jsi vytvořil v Laragonu (přes HeidiSQL)
    $username = "root";
    $password = ""; // V Laragonu je heslo prázdné
} else {
    // NASTAVENÍ PRO CZECHIA (tvůj main web)
    $host = "webrodina.dbaserver.net";
    $dbname = "webrodina";
    $username = "webrodina";
    $password = "WPyG3CNeH5VA";
}

try {
    // Tady musíme použít PŘESNĚ ty názvy, které jsou nahoře
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    
    // Nastavení vyhazování chyb
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
} catch (PDOException $e) {
    die("Chyba připojení k databázi: " . $e->getMessage());
}

require_once __DIR__ . '/Parsedown.php';

function formatText($text) {
    static $parsedown = null;
    if ($parsedown === null) {
        $parsedown = new Parsedown();
        // Volitelně: vypne interpretaci HTML vloženého uživatelem (bezpečnost)
        $parsedown->setSafeMode(true); 
    }
    return $parsedown->text($text);
}
?>