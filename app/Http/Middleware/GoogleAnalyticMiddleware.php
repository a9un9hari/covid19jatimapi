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
        $cid = ( ! empty($request->get('ip')) ) ? $request->get('ip') : $_SERVER['HTTP_ORIGIN'];
        $gamp = GAMP::setClientId( $cid );
        $gamp->setDocumentPath( $request->path() );
        $gamp->sendPageview();

        return $next($request);
    }

}