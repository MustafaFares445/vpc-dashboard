<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('client_interactions', function (Blueprint $table): void {
            $table->foreignId('employee_id')
                ->nullable()
                ->after('user_id')
                ->constrained('employees')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('client_interactions', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('employee_id');
        });
    }
};
