<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'usuarios';
    protected $primaryKey = 'ID_USUARIOS';
    public $timestamps = false;

    protected $fillable = [
        'ROLES_ID_ROLES',
        'NOMBRES',
        'APELLIDOS',
        'TIPO_DOCUMENTO',
        'NUMERO_DOCUMENTO',
        'TELEFONO',
        'CORREO',
        'GENERO',
        'FECHA_NACIMIENTO',
        'DIRECCION',
        'RH',
        'CONTRASEÑA',
        'TOKEN_RECUPERACION',
        'TOKEN_EXPIRACION',
        'ESTADO',
    ];

    protected $hidden = [
        'CONTRASEÑA',
    ];

    /**
     * Nombre de la columna de contraseña para autenticación en Laravel
     */
    public function getAuthPasswordName()
    {
        return 'CONTRASEÑA';
    }

    /**
     * Anula remember_token ya que la tabla usuarios no tiene dicha columna
     */
    public function getRememberTokenName()
    {
        return null;
    }

    /**
     * Nombre completo
     */
    public function getNombreCompletoAttribute()
    {
        return trim(($this->NOMBRES ?? '') . ' ' . ($this->APELLIDOS ?? ''));
    }

    /**
     * Relación con Paciente
     */
    public function paciente()
    {
        return $this->hasOne(Paciente::class, 'USUARIOS_ID_USUARIOS', 'ID_USUARIOS');
    }

    /**
     * Relación con Odontólogo
     */
    public function odontologo()
    {
        return $this->hasOne(Odontologo::class, 'USUARIOS_ID_USUARIOS', 'ID_USUARIOS');
    }

    /**
     * Pacientes a cargo (hijos o dependientes menores de edad)
     */
    public function pacientesACargo()
    {
        return $this->belongsToMany(Paciente::class, 'paciente_has_acudiente', 'USUARIOS_ID_ACUDIENTE', 'PACIENTE_ID_PACIENTE')
                    ->withPivot('PARENTESCO', 'CONTACTO_PRINCIPAL', 'AUTORIZA_TRATAMIENTOS');
    }
}