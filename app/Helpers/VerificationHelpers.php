<?php
/**
 * Created by PhpStorm.
 * User: Zwei
 * Date: 9/26/2019
 * Time: 6:02 PM
 */

namespace App\Helpers;

use App\Mail\VerificationCode;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;

class  VerificationHelpers
{
    public static function sendVerificationCode($user, $verificationCode)
    {
//        if(env('APP_ENV') === "local") return;
//        $args = http_build_query(array(
//            'auth_token' => '4ea006f9a9268d53ab935f7c1f3b7badc444dddbc837ee04ca4ec26273281ba0',
//            'to' => $user->phone,
//            'text' => 'Bhoklayo! Hi ✋, ' . $verificationCode . ' is your verification code for Bhoklayo App.'));
//        $url = "https://sms.aakashsms.com/sms/v3/send";
//
//        # Make the call using API.
//        $ch = curl_init();
//        curl_setopt($ch, CURLOPT_URL, $url);
//        curl_setopt($ch, CURLOPT_POST, 1); ///
//        curl_setopt($ch, CURLOPT_POSTFIELDS, $args);
//        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
//        // Response
//        $response = curl_exec($ch);
//        curl_close($ch);

        Mail::to($user->email )->send(new VerificationCode($verificationCode, $user->name));

    }

    static function generateVerificationCode()
    {
        if(env('APP_ENV') === "local") return 12345; //for testing purposes
        return rand(10000, 99999);
    }
}

?>
