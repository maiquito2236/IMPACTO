<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Odontologo extends Model
{
    protected $table = 'odontologo';
    protected $primaryKey = 'ID_ODONTOLOGO';
    public $timestamps = false;

    protected $fillable = [
        'USUARIOS_ID_USUARIOS',
        'ESPECIALIDAD_ID_ESPECIALIDAD',
        'CONSULTORIO_ID_CONSULTORIO'
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'USUARIOS_ID_USUARIOS', 'ID_USUARIOS');
    }

    public function consultorio()
    {
        return $this->belongsTo(Consultorio::class, 'CONSULTORIO_ID_CONSULTORIO', 'ID_CONSULTORIO');
    }

    public function citas()
    {
        return $this->hasMany(Cita::class, 'ODONTOLOGO_ID_ODONTOLOGO', 'ID_ODONTOLOGO');
    }
}