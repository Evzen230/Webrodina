<head>
    <meta charset="UTF-8">
    <srcript src="includes/respons.js"></script>
    <title>Web rodina</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/kalendar.css">
    <link rel="stylesheet" href="css/style2.css">
    <link rel="stylesheet" href="style.css">
</head>
<!-- Google tag (gtag.js) 
<script async src="https://www.googletagmanager.com/gtag/js?id=G-GGN9Y19FYQ"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'G-GGN9Y19FYQ');
</script> -->


<?php
require "includes/db.php";
require "includes/auth.php";
include "includes/auth_check.php";
checkAuth();
// 1. NEJDŘÍVE ZJISTÍME DATUM (aby ho znalo ukládání i načítání)
$month = isset($_GET['m']) ? (int)$_GET['m'] : (int)date('m');
$year = isset($_GET['y']) ? (int)$_GET['y'] : (int)date('Y');

// 2. ZPRACOVÁNÍ FORMULÁŘE (Ukládání do DB)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_event'])) {
    $title = htmlspecialchars($_POST['title'] ?? '');
    $date = $_POST['date'] ?? '';
    
    // Defaultní konec = začátek
    $end_date = !empty($_POST['end_date']) ? $_POST['end_date'] : $date;
    
    $type = $_POST['type'] ?? 'oslava';
    $recurrence = $_POST['recurrence'] ?? 'none';
    $desc = htmlspecialchars($_POST['description'] ?? ''); 
    $user_id = $_SESSION['user_id'] ?? 0;

    // Příprava dotazu se 7 parametry
    $stmtInsert = $conn->prepare("INSERT INTO calendar_events (title, event_date, end_date, type, description, created_by, recurrence) VALUES (?, ?, ?, ?, ?, ?, ?)");
    
    try {
        $stmtInsert->execute([$title, $date, $end_date, $type, $desc, $user_id, $recurrence]);
        // Po uložení hned redirect, aby se při refresh stránky data neuložila znovu
        header("Location: kalendar.php?m=$month&y=$year");
        exit;
    } catch (PDOException $e) {
        die("Chyba při ukládání: " . $e->getMessage());
    }
}

// 3. LOGIKA VÝPOČTU DNŮ
$firstDayOfMonth = mktime(0, 0, 0, $month, 1, $year);
$daysInMonth = date('t', $firstDayOfMonth);
$dayOfWeek = date('N', $firstDayOfMonth); // 1 (Po) až 7 (Ne)

// 4. NAČTENÍ UDÁLOSTÍ PRO TENTO MĚSÍC (Včetně dovolených a opakování)
$currentMonthStart = sprintf('%04d-%02d-01', $year, $month);
$stmtFetch = $conn->prepare("SELECT * FROM calendar_events WHERE 
    (event_date <= LAST_DAY(:start) AND end_date >= :start) 
    OR recurrence != 'none'");
$stmtFetch->execute(['start' => $currentMonthStart]);
$allEvents = $stmtFetch->fetchAll(PDO::FETCH_ASSOC);
usort($allEvents, function($a, $b) {
    // 1. Dřívější datum začátku má přednost
    if ($a['event_date'] != $b['event_date']) {
        return strcmp($a['event_date'], $b['event_date']);
    }
    // 2. Delší trvání má přednost (aby dlouhé čáry byly nahoře)
    $durA = strtotime($a['end_date']) - strtotime($a['event_date']);
    $durB = strtotime($b['end_date']) - strtotime($b['event_date']);
    if ($durA != $durB) {
        return $durB - $durA;
    }
    // 3. Pojistka podle ID
    return $a['id'] - $b['id'];
});
// České názvy měsíců
$mesice = [
    1 => 'Leden', 'Únor', 'Březen', 'Duben', 'Květen', 'Červen', 
    'Červenec', 'Srpen', 'Září', 'Říjen', 'Listopad', 'Prosinec'
];
if (isset($_POST['delete_event']) && isset($_POST['delete_id'])) {
    $stmt = $conn->prepare("DELETE FROM calendar_events WHERE id = ?");
    $stmt->execute([$_POST['delete_id']]);
    header("Location: kalendar.php?m=$month&y=$year");
    exit;
}
?>
<?php include "includes/navbar.php"; ?>

<div class="container">
<div class="calendar-header">
    <div class="nav-left">
        <a href="?m=<?= $month-1 == 0 ? 12 : $month-1 ?>&y=<?= $month-1 == 0 ? $year-1 : $year ?>" class="btn-sm"><i class="fas fa-chevron-left"></i></a>
        <a href="kalendar.php" class="btn-today"><i class="fas fa-calendar-day"></i> Dnes</a>
    </div>
    
    <h2><?= $mesice[(int)$month] . " " . $year ?></h2>
    
    <a href="?m=<?= $month+1 == 13 ? 1 : $month+1 ?>&y=<?= $month+1 == 13 ? $year+1 : $year ?>" class="btn-sm"><i class="fas fa-chevron-right"></i></a>
</div>

    <table class="calendar-table">
        <thead>
            <tr>
                <th>Po</th><th>Út</th><th>St</th><th>Čt</th><th>Pá</th><th>So</th><th class="sun">Ne</th>
            </tr>
        </thead>
        <tbody>
            <tr>
    <?php
// Odsazení prázdných buněk na začátku měsíce
for ($i = 1; $i < $dayOfWeek; $i++) {
    echo "<td></td>";
}

for ($day = 1; $day <= $daysInMonth; $day++) {
    // Formát aktuálního dne pro porovnání
    $currentDate = sprintf('%04d-%02d-%02d', $year, $month, $day);
    $isToday = ($currentDate == date('Y-m-d')) ? 'today' : '';

    // Začátek nové řádky po 7 dnech
    if (($day + $dayOfWeek - 2) % 7 == 0 && $day != 1) {
        echo "</tr><tr>";
    }

    echo "<td class='$isToday'>";
    echo "  <div class='day-header'><span class='day-num'>$day</span>";
    echo "  <button type='button' class='add-event-btn' onclick='openPopup(\"$currentDate\")'><i class='fas fa-plus'></i></button></div>";

    // Projdeme všechny načtené události
    foreach ($allEvents as $event) {
        $start = $event['event_date'];
        $end = $event['end_date'];
        $rec = $event['recurrence'];

        // --- LOGIKA PRO CSS TŘÍDY (propojení čar) ---
        $classes = ['event-tag', $event['type']];
        if ($start !== $end) {
            if ($currentDate === $start) $classes[] = 'event-start';
            elseif ($currentDate === $end) $classes[] = 'event-end';
            elseif ($currentDate > $start && $currentDate < $end) $classes[] = 'event-mid';
        }

        // --- LOGIKA PRO ZOBRAZENÍ ---
        $isInRange = ($currentDate >= $start && $currentDate <= $end);
        $isYearly  = ($rec == 'yearly' && date('m-d', strtotime($start)) == date('m-d', strtotime($currentDate)) && $currentDate >= $start);
        $isMonthly = ($rec == 'monthly' && date('d', strtotime($start)) == date('d', strtotime($currentDate)) && $currentDate >= $start);
        $isWeekly  = ($rec == 'weekly' && date('N', strtotime($start)) == date('N', strtotime($currentDate)) && $currentDate >= $start);

        if ($isInRange || $isYearly || $isMonthly || $isWeekly) {
            $eventJson = htmlspecialchars(json_encode($event), ENT_QUOTES, 'UTF-8');
            $classString = implode(' ', $classes);
            
            echo "<div class='$classString' onclick='openDetail($eventJson)'>";
            echo htmlspecialchars($event['title']);
            echo "</div>";
        }
    }
    echo "</td>";
}

// Doplnění prázdných buněk na konci měsíce
while (($day + $dayOfWeek - 2) % 7 != 0) {
    echo "<td></td>";
    $day++;
}
?>
</tr>
        </tbody>
    </table>
</div>
<div id="eventPopup" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closePopup()">&times;</span>
        <h3><i class="fas fa-calendar-plus"></i> Nová událost</h3>
        
        <form method="POST">
            <div class="input-group">
                <label>Název</label>
                <input type="text" name="title" required>
            </div>

            <div class="form-row">
                <div class="input-group">
                    <label>Začátek</label>
                    <input type="date" name="date" id="popupDate" required onchange="updateEndDate(this.value)">
                </div>
                <div class="input-group">
                    <label>Konec</label>
                    <input type="date" name="end_date" id="popupEndDate">
                </div>
            </div>

            <div class="input-group">
                <label>Typ</label>
                <select name="type">
                    <option value="narozeniny">Narozeniny</option>
                    <option value="oslava">Oslava</option>
                    <option value="sraz">Sraz</option>
                    <option value="vyroci">Výročí</option>
                    <option value="jine">Jiné</option>
                </select>
            </div>

            <div class="input-group">
                <label>Opakování</label>
                <select name="recurrence">
                    <option value="none">Bez opakování</option>
                    <option value="yearly">Každý rok</option>
                    <option value="monthly">Každý měsíc</option>
                    <option value="weekly">Každý týden</option>
                </select>
            </div>

            <div class="input-group">
                <label>Popis</label>
                <textarea name="description" rows="2"></textarea>
            </div>

            <button type="submit" name="add_event" class="btn-save">Uložit</button>
        </form>
    </div>
</div>
<div id="detailPopup" class="modal">
    <div class="modal-content detail-content">
        <span class="close" onclick="closeDetail()">&times;</span>
        
        <div id="detailHeader" class="detail-header">
            <h2 id="viewTitle"></h2>
            <span id="viewType" class="event-tag"></span>
        </div>

        <div class="detail-info">
            <p><strong><i class="fas fa-calendar-alt"></i> Kdy:</strong> <span id="viewDate"></span></p>
            <div class="description-box">
                <strong><i class="fas fa-align-left"></i> Popis:</strong>
                <p id="viewDesc" style="white-space: pre-wrap;"></p>
            </div>
        </div>

        <div class="detail-footer">
            <form method="POST" onsubmit="return confirm('Opravdu chceš tuto událost smazat?');">
                <input type="hidden" name="delete_id" id="deleteId">
                <button type="submit" name="delete_event" class="btn-delete">
                    <i class="fas fa-trash"></i> Smazat událost
                </button>
            </form>
        </div>
    </div>
</div>
<?php include "includes/footer.php"; ?>

<script>
function openDetail(data) {
    document.getElementById("viewTitle").innerText = data.title;
    document.getElementById("viewType").innerText = data.type;
    document.getElementById("viewType").className = "event-tag " + data.type;
    
    // Formátování data pro zobrazení
    let dateText = data.event_date;
    if (data.end_date && data.end_date !== data.event_date) {
        dateText += " až " + data.end_date;
    }
    document.getElementById("viewDate").innerText = dateText;
    
    // Popis - pokud je prázdný, napíšeme "Žádný popis"
    document.getElementById("viewDesc").innerText = data.description || "Žádný podrobný popis nebyl zadán.";
    
    // ID pro smazání
    document.getElementById("deleteId").value = data.id;

    document.getElementById("detailPopup").style.display = "block";
}

function closeDetail() {
    document.getElementById("detailPopup").style.display = "none";
}
function openPopup(dateString) {
    const dateInput = document.getElementById("popupDate");
    const endDateInput = document.getElementById("popupEndDate");
    
    dateInput.value = dateString;
    endDateInput.value = dateString; // Defaultně nastavíme konec na stejný den
    
    document.getElementById("eventPopup").style.display = "block";
}

// Když uživatel změní datum začátku, aktualizujeme i konec (pokud je menší)
function updateEndDate(val) {
    document.getElementById("popupEndDate").value = val;
}

function closePopup() {
    document.getElementById("eventPopup").style.display = "none";
}
function openPopup(dateString) {
    // Teď už je dateString ve formátu YYYY-MM-DD, 
    // takže ho můžeme rovnou vložit do inputu
    document.getElementById("popupDate").value = dateString;
    document.getElementById("eventPopup").style.display = "block";
}

function closePopup() {
    document.getElementById("eventPopup").style.display = "none";
}

// Zavření okna kliknutím mimo box
window.onclick = function(event) {
    let modal = document.getElementById("eventPopup");
    if (event.target == modal) {
        modal.style.display = "none";
    }
}
</script>