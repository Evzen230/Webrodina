<head>
</head>
<!-- Google tag (gtag.js) 
<script async src="https://www.googletagmanager.com/gtag/js?id=G-GGN9Y19FYQ"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'G-GGN9Y19FYQ');
</script>
-->

<?php   
require "includes/db.php";
require "includes/auth.php";
include "includes/auth_check.php";
checkAuth();
// Zde si přidej kontrolu, zda je uživatel admin

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $version = $_POST['version'];
    $title = $_POST['title'];
    $type = $_POST['type'];
    $description = $_POST['description'];

    $stmt = $conn->prepare("INSERT INTO dev_log (version, title, type, description) VALUES (?, ?, ?, ?)");
    $stmt->execute([$version, $title, $type, $description]);
    header("Location: devlog.php");
    exit;
}
?>

<div class="admin-container">
    <h1>Nový záznam do DevLogu</h1>
    
    <form method="POST" class="devlog-form">
        <div class="form-row">
            <input type="text" name="version" placeholder="Verze (např. v2.1)" required>
            <select name="type">
                <option value="minor">Minor Update</option>
                <option value="major">Major Update</option>
                <option value="hotfix">Hotfix</option>
            </select>
        </div>
        
        <input type="text" name="title" placeholder="Název aktualizace" required class="full-width">

        <div class="editor-wrapper">
            <div class="editor-column">
                <label>Popis (Markdown):</label>
                <textarea name="description" id="markdown-input" placeholder="Zde piš změny..."></textarea>
            </div>
            <div class="editor-column">
                <label>Živý náhled:</label>
                <div id="markdown-preview" class="preview-box"></div>
            </div>
        </div>

        <button type="submit" class="btn-save">Vydat aktualizaci</button>
    </form>
</div>

<style>
    .devlog-form { display: flex; flex-direction: column; gap: 15px; }
    .form-row { display: flex; gap: 10px; }
    .full-width { padding: 10px; border-radius: 5px; border: 1px solid #ddd; }
    
    .editor-wrapper { 
        display: grid; 
        grid-template-columns: 1fr 1fr; 
        gap: 20px; 
        min-height: 300px;
    }
    
    textarea { 
        width: 100%; 
        height: 100%; 
        padding: 10px; 
        font-family: monospace; 
        border: 1px solid #ddd;
        border-radius: 5px;
    }

    .preview-box { 
        border: 1px solid #eee; 
        padding: 15px; 
        background: #fdfdfd; 
        border-radius: 5px;
        height: 100%;
        overflow-y: auto;
    }

    .btn-save { 
        background: #28a745; 
        color: white; 
        padding: 12px; 
        border: none; 
        border-radius: 5px; 
        cursor: pointer;
        font-weight: bold;
    }
</style>

<script>
    const input = document.getElementById('markdown-input');
    const preview = document.getElementById('markdown-preview');

    input.addEventListener('input', function() {
        const formData = new FormData();
        formData.append('text', this.value);

        fetch('includes/preview_markdown.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(html => {
            preview.innerHTML = html;
        });
    });
</script>