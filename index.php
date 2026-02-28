<?php
require "includes/auth.php";
require "includes/db.php";
include "includes/auth_check.php";
checkAuth();

// Načteme 3 nejnovější alba pro sekci "Nová alba"
$latest_albums = $conn->query("SELECT * FROM albums ORDER BY id DESC LIMIT 3")->fetchAll();

// Načteme poslední zápis z kroniky
$latest_kronika = $conn->query("SELECT * FROM kronika ORDER BY event_date DESC LIMIT 1")->fetch();
?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <srcript src="includes/respons.js"></script>
    <title>Web rodina</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/devlog_style.css">
</head>
<body>
<?php include "includes/navbar.php"; ?>
<?php
// --- NASTAVENÍ VERZE ---
$current_version = "2.0"; 
// Pokud cookie neexistuje nebo má jinou verzi, zobrazíme box
$show_update = !isset($_COOKIE['app_version']) || $_COOKIE['app_version'] !== $current_version;
?>

<?php if ($show_update): ?>
<?php if ($show_update): ?>
<div id="version-log-overlay" class="log-overlay">
    <div id="version-log-box" class="version-log-popup">
        <div class="version-log-header">
            <h4><i class="fas fa-rocket"></i> Co je nového? (v<?= $current_version ?>)</h4>
            <button onclick="closeVersionLog('<?= $current_version ?>')" class="close-log-btn">&times;</button>
        </div>
        <div class="version-log-body">
    <div class="version-grid">
        <div class="grid-item label"><strong>🖼️ Zlepšení <br>galerie</strong></div>
        <div class="grid-item desc">Byla zlepšená funkce na prohlížení obrázků.</div>

        <div class="grid-item label"><strong>🔗 Sdílení</strong></div>
        <div class="grid-item desc">Byla přidaná možnost na sdílení s lidmi, který na web nemají přístup.</div>

        <div class="grid-item label"><strong>📥 Vylepšní <br> nahrávání</strong></div>
        <div class="grid-item desc">Byl zlepšen systém nahrávání.</div>
        
        <div class="grid-item label"><strong>👤 Nová karta <br> členové </strong></div>
        <div class="grid-item desc">Byla přidána stránka se všemi členy webu a jejich statistikama.</div>

        <div class="grid-item label"><strong>📅 Přidaný <br> kalendář</strong></div>
        <div class="grid-item desc">Byl přidán kalendář, kam se dají zapisovat nejrůznější akce.</div>

        <div class="grid-item label"><strong>📕 Zlepšený zápis <br> do kroniky </strong></div>
        <div class="grid-item desc">Byl zlepšen zápis do kroniky, kde lze nyní použít styli markdown.</div>

        <div class="grid-item label"><strong>ℹ️Více informací</strong></div>
        <div class="grid-item desc"><a target="blank" href="https://docs.google.com/document/d/15NRwHlDD9V2s0ZWCOWK747VPvQiscQ9EuaafZcCgeLY/edit?usp=sharing">Dev log (Google docs)</a></div>

    
    </div>

    <div style="text-align: right; margin-top: 30px;">
        <button onclick="closeVersionLog('<?= $current_version ?>')" class="btn-confirm">Rozumím, díky!</button>
    </div>
</div>
    </div>
</div>

<script>
function closeVersionLog(version) {
    const d = new Date();
    d.setTime(d.getTime() + (365*24*60*60*1000));
    document.cookie = "app_version=" + version + ";expires="+ d.toUTCString() + ";path=/";
    
    // Skryjeme celé překrytí
    document.getElementById('version-log-overlay').style.display = 'none';
}
</script>
<?php endif; ?>

<?php endif; ?>
<div class="container">
    <header class="hero">
        <h1>Ahoj, <?= htmlspecialchars($_SESSION['user']?? 'Host') ?>! 👋</h1>
        <p>Vítej zpět na našem rodinném webu.</p>
    </header>

    <section class="action-grid">
        <a href="galerie.php" class="action-card">
            <div class="icon-circle"><i class="fas fa-images"></i></div>
            <h3>Galerie</h3>
            <p>Prohlížet společné fotky a videa</p>
        </a>
        <a href="upload.php" class="action-card">
            <div class="icon-circle"><i class="fas fa-cloud-upload-alt"></i></div>
            <h3>Nahrát</h3>
            <p>Přidat nové vzpomínky do alb</p>
        </a>
        <a href="kronika.php" class="action-card">
            <div class="icon-circle"><i class="fas fa-book-open"></i></div>
            <h3>Kronika</h3>
            <p>Zapsat nebo číst rodinnou kroniku</p>
        </a>
        <a href="kalendar.php" class="action-card">
            <span class="new-feature-badge">Novinka</span>
            <div class="icon-circle"><i class="fa-solid fa-calendar"></i></div>
            <h3>Kalendář</h3>
            <p>Rodinný kalendář</p>
        </a>
        <a href="clenove.php" class="action-card">
            <span class="new-feature-badge">Novinka</span>
            <div class="icon-circle"><i class="fa-solid fa-circle-user"></i></div>
            <h3>Členové</h3>
            <p>Členové webu a jejich statistiky</p>
        </a>
        <a href="map.php" class="action-card">
            <span class="new-feature-badge">Novinka</span>
            <div class="icon-circle"><i class="fa-solid fa-map"></i></div>
            <h3>Mapa</h3>
            <p>Mapa toho, kde se fotilo.</p>
        </a>
        <a href="statistiky.php" class="action-card">
            <span class="new-feature-badge">Novinka</span>
            <div class="icon-circle"><i class="fa-solid fa-chart-column"></i></div>
            <h3>Statistiky</h3>
            <p>Statistiky celého webu.</p>
        </a>
    </section>

    <section class="home-section">
        <h2><i class="fas fa-star"></i> Nově přidaná alba</h2>
        <div class="album-preview-grid">
            <?php foreach ($latest_albums as $album): ?>
                <a href="galerie.php?album_id=<?= $album['id'] ?>" class="album-mini-card">
                    <div class="album-folder-icon"><i class="fas fa-folder"></i></div>
                    <span><?= htmlspecialchars($album['name']) ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </section>

    <?php if ($latest_kronika): ?>
    <section class="home-section">
        <h2><i class="fas fa-bullhorn"></i> Poslední zápis v kronice</h2>
        <div class="news-box">
            <span class="news-date"><?= date("j. n. Y", strtotime($latest_kronika['event_date'])) ?></span>
            <h3><?= htmlspecialchars($latest_kronika['title']) ?></h3>
            <p><?= mb_strimwidth(htmlspecialchars($latest_kronika['content']), 0, 200, "...") ?></p>
            <a href="kronika.php" class="read-more">Číst dál →</a>
        </div>
    </section>
    <?php endif; ?>
</div>

</body>
<?php include "includes/footer.php"; ?>

</html>
