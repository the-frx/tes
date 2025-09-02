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
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->unique()->after('name');
            $table->string('id_card')->nullable()->after('email');
            $table->boolean('custom_fee')->default(false)->after('id_card');
            $table->integer('fee')->default(0)->after('custom_fee');
            $table->decimal('ballance', 8, 2)->default(0)->after('fee');
            $table->string('referal')->nullable()->default('system')->after('ballance');
            $table->boolean('is_active')->default(false)->after('referal');
            $table->boolean('is_admin')->default(false)->after('is_active');
        });
    }
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['username', 'id_card', 'fee', 'custom_fee', 'ballance', 'referal', 'is_active', 'is_admin']);
        });
    }
};
