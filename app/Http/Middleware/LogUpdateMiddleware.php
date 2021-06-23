<?php

namespace App\Http\Middleware;

use App\Models\CallLog;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LogUpdateMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $log = [
            'user_id'       => auth('api')->id(),
            'path'          => $request->getRequestUri(),
            'request_body'  => json_encode($request->all()),
            'method'        => $request->getMethod(),
        ];

        if(!in_array($log['method'], ['POST', 'PUT'])){
            return $next($request);
        }

        CallLog::create($log);

        return $next($request);
    }
}
