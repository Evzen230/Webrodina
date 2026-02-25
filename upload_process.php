<?php
require "includes/auth.php";
require "includes/db.php";
include "includes/auth_check.php";
checkAuth();

// Funkce pro vlastní logování
function log_moje_chyba($zprava) {
    $datum = date("Y-m-d H:i:s");
    $uzivatel = $_SESSION['username'] ?? 'neprihlasen';
    file_put_contents('error_log.txt', "[$datum] Uživatel $uzivatel: $zprava\n", FILE_APPEND);
}

@ini_set('memory_limit', '256M');
@set_time_limit(90); // Důležité pro fotky ze zrcadlovky!

if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_FILES['images'])) {
    $user_id = $_SESSION['user_id'];
    $files = $_FILES['images'];
    
    // 1. KONTROLA CHYB NA ZAČÁTKU
    foreach ($files['error'] as $key => $error) {
        if ($error !== UPLOAD_ERR_OK) {
            $chyba = "Neznámá chyba (Kód: $error)";
            switch ($error) {
                case UPLOAD_ERR_INI_SIZE: $chyba = "Fotka je moc velká (přesahuje limit serveru)."; break;
                case UPLOAD_ERR_PARTIAL: $chyba = "Fotka byla nahrána jen částečně."; break;
                case UPLOAD_ERR_NO_FILE: continue 2; // Přeskočí prázdné sloty
            }
            log_moje_chyba("Chyba u souboru " . $files['name'][$key] . ": " . $chyba);
            header("Location: nahrat.php?error=" . urlencode($chyba));
            exit;
        }

        // Kontrola typu souboru
        $allowed_types = ['image/jpeg', 'image/png'];
        if (!in_array($files['type'][$key], $allowed_types)) {
            $msg = "Formát souboru " . $files['name'][$key] . " není podporován (pouze JPG/PNG).";
            log_moje_chyba($msg);
            header("Location: nahrat.php?error=" . urlencode($msg));
            exit;
        }
    }

    // 2. LOGIKA ALBA
    $common_description = $_POST['description'] ?? '';
    $common_tags = trim($_POST['tags'] ?? '');
    $album_id = !empty($_POST['album_id']) ? (int)$_POST['album_id'] : null;
    $new_album_name = trim($_POST['new_album_name'] ?? '');

    if ($new_album_name !== '') {
        $stmt = $conn->prepare("INSERT INTO albums (name) VALUES (?) ON DUPLICATE KEY UPDATE id=LAST_INSERT_ID(id)");
        $stmt->execute([$new_album_name]);
        $album_id = $conn->lastInsertId();
    }

    if (!$album_id) {
        die("Chyba: Nevybral jsi album.");
    }

    $uploaded_count = 0;

    // 3. ZPRACOVÁNÍ FOTEK
    for ($i = 0; $i < count($files['name']); $i++) {
        $tmp_name = $files['tmp_name'][$i];
        
        // Individuální popis/štítky
        $current_description = !empty($_POST['individual_descriptions'][$i]) ? trim($_POST['individual_descriptions'][$i]) : $common_description;
        $current_tags_input = !empty($_POST['individual_tags'][$i]) ? trim($_POST['individual_tags'][$i]) : $common_tags;

        $new_filename = uniqid() . ".jpg"; 
        $target = "uploads/images/" . $new_filename;

        // --- Zpracování obrázku ---
        $img_info = getimagesize($tmp_name);
        if (!$img_info) {
            log_moje_chyba("Nelze přečíst info o souboru: " . $files['name'][$i]);
            continue;
        }
        
        list($w, $h) = $img_info;
        $max_size = 1200;
        $ratio = $w / $h;
        if ($w > $h) {
            $new_w = ($w > $max_size) ? $max_size : $w;
            $new_h = $new_w / $ratio;
        } else {
            $new_h = ($h > $max_size) ? $max_size : $h;
            $new_w = $new_h * $ratio;
        }

        // ... (výpočet $new_w a $new_h zůstává stejný)

        $source = imagecreatefromstring(file_get_contents($tmp_name));
        if (!$source) {
            log_moje_chyba("Chyba při vytváření zdroje z: " . $files['name'][$i]);
            continue;
        }

        $dest = imagecreatetruecolor($new_w, $new_h);

        // --- ZLEPŠENÍ KVALITY A PRŮHLEDNOSTI ---
        imagealphablending($dest, false);
        imagesavealpha($dest, true);
        imagecopyresampled($dest, $source, 0, 0, 0, 0, $new_w, $new_h, $w, $h);

        // Uložení s vyšší kvalitou (z 85 na 90) pro zamezení zrnění
        imagejpeg($dest, $target, 90); 

        imagedestroy($source);
        imagedestroy($dest);

        // ... (pokračuje zápis do DB)

        // Zápis do DB
        $stmt = $conn->prepare("INSERT INTO media (album_id, user_id, filename, type, description) VALUES (?, ?, ?, 'image', ?)");
        $stmt->execute([$album_id, $user_id, $new_filename, $current_description]);
        $current_media_id = $conn->lastInsertId();

        // Štítky
        if (!empty($current_tags_input) && $current_media_id) {
            $tagNames = explode(',', $current_tags_input);
            foreach ($tagNames as $tagName) {
                $tagName = trim($tagName);
                if ($tagName === "") continue;
                $conn->prepare("INSERT IGNORE INTO tags (name) VALUES (?)")->execute([$tagName]);
                $tagId = $conn->query("SELECT id FROM tags WHERE name = '$tagName'")->fetchColumn();
                if ($tagId) {
                    $conn->prepare("INSERT IGNORE INTO media_tags (media_id, tag_id) VALUES (?, ?)")->execute([$current_media_id, $tagId]);
                }
            }
        }
        $uploaded_count++;
    }

    header("Location: galerie.php?album_id=$album_id&msg=nahrano_$uploaded_count");
    exit();
}