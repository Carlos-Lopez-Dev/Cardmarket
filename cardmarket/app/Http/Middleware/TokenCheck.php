<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\users;


class TokenCheck
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
       if($request->has('api_token')){

            //Comprobar que existe un usuario con ese token
            $apiToken = $request->input('api_token');
            $user = users::where('api_token', $apiToken)->first();

            if(!$user){

                $answer['msg'] = "El usuario no existe";

            }else{

                $request->user = $user;
                return $next($request);

            }

        }else{
            $answer['msg'] = "Token vacio";
        }

        return response()->json($answer);

    }
    
}
