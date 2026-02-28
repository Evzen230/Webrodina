<?php
require_once "includes/auth.php";
require_once "includes/db.php";
include_once "includes/auth_check.php";
checkAuth();
?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nápověda | Rodinný Archiv</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="css/style2.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>

<?php include "includes/navbar.php"; ?>

<div class="container main-content">
    <header class="gallery-header">
        <h1><i class="fas fa-life-ring"></i> Jak používat náš web?</h1>
    </header>

    <div class="help-grid">
        <section class="help-card">
            <h2><i class="fas fa-cloud-upload-alt"></i> Nahrávání fotek</h2>
            <ol>
                <li>Klikni na <strong>"Nahrát"</strong> v menu.</li>
                <li>Vyber fotky z mobilu nebo počítače (můžeš jich vybrat i 50 najednou).</li>
                <li><strong>Kam to patří?</strong> Vyber stávající album (třeba "Vánoce 2024") nebo napiš název pro úplně nové album.</li>
                <li>Můžeš přidat společný popis pro všechno, nebo u každé fotky napsat zvlášť.</li>
                <li>Klikni na "Nahrát vše" a počkej, až doběhne do konce.</li>
            </ol>
        </section>

        <section class="help-card">
            <h2><i class="fas fa-images"></i> Prohlížení galerie</h2>
            <ul>
                <li>Kliknutím na fotku ji zvětšíš na celou obrazovku.</li>
                <li>Mezi fotkami můžeš listovat <strong>šipkami</strong> (na PC) nebo <strong>potažením prstem</strong> (na mobilu).</li>
                <li>Křížkem vpravo nahoře nebo kliknutím mimo fotku se vrátíš zpět.</li>
            </ul>
        </section>

        <section class="help-card" id="hledani">
            <h2><i class="fas fa-search"></i> Jak něco najít?</h2>
            <p>Nahoře v liště je vyhledávací pole. Stačí začít psát:</p>
            <ul>
                <li>např. "Děti"</li>
            </ul>
            <p>Našeptávač ti hned ukáže, co našel.</p>
        </section>

        <section class="help-card">
            <h2><i class="fas fa-book"></i> Kronika</h2>
            <p>Kronika slouží pro delší vyprávění nebo zapsání důležitých zážitků, které si nechceme nechat jen pro sebe.</p>
            <ul>
                <li>Přidej nadpis a text (co se stalo, kdo tam byl).</li>
                <li>Můžeš přiložit jednu hlavní fotku, která událost nejlépe vystihuje.</li>
            </ul>
        </section>
    </div>

    <div style="text-align: center; margin-top: 40px; padding: 20px; background: #fdf6e3; border-radius: 10px;">
        <p><strong>Něco nefunguje?</strong> Napiš mi, já to opravim. 👨‍💻</p>
    </div>
</div>
</section>
</body>
<?php include "includes/footer.php"; ?>
</html>