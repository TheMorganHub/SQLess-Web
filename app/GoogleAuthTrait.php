<?php

namespace sqless;

use sqless\Maple\MapleException;
use sqless\Maple\Utils;
use Google_Client;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Response;

trait GoogleAuthTrait {

    /**
     * Valida un ID token y, si la validación es exitosa, ejecuta el callback dado. El callback tendrá como parámetro
     * la información de usuario contenida en el ID token, y retornará lo que la función dada como callback retorne.
     * Si la validación no es exitosa, este método retornará un JSON con el status y mensaje de error correspondientes.
     *
     * @param $id_token
     * @param $source
     * @param callable|null $callback
     * @return \Illuminate\Http\JsonResponse|mixed
     */
    public function validateAndDo($id_token, $source, callable $callback = null) {
        switch ($source) {
            case 'DESKTOP':
                $client = new Google_Client(['client_id' => Config::get('constants.auth.desktop')]);
                break;
            case 'MOBILE':
                $client = new Google_Client(['client_id' => Config::get('constants.auth.mobile')]);
                break;
            default:
                return Response::json(['err' => 'La petición está malformada.'], 400);
        }

        try {
            \Firebase\JWT\JWT::$leeway = 59;
            $payload = $client->verifyIdToken($id_token);
            if ($payload) {
                $user = Googleuser::firstOrCreate(['google_id' => $payload['sub'], 'email' => $payload['email']]);
                return $callback != null ? $callback($user) : $user;
            } else {
                return Response::json(['err' => 'El token dado es inválido.'], 400);
            }
        } catch (\Exception $e) {
            if ($e instanceof MapleException) {
                $errorMessage = $e->getMessage();
            } else {
                Utils::logError($e->getMessage());
                $errorMessage = $e->getCode() == 2002 ? 'El servidor no se encuentra disponible en estos momentos. Intenta más tarde.' : 'Hubo un error al procesar la petición.';
            }
            return Response::json(['err' => $errorMessage], 500);
        }
    }
}