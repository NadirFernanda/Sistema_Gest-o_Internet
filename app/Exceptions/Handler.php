<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Auth\Access\AuthorizationException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register(): void
    {
        // Render a friendly message for authorization failures.
        $this->renderable(function (AuthorizationException $e, $request) {
            if ($request->wantsJson() || $request->expectsJson()) {
                return response()->json([
                    'error' => 'Acesso negado: você não tem permissão para realizar esta ação.'
                ], 403);
            }

            $message = $e->getMessage() ?: 'Você não tem permissão para acessar esta funcionalidade.';
            return response()->view('errors.403', ['message' => $message], 403);
        });
    }
}
