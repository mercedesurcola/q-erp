document.querySelectorAll('.toggle-password').forEach(function (boton) {
    boton.addEventListener('click', function () {
        var input = document.getElementById(boton.dataset.target);
        var mostrando = input.type === 'text';
        input.type = mostrando ? 'password' : 'text';
        boton.classList.toggle('mostrando', !mostrando);
        boton.setAttribute('aria-label', mostrando ? 'Mostrar contraseña' : 'Ocultar contraseña');
    });
});

// ---------- Buscador de cliente (typeahead con seguridad server-side) ----------
document.querySelectorAll('.buscador-cliente').forEach(function (contenedor) {
    var input = contenedor.querySelector('input[type=text]');
    var lista = contenedor.querySelector('.buscador-resultados');
    var hidden = contenedor.querySelector('input[type=hidden]');
    var url = contenedor.dataset.buscarUrl;
    var tarjeta = document.getElementById('tarjetaClienteFlotante');
    var tarjetaImg = tarjeta ? tarjeta.querySelector('img') : null;
    var tarjetaNombre = tarjeta ? tarjeta.querySelector('.nombre') : null;
    var timer = null;

    function elegir(item) {
        hidden.value = item.id;
        input.value = item.nombre;
        lista.hidden = true;
        if (!tarjeta) return;
        tarjeta.hidden = false;
        tarjetaNombre.textContent = item.nombre;
        if (item.imagen) {
            tarjetaImg.src = item.imagen;
            tarjetaImg.hidden = false;
        } else {
            tarjetaImg.hidden = true;
        }
    }

    input.addEventListener('input', function () {
        hidden.value = '';
        clearTimeout(timer);
        var q = input.value.trim();
        if (q.length < 2) {
            lista.hidden = true;
            lista.innerHTML = '';
            return;
        }
        timer = setTimeout(function () {
            fetch(url + '?q=' + encodeURIComponent(q))
                .then(function (r) { return r.json(); })
                .then(function (items) {
                    lista.innerHTML = '';
                    if (!items.length) {
                        lista.hidden = true;
                        return;
                    }
                    items.forEach(function (item) {
                        var fila = document.createElement('div');
                        fila.className = 'buscador-item';
                        fila.textContent = item.nombre;
                        fila.addEventListener('click', function () { elegir(item); });
                        lista.appendChild(fila);
                    });
                    lista.hidden = false;
                });
        }, 250);
    });

    document.addEventListener('click', function (e) {
        if (!contenedor.contains(e.target)) {
            lista.hidden = true;
        }
    });
});

// ---------- Editor de texto "lite" (negrita / lista, sin HTML crudo) ----------
document.querySelectorAll('.editor-lite').forEach(function (contenedor) {
    var textarea = contenedor.querySelector('textarea');
    contenedor.querySelectorAll('[data-formato]').forEach(function (boton) {
        boton.addEventListener('click', function () {
            var tipo = boton.dataset.formato;
            var inicio = textarea.selectionStart;
            var fin = textarea.selectionEnd;
            var seleccion = textarea.value.substring(inicio, fin) || (tipo === 'lista' ? 'Item' : 'texto');
            var nuevo = tipo === 'negrita' ? '**' + seleccion + '**' : '- ' + seleccion;
            textarea.setRangeText(nuevo, inicio, fin, 'end');
            textarea.focus();
        });
    });
});

// ---------- Adjuntos: zona de arrastrar y soltar ----------
document.querySelectorAll('.dropzone').forEach(function (zona) {
    var input = zona.querySelector('input[type=file]');
    var lista = zona.querySelector('.dropzone-lista');

    function mostrarNombres() {
        if (!lista) return;
        lista.innerHTML = '';
        Array.from(input.files).forEach(function (f) {
            var li = document.createElement('li');
            li.textContent = f.name + ' (' + Math.round(f.size / 1024) + ' KB)';
            lista.appendChild(li);
        });
    }

    zona.addEventListener('click', function (e) {
        if (e.target !== input) input.click();
    });
    input.addEventListener('change', mostrarNombres);
    zona.addEventListener('dragover', function (e) {
        e.preventDefault();
        zona.classList.add('dragover');
    });
    zona.addEventListener('dragleave', function () {
        zona.classList.remove('dragover');
    });
    zona.addEventListener('drop', function (e) {
        e.preventDefault();
        zona.classList.remove('dragover');
        input.files = e.dataTransfer.files;
        mostrarNombres();
    });
});
