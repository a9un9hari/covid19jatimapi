<?php

namespace App\Http\Middleware;

use Irazasyed\LaravelGAMP\Facades\GAMP;
use Closure;

class GoogleAnalyticMiddleware
{
    /**
     * Run the request filter.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $cid = ( ! empty($_SERVER['HTTP_ORIGIN']) ) ? $_SERVER['HTTP_ORIGIN'] : 'development';
        $gamp = GAMP::setClientId( $cid );
        $gamp->setDocumentPath( $request->path() );
        $gamp->sendPageview();

        return $next($request);
    }

}