<?php $this->extend("layout/layout") ?>
<?php $this->section("content"); ?>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="">

<div class="row justify-content-center">
    <div class="col-12 m-1">
        <h1 class="h3">Earthquakes M4.5+ &ndash; past 30 days</h1>
        <p class="text-muted mb-2">
            Data: <a href="https://earthquake.usgs.gov/earthquakes/feed/" target="_blank" rel="noopener">USGS Earthquake Hazards Program</a>
            <span id="eq-status" class="ms-2"></span>
        </p>
        <div id="eq-map" style="height: 500px;" class="rounded border"></div>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-12 m-1">
        <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
            <table class="table table-sm table-hover align-middle">
                <thead class="table-light sticky-top">
                    <tr>
                        <th>Time</th>
                        <th>Mag</th>
                        <th>Location</th>
                        <th>Depth (km)</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="eq-rows">
                    <tr><td colspan="5" class="text-center text-muted">Loading&hellip;</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
<script>
    const FEED = 'https://earthquake.usgs.gov/earthquakes/feed/v1.0/summary/4.5_month.geojson';

    const map = L.map('eq-map', { worldCopyJump: true }).setView([20, 0], 2);
    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);

    const escapeHtml = (s) => String(s ?? '').replace(/[&<>"']/g, c => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
    }[c]));

    // Barva podle magnitudy: 4.5 = žlutá ... 7+ = tmavě červená
    const colorFor = (mag) => {
        const t = Math.min(Math.max((mag - 4.5) / 2.5, 0), 1);
        return 'hsl(' + (55 - t * 55) + ', 90%, ' + (55 - t * 15) + '%)';
    };
    const radiusFor = (mag) => Math.pow(mag, 2) / 3;

    // Legenda
    const legend = L.control({ position: 'bottomright' });
    legend.onAdd = function () {
        const div = L.DomUtil.create('div', 'bg-white p-2 rounded border');
        div.innerHTML =
            '<div class="small fw-bold mb-1">Magnitude</div>' +
            '<div style="width:140px;height:12px;background:linear-gradient(to right, hsl(55,90%,55%), hsl(27,90%,47%), hsl(0,90%,40%));"></div>' +
            '<div class="d-flex justify-content-between small"><span>4.5</span><span>7+</span></div>';
        return div;
    };
    legend.addTo(map);

    const status = document.getElementById('eq-status');
    const tbody  = document.getElementById('eq-rows');

    fetch(FEED)
        .then(r => {
            if (!r.ok) throw new Error('HTTP ' + r.status);
            return r.json();
        })
        .then(data => {
            const quakes = data.features
                .filter(f => f.geometry && f.properties.mag !== null)
                .sort((a, b) => b.properties.time - a.properties.time);

            status.textContent = quakes.length + ' earthquakes';

            // Kreslíme od nejslabších, ať silné zůstanou navrchu
            const markerById = {};
            [...quakes].sort((a, b) => a.properties.mag - b.properties.mag).forEach(f => {
                const [lng, lat, depth] = f.geometry.coordinates;
                const p = f.properties;
                markerById[f.id] = L.circleMarker([lat, lng], {
                    radius: radiusFor(p.mag),
                    color: '#333',
                    weight: 1,
                    fillColor: colorFor(p.mag),
                    fillOpacity: 0.75
                }).addTo(map).bindPopup(
                    '<strong>M ' + p.mag.toFixed(1) + '</strong><br>' +
                    escapeHtml(p.place) + '<br>' +
                    '<small>' + new Date(p.time).toLocaleString() + ' &middot; depth ' + Number(depth).toFixed(0) + ' km</small><br>' +
                    '<a href="' + escapeHtml(p.url) + '" target="_blank" rel="noopener">USGS details</a>'
                );
            });

            tbody.innerHTML = '';
            quakes.forEach(f => {
                const p = f.properties;
                const depth = f.geometry.coordinates[2];
                const tr = document.createElement('tr');
                tr.style.cursor = 'pointer';
                tr.innerHTML =
                    '<td>' + escapeHtml(new Date(p.time).toLocaleString()) + '</td>' +
                    '<td><span class="badge text-dark" style="background:' + colorFor(p.mag) + '">' + p.mag.toFixed(1) + '</span></td>' +
                    '<td>' + escapeHtml(p.place) + '</td>' +
                    '<td>' + Number(depth).toFixed(0) + '</td>' +
                    '<td><a href="' + escapeHtml(p.url) + '" target="_blank" rel="noopener">USGS</a></td>';
                tr.addEventListener('click', (e) => {
                    if (e.target.tagName === 'A') return;
                    const m = markerById[f.id];
                    map.flyTo(m.getLatLng(), Math.max(map.getZoom(), 5));
                    m.openPopup();
                    document.getElementById('eq-map').scrollIntoView({ behavior: 'smooth', block: 'center' });
                });
                tbody.appendChild(tr);
            });
        })
        .catch(err => {
            status.textContent = '';
            tbody.innerHTML = '<tr><td colspan="5" class="text-center text-danger">Could not load USGS data (' + escapeHtml(err.message) + ').</td></tr>';
        });
</script>

<?php $this->endsection() ?>