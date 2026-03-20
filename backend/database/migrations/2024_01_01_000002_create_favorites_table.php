<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('favorites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('place_id');
            $table->string('name');
            $table->string('address')->nullable();
            $table->string('phone')->nullable();
            $table->tinyInteger('price_level')->unsigned()->nullable();
            $table->decimal('rating', 3, 1)->nullable();
            $table->integer('user_ratings_total')->unsigned()->nullable();
            $table->string('photo_url', 2048)->nullable();
            $table->decimal('lat', 10, 7);
            $table->decimal('lng', 10, 7);
            $table->timestamps();
            $table->unique(['user_id', 'place_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('favorites');
    }
};
