<?php

namespace App\Services;

/**
 * DBServices
 *
 * classe para serviços comuns relacionados à base de dados
 */
class CURLServices
{
    public static function execCURL($url, $payload, $method, $jwt = '')
    {

        $header = array("accept: */*", 'Content-Type: application/json');
        if ($jwt) array_push($header, "x-access-token:{$jwt}");

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => strtoupper($method),
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => $header,
            CURLOPT_SSL_VERIFYPEER, FALSE
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) {
            return json_decode($err);
        } else {
            return json_decode($response);
        }
    }
}
