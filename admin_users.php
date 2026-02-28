
<link rel="stylesheet" href="style.css">
<link rel="stylesheet" href="css/style2.css">
<?php
require "includes/auth.php";
require "includes/db.php";
include "includes/auth_check.php";
checkAuth();


// Smazání uživatele
if (isset($_GET['delete'])) {
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ? AND is_admin = 0");
    $stmt->execute([$_GET['delete']]);
    header("Location: admin_users.php");
}

// Přidání uživatele (přesunuto z registr.php)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST["name"];
    $email = $_POST["email"];
    $password = password_hash($_POST["password"], PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
    $stmt->execute([$name, $email, $password]);
}

$users = $conn->query("SELECT * FROM users")->fetchAll();
?>
<div class="container">
        <?php include "includes/navbar.php"; ?>
</div>
<h2>Správa uživatelů</h2>
<table border="1">
    <tr>
        <th>Jméno</th>
        <th>Email</th>
        <th>Akce</th>
    </tr>
    <?php foreach ($users as $u): ?>
    <tr>
        <td><?= htmlspecialchars($u["name"]) ?></td>
        <td><?= htmlspecialchars($u["email"]) ?></td>
        <td>
            <?php if ($u["is_admin"] == 0): ?>
                <a href="?delete=<?= $u["id"] ?>" onclick="return confirm('Opravdu smazat?')">Smazat</a>
            <?php else: ?>
                Admin
            <?php endif; ?>
        </td>
    </tr>
    <?php endforeach; ?>
</table>

<hr>
<h3>Přidat nového člena rodiny</h3>
<form method="POST">
    <input type="text" name="name" placeholder="Jméno" required><br>
    <input type="email" name="email" placeholder="Email" required><br>
    <input type="password" name="password" placeholder="Heslo" required><br>
    <button type="submit">Vytvořit účet</button>
</form>
<br>
<a href="index.php">Zpět na hlavní stranu</a>