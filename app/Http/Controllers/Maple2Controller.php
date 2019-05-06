<?php

namespace sqless\Http\Controllers;

use Illuminate\Http\Request;

class Maple2Controller extends Controller {

    public function call(Request $request) {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://maplews.herokuapp.com/convert",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode(array('statement' => $request->get('maple_statement'))),
            CURLOPT_HTTPHEADER => array(
                "Content-Type: application/json",
            ),
        ));
        $response = json_decode(curl_exec($curl), true);
        $err = curl_error($curl);

        return ['sql_from_maple' => $response['sqlStatement']];
    }

}