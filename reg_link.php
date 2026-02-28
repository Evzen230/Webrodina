<?php
require "includes/auth.php";
require "includes/db.php";
include "includes/auth_check.php";
checkAuth();
checkAdmin();
require "includes/db.php"; // Předpokládám tvé PDO připojení $conn

// Akce: Generování nového tokenu
if (isset($_POST['generate_invite'])) {
    $token = bin2hex(random_bytes(32)); // Bezpečný náhodný řetězec
    $expires = date('Y-m-d H:i:s', strtotime('+24 hours')); // Platnost 24h

    $stmt = $conn->prepare("INSERT INTO registration_tokens (token, expires_at) VALUES (?, ?)");
    $stmt->execute([$token, $expires]);
    $msg = "Pozvánka vytvořena!";
}

// Načtení existujících tokenů
$tokens = $conn->query("SELECT * FROM registration_tokens ORDER BY created_at DESC")->fetchAll();
?>
<link rel="stylesheet" href="style.css">
<div class="container">
    <h2>Správa pozvánek</h2>
    <form method="POST">
        <button type="submit" name="generate_invite" class="btn btn-primary">Generovat nový odkaz (platnost 24h)</button>
    </form>

    <table class="docs-table" style="margin-top: 20px;">
        <thead>
            <tr>
                <th>Odkaz</th>
                <th>Platnost do</th>
                <th>Stav</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($tokens as $t): ?>
            <tr>
                <td><code>register.php?token=<?= $t['token'] ?></code></td>
                <td><?= date('d.m. H:i', strtotime($t['expires_at'])) ?></td>
                <td>
                    <?php if ($t['is_used']): ?>
                        <span class="badge danger">Použito</span>
                    <?php elseif (strtotime($t['expires_at']) < time()): ?>
                        <span class="badge warning">Expirováno</span>
                    <?php else: ?>
                        <span class="badge success">Aktivní</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>