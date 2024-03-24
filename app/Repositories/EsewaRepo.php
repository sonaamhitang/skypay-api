<?php
namespace App\Repositories;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use App\Enums\PaymentStatus;
class EsewaRepo
{

    private static $testUrl = 'https://uat.esewa.com.np/api/epay/transaction/status';
    private static $prodUrl = 'https://epay.esewa.com.np/api/epay/transaction/status';
    private static $client = null;

    /**
     * Initializes and returns the HTTP client.
     * @return Client
     */
    private static function getClient(): Client
    {
        if (self::$client === null) {
            self::$client = new Client();
        }
        return self::$client;
    }

    /**
     * Checks the status of a payment on eSewa and returns a standardized status enum.
     *
     * @param string $environment 'test' for testing or 'prod' for production environment.
     * @param array $data An array containing 'product_code', 'total_amount', 'transaction_uuid'.
     * @return PaymentStatus The standardized status of the payment.
     */
    public static function verify($environment, $data): PaymentStatus
    {
        $url = $environment === 'live' ? self::$prodUrl : self::$testUrl;
        $queryParams = [
            'query' => [
                'product_code' => $data['product_code'],
                'total_amount' => $data['total_amount'],
                'transaction_uuid' => $data['transaction_uuid'],
            ]
        ];

        try {
            $response = self::getClient()->request('GET', $url, $queryParams);
            $body = $response->getBody();
            $responseData = json_decode($body, true);

            if (!is_array($responseData)) {
                return PaymentStatus::INVALID_RESPONSE;
            }

            return match ($responseData['status'] ?? '') {
                'COMPLETE' => PaymentStatus::COMPLETE,
                'PENDING' => PaymentStatus::PENDING,
                'FULL_REFUND', 'PARTIAL_REFUND' => PaymentStatus::REFUNDED,
                'AMBIGUOUS' => PaymentStatus::AMBIGUOUS,
                'NOT_FOUND' => PaymentStatus::NOT_FOUND,
                'CANCELED' => PaymentStatus::CANCELLED,
                default => PaymentStatus::UNKNOWN
            };
        } catch (GuzzleException $e) {
            // Handle the Guzzle exception as a 'Service Unavailable' error.
            return PaymentStatus::SERVICE_UNAVAILABLE;
        }
    }
}
