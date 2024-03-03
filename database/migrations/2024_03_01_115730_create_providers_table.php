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
        Schema::create('providers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('logo_url')->nullable();
            $table->boolean('status')->default(true);
            $table->longText('description')->nullable();
            $table->decimal('fee_percentage', 5, 2)->nullable();
            $table->decimal('fee_fixed', 10, 2)->nullable();
            $table->string('currency')->nullable();
            $table->decimal('minimum_amount', 15, 2)->nullable();
            $table->decimal('maximum_amount', 15, 2)->nullable();
            $table->string('website_url')->nullable();
            $table->string('documentation_url')->nullable();
            $table->string('support_email')->nullable();
            $table->string('region')->nullable();
            $table->enum('integration_difficulty', ['Easy', 'Medium', 'Hard'])->default('Medium');
            $table->string('signup_url')->nullable();
            $table->string('api_version')->nullable();
            $table->decimal('rating', 3, 2)->nullable();
            $table->boolean('featured')->default(false);
            $table->decimal('transaction_success_rate', 5, 2)->nullable();
            $table->string('average_processing_time')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('providers');
    }
};
