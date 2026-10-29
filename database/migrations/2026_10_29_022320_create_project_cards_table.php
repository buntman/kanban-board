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
        Schema::create('project_cards', function (Blueprint $table) {
            $table->id('project_card_id');
            $table->foreign('project_id')->constrained('projects', 'project_id')->onDelete('cascade');
            $table->string('name', 100);
            $table->unsignedBigInteger('position')->default(1000);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_cards');
    }
};
