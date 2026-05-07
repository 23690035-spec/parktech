
Copiar

/* ======================================================
   UI.JS — Interfaz ParkTech CV
   ====================================================== */
 
function render404() {
    document.getElementById("content").innerHTML = `
        <div class="not-found">
            <h2>404</h2>
            <p>Página no encontrada.</p>
        </div>
    `;
}
 
function renderMenu() {
    const nav = document.getElementById("nav-menu");
    if (!nav) return;
 
    if (!window.usuarioActual) {
        nav.innerHTML = `
            <button onclick="navigateTo('login')">Ingresar</button>
        `;
    } else {
        const nombre = window.usuarioActual.name.split(' ')[0];
        nav.innerHTML = `
            <span class="user-greeting">// ${nombre}</span>
            <div class="nav-divider"></div>
            <button onclick="navigateTo('map')">Mapa</button>
            <button onclick="navigateTo('dashboard')">Reservas</button>
            <div class="nav-divider"></div>
            <button onclick="logout()" class="btn-logout">Salir</button>
        `;
    }
}
 
function renderLogin() {
    renderMenu();
    document.getElementById("content").innerHTML = `
        <div class="auth-wrapper">
            <div class="login-box">
                <p class="auth-eyebrow">Acceso al sistema</p>
                <h2 class="auth-title">Iniciar sesión</h2>
                <p class="auth-subtitle">Ingresa tus credenciales para continuar</p>
 
                <label class="input-label" for="email">Correo electrónico</label>
                <input type="email" id="email" placeholder="usuario@correo.com" class="input-field">
 
                <label class="input-label" for="password">Contraseña</label>
                <input type="password" id="password" placeholder="••••••••" class="input-field"
                    onkeydown="if(event.key==='Enter') login()">
 
                <button onclick="login()" class="btn-primary">Ingresar</button>
 
                <p class="auth-footer">
                    ¿No tienes cuenta?&nbsp;
                    <span onclick="navigateTo('register')">Crear cuenta</span>
                </p>
            </div>
        </div>
    `;
}
 
function renderRegister() {
    renderMenu();
    document.getElementById("content").innerHTML = `
        <div class="auth-wrapper">
            <div class="register-box">
                <p class="auth-eyebrow">Nuevo usuario</p>
                <h2 class="auth-title">Crear cuenta</h2>
                <p class="auth-subtitle">Completa los datos para registrarte</p>
 
                <label class="input-label" for="name">Nombre completo</label>
                <input id="name" type="text" placeholder="Tu nombre" class="input-field">
 
                <label class="input-label" for="email">Correo electrónico</label>
                <input id="email" type="email" placeholder="usuario@correo.com" class="input-field">
 
                <label class="input-label" for="password">Contraseña</label>
                <input id="password" type="password" placeholder="Mínimo 8 caracteres" class="input-field"
                    onkeydown="if(event.key==='Enter') registerUser()">
 
                <button onclick="registerUser()" class="btn-primary">Registrarse</button>
 
                <p class="auth-footer">
                    ¿Ya tienes cuenta?&nbsp;
                    <span onclick="navigateTo('login')">Inicia sesión</span>
                </p>
            </div>
        </div>
    `;
}
 
function renderDashboard() {
    renderMenu();
    document.getElementById("content").innerHTML = `
        <div class="dashboard-wrapper">
            <div class="dashboard-header">
                <p class="dashboard-eyebrow">Panel de control</p>
                <h2 class="dashboard-title">Mis Reservas</h2>
            </div>
 
            <div id="reservation-info"></div>
 
            <p class="section-label">Disponibilidad en tiempo real</p>
 
            <div id="spots-list" class="spots-grid">
                <div class="loading-full">
                    <div class="loading"></div>
                    Cargando lugares...
                </div>
            </div>
        </div>
    `;
    fetchSpotsAndReservations();
}
 
function updateSpotsUI(spots) {
    const spotsList = document.getElementById("spots-list");
    if (!spotsList) return;
 
    if (!spots || spots.length === 0) {
        spotsList.innerHTML = `
            <div class="loading-full" style="color: var(--muted);">
                No hay lugares disponibles.
            </div>
        `;
        return;
    }
 
    spotsList.innerHTML = spots.map(s => {
        const isReservedByMe = s.status === "reservado" && s.user_id == window.usuarioActual.id;
        const isAvailable    = s.status === "disponible";
 
        let cardClass, statusText, icon, actionBtn;
 
        if (isReservedByMe) {
            cardClass  = "reserved";
            statusText = "Tu reserva";
            icon       = "🔒";
            actionBtn  = `<button onclick="cancelar(${s.id})" class="btn-danger">✕ Cancelar reserva</button>`;
        } else if (isAvailable) {
            cardClass  = "available";
            statusText = "Disponible";
            icon       = "🅿";
            actionBtn  = `<button onclick="reservar(${s.id})" class="btn-reserve">+ Reservar</button>`;
        } else {
            cardClass  = "occupied";
            statusText = "Ocupado";
            icon       = "🚫";
            actionBtn  = `<div style="font-family:'Space Mono',monospace;font-size:0.65rem;color:var(--red);letter-spacing:0.1em;text-transform:uppercase;text-align:center;padding:0.55rem 0;opacity:0.7;">No disponible</div>`;
        }
 
        return `
            <div class="spot-card ${cardClass}">
                <div>
                    <div class="spot-top">
                        <span class="spot-number">#${String(s.id).padStart(2, '0')}</span>
                        <span class="spot-icon">${icon}</span>
                    </div>
                    <div class="spot-status-label">${statusText}</div>
                </div>
                <div>${actionBtn}</div>
            </div>
        `;
    }).join("");
 
    // Banner de reserva activa
    const myReservation = spots.find(s => s.user_id == window.usuarioActual.id && s.status === "reservado");
    const infoDiv = document.getElementById("reservation-info");
    if (infoDiv) {
        if (myReservation) {
            infoDiv.innerHTML = `
                <div class="active-reservation-banner">
                    <span class="banner-icon">⚡</span>
                    <div>
                        <div style="font-weight:700;letter-spacing:0.05em;">Reserva activa — Lugar #${String(myReservation.id).padStart(2,'0')}</div>
                        <div style="opacity:0.6;margin-top:2px;font-size:0.65rem;">Tu lugar está apartado y listo.</div>
                    </div>
                </div>
            `;
        } else {
            infoDiv.innerHTML = "";
        }
    }
}
 