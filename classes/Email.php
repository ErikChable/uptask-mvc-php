<?php

namespace Classes;

use PHPMailer\PHPMailer\PHPMailer;

class Email
{

    protected $nombre;
    protected $email;
    protected $token;

    public function __construct($nombre, $email, $token)
    {
        $this->nombre = $nombre;
        $this->email = $email;
        $this->token = $token;
    }

    public function enviarConfirmacion()
    {
        $mail = new PHPMailer();
        $mail->isSMTP();
        $mail->Host = $_ENV['EMAIL_HOST'];
        $mail->SMTPAuth = true;
        $mail->Port = $_ENV['EMAIL_PORT'];
        $mail->Username = $_ENV['EMAIL_USER'];
        $mail->Password = $_ENV['EMAIL_PASS'];

        $mail->setFrom($_ENV['EMAIL_USER'], 'UpTask.com');
        $mail->addAddress($this->email, $this->nombre);
        $mail->Subject = 'Confirma tu Cuenta';

        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';

        $contenido = "<html>";
        $contenido .= "<body>";
        $contenido .= "<p><strong>Hola " . $this->nombre . "</strong> has creado tu cuenta en Uptask, ahora solo debes de confirmarla presionando en el siguiente enlace.</p>";
        $contenido .= "<p>Presione aquí: <a href='" . $_ENV['APP_URL'] . "/confirmar?token=" . $this->token . "'>Confirmar Cuenta.</a></p>";
        $contenido .= "<p>Si tu no solicitaste esta cuenta, puedes ignorar este mensaje.</p>";
        $contenido .= "</body>";
        $contenido .= "</html>";

        $mail->Body = $contenido;
        $mail->send();
    }

    public function enviarInstrucciones()
    {
        $mail = new PHPMailer();
        $mail->isSMTP();
        $mail->Host = $_ENV['EMAIL_HOST'];
        $mail->SMTPAuth = true;
        $mail->Port = $_ENV['EMAIL_PORT'];
        $mail->Username = $_ENV['EMAIL_USER'];
        $mail->Password = $_ENV['EMAIL_PASS'];

        $mail->setFrom($_ENV['EMAIL_HOST'], 'UpTask.com');
        $mail->addAddress($this->email, $this->nombre);
        $mail->Subject = 'Reestablece tu Password';

        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';

        $contenido = "<html>";
        $contenido .= "<body>";
        $contenido .= "<p><strong>Hola " . $this->nombre . "</strong> Parece que has olvidado tu password, haz click en el siguiente enlace para recuperarlo.</p>";
        $contenido .= "<p>Presione aquí: <a href='" . $_ENV['APP_URL'] . "/reestablecer?token=" . $this->token . "'>Reestablecer Password.</a></p>";
        $contenido .= "<p>Si tu no solicitaste este reestablecimiento, puedes ignorar este mensaje.</p>";
        $contenido .= "</body>";
        $contenido .= "</html>";

        $mail->Body = $contenido;
        $mail->send();
    }
}
