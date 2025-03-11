<?php

namespace Model;

class Usuario extends ActiveRecord
{
    protected static $tabla = 'usuarios';
    protected static $columnasDB = ['id', 'nombre', 'email', 'password', 'token', 'confirmado', 'emailTemp'];

    public $id;
    public $nombre;
    public $email;
    public $password;
    public $password2; // Confirmación de Contraseña del Usuario para verificar que coincida con la primera contraseña.
    public $password_actual;
    public $password_nuevo;
    public $token;
    public $confirmado;
    public $emailTemp;

    public function __construct($args = [])
    {
        $this->id = $args['id'] ?? null;
        $this->nombre = $args['nombre'] ?? '';
        $this->email = $args['email'] ?? '';
        $this->password = $args['password'] ?? '';
        $this->password2 = $args['password2'] ?? '';
        $this->password_actual = $args['password_actual'] ?? '';
        $this->password_nuevo = $args['password_nuevo'] ?? '';
        $this->token = $args['token'] ?? '';
        $this->confirmado = $args['confirmado'] ?? 0;
        $this->emailTemp = $args['emailTemp'] ?? '';
    }

    public function validarNuevaCuenta(): array
    {
        if (!$this->nombre) {
            self::$alertas['error'][] = 'El Nombre del Usuario es Obligatorio';
        }

        if (strlen($this->nombre) > 30) {
            self::$alertas['error'][] = 'El Nombre no puede tener más de 30 caracteres';
        }

        if (!$this->email) {
            self::$alertas['error'][] = 'El Email del Usuario es Obligatorio';
        }

        if (strlen($this->email) > 30) {
            self::$alertas['error'][] = 'El Email no puede tener más de 30 caracteres';
        } else if (!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            self::$alertas['error'][] = 'Formato de Email No Válido';
        }

        if (!$this->password) {
            self::$alertas['error'][] = 'El Password es Obligatorio';
        }
        if (strlen($this->password) < 8) {
            self::$alertas['error'][] = 'El Password debe contener al menos 8 caracteres';
        }
        if (strlen($this->password) > 60) {
            self::$alertas['error'][] = 'El Password no puede tener más de 60 caracteres';
        } else if ($this->password !== $this->password2) {
            self::$alertas['error'][] = 'Los Password no coinciden';
        }


        return self::$alertas;
    }

    // Validar login
    public function validarLogin(): array
    {
        if (!$this->email) {
            self::$alertas['error'][] = 'El Email es obligatorio';
        }
        if (strlen($this->email) > 30) {
            self::$alertas['error'][] = 'El Email no puede tener más de 30 caracteres';
        } else if (!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            self::$alertas['error'][] = 'Formato de Email No Válido';
        }

        if (!$this->password) {
            self::$alertas['error'][] = 'El Password es Obligatorio';
        }
        if (strlen($this->password) > 60) {
            self::$alertas['error'][] = 'El Password no puede tener más de 60 caracteres';
        }
        if (strlen($this->password) < 8) {
            self::$alertas['error'][] = 'El Password debe contener al menos 8 caracteres';
        }

        return self::$alertas;
    }

    // Validar el email
    public function validarEmail(): array
    {
        if (!$this->email) {
            self::$alertas['error'][] = 'El Email es obligatorio';
        }
        if (strlen($this->email) > 30) {
            self::$alertas['error'][] = 'El Email no puede tener más de 30 caracteres';
        } else if (!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            self::$alertas['error'][] = 'Formato de Email No Válido';
        }

        return self::$alertas;
    }

    // Validar el password
    public function validarPassword(): array
    {
        if (!$this->password) {
            self::$alertas['error'][] = 'El Password es Obligatorio';
        }
        if (strlen($this->password) < 8) {
            self::$alertas['error'][] = 'El Password debe contener al menos 8 caracteres';
        }
        if (strlen($this->password) > 60) {
            self::$alertas['error'][] = 'El Password no puede tener más de 60 caracteres';
        }

        return self::$alertas;
    }

    public function validar_perfil(): array
    {
        if (!$this->nombre) {
            self::$alertas['error'][] = 'El Nombre del Usuario es Obligatorio';
        }
        if (strlen($this->nombre) > 30) {
            self::$alertas['error'][] = 'El Nombre no puede tener más de 30 caracteres';
        }

        if (!$this->email) {
            self::$alertas['error'][] = 'El Email es obligatorio';
        }
        if (strlen($this->email) > 30) {
            self::$alertas['error'][] = 'El Email no puede tener más de 30 caracteres';
        } else if (!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            self::$alertas['error'][] = 'Formato de Email No Válido';
        }

        return self::$alertas;
    }

    public function nuevo_password(): array
    {
        if (!$this->password_actual) {
            self::$alertas['error'][] = 'El Password Actual es obligatorio';
        }
        if (strlen($this->password_actual) < 8) {
            self::$alertas['error'][] = 'El Password Actual debe contener al menos 8 caracteres';
        }
        if (strlen($this->password_actual) > 60) {
            self::$alertas['error'][] = 'El Password Actual no puede tener más de 60 caracteres';
        }

        if (!$this->password_nuevo) {
            self::$alertas['error'][] = 'El Password Nuevo es obligatorio';
        }
        if (strlen($this->password_nuevo) < 8) {
            self::$alertas['error'][] = 'El Password Nuevo debe contener al menos 8 caracteres';
        }
        if (strlen($this->password_nuevo) > 60) {
            self::$alertas['error'][] = 'El Password Nuevo no puede tener más de 60 caracteres';
        }

        return self::$alertas;
    }

    public function comprobar_password(): bool
    {
        return password_verify($this->password_actual, $this->password);
    }

    // Hashea el password
    public function hashPassword(): void
    {
        $this->password = password_hash($this->password, PASSWORD_BCRYPT);
    }

    // Genera un token de confirmación
    public function crearToken(): void
    {
        $this->token = md5(uniqid()); // Genera un token unico de 32 caracteres
    }
}
