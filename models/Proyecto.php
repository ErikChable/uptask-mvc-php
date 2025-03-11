<?php

namespace Model;

use Model\ActiveRecord;

class Proyecto extends ActiveRecord
{
    protected static $tabla = 'proyectos';
    protected static $columnasDB = ['id', 'proyecto', 'url', 'propietarioId'];

    public $id;
    public $proyecto;
    public $url;
    public $propietarioId;

    public function __construct($args = [])
    {
        $this->id = $args['id'] ?? null;
        $this->proyecto = $args['proyecto'] ?? '';
        $this->url = $args['url'] ?? '';
        $this->propietarioId = $args['propietarioId'] ?? null;
    }

    public function validarProyecto()
    {
        if (!$this->proyecto) {
            self::$alertas['error'][] = 'El Nombre del Proyecto es Obligatorio';
        }

        if (strlen($this->proyecto) > 60) {
            self::$alertas['error'][] = 'El Nombre del Proyecto no puede superar los 60 caracteres';
        }

        return self::$alertas;
    }
}
