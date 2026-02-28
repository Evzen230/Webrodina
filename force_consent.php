<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <srcript src="includes/respons.js"></script>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <title>Souhlas|Web Rodina</title>
</head>
<body>
<?php
require "includes/db.php";
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Pokud uživatel není přihlášen, nemá tu co dělat
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (isset($_POST['accept_now'])) {
    $userId = $_SESSION['user_id'];
    $now = date('Y-m-d H:i:s');

    try {
        // 1. Uložíme do databáze
        $update = $conn->prepare("UPDATE users SET agreed_at = ? WHERE id = ?");
        if ($update->execute([$now, $userId])) {
            
            // 2. !!! KLÍČOVÝ KROK: AKTUALIZACE SESSION !!!
            // Bez tohoto tě auth_check.php nepustí, protože si myslí, že jsi pořád "neodsouhlasil"
            $_SESSION['agreed_at'] = $now;

            // 3. Přesměrování na hlavní stranu
            header("Location: index.php");
            exit();
        }
    } catch (PDOException $e) {
        $error = "Chyba při ukládání: " . $e->getMessage();
    }
}
?>
<body class="consent-page"> <div class="consent-overlay">
        <div class="consent-card">
            <div class="consent-content">
                <i class="fas fa-user-shield consent-icon"></i>
                <h1>Dání souhlasu se zpracováním osobních údajů</h1>
                <p>
                    Abychom mohli dál bezpečně provozovat náš rodinný web, 
                    potřebujeme tvůj souhlas s ochranou údajů a podmínkami používání.
                </p>
                <p class="meta">
                    Přečti si prosím naše <a href="zasady.php">zásady ochrany osobních údajů</a> a <a href="podminky.php">podmínky používání</a>.
                </p>
                
                <form method="POST" style="margin-top: 30px;">
                    <button type="submit" name="accept_now" class="btn btn-primary btn-lg">
                        Souhlasím a chci pokračovat
                    </button>
                </form>
            </div>
        </div>
    </div>
</body>
</body>
</html>

<style>
/* Speciální pozadí pro stránku se souhlasem */
.consent-page {
    background-image: url('/uploads/souhlas_bg.jpg'); 
    background-repeat: no-repeat;
    background-position: center center;
    background-attachment: fixed;
    background-size: cover;
    background-color: #2c3e50; /* Záložní barva, kdyby obrázek selhal */
    height: 100vh;
    margin: 0;
    padding: 0;
}

/* Ztmavení obrázku na pozadí, aby text lépe vynikl */
.consent-overlay {
    background: rgba(0, 0, 0, 0.4); /* Jemný tmavý filtr */
    width: 100%;
    height: 100%;
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 20px;
}

/* Plovoucí bílé okno */
.consent-card {
    background: rgba(255, 255, 255, 0.95); /* Téměř bílá, lehce průsvitná */
    backdrop-filter: blur(10px); /* Moderní efekt rozmazání podkladu */
    padding: 40px;
    border-radius: 20px;
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
    max-width: 550px;
    width: 100%;
    text-align: center; /* Vše uvnitř bude na střed */
    animation: fadeInUp 0.6s ease-out;
}

/* Ikona nahoře */
.consent-icon {
    font-size: 3rem;
    color: var(--primary-color);
    margin-bottom: 20px;
}

/* Centrování nadpisu a textu */
.consent-content h1 {
    font-size: 1.8rem;
    color: #2c3e50;
    margin-bottom: 20px;
}

.consent-content p {
    font-size: 1.1rem;
    color: #555;
    line-height: 1.6;
}

/* Větší tlačítko */
.btn-lg {
    padding: 15px 30px;
    font-size: 1.1rem;
    width: 100%;
    border-radius: 12px;
}

/* Animace pro hezký nástup */
@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>