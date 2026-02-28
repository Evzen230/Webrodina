<?php
require "includes/auth.php";
require "includes/db.php";
include "includes/auth_check.php";
checkAuth();

$raw_q = isset($_GET['q']) ? trim($_GET['q']) : "";
$q = "%" . $raw_q . "%";

if ($raw_q != "") {
    // Vynucujeme COLLATE utf8mb4_unicode_ci u všech textových polí, 
    // aby se předešlo chybě "Illegal mix of collations" při operaci UNION.
    $stmt = $conn->prepare("
        (SELECT name COLLATE utf8mb4_unicode_ci as title, 'album' as type, id as target_id 
         FROM albums 
         WHERE name LIKE ?)
        UNION
        (SELECT description COLLATE utf8mb4_unicode_ci as title, 'photo' as type, album_id as target_id 
         FROM media 
         WHERE description LIKE ? AND description != '')
        UNION
        (SELECT name COLLATE utf8mb4_unicode_ci as title, 'tag' as type, id as target_id 
         FROM tags 
         WHERE name LIKE ?)
        UNION
        (SELECT user COLLATE utf8mb4_unicode_ci as title, 'member' as type, id as target_id 
         FROM users 
         WHERE user LIKE ?)
        LIMIT 10
    ");
    
    // Musíme poslat $q čtyřikrát (pro alba, fotky, tagy a členy)
    $stmt->execute([$q, $q, $q, $q]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($results)) {
        echo "<div class='suggestion-item' style='color: #888;'>Nic nenalezeno...</div>";
    }

    foreach ($results as $row) {
        switch ($row['type']) {
            case 'tag':
                $link = "search.php?q=%23" . urlencode($row['title']);
                $icon = "🏷️";
                $label = "Tag: " . $row['title'];
                break;
            case 'photo':
                $link = "galerie.php?album_id=" . $row['target_id'];
                $icon = "🖼️";
                $label = $row['title'];
                break;
            case 'member':
                $link = "profil.php?id=" . $row['target_id'];
                $icon = "👤";
                $label = "Člen: " . $row['title'];
                break;
            case 'album':
            default:
                $link = "galerie.php?album_id=" . $row['target_id'];
                $icon = "📁";
                $label = "Album: " . $row['title'];
                break;
        }
        
        // Výpis odkazu s třídou pro CSS, kterou jsme ladili minule
        echo "<a href='" . htmlspecialchars($link) . "' class='suggestion-item' style='text-decoration: none; display: block;'>";
        echo "<span>$icon " . htmlspecialchars($label) . "</span>";
        echo "</a>";
    }
}
?>