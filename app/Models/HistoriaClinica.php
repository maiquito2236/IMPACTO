<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HistoriaClinica extends Model
{
    protected $table = 'historia_clinica';
    protected $primaryKey = 'ID_HISTORIA_CLINICA';
    public $timestamps = false;

    protected $fillable = [
        'PACIENTE_ID_PACIENTE',
        'ODONTOLOGO_ID_ODONTOLOGO',
        'TRATAMIENTO',
        'FECHA_REGISTRO',
        'OBSERVACIONES'
    ];
}