<?php
require "includes/auth.php";
require "includes/db.php";
include "includes/auth_check.php";
checkAuth();

// --- ZPRACOVÁNÍ POŽADAVKU ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES['image'])) {
    ob_start(); 
    header('Content-Type: application/json');

    // Pomocná funkce pro GPS zůstává
    function getGpsDecimal($coordinate, $ref) {
        if (!$coordinate || !$ref) return null;
        $parts = [];
        foreach ($coordinate as $part) {
            $p = explode('/', $part);
            if (count($p) == 2 && $p[1] > 0) $parts[] = $p[0] / $p[1];
            else $parts[] = $p[0];
        }
        $decimal = $parts[0] + ($parts[1] / 60) + ($parts[2] / 3600);
        return ($ref == 'S' || $ref == 'W') ? $decimal * -1 : $decimal;
    }

    @ini_set('memory_limit', '256M');
    @set_time_limit(90);

    $user_id = $_SESSION['user_id'];
    $file = $_FILES['image'];
    
    // --- ÚPRAVA: Získání hodnoty pro anonymizaci ---
    // --- ÚPRAVA: Lepší chytání hodnoty (zkontrolujeme text i boolean) ---
$remove_exif = (isset($_POST['remove_exif']) && ($_POST['remove_exif'] === 'true' || $_POST['remove_exif'] === '1' || $_POST['remove_exif'] === true));

    if ($file['error'] !== UPLOAD_ERR_OK) {
        exit(json_encode(['status' => 'error', 'message' => 'Chyba nahrávání']));
    }

    $album_id = !empty($_POST['album_id']) ? (int)$_POST['album_id'] : null;
    $new_album_name = trim($_POST['new_album_name'] ?? '');

    if (!$album_id && $new_album_name !== '') {
        $stmt = $conn->prepare("INSERT IGNORE INTO albums (name) VALUES (?)");
        $stmt->execute([$new_album_name]);
        $album_id = $conn->lastInsertId() ?: $conn->query("SELECT id FROM albums WHERE name = " . $conn->quote($new_album_name))->fetchColumn();
    }

    $description = trim($_POST['description'] ?? '');
    $tags_raw = trim($_POST['tags'] ?? '');
    $tmp_name = $file['tmp_name'];
    $new_filename = uniqid() . ".jpg"; 
    $target = "uploads/images/" . $new_filename;

    // EXIF data - Čteme je vždy, abychom mohli fotku otočit, ale ukládat je budeme jen když není anonymizace
    $exif = @exif_read_data($tmp_name);
    $taken_at = null; $device = null; $lat = null; $lng = null; $exif_json = null;

    // --- ÚPRAVA: Logika ukládání EXIFu do DB ---
    if ($exif && !$remove_exif) {
        $exif_json = json_encode($exif);
        if (isset($exif['DateTimeOriginal'])) {
            $taken_at = str_replace(':', '-', substr($exif['DateTimeOriginal'], 0, 10)) . substr($exif['DateTimeOriginal'], 10);
        }
        $make = $exif['Make'] ?? '';
        $model = $exif['Model'] ?? '';
        $device = trim("$make $model");
        if (isset($exif['GPSLatitude'], $exif['GPSLatitudeRef'], $exif['GPSLongitude'], $exif['GPSLongitudeRef'])) {
            $lat = getGpsDecimal($exif['GPSLatitude'], $exif['GPSLatitudeRef']);
            $lng = getGpsDecimal($exif['GPSLongitude'], $exif['GPSLongitudeRef']);
        }
    }

    // Resize a Auto-Rotate (GD knihovna při imagejpeg automaticky odstraňuje EXIF z fyzického souboru)
    $img_info = getimagesize($tmp_name);
    list($w, $h) = $img_info;
    $source = imagecreatefromstring(file_get_contents($tmp_name));

    // Rotaci uděláme i při anonymizaci (aby fotka nebyla bokem), ale EXIF pak zmizí
    if ($exif && isset($exif['Orientation'])) {
        switch ($exif['Orientation']) {
            case 3: $source = imagerotate($source, 180, 0); break;
            case 6: $source = imagerotate($source, -90, 0); $temp = $w; $w = $h; $h = $temp; break;
            case 8: $source = imagerotate($source, 90, 0); $temp = $w; $w = $h; $h = $temp; break;
        }
    }

    $max_size = 1200;
    $ratio = $w / $h;
    if ($w > $h) {
        $new_w = ($w > $max_size) ? $max_size : $w;
        $new_h = $new_w / $ratio;
    } else {
        $new_h = ($h > $max_size) ? $max_size : $h;
        $new_w = $new_h * $ratio;
    }

    $dest = imagecreatetruecolor($new_w, $new_h);
    imagealphablending($dest, false);
    imagesavealpha($dest, true);
    imagecopyresampled($dest, $source, 0, 0, 0, 0, $new_w, $new_h, $w, $h);
    
    // imagejpeg uloží fotku bez EXIFu (GD EXIF neumí), čímž dojde k fyzické anonymizaci souboru
    imagejpeg($dest, $target, 85);
    imagedestroy($source); imagedestroy($dest);

    // Zápis do DB
    $stmt = $conn->prepare("INSERT INTO media (album_id, user_id, filename, type, description, taken_at, device_make_model, lat, lng, exif_raw) VALUES (?, ?, ?, 'image', ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$album_id, $user_id, $new_filename, $description, $taken_at, $device, $lat, $lng, $exif_json]);
    $current_media_id = $conn->lastInsertId();

    // Štítky zůstávají stejné...
    if (!empty($tags_raw) && $current_media_id) {
        $tagNames = explode(',', $tags_raw);
        foreach ($tagNames as $tagName) {
            $tagName = trim($tagName);
            if ($tagName === "") continue;
            $conn->prepare("INSERT IGNORE INTO tags (name) VALUES (?)")->execute([$tagName]);
            $tagId = $conn->query("SELECT id FROM tags WHERE name = " . $conn->quote($tagName))->fetchColumn();
            if ($tagId) $conn->prepare("INSERT IGNORE INTO media_tags (media_id, tag_id) VALUES (?, ?)")->execute([$current_media_id, $tagId]);
        }
    }

    ob_clean();
    echo json_encode(['status' => 'success', 'album_id' => $album_id]);
    exit();
}

$albums = $conn->query("SELECT * FROM albums ORDER BY name ASC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nahrávání fotek | Web Rodinna</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* OPRAVA OBŘÍCH FOTEK - GRID SYSTÉM */
        .preview-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }
        .preview-card {
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 12px;
            padding: 10px;
            position: relative;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        }
        .preview-card img {
            width: 100%;
            height: 150px;
            object-fit: cover;
            border-radius: 8px;
        }
        .preview-inputs { margin-top: 10px; }
        .preview-inputs textarea, .preview-inputs input {
            width: 100%;
            margin-bottom: 5px;
            font-size: 0.85rem;
            padding: 5px;
            border: 1px solid #eee;
            border-radius: 4px;
        }
        .remove-btn {
            position: absolute;
            top: 5px; right: 5px;
            background: rgba(231, 76, 60, 0.9);
            color: white; border: none;
            width: 25px; height: 25px;
            border-radius: 50%; cursor: pointer;
        }
        .upload-drop-area {
            border: 2px dashed #3498db;
            padding: 40px; text-align: center;
            border-radius: 15px; cursor: pointer;
            transition: 0.3s; background: #f8f9fa;
        }
        .upload-drop-area.drag-over { background: #e3f2fd; border-color: #2980b9; }
        .exif-anonymize-box {
            padding: 15px;
            border-radius: 10px;
            margin: 15px 0;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .switch { position: relative; display: inline-block; width: 44px; height: 22px; flex-shrink: 0; }
        .switch input { opacity: 0; width: 0; height: 0; }
        .slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #ccc; transition: .4s; border-radius: 34px; }
        .slider:before { position: absolute; content: ""; height: 16px; width: 16px; left: 3px; bottom: 3px; background-color: white; transition: .4s; border-radius: 50%; }
        input:checked + .slider { background-color: #3498db; }
        input:checked + .slider:before { transform: translateX(22px); }
    </style>
</head>
<body>
    <div class="container"><?php include "includes/navbar.php"; ?></div>

    <div class="container main-content">
        <header class="gallery-header">
            <h1><i class="fas fa-cloud-upload-alt"></i> Nahrávání fotek</h1>
        </header>

        <div class="form-container">
            <div id="drop-zone" class="upload-drop-area">
                <i class="fas fa-images" style="font-size: 3rem; color: #3498db;"></i>
                <p>Přetáhni fotky sem nebo klikni pro výběr</p>
                <input type="file" id="image-input" multiple style="display:none;" accept="image/jpeg,image/png">
            </div>

            <div class="exif-anonymize-box">
                <label class="switch">
                    <input type="checkbox" id="remove_exif_toggle">
                    <span class="slider"></span>
                </label>
                <div>
                    <strong>Odstranit metadata</strong><br>
                    <small style="color: #666;">Odstraní metadata <strong>všech</strong> fotek.</small>
                </div>
            </div>

            <div id="file-preview-container" class="preview-grid"></div>

            <form id="mainUploadForm">
                <div class="form-group" style="margin-top: 20px;">
                    <label>Kam to nahrát?</label>
                    <select name="album_id" id="album_select" class="form-control">
                        <option value="">-- Vyber existující album --</option>
                        <?php foreach($albums as $album): ?>
                            <option value="<?= $album['id'] ?>"><?= htmlspecialchars($album['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <div style="margin: 10px 0; text-align: center; color: #888;">NEBO</div>
                    <input type="text" name="new_album_name" id="new_album_input" placeholder="Vytvořit úplně nové album" class="form-control">
                </div>

                <div class="form-group">
                    <label>Společný popis (pro ty bez vlastního popisu):</label>
                    <textarea name="description" class="form-control" rows="2"></textarea>
                </div>

                <div class="form-group">
                    <label>Společné Štítky:</label>
                    <input type="text" name="tags" class="form-control" placeholder="např. Vánoce, 2024">
                </div>

                <button type="submit" id="submitBtn" class="btn btn-primary" style="width: 100%; padding: 15px; margin-top:20px;">
                    <i class="fas fa-upload"></i> Nahrát na web
                </button>
            </form>

            <div id="upload-status" style="display:none; margin-top: 20px;">
                <div id="progress-text">Připraveno...</div>
                <div class="progress-wrapper" style="width: 100%; background: #eee; height: 10px; border-radius: 5px; overflow: hidden; margin-top: 5px;">
                    <div id="progress-bar" style="width: 0%; height: 100%; background: #3498db; transition: 0.3s;"></div>
                </div>
            </div>
        </div>
    </div>

<script>
// 1. DEFINICE KONSTANT
const dropZone = document.getElementById('drop-zone');
const fileInput = document.getElementById('image-input');
const previewContainer = document.getElementById('file-preview-container');
const uploadForm = document.getElementById('mainUploadForm');
const progressBar = document.getElementById('progress-bar');
const progressText = document.getElementById('progress-text');
const uploadStatus = document.getElementById('upload-status');
const albumSelect = document.getElementById('album_select');
const newAlbumInput = document.getElementById('new_album_input');
const removeExifToggle = document.getElementById('remove_exif_toggle');

let selectedFiles = [];

// 2. OBSLUHA KLIKÁNÍ A PŘETAHOVÁNÍ
dropZone.addEventListener('click', () => fileInput.click());
dropZone.addEventListener('dragover', (e) => { e.preventDefault(); dropZone.classList.add('drag-over'); });
dropZone.addEventListener('dragleave', () => dropZone.classList.remove('drag-over'));
dropZone.addEventListener('drop', (e) => { 
    e.preventDefault(); 
    dropZone.classList.remove('drag-over'); 
    handleFiles(e.dataTransfer.files); 
});

fileInput.addEventListener('change', (e) => handleFiles(e.target.files));

function handleFiles(files) {
    Array.from(files).forEach(file => {
        if (!file.type.startsWith('image/')) return;
        
        const fileId = Date.now() + Math.random();
        selectedFiles.push({ id: fileId, file: file });

        const reader = new FileReader();
        reader.onload = (e) => {
            const div = document.createElement('div');
            div.className = 'preview-card';
            div.innerHTML = `
                <img src="${e.target.result}">
                <div class="preview-inputs">
                    <textarea placeholder="Popis této fotky..." class="item-desc" data-id="${fileId}"></textarea>
                    <input type="text" placeholder="Štítky této fotky..." class="item-tags" data-id="${fileId}">
                </div>
                <button type="button" class="remove-btn" onclick="removeFile(${fileId}, this)">×</button>
            `;
            previewContainer.appendChild(div);
        };
        reader.readAsDataURL(file);
    });
}

function removeFile(id, btn) {
    selectedFiles = selectedFiles.filter(f => f.id !== id);
    btn.parentElement.remove();
}

// 3. ODESÍLÁNÍ NA SERVER
uploadForm.onsubmit = async function(e) {
    e.preventDefault();
    if (selectedFiles.length === 0) return alert("Vyberte fotky!");

    // Zjistíme stav anonymizace jednou pro celou dávku
    const anonymize = removeExifToggle.checked ? "true" : "false";

    uploadStatus.style.display = 'block';
    let currentAlbumId = albumSelect.value;
    const newAlbumName = newAlbumInput.value;
    const globalDesc = document.querySelector('textarea[name="description"]').value;
    const globalTags = document.querySelector('input[name="tags"]').value;

    for (let i = 0; i < selectedFiles.length; i++) {
        const item = selectedFiles[i]; // Tady se korektně definuje 'item'
        
        const localDesc = document.querySelector(`.item-desc[data-id="${item.id}"]`).value;
        const localTags = document.querySelector(`.item-tags[data-id="${item.id}"]`).value;

        const formData = new FormData();
        formData.append('image', item.file);
        formData.append('description', localDesc || globalDesc);
        formData.append('tags', localTags || globalTags);
        formData.append('remove_exif', anonymize);

        if (newAlbumName !== "" && i === 0) {
            formData.append('new_album_name', newAlbumName);
        } else {
            formData.append('album_id', currentAlbumId);
        }

        progressText.innerText = `Nahrávám fotku ${i + 1} z ${selectedFiles.length}...`;
        
        try {
            const response = await uploadSingleFile(formData);
            const res = JSON.parse(response);
            if (res.status === 'success' && res.album_id) {
                currentAlbumId = res.album_id;
            }
        } catch (err) { 
            console.error("Chyba při nahrávání:", err); 
        }
    }
    window.location.href = 'galerie.php?album_id=' + currentAlbumId;
};

function uploadSingleFile(formData) {
    return new Promise((resolve, reject) => {
        const xhr = new XMLHttpRequest();
        xhr.open('POST', window.location.href, true);
        xhr.upload.onprogress = (e) => {
            if (e.lengthComputable) {
                const percent = (e.loaded / e.total * 100);
                progressBar.style.width = percent + '%';
            }
        };
        xhr.onload = () => resolve(xhr.responseText);
        xhr.onerror = () => reject("Network Error");
        xhr.send(formData);
    });
}

// 4. LOGIKA ALB (PŘEPÍNÁNÍ)
albumSelect.addEventListener('change', () => {
    if (albumSelect.value !== "") {
        newAlbumInput.value = "";
        newAlbumInput.disabled = true;
        newAlbumInput.style.opacity = "0.5";
    } else {
        newAlbumInput.disabled = false;
        newAlbumInput.style.opacity = "1";
    }
});

newAlbumInput.addEventListener('input', () => {
    if (newAlbumInput.value !== "") {
        albumSelect.value = "";
        albumSelect.disabled = true;
        albumSelect.style.opacity = "0.5";
    } else {
        albumSelect.disabled = false;
        albumSelect.style.opacity = "1";
    }
});
</script>
</body>
</html>