<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Consultorio;

class ConsultorioController extends Controller
{
    public function index()
    {
        // 1. Le pedimos al modelo que traiga TODOS los consultorios
        $consultorios = Consultorio::all();

        // 2. Retornamos una vista (que crearemos en el paso 3) y le pasamos los datos
        return view('admin.consultorios.index', compact('consultorios'));
    }

        // Esta función recibe el $request, que es donde vienen todos los datos del formulario
    public function store(Request $request)
    {
        // 1. Le decimos al modelo que cree un nuevo registro
        Consultorio::create([
            'NOMBRE' => $request->NOMBRE,
            'UBICACION' => $request->UBICACION,
            'DESCRIPCION' => $request->DESCRIPCION,
            'ESTADO' => 'DISPONIBLE' // Le ponemos este valor por defecto al crearlo
        ]);

        // 2. Redireccionamos al usuario de vuelta a la tabla
        return redirect('/admin/consultorios');
    }
}
