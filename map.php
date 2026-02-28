<?php
require "includes/auth.php";
require "includes/db.php";
include "includes/auth_check.php";
checkAuth();

?>

<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mapa fotek | Rodinný web</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.Default.css" />
    <script src="https://unpkg.com/leaflet.markercluster/dist/leaflet.markercluster.js"></script>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="css/mapa.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
    </style>
</head>
<body class="sticky-nav-body">

<?php include "includes/navbar.php"; ?>

<div class="container main-content">
    <header class="gallery-header">
        <div class="header-text">
            <h1><i class="fas fa-map-marked-alt"></i> Mapa našich Fotek</h1>
            <p>Heatmapa míst, kde byly fotky vyfoceny.</p>
        </div>
    </header>

    <div id="map"></div>

    <div class="map-stats">
        <div class="stat-card">
            <i class="fas fa-camera-retro" style="font-size: 2rem; color: #3498db;"></i>
            <h3 id="photo-count">0</h3>
            <p>Lokalizovaných médií</p>
        </div>
        <div class="stat-card">
            <i class="fas fa-fire" style="font-size: 2rem; color: #e74c3c;"></i>
            <h3>Nejteplejší barva</h3>
            <p>Označuje nejčastější místa</p>
        </div>
    </div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet.heat/dist/leaflet-heat.js"></script>
<script src="https://unpkg.com/leaflet.markercluster@1.4.1/dist/leaflet.markercluster.js"></script>

<script>
// 1. Inicializace mapy (střed nastavíme na ČR)
// 1. Inicializace mapy (bez defaultního zoomu vlevo)
const map = L.map('map', { zoomControl: false }).setView([49.8175, 15.4730], 7);

// 2. Stadia Maps (Alidade Smooth) - moderní vzhled
L.tileLayer('https://tiles.stadiamaps.com/tiles/alidade_smooth/{z}/{x}/{y}{r}.png', {
    maxZoom: 20,
    attribution: '© Stadia Maps, © OpenStreetMap contributors'
}).addTo(map);

// Přidání zoomu doprava dolů
L.control.zoom({ position: 'bottomright' }).addTo(map);

// 3. Načtení dat
fetch('mapa_data.php')
    .then(response => response.json())
    .then(data => {
        document.getElementById('photo-count').innerText = data.length;

        // Vytvoření clusteru (shluků)
        const markers = L.markerClusterGroup({
            showCoverageOnHover: false,
            maxClusterRadius: 50
        });

        const heatPoints = [];

        data.forEach(p => {
            const lat = parseFloat(p.lat);
            const lng = parseFloat(p.lng);
            heatPoints.push([lat, lng, 0.5]);

            const photoIcon = L.divIcon({
                className: 'custom-photo-marker',
                html: `<img src="uploads/images/${p.filename}">`,
                iconSize: [50, 62],
                iconAnchor: [25, 62],
                popupAnchor: [0, -65]
            });

            const marker = L.marker([lat, lng], { icon: photoIcon })
                .bindPopup(`
                    <div style="text-align:center; width:200px;">
                        <img src="uploads/images/${p.filename}" style="width:100%; border-radius:5px;">
                        <br><br>
                        <a href="galerie.php?album_id=${p.album_id}#photo-${p.id}" style="color:#3498db; text-decoration:none; font-weight:bold;">
                            <i class="fas fa-eye"></i> Přejít k fotce
                        </a>
                    </div>
                `);

            markers.addLayer(marker);
        });

        // Přidání shluků na mapu
        map.addLayer(markers);

        // Heatmapa pod shluky
        L.heatLayer(heatPoints, {radius: 20, blur: 15, opacity: 0.3}).addTo(map);

        if(data.length > 0) {
            const bounds = new L.LatLngBounds(data.map(p => [p.lat, p.lng]));
            map.fitBounds(bounds, { padding: [50, 50], maxZoom: 15 });
        }
    })
    .catch(err => console.error("Chyba:", err));
</script>

<?php include "includes/footer.php"; ?>
</body>
</html>