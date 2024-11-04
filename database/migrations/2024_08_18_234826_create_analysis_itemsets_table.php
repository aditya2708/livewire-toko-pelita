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
        Schema::create('analysis_itemsets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('apriori_analysis_id')->constrained('apriori_analyses')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->float('support', 8, 4);
            $table->integer('transaction_count');
            $table->timestamps();

            // Adding a unique constraint to ensure no duplicate entries for a product in an analysis
            $table->unique(['apriori_analysis_id', 'product_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('analysis_itemsets');
    }
};
