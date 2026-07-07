document.querySelectorAll('.toggle-password').forEach(function (boton) {
    boton.addEventListener('click', function () {
        var input = document.getElementById(boton.dataset.target);
        var mostrando = input.type === 'text';
        input.type = mostrando ? 'password' : 'text';
        boton.classList.toggle('mostrando', !mostrando);
        boton.setAttribute('aria-label', mostrando ? 'Mostrar contraseña' : 'Ocultar contraseña');
    });
});
