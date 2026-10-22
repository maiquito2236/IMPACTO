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

    /**
     * Relación con los registros de acudiente
     */
    public function relacionesAcudiente()
    {
        return $this->hasMany(PacienteHasAcudiente::class, 'PACIENTE_ID_PACIENTE', 'ID_PACIENTE');
    }

    /**
     * Usuarios adultos que actúan como acudientes de este paciente
     */
    public function acudientes()
    {
        return $this->belongsToMany(User::class, 'paciente_has_acudiente', 'PACIENTE_ID_PACIENTE', 'USUARIOS_ID_ACUDIENTE')
                    ->withPivot('PARENTESCO', 'CONTACTO_PRINCIPAL', 'AUTORIZA_TRATAMIENTOS');
    }

    /**
     * Determina si el paciente es menor de 18 años a partir de la fecha de nacimiento de su usuario
     */
    public function getEsMenorAttribute(): bool
    {
        if ($this->usuario && $this->usuario->FECHA_NACIMIENTO) {
            return \Carbon\Carbon::parse($this->usuario->FECHA_NACIMIENTO)->age < 18;
        }
        return false;
    }
}