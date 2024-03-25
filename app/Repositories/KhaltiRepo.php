<?php

namespace App\Repositories;

use App\Enums\ApiEnvironment;
use App\Models\Payment;
use App\Notifications\PaymentComplete;
use App\Repositories\KhaltiRepoInterface;
use Carbon\Carbon;
use Exception;
use GuzzleHttp\Client;

class KhaltiRepo
{
    public $url, $environment, $key;
    public $liveURL = "https://khalti.com/api/v2/";
    public $uatURL = "https://a.khalti.com/api/v2/";

    public function __construct(String $environment = ApiEnvironment::UAT, String $key = "live_secret_key_68791341fdd94846a146f0457ff7b455")
    {
        $this->environment = $environment;
        $this->key = $key;
        $this->url = $environment == ApiEnvironment::LIVE ? $this->liveURL : $this->uatURL;
    }

    function generateLink(Payment $payment): String
    {
        $client = new Client();

        $amount = $payment->amount;

        //CAPPING to 10-1,000 RS LIMIT
        if ($this->environment === ApiEnvironment::UAT) {
            if ($amount < 10) $amount = 10;
            else if ($amount > 1000) $amount = 1000;
        }

        $tid = rand(99999, 9999999);

        $amount = $amount * 100;
        $body = [
            "return_url" => env('FE_URL') . '/checkout/handle/khalti',
            "website_url" => env('APP_URL'),
            "amount" => $amount,
            "purchase_order_id" => "$tid",
            "purchase_order_name" => "SKYPAY_" . $tid,
            // "customer_info" => [
            //     'name' =>  $payment->customer_info['name'],
            //     'phone' =>  $payment->customer_info['phone'],
            // ]
            ];
        $response = $client->request('POST', $this->url . 'epayment/initiate/', [
            'headers' => [
                'Authorization' => 'Key ' . $this->key,
                'Content-Type' => 'application/json',
            ],
            'json' => $body
        ]);

        error_log(json_encode($body));
        error_log($response->getBody());
        $body = json_decode($response->getBody());
        $payment->payment_data = $body;
        $payment->save();
        return $body->payment_url;
    }
    function verify(Payment $payment, String $pidx): bool
    {
        $client = new Client();

        try {
            $response = $client->request('POST', $this->url . 'epayment/lookup/', [
                'headers' => [
                    'Authorization' => 'key ' . $this->key,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    "pidx" => $pidx
                ],
            ]);

            $body = json_decode($response->getBody());
            $payment->api_data = $body;
            if ($body->status === "Completed") {
                $payment->status = "complete";
                $payment->completed_at = Carbon::now();
                // $payment->user->notify(new PaymentComplete($payment));
                $payment->save();
                return true;
            } else {
                $payment->status = "failed";
                $payment->failed_at = Carbon::now();
                $payment->status_message = $body->status;
                $payment->save();
            }
            return false;
        } catch (\Exception $e) {
            return false;
        }
    }
}