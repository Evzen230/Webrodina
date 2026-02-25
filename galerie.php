<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require "includes/auth.php";
require "includes/db.php";
include "includes/auth_check.php";
checkAuth();

$album_id = isset($_GET['album_id']) ? (int)$_GET['album_id'] : null;

// --- LOGIKA (Zpracování POST/GET) ---

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_comment'])) {
    $stmt = $conn->prepare("INSERT INTO comments (media_id, user_id, comment_text) VALUES (?, ?, ?)");
    $stmt->execute([$_POST['media_id'], $_SESSION['user_id'], $_POST['comment_text']]);
    header("Location: galerie.php?album_id=" . $album_id . "#photo-" . $_POST['media_id']);
    exit();
}

if (isset($_GET['like_media'])) {
    $stmt = $conn->prepare("INSERT IGNORE INTO likes (user_id, media_id) VALUES (?, ?)");
    $stmt->execute([$_SESSION['user_id'], $_GET['like_media']]);
    header("Location: galerie.php?album_id=" . $album_id . "#photo-" . $_GET['like_media']);
    exit();
}

if (isset($_GET['delete_media']) && $_SESSION['is_admin'] == 1) {
    $stmt = $conn->prepare("SELECT filename, type FROM media WHERE id = ?");
    $stmt->execute([$_GET['delete_media']]);
    $file = $stmt->fetch();
    if ($file) {
        $path = ($file['type'] == 'video' ? 'uploads/videos/' : 'uploads/images/') . $file['filename'];
        if (file_exists($path)) unlink($path);
        $stmt = $conn->prepare("DELETE FROM media WHERE id = ?");
        $stmt->execute([$_GET['delete_media']]);
    }
    header("Location: galerie.php?album_id=" . $album_id);
    exit();
}
?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <srcript src="includes/respons.js"></script>
    <title>Galerie | Rodinný web</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/luminous-lightbox/2.4.0/luminous-basic.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/luminous-lightbox/2.4.0/luminous.js"></script>
    <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">
</head>
<!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-GGN9Y19FYQ"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'G-GGN9Y19FYQ');
</script>
<body class="sticky-nav-body">

<?php include "includes/navbar.php"; ?>

<div class="container main-content">

    <?php if (!$album_id): ?>
        <header class="gallery-header">
            <div class="header-text">
                <h1>Rodinná Galerie</h1>
                <p>Prozkoumejte naše společné vzpomínky roztříděné do alb.</p>
            </div>
            <div class="header-actions">
                <a href="upload.php" class="btn btn-primary"><i class="fas fa-plus"></i> Nahrát fotky</a>
            </div>
        </header>

        <hr class="separator">

        <?php
        $query = "SELECT a.*, 
                  (SELECT filename FROM media WHERE album_id = a.id AND type = 'image' ORDER BY created_at DESC LIMIT 1) as cover_photo 
                  FROM albums a ORDER BY a.name ASC";
        $albums = $conn->query($query)->fetchAll();
        ?>

        <div class="album-grid">
            <?php foreach ($albums as $a): ?>
                <div class="album-card">
                    <a href="galerie.php?album_id=<?= $a['id'] ?>">
                        <div class="album-preview">
                            <?php if ($a['cover_photo']): ?>
                                <img src="uploads/images/<?= $a['cover_photo'] ?>" class="album-img">
                            <?php else: ?>
                                <div class="album-empty"><i class="fas fa-folder-open"></i></div>
                            <?php endif; ?>
                        </div>
                        <div class="album-info">
                            <strong><?= htmlspecialchars($a["name"]) ?></strong>
                            <span>Otevřít album <i class="fas fa-chevron-right"></i></span>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>

    <?php else: ?>
        <?php
        $stmt = $conn->prepare("SELECT name FROM albums WHERE id = ?");
        $stmt->execute([$album_id]);
        $album_name = $stmt->fetchColumn();

        // SQL: Opraveno u.name na u.user a sjednoceno řazení
        $stmt = $conn->prepare("SELECT m.*, u.user as author FROM media m JOIN users u ON m.user_id = u.id WHERE m.album_id = ? ORDER BY m.created_at DESC");
        $stmt->execute([$album_id]);
        $media = $stmt->fetchAll();
        ?>

        <header class="gallery-header">
            <div class="header-text">
                <a href="galerie.php" class="back-link"><i class="fas fa-arrow-left"></i> Zpět na alba</a>
                <h1><?= htmlspecialchars($album_name) ?></h1>
            </div>
            <div class="header-actions">
                <a href="upload.php?album_id=<?= $album_id ?>" class="btn btn-primary"><i class="fas fa-upload"></i> Přidat do alba</a>
            </div>
        </header>

        <hr class="separator">

        <div class="media-grid">
            <?php foreach ($media as $item): ?>
                <div class="media-card" id="photo-<?= $item['id'] ?>">
                    <div class="media-display">
                        <?php if ($item["type"] == "image"): ?>
                            <a href="uploads/images/<?= $item["filename"] ?>" class="lightbox-trigger">
                                <img src="uploads/images/<?= $item["filename"] ?>" alt="foto">
                            </a>
                        <?php else: ?>
                            <video controls preload="metadata">
                                <source src="uploads/videos/<?= $item["filename"] ?>">
                                Váš prohlížeč nepodporuje přehrávání videa.
                            </video>
                        <?php endif; ?>
                    </div>

                    <div class="media-content">
                        <div class="media-desc">
                            <p><?= htmlspecialchars($item["description"]) ?></p>
                            <small><i class="fas fa-user"></i> <?= htmlspecialchars($item["author"]) ?></small>
                        </div>
                        <div class="media-tags" style="margin-top: 8px;">
                            <?php
                            $tag_stmt = $conn->prepare("
                                SELECT t.name FROM tags t 
                                JOIN media_tags mt ON t.id = mt.tag_id 
                                WHERE mt.media_id = ?
                            ");
                            $tag_stmt->execute([$item['id']]);
                            $item_tags = $tag_stmt->fetchAll();
                            
                            foreach ($item_tags as $t): ?>
                                <span class="tag-badge">#<?= htmlspecialchars($t['name']) ?></span>
                            <?php endforeach; ?>
                        </div>
                        <div class="media-interactions">
                            <a href="?album_id=<?= $album_id ?>&like_media=<?= $item['id'] ?>" class="like-btn">
                                ❤️ <span><?php
                                    $likes = $conn->prepare("SELECT COUNT(*) FROM likes WHERE media_id = ?");
                                    $likes->execute([$item['id']]);
                                    echo $likes->fetchColumn();
                                ?></span>
                            </a>
                            <?php if ($_SESSION["is_admin"] == 1): ?>
                                <a href="?album_id=<?= $album_id ?>&delete_media=<?= $item['id'] ?>" class="delete-btn" onclick="return confirm('Smazat?')">
                                    <i class="fas fa-trash"></i>
                                </a>
                            <?php endif; ?>
                        </div>

                        <div class="comments-section">
                            <?php
                            $comm_stmt = $conn->prepare("SELECT c.*, u.user FROM comments c JOIN users u ON c.user_id = u.id WHERE c.media_id = ? ORDER BY c.created_at ASC");
                            $comm_stmt->execute([$item['id']]);
                            $comments = $comm_stmt->fetchAll();
                            foreach ($comments as $c): ?>
                                <div class="comment">
                                    <strong><?= htmlspecialchars($c['user']) ?>:</strong> <?= htmlspecialchars($c['comment_text']) ?>
                                </div>
                            <?php endforeach; ?>

                            <form method="POST" class="comment-form">
                                <input type="hidden" name="media_id" value="<?= $item['id'] ?>">
                                <input type="text" name="comment_text" placeholder="Napiš komentář..." required>
                                <button type="submit" name="add_comment"><i class="fas fa-paper-plane"></i></button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
    // Inicializace Lightboxu po načtení
    document.addEventListener('DOMContentLoaded', function() {
        if (window.location.hash) {
            const targetId = window.location.hash.substring(1);
            const element = document.getElementById(targetId);
            if (element) {
                element.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }

        var luminousElements = document.querySelectorAll(".lightbox-trigger");
        if (luminousElements.length > 0) {
            new LuminousGallery(luminousElements);
        }
    });
</script>

</body>
<?php include "includes/footer.php"; ?>
</html>