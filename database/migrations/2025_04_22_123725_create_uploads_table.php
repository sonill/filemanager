<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up(): void {
		Schema::create('uploads', function (Blueprint $table) {
			$table->id();

			$table->string('upload_path');
			$table->string('ext');
			$table->string('disk');
			$table->string('mime_type')->nullable();
			$table->string('collection')->nullable();
			$table->unsignedBigInteger('size')->nullable()->comment('in kb');
			$table->string('tags')->nullable()->index();

			$table->timestamps();
		});
	}

	public function down(): void {
		Schema::dropIfExists('uploads');
	}
};
