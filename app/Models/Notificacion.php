<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notificacion extends Model
{
    protected $table = 'notificaciones';
    protected $primaryKey = 'ID_NOTIFICACIONES';
    public $timestamps = false;

    protected $fillable = [
        'TIPO_NOTIFICACION_ID_TIPO_NOTIFICACION',
        'MENSAJE',
        'ESTADO',
        'FECHA_ENVIO'
    ];
}