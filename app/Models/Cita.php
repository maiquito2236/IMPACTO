<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cita extends Model
{
    // 1. Nombre exacto de tu tabla
    protected $table = 'cita';

    // 2. Llave primaria de tu tabla
    protected $primaryKey = 'ID_CITA';

    // 3. Apagamos los timestamps automáticos de Laravel
    public $timestamps = false;

    // 4. Campos que se pueden llenar desde formularios
    protected $fillable = [
        'FECHA_HORA',
        'MOTIVO',
        'PACIENTE_ID_PACIENTE',
        'ESTADO_CITA_ID'
    ];
}
