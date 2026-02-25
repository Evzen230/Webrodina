<?php
$host = "webrodina.dbaserver.net";
$dbname = "webrodina";
$username = "webrodina";
$password = "WPyG3CNeH5VA";

try {
    // Tady musíme použít PŘESNĚ ty názvy, které jsou nahoře
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    
    // Nastavení vyhazování chyb
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
} catch (PDOException $e) {
    die("Chyba připojení k databázi: " . $e->getMessage());
}
?>