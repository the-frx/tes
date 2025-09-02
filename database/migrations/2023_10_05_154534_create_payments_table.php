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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->date('startDate');
            $table->date('endDate');
            $table->unsignedBigInteger('network_id');
            $table->string('method', 255);
            $table->string('imgurl');
            $table->decimal('ballance', 8, 2);
            $table->decimal('rate', 8, 2);
            $table->decimal('amount', 8, 2);
            $table->unsignedBigInteger('user_id');
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
        Schema::dropIfExists('payments');
    }
};
