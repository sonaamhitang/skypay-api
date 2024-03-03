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
        Schema::create('users', function (Blueprint $table) {
            $table->uuid('id')->primary(); // Use UUID for a globally unique identifier
            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone')->unique()->nullable();
            $table->string('api_key')->unique()->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamp('phone_verified_at')->nullable();
            $table->string('phone_otp')->nullable();
            $table->string('password');

            $table->string('avatar_url')->nullable();

            $table->datetime('last_login_at')->nullable();
            $table->string('last_login_ip')->nullable();

            $table->string('business_name')->nullable();
            $table->string('business_type')->nullable();
            $table->string('business_legal_type')->nullable();
            $table->string('business_legal_number')->nullable();
            $table->string('fcm_token')->nullable();
            $table->string('status')->default('Active');

            $table->string('subscription_plan')->default('Free');
            $table->timestamp('subscription_expiry')->nullable();
            $table->decimal('balance', 10, 2)->default(0.00);
            $table->uuid('parent_id')->nullable();
            $table->foreign('parent_id')->references('id')->on('users')->onDelete('cascade');
            $table->rememberToken();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
