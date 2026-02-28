<?php
require "includes/db.php";
require "includes/auth.php";
include "includes/auth_check.php";
checkAuth();

// Kontrola, zda je uživatel admin
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: index.php");
    exit();
}

// Načtení všech alb a počtu fotek v nich (pro info)
$query = "
    SELECT a.id, a.name, COUNT(m.id) as media_count 
    FROM albums a 
    LEFT JOIN media m ON a.id = m.album_id 
    GROUP BY a.id 
    ORDER BY a.name ASC
";
$albums = $conn->query($query)->fetchAll();

// Statistiky
$stats_query = $conn->query("
    SELECT 
        (SELECT COUNT(*) FROM media) as total_files,
        (SELECT COUNT(*) FROM albums) as total_albums,
        (SELECT COUNT(*) FROM users) as total_users
")->fetch();

// Výpočet velikosti složky uploads (v MB)
function get_dir_size($dir) {
    $size = 0;
    foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir)) as $file) {
        $size += $file->getSize();
    }
    return round($size / 1024 / 1024, 2);
}
$storage_size = get_dir_size('uploads/');
?>

<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <srcript src="includes/respons.js"></script>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Administrace | Web Rodinna</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>

<?php include "includes/navbar.php"; ?>

<a href="admin_devlog.php">
    <p>Zápis do devlogu</p>
</a>
<a href="admin_oznam.php">
    <p>Vytvořit oznámení</p>
</a>
<a href="reg_link.php">
    <p>Vytvořit link na registraci</p>
</a>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px;">
    <div class="admin-card" style="padding: 20px; text-align: center; border-bottom: 4px solid #3498db;">
        <i class="fas fa-images" style="font-size: 2rem; color: #3498db;"></i>
        <h3><?= $stats_query['total_files'] ?></h3>
        <p>Souborů celkem</p>
    </div>
    <div class="admin-card" style="padding: 20px; text-align: center; border-bottom: 4px solid #2ecc71;">
        <i class="fas fa-hdd" style="font-size: 2rem; color: #2ecc71;"></i>
        <h3><?= $storage_size ?> MB</h3>
        <p>Využité místo</p>
    </div>
    <div class="admin-card" style="padding: 20px; text-align: center; border-bottom: 4px solid #f1c40f;">
        <i class="fas fa-users" style="font-size: 2rem; color: #f1c40f;"></i>
        <h3><?= $stats_query['total_users'] ?></h3>
        <p>Členů rodiny</p>
    </div>
</div>

<?php
$users_list = $conn->query("SELECT id, user, is_admin FROM users ORDER BY user ASC")->fetchAll();
?>

<header class="gallery-header" style="margin-top: 50px;">
    <h1><i class="fas fa-users-cog"></i> Správa uživatelů</h1>
</header>

<div class="admin-card" style="background: white; border-radius: 15px; padding: 20px; margin-bottom: 40px;">
    <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
        <thead>
            <tr style="border-bottom: 2px solid #eee; text-align: left;">
                <th style="padding: 10px;">Jméno (user)</th>
                <th style="padding: 10px;">Role</th>
                <th style="padding: 10px; text-align: right;">Akce</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users_list as $u): ?>
            <tr style="border-bottom: 1px solid #eee;">
                <td style="padding: 10px;"><?= htmlspecialchars($u['user']) ?></td>
                <td style="padding: 10px;"><?= $u['is_admin'] ? '⭐ Admin' : 'Člen' ?></td>
                <td style="padding: 10px; text-align: right;">
                    <a href="admin_actions.php?action=delete_user&id=<?= $u['id'] ?>" 
                       class="text-danger" onclick="return confirm('Smazat uživatele?')"><i class="fas fa-user-minus"></i></a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <hr>
    
    <h3><i class="fas fa-user-plus"></i> Přidat nového člena</h3>
    <form action="admin_actions.php" method="POST" style="display: grid; grid-template-columns: 1fr 1fr 1fr auto; gap: 10px; margin-top: 15px;">
        <input type="hidden" name="action" value="add_user">
        <input type="text" name="username" class="form-control" required minlength="3" placeholder="Jméno (login)" required>
        <input type="password" name="password" class="form-control" required minlength="3" placeholder="Heslo" required>
        <select name="is_admin" class="form-control">
            <option value="0">Člen rodiny</option>
            <option value="1">Administrátor</option>
        </select>
        <button type="submit" class="btn btn-primary">Přidat</button>
    </form>
</div>

<div class="container main-content">
    <header class="gallery-header">
        <h1><i class="fas fa-user-shield"></i> Správa alb</h1>
        <p>Zde můžete přejmenovávat nebo mazat celá alba.</p>
    </header>

    <div class="admin-card" style="background: white; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.05); overflow: hidden;">
        <table style="width: 100%; border-collapse: collapse; text-align: left;">
            <thead style="background: #f8f9fa; border-bottom: 2px solid #eee;">
                <tr>
                    <th style="padding: 15px 20px;">Název alba</th>
                    <th style="padding: 15px 20px;">Počet položek</th>
                    <th style="padding: 15px 20px; text-align: right;">Akce</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($albums as $a): ?>
                <tr style="border-bottom: 1px solid #eee;">
                    <td style="padding: 15px 20px; font-weight: 600; color: #2c3e50;">
                        <span id="name-text-<?= $a['id'] ?>"><?= htmlspecialchars($a['name']) ?></span>
                        <form id="edit-form-<?= $a['id'] ?>" action="admin_actions.php" method="POST" style="display: none;">
                            <input type="hidden" name="action" value="rename">
                            <input type="hidden" name="album_id" value="<?= $a['id'] ?>">
                            <input type="text" name="new_name" value="<?= htmlspecialchars($a['name']) ?>" class="form-control" style="display: inline-block; width: auto; padding: 5px 10px;">
                            <button type="submit" class="btn btn-primary btn-sm" style="padding: 5px 10px;">Uložit</button>
                        </form>
                    </td>
                    <td style="padding: 15px 20px; color: #666;"><?= $a['media_count'] ?> ks</td>
                    <td style="padding: 15px 20px; text-align: right;">
                        <button onclick="toggleEdit(<?= $a['id'] ?>)" class="btn btn-secondary btn-sm" title="Přejmenovat">
                            <i class="fas fa-edit"></i>
                        </button>
                        
                        <a href="admin_actions.php?action=delete&id=<?= $a['id'] ?>" 
                           class="btn btn-danger btn-sm" 
                           onclick="return confirm('Opravdu smazat celé album i s fotkami? Tato akce je nevratná!')"
                           title="Smazat">
                            <i class="fas fa-trash"></i>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function toggleEdit(id) {
    const text = document.getElementById('name-text-' + id);
    const form = document.getElementById('edit-form-' + id);
    if (form.style.display === 'none') {
        form.style.display = 'inline-block';
        text.style.display = 'none';
    } else {
        form.style.display = 'none';
        text.style.display = 'inline-block';
    }
}
</script>

</body>
<?php include "includes/footer.php"; ?>
</html>