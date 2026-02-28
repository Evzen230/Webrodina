<link rel="stylesheet" href="/css/nav_bar.css">
<link rel="stylesheet" href="/css/oznam.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<?php
$today = date('Y-m-d');
$sqlToday = "SELECT title, type FROM calendar_events 
             WHERE ('$today' BETWEEN event_date AND end_date)
             OR (recurrence = 'yearly' AND DATE_FORMAT(event_date, '%m-%d') = DATE_FORMAT('$today', '%m-%d'))
             OR (recurrence = 'monthly' AND DATE_FORMAT(event_date, '%d') = DATE_FORMAT('$today', '%d'))";
$stmtToday = $conn->prepare($sqlToday);
$stmtToday->execute();
$todayEvents = $stmtToday->fetchAll(PDO::FETCH_ASSOC);

$stmtA = $conn->query("SELECT * FROM admin_announcements WHERE is_active = 1 LIMIT 1");
$announce = $stmtA->fetch();
?>
<div class="top-notifications-container">
    <?php if ($announce): 
        $icon = 'info-circle';
        if ($announce['type'] === 'warning') $icon = 'exclamation-triangle';
        if ($announce['type'] === 'success') $icon = 'check-circle';
    ?>
        <div class="announcement-box <?= htmlspecialchars($announce['type']) ?>">
            <i class="fas fa-<?= $icon ?>"></i>
            <div class="announcement-text"><?= formatText($announce['message']) ?></div>
        </div>
    <?php endif; ?>
    <?php if (!empty($todayEvents)): ?>
        <div class="compact-alert">
            <i class="fa-solid fa-calendar"></i>
            <span class="alert-text">
                <?php 
                $evList = array_map(function($ev) {
                    $emojis = [
                        'narozeniny' => '🎂 ',
                        'oslava'     => '🎉 ',
                        'vyroci'     => '💐 ',
                        'sraz'       => '🤝 ', 
                        'jine'       => '📅 '
                    ];
                    
                    $type = mb_strtolower($ev['type']);
                    $emoji = $emojis[$type] ?? '📅 '; 
                    
                    return $emoji . htmlspecialchars($ev['title']);
                }, $todayEvents);
                
                echo implode(', ', $evList);
                ?>
            </span>
        </div>
    <?php endif; ?>
</div>

<nav class="navbar">
    <button class="burger-menu" id="burgerBtn">
        <span></span><span></span><span></span>
    </button>

    <div class="nav-menu" id="navMenu">
        <div class="nav-links main-nav">
            <a href="index.php"><i class="fas fa-home"></i> <span>Domů</span></a>
            <a href="galerie.php"><i class="fas fa-images"></i> <span>Galerie</span></a>
            <a href="kronika.php"><i class="fas fa-book"></i> <span>Kronika</span></a>
            <a href="clenove.php"><i class="fas fa-users"></i> <span>Členové</span></a>
            <a href="kalendar.php"><i class="fa-solid fa-calendar"></i> <span>Kalendář</span></a>
            <a href="map.php"><i class="fa-solid fa-map"></i> <span>Mapa</span></a>
        </div>

        <div class="search-container">
            <form action="search.php" method="GET" class="search-form">
                <input type="text" id="search-input" name="q" placeholder="Hledat..." autocomplete="off">
                <button type="submit" class="btn-search"><i class="fas fa-search"></i></button>
            </form>
            <div id="search-suggestions" class="suggestions-box"></div>
        </div>

        <div class="nav-links user-menu-dropdown">
            <div class="dropdown">
                <button type="button" class="dropdown-toggle" id="userBtn">
                    <i class="fas fa-user-circle"></i> 
                    <span>Můj účet</span>
                </button>

                <div class="dropdown-content" id="userDropdown">
                    <?php if (isset($_SESSION["is_admin"]) && $_SESSION["is_admin"] == 1): ?>
                        <a href="admin.php" class="admin-link"><i class="fas fa-user-shield"></i> Admin Panel</a>
                    <?php endif; ?>
                    <a href="profil.php?id=<?= $_SESSION['user_id'] ?>"><i class="fas fa-user"></i> Můj profil</a>
                    <a href="nastaveni.php"><i class="fas fa-cog"></i> Nastavení</a>
                    <hr>
                    <a href="logout.php" class="logout-link"><i class="fas fa-sign-out-alt"></i> Odhlásit se</a>
                </div>
            </div>
        </div>
    </div>
</nav>

<script src="/includes/respons.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. DROPDOWN LOGIKA (Můj účet)
    const btn = document.getElementById('userBtn');
    const menu = document.getElementById('userDropdown');

    if (btn && menu) {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            menu.classList.toggle('show');
        });
    } else {
        console.error("CHYBA: Prvky userBtn nebo userDropdown nebyly nalezeny!");
    }

    // Zavření dropdownu při kliku kamkoliv jinam
    window.addEventListener('click', function() {
        if (menu && menu.classList.contains('show')) {
            menu.classList.remove('show');
        }
    });

    // 2. BURGER MENU LOGIKA
    const burgerBtn = document.getElementById('burgerBtn');
    const navMenu = document.getElementById('navMenu');

    if (burgerBtn && navMenu) {
        burgerBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            navMenu.classList.toggle('active');
        });
    }

    // 3. NAŠEPTÁVAČ
    const searchInput = document.getElementById('search-input');
    const suggestionsBox = document.getElementById('search-suggestions');

    if (searchInput && suggestionsBox) {
        searchInput.addEventListener('input', function() {
            const query = this.value;
            if (query.length < 2) {
                suggestionsBox.style.display = 'none';
                return;
            }

            fetch('fetch_suggestions.php?q=' + encodeURIComponent(query))
                .then(response => response.text())
                .then(data => {
                    if (data.trim().length > 0) {
                        suggestionsBox.innerHTML = data;
                        suggestionsBox.style.display = 'block';
                    } else {
                        suggestionsBox.style.display = 'none';
                    }
                });
        });

        // Zavřít našeptávač při kliku vedle
        document.addEventListener('click', function(e) {
            if (!searchInput.contains(e.target) && !suggestionsBox.contains(e.target)) {
                suggestionsBox.style.display = 'none';
            }
        });
    }
});
</script>