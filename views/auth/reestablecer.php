<div class="contenedor reestablecer">
    <?php include_once __DIR__ . '/../templates/nombre-sitio.php'; ?>
    <div class="contenedor-sm">
        <p class="descripcion-pagina">Coloca tu nuevo password</p>

        <?php include_once __DIR__ . '/../templates/alertas.php'; ?>

        <?php if ($mostrar) { ?>

            <form class="formulario" method="POST">
                <form class="formulario" action="/" method="POST">

                    <div class="campo">
                        <label for="password">Password</label>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            placeholder="Tu Password">
                    </div>

                    <input type="submit" class="boton" value="Guardar Password">
                </form>

            <?php }; ?>

            <div class="acciones">
                <a href="/crear">¿Aún no tienes una cuenta? Crea una</a>
                <a href="/olvide">¿Olvidaste tu Password?</a>
            </div>
    </div> <!-- .contenedor-sm -->
</div>