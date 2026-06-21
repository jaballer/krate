<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vinyl_records', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('artist');
            $table->string('genre', 100)->nullable();
            $table->integer('release_year')->nullable();
            $table->string('label')->nullable();
            $table->string('catalog_number')->nullable();
            $table->enum('format', ['12"', '10"', '7"']);
            $table->enum('speed', ['33 1/3 RPM', '45 RPM', '78 RPM']);
            $table->enum('condition', ['Mint', 'Near Mint', 'Very Good', 'Good', 'Fair', 'Poor']);
            $table->date('purchase_date')->nullable();
            $table->decimal('purchase_price', 10, 2)->nullable();
            $table->text('notes')->nullable();
            $table->string('front_image')->nullable();
            $table->string('back_image')->nullable();
            $table->string('purchase_link')->nullable();
            $table->string('audio_file_url')->nullable();
            $table->integer('bpm')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vinyl_records');
    }
};
