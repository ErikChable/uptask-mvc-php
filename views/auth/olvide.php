<div class="contenedor olvide">
    <?php include_once __DIR__ . '/../templates/nombre-sitio.php'; ?>
    <div class="contenedor-sm">
        <p class="descripcion-pagina">Recupera tu acceso UpTask</p>

        <?php include_once __DIR__ . '/../templates/alertas.php'; ?>

        <form class="formulario" action="/olvide" method="POST">

            <div class="campo">
                <label for="email">Email</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    placeholder="Tu Email">
            </div>

            <input type="submit" class="boton" value="Enviar Instrucciones">

            <div class="acciones">
                <a href="/">¿Ya tienes cuenta? Inicia Sesión</a>
                <a href="/crear">¿Aún no tienes una cuenta? Crea una</a>
            </div>
    </div> <!-- .contenedor-sm -->
</div>