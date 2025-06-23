<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('uploadables', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('upload_id')->index();
            $table->morphs('uploadable');

            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('uploadables');
    }
};
