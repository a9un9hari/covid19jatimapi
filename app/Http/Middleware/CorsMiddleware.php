<?php

namespace App\Http\Middleware;

use Closure;

class CorsMiddleware
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
        $allowedDomains = [
            'https://infoblitar.com',
            'http://infoblitar.test',
            'http://covid19.test',
            'https://agunghari.com'
        ];

        $curUrl = ( ! empty($_SERVER['HTTP_ORIGIN']) ) ? $_SERVER['HTTP_ORIGIN'] : '';
        
        $domainApproved = '';

        if (in_array($curUrl, $allowedDomains)) {
            $domainApproved = $curUrl;
        }

       //RULE HEADERSNYA HARUS KITA SET SECARA SPESIFIK SEPERTI INI 
        $headers = [
            'Access-Control-Allow-Origin'      => $domainApproved,
            'Access-Control-Allow-Methods'     => 'GET', //'POST, GET, OPTIONS, PUT, DELETE'
            'Access-Control-Allow-Credentials' => 'true',
            'Access-Control-Max-Age'           => '86400',
            'Access-Control-Allow-Headers'     => 'Content-Type, Authorization, X-Requested-With'
        ];
        
        //TAPI JIKA METHOD YANG MASUK ADALAH OPTIONS
        if ($request->isMethod('OPTIONS')) {
            //MAKA KITA KEMBALIKAN BAHWA METHOD TERSEBUT ADALAH OPTIONS
            return response()->json('{"method": "OPTIONS"}', 200, $headers);
        }

        // dd($headers);

        //SELAIN ITU, KITA AKAN MENERUSKAN RESPONSE SEPERTI BIASA DENGAN MENGIKUT SERTAKAN HEADERS YANG SUDAH DITETAPKAN.
        $response = $next($request);
        foreach ($headers as $key => $row) {
            $response->header($key, $row);
        }
        return $response;
    }

}