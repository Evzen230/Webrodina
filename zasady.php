<?php
require "includes/db.php";
require "includes/auth.php";
include "includes/auth_check.php";

?>

<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <script src="includes/respons.js"></script>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="css/style2.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/doc.css">
    <title>Zásdy ochrany osobních údajů|Web Rodina</title>
</head>
<body>
<?php 
// Navbar se zobrazí JEN pokud:
// 1. Uživatel je přihlášen 
// 2. A ZÁROVEŇ už odsouhlasil podmínky
$show_nav = isset($_SESSION['user_id']) && !empty($_SESSION['agreed_at']);

if ($show_nav): 
?>
    <?php include "includes/navbar.php"; ?>
<?php endif;?>
<div class="legal-container">
    <aside class="legal-sidebar">
        <div class="legal-toc">
            <h4 style="color: #2c3e50; margin-bottom: 15px;">Navigace dokumentem</h4>
            <a href="#spravce">Správce osobních údajů</a>
            <a href="#uvod">Úvod</a>
            <a href="#shrom">Shromažďované osobní údaje</a>
            <a href="#ucel">Účel zpracování</a>
            <a href="#pravni">Právní základ zpracování</a>
            <a href="#zver">Zveřejňování údajů</a>
            <a href="#pred">Předávání osobních údajů</a>
            <a href="#doba">Doba uchovávání údajů</a>
            <a href="#zabez">Zabezpečení osobních údajů</a>
            <a href="#prava">Práva uživatelů</a>
            <a href="#zmeny">Změny zásad</a>
            <a href="docs/pdf/zasady.pdf" download="GDPR.pdf" class="legal-btn-download">
                <i class="fas fa-file-pdf"></i> Stáhnout PDF verzi
            </a>      
        </div>
    </aside>
    <main class="legal-body">
        <div class="legal-doc-header">
            <div class="header-content">
                <span class="doc-badge"><i class="fa-solid fa-award"></i> Oficiální dokument</span>
                <h1>Zásady ochrany osobních údajů</h1>
                <div class="doc-meta">
                    <span><i class="fas fa-user"></i> <strong>Autor:</strong>Jan Hrabal</span>
                    <span><i class="fas fa-calendar-alt"></i> <strong>Vydáno:</strong> 28. 02. 2026</span>
                    <span><i class="fas fa-calendar-alt"></i> <strong>Poslední úprava:</strong> 23. 02. 2026</span>
                    <span><i class="fa-solid fa-circle-info"></i> <strong>Verze dokumentu:</strong> 1. verze</span>
                </div>
            </div>
        </div>
        <section id="spravce">
            <h2 class="legal-title">Úvodní ustanovení</h2>
            <h3 class="legal-title3">Správce osobních údajů::</h3>
            <p>
                <strong>Jméno a příjmení:</strong> Jan Hrabal<br>
                <strong>Adresa:</strong><br>
                K Lučinám 2463/7<br>
                Praha 3<br>
                130 00<br>
                <strong>E-mail:</strong> janekevzen@gmail.com <br>
                <strong>Web:</strong> webrodina.evza.cz <br>
            </p>
        </section>
        <section id="uvod">
            <h2 class="legal-title">Úvod</h2>
            <p>Tyto zásady ochrany osobních údajů (dále jen „Zásady”) vysvětlují, jak správce webové platformy Web rodina nakládá s osobními údaji svých uživatelů.</p>
            <hr class="legal-divider">
            <p>Osobní údaje jsou zpracovávány v souladu s Nařízením Evropského parlamentu a Rady (EU) 2016/679 (GDPR) a zákonem č. 110/2019 Sb., o zpracování osobních údajů.</p>
        </section>
        <section id="shrom">
            <h2 class="legal-title">Shromažďované osobní údaje</h2>
            <p>Správce může zpracovávat následující osobní údaje:</p>
            <details class="legal-q">
                <summary>
                    <span class="summary-content"><i class="fa-solid fa-user-plus"></i>
                        Registrační údaje
                    </span>
                </summary>
                <div class="legal-ans">
                    <ul>
                        <li>Uživatelské jméno</li>
                        <li>Hash hesla</li>
                    </ul>
                </div>
            </details>
            <details class="legal-q">
                <summary>
                    <span class="summary-content"><i class="fa-solid fa-user-gear"></i>
                        Údaje vzniklé používáním služby
                    </span>
                </summary>
                <div class="legal-ans">
                    <ul>
                        <li>identifikátor relace</li>
                        <li>IP adresa</li>
                        <li>technické logy související s provozem a zabezpečením systému</li>
                    </ul>
                </div>
            </details>
            <details class="legal-q">
                <summary>
                    <span class="summary-content"><i class="fa-solid fa-user-pen"></i>
                        Uživatelský obsah
                    </span>
                </summary>
                <div class="legal-ans">
                    <ul>
                        <li>profilové fotografie</li>
                        <li>zápisy do kroniky</li>
                        <li>komentáře</li>
                        <li>informace uvedené v sekci „bio“</li>
                    </ul>
                </div>
            </details>
            <details class="legal-q">
                <summary>
                    <span class="summary-content"><i class="fa-solid fa-file-code"></i>
                        Metadata fotografií (EXIF)
                    </span>
                </summary>
                <div class="legal-ans">
                    <p>Při nahrání fotografie může docházet ke zpracování jejích technických metadat (EXIF), zejména:</p>
                    <ul>
                        <li>datum a čas pořízení snímku,</li>
                        <li>informace o použitém zařízení,</li>
                        <li>případné údaje o geografické poloze (GPS), pokud jsou součástí souboru.</li>
                    </ul>
                    <hr class="legal-divider">
                    <p>Správce si vyhrazuje právo omezit nebo odstranit zobrazení údajů o geografické poloze, zejména pokud se týkají nezletilých osob.</p>
                </div>
            </details>
            <p>Uživatel registrací potvrzuje, že tuto podmínku splňuje.</p>
        </section>
        <section id="ucel">
            <h2 class="legal-title">Účel zpracování</h2>
            <p>Osobní údaje jsou zpracovávány za účelem:</p>
            <ul>
                <li>vytvoření a správy uživatelského účtu,</li>
                <li>umožnění zveřejňování uživatelského obsahu,</li>
                <li>zajištění technického fungování webu,</li>
                <li>zobrazování technických informací o fotografiích a vizualizace míst jejich pořízení v rámci funkcionality platformy,</li>
                <li>ochrany práv Správce a ostatních uživatelů,</li>
                <li>plnění zákonných povinností.</li>
            </ul>
        </section>
        <section id="pravni">
            <h2 class="legal-title">Právní základ zpracování</h2>
            <p>Zpracování osobních údajů probíhá zejména na základě:</p>
            <ul>
                <li>plnění smlouvy (provoz uživatelského účtu),</li>
                <li>oprávněného zájmu Správce (zajištění bezpečnosti systému a ochrana proti zneužití),</li>
                <li>souhlasu uživatele (v případech, kdy je vyžadován),</li>
                <li>plnění právních povinností.</li>
            </ul>
        </section>
        <section id="zver">
            <h2 class="legal-title">Zveřejňování údajů</h2>
            <p>Uživatelské jméno, profilové fotografie, informace v sekci „bio“, zápisy do kroniky a komentáře jsou viditelné pouze přihlášeným uživatelům platformy.</p>
            <p>Fotografie mohou být zpřístupněny i osobám mimo platformu, pokud uživatel vytvoří veřejný odkaz ke sdílení alba. V takovém případě může být obsah přístupný i neregistrovaným osobám.</p>
            <p>Metadata fotografií (včetně případných údajů o poloze) jsou dostupná pouze přihlášeným uživatelům platformy.</p>
            <p>Uživatel je odpovědný za obsah, který zveřejňuje.</p>
        </section>
        <section id="pred">
            <h2 class="legal-title">Předávání osobních údajů</h2>
            <details class="legal-q">
                <summary>
                    <span class="summary-content"><i class="fa-solid fa-gear"></i>
                        Osobní údaje mohou být zpřístupněny:
                    </span>
                </summary>
                <div class="legal-ans">
                    <ul>
                        <li>Poskytovatelům hostingových a IT služeb nezbytných pro provoz webu</li>
                        <li>Orgánům veřejné moci, pokud to vyžaduje právní předpis</li>
                    </ul>
                </div>
            </details>
            <p>Správce nepředává osobní údaje mimo Evropskou unii.</p>
        </section>
        <section id="doba">
            <h2 class="legal-title">Doba uchovávání údajů</h2>
            <p>Osobní údaje jsou uchovávány po dobu trvání uživatelského účtu.</p>
            <p>Po zrušení účtu jsou osobní údaje vymazány bez zbytečného odkladu, pokud jejich uchování nevyžaduje právní předpis.</p>
            <p>Technické logy jsou uchovávány po přiměřenou dobu nezbytnou k zajištění bezpečnosti a ochrany systému.</p>
        </section>
        <section id="zabez">
            <h2 class="legal-title">Zabezpečení osobních údajů</h2>
            <p>Správce přijal přiměřená technická a organizační opatření k ochraně osobních údajů následující opatření.</p>
            <details class="legal-q">
                <summary>
                    <span class="summary-content"><i class="fa-solid fa-shield"></i>
                        Bezpečnostní opatření:
                    </span>
                </summary>
                <div class="legal-ans">
                    <ul>
                        <li>používání šifrovaného spojení (HTTPS),</li>
                        <li>ukládání hesel v hashované podobě,</li>
                        <li>omezení přístupu k databázi a serverovým souborům,</li>
                        <li>kontrolu přístupu pouze pro oprávněné osoby.</li>
                        <li>pravidelné aktualizace serverového prostředí a zabezpečení proti neoprávněnému přístupu.</li>
                    </ul>
                </div>
            </details>
        
        </section>
        <section id="prava">
            <h2 class="legal-title">Práva uživatelů</h2>
            <p>Uživatel má právo:</p>
            <ul>
                <li>požadovat přístup ke svým osobním údajům,</li>
                <li>požadovat opravu nepřesných údajů,</li>
                <li>požadovat výmaz osobních údajů („právo být zapomenut“),</li>
                <li>požadovat omezení zpracování,</li>
                <li>vznést námitku proti zpracování,</li>
                <li>podat stížnost u Úřadu pro ochranu osobních údajů (www.uoou.cz).</li>
            </ul>
            <p>Žádosti lze zaslat na e-mail: janekevzen@gmail.com</p>
        </section>
        <section id="zmeny">
            <p>Správce si vyhrazuje právo tyto Zásady aktualizovat. <br>
                Aktuální verze je vždy dostupná na webových stránkách platformy. <br> <br>
                Datum poslední aktualizace: 23.02.2026 <br>
            </p>
        </section>
    </main>
</div>
</body>
<?php if ($show_nav): ?>
<?php include "includes/footer.php"; ?>
<?php endif;?>
</html>

<script>
    function prepareAndPrint() {
    // Nejdříve rozbalíme všechny accordiony, aby v PDF/tisku byl vidět všechen text
    let details = document.querySelectorAll('.legal-q');
    details.forEach(detail => {
        detail.setAttribute('data-was-open', detail.open);
        detail.open = true;
    });

    // Spustíme tisk
    window.print();

    // Po zavření tiskového okna (volitelné) vrátíme accordiony do původního stavu
    setTimeout(() => {
        details.forEach(detail => {
            if (detail.getAttribute('data-was-open') === 'false') {
                detail.open = false;
            }
        });
    }, 1000);
}
</script>