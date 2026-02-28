<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require "includes/auth.php";
require "includes/db.php";
include "includes/auth_check.php";
checkAuth();

// --- ZPRACOVÁNÍ AJAX GENEROVÁNÍ ODKAZU ---
if (isset($_GET['ajax_generate_link']) && isset($_GET['album_id'])) {
    header('Content-Type: application/json');
    $album_id = (int)$_GET['album_id'];
    $val = filter_var($_GET['exp_val'], FILTER_VALIDATE_INT);
    $unit = $_GET['exp_unit'] ?? 'never';
    $token = bin2hex(random_bytes(16));
    $expires_at = null;

    if ($unit !== 'never') {
        if ($val === false || $val <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'Zadejte prosím celé kladné číslo.']);
            exit;
        }
        $allowed_units = ['hours', 'days', 'months'];
        if (in_array($unit, $allowed_units)) {
            $expires_at = date('Y-m-d H:i:s', strtotime("+{$val} {$unit}"));
        }
    }

    $stmt = $conn->prepare("INSERT INTO shared_links (album_id, token, expires_at) VALUES (?, ?, ?)");
    if ($stmt->execute([$album_id, $token, $expires_at])) {
        $share_url = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/view.php?token=" . $token;
        echo json_encode(['status' => 'success', 'url' => $share_url]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'DB error']);
    }
    exit;
}

$album_id = isset($_GET['album_id']) ? (int)$_GET['album_id'] : null;

// --- LOGIKA (Komentáře, Líbí se, Mazání) ---
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

// Upravené mazání: Smaže soubor i z disku (Admin nebo Vlastník)
if (isset($_GET['delete_media'])) {
    $media_id = (int)$_GET['delete_media'];
    $stmt = $conn->prepare("SELECT filename, type, user_id FROM media WHERE id = ?");
    $stmt->execute([$media_id]);
    $file = $stmt->fetch();

    if ($file && ($_SESSION['is_admin'] == 1 || $file['user_id'] == $_SESSION['user_id'])) {
        $path = ($file['type'] == 'video' ? 'uploads/videos/' : 'uploads/images/') . $file['filename'];
        if (file_exists($path)) unlink($path);
        $conn->prepare("DELETE FROM media WHERE id = ?")->execute([$media_id]);
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
    <title>Galerie | Rodinný web</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="css/gal_style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fancyapps/ui@5.0/dist/fancybox/fancybox.css" />
    <script src="https://cdn.jsdelivr.net/npm/@fancyapps/ui@5.0/dist/fancybox/fancybox.umd.js"></script>
    <style>
        /* Styl pro metadata popup */
        .metadata-modal .modal-content { max-width: 600px; text-align: left; }
        .raw-exif { background: #1a1a1a; color: #ffffff; padding: 15px; border-radius: 5px; font-family: monospace; font-size: 12px; overflow-x: auto; max-height: 300px; }
        .info-overlay-btn { position: absolute; top: 10px; left: 10px; z-index: 10; background: rgba(0,0,0,0.5); color: #fff; border: none; padding: 5px 10px; border-radius: 20px; cursor: pointer; transition: 0.3s; }
        .info-overlay-btn:hover { background: #3498db; }
        .media-display { position: relative; }
    </style>
</head>
<body class="sticky-nav-body">

<?php include "includes/navbar.php"; ?>

<div class="container main-content">

    <?php if (!$album_id): ?>
        <header class="gallery-header">
            <div class="header-text">
                <h1>Rodinná Galerie</h1>
                <p>Prozkoumej naše fotky zorganizované do alb.</p>
            </div>
            <div class="header-actions">
                <a href="upload.php" class="btn btn-primary"><i class="fas fa-plus"></i> Nahrát fotky</a>
            </div>
        </header>

        <hr class="separator">

        <?php
        $query = "SELECT a.*, (SELECT filename FROM media WHERE album_id = a.id AND type = 'image' ORDER BY created_at DESC LIMIT 1) as cover_photo FROM albums a ORDER BY a.name ASC";
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

        // SQL: Přidána metadata do selectu
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
                <button type="button" class="btn btn-share" onclick="openShareModal()"><i class="fas fa-share-nodes"></i> Sdílet album</button>
            </div>
        </header>

        <div class="media-grid">
            <?php foreach ($media as $item): ?>
                <div class="media-card" id="photo-<?= $item['id'] ?>">
                    
                    <div class="media-display">
                        <button class="info-overlay-btn" onclick='showExif(<?= json_encode($item) ?>)'>
                            <i class="fas fa-info-circle"></i>
                        </button>

                        <?php if ($item["type"] == "image"): ?>
                            <a href="uploads/images/<?= $item['filename'] ?>" data-fancybox="gallery" data-caption="<?= htmlspecialchars($item['description'] ?? '') ?>">
                                <img src="uploads/images/<?= $item['filename'] ?>" loading="lazy" />
                            </a>
                        <?php else: ?>
                            <video controls preload="metadata"><source src="uploads/videos/<?= $item["filename"] ?>"></video>
                        <?php endif; ?>
                    </div>

                    <div class="media-content">
                        <div class="media-desc">
                            <p><?= htmlspecialchars($item["description"] ?? '') ?></p>
                            
                            <div class="tech-info-small" style="font-size: 0.75rem; color: #777; margin-bottom: 5px;">
                                <?php if($item['taken_at']): ?>
                                    <span><i class="far fa-calendar-alt"></i> <?= date("j.n.Y", strtotime($item['taken_at'])) ?></span>
                                <?php endif; ?>
                                <?php if($item['device_make_model']): ?>
                                    <span style="margin-left: 10px;"><i class="fas fa-camera"></i> <?= htmlspecialchars($item['device_make_model']) ?></span>
                                <?php endif; ?>
                            </div>

                            <small>
                                <i class="fas fa-user"></i> 
                                <a href="profil.php?id=<?= $item['user_id'] ?>" style="text-decoration: none; color: inherit;">
                                    <?= htmlspecialchars($item["author"] ?? 'Neznámý') ?>
                                </a>
                            </small>
                        </div>

                        <div class="media-tags">
                            <?php
                            $tag_stmt = $conn->prepare("SELECT t.name FROM tags t JOIN media_tags mt ON t.id = mt.tag_id WHERE mt.media_id = ?");
                            $tag_stmt->execute([$item['id']]);
                            foreach ($tag_stmt->fetchAll() as $t): ?>
                                <span class="tag-badge">#<?= htmlspecialchars($t['name']) ?></span>
                            <?php endforeach; ?>
                        </div>

                        <div class="media-interactions">
                            <a href="?album_id=<?= $album_id ?>&like_media=<?= $item['id'] ?>" class="like-btn">
                                <i class="fas fa-heart"></i> 
                                <span class="like-count">
                                    <?php
                                    $likes = $conn->prepare("SELECT COUNT(*) FROM likes WHERE media_id = ?");
                                    $likes->execute([$item['id']]);
                                    echo $likes->fetchColumn();
                                    ?>
                                </span>
                            </a>

                            <?php if ($_SESSION["is_admin"] == 1 || $item['user_id'] == $_SESSION['user_id']): ?>
                                <a href="?album_id=<?= $album_id ?>&delete_media=<?= $item['id'] ?>" class="delete-btn" onclick="return confirm('Smazat tuto fotku?')">
                                    <i class="fas fa-trash-alt"></i>
                                </a>
                            <?php endif; ?>
                        </div>

                        <div class="comments-section">
                            <?php
                            $comm_stmt = $conn->prepare("SELECT c.*, u.user FROM comments c JOIN users u ON c.user_id = u.id WHERE c.media_id = ? ORDER BY c.created_at ASC");
                            $comm_stmt->execute([$item['id']]);
                            foreach ($comm_stmt->fetchAll() as $c): ?>
                                <div class="comment"><strong><?= htmlspecialchars($c['user']) ?>:</strong> <?= htmlspecialchars($c['comment_text']) ?></div>
                            <?php endforeach; ?>

                            <form method="POST" class="comment-form">
                                <input type="hidden" name="media_id" value="<?= $item['id'] ?>">
                                <input type="text" name="comment_text" placeholder="Komentář..." required>
                                <button type="submit" name="add_comment"><i class="fas fa-paper-plane"></i></button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<div id="shareModal" class="modal-overlay" style="display:none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-link"></i> Sdílení pro hosty</h3>
            <button class="close-modal" onclick="closeShareModal()">&times;</button>
        </div>
        <div class="modal-body">
            <p>Platnost odkazu:</p>
            <div class="expiry-settings">
                <input type="number" id="expiry-value" value="1" min="1">
                <select id="expiry-unit" onchange="toggleExpiryInput()">
                    <option value="hours">Hodin</option>
                    <option value="days" selected>Dnů</option>
                    <option value="months">Měsíců</option>
                    <option value="never">Navždy</option>
                </select>
            </div>
            <button class="btn-generate-full" onclick="generateShareLink(<?= $album_id ?>)">Vytvořit odkaz</button>
            <div id="result-section" style="display:none; margin-top: 15px;">
                <input type="text" id="share-link-input" readonly style="width: 80%; padding: 5px;">
                <button onclick="copyShareLink()" class="btn-copy-icon"><i class="fas fa-copy"></i></button>
            </div>
        </div>
    </div>
</div>

<div id="exifModal" class="modal-overlay" style="display:none;">
    <div class="modal-content metadata-modal">
        <div class="modal-header">
            <h3><i class="fas fa-camera-retro"></i> Technická data (EXIF)</h3>
            <button class="close-modal" onclick="document.getElementById('exifModal').style.display='none'">&times;</button>
        </div>
        <div class="modal-body" id="exif-content">
            </div>
    </div>
</div>

<script>
// Fancybox inicializace
Fancybox.bind("[data-fancybox]", {});

// Zobrazení EXIF Metadat
function showExif(item) {
    const modal = document.getElementById('exifModal');
    const content = document.getElementById('exif-content');
    
    let rawExif = "Žádná metadata k dispozici.";
    try {
        if(item.exif_raw) {
            const parsed = JSON.parse(item.exif_raw);
            rawExif = JSON.stringify(parsed, null, 2);
        }
    } catch(e) { console.error("JSON Error", e); }

    let gpsLink = item.lat ? `<a href="https://www.google.com/maps?q=${item.lat},${item.lng}" target="_blank" class="btn btn-sm btn-primary" style="font-size: 12px; padding: 2px 8px; text-decoration: none; color: white; border-radius: 3px;"><i class="fas fa-map-marker-alt"></i> Ukázat na mapě</a>` : "Není k dispozici";

    content.innerHTML = `
        <table style="width: 100%; border-collapse: collapse; margin-bottom: 15px;">
            <tr><td><strong>Datum pořízení:</strong></td><td>${item.taken_at || 'Neznámo'}</td></tr>
            <tr><td><strong>Zařízení:</strong></td><td>${item.device_make_model || 'Neznámo'}</td></tr>
            <tr><td><strong>Lokace (GPS):</strong></td><td>${gpsLink}</td></tr>
            <tr><td><strong>Souřadnice:</strong></td><td>${item.lat ? item.lat + ', ' + item.lng : 'Není'}</td></tr>
        </table>
        <label><strong>Metadata (JSON):</strong></label>
        <div class="raw-exif"><pre>${rawExif}</pre></div>
    `;
    modal.style.display = 'flex';
}

// Funkce pro Sdílení (Zkrácené pro přehlednost)
function openShareModal() { document.getElementById('shareModal').style.display = 'flex'; }
function closeShareModal() { document.getElementById('shareModal').style.display = 'none'; }
function toggleExpiryInput() {
    const unit = document.getElementById('expiry-unit').value;
    document.getElementById('expiry-value').style.visibility = (unit === 'never') ? 'hidden' : 'visible';
}

function generateShareLink(albumId) {
    const val = document.getElementById('expiry-value').value;
    const unit = document.getElementById('expiry-unit').value;
    fetch(`galerie.php?ajax_generate_link=1&album_id=${albumId}&exp_val=${val}&exp_unit=${unit}`)
        .then(r => r.json())
        .then(data => {
            if (data.status === 'success') {
                document.getElementById('share-link-input').value = data.url;
                document.getElementById('result-section').style.display = 'block';
            }
        });
}

function copyShareLink() {
    const input = document.getElementById('share-link-input');
    input.select();
    navigator.clipboard.writeText(input.value);
    alert("Odkaz zkopírován!");
}

window.onclick = function(event) {
    if (event.target.classList.contains('modal-overlay')) {
        event.target.style.display = 'none';
    }
}
</script>

</body>
<?php include "includes/footer.php"; ?>
</html>