<?php

namespace Controllers;

use Classes\Email;
use Model\Usuario;
use MVC\Router;

class LoginController
{
    public static function login(Router $router)
    {
        $alertas = [];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $usuario = new Usuario($_POST);

            $alertas = $usuario->validarLogin();

            if (empty($alertas)) {
                // Verificar que el usuario exista
                $usuario = Usuario::where('email', $usuario->email);

                if (!$usuario) {
                    Usuario::setAlerta('error', 'El Usuario No Existe');
                } else if (!$usuario->confirmado) {
                    Usuario::setAlerta('error', 'Su cuenta aún no ha sido confirmada');
                } else {
                    // El usuario existe
                    if (password_verify($_POST['password'], $usuario->password)) {
                        // Iniciar sesión
                        session_start();
                        $_SESSION['id'] = $usuario->id;
                        $_SESSION['nombre'] = $usuario->nombre;
                        $_SESSION['email'] = $usuario->email;
                        $_SESSION['login'] = true;

                        // Redireccionar
                        header('Location: /dashboard');
                    } else {
                        Usuario::setAlerta('error', 'Password Incorrecto');
                    }
                }
            }
        }

        $alertas = Usuario::getAlertas();

        $router->render('auth/login', [
            'titulo' => 'Iniciar Sesion',
            'alertas' => $alertas
        ]);
    }

    public static function logout()
    {
        session_start();
        $_SESSION = [];
        header('Location: /');
    }

    public static function crear(Router $router)
    {
        $usuario = new Usuario;
        $alertas = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $usuario->sincronizar($_POST);
            $alertas = $usuario->validarNuevaCuenta();

            if (empty($alertas)) {
                $existeUsuario = Usuario::where('email', $usuario->email);

                if ($existeUsuario) {
                    Usuario::setAlerta('error', 'El Usuario ya esta registrado');
                    $alertas = Usuario::getAlertas();
                } else {
                    // Hashear el password
                    $usuario->hashPassword();

                    // Eliminar password2
                    unset($usuario->password2);

                    // Generar el token
                    $usuario->crearToken();

                    // Crear nuevo usuario
                    $resultado = $usuario->guardar();

                    // Envia el email
                    $email = new Email($usuario->nombre, $usuario->email, $usuario->token);
                    $email->enviarConfirmacion();

                    if ($resultado) {
                        header('Location: /mensaje');
                    }
                }
            }
        }

        $router->render('auth/crear', [
            'titulo' => 'Crear Cuenta',
            'usuario' => $usuario,
            'alertas' => $alertas
        ]);
    }

    public static function olvide(Router $router)
    {
        $alertas = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $usuario = new Usuario($_POST);
            $alertas = $usuario->validarEmail();

            if (empty($alertas)) {
                // Buscar el usuario
                $usuario = Usuario::where('email', $usuario->email);

                if ($usuario && $usuario->confirmado) {
                    // Generar un nuevo token
                    $usuario->crearToken();
                    unset($usuario->password2);
                    // Actualizar el usuario
                    $usuario->guardar();
                    // Enviar el email 
                    $email = new Email($usuario->nombre, $usuario->email, $usuario->token);
                    $email->enviarInstrucciones();
                    // Imprimir la alerta
                    Usuario::setAlerta('exito', 'Hemos enviado las instrucciones a tu email');
                } else {
                    Usuario::setAlerta('error', 'El Usuario no existe o no esta confirmado');
                }
            }
        }

        $alertas = Usuario::getAlertas();

        $router->render('auth/olvide', [
            'titulo' => 'Olvidé Contraseña',
            'alertas' => $alertas
        ]);
    }

    public static function reestablecer(Router $router)
    {
        $alertas = [];
        $token = s($_GET['token']);
        $mostrar = true;
        if (!$token) header('Location: /');

        // Identificamos al usuario con el token
        $usuario = Usuario::where('token', $token);

        if (empty($usuario)) {
            // No se encontro al usuario con ese token
            Usuario::setAlerta('error', 'Token No Válido');
            $mostrar = false;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Añadir el nuevo password
            $usuario->sincronizar($_POST);

            // Validar password
            $alertas = $usuario->validarPassword();

            if (empty($alertas)) {
                // Hashear el password
                $usuario->hashPassword();
                unset($usuario->password2); // No es necesario eliminarlo, pero lo hago para evitar algun tipo de problema
                // Eliminar token
                $usuario->token = null;
                // Guardar el usuario en la BD
                $resultado = $usuario->guardar();
                // Redireccionar 
                if ($resultado) {
                    header('Location: /');
                    exit;
                }
            }
        }

        $alertas = Usuario::getAlertas();

        $router->render('auth/reestablecer', [
            'titulo' => 'Reestablecer Contraseña',
            'alertas' => $alertas,
            'mostrar' => $mostrar
        ]);
    }

    public static function mensaje(Router $router)
    {
        $router->render('auth/mensaje', [
            'titulo' => 'Instrucciones'
        ]);
    }

    public static function confirmar(Router $router)
    {
        $token = s($_GET['token']);
        $alertas = [];

        if (!$token) header('Location: /');

        // Encontrar al usuario con el token
        $usuario = Usuario::where('token', $token);

        if (empty($usuario)) {
            // No se encontro al usuario con ese token
            Usuario::setAlerta('error', 'Token NO Válido');
        } else {
            // Confirmar la cuenta
            $usuario->confirmado = 1;
            $usuario->token = null;
            unset($usuario->password2);

            if ($usuario->emailTemp) {
                $usuario->email = $usuario->emailTemp;
                $usuario->emailTemp = null;
            }

            $usuario->guardar();

            Usuario::setAlerta('exito', 'Cuenta Confirmada Correctamente');
        }

        $alertas = Usuario::getAlertas();

        $router->render('auth/confirmar', [
            'titulo' => 'Confirma tu cuenta',
            'alertas' => $alertas
        ]);
    }
}
