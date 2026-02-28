<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require "includes/auth.php";
require "includes/db.php";
include "includes/auth_check.php";
checkAuth();

// Získání všech zápisů z kroniky a jména autora
// Seřazeno podle data události od nejnovější po nejstarší
// PŮVODNÍ (CHYBNÝ): SELECT k.*, u.name ...
// OPRAVENÝ:
$query = "
    SELECT k.*, u.user as author 
    FROM kronika k 
    LEFT JOIN users u ON k.user_id = u.id 
    ORDER BY k.event_date DESC
";
$entries = $conn->query($query)->fetchAll();
?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <srcript src="includes/respons.js"></script>
    <title>Rodinná Kronika</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="luminous.css">
    <script src="luminous.js"></script>
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

<div class="container">
    <?php include "includes/navbar.php"; ?>

    <header style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
        <div>
            <h1>✍ Rodinná Kronika</h1>
            <p>Uchováváme naše společné vzpomínky a příběhy.</p>
        </div>
        <a href="add_kronika.php" class="btn" style="background: #3498db; color: white; padding: 12px 20px; text-decoration: none; border-radius: 8px; font-weight: bold;">
            + Přidat nový zápis
        </a>
    </header>

    <?php if (empty($entries)): ?>
        <p style="text-align: center; color: #777; margin-top: 50px;">Kronika je zatím prázdná. Buďte první, kdo do ní něco zapíše!</p>
    <?php else: ?>
        <div class="timeline">
            <?php foreach ($entries as $e): ?>
                <div class="timeline-item">
                    <span class="timeline-date"><?= date("j. n. Y", strtotime($e['event_date'])) ?></span>
                    
                    <div class="timeline-content">
                        <h3><?= htmlspecialchars($e['title']) ?></h3>
                        
                        <?php if (!empty($e['image_path'])): ?>
                            <div class="kronika-photo" style="margin-bottom: 15px;">
                                <a href="uploads/kronika/<?= $e['image_path'] ?>" class="lightbox-trigger">
                                    <img src="uploads/kronika/<?= $e['image_path'] ?>" alt="<?= htmlspecialchars($e['title']) ?>" 
                                         style="max-width: 100%; border-radius: 8px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); cursor: zoom-in;">
                                </a>
                            </div>
                        <?php endif; ?>

                        <p style="white-space: pre-line; line-height: 1.6; color: #444;">
                            <?= formatText($e['content']) ?>
                        </p>
                    </div>

                    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 15px;">
                        <span class="timeline-author">✍ Zapsal/a: <strong><?= htmlspecialchars($e['author']) ?></strong></span>
                        
                        <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1): ?>
                            <div style="margin-top: 10px;">
                                <button onclick="confirmDelete(<?= $e['id'] ?>, '<?= $e['image_path'] ? $e['image_path'] : '' ?>')" 
                                    style="background: none; border: none; color: #e74c3c; cursor: pointer; font-size: 0.85em; padding: 0;">
                                    ❌ Smazat zápis
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
    window.addEventListener('load', function() {
        var images = document.querySelectorAll(".lightbox-trigger");
        if (images.length > 0 && typeof LuminousGallery !== 'undefined') {
            new LuminousGallery(images);
        }
    });
    function confirmDelete(id, hasImage) {
    let url = "delete_kronika.php?id=" + id;
    
    if (hasImage !== '') {
        // Pokud zápis má fotku, dáme vybrat
        if (confirm("Chcete smazat zápis i s PŘIPOJENOU FOTKOU?\n\nOK = Smazat vše\nStorno = Smazat jen text (pokud chcete smazat jen text, napište mi, upravíme skript)")) {
            url += "&delete_file=1";
            window.location.href = url;
        } else {
            // Tady se uživatel rozhodl akci zrušit úplně
            // Pokud bys chtěl mazat jen text a fotku nechat, museli bychom přidat další podmínku
        }
    } else {
        // Zápis nemá fotku, stačí obyčejné potvrzení
        if (confirm("Opravdu chcete smazat tento zápis?")) {
            window.location.href = url;
        }
    }
}
</script>

</body>
<?php include "includes/footer.php"; ?>
</html>