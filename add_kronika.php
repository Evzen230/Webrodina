<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once "includes/auth.php";
require_once "includes/db.php";
include_once "includes/auth_check.php"; // STŘEDNÍK OPRAVEN
checkAuth();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST['title'] ?? '';
    $content = $_POST['content'] ?? '';
    $event_date = $_POST['event_date'] ?? date('Y-m-d');
    $user_id = $_SESSION['user_id'];
    $image_name = null;

    // Zpracování fotky, pokud byla nahrána
    if (!empty($_FILES['image']['tmp_name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        
        // Vytvořit složku, pokud neexistuje (pojistka)
        if (!is_dir('uploads/kronika/')) {
            mkdir('uploads/kronika/', 0777, true);
        }

        $image_name = uniqid() . ".jpg";
        $target = "uploads/kronika/" . $image_name;
        
        list($w, $h) = getimagesize($_FILES['image']['tmp_name']);
        
        // Dynamický výpočet šířky (aby se nezvětšovaly malé fotky)
        $new_w = ($w > 1000) ? 1000 : $w;
        $new_h = ($h / $w) * $new_w;
        
        $source = imagecreatefromstring(file_get_contents($_FILES['image']['tmp_name']));
        $tmp = imagecreatetruecolor($new_w, $new_h);
        
        // Zachování průhlednosti (pro jistotu, kdyby někdo nahrál PNG)
        imagealphablending($tmp, false);
        imagesavealpha($tmp, true);
        
        imagecopyresampled($tmp, $source, 0, 0, 0, 0, $new_w, $new_h, $w, $h);
        imagejpeg($tmp, $target, 80); 

        imagedestroy($source);
        imagedestroy($tmp);
    }

    $stmt = $conn->prepare("INSERT INTO kronika (title, content, event_date, user_id, image_path) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$title, $content, $event_date, $user_id, $image_name]);

    header("Location: kronika.php");
    exit();
}
?>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
</head>
<!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-GGN9Y19FYQ"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'G-GGN9Y19FYQ');
</script>

<div class="container">
    <?php include "includes/navbar.php"; ?>
<div class="container main-content">
    <header class="gallery-header">
        <h1><i class="fas fa-pen-nib"></i> Nový zápis do kroniky</h1>
    </header>

    <div class="form-container kronika-form-card">
        <form method="POST">
            <div class="form-group">
                <label>Název události</label>
                <input type="text" name="title" class="form-control form-control-lg" placeholder="Jak se to jmenovalo?" required>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label>Datum události</label>
                    <input type="date" name="event_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                </div>
                <div class="form-group">
                    <label>Lokalita</label>
                    <input type="text" name="location" class="form-control" placeholder="Kde to bylo?">
                </div>
            </div>

            <div class="form-group">
                <label>Příběh / Vyprávění</label>
                <textarea name="content" class="form-control" rows="10" placeholder="Co se tenkrát stalo..." required></textarea>
            </div>

            <div class="header-actions" style="justify-content: flex-end;">
                <a href="kronika.php" class="btn btn-secondary">Zrušit</a>
                <button type="submit" name="submit_kronika" class="btn btn-primary">
                    <i class="fas fa-save"></i> Uložit do kroniky
                </button>
            </div>
        </form>
    </div>
</div>
</div>