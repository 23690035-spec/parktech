
Copiar

function renderMap() {
    const content = document.getElementById("content");
    renderMenu();
 
    content.innerHTML = `
        <div class="map-wrapper">
            <div class="map-header">
                <div>
                    <p class="map-eyebrow">Ciudad Valles, SLP</p>
                    <h2 class="map-title">Mapa de Estacionamientos</h2>
                </div>
                <div class="map-stats">
                    <div class="stat-chip"><span class="dot green"></span> Disponibles</div>
                    <div class="stat-chip"><span class="dot amber"></span> Reservados</div>
                    <div class="stat-chip"><span class="dot red"></span> Ocupados</div>
                </div>
            </div>
            <div class="map-container">
                <div id="map"></div>
            </div>
        </div>
    `;
 
    setTimeout(() => {
        try {
            if (window.mapInstance) {
                window.mapInstance.remove();
                window.mapInstance = null;
            }
 
            window.mapInstance = L.map('map', {
                zoomControl: true,
                attributionControl: true,
            }).setView([21.9833, -99.0167], 15);
 
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© <a href="https://openstreetmap.org">OpenStreetMap</a>',
                maxZoom: 19,
            }).addTo(window.mapInstance);
 
            // Invalidar tamaño por si el contenedor no estaba listo
            setTimeout(() => window.mapInstance.invalidateSize(), 100);
 
            console.log("✅ Mapa cargado");
        } catch (e) {
            console.error("Error cargando mapa:", e);
            document.querySelector('.map-container').innerHTML = `
                <div style="display:flex;align-items:center;justify-content:center;height:100%;
                    font-family:'Space Mono',monospace;font-size:0.75rem;color:var(--red);">
                    Error al cargar el mapa. Verifica tu conexión.
                </div>
            `;
        }
    }, 200);
}