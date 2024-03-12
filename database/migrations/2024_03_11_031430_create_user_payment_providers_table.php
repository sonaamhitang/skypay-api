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
        Schema::create('user_payment_providers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id')->nullable();
            $table->uuid('provider_id')->nullable();
            $table->string('alias_name')->nullable();
            $table->integer('order')->default(0);
            $table->decimal('payment_limit', 15, 2)->nullable();
            $table->boolean('is_default')->default(false);

            $table->longText('preferences')->nullable();

            $table->longText('manual_configuration')->nullable(); // For Manual settings
            $table->longText('api_configuration')->nullable(); // For API
            $table->longText('assisted_configuration')->nullable(); // For Assisted

            $table->longText('notes')->nullable();
            $table->boolean('status')->default(true);

            $table->enum('mode', ['Manual', 'API', 'Assisted'])->default('Manual');

            $table->longText('credentials')->nullable(); // Merchant IDs or other credentials
            $table->string('settlement_frequency')->nullable(); // Daily, Weekly, etc. for Assisted mode
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('provider_id')->references('id')->on('providers')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_payment_providers');
    }
};
