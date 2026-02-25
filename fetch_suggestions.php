<?php
require "includes/auth.php";
require "includes/db.php";
include "includes/auth_check.php";
checkAuth();

$raw_q = isset($_GET['q']) ? trim($_GET['q']) : "";
$q = "%" . $raw_q . "%";

if ($raw_q != "") {
    $stmt = $conn->prepare("
        (SELECT name as title, 'album' as type, id as target_id FROM albums WHERE name LIKE ?)
        UNION
        (SELECT description as title, 'photo' as type, album_id as target_id FROM media WHERE description LIKE ? AND description != '')
        UNION
        (SELECT DISTINCT t.name as title, 'tag' as type, t.id as target_id 
         FROM tags t 
         JOIN media_tags mt ON t.id = mt.tag_id 
         WHERE t.name LIKE ?)
        LIMIT 8
    ");
    $stmt->execute([$q, $q, $q]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($results as $row) {
        if ($row['type'] == 'tag') {
            // Přesměrování na search.php s mřížkou, aby to search.php správně pochopil
            $link = "search.php?q=%23" . urlencode($row['title']);
            $icon = "🏷️";
        } elseif ($row['type'] == 'photo') {
            // U fotky jdeme do alba, kde ta fotka je
            $link = "galerie.php?album_id=" . $row['target_id'];
            $icon = "🖼️";
        } else {
            // U alba jdeme přímo do alba
            $link = "galerie.php?album_id=" . $row['target_id'];
            $icon = "📁";
        }
        
        echo "<a href='$link' class='suggestion-item'>";
        echo "<span>$icon " . htmlspecialchars($row['title']) . "</span>";
        echo "</a>";
    }
}
?>