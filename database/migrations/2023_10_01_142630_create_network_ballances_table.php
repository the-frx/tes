<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('network_ballances', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('network_id');
            $table->decimal('balance', 8, 2);

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('network_id')->references('id')->on('networks')->onDelete('cascade');

            $table->unique(['user_id', 'network_id']); // memastikan kombinasi user dan network adalah unik
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('network_ballances');
    }
};
