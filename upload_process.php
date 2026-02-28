<?php
require "includes/auth.php";
require "includes/db.php";
include "includes/auth_check.php";
checkAuth();

// Zachytíme jakýkoliv nechtěný výstup (např. Warningy), aby nerozbily JSON
ob_start();

function log_moje_chyba($zprava) {
    $datum = date("Y-m-d H:i:s");
    $uzivatel = $_SESSION['user'] ?? 'neprihlasen';
    file_put_contents('error_log.txt', "[$datum] Uživatel $uzivatel: $zprava\n", FILE_APPEND);
}

function getGpsDecimal($coordinate, $ref) {
    if (!$coordinate || !$ref) return null;

    $parts = [];
    foreach ($coordinate as $part) {
        $p = explode('/', $part);
        if (count($p) == 2 && $p[1] > 0) {
            $parts[] = $p[0] / $p[1];
        } else {
            $parts[] = $p[0];
        }
    }

    $decimal = $parts[0] + ($parts[1] / 60) + ($parts[2] / 3600);
    // Pokud je to Jih nebo Západ, musí být číslo záporné
    return ($ref == 'S' || $ref == 'W') ? $decimal * -1 : $decimal;
}

@ini_set('memory_limit', '256M');
@set_time_limit(90);

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES['image'])) {
    $user_id = $_SESSION['user_id'];
    $file = $_FILES['image'];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        log_moje_chyba("Chyba uploadu: " . $file['error']);
        http_response_code(400);
        exit(json_encode(['status' => 'error', 'message' => 'Chyba nahrávání']));
    }

    // 1. LOGIKA ALBA
    $album_id = !empty($_POST['album_id']) ? (int)$_POST['album_id'] : null;
    $new_album_name = trim($_POST['new_album_name'] ?? '');

    if (!$album_id && $new_album_name !== '') {
        $stmt = $conn->prepare("SELECT id FROM albums WHERE name = ?");
        $stmt->execute([$new_album_name]);
        $album_id = $stmt->fetchColumn();

        if (!$album_id) {
            $stmt = $conn->prepare("INSERT INTO albums (name) VALUES (?)");
            $stmt->execute([$new_album_name]);
            $album_id = $conn->lastInsertId();
        }
    }

    // 2. DATA Z FORMULÁŘE (Individuální pro každou fotku)
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';
    $tags_raw = isset($_POST['tags']) ? trim($_POST['tags']) : '';

    // 3. ZPRACOVÁNÍ OBRÁZKU
    $tmp_name = $file['tmp_name'];
    $new_filename = uniqid() . ".jpg"; 
    $target = "uploads/images/" . $new_filename;

    $img_info = getimagesize($tmp_name);
    if (!$img_info) {
        log_moje_chyba("Nelze přečíst info o souboru.");
        http_response_code(400);
        exit(json_encode(['status' => 'error', 'message' => 'Neplatný formát']));
    }
    
    list($w, $h) = $img_info;
    $source = imagecreatefromstring(file_get_contents($tmp_name));

    // Rotace podle EXIF
    $exif = @exif_read_data($tmp_name);
    if ($exif && isset($exif['Orientation'])) {
        switch ($exif['Orientation']) {
            case 3: $source = imagerotate($source, 180, 0); break;
            case 6: 
                $source = imagerotate($source, -90, 0);
                $temp = $w; $w = $h; $h = $temp;
                break;
            case 8: 
                $source = imagerotate($source, 90, 0);
                $temp = $w; $w = $h; $h = $temp;
                break;
        }
    }

    // Výpočet rozměrů (max 1200px)
    $max_size = 1200;
    $ratio = $w / $h;
    if ($w > $h) {
        $new_w = (int)($w > $max_size ? $max_size : $w);
        $new_h = (int)($new_w / $ratio);
    } else {
        $new_h = (int)($h > $max_size ? $max_size : $h);
        $new_w = (int)($new_h * $ratio);
    }

    $dest = imagecreatetruecolor($new_w, $new_h);
    imagealphablending($dest, false);
    imagesavealpha($dest, true);
    imagecopyresampled($dest, $source, 0, 0, 0, 0, $new_w, $new_h, $w, $h);

    if (!imagejpeg($dest, $target, 90)) {
        log_moje_chyba("Nepodařilo se uložit soubor do složky.");
    }

    imagedestroy($source);
    imagedestroy($dest);

    // 4. ZÁPIS DO DB
    $stmt = $conn->prepare("INSERT INTO media (album_id, user_id, filename, type, description) VALUES (?, ?, ?, 'image', ?)");
    $stmt->execute([$album_id, $user_id, $new_filename, $description]);
    $current_media_id = $conn->lastInsertId();

    // 5. ZPRACOVÁNÍ ŠTÍTKŮ
    if (!empty($tags_raw) && $current_media_id) {
        $tagNames = explode(',', $tags_raw);
        foreach ($tagNames as $tagName) {
            $tagName = trim($tagName);
            if ($tagName === "") continue;

            $conn->prepare("INSERT IGNORE INTO tags (name) VALUES (?)")->execute([$tagName]);
            
            $stmt_tag = $conn->prepare("SELECT id FROM tags WHERE name = ?");
            $stmt_tag->execute([$tagName]);
            $tagId = $stmt_tag->fetchColumn();

            if ($tagId) {
                $conn->prepare("INSERT IGNORE INTO media_tags (media_id, tag_id) VALUES (?, ?)")->execute([$current_media_id, $tagId]);
            }
        }
    }

    // Vyčistíme buffer a pošleme čistý JSON
    ob_clean();
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'success', 
        'album_id' => $album_id
    ]);
    exit();
}