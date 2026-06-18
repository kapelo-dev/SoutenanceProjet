<?php

namespace App\Http\Middleware;

use App\Models\ConfigAppMobile;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateSmsApiToken
{
    public function handle(Request $request, Closure $next): Response
    {
        // Token prioritaire : configuration en base (interface Laravel), sinon config/sms_api
        $config = ConfigAppMobile::getActive();
        $token = $config && ! empty($config->api_token)
            ? $config->api_token
            : config('sms_api.token');

        if (empty($token)) {
            return response()->json(['success' => false, 'message' => 'API SMS non configurée.'], 503);
        }

        $auth = $request->bearerToken();
        if ($auth !== $token) {
            \Log::warning('[SMS-API] Token invalide', ['ip' => $request->ip()]);
            return response()->json(['success' => false, 'message' => 'Token invalide.'], 401);
        }

        return $next($request);
    }
}
