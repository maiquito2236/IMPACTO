@extends('layouts.admin')

@section('titulo', 'Inicio - Panel Admin')

@section('contenido')
    <div style="background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
        <h2 style="color: #1e293b; margin-bottom: 10px;">¡Tu distribución en Laravel está funcionando!</h2>
        <p style="color: #64748b;">Si estás viendo este recuadro blanco dentro de tu diseño normal, significa que el sistema de Layouts de Blade está perfectamente configurado.</p>
        
        <br>
        <p>Próximo paso: Pasar el código de tus tarjetas (KPIs) y gráficos a este archivo.</p>
    </div>
@endsection