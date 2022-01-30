<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class PositionCheck
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
        $user = $request->user;

        if($user->rol =='Admin'){
            return $next($request);
        }else{
            $answer['msg'] = "This user does not have permissions";
        }


        return response()-> json($answer);
    }
}
