<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProviderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $providers = [
            [
                'id' => (string) "001", // Generate UUID
                'code' => 'esewa',
                'name' => 'eSewa',
                'logo_url' => "https://esewa.com.np/common/images/esewa_logo.png",
                'status' => true,
                'description' => 'eSewa is a digital wallet for online payment in Nepal.',
                'fee_percentage' => null,
                'fee_fixed' => null,
                'currency' => 'NPR',
                'minimum_amount' => null,
                'maximum_amount' => null,
                'website_url' => 'https://esewa.com.np/',
                'documentation_url' => null,
                'support_email' => 'support@esewa.com.np',
                'region' => 'Nepal',
                'integration_difficulty' => 'Easy',
                'signup_url' => null,
                'api_version' => null,
                'rating' => null,
                'featured' => false,
                'transaction_success_rate' => null,
                'average_processing_time' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => (string) "002",
                'code' => 'khalti',
                'name' => 'Khalti',
                'logo_url' => "https://play-lh.googleusercontent.com/Xh_OlrdkF1UnGCnMN__4z-yXffBAEl0eUDeVDPr4UthOERV4Fll9S-TozSfnlXDFzw",
                'status' => true,
                'description' => 'Khalti is an online payment solution in Nepal offering digital wallet for instant online payment services.',
                'fee_percentage' => null,
                'fee_fixed' => null,
                'currency' => 'NPR',
                'minimum_amount' => null,
                'maximum_amount' => null,
                'website_url' => 'https://khalti.com/',
                'documentation_url' => null,
                'support_email' => 'support@khalti.com',
                'region' => 'Nepal',
                'integration_difficulty' => 'Medium',
                'signup_url' => null,
                'api_version' => null,
                'rating' => null,
                'featured' => false,
                'transaction_success_rate' => null,
                'average_processing_time' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => (string) "003",
                'code' => 'imepay',
                'name' => 'IME Pay',
                'logo_url' => "https://play-lh.googleusercontent.com/LzKjYKvzLnyMq9XaRm3RauNI-ni7QwuN4r_IzClSXUNpO6o443SDACRd92ePn03UNHU",
                'status' => true,
                'description' => 'IMEPAY is an online payment solution in Nepal offering digital wallet for instant online payment services.',
                'fee_percentage' => null,
                'fee_fixed' => null,
                'currency' => 'NPR',
                'minimum_amount' => null,
                'maximum_amount' => null,
                'website_url' => 'https://imepay.com/',
                'documentation_url' => null,
                'support_email' => 'support@imepay.com',
                'region' => 'Nepal',
                'integration_difficulty' => 'Medium',
                'signup_url' => null,
                'api_version' => null,
                'rating' => null,
                'featured' => false,
                'transaction_success_rate' => null,
                'average_processing_time' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ], [
                'id' => (string) "004",
                'code' => 'stripe',
                'name' => 'Stripe',
                'logo_url' => "https://upload.wikimedia.org/wikipedia/commons/thumb/b/ba/Stripe_Logo%2C_revised_2016.svg/2560px-Stripe_Logo%2C_revised_2016.svg.png",
                'status' => true,
                'description' => 'Something about Stripe',
                'fee_percentage' => null,
                'fee_fixed' => null,
                'currency' => 'USD',
                'minimum_amount' => null,
                'maximum_amount' => null,
                'website_url' => 'https://stripe.com/',
                'documentation_url' => null,
                'support_email' => 'support@stripe.com',
                'region' => 'US',
                'integration_difficulty' => 'Medium',
                'signup_url' => null,
                'api_version' => null,
                'rating' => null,
                'featured' => false,
                'transaction_success_rate' => null,
                'average_processing_time' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ];

        // Insert providers into the database
        DB::table('providers')->insert($providers);


        // User Payment Providers Configuration
        $userPaymentProviders = [
            [
                'id' => (string) Str::uuid(),
                'user_id' => "001",
                'provider_id' => $providers[0]['id'], // eSewa
                'alias_name' => 'My eSewa 1',
                'order' => 0,
                'payment_limit' => null,
                'is_default' => true,
                'preferences' => null,
                'manual_configuration' => json_encode(['username' => '9800000001', 'name' => "Sonam Enterprises"]),
                'mode' => 'Manual',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            [
                'id' => (string) Str::uuid(),
                'user_id' => "001",
                'provider_id' => $providers[1]['id'], //khalti
                'alias_name' => 'My Khalti 1',
                'order' => 1,
                'payment_limit' => null,
                'is_default' => false,
                'preferences' => null,
                'manual_configuration' => json_encode(['username' => '9800000001', 'name' => "Sonam Enterprises X"]),
                'mode' => 'Manual',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Add more configurations as needed

            [
                'id' => (string) Str::uuid(),
                'user_id' => "002",
                'provider_id' => $providers[0]['id'], // eSewa
                'alias_name' => '2 eSewa',
                'order' => 0,
                'payment_limit' => null,
                'is_default' => true,
                'preferences' => null,
                'manual_configuration' => json_encode(['username' => 'info@2.2', 'name' => "2 Enterprises"]),
                'mode' => 'Manual',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            [
                'id' => (string) Str::uuid(),
                'user_id' => "002",
                'provider_id' => $providers[1]['id'], //khalti
                'alias_name' => '2 Khalti',
                'order' => 1,
                'payment_limit' => null,
                'is_default' => false,
                'preferences' => null,
                'manual_configuration' => json_encode(['username' => '2@khalti', 'name' => "2 Enterprises X"]),
                'mode' => 'Manual',
                'created_at' => now(),
                'updated_at' => now(),
            ],


        ];

        // Insert user payment provider configurations into the database
        DB::table('user_payment_providers')->insert($userPaymentProviders);
    }
}
