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
        Schema::create('analysis_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('apriori_analysis_id')->constrained()->onDelete('cascade');
            $table->text('antecedents');
            $table->text('consequents');
            $table->float('support');
            $table->float('confidence');
            $table->float('lift');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('analysis_rules');
    }
};
