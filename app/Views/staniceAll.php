<?php $this->extend("layout/layout") ?>
<?php $this->section("content"); ?>

<?php
// Podklad pro mapu: jen stanice s platnými souřadnicemi
$mapStations = [];
foreach ($stations as $row) {
    if (! is_numeric($row['geo_latitude']) || ! is_numeric($row['geo_longtitude'])) {
        continue;
    }
    $mapStations[] = [
        'name'   => $row['place'],
        'lat'    => (float) $row['geo_latitude'],
        'lng'    => (float) $row['geo_longtitude'],
        'height' => $row['height'],
        'url'    => site_url('stanice/' . $row['S_ID']),
    ];
}
?>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="">

<div class="row justify-content-center">
    <div class="col-12 col-md-5 col-lg-8  m-1">
        <div id="map" style="height: 450px;" class="rounded border"></div>
    </div>
</div>

<div class="row justify-content-center">
<?php foreach ($stations as $row): ?>
    <div class="card col-12 col-md-8 col-lg-3 m-1">
        <div class="card-body">
            <h5 class="card-title">
                <?= anchor('stanice/' . $row['S_ID'], esc($row['place'])) ?>
            </h5>
            <p class="card-text">Elevation: <?= esc($row['height']) ?> m n. m.</p>
            <p class="card-text">Latitude: <?= esc($row['geo_latitude']) ?>°</p>
            <p class="card-text">Longitude: <?= esc($row['geo_longtitude']) ?>°</p>
            <p class="card-text">
                <?= img([
                    'src'   => base_url('obrazky/vlajky/' . $row['flag']),
                    'class' => 'img-fluid',
                    'alt'   => esc($row['place']),
                ]) ?>
            </p>
        </div>
    </div>
<?php endforeach; ?>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
<script>
    const stations = <?= json_encode($mapStations, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;

    const map = L.map('map').setView([50, 10], 5);

    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);

    const escapeHtml = (s) => String(s).replace(/[&<>"']/g, c => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
    }[c]));

    // Barva podle nadmořské výšky: nízko = zelená, vysoko = červená/fialová
    const heights = stations.map(s => parseFloat(s.height)).filter(h => !isNaN(h));
    const minH = Math.min(...heights);
    const maxH = Math.max(...heights);

    const colorFor = (h) => {
        h = parseFloat(h);
        if (isNaN(h) || maxH === minH) return 'hsl(0, 0%, 50%)';
        const t = (h - minH) / (maxH - minH);   // 0 = nejnižší, 1 = nejvyšší
        const hue = 120 - t * 120;              // 120 (zelená) -> 0 (červená)
        return 'hsl(' + hue + ', 80%, 45%)';
    };

    const markers = stations.map(s =>
        L.circleMarker([s.lat, s.lng], {
            radius: 9,
            color: '#333',
            weight: 1,
            fillColor: colorFor(s.height),
            fillOpacity: 0.9
        })
            .addTo(map)
            .bindPopup(
                '<strong><a href="' + escapeHtml(s.url) + '">' + escapeHtml(s.name) + '</a></strong><br>' +
                escapeHtml(s.height) + ' m n. m.'
            )
    );

    // Legenda s barevným přechodem
    if (heights.length > 0) {
        const legend = L.control({ position: 'bottomright' });
        legend.onAdd = function () {
            const div = L.DomUtil.create('div', 'bg-white p-2 rounded border');
            div.innerHTML =
                '<div class="small fw-bold mb-1">Nadmořská výška</div>' +
                '<div style="width:140px;height:12px;background:linear-gradient(to right, hsl(120,80%,45%), hsl(60,80%,45%), hsl(0,80%,45%));"></div>' +
                '<div class="d-flex justify-content-between small">' +
                '<span>' + Math.round(minH) + ' m</span><span>' + Math.round(maxH) + ' m</span></div>';
            return div;
        };
        legend.addTo(map);
    }

    if (markers.length > 0) {
        map.fitBounds(L.featureGroup(markers).getBounds().pad(0.1));
    }
</script>

<?php $this->endsection() ?>