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
        if (tarjeta) {
            tarjeta.hidden = false;
            tarjetaNombre.textContent = item.nombre;
            if (item.imagen) {
                tarjetaImg.src = item.imagen;
                tarjetaImg.hidden = false;
            } else {
                tarjetaImg.hidden = true;
            }
        }
        document.dispatchEvent(new CustomEvent('cliente:seleccionado', { detail: item }));
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

// ---------- Grillas con filtro y orden por columna ----------
document.querySelectorAll('.tabla-filtrable').forEach(function (tabla) {
    var thead = tabla.querySelector('thead');
    var headerRow = thead.querySelector('tr');
    var ths = Array.from(headerRow.children);
    var tbody = tabla.querySelector('tbody');

    function filasDeDatos() {
        return Array.from(tbody.querySelectorAll('tr')).filter(function (f) {
            return !f.querySelector('td[colspan]');
        });
    }

    // Fila de filtros, uno por columna (salvo la de acciones o columnas sin título)
    var filaFiltros = document.createElement('tr');
    filaFiltros.className = 'fila-filtros';
    var filtros = [];
    ths.forEach(function (th, i) {
        var celda = document.createElement('th');
        if (!th.classList.contains('th-acciones') && th.textContent.trim() !== '') {
            var input = document.createElement('input');
            input.type = 'text';
            input.placeholder = 'Filtrar...';
            input.dataset.col = String(i);
            filtros.push(input);
            celda.appendChild(input);
        }
        filaFiltros.appendChild(celda);
    });
    thead.appendChild(filaFiltros);

    filtros.forEach(function (input) {
        input.addEventListener('input', function () {
            filasDeDatos().forEach(function (fila) {
                var visible = filtros.every(function (f) {
                    var val = f.value.trim().toLowerCase();
                    if (!val) return true;
                    var celda = fila.children[parseInt(f.dataset.col, 10)];
                    return celda && celda.textContent.toLowerCase().indexOf(val) !== -1;
                });
                fila.style.display = visible ? '' : 'none';
            });
        });
    });

    // Orden por columna (usa data-valor si existe, si no el texto visible)
    ths.forEach(function (th, i) {
        if (th.classList.contains('th-acciones') || th.textContent.trim() === '') return;
        th.classList.add('th-ordenable');
        th.addEventListener('click', function () {
            var nuevoOrden = th.dataset.orden === 'asc' ? 'desc' : 'asc';
            ths.forEach(function (otro) {
                delete otro.dataset.orden;
                otro.classList.remove('orden-asc', 'orden-desc');
            });
            th.dataset.orden = nuevoOrden;
            th.classList.add('orden-' + nuevoOrden);

            var filas = filasDeDatos();
            filas.sort(function (fa, fb) {
                var ca = fa.children[i];
                var cb = fb.children[i];
                var va = ca.dataset.valor !== undefined ? parseFloat(ca.dataset.valor) : ca.textContent.trim().toLowerCase();
                var vb = cb.dataset.valor !== undefined ? parseFloat(cb.dataset.valor) : cb.textContent.trim().toLowerCase();
                if (va < vb) return nuevoOrden === 'asc' ? -1 : 1;
                if (va > vb) return nuevoOrden === 'asc' ? 1 : -1;
                return 0;
            });
            filas.forEach(function (fila) { tbody.appendChild(fila); });
        });
    });
});

// ---------- Sidebar en mobile (botón hamburguesa) ----------
(function () {
    var boton = document.getElementById('menuToggle');
    var sidebar = document.getElementById('qerpSidebar');
    var overlay = document.getElementById('menuOverlay');
    if (!boton || !sidebar) return;

    function cerrar() {
        sidebar.classList.remove('abierto');
        if (overlay) overlay.hidden = true;
    }

    boton.addEventListener('click', function () {
        var abierto = sidebar.classList.toggle('abierto');
        if (overlay) overlay.hidden = !abierto;
    });

    if (overlay) overlay.addEventListener('click', cerrar);
})();

// ---------- Solapas (tabs) genéricas ----------
document.querySelectorAll('.tabs').forEach(function (tabs) {
    var botones = Array.from(tabs.querySelectorAll(':scope > .tabs-nav > .tab-btn'));
    var paneles = Array.from(tabs.querySelectorAll(':scope > .tab-panel'));
    botones.forEach(function (boton) {
        boton.addEventListener('click', function () {
            botones.forEach(function (b) { b.classList.remove('activo'); });
            paneles.forEach(function (p) { p.hidden = true; });
            boton.classList.add('activo');
            var panel = tabs.querySelector(':scope > .tab-panel[data-tab-panel="' + boton.dataset.tab + '"]');
            if (panel) panel.hidden = false;
        });
    });
});

// ---------- Filas dinámicas (ej. productos asociados a una actividad) ----------
(function () {
    var boton = document.getElementById('agregarProducto');
    var plantilla = document.getElementById('plantillaFilaProducto');
    var tbody = document.getElementById('filasProductos');
    if (!boton || !plantilla || !tbody) return;

    function activarQuitar(fila) {
        var btnQuitar = fila.querySelector('.quitar-fila-producto');
        if (btnQuitar) {
            btnQuitar.addEventListener('click', function () { fila.remove(); });
        }
    }

    tbody.querySelectorAll('tr').forEach(activarQuitar);

    boton.addEventListener('click', function () {
        var fragmento = plantilla.content.cloneNode(true);
        var fila = fragmento.querySelector('tr');
        tbody.appendChild(fragmento);
        activarQuitar(tbody.lastElementChild);
    });
})();

// ---------- Histórico de contacto del cliente elegido en el buscador ----------
(function () {
    var contenedor = document.getElementById('historicoContenido');
    if (!contenedor) return;
    var url = contenedor.dataset.url;

    document.addEventListener('cliente:seleccionado', function (evento) {
        contenedor.textContent = 'Cargando...';
        fetch(url + '?cliente_id=' + encodeURIComponent(evento.detail.id))
            .then(function (r) { return r.json(); })
            .then(function (items) {
                contenedor.innerHTML = '';
                if (!items.length) {
                    contenedor.textContent = 'Todavía no hay acciones registradas para este cliente.';
                    return;
                }
                var envoltorio = document.createElement('div');
                envoltorio.className = 'tabla-responsive';
                var tabla = document.createElement('table');
                tabla.className = 'tabla-qerp';
                var thead = document.createElement('thead');
                thead.innerHTML = '<tr><th>Fecha</th><th>Canal</th><th>Motivo</th><th>Resultado</th><th>Vendedor</th></tr>';
                var tbody = document.createElement('tbody');
                items.forEach(function (h) {
                    var fila = document.createElement('tr');
                    ['fecha', 'canal', 'motivo', 'resultado', 'vendedor'].forEach(function (campo) {
                        var td = document.createElement('td');
                        td.textContent = h[campo];
                        fila.appendChild(td);
                    });
                    tbody.appendChild(fila);
                });
                tabla.appendChild(thead);
                tabla.appendChild(tbody);
                envoltorio.appendChild(tabla);
                contenedor.appendChild(envoltorio);
            });
    });
})();

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
