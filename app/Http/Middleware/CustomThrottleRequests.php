<?php

namespace App\Http\Middleware;

use App\Traits\apiResponser;
use Closure;
use Illuminate\Routing\Middleware\ThrottleRequests;

class CustomThrottleRequests extends ThrottleRequests
{

    use apiResponser;
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    protected function buildException($key, $maxAttempts)
    {
        $response = $this->errorResponse('Too Many Attempts', 429);

        $retryAfter = $this->getTimeUntilNextRetry($key);

        return $this->getHeaders(
            $maxAttempts,
            $this->calculateRemainingAttempts($key, $maxAttempts, $retryAfter),
            $retryAfter
        );

    }
}
