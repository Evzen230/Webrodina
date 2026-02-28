
<?php
require "includes/auth.php";
require "includes/db.php";
include "includes/auth_check.php";
checkAuth();

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_album'])) {
    $stmt = $conn->prepare("INSERT INTO albums (name, description) VALUES (?, ?)");
    $stmt->execute([$_POST["name"], $_POST["description"]]);
    header("Location: alba.php");
}

$albums = $conn->query("SELECT * FROM albums ORDER BY name ASC")->fetchAll();
include "includes/auth_check.php"
?>
<div class="container">
        <?php include "includes/navbar.php"; ?> 
</div>
<h2>Moje Alba (Složky)</h2>
<form method="POST">
    <input type="text" name="name" placeholder="Název nové složky" required>
    <input type="text" name="description" placeholder="Stručný popis">
    <button type="submit" name="add_album">Vytvořit složku</button>
</form>

<hr>
<div style="display: flex; gap: 20px; flex-wrap: wrap;">
    <?php foreach ($albums as $a): ?>
        <div style="border: 2px solid #edb458; padding: 20px; border-radius: 10px; background: #fff9f0; width: 150px; text-align: center;">
            <a href="galerie.php?album_id=<?= $a['id'] ?>" style="text-decoration: none; color: #333;">
                <span style="font-size: 50px;">📁</span><br>
                <strong><?= htmlspecialchars($a["name"]) ?></strong>
            </a>
        </div>
    <?php endforeach; ?>
</div>
<br><a href="index.php">Zpět domů</a>