<?php


namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class JsonRequestMiddleware
{
    public function handle(Request $req, Closure $next){
        if(in_array($req->method(), ['POST', 'PUT', 'PATCH']) && $req->isJson()){
            $data = $req->json()->all();
            $req->request->replace(is_array($data) ? $data : []);
        }
        return $next($req);
    }
}
