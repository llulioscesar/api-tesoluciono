<?php

namespace App\Http\Middleware;

use Closure;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use http\Client\Curl\User;
use Illuminate\Contracts\Auth\Factory as Auth;

class Authenticate
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($req, Closure $next, $guard = null)
    {

        $token = $req->get('Authorization');


        if(!$token){
            return response()->json([
                "error" => [
                    "message" => "Token no proporcionado."
                ]
            ], 401);
        }


        try{
            $credential = JWT::decode($token, env('JWT_SECRET'), ['HS256']);
        }catch (ExpiredException $exception){
            return response()->json([
                "error" => [
                    "message" => "El token proporcionado ha caducado."
                ]
            ], 400);
        } catch (Exception $exception){
            return response()->json([
                "error" => [
                    "message" => "Un error al decodificar el token."
                ]
            ], 400);
        }

        $user = User::find($credential->sub->id);

        if($user != null){
            if($user->reset_pass == 0){
                $req->user = $user;
                return $next($req);
            } else {
                return response()->json([
                    'error' => [
                        'message' => 'El usuario debe cambiar la contraseña'
                    ]
                ], 400);
            }
        }

        return response()->json([
            'error' => [
                'message' => 'No se encontro el usuario'
            ]
        ], 400);


    }
}
