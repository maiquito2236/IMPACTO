<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Modificar tabla usuarios para permitir R.C y campos opcionales en menores
        DB::statement("ALTER TABLE usuarios 
            MODIFY COLUMN TIPO_DOCUMENTO ENUM('R.C','T.I','C.C','C.E','PPT') NOT NULL,
            MODIFY COLUMN TELEFONO VARCHAR(45) NULL DEFAULT NULL,
            MODIFY COLUMN CORREO VARCHAR(200) NULL DEFAULT NULL,
            MODIFY COLUMN `CONTRASEÑA` VARCHAR(250) NULL DEFAULT NULL");

        // 2. Crear tabla intermedia paciente_has_acudiente si no existe
        if (!Schema::hasTable('paciente_has_acudiente')) {
            Schema::create('paciente_has_acudiente', function (Blueprint $table) {
                $table->integer('ID_PACIENTE_HAS_ACUDIENTE', true);
                $table->integer('PACIENTE_ID_PACIENTE');
                $table->integer('USUARIOS_ID_ACUDIENTE');
                $table->enum('PARENTESCO', ['Madre', 'Padre', 'Abuelo/a', 'Tío/a', 'Tutor Legal', 'Otro'])->default('Madre');
                $table->enum('CONTACTO_PRINCIPAL', ['Si', 'No'])->default('Si');
                $table->enum('AUTORIZA_TRATAMIENTOS', ['Si', 'No'])->default('Si');
                $table->timestamp('FECHA_VINCULACION')->useCurrent();

                $table->unique(['PACIENTE_ID_PACIENTE', 'USUARIOS_ID_ACUDIENTE'], 'uk_paciente_acudiente');
                $table->foreign('PACIENTE_ID_PACIENTE', 'fk_pha_paciente')
                      ->references('ID_PACIENTE')->on('paciente')
                      ->onDelete('cascade')->onUpdate('cascade');
                $table->foreign('USUARIOS_ID_ACUDIENTE', 'fk_pha_acudiente')
                      ->references('ID_USUARIOS')->on('usuarios')
                      ->onDelete('cascade')->onUpdate('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('paciente_has_acudiente');
    }
};
