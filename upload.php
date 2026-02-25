<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/luminous-lightbox/2.4.0/luminous-basic.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/luminous-lightbox/2.4.0/luminous.js"></script>
    <srcript src="includes/respons.js"></script>
</head>
<!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-GGN9Y19FYQ"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'G-GGN9Y19FYQ');
</script>

<?php
require "includes/auth.php";
require "includes/db.php";
include "includes/auth_check.php";
checkAuth();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $album_id = $_POST['album_id'];
    $new_album_name = trim($_POST['new_album_name']);
    $description = $_POST['description'];

    // 1. Pokud uživatel napsal název nového alba, vytvoříme ho
    if (!empty($new_album_name)) {
        $stmt = $conn->prepare("INSERT INTO albums (name) VALUES (?)");
        $stmt->execute([$new_album_name]);
        $album_id = $conn->lastInsertId(); // Nastavíme ID na právě vytvořené album
    }

    if (empty($album_id)) {
        die("Musíš vybrat nebo vytvořit album!");
    }
    if (!empty($_POST['tags'])) {
    // 1. Rozdělení textu podle čárek a odstranění mezer
    $tagNames = explode(',', $_POST['tags']);
    
        foreach ($tagNames as $tagName) {
            $tagName = trim($tagName);
            if ($tagName === "") continue;

            // 2. Zjistíme, jestli už štítek v DB existuje, nebo ho vytvoříme
            $stmt = $conn->prepare("INSERT IGNORE INTO tags (name) VALUES (?)");
            $stmt->execute([$tagName]);
            
            // Získáme ID štítku (buď nové, nebo existující)
            $stmt = $conn->prepare("SELECT id FROM tags WHERE name = ?");
            $stmt->execute([$tagName]);
            $tagId = $stmt->fetchColumn();

            // 3. Propojíme fotku se štítkem
            $stmt = $conn->prepare("INSERT IGNORE INTO media_tags (media_id, tag_id) VALUES (?, ?)");
            $stmt->execute([$media_id, $tagId]);
        }
    }
    // 2. Zpracování nahrávaných souborů
    foreach ($_FILES['files']['tmp_name'] as $key => $tmp_name) {
        if ($_FILES['files']['error'][$key] !== UPLOAD_ERR_OK) continue;

        $file_name = $_FILES['files']['name'][$key];
        $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $new_name = uniqid() . ".jpg"; 
        $target = "uploads/images/" . $new_name;

        // --- KOMPRESE OBRÁZKU ---
        if (in_array($ext, ['jpg', 'jpeg', 'png'])) {
            $source = ($ext == 'png') ? @imagecreatefrompng($tmp_name) : @imagecreatefromjpeg($tmp_name);
            if (!$source) continue;

            list($width, $height) = getimagesize($tmp_name);
            
            // Maximální šířka 1200px
            $new_width = ($width > 1200) ? 1200 : $width;
            $new_height = ($height / $width) * $new_width;
            
            $tmp_img = imagecreatetruecolor($new_width, $new_height);
            
            // Průhlednost pro PNG
            imagealphablending($tmp_img, false);
            imagesavealpha($tmp_img, true);

            imagecopyresampled($tmp_img, $source, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
            
            // Uložení s kvalitou 75 % jako JPG pro úsporu místa
            imagejpeg($tmp_img, $target, 75);
            
            imagedestroy($source);
            imagedestroy($tmp_img);

            // Zápis do DB
            $stmt = $conn->prepare("INSERT INTO media (user_id, filename, type, description, album_id) VALUES (?, ?, 'image', ?, ?)");
            $stmt->execute([$_SESSION["user_id"], $new_name, $description, $album_id]);
        }
    }
    header("Location: galerie.php?album_id=" . $album_id);
    exit();
}

$albums = $conn->query("SELECT * FROM albums ORDER BY name ASC")->fetchAll();
?>

<div class="container">
        <?php include "includes/navbar.php"; ?> 
</div>

<div class="container main-content">
    <header class="gallery-header">
        <h1>Nahrávání fotek</h1>
    </header>

    <div class="form-container">
        <div class="form-group">
            <label><i class="fas fa-file-image"></i> 1. Vyberte fotky:</label>
            <input type="file" name="images[]" id="image_input" accept="image/jpeg, image/png" multiple required>
            <p style="font-size: 0.8em; color: #666;">Můžete vybírat fotky postupně, seznam se bude doplňovat.</p>
        </div>

        <form action="upload_process.php" method="POST" enctype="multipart/form-data" id="mainUploadForm">
            <div id="preview_container" class="preview-list-vertical"></div>

            <div class="form-group" style="margin-top: 30px; border-top: 2px solid #eee; pt-3">
                <label>2. Kam to nahrát?</label>
                <select name="album_id" id="album_select" class="form-control">
                    <option value="">-- Vyber existující album --</option>
                    <?php foreach($albums as $album): ?>
                        <option value="<?= $album['id'] ?>"><?= htmlspecialchars($album['name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <div style="margin: 10px 0; text-align: center; color: #888; font-weight: bold;">NEBO</div>
                <input type="text" name="new_album_name" id="new_album_input" placeholder="Vytvořit úplně nové album" class="form-control">
            </div>

            <div class="form-group">
                <label><i class="fas fa-comment-alt"></i> 3. Společný popis (pro nevyplněné):</label>
                <textarea name="description" class="form-control" placeholder="Tento popis dostanou všechny fotky, které nebudou mít svůj vlastní." rows="2"></textarea>
            </div>

            <div class="form-group">
                <label for="tags">4. Společné Štítky:</label>
                <input type="text" name="tags" id="tags" class="form-control" placeholder="např. Vánoce, 2024">
            </div>

            <button type="submit" id="submitBtn" class="btn btn-primary" style="width: 100%; padding: 15px; font-size: 1.1em;">
                <i class="fas fa-cloud-upload-alt"></i> Nahrát vše na web
            </button>
        </form>
            <?php if (isset($_GET['error'])): ?>
                <div style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin-bottom: 20px; border: 1px solid #f5c6cb;">
                    <strong><i class="fas fa-exclamation-triangle"></i> Chyba:</strong> 
                    <?= htmlspecialchars($_GET['error']) ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['success'])): ?>
                <div style="background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin-bottom: 20px; border: 1px solid #c3e6cb;">
                    <strong><i class="fas fa-check-circle"></i> Hotovo!</strong> Fotky byly úspěšně uloženy.
                </div>
            <?php endif; ?>           
        <div id="upload-status" style="display:none; margin-top: 20px;">
            <p id="status-text">Odesílám fotky na server...</p>
            <div style="width: 100%; background: #eee; height: 10px; border-radius: 5px; overflow: hidden;">
                <div id="progress-bar" style="width: 0%; height: 100%; background: #3498db; transition: 0.3s;"></div>
            </div>
        </div>
    </div>
</div>

<script>
let selectedFiles = []; // Tady budeme držet naše soubory
const imageInput = document.getElementById('image_input');
const previewContainer = document.getElementById('preview_container');
const uploadForm = document.getElementById('mainUploadForm');

// 1. Logika pro alba (tvoje původní)
const albumSelect = document.getElementById('album_select');
const newAlbumInput = document.getElementById('new_album_input');

function toggleInputs() {
    if (albumSelect.value !== "") {
        newAlbumInput.disabled = true;
        newAlbumInput.style.backgroundColor = "#e9ecef";
    } else if (newAlbumInput.value.trim() !== "") {
        albumSelect.disabled = true;
        albumSelect.style.backgroundColor = "#e9ecef";
    } else {
        albumSelect.disabled = false;
        newAlbumInput.disabled = false;
        albumSelect.style.backgroundColor = "";
        newAlbumInput.style.backgroundColor = "";
    }
}
albumSelect.addEventListener('change', toggleInputs);
newAlbumInput.addEventListener('input', toggleInputs);

// 2. Logika přidávání fotek
imageInput.addEventListener('change', function() {
    const newFiles = Array.from(this.files);
    
    newFiles.forEach(file => {
    // Kontrola velikosti (64MB je limit, ale raději dejme 40MB jako rezervu)
    if (file.size > 40 * 1024 * 1024) {
        alert("Soubor " + file.name + " je příliš velký (nad 40MB). Zmenšete ho prosím před nahráním.");
        return; // Tento soubor nepřidáme
    }
        // Unikátní ID pro každý řádek
        const fileId = Math.random().toString(36).substr(2, 9);
        selectedFiles.push({ id: fileId, file: file });

        const reader = new FileReader();
        reader.onload = function(e) {
            const div = document.createElement('div');
            div.className = 'upload-row upload-item-row';
            div.id = 'row-' + fileId;
            div.innerHTML = `
                <img src="${e.target.result}">
                <input type="text" name="individual_descriptions[]" class="form-control" placeholder="Vlastní název/popis">
                <input type="text" name="individual_tags[]" class="form-control" placeholder="Vlastní štítky">
                <button type="button" class="btn btn-danger btn-sm" onclick="removeFile('${fileId}')">✕</button>
            `;
            previewContainer.appendChild(div);
        }
        reader.readAsDataURL(file);
    });
    
    // Vyčistíme input, aby šel vybrat stejný soubor znovu, pokud by ho uživatel smazal
    this.value = '';
});

// 3. Odstranění fotky ze seznamu
function removeFile(id) {
    selectedFiles = selectedFiles.filter(item => item.id !== id);
    const element = document.getElementById('row-' + id);
    if (element) element.remove();
}

// 4. Odeslání (přidání souborů do FormData)
uploadForm.onsubmit = function(e) {
    e.preventDefault(); // Zastavíme klasické odeslání

    if (selectedFiles.length === 0) {
        alert("Vyberte alespoň jednu fotku!");
        return;
    }

    document.getElementById('upload-status').style.display = 'block';
    const submitBtn = document.getElementById('submitBtn');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Nahrávám...';

    const formData = new FormData(this);
    
    // Ručně přidáme soubory z našeho pole selectedFiles
    selectedFiles.forEach(item => {
        formData.append('images[]', item.file);
    });

    // Odeslání pomocí AJAXu (aby fungoval progres bar a naše pole souborů)
    const xhr = new XMLHttpRequest();
    xhr.open('POST', 'upload_process.php', true);

    xhr.upload.onprogress = function(e) {
        if (e.lengthComputable) {
            const percent = (e.loaded / e.total) * 100;
            document.getElementById('progress-bar').style.width = percent + '%';
        }
    };

    xhr.onload = function() {
        if (xhr.status === 200) {
            // Přesměrování po úspěchu (můžeš upravit kam)
            window.location.href = 'galerie.php?msg=success';
        } else {
            alert('Chyba při nahrávání!');
            submitBtn.disabled = false;
        }
    };

    xhr.send(formData);
};
</script>