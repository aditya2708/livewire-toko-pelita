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
        Schema::create('restock_recommendations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('apriori_analysis_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained();
            $table->integer('transaction_count');
            $table->float('support', 8, 4);
            $table->json('related_products')->nullable();
            $table->float('recommendation_score', 8, 2);
            $table->timestamps();

            // Tambahkan indeks untuk meningkatkan performa query
            $table->index('recommendation_score');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('restock_recommendations');
    }
};
