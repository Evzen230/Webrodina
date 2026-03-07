<?php

//!!NEPPOUŽÍVAT!!

require "includes/db.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST["name"];
    $email = $_POST["email"];
    $password = password_hash($_POST["password"], PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
    $stmt->execute([$name, $email, $password]);

    header("Location: login.php");
}
?>

<form method="POST">
    <h2>Registrace</h2>
    <input type="text" name="name" placeholder="Jméno" required><br>
    <input type="email" name="email" placeholder="Email" required><br>
    <input type="password" name="password" placeholder="Heslo" required><br>
    <button type="submit">Registrovat</button>
</form>