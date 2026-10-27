@extends('layouts.odontologo')

@section('titulo', 'Odonto Estética - Planes de Tratamiento')

@push('css')
    <link rel="stylesheet" href="{{ asset('css/odontologo/planes_tratamientos_odon.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
@endpush

@php
    if (!isset($paciente_nombre)) $paciente_nombre = "Seleccione Paciente";
    if (!isset($id_paciente)) $id_paciente = "";
    if (!isset($documento)) $documento = "";
    if (!isset($tratamiento_motivo)) $tratamiento_motivo = "Ninguno asignado";
@endphp

@section('contenido')
<main class="main-content-area">
    <h2 class="section-dashboard-title">
        Planes de Tratamiento
    </h2>

    <div class="patient-banner-container" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
        <div class="patient-banner-left" style="display: flex; align-items: center; gap: 16px;">
            <div class="patient-avatar-wrapper">
                <div class="patient-avatar-initials" id="avatarInitials" style="background-color: {{ !empty($id_paciente) ? '#1a56db' : '#94a3b8' }};">
                    @php 
                        if (!empty($paciente_nombre) && $paciente_nombre !== 'Ningún paciente seleccionado' && $paciente_nombre !== 'Seleccione Paciente') {
                            $partes = explode(' ', trim($paciente_nombre));
                            echo strtoupper(isset($partes[1]) ? $partes[0][0].$partes[1][0] : substr($partes[0], 0, 2));
                        } else {
                            echo "--";
                        }
                    @endphp
                </div>
            </div>
            
            <div class="patient-info-text">
                <h3 class="patient-name-title" id="bannerPatientName" style="color: {{ !empty($id_paciente) ? '#0f172a' : '#64748b' }};">
                    {{ $paciente_nombre }}
                </h3>
                <p class="patient-meta-sub" id="bannerPatientMeta">
                    @if (!empty($id_paciente))
                        <strong>Doc:</strong> {{ $documento ?: 'N/A' }} &bull; 
                        <strong>Motivo:</strong> {{ $tratamiento_motivo ?: 'Plan de tratamiento general' }}
                        @if (!empty($id_cita))
                            &bull; <span style="background:#e0f2fe;color:#0369a1;padding:2px 8px;border-radius:4px;font-size:11px;font-weight:600;">Cita #{{ $id_cita }}</span>
                        @endif
                    @else
                        <span style="color: #64748b;">Seleccione una cita desde su Agenda o elija un paciente para iniciar su atención.</span>
                    @endif
                </p>
            </div>
        </div>
        
        <div class="actions-container" style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
            <div style="min-width: 240px;">
                <select id="select-paciente-cambio" class="form-control" style="padding: 9px 12px; border-radius: 6px; border: 1px solid #cbd5e1; font-size: 13px; width: 100%; background: #ffffff; cursor: pointer;" onchange="if(this.value) window.location.href='/odontologo/planes?paciente_id='+this.value">
                    <option value="">-- Seleccionar Paciente --</option>
                    @if(isset($lista_pacientes))
                        @foreach($lista_pacientes as $pac)
                            <option value="{{ $pac->ID_PACIENTE }}" 
                                    data-es-infantil="{{ !empty($pac->es_infantil) ? '1' : '0' }}" 
                                    data-edad="{{ $pac->edad ?? '' }}" 
                                    {{ (!empty($id_paciente) && $id_paciente == $pac->ID_PACIENTE) ? 'selected' : '' }}>
                                {{ $pac->NOMBRE_COMPLETO }} (Doc: {{ $pac->NUMERO_DOCUMENTO }}) {{ !empty($pac->es_infantil) ? '👶 [Infantil]' : '' }}
                            </option>
                        @endforeach
                    @endif
                </select>
            </div>

            <a href="{{ url('/odontologo/agenda') }}" class="btn-primary" style="background-color: #1a56db; color: white; padding: 10px 16px; border-radius: 6px; text-decoration: none; font-size: 13px; font-weight: 600; display: inline-flex; align-items: center; gap: 6px;">
                <i class="fa-solid fa-calendar-days"></i> Ver Agenda
            </a>

            <button type="button" class="btn-success-action" id="btn-guardar-enviar-historial" style="background-color: {{ !empty($id_paciente) ? '#2bc48a' : '#94a3b8' }}; color: white; padding: 10px 18px; border: none; border-radius: 6px; font-weight: bold; cursor: {{ !empty($id_paciente) ? 'pointer' : 'not-allowed' }}; font-size: 13px; display: inline-flex; align-items: center; gap: 6px;">
                 <i class="fa-solid fa-floppy-disk"></i> Guardar y enviar a historial clínico
            </button>
        </div>
    </div>

    <div class="workspace-grid">
        <section class="ui-panel panel-odontogram">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px; flex-wrap: wrap; gap: 10px;">
                <h3 style="margin: 0;" id="odontograma-header-title">
                    Odontograma Clínico {{ !empty($es_infantil) ? '(Infantil)' : '(Adulto)' }}
                </h3>

                <!-- SELECTOR DE TIPO: ADULTO / INFANTIL -->
                <div class="odontogram-type-selector" style="display: inline-flex; background: #f1f5f9; padding: 4px; border-radius: 8px; gap: 4px; border: 1px solid #cbd5e1;">
                    <button type="button" id="btn-odo-adulto" class="btn-odo-toggle {{ empty($es_infantil) ? 'active' : '' }}" style="border: none; padding: 6px 14px; border-radius: 6px; font-size: 13px; font-weight: 600; cursor: pointer; transition: all 0.2s; background: {{ empty($es_infantil) ? '#1a56db' : 'transparent' }}; color: {{ empty($es_infantil) ? '#ffffff' : '#64748b' }}; display: inline-flex; align-items: center; gap: 6px;">
                        <i class="fa-solid fa-user"></i> Adulto (32)
                    </button>
                    <button type="button" id="btn-odo-infantil" class="btn-odo-toggle {{ !empty($es_infantil) ? 'active' : '' }}" style="border: none; padding: 6px 14px; border-radius: 6px; font-size: 13px; font-weight: 600; cursor: pointer; transition: all 0.2s; background: {{ !empty($es_infantil) ? '#1a56db' : 'transparent' }}; color: {{ !empty($es_infantil) ? '#ffffff' : '#64748b' }}; display: inline-flex; align-items: center; gap: 6px;">
                        <i class="fa-solid fa-child"></i> Infantil (20)
                    </button>
                </div>
            </div>

            <!-- ODONTOGRAMA ADULTO (1 al 32) -->
            <div class="odontogram-visualizer-box" id="visualizer-adulto" style="display: {{ empty($es_infantil) ? 'flex' : 'none' }};">
                <div class="dental-arch arch-upper">
                    @for($i = 1; $i <= 16; $i++)
                        <a href="#modal-registrar-plan" class="tooth-item tooth-select" data-tooth="{{ $i }}">
                            {{ $i }}
                        </a>
                    @endfor
                </div>

                <div class="jaw-divider-line"></div>

                <div class="dental-arch arch-lower">
                    @for($i = 17; $i <= 32; $i++)
                        <a href="#modal-registrar-plan" class="tooth-item tooth-select" data-tooth="{{ $i }}">
                            {{ $i }}
                        </a>
                    @endfor
                </div>
            </div>

            <!-- ODONTOGRAMA INFANTIL (1 al 20: 1-10 superior, 11-20 inferior) -->
            <div class="odontogram-visualizer-box" id="visualizer-infantil" style="display: {{ !empty($es_infantil) ? 'flex' : 'none' }};">
                <div class="dental-arch arch-upper">
                    @for($i = 1; $i <= 10; $i++)
                        <a href="#modal-registrar-plan" class="tooth-item tooth-select" data-tooth="{{ $i }}">
                            {{ $i }}
                        </a>
                    @endfor
                </div>

                <div class="jaw-divider-line"></div>

                <div class="dental-arch arch-lower">
                    @for($i = 11; $i <= 20; $i++)
                        <a href="#modal-registrar-plan" class="tooth-item tooth-select" data-tooth="{{ $i }}">
                            {{ $i }}
                        </a>
                    @endfor
                </div>
            </div>

            <div class="odontogram-utility-bar">
                <button type="button" class="btn-utility" id="resetTeeth" title="Limpiar Selección de Piezas">
                    <i class="fa-solid fa-rotate-right" style="margin-right: 5px;"></i> Reiniciar Selección
                </button>
            </div>
        </section>

        <div class="metrics-and-phases-column">
            <section class="ui-panel phase-tracking-panel">
                <div class="panel-inner-header">
                    <div class="title-with-status">
                        <h4>Historial de Fases / Tratamientos</h4>
                        <p>Procedimientos registrados en Historia Clínica</p>
                    </div>
                    <span class="badge-status-active" style="background-color: {{ !empty($procedimientos_paciente) ? '#2bc48a' : '#6b7280' }};">
                        {{ !empty($procedimientos_paciente) ? 'En Curso' : 'Sin Plan' }}
                    </span>
                </div>

                <div class="sub-tab-bar">
                    <span class="tab-link active">Plan de Fase</span>
                </div>

                <div class="phases-table-list" id="contenedor-fases-dinamicas">
                    @if (!empty($procedimientos_paciente))
                        @foreach ($procedimientos_paciente as $index => $fase)
                            @if ($fase['TIPO_SEGUIMIENTO'] === 'EVOLUCION_FASES')
                            <div class="table-row-item" 
                                 style="display: flex; justify-content: space-between; padding: 12px 10px; border-bottom: 1px solid #eee; align-items: center; 
                                        border-left: 4px solid {{ $fase['TIPO_SEGUIMIENTO'] === 'EVOLUCION_FASES' ? '#a855f7' : '#2bc48a' }}; margin-bottom: 5px; background: #fff;">
                                
                                <span class="row-cell-name" style="display: flex; align-items: center; gap: 10px;">
                                    <strong>Fase {{ $index + 1 }}:</strong> 
                                    
                                    <div>
                                        <span style="font-weight: 600; color: #1f2937;">{{ $fase['NOMBRE_PROCEDIMIENTO'] }}</span>
                                        
                                        @if ($fase['TIPO_SEGUIMIENTO'] === 'EVOLUCION_FASES')
                                            <small style="color: #a855f7; display: block; font-size: 0.75rem;">• Control Continuo / Evolución</small>
                                        @else
                                            <small style="color: #2bc48a; display: block; font-size: 0.75rem;">✓ Sesión Inmediata</small>
                                        @endif

                                        @if (!empty($fase['NOTAS']))
                                            <small style="color: #6b7280; font-size: 0.7rem; display: block; max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="{{ $fase['NOTAS'] }}">
                                                Obs: {{ $fase['NOTAS'] }}
                                            </small>
                                        @endif
                                        
                                        <small style="color: #6b7280; display: block; font-size: 0.75rem; margin-top: 2px;">
                                            <i class="fa-regular fa-calendar" style="color: #2bc48a; margin-right: 3px;"></i> {{ !empty($fase['FECHA_REGISTRO']) ? date('d/m/Y', strtotime($fase['FECHA_REGISTRO'])) : 'N/A' }}
                                        </small>
                                    </div>

                                    <span style="background: {{ $fase['PIEZA_DENTAL'] !== 'Gral' ? '#e0f2fe' : '#f3e8ff' }}; 
                                                 color: {{ $fase['PIEZA_DENTAL'] !== 'Gral' ? '#0369a1' : '#6b21a8' }}; 
                                                 padding: 2px 6px; border-radius: 4px; font-size: 0.7rem; font-weight: bold; white-space: nowrap;">
                                        {{ $fase['PIEZA_DENTAL'] !== 'Gral' ? 'Pieza ' . $fase['PIEZA_DENTAL'] : 'Boca Completa' }}
                                    </span>
                                </span>
                                
                                <span class="tag-phase-state" style="color: {{ strtolower($fase['ESTADO_FASE']) === 'hecho' ? '#2bc48a' : '#f59e0b' }}; font-weight: 600; font-size: 0.85rem;">
                                    {{ $fase['ESTADO_FASE'] }}
                                </span>
                                
                                <span class="row-cell-price" style="font-weight: bold; color: #374151;">
                                    ${{ number_format($fase['PRECIO_APLICADO'] * $fase['CANTIDAD'], 2) }}
                                </span>
                            </div>
                            @endif
                        @endforeach
                    @else
                        <div style="text-align: center; padding: 35px 15px; color: #6b7280;">
                            <div style="font-size: 2.5rem; margin-bottom: 10px; color: #d1d5db;">
                                <i class="fa-solid fa-folder-open"></i>
                            </div>
                            <h5 style="margin-bottom: 5px; color: #4b5563;">Sin tratamientos</h5>
                            <p style="font-size: 0.85rem; max-width: 220px; margin: 0 auto;">
                                Selecciona piezas en el odontograma para armar el plan de fases de este paciente.
                            </p>
                        </div>
                    @endif
                </div>
            </section>

            <section class="ui-panel budget-summary-panel">
                <h5>Presupuesto y Balance Financiero</h5>
                <div class="total-amount-lbl" id="monto-total-fase" data-base-total="{{ $total_presupuesto }}">
                    Total Estimado: ${{ number_format($total_presupuesto, 2) }}
                </div>
                <div class="acceptance-pill" style="background-color: {{ $total_presupuesto > 0 ? '#e0f2fe' : '#f3f4f6' }}; color: {{ $total_presupuesto > 0 ? '#0369a1' : '#6b7280' }};">
                    <i class="fa-solid fa-circle-check"></i> {{ $total_presupuesto > 0 ? 'Registro Vinculado' : 'Esperando Diagnóstico' }}
                </div>
            </section>
        </div>
    </div>
</main>

<!-- Modal Registrar Plan -->
<div id="modal-registrar-plan" class="modal-overlay-backdrop">
    <div class="modal-dialog-box">
        <div class="modal-header-bar">
            <h3>Registrar Evolución en Odontograma</h3>
            <button type="button" class="btn-close-modal">&times;</button>
        </div>

        <div class="modal-form-content-split">
            <div class="selected-tooth-preview">
                <img src="https://cdn-icons-png.flaticon.com/512/2966/2966486.png" alt="Diente" id="toothImage">
                <h4 id="selectedTooth">Selecciona una pieza dental</h4>
            </div>

            <div class="modal-right-inputs-pane">
                <div class="form-group-field">
                    <label>Paciente Asignado</label>
                    <input type="text" class="form-control" id="patientInput" value="{{ $paciente_nombre }}" readonly>
                    <input type="hidden" id="patientIdInput" value="{{ $id_paciente }}">
                    <input type="hidden" id="citaIdInput" value="{{ $id_cita ?: '' }}">
                    <input type="hidden" id="esInfantilDefault" value="{{ !empty($es_infantil) ? '1' : '0' }}">
                </div>

                <div class="form-group-field">
                    <label>Fecha de Registro</label>
                    <input type="date" class="form-control" id="dateInput">
                </div>

                <div class="form-group-field">
                    <label>Procedimientos Clínicos</label>
                    <div id="selectProcedimientos" class="checkbox-group" style="max-height: 120px; overflow-y: auto; border: 1px solid #d1d5db; border-radius: 4px; padding: 10px; background: #fff;"></div>
                </div>

                <div class="form-group-field">
                    <label>Notas de Evolución / Diagnóstico</label>
                    <textarea class="form-control textarea-custom" id="notesInput" rows="3" placeholder="Escribe las observaciones de la pieza..."></textarea>
                </div>
            </div>
        </div>

        <div class="modal-footer-bar">
            <button type="button" class="btn-secondary btn-close-modal">Cancelar</button>
            <button type="button" class="btn-primary-submit" id="saveTreatment">Guardar Plan</button>
        </div>
    </div>
</div>

<div class="success-message" id="successMessage">
    <i class="fa-solid fa-circle-check"></i> Tratamiento guardado exitosamente en el Odontograma
</div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('js/odontologo/planes_tratamientos_odon.js') }}" defer></script>
@endpush
