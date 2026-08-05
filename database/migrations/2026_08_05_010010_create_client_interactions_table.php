<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_interactions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('contacted_at')->index();
            $table->string('contact_method')->nullable();
            $table->text('note');
            $table->timestamp('next_follow_up_at')->nullable()->index();
            $table->timestamps();

            $table->index(['client_id', 'contacted_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_interactions');
    }
};
