let users = [];

document.getElementById('registerForm').addEventListener('submit', function(event) {
    event.preventDefault();
    
    let fname = document.getElementById('fname').value;
    let email = document.getElementById('email').value;
    let password = document.getElementById('password').value;

    let user = {
        fname: fname,
        email: email,
        password: password
    };

    users.push(user);

    document.getElementById('fname').value = '';
    document.getElementById('email').value = '';
    document.getElementById('password').value = '';

    alert('Registrierung erfolgreich!');

    redirectToDashboard();
});

document.getElementById('loginForm').addEventListener('submit', function(event) {
    event.preventDefault();

    let loginEmail = document.getElementById('loginEmail').value;
    let loginPassword = document.getElementById('loginPassword').value;

    let loggedInUser = users.find(user => user.email === loginEmail && user.password === loginPassword);

    if (loggedInUser) {
        alert('Login erfolgreich!');
        redirectToDashboard();
    } else {
        alert('Login fehlgeschlagen. Überprüfen Sie Ihre Email und Passwort.');
    }

    document.getElementById('loginEmail').value = '';
    document.getElementById('loginPassword').value = '';
});

function redirectToDashboard() {
    window.location.href = 'dashboard.html';
}