<?php
require_once "db.php"; // Zde už máš require na Parsedown a funkci formatText
if (isset($_POST['text'])) {
    echo formatText($_POST['text']);
}