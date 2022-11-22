<?php

namespace App\Exceptions;

use Illuminate\Auth\Access\AuthorizationException;
use Laravel\Lumen\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;
use Illuminate\Http\Exception\HttpResponseException;
use Illuminate\Http\Response;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [

    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Throwable  $exception
     * @return void
     *
     * @throws \Exception
     */
    public function report(Throwable $exception)
    {
        try {
        parent::report($exception);
        } catch(\Exception $e) {

        }
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $exception
     * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     *
     * @throws \Throwable
     */
    public function render($request, Throwable $e)
    {
        try {
        $error_page = parent::render($request, $e);
        $status = (int) $error_page->getStatusCode();
        $e->getMessage() === NULL ? $message = 'No error message specified.' : $message = $e->getMessage();

        if($status === 401 || $status === 403) {
            $e->getStatusCode() === 401 ? $message = 'Unauthenticated.' : $message = 'Forbidden.';
        }

        if($status === 404) {
            $message = 'Resource not found.';
        }
        if($status === 405) {
            $message = 'Not allowed method.';
        }
        if($status === 419) {
            $message = 'You should re-authenticate.';
        }

        if($status !== 404) {
            if($status !== 422) {
              $dd = explode('/', $e->getFile());
              return response()->json([
                      'status' => 'error',
                      'code' => (int) $status,
                      'message' => $message,
                      'location' => end($dd),
                      'line' => $e->getLine()
              ], $status);
            }
        }

        return response()->json([
                'status' => 'error',
                'code' => (int) $status,
                'message' => $message,
        ], $status);

        } catch(\Exception $error_errorred) {
            return response()->json([
                    'status' => 'error',
                    'code' => (int) 500,
                    'message' => 'Exception handler itself actually errored with message: '.$error_errorred->getMessage(),
            ], 500);
        }
    }
}
