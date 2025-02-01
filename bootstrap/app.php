<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Exceptions\Handler;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\ErrorHandler\Error\FatalError;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'status' => 'false',
                    'message' => 'Route Not found',
                ], 404);
            }
        });

        $exceptions->render(function (\ErrorException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'code' => 500,
                    'status' => 'error',
                    'message' => $e->getMessage(),
                    'error' => [
                        'line' =>  $e->getLine(),
                        'class' => str_replace('/', '\\', $e->getFile())
                    ]
                ], 404);
            }
            
        });

        $exceptions->render(function (ValidationException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'code'  => 422,
                    'status' => 'false',
                    'message' => 'some fields are missing.',
                    'errors' => $e->validator->errors()
                ], 422);
            }
        });

        $exceptions->render(function (QueryException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'status' => 'false',
                    'message' => 'An error occured in the query.',
                    'errors' => $e->getMessage(),
                ], 422);
            }
        });

        $exceptions->render(function (BadRequestException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'code' => 400,
                    'status' => 'false',
                    'message' => 'Malformed request syntax',
                ], 400); // 400 Bad Request
            }
        });

        $exceptions->render(function (RuntimeException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'code' => 500,
                    'status' => 'false',
                    'message' => 'Internal Server error.',
                    'error'   => $e->getMessage()
                ], 400); // 400 Bad Request
            }
        });

        $exceptions->render(function (FatalError $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'code' => 500,
                    'status' => 'false',
                    'message' => 'Internal Server error.',
                    'error'   => $e->getMessage()
                ], 400); // 400 Bad Request
            }
        });

        $exceptions->render(function (\InvalidArgumentException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'code' => 500,
                    'status' => 'false',
                    'message' => 'Internal Server error.',
                    'error'   => $e->getMessage()
                ], 400); // 400 Bad Request
            }
        });
 
    })->create();
