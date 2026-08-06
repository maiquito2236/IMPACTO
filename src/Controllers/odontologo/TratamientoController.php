<?php
namespace App\Controllers\odontologo;

use App\Models\odontologo\TratamientoModel;

class TratamientoController {

    private $modelo;

    public function __construct() {
        $this->modelo = new TratamientoModel();
    }

    public function listar() {
        header('Content-Type: application/json');
        // Llamamos al nuevo método del modelo
        echo json_encode($this->modelo->listarHistoricoClinico());
        exit;
    }
}