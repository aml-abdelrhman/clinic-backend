<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
 public function up()
{
    Schema::create('doctors', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null'); // ربط بحساب الدكتور
        $table->foreignId('specialty_id')->constrained(); // ربط بالتخصص
        $table->json('name');
        $table->json('bio')->nullable();
        $table->integer('years_experience')->default(0);
        $table->decimal('rating', 3, 2)->default(5.0);
        $table->string('image')->nullable();
        $table->json('languages')->nullable(); // لتخزين اللغات (['Arabic', 'English'])
        $table->decimal('price_from', 8, 2);
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('doctors');
    }
};
