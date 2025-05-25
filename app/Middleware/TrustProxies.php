<?php

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\TrustProxies as Middleware;
use Illuminate\Http\Request; // Zorg ervoor dat deze use statement aanwezig is

class TrustProxies extends Middleware
{
    /**
     * The trusted proxies for this application.
     *
     * @var array<int, string>|string|null
     */
    // Vertrouw alle proxies (meest voorkomende instelling achter een reverse proxy)
    // of vervang '*' door het IP-adres van je Nginx reverse proxy server.
    protected $proxies = '*';

    /**
     * The headers that should be used to detect proxies.
     * Laravel gebruikt deze headers om te bepalen of de request via HTTPS binnenkwam etc.
     * De standaardinstelling hier is meestal goed.
     * @var int
     */
    protected $headers =
        Request::HEADER_X_FORWARDED_FOR |
        Request::HEADER_X_FORWARDED_HOST |
        Request::HEADER_X_FORWARDED_PORT |
        Request::HEADER_X_FORWARDED_PROTO |
        Request::HEADER_X_FORWARDED_AWS_ELB; // Deze laatste is voor AWS Elastic Load Balancers, kan meestal blijven staan.
}