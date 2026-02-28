<?php
require "includes/db.php";
require "includes/auth.php";
include "includes/auth_check.php";
checkAuth(); 

// Rozšířené SQL o noční fotky
// Rozšířené SQL o avatary a statistiky
$sql = "SELECT 
            u.id, 
            u.user, 
            u.avatar,
            (SELECT COUNT(*) FROM media WHERE media.user_id = u.id) AS total_photos, 
            (SELECT COUNT(*) FROM media WHERE media.user_id = u.id AND (HOUR(taken_at) >= 22 OR HOUR(taken_at) < 4)) AS night_photos,
            (SELECT COUNT(*) FROM kronika WHERE kronika.user_id = u.id) AS logbook_entries,
            (SELECT COUNT(*) FROM likes WHERE likes.user_id = u.id) AS likes_given,
            (SELECT COUNT(*) FROM comments WHERE comments.user_id = u.id) AS comments_given,
            (SELECT COUNT(*) FROM likes JOIN media ON likes.media_id = media.id WHERE media.user_id = u.id) AS total_likes_received
        FROM users u
        ORDER BY total_photos DESC";

$stmt = $conn->prepare($sql);
$stmt->execute();
$members = $stmt->fetchAll();


// Funkce pro získání hlavního ranku
function getRankData($count) {
    if ($count > 250) return ['class' => 'gold',   'icon' => 'fa-crown',      'title' => 'Legenda'];
    if ($count > 100) return ['class' => 'purple', 'icon' => 'fa-award',      'title' => 'Profík'];
    if ($count > 40)  return ['class' => 'red',    'icon' => 'fa-bolt',       'title' => 'Paparazzi'];
    if ($count > 15)  return ['class' => 'blue',   'icon' => 'fa-binoculars', 'title' => 'Lovec'];
    if ($count > 5)   return ['class' => 'green',  'icon' => 'fa-camera',     'title' => 'Turista'];
    return                   ['class' => 'gray',   'icon' => 'fa-baby',       'title' => 'Nováček'];
}
?>

<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <title>Rodinný tým</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="css/clen_styl.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Rychlé styly pro nové odznaky, pokud je nemáš v CSS */
        .badge.night { background: #2f3542; color: #ffa502; border: 1px solid #ffa502; }
        .member-badges { display: flex; flex-wrap: wrap; gap: 5px; justify-content: center; min-height: 32px; }
    </style>
</head>
<body>
    <div class="container">
        <?php include "includes/navbar.php"; ?> 
    </div>
    <div class="container">
        <header style="margin-bottom: 30px;">
            <a href="galerie.php" class="back-link"><i class="fas fa-arrow-left"></i> Zpět do galerie</a>
            <h1><i class="fas fa-camera-retro"></i> Naši fotografové</h1>
        </header>

        <div class="member-grid">
            <?php foreach ($members as $user): 
                $rank = getRankData($user['total_photos']); 
                
                // Logika pro profilovku (stejná jako na profilu)
                $avatarPath = !empty($user['avatar']) 
                    ? 'uploads/avatars/' . $user['avatar'] 
                    : 'https://api.dicebear.com/9.x/thumbs/svg?backgroundType=solid,gradientLinear&seed=' . urlencode($user['user']);
            ?>
                <a href="profil.php?id=<?= $user['id'] ?>" class="member-card-link">
                    <div class="member-card">
                        <div class="member-avatar">
                            <img src="<?= $avatarPath ?>" alt="<?= htmlspecialchars($user['user']) ?>" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                        </div>
                        
                        <h3><?= htmlspecialchars($user['user']) ?></h3>
                        
                        <div class="member-badges">
                            <span class="badge <?= $rank['class'] ?>">
                                <i class="fas <?= $rank['icon'] ?>"></i> <?= $rank['title'] ?>
                            </span>

                            <?php if($user['night_photos'] >= 5): ?>
                                <span class="badge night">
                                    <i class="fas fa-moon"></i> Noční pták
                                </span>
                            <?php endif; ?>

                            <?php if($user['total_likes_received'] > $user['total_photos'] && $user['total_photos'] > 0): ?>
                                <span class="badge heart">
                                    <i class="fas fa-grin-hearts"></i> Srdcař
                                </span>
                            <?php endif; ?>

                            <?php if($user['logbook_entries'] >= 10): ?>
                                <span class="badge book">
                                    <i class="fas fa-book"></i> Kronikář
                                </span>
                            <?php endif; ?>
                            <?php if($user['comments_given'] >= 10): ?>
                            <span class="badge orange" title="Napsal už přes 10 komentářů">
                                <i class="fas fa-comments"></i> Kritik
                            </span>
                        <?php endif; ?>

                        <?php if($user['likes_given'] >= 30): ?>
                            <span class="badge teal" title="Rozdal už přes 50 lajků">
                                <i class="fas fa-hand-holding-heart"></i> Sponzor radosti
                            </span>
                        <?php endif; ?>
                        </div>
                        <div class="member-stats">
                            <div class="stat">
                                <strong><?= $user['total_photos'] ?></strong>
                                <span>Fotek</span>
                            </div>
                            <div class="stat">
                                <strong><?= $user['total_likes_received'] ?></strong>
                                <span>Lajků</span>
                            </div>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</body>
<?php include "includes/footer.php"; ?>
</html>