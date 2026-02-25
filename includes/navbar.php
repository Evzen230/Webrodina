<link rel="stylesheet" href="/style.css">

<nav class="navbar">
    <button class="burger-menu" id="burgerBtn">
        <span></span>
        <span></span>
        <span></span>
    </button>

    <div class="nav-menu" id="navMenu">
        <div class="nav-links">
            <a href="index.php">Domů</a>
            <a href="galerie.php">🖼 Galerie</a>
            <a href="kronika.php">✍ Kronika</a>
            <?php if ($_SESSION["is_admin"] == 1): ?>
                <a href="admin.php" style="color: #e74c3c;">⚙ Admin</a>
            <?php endif; ?>
        </div>

        <div class="search-container">
            <form action="search.php" method="GET" class="search-form">
                <input type="text" id="search-input" name="q" placeholder="Hledat..." autocomplete="off">
                <button type="submit" class="btn">🔍</button>
            </form>
            <div id="search-suggestions" class="suggestions-box"></div>
        </div>

        <div class="nav-links">
            <a href="profile.php"><i class="fas fa-cog"></i> Nastavení</a>
            <a href="logout.php">Odhlásit</a>
        </div>
    </div>
</nav>



<script src="/includes/respons.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. BURGER MENU LOGIKA
    const burgerBtn = document.getElementById('burgerBtn');
    const navMenu = document.getElementById('navMenu'); // Zkontroluj, zda máš v HTML id="navMenu"

    if (burgerBtn && navMenu) {
        burgerBtn.addEventListener('click', function() {
            navMenu.classList.toggle('active');
        });
    } else {
        console.error("Chyba: Burger tlačítko nebo Nav menu nebylo v HTML nalezeno!");
    }

    // 2. NAŠEPTÁVAČ (Jen jedna deklarace!)
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