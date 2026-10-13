<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Paciente extends Model
{
    protected $table = 'paciente';
    protected $primaryKey = 'ID_PACIENTE';
    public $timestamps = false;

    protected $fillable = [
        'USUARIOS_ID_USUARIOS',
        'EPS_ID_EPS',
        'NOMBRE_CONTACTO_EMERGENCIA',
        'NUMERO_CONTACTO_EMERGENCIA'
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'USUARIOS_ID_USUARIOS', 'ID_USUARIOS');
    }

    public function eps()
    {
        return $this->belongsTo(Eps::class, 'EPS_ID_EPS', 'ID_EPS');
    }

    public function citas()
    {
        return $this->hasMany(Cita::class, 'PACIENTE_ID_PACIENTE', 'ID_PACIENTE');
    }

    public function historiasClinicas()
    {
        return $this->hasMany(HistoriaClinica::class, 'PACIENTE_ID_PACIENTE', 'ID_PACIENTE');
    }
}