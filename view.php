<?php
require "includes/db.php";

$token = $_GET['token'] ?? '';

if (empty($token)) {
    die("Chybí přístupový klíč.");
}

// 1. Ověření tokenu a získání informací o albu
$stmt = $conn->prepare("
    SELECT albums.id, albums.name 
    FROM shared_links 
    JOIN albums ON shared_links.album_id = albums.id 
    WHERE shared_links.token = ? 
    AND (shared_links.expires_at IS NULL OR shared_links.expires_at > NOW())
");
$stmt->execute([$token]);
$album = $stmt->fetch();

if (!$album) {
    die("Tento odkaz již neexistuje nebo je neplatný.");
}

// 2. Načtení fotek z tohoto alba
$stmt = $conn->prepare("SELECT * FROM media WHERE album_id = ? ORDER BY id DESC");
$stmt->execute([$album['id']]);
$photos = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <title>Sdílené album: <?= htmlspecialchars($album['name']) ?></title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fancyapps/ui@5.0/dist/fancybox/fancybox.css"/>
    <style>
        /* Speciální úprava pro hosty: vycentrování a čistota */
        .guest-header {
            text-align: center;
            padding: 40px 20px;
            background: #f9f9f9;
            margin-bottom: 30px;
            border-bottom: 1px solid #eee;
        }
        .guest-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
    </style>
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
<body>

<div class="guest-header">
    <h1><i class="fas fa-images"></i> <?= htmlspecialchars($album['name']) ?></h1>
    <p>Prohlížíte si sdílené rodinné album</p>
</div>

<div class="guest-container">
    <div class="media-grid">
        <?php foreach ($photos as $item): ?>
            <div class="media-card">
                <a href="uploads/images/<?= $item['filename'] ?>" 
                   data-fancybox="gallery" 
                   data-caption="<?= htmlspecialchars($item['description'] ?? '') ?>">
                    <img src="uploads/images/<?= $item['filename'] ?>" alt="Foto" loading="lazy">
                </a>
                
                <?php if (!empty($item['description'])): ?>
                    <div class="media-info">
                        <p class="description"><?= htmlspecialchars($item['description']) ?></p>
                    </div>
                <?php endif; ?>

                <div class="media-interactions">
                    <span class="like-btn" style="cursor: default;">
                        <i class="fas fa-heart"></i> 
                        <span class="like-count">
                            <?php
                                $likes = $conn->prepare("SELECT COUNT(*) FROM likes WHERE media_id = ?");
                                $likes->execute([$item['id']]);
                                echo $likes->fetchColumn();
                            ?>
                        </span>
                    </span>
                    </div>
            </div>
        <?php endforeach; ?>
    </div>

    <?php if (empty($photos)): ?>
        <p style="text-align:center; color: #666;">V tomto albu zatím nejsou žádné fotky.</p>
    <?php endif; ?>
</div>

<footer style="text-align:center; padding: 40px; color: #aaa; font-size: 0.8rem;">
    &copy; <?= date("Y") ?> Rodinný Archiv | Vytvořeno pomocí tajného odkazu
</footer>

<script src="https://cdn.jsdelivr.net/npm/@fancyapps/ui@5.0/dist/fancybox/fancybox.umd.js"></script>
<script>
    Fancybox.bind("[data-fancybox]", {
        // Tady můžeš nechat slideshow nebo download, aby si hosté mohli fotky stáhnout
        Toolbar: {
            display: {
                left: ["infobar"],
                right: ["zoom", "download", "close"],
            },
        },
    });
</script>

</body>
</html>