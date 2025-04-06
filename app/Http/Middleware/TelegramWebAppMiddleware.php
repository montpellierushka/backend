<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TelegramWebAppMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Проверяем наличие initData в заголовке
        $initData = $request->header('X-Telegram-Init-Data');
        
        if (!$initData) {
            return response()->json([
                'status' => 'error',
                'message' => 'Требуется аутентификация через Telegram Web App'
            ], 401);
        }
        
        // TODO: Добавить проверку подписи initData
        
        return $next($request);
    }
} 