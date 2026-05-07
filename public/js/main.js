// ==================== MAIN.JS CORREGIDO ====================
window.usuarioActual = JSON.parse(localStorage.getItem('user')) || null;
function navigateTo(page) {
    console.log("🚀 Navegando a:", page);
    const contentDiv = document.getElementById("content");
    if (!contentDiv) return;

    // Siempre limpiamos primero
    contentDiv.innerHTML = "";

    if (window.mapInstance) {
        window.mapInstance.remove();
        window.mapInstance = null;
    }

    if (!window.usuarioActual) {
        // Usuario NO logueado
        if (page === "register") renderRegister();
        else renderLogin();
    } else {
        // Usuario logueado
        renderMenu();

        if (page === "map" || !page) {
            renderMap();
        } else if (page === "dashboard") {
            renderDashboard();
        } else if (page === "register") {
            renderRegister();
        }
    }
}

// Función de arranque
function bootstrap() {
    console.log("✅ App iniciada - usuario:", window.usuarioActual ? window.usuarioActual.name : "ninguno");
    if (window.usuarioActual) {
        navigateTo('map');
    } else {
        navigateTo('login');
    }
}

document.addEventListener('DOMContentLoaded', bootstrap);

function logout() {
    localStorage.removeItem('user');
    window.usuarioActual = null;
    location.reload(); // recarga limpia
}