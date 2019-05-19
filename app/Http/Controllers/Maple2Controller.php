<?php

namespace sqless\Http\Controllers;

use Illuminate\Http\Request;

class Maple2Controller extends Controller {

    public function call(Request $request) {
        $mapleStatement = is_null($request->get('maple_statement')) ? '' : $request->get('maple_statement');

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://maplews.herokuapp.com/convert",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode(array('statement' => $mapleStatement)),
            CURLOPT_HTTPHEADER => array(
                "Content-Type: application/json",
            ),
        ));
        $curlResp = curl_exec($curl);
        $statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $response = json_decode($curlResp, true);
        if ($statusCode != 200 || !isset($response['result'])) {
            return ['err' => 'There has been an error contacting the Maple web service. Please try again later.'];
        }

        if ($response['result']['errors']) {
            return ['err' => $response['result']['errorMessage']];
        }

        return ['sql_from_maple' => $response['result']['sqlStatements']];
    }

}