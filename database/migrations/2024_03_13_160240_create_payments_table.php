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
            $table->uuid('id')->primary();
            $table->string('code');
            $table->uuid('user_id')->nullable();
            $table->uuid('user_payment_provider_id')->nullable();

            // $table->unique(['user_payment_provider_id', 'code']);

            $table->longText('success_url')->nullable();
            $table->longText('failure_url')->nullable();

            $table->decimal('amount', 10, 2)->default(0);

            $table->longText('notes')->nullable();

            $table->longText('data')->nullable();

            $table->longText('other_info')->nullable();
            $table->longText('customer_info')->nullable();
            $table->longText('amount_info')->nullable();

            $table->enum('status', ['Pending', 'Waiting', 'Paid', 'Cancelled', 'Invalid'])->default('Pending');

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('user_payment_provider_id')->references('id')->on('user_payment_providers')->onDelete('cascade');

            $table->timestamp('expires_at');
            $table->timestamp('marked_paid_at')->nullable(); //customer side
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('invalid_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
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
