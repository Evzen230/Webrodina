<?php
require "includes/db.php";
require "includes/auth.php";
include "includes/auth_check.php";
checkAuth();
$user_id = isset($_GET['id']) ? (int)$_GET['id'] : $_SESSION['user_id'];

// KOMPLEXNÍ SQL PRO PROFIL A BADGES
$sql = "SELECT 
            u.id, u.user, u.bio, u.avatar,
            (SELECT COUNT(*) FROM media WHERE user_id = u.id) AS total_photos, 
            (SELECT COUNT(*) FROM media WHERE user_id = u.id AND HOUR(created_at) BETWEEN 0 AND 4) AS night_photos,
            (SELECT COUNT(*) FROM kronika WHERE user_id = u.id) AS logbook_entries,
            (SELECT COUNT(*) FROM likes WHERE user_id = u.id) AS likes_given,
            (SELECT COUNT(*) FROM comments WHERE user_id = u.id) AS comments_given,
            (SELECT COUNT(*) FROM likes l JOIN media m ON l.media_id = m.id WHERE m.user_id = u.id) AS total_likes_received
        FROM users u 
        WHERE u.id = ?";

$stmt = $conn->prepare($sql);
$stmt->execute([$user_id]);
$profileUser = $stmt->fetch();


if (!$profileUser) die("Uživatel nenalezen.");



// Funkce pro rank
function getRankData($count) {
    if ($count > 250) return ['class' => 'gold',   'icon' => 'fa-crown',      'title' => 'Legenda'];
    if ($count > 100) return ['class' => 'purple', 'icon' => 'fa-award',      'title' => 'Profík'];
    if ($count > 40)  return ['class' => 'red',    'icon' => 'fa-bolt',       'title' => 'Paparazzi'];
    if ($count > 15)  return ['class' => 'blue',   'icon' => 'fa-binoculars', 'title' => 'Lovec'];
    if ($count > 5)   return ['class' => 'green',  'icon' => 'fa-camera',     'title' => 'Turista'];
    return                   ['class' => 'gray',   'icon' => 'fa-baby',       'title' => 'Nováček'];
}

$rank = getRankData($profileUser['total_photos']);
// Získání ID z URL, pokud chybí, použijeme ID přihlášeného uživatele
$user_id = isset($_GET['id']) ? (int)$_GET['id'] : $_SESSION['user_id'];

// 1. Info o uživateli (přidány sloupce bio a avatar)

if (!$profileUser) {
    die("Uživatel nenalezen.");
}

// Ošetření BIO (aby nebyl Warning, když je prázdné)
$userBio = $profileUser['bio'] ?? 'O tomto uživateli zatím nic nevíme.';

// 2. Fotky roztříděné do alb
$stmtPhotos = $conn->prepare("
    SELECT a.name AS album_name, a.id AS album_id, m.filename 
    FROM media m
    JOIN albums a ON m.album_id = a.id
    WHERE m.user_id = ?
    ORDER BY a.name ASC
");
$stmtPhotos->execute([$user_id]);
$photosByAlbum = $stmtPhotos->fetchAll(PDO::FETCH_GROUP);

// VÝPOČET STATISTIKY FOTEK
$countPhotos = 0;
if ($photosByAlbum) {
    foreach ($photosByAlbum as $album) {
        $countPhotos += count($album);
    }
}

// 3. Zápisy v kronice
$stmtKronika = $conn->prepare("SELECT * FROM kronika WHERE user_id = ? ORDER BY event_date DESC");
$stmtKronika->execute([$user_id]);
$kronikaEntries = $stmtKronika->fetchAll();

// VÝPOČET STATISTIKY KRONIKY
$countKronika = count($kronikaEntries);

$isMyProfile = ($profileUser['id'] == $_SESSION['user_id']);

$avatarPath = !empty($profileUser['avatar']) 
    ? 'uploads/avatars/' . $profileUser['avatar'] 
    : 'https://api.dicebear.com/9.x/thumbs/svg?backgroundType=solid,gradientLinear&seed=' . urlencode($profileUser['user']);
?>

<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <title>Profil: <?= htmlspecialchars($profileUser['user']) ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="css/profile_styl.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fancyapps/ui@5.0/dist/fancybox/fancybox.css" />
    <script src="https://cdn.jsdelivr.net/npm/@fancyapps/ui@5.0/dist/fancybox/fancybox.umd.js"></script>
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
    <?php include "includes/navbar.php"; ?>
    <div class="profile-layout">
    <section class="profile-header-box">
        <div class="header-left">
            <div class="profile-pic container-edit">
                <img src="<?= $avatarPath ?>" id="avatar-img" alt="Avatar">
                
                <?php if ($isMyProfile): ?>
                    <div class="edit-controls">
                        <label for="avatar-upload" class="edit-icon-btn blue">
                            <i class="fas fa-pencil-alt"></i>
                            <input type="file" id="avatar-upload" style="display:none" accept="image/*" onchange="uploadAvatar(this)">
                        </label>

                        <?php if (!empty($profileUser['avatar'])): ?>
                            <button onclick="deleteAvatar()" class="edit-icon-btn red" title="Odebrat fotku">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
            <div class="user-main-info">
                <h1><?= htmlspecialchars($profileUser['user']) ?></h1>
                <div class="member-badges">
                    <?php $rank = getRankData($profileUser['total_photos'] ?? 0); ?>
                    <span class="badge <?= $rank['class'] ?>">
                        <i class="fas <?= $rank['icon'] ?>"></i> <?= $rank['title'] ?>
                    </span>

                    <?php if(($profileUser['night_photos'] ?? 0) >= 5): ?>
                        <span class="badge night"><i class="fas fa-moon"></i> Noční pták</span>
                    <?php endif; ?>

                    <?php if(($profileUser['total_likes_received'] ?? 0) > ($profileUser['total_photos'] ?? 0) && $profileUser['total_photos'] > 0): ?>
                        <span class="badge heart"><i class="fas fa-grin-hearts"></i> Srdcař</span>
                    <?php endif; ?>

                    <?php if(($profileUser['logbook_entries'] ?? 0) > 10): ?>
                        <span class="badge book"><i class="fas fa-book"></i> Kronikář</span>
                    <?php endif; ?>

                    <?php if(($profileUser['comments_given'] ?? 0) >= 20): ?>
                        <span class="badge orange"><i class="fas fa-comments"></i> Kritik</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="header-right">
            <div class="bio-box">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <label>Bio</label>
                    <?php if ($isMyProfile): ?>
                        <button onclick="toggleBioEdit()" class="edit-pencil-small"><i class="fas fa-pencil-alt"></i></button>
                    <?php endif; ?>
                </div>
                
                <p id="bio-text"><?= nl2br(htmlspecialchars($userBio)) ?></p>
                
                <?php if ($isMyProfile): ?>
                    <div id="bio-edit-area" style="display:none;">
                        <textarea id="bio-input" class="edit-textarea"><?= htmlspecialchars($profileUser['bio'] ?? '') ?></textarea>
                        <button onclick="saveBio()" class="btn-save-small">Uložit</button>
                    </div>
                <?php endif; ?>
            </div>
            <div class="stats-box">
                <label>Statistiky</label>
                <div class="stats-grid">
                    <div class="stat-item"><strong><?= $countPhotos ?></strong><span>Fotek</span></div>
                    <div class="stat-item"><strong><?= $countKronika ?></strong><span>Zápisů</span></div>
                </div>
            </div>
        </div>
    </section>

    <h2 class="section-title">Alba</h2>

<section class="profile-content-grid">
    <aside class="album-sidebar">
        <?php if (empty($photosByAlbum)): ?>
            <p>Žádná alba.</p>
        <?php else: ?>
            <div class="album-nav">
                <?php foreach ($photosByAlbum as $albumName => $photos): ?>
                    <button class="album-tab-btn" onclick="openAlbum(event, '<?= md5($albumName) ?>')">
                        <i class="fas fa-folder"></i>
                        <span class="name"><?= htmlspecialchars($albumName) ?></span>
                        <span class="badge-count"><?= count($photos) ?></span>
                    </button>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </aside>

    <main class="photo-display-area">
        <?php if (!empty($photosByAlbum)): ?>
            <?php foreach ($photosByAlbum as $albumName => $photos): ?>
                <div id="album-<?= md5($albumName) ?>" class="album-content-view">
                    <div class="photo-grid">
                        <?php foreach ($photos as $photo): ?>
                            <div class="photo-item">
                                <a href="uploads/images/<?= $photo['filename'] ?>" 
                                data-fancybox="gallery-<?= md5($albumName) ?>" 
                                data-caption="Album: <?= htmlspecialchars($albumName) ?>">
                                
                                    <img src="uploads/images/<?= $photo['filename'] ?>" loading="lazy" alt="Foto">
                                    
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty-state">Vyberte album ze seznamu vlevo.</div>
        <?php endif; ?>
    </main>
</section>
</div>
</body>
<?php include "includes/footer.php"; ?>
</html>

<script>
    function openAlbum(evt, albumId) {
    // 1. Schovat všechna alba
    var views = document.getElementsByClassName("album-content-view");
    for (var i = 0; i < views.length; i++) {
        views[i].style.display = "none";
        views[i].classList.remove("active");
    }

    // 2. Deaktivovat všechna tlačítka
    var tabs = document.getElementsByClassName("album-tab-btn");
    for (var i = 0; i < tabs.length; i++) {
        tabs[i].classList.remove("active");
    }

    // 3. Zobrazit vybrané album a nastavit tlačítko jako aktivní
    document.getElementById("album-" + albumId).style.display = "block";
    document.getElementById("album-" + albumId).classList.add("active");
    evt.currentTarget.classList.add("active");
}

// Automaticky otevřít první album při načtení
document.addEventListener("DOMContentLoaded", function() {
    var firstTab = document.querySelector(".album-tab-btn");
    if (firstTab) {
        firstTab.click();
    }
});
    function showAlbum(albumId) {
    // Schovat všechna alba
    document.querySelectorAll('.album-view').forEach(view => {
        view.style.display = 'none';
    });
    // Zobrazit vybrané
    document.getElementById('album-' + albumId).style.display = 'block';
    
    // Upravit aktivní styl v sidebaru
    document.querySelectorAll('.album-tab').forEach(tab => {
        tab.classList.remove('active');
    });
    event.currentTarget.classList.add('active');
}

// Při načtení zobrazit první album
document.addEventListener('DOMContentLoaded', () => {
    const firstTab = document.querySelector('.album-tab');
    if (firstTab) firstTab.click();
});

function toggleBioEdit() {
    const text = document.getElementById('bio-text');
    const area = document.getElementById('bio-edit-area');
    if (area.style.display === 'none') {
        area.style.display = 'block';
        text.style.display = 'none';
    } else {
        area.style.display = 'none';
        text.style.display = 'block';
    }
}

async function saveBio() {
    const newBio = document.getElementById('bio-input').value;
    const formData = new FormData();
    formData.append('bio', newBio);

    const response = await fetch('update_profile.php', {
        method: 'POST',
        body: formData
    });

    if (response.ok) {
        location.reload(); // Obnoví stránku s novým biem
    }
}

async function uploadAvatar(input) {
    if (input.files && input.files[0]) {
        const formData = new FormData();
        formData.append('avatar', input.files[0]);

        const response = await fetch('update_profile.php', {
            method: 'POST',
            body: formData
        });

        if (response.ok) {
            location.reload();
        }
    }
}

async function deleteAvatar() {
    if (!confirm("Opravdu chceš smazat svou profilovou fotku?")) return;

    const formData = new FormData();
    formData.append('delete_avatar', 'true');

    const response = await fetch('update_profile.php', {
        method: 'POST',
        body: formData
    });

    if (response.ok) {
        location.reload(); // Stránka se obnoví a naskočí Thumbs
    }
}

// Inicializace Fancyboxu s českým nastavením (volitelné)
Fancybox.bind("[data-fancybox]", {
  // Tady můžeš přidat různé efekty
  Hash: false,
  Thumbs: {
    autoStart: false,
  },
});
</script>