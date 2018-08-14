<?php

namespace sqless\Http\Controllers;

use sqless\GoogleAuthTrait;
use sqless\Maple\Parser;
use sqless\Maple\Utils;
use Illuminate\Http\Request;

class MapleController extends Controller {
    use GoogleAuthTrait;

    /**
     * Parsea la sentencia Maple dada sin necesidad de estar autenticado.
     *
     * @param Request $request
     * @return array Un array con la sentencia Maple convertida a SQL. Si hubo algún error, se retornará un array con el mensaje de error.
     */
    public function parse(Request $request) {
        try {
            $mapleStmt = $request->get('maple_statement');
            $stmt = Parser::processMaple($mapleStmt == null ? '' : $mapleStmt, null, 'WEB');
            return ['sql_from_maple' => $stmt];
        } catch (\Exception $e) {
            return ['err' => $e->getMessage()];
        }
    }

    /**
     * Autentica las credenciales dadas y, si la autenticación es exitosa, parsea la sentencia Maple dada y la convierte a SQL.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|mixed Si la validación es exitosa, retornará lo que retorne la función dada
     * como callback. En cambio, si no lo es, retornará un JSON con mensaje de error.
     */
    public function parseAuthenticated(Request $request) {
        return $this->validateAndDo($request->get('id_token'), $request->get('source'), function ($user) use ($request) {
            $mapleStmt = $request->get('maple_statement');
            $stmt = Parser::processMaple($mapleStmt == null ? '' : $mapleStmt, $user->id, $request->get('source'));
            return ['sql_from_maple' => $stmt];
        });
    }
}
