<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pago extends Model
{
    protected $table = 'pago';
    protected $primaryKey = 'ID_PAGO';
    public $timestamps = false;

    protected $fillable = [
        'CITA_ID_CITA',
        'MONTO',
        'METODO_PAGO',
        'ESTADO',
        'FECHA_PAGO'
    ];
}