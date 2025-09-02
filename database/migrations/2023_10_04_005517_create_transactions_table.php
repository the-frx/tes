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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('network_id');
            $table->enum('type', ['Bonus', 'Convertion', 'Payout']);
            $table->decimal('ballance', 8, 2)->default(0);
            $table->decimal('amount', 8, 2)->default(0);
            $table->unsignedBigInteger('user_id');
            $table->boolean('is_read')->default(false);
            $table->foreign('network_id')->references('id')->on('networks')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
