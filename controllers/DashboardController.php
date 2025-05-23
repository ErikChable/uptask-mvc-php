<?php

namespace Controllers;

use Classes\Email;
use Model\Proyecto;
use Model\Usuario;
use MVC\Router;

class DashboardController
{
    public static function index(Router $router)
    {
        session_start();
        isAuth();

        $id = $_SESSION['id'];

        $proyectos = Proyecto::belongsTo('propietarioId', $id);

        $router->render('dashboard/index', [
            'titulo' => 'Proyectos',
            'proyectos' => $proyectos
        ]);
    }

    public static function crear_proyecto(Router $router)
    {
        session_start();
        isAuth();
        $alertas = [];

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $proyecto = new Proyecto($_POST);

            // Validacion
            $alertas = $proyecto->validarProyecto();

            if (empty($alertas)) {
                // Generar una URL única
                $hash = md5(uniqid());
                $proyecto->url = $hash;

                // Almacenar el creador del proyecto
                $proyecto->propietarioId = $_SESSION['id'];

                // Guardar el Proyecto
                $proyecto->guardar();

                // Redireccionar
                header('Location: /proyecto?id=' . $proyecto->url);
            }
        }

        $router->render('dashboard/crear-proyecto', [
            'titulo' => 'Crear Proyecto',
            'alertas' => $alertas
        ]);
    }

    public static function proyecto(Router $router)
    {
        session_start();
        isAuth();

        $token = $_GET['id'];

        if (!$token) header('Location: /dashboard');
        // Revisar que la persona que visita el proyecto, es quien lo creo
        $proyecto = Proyecto::where('url', $token);
        if ($proyecto->propietarioId !== $_SESSION['id']) {
            header('Location: /dashboard');
        }

        $router->render('dashboard/proyecto', [
            'titulo' => $proyecto->proyecto
        ]);
    }

    public static function perfil(Router $router)
    {
        session_start();
        isAuth();
        $usuario = Usuario::find($_SESSION['id']);
        $alertas = [];

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $usuario->sincronizar($_POST);
            $alertas = $usuario->validar_perfil();

            if (empty($alertas)) {
                $existeUsuario = Usuario::where('email', $usuario->email);

                if ($existeUsuario && $existeUsuario->id !== $usuario->id) {
                    // Mensaje de error
                    Usuario::setAlerta('error', 'Este correo electrónico ya está en uso. Intenta con otro.');
                } else {
                    $correoAnterior = $_SESSION['email'];

                    if ($correoAnterior !== $usuario->emailTemp) {
                        // debuguear($usuario);
                        $usuario->crearToken();
                        $usuario->confirmado = 0;

                        $resultado = $usuario->guardar();

                        // Enviar el email de confirmacion
                        $email = new Email($usuario->nombre, $usuario->emailTemp, $usuario->token);
                        $email->enviarConfirmacion();

                        if ($resultado) {
                            $_SESSION = [];
                            header('Location: /mensaje');
                        }
                    } else {
                        // Guardar el usuario
                        $usuario->emailTemp = null;
                        $usuario->guardar();
                        Usuario::setAlerta('exito', 'Los datos de tu perfil se han actualizado correctamente.');
                    }
                }
                // Asignamos el nuevo nombre a la barra
                $_SESSION['nombre'] = $usuario->nombre;
            }
        }

        $alertas = Usuario::getAlertas();
        $router->render('dashboard/perfil', [
            'titulo' => 'Perfil',
            'usuario' => $usuario,
            'alertas' => $alertas
        ]);
    }

    public static function cambiar_password(Router $router)
    {
        session_start();
        isAuth();
        $alertas = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $usuario = Usuario::find($_SESSION['id']);

            // Sincronizar con los datos del usuario
            $usuario->sincronizar($_POST);
            $alertas = $usuario->nuevo_password();

            if (empty($alertas)) {
                $resultado = $usuario->comprobar_password();

                if ($resultado) {
                    $usuario->password = $usuario->password_nuevo;

                    // Borrar los datos temporales  
                    unset($usuario->password_actual);
                    unset($usuario->password_nuevo);

                    // Hashear el password
                    $usuario->hashPassword();

                    // Actualizar 
                    $resultado = $usuario->guardar();
                    Usuario::setAlerta('exito', 'Password Guardado Correctamente.');
                    $alertas = Usuario::getAlertas();
                } else {
                    Usuario::setAlerta('error', 'Password Incorrecto.');
                    $alertas = Usuario::getAlertas();
                }
            }
        }

        $router->render('dashboard/cambiar_password', [
            'titulo' => 'Cambiar Password',
            'alertas' => $alertas
        ]);
    }
}
