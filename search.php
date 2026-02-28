<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <srcript src="includes/respons.js"></script>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="css/style2.css">
    <title>Výsledky vyhledávání</title>
</head>
<!-- Google tag (gtag.js) 
<script async src="https://www.googletagmanager.com/gtag/js?id=G-GGN9Y19FYQ"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'G-GGN9Y19FYQ');
</script>
-->
<?php
require "includes/auth.php";
require "includes/db.php";
include "includes/auth_check.php";
checkAuth();

$raw_q = trim($_GET['q'] ?? '');
$search_param = "%" . ltrim($raw_q, '#') . "%";

// 1. HLEDÁNÍ FOTEK (Hledá v popisku NEBO v názvu štítku)
// Použijeme LEFT JOIN, aby se fotka ukázala, i když má shodu jen v popisku
$stmt = $conn->prepare("
    SELECT DISTINCT m.* FROM media m
    LEFT JOIN media_tags mt ON m.id = mt.media_id
    LEFT JOIN tags t ON mt.tag_id = t.id
    WHERE m.description LIKE ? 
    OR t.name LIKE ?
    ORDER BY m.id DESC
");
$stmt->execute([$search_param, $search_param]);
$results_media = $stmt->fetchAll();

// 2. HLEDÁNÍ ALB
$stmt = $conn->prepare("SELECT * FROM albums WHERE name LIKE ? ORDER BY name ASC");
$stmt->execute([$search_param]);
$results_albums = $stmt->fetchAll();
?>

<?php include "includes/navbar.php"; ?>

<div class="container">
    <h2>Výsledky pro: <?= htmlspecialchars($raw_q) ?></h2>

    <?php if (!empty($results_albums)): ?>
        <h3>Nalezená alba</h3>
        <div class="album-grid" style="display: flex; gap: 15px; margin-bottom: 30px;">
            <?php foreach($results_albums as $album): ?>
                <a href="galerie.php?album_id=<?= $album['id'] ?>" class="album-card" style="padding: 15px; background: #fff; border-radius: 8px; border: 1px solid #ddd; text-decoration: none; color: #333;">
                   📁 <?= htmlspecialchars($album['name']) ?>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <h3>Fotky a videa</h3>
    <div class="media-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 20px;">
        <?php if (!empty($results_media)): ?>
            <?php foreach($results_media as $item): ?>
                <div class="media-card" style="background: #fff; padding: 10px; border-radius: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
                    <a href="galerie.php?album_id=<?= $item['album_id'] ?>#photo-<?= $item['id'] ?>">
                        <img src="uploads/images/<?= $item['filename'] ?>" style="width: 100%; height: 200px; object-fit: cover; border-radius: 5px;">
                    </a>
                    <div style="margin-top: 10px;">
                        <p style="margin: 0; font-weight: bold;"><?= htmlspecialchars($item['description']) ?></p>
                        
                        <div class="media-tags" style="margin-top: 5px;">
                            <?php
                            $st_tags = $conn->prepare("SELECT t.name FROM tags t JOIN media_tags mt ON t.id = mt.tag_id WHERE mt.media_id = ?");
                            $st_tags->execute([$item['id']]);
                            foreach ($st_tags->fetchAll() as $t): ?>
                                <span style="font-size: 0.7rem; background: #eef; padding: 2px 6px; border-radius: 10px; color: #55a;">#<?= htmlspecialchars($t['name']) ?></span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>Nebylo nic nalezeno.</p>
        <?php endif; ?>
    </div>
</div>

<?php include "includes/footer.php"; ?>