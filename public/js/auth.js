// ==================== AUTH.JS CORREGIDO ====================
function login() {
    const email = document.getElementById("email").value;
    const password = document.getElementById("password").value;

    if (!email || !password) {
        alert("Por favor completa todos los campos");
        return;
    }

    fetch("/api/login.php", {
        method: "POST",
        body: new URLSearchParams({ email, password })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            // ✅ GUARDAMOS EN localStorage Y EN WINDOW
            window.usuarioActual = data.user;
            localStorage.setItem('user', JSON.stringify(data.user));
            
            navigateTo("map");        // ← cambiado de "map.js"
        } else {
            alert(data.error || "Credenciales incorrectas");
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert("Error de conexión con el servidor");
    });
}

function registerUser() {
    const name = document.getElementById("name").value;
    const email = document.getElementById("email").value;
    const password = document.getElementById("password").value;

    fetch("/api/register.php", {
        method: "POST",
        body: new URLSearchParams({ name, email, password })
    })
    .then(res => res.json())
    .then(data => {
        if (!data.success) {
            alert(data.error || "Error al registrar");
            return;
        }
        alert("Registro exitoso. Ahora inicia sesión.");
        navigateTo("login");
    });
}

// ==================== FUNCIONES DE RESERVAS (corregidas) ====================
function fetchSpotsAndReservations() {
    fetch("/api/getSpots.php") 
        .then(res => res.json())
        .then(spots => updateSpotsUI(spots))
        .catch(err => console.error("Error cargando spots:", err));
}

function reservar(idLugar) {
    if (!window.usuarioActual) return;
    const plate = prompt("Ingresa tu número de placa:");
    if (!plate) return;
    
    fetch("/api/reserve.php", {
        method: "POST",
        body: new URLSearchParams({ 
            spot_id: idLugar, 
            user_id: window.usuarioActual.id, 
            plate: plate 
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) renderDashboard();
        else alert(data.error);
    });
}

function cancelar(idLugar) {
    if (!confirm("¿Cancelar reserva?")) return;
    fetch("/api/cancel.php", {
        method: "POST",
        body: new URLSearchParams({ spot_id: idLugar })
    })
    .then(res => res.json())
    .then(() => renderDashboard());
}