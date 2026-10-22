<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PacienteHasAcudiente extends Model
{
    protected $table = 'paciente_has_acudiente';
    protected $primaryKey = 'ID_PACIENTE_HAS_ACUDIENTE';
    public $timestamps = false;

    protected $fillable = [
        'PACIENTE_ID_PACIENTE',
        'USUARIOS_ID_ACUDIENTE',
        'PARENTESCO',
        'CONTACTO_PRINCIPAL',
        'AUTORIZA_TRATAMIENTOS',
        'FECHA_VINCULACION',
    ];

    /**
     * Paciente (menor o dependiente)
     */
    public function paciente()
    {
        return $this->belongsTo(Paciente::class, 'PACIENTE_ID_PACIENTE', 'ID_PACIENTE');
    }

    /**
     * Usuario acudiente / tutor
     */
    public function acudiente()
    {
        return $this->belongsTo(User::class, 'USUARIOS_ID_ACUDIENTE', 'ID_USUARIOS');
    }
}
