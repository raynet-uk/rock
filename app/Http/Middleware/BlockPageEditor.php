<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class BlockPageEditor
{
    protected array $blocked = [];

    public function handle(Request $request, Closure $next)
    {
        if (in_array(auth()->id(), $this->blocked)) {
            abort(403, 'You do not have access to the page editor.');
        }

        return $next($request);
    }
}
