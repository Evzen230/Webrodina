<?php
require "includes/db.php";
require "includes/auth.php";
include "includes/auth_check.php";
checkAuth();

// 1. Celkový počet všeho
$totalStats = $conn->query("
    SELECT 
        (SELECT COUNT(*) FROM media) as total_photos,
        (SELECT COUNT(*) FROM kronika) as total_entries,
        (SELECT COUNT(*) FROM users) as total_members,
        (SELECT COUNT(*) FROM likes) as total_likes
")->fetch();

// 2. Král fotografů (Top 3)
$topPhotographers = $conn->query("
    SELECT u.user, u.avatar, u.id, COUNT(m.id) as count 
    FROM users u 
    LEFT JOIN media m ON u.id = m.user_id 
    GROUP BY u.id 
    ORDER BY count DESC LIMIT 3
")->fetchAll();

// 3. Nejoblíbenější alba (podle počtu fotek)
$topAlbums = $conn->query("
    SELECT a.name, COUNT(m.id) as count 
    FROM albums a 
    JOIN media m ON a.id = m.album_id 
    GROUP BY a.id 
    ORDER BY count DESC LIMIT 5
")->fetchAll();

// 4. Noční tvorba (Kdo nahrává po půlnoci nejvíc)
$nightOwls = $conn->query("
    SELECT u.user, COUNT(m.id) as count 
    FROM users u 
    JOIN media m ON u.id = m.user_id 
    WHERE HOUR(m.created_at) BETWEEN 0 AND 4 
    GROUP BY u.id 
    ORDER BY count DESC LIMIT 1
")->fetch();
?>

<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <title>Rodinné Statistiky</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="css/stat.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

</head>
<body>
    <div class="container">
        <?php include "includes/navbar.php"; ?>
        
        <header style="text-align: center; margin: 40px 0;">
            <h1><i class="fas fa-chart-line"></i> Rodinné statistiky</h1>
            <p>Jak moc žijeme v našem digitálním albu?</p>
        </header>

        <div class="stats-grid-main">
            <div class="stat-card-big">
                <i class="fas fa-images"></i>
                <h2><?= $totalStats['total_photos'] ?></h2>
                <span>Celkový počet fotek</span>
            </div>
            <div class="stat-card-big">
                <i class="fas fa-heart" style="color: #e74c3c;"></i>
                <h2><?= $totalStats['total_likes'] ?></h2>
                <span>Rozdaných srdíček</span>
            </div>
            <div class="stat-card-big">
                <i class="fas fa-book-open" style="color: #9b59b6;"></i>
                <h2><?= $totalStats['total_entries'] ?></h2>
                <span>Zápisů v kronice</span>
            </div>
        </div>

        <div class="hall-of-fame">
            <div class="ranking-box">
                <h3><i class="fas fa-crown" style="color: #f1c40f;"></i> Králové objektivu</h3>
                <?php foreach($topPhotographers as $index => $p): ?>
                    <div class="rank-item">
                        <span class="rank-number"><?= $index + 1 ?>.</span>
                        <span class="rank-name"><?= htmlspecialchars($p['user']) ?></span>
                        <span class="rank-count"><?= $p['count'] ?> fotek</span>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="ranking-box">
                <h3><i class="fas fa-folder-open"></i> Nejobsáhleší alba</h3>
                <?php foreach($topAlbums as $index => $a): ?>
                    <div class="rank-item">
                        <span class="rank-number"><?= $index + 1 ?>.</span>
                        <span class="rank-name"><?= htmlspecialchars($a['name']) ?></span>
                        <span class="rank-count"><?= $a['count'] ?> ks</span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <?php if($nightOwls): ?>
        <div style="margin-top: 30px; background: #2c3e50; color: white; padding: 20px; border-radius: 15px; text-align: center;">
            <i class="fas fa-moon"></i> 
            Aktuálně největší <strong>Noční pták</strong> rodiny je 
            <strong><?= htmlspecialchars($nightOwls['user']) ?></strong> 
            se svými <?= $nightOwls['count'] ?> nočními úlovky!
        </div>
        <?php endif; ?>

    </div>
</body>
<?php include "includes/footer.php"; ?>
</html>