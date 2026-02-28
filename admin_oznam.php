<?php
require "includes/db.php";
require "includes/auth.php";
checkAuth();


// 1. Zpracování formuláře pro přidání/úpravu
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_announcement'])) {
        $message = $_POST['message'];
        $type = $_POST['type'];
        
        // Při přidání nového můžeme stará automaticky vypnout (chceme jen jedno aktivní)
        $conn->query("UPDATE admin_announcements SET is_active = 0");
        
        $stmt = $conn->prepare("INSERT INTO admin_announcements (message, type, is_active) VALUES (?, ?, 1)");
        $stmt->execute([$message, $type]);
    }

    if (isset($_POST['toggle_status'])) {
        $id = $_POST['announce_id'];
        $new_status = $_POST['current_status'] == 1 ? 0 : 1;
        
        // Pokud zapínáme, vypneme ostatní
        if ($new_status == 1) {
            $conn->query("UPDATE admin_announcements SET is_active = 0");
        }
        
        $stmt = $conn->prepare("UPDATE admin_announcements SET is_active = ? WHERE id = ?");
        $stmt->execute([$new_status, $id]);
    }

    if (isset($_POST['delete_announce'])) {
        $stmt = $conn->prepare("DELETE FROM admin_announcements WHERE id = ?");
        $stmt->execute([$_POST['announce_id']]);
    }
    header("Location: admin_oznam.php");
    exit;
}

// 2. Načtení všech oznámení
$announcements = $conn->query("SELECT * FROM admin_announcements ORDER BY created_at DESC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <title>Správa oznámení</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .announce-admin-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin-top: 20px; }
        .announce-card { background: #f9f9f9; padding: 15px; border-radius: 8px; margin-bottom: 10px; border-left: 5px solid #ccc; }
        .announce-card.active { border-left-color: #2ecc71; background: #effaf3; }
        .announce-card.info { border-left-color: #3498db; }
        .announce-card.warning { border-left-color: #e67e22; }
        .actions { margin-top: 10px; display: flex; gap: 5px; }
        textarea { width: 100%; height: 100px; padding: 10px; margin-bottom: 10px; }
        .badge { font-size: 10px; padding: 3px 6px; border-radius: 4px; text-transform: uppercase; }
    </style>
</head>
<body>
    <?php include "includes/navbar.php"; ?>

    <div class="container">
        <h1>📢 Správa globálních oznámení</h1>

        <div class="announce-admin-grid">
            <div class="form-box">
                <h3>Nové oznámení</h3>
                <form method="POST">
                    <textarea name="message" placeholder="Text oznámení (Markdown podporován)..." required></textarea>
                    <div class="form-group">
                        <label>Typ:</label>
                        <select name="type">
                            <option value="info">Info (Modrá)</option>
                            <option value="warning">Varování (Oranžová/Červená)</option>
                            <option value="success">Úspěch (Zelená)</option>
                        </select>
                    </div>
                    <button type="submit" name="add_announcement" class="btn">Vydat a aktivovat</button>
                </form>
            </div>

            <div class="list-box">
                <h3>Historie a stav</h3>
                <?php foreach ($announcements as $a): ?>
                    <div class="announce-card <?= $a['is_active'] ? 'active' : '' ?> <?= $a['type'] ?>">
                        <small><?= date("d.m.H:i", strtotime($a['created_at'])) ?></small>
                        <div><?= formatText($a['message']) ?></div>
                        
                        <div class="actions">
                            <form method="POST">
                                <input type="hidden" name="announce_id" value="<?= $a['id'] ?>">
                                <input type="hidden" name="current_status" value="<?= $a['is_active'] ?>">
                                <button type="submit" name="toggle_status" class="btn-sm">
                                    <?= $a['is_active'] ? 'Vypnout' : 'Zapnout' ?>
                                </button>
                                <button type="submit" name="delete_announce" class="btn-sm btn-danger" onclick="return confirm('Smazat?')">Smazat</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</body>
</html>