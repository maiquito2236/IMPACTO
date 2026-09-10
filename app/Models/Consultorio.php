<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Consultorio extends Model
{
    // 1. Le decimos el nombre exacto de tu tabla
    protected $table = 'consultorio';

    // 2. Le decimos cuál es tu llave primaria
    protected $primaryKey = 'ID_CONSULTORIO';

    // 3. Como tu tabla no tiene columnas de fechas, apagamos esto
    public $timestamps = false;

    // 4. Le decimos qué campos se van a poder guardar desde los formularios
    protected $fillable = [
        'NOMBRE',
        'UBICACION',
        'DESCRIPCION',
        'ESTADO'
    ];
}