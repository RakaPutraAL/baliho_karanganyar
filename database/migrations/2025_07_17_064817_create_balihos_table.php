<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('balihos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('opd_id')->nullable();
            $table->foreign('opd_id')->references('id')->on('opds')->onDelete('set null');
            $table->string('jenis_baliho');
            $table->string('pemasangan');
            $table->string('view');
            $table->string('dimensi');
            $table->string('jenis_kontruksi');
            $table->string('alamat');
            $table->unsignedBigInteger('kode')->nullable();
            $table->foreign('kode')->references('kode')->on('kecamatan')->onDelete('set null');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('foto')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('balihos');
    }
};
