(function() { // IIFE (Immediately invoked function expression) Logra que las variables puedan ser utiñizadas solo en este archivo

    obtenerTareas();
    let tareas = [],
        completadas = [],
        pendientes = [];
        
    let todasCheck = document.querySelector('#todas'),
        pendientesCheck = document.querySelector('#pendientes'),
        completadasCheck = document.querySelector('#completadas');

    // Boton para mostrar el modal de Agregar Tarea
    const nuevaTareaBTN = document.querySelector('#agregar-tarea');
    nuevaTareaBTN.addEventListener('click', function() {
        mostrarFormulario();
    });

    // Filtros de busqueda
    const filtros = document.querySelectorAll('#filtros input[type="radio"]');

    filtros.forEach(radio => {
        radio.addEventListener('input', mostrarTareas);
    });  

    async function obtenerTareas() {
        try {
            const id = obtenerProyecto();
            const url = `/api/tareas?id=${id}`;
            const respuesta = await fetch(url);
            const resultado = await respuesta.json();

            tareas = resultado.tareas;
            actualizarCompletadasYPendientes();
            mostrarTareas();
            
        } catch (error) {
            console.log(error);
            
        }
    }

    function actualizarCompletadasYPendientes() {
        // Inicializamos completadas y todas
        completadas = tareas.filter(tarea => tarea.estado === "1");
        if (!completadas.length) { // Si no hay tareas completadas
            completadasCheck.disabled = true; // Desactivamos el radio button de completadas
        } else { // Pero si las hay
            completadasCheck.disabled = false; // Reactivamos el radio button de completadas
        }

        // Inicializamos pendientes
        pendientes = tareas.filter(tareas => tareas.estado === "0");
        if (!pendientes.length) { // Si no hay tareas pendientes
            pendientesCheck.disabled = true; // Desactivamos el radio button de pendientes
        } else { // Pero si las hay
            pendientesCheck.disabled = false; // Reactivamos el radio button de pendientes
        }
    }

    function mostrarTareas() {
        limpiarTareas();

        let arrayTareas = [];

        if (todasCheck.checked) {
            arrayTareas = tareas;
        } else if (completadasCheck.checked){
            arrayTareas = completadas;
        } else {
            arrayTareas = pendientes;
        }

        if (arrayTareas.length === 0) {
        // Aquí se podrían mostrar las tareas en el DOM   
            const contenedorTareas = document.querySelector('#listado-tareas');

            const textoNoTareas = document.createElement('LI');
            textoNoTareas.textContent = 'No hay tareas agregadas';
            textoNoTareas.classList.add('no-tareas');
            
            contenedorTareas.appendChild(textoNoTareas);

            return;
        }

        const estados = {
            0: 'Pendiente',
            1: 'Completa'
        }

        arrayTareas.forEach(tarea => {
            const contenedorTarea = document.createElement('LI');
            contenedorTarea.dataset.tareaId = tarea.id;
            contenedorTarea.classList.add('tarea');

            const nombreTarea = document.createElement('P');
            nombreTarea.textContent = tarea.nombre;
            nombreTarea.ondblclick = function() {
                mostrarFormulario(editar = true, {...tarea});
            }
            
            const opcionesDiv = document.createElement('DIV');
            opcionesDiv.classList.add('opciones');

            // Botones
            const btnEstadoTarea = document.createElement('BUTTON');
            btnEstadoTarea.classList.add('estado-tarea');
            btnEstadoTarea.classList.add(`${estados[tarea.estado].toLowerCase()}`)
            btnEstadoTarea.textContent = estados[tarea.estado];
            btnEstadoTarea.dataset.estadoTarea = tarea.estado;
            btnEstadoTarea.ondblclick = function() {
                cambiarEstadoTarea({...tarea});
            }

            btnEliminarTarea = document.createElement('BUTTON');
            btnEliminarTarea.classList.add('eliminar-tarea');
            btnEliminarTarea.dataset.idTarea = tarea.id;
            btnEliminarTarea.textContent = 'Eliminar';
            btnEliminarTarea.ondblclick = function() {
                confirmarEliminarTarea({...tarea});
            }

            opcionesDiv.appendChild(btnEstadoTarea);
            opcionesDiv.appendChild(btnEliminarTarea);

            contenedorTarea.appendChild(nombreTarea);
            contenedorTarea.appendChild(opcionesDiv);

            const listadoTareas = document.querySelector('#listado-tareas');
            listadoTareas.appendChild(contenedorTarea);
        });
        
    }

    function mostrarFormulario(editar = false, tarea = {}) {
        // console.log(tarea);
        
        const modal = document.createElement('DIV');
        modal.classList.add('modal');
        modal.innerHTML = `
            <form class="formulario nueva-tarea">
                <legend>${editar ? 'Editar Tarea' : 'Añade una nueva tarea'}</legend>
                <div class="campo">
                    <label>Tarea:</label>
                    <input 
                        type="text"
                        name="tarea"
                        placeholder="${editar ? 'Edita la Tarea' : 'Añadir Tarea al Proyecto Actual'}"
                        id="tarea"
                        value="${tarea.nombre ? tarea.nombre : ''}">
                </div>

                <div class="opciones">
                    <input type="submit" class="submit-nueva-tarea" value="${editar ? 'Guardar Cambios' : 'Añadir Tarea'}"> 
                    <button type="button" class="cerrar-modal">Cancelar</button>
                </div>
            </form>
        `;  

        setTimeout(() => {
            const formulario = document.querySelector('.formulario');
            formulario.classList.add('animar');
        }, 0);

        modal.addEventListener('click', function(e) {
            e.preventDefault();
            if (e.target.classList.contains('cerrar-modal')) {
                const formulario = document.querySelector('.formulario');
                formulario.classList.add('cerrar');

                setTimeout(() => {
                    modal.remove();
                }, 300);
                
            } 

            if (e.target.classList.contains('submit-nueva-tarea')) {
                const nombreTarea = document.querySelector('#tarea').value.trim();

                if (nombreTarea === "") {
                    // Mostrar una alerta de error
                    mostrarAlerta('El Nombre de la Tarea es Obligatorio', 'error', document.querySelector('.formulario legend'));
        
                    return;
                } 

                if (nombreTarea.length > 60) {
                    mostrarAlerta('El Nombre de la Tarea no debe exceder los 60 caracteres', 'error', document.querySelector('.formulario legend'));
                    return;
                }

                if (editar) {
                    tarea.nombre = nombreTarea;
                    actualizarTarea(tarea);
                } else {
                    agregarTarea(nombreTarea);
                }
            }
        });

        document.querySelector('.dashboard').appendChild(modal);
    }

    // Muestra un mensaje en la interfaz
    function mostrarAlerta(mensaje, tipo, referencia) {
        // Previene la creacion de multiples alertas
        const alertaPrevia = document.querySelector('.alerta');
        if (alertaPrevia) {
            alertaPrevia.remove();
        }

        const alerta = document.createElement('DIV');
        alerta.classList.add('alerta', tipo);
        alerta.textContent = mensaje;

        // Inserta la alerta antes del legend
        // referencia.parentElement.insertBefore(alerta, referencia);

        // Inserta la alerta despues del legend
        referencia.parentElement.insertBefore(alerta, referencia.nextElementSibling); // nextElementSibling significa antes del proximo hermano

        // Elimina la alerta despues de 5 segundos
        setTimeout(() => {
            alerta.remove();
        }, 5000);
    }
 
    // Consultar al servidor para añadir una nueva tarea al proyecto actual
    async function agregarTarea(tarea) {
        // Construir la petición
        const datos = new FormData();
        datos.append('nombre', tarea);
        datos.append('proyectoId', obtenerProyecto()); 

        try {
            const url = '/api/tarea';
            const respuesta = await fetch(url, {
                method: 'POST',
                body: datos
            }); 

            const resultado = await respuesta.json();
            mostrarAlerta(resultado.mensaje, resultado.tipo, document.querySelector('.formulario legend'));

            if (resultado.tipo === 'exito') {
                const modal = document.querySelector('.modal');
                setTimeout(() => {
                    modal.remove();
                }, 3000);

                // Agregar el objeto de tarea al global de tareas
                const tareaObj = {
                    id: String(resultado.id),
                    nombre: tarea,
                    estado: "0",
                    proyectoId: resultado.proyectoId
                }

                tareas = [...tareas, tareaObj];
                actualizarCompletadasYPendientes();
                mostrarTareas();
            }   
            
        } catch (error) {
            console.log(error);
        }
    }

    function cambiarEstadoTarea(tarea) {
        const nuevoEstado = tarea.estado === "1" ? "0" : "1"
        tarea.estado = nuevoEstado;
        actualizarTarea(tarea);
    }

    async function actualizarTarea(tarea) {
        const {estado, id, nombre, proyectoId} = tarea;

        const datos = new FormData();
        datos.append('id', id);
        datos.append('nombre', nombre);
        datos.append('estado', estado);
        datos.append('proyectoId', obtenerProyecto());
        
        // for(let valor of datos.values()) {
        //     console.log(valor);
        // } // Permite ver los datos del formData
        try {
            const url = '/api/tarea/actualizar';

            const respuesta = await fetch(url, {
                method: 'POST',
                body: datos
            });
            const resultado = await respuesta.json();

            // console.log(resultado);
            
            if (resultado.respuesta.tipo === 'exito') {
                Swal.fire (
                    resultado.respuesta.mensaje,
                    resultado.respuesta.mensaje2,
                    'success'
                );

                const modal = document.querySelector('.modal');
                if (modal) {
                    modal.remove();
                }

                tareas = tareas.map(tareaMemoria => {
                    if (tareaMemoria.id === id) {
                        tareaMemoria.estado = estado;
                        tareaMemoria.nombre = nombre;
                    }
                    return tareaMemoria; 
                });
                actualizarCompletadasYPendientes();
                mostrarTareas();
            }
            
        } catch (error) {
            console.log(error);
        }
    }

    function confirmarEliminarTarea(tarea) {
        Swal.fire({
            title: "¿Estás seguro de eliminar esta tarea?",
            showCancelButton: true,
            confirmButtonText: "Si",
            cancelButtonText: "No"
          }).then((result) => {
            if (result.isConfirmed) {
                eliminarTarea(tarea);
            } 
          });
    }

    async function eliminarTarea(tarea) {

        const {estado, id, nombre} = tarea;

        const datos = new FormData();
        datos.append('id', id);
        datos.append('nombre', nombre);
        datos.append('estado', estado);
        datos.append('proyectoId', obtenerProyecto());

        try {
            const url = '/api/tarea/eliminar';
            const respuesta = await fetch(url, {
                method: 'POST',
                body: datos
            });
        
            const resultado = await respuesta.json();
            if (resultado.resultado) {
                Swal.fire('¡Tarea Eliminada!', resultado.mensaje, 'success');

                tareas = tareas.filter(tareaMemoria => tareaMemoria.id !== tarea.id); // Filtra y trae a todas las tareas con un id diferente al que se elimino
                actualizarCompletadasYPendientes();
                mostrarTareas();
            }
            
        } catch (error) {
            console.log(error);
        }
    }

    function obtenerProyecto() {
        const proyectoParams = new URLSearchParams(window.location.search);
        const proyecto = Object.fromEntries(proyectoParams.entries())
        return proyecto.id;
    }

    function limpiarTareas() {
        const listadoTareas = document.querySelector('#listado-tareas');

        while(listadoTareas.firstChild) {
            listadoTareas.removeChild(listadoTareas.firstChild);
        }
    }
}());