<?php

namespace sqless\Http\Controllers;

use sqless\GoogleAuthTrait;
use sqless\Logger;
use sqless\Maple\Utils;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class GoogleUserController extends Controller {
    use GoogleAuthTrait;

    public function __construct() {
    }

    public function validateOrCreate(Request $request) {
        return $this->validateAndDo($request->get('id_token'), $request->get('source'), function ($user) use ($request) {
            if (Logger::login_by_user($user, $request->get('source'))) {
                return $user;
            } else {
                return Response::json(['err' => 'Hubo un error al iniciar sesión'], 500);
            }
        });
    }
}
