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
        $origin = ( ! empty($_SERVER['HTTP_ORIGIN']) ) ? $_SERVER['HTTP_ORIGIN'] : url();
        $ip = ( ! empty($request->get('ip')) ) ? $request->get('ip') : $origin;
        $ua = ( ! empty($request->get('ua')) ) ? $request->get('ua') : $request->header('User-Agent');
        $sr = ( ! empty($request->get('sr')) ) ? $request->get('sr') : '';
        $vp = ( ! empty($request->get('vp')) ) ? $request->get('vp') : '';
        $de = ( ! empty($request->get('de')) ) ? $request->get('de') : '';
        $dt = ( ! empty($request->get('dt')) ) ? $request->get('dt') : '';

        $gamp = GAMP::setClientId( $ip );
        $gamp->setDocumentPath( $request->path() )
            ->setDataSource($origin)
            ->setIpOverride($ip)
            ->setUserAgentOverride($ua)
            ->setDocumentReferrer($origin)
            ->setScreenResolution($sr)
            ->setViewportSize($vp)
            ->setDocumentEncoding($de)
            ->setDocumentTitle($dt)
            ->sendPageview();

        return $next($request);
    }

}