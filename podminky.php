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
    <title>Podminky užívání|Web Rodina</title>
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
            <a href="#uvod">Úvod</a>
            <a href="#vznik">Vznik uživatelského účtu</a>
            <a href="#vek">Věkové omezení</a>
            <a href="#obsah">Uživatelský obsah</a>
            <a href="#zakaz">Zakázaný obsah</a>
            <a href="#sdile">Sdílení fotografií</a>
            <a href="#odpov">Odpovědnost provozovatele</a>
            <a href="#zruseni">Zrušení účtu</a>
            <a href="#dusev">Duševní vlastnictví</a>
            <a href="#licen">Licence k uživatelskému obsahu</a>
            <a href="#osobni">Ochrana osobních údajů</a>
            <a href="#ochrana">Ochrana nezletilých osob</a>
            <a href="#nahlas">Nahlašování závadného obsahu</a>
            <a href="#sankce">Porušení podmínek a sankce</a>
            <a href="#kom">Komunikace</a>
            <a href="#zaver">Závěrečná ustanovení</a>
            <a href="docs/pdf/podminky.pdf" download="Podminky_pouzivani.pdf" class="legal-btn-download">
                <i class="fas fa-file-pdf"></i> Stáhnout PDF verzi
            </a>      
        </div>
    </aside>
    <main class="legal-body">
        <div class="legal-doc-header">
            <div class="header-content">
                <span class="doc-badge"><i class="fa-solid fa-award"></i> Oficiální dokument</span>
                <h1>Podmínky používání webové platformy „Web rodina“</h1>
                <div class="doc-meta">
                    <span><i class="fas fa-user"></i> <strong>Autor:</strong>Jan Hrabal</span>
                    <span><i class="fas fa-calendar-alt"></i> <strong>Vydáno:</strong> 28. 02. 2026</span>
                    <span><i class="fas fa-calendar-alt"></i> <strong>Poslední úprava:</strong> 23. 02. 2026</span>
                    <span><i class="fa-solid fa-circle-info"></i> <strong>Verze dokumentu:</strong> 1. verze</span>
                </div>
            </div>
        </div>
        <section id="uvod">
            <h2 class="legal-title">Úvodní ustanovení</h2>
            <p>Tento portál je soukromým rodinným archivem. Přístupem na stránky souhlasíte s níže uvedenými podmínkami.</p>
            <hr class="legal-divider">
            <h3 class="legal-title3">Provozovatel:</h3>
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
        <section id="vznik">
            <h2 class="legal-title">Vznik uživatelského účtu</h2>
            <p>Uživatel pro registraci musí kontaktovat Provozovatele, který registraci potvrdí a vytvoří uživatelský účet.</p>
            <hr class="legal-divider">
            <p>Uživatelský účet vzniká dokončením registrace a aktivací ze strany Provozovatele</p>
            <details class="legal-q">
                <summary>
                    <span class="summary-content"><i class="fa-solid fa-circle-info"></i>
                        Doplňující informace k registraci
                    </span>
                </summary>
                <div class="legal-ans">
                    <ul>
                        <li>Uživatel je povinen uvádět pravdivé údaje.</li>
                        <li>Uživatel je odpovědný za ochranu svých přihlašovacích údajů.</li> 
                        <li>Uživatel nesmí umožnit přístup ke svému účtu třetím osobám..</li> 
                    </ul>
                </div>
            </details>
        </section>
        <section id="vek">
            <h2 class="legal-title">Věkové omezení</h2>
            <p>Registrace na platformě je povolena pouze osobám starším 15 let.</p>
            <details class="legal-q">
                <summary>
                    <span class="summary-content"><i class="fa-solid fa-section"></i>
                        Proč?
                    </span>
                </summary>
                <div class="legal-ans">
                    <p>V souladu se zákonem č. 110/2019 Sb., o zpracování osobních údajů, je minimální věková hranice pro samostatné udělení souhlasu 15 let.</p>
                </div>
            </details>
            <details class="legal-q">
                <summary>
                    <span class="summary-content"><i class="fas fa-star-of-life"></i>
                        Výjimka
                    </span>
                </summary>
                <div class="legal-ans">
                    <p>Osoba, která ještě nedovršila věku 15 let může se souhlasem zákonného zástupce odsouhlasit zásady ochrany osobních údajů.</p>
                </div>
            </details>
            <p>Uživatel registrací potvrzuje, že tuto podmínku splňuje.</p>
        </section>
        <section id="obsah">
            <h2 class="legal-title">Uživatelský obsah</h2>
            <p>Uživatelským obsahem se rozumí, jakýkoliv obsah nahraný uživatelem webových služeb.</p>
            <details class="legal-q">
                <summary>
                    <span class="summary-content"><i class="fas fa-photo-video"></i>
                    Co se bere jako uživatelský obsah?
                    </span>
                </summary>
                <div class="legal-ans">
                    <ul>
                        <li>Fotografie</li>
                        <li>Zápisy do kroniky</li>
                        <li>Komentáře</li>
                        <li>Profilové informace</li>
                        <li>Profilový obrázek</li>
                    </ul>
                </div>
            </details>
            <p>Uživatel odpovídá za obsah, který zveřejní.</p>
        </section>
        <section id="zakaz">
            <h2 class="legal-title">Zakázaný obsah</h2>
            <p>Uživatel nesmí zveřejňovat obsah porušující následující pravidla.</p>
            <details class="legal-q">
                <summary>
                    <span class="summary-content"><i class="fas fa-ban"></i>
                    Jaký obsah nemůže uživatel zveřejňovat?
                    </span>
                </summary>
                <div class="legal-ans">
                    <ul>
                        <li>Porušuje práva třetích osob, například autorská práva nebo právo na ochranu osobnosti</li>
                        <li>Obsahuje osobní údaje třetích osob bez jejich výslovného souhlasu</li>
                        <li>Je v rozporu s právními předpisy České republiky</li>
                        <li>Je urážlivý, nenávistný, diskriminační nebo jinak nevhodný</li>
                        <li>Vyobrazuje nahotu nebo sexuálně explicitní obsah</li>
                    </ul>
                </div>
            </details>
            <p>Provozovatel si vyhrazuje právo odstranit jakýkoliv obsah, který tyto podmínky porušuje, a případně uživatelský účet omezit nebo zrušit. Více v sekci <a href="#sankce">sankce</a></p>
        </section>
        <section id="sdile">
            <h2 class="legal-title">Sdílení fotografií</h2>
            <p>Pokud Uživatel vytvoří veřejný odkaz ke sdílení alba:</p>
            <ul>
                <li>Může být obsah přístupný i osobám mimo platformu</li>
                <li>Uživatel nese odpovědnost za důsledky takového sdílení</li>
            </ul>
            <p>Provozovatel nenese odpovědnost za další šíření obsahu třetími osobami.</p>
        </section>
        <section id="odpov">
            <h2 class="legal-title">Odpovědnost provozovatele</h2>
            <p>Provozovatel má následující odpovědnosti a práva</p>
            <details class="legal-q">
                <summary>
                    <span class="summary-content"><i class="fas fa-handshake"></i>
                    Provozovatel se zavazuje:
                    </span>
                </summary>
                <div class="legal-ans">
                    <ul>
                        <li>Zajistit dostupnost webové platformy a její technické fungování v rámci možností a technických omezení</li>
                        <li>Chránit osobní údaje uživatelů v souladu se Zásadami ochrany osobních údajů</li>
                        <li>Reagovat na nahlášený závadný obsah v rozumné lhůtě a přijímat opatření podle těchto Podmínek</li>
                    </ul>
                </div>
            </details>
            <details class="legal-q">
                <summary>
                    <span class="summary-content"><i class="fas fa-user-shield"></i>
                    Provozovatel nezodpovídá za:
                    </span>
                </summary>
                <div class="legal-ans">
                    <ul>
                        <li>Obsah vložený uživateli</li>
                        <li>Škody vzniklé zveřejněním obsahu uživatelem</li>
                        <li>Je v rozporu s právními předpisy České republiky</li>
                        <li>Dočasnou nedostupnost služby z technických nebo jiných oprávněných důvodů</li>
                    </ul>
                </div>
            </details>
            <details class="legal-q">
                <summary>
                    <span class="summary-content"><i class="fas fa-gavel"></i>
                    Provozovatel má právo:</summary>
                    </span>
                <div class="legal-ans">
                    <ul>
                        <li>Dočasně omezit nebo ukončit přístup uživatele</li>
                        <li>Odstranit obsah, který porušuje tyto Podmínky nebo právní předpisy</li>
                    </ul>
                </div>
            </details>
        </section>
        <section id="zruseni">
            <h2 class="legal-title">Zrušení účtu</h2>
            <p>Uživatel může kdykoliv požádat o zrušení účtu.</p>
            <hr class="legal-divider">
            <p>Provozovatel může účet zrušit v případě porušení těchto podmínek.</p>
        </section>
        <section id="dusev">
            <h2 class="legal-title">Duševní vlastnictví</h2>
            <p>Webová platforma jako celek (design, struktura, software) je chráněna autorským právem.</p>
        </section>
        <section id="licen">
            <h2 class="legal-title">Licence k uživatelskému obsahu</h2>
            <p>Uživatel prohlašuje, že je oprávněn zveřejnit obsah, který na platformu nahrává, a že tím neporušuje práva třetích osob.</p>
            <hr class="legal-divider">
            <p>Nahráním fotografie, textu nebo jiného obsahu poskytuje Uživatel Provozovateli nevýhradní, bezúplatnou licenci k užití tohoto obsahu v rozsahu nezbytném pro provoz a prezentaci webové platformy.</p>
            <details class="legal-q">
                <summary>
                    <span class="summary-content"><i class="fas fa-list-ul"></i>
                        Zejména k jeho:</summary>
                    </span>
                <div class="legal-ans">
                    <ul>
                        <li>Ukládání</li>
                        <li>Zobrazování</li>
                        <li>Technickému zpracování</li>
                        <li>Zpřístupnění ostatním uživatelům nebo prostřednictvím sdílecího odkazu</li>
                    </ul>
                </div>
            </details>
            <p>Licence je poskytována na dobu trvání uživatelského účtu, není-li obsah dříve odstraněn.</p>
        </section>
        <section id="osobni">
            <h2 class="legal-title">Ochrana osobních údajů</h2>
            <p>Zpracování osobních údajů se řídí samostatným dokumentem <a href="zasady.php">„Zásady ochrany osobních údajů“.</a></p>
        </section>
        <section id="ochrana">
            <h2 class="legal-title">Ochrana nezletilých osob</h2>
            <p>Uživatel bere na vědomí, že zveřejňování fotografií nebo jiného obsahu zobrazujícího nezletilé osoby podléhá zvláštní právní ochraně.</p>
            <details class="legal-q">
                <summary>
                    <span class="summary-content"><i class="fas fa-user-check"></i>
                        Uživatel odpovídá za to, že:</summary>
                    </span>
                <div class="legal-ans">
                    <ul>
                        <li>Je zákonným zástupcem nezletilé osoby, kterou na platformě zveřejňuje, nebo</li>
                        <li>má prokazatelný souhlas zákonného zástupce se zveřejněním takového obsahu.</li>
                    </ul>
                </div>
            </details>
            <p>Provozovatel si vyhrazuje právo odstranit obsah týkající se nezletilých osob, pokud vznikne pochybnost o oprávněnosti jeho zveřejnění.</p>
        </section>
        <section id="nahlas">
            <h2 class="legal-title">Nahlašování závadného obsahu</h2>
            <p>Každý uživatel může nahlásit obsah, který je v rozporu s těmito podmínkami nebo právními předpisy, prostřednictvím e-mailu: janekevzen@gmail.com, či jinou formou, uvedenou v části komunikace.</p>
            <hr class="legal-divider">
            <p>Samotné nahlášení musí mít dostatek informací na posouzení.</p>
            <hr class="legal-divider">
            <p>Provozovatel se zavazuje takové oznámení bez zbytečného odkladu posoudit.</p>            
            <hr class="legal-divider">
            <p>Pokud bude zjištěno porušení podmínek nebo právních předpisů, může být obsah odstraněn a uživatelský účet omezen nebo zrušen.</p>
        </section>
        <section id="sankce">
            <h2 class="legal-title">Porušení podmínek a sankce</h2>
            <p>V případě porušení těchto podmínek může provozovatel podle závažnosti porušení provést následující opatření.</p>
            <details class="legal-q">
                <summary>
                    <span class="summary-content"><i class="fas fa-exclamation-triangle"></i>
                        Platné opatření</summary>
                    </span>
                <div class="legal-ans">
                    <ul>
                        <li>upozornit Uživatele na závadné jednání,</li>
                        <li>dočasně omezit funkčnost účtu,</li>
                        <li>odstranit závadný obsah,</li>
                        <li>dočasně zablokovat uživatelský účet,</li>
                        <li>trvale zrušit uživatelský účet.</li>
                    </ul>
                </div>
            </details>
            <p>Rozhodnutí o přijetí opatření je na uvážení Provozovatele a přiměřené závažnosti porušení.</p>
        </section>
        <section id="kom">
            <h2 class="legal-title">Komunikace</h2>
            <p>Oficiální komunikace mezi Uživatelem a Provozovatelem probíhá prostřednictvím aplikace whatsapp.</p>
            <hr class="legal-divider">
            <p>Uživatel také může provozovatele kontaktovat prostřednictvím e-mailu na adresu: janekevzen@gmail.com</p>
            <hr class="legal-divider">
            <p>Jiné formy komunikace jsou brány za neoficiální a jejich sdělení nemusí být prošetřeno.</p>
        </section>
        <section id="zaver">
            <h2 class="legal-title">Závěrečná ustanovení</h2>
                        <p>Správce si vyhrazuje právo tyto podmínky aktualizovat. <br>
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