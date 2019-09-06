<?php

namespace Modules\Core\Supports;

use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Modules\Core\Enums\StatusCodeEnum;
use Modules\Core\Traits\Supports\HandleParseTrait;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tymon\JWTAuth\Exceptions\JWTException;

class Handler extends ExceptionHandler
{
    const STATUS_CODE = 'status_code';
    const ERROR_CODE = 'error_code';
    const MESSAGE = 'message';
    const DEBUG = 'debug';

    use HandleParseTrait;

    /**
     * Render an exception into an HTTP response.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Exception|HttpException $exception
     *
     * @return \Illuminate\Http\Response|\Symfony\Component\HttpFoundation\Response
     */
    public function render($request, Exception $exception)
    {
        if ($request->is('api/*') || $request->wantsJson()) {
            if ('web' !== config('core.api.error_format')) {
                return $this::renderException($exception)->render();
            }
        }

        return parent::render($request, $exception);
    }

    public static function renderException(Exception $exception)
    {
        $response = [];
        $exception = self::parseUnauthorizedHttpException($exception);

        if ($exception instanceof ModelNotFoundException) {
            $response = self::parseModelNotFoundException($response, $exception);
        } elseif ($exception instanceof JWTException) {
            $response = self::parseJWTException($response, $exception);
        } else {
            $response = self::parseException($response, $exception, get_class($exception));
        }

        $response = self::parseDebug($response, $exception);

        return self::response(collect($response)->toArray());
    }

    protected static function response(array $response)
    {
        return new Response($response);
    }

    protected static function parseDebug(array $response, Exception $exception)
    {
        if (true === config('app.debug')) {
            $response['meta'][self::DEBUG]['file'] = $exception->getFile();
            $response['meta'][self::DEBUG]['line'] = $exception->getLine();
            $response['meta'][self::DEBUG]['trace'] = $exception->getTrace();
        }

        return $response;
    }

    protected static function parseUnauthorizedHttpException(Exception $exception)
    {
        if ($exception instanceof UnauthorizedHttpException) {
            if (method_exists($exception, 'getPrevious') && $exception->getPrevious() instanceof JWTException) {
                $exception = $exception->getPrevious();
            }
        }

        return $exception;
    }

    protected static function parseModelNotFoundException(array $response, Exception $exception): array
    {
        $response['meta'][self::STATUS_CODE] = StatusCodeEnum::HTTP_NOT_FOUND;
        $response['meta'][self::MESSAGE] = $exception->getMessage();

        return $response;
    }

    protected static function parseException(array $response, Exception $exception, string $exceptionClass): array
    {
        if (method_exists($exception, 'getStatusCode')) {
            $response['meta'][self::STATUS_CODE] = $exception->getStatusCode();
        } else {
            $response['meta'][self::STATUS_CODE] = StatusCodeEnum::HTTP_INTERNAL_SERVER_ERROR;
        }

        if (method_exists($exception, 'getCode')) {
            $response['meta'][self::ERROR_CODE] = $exception->getCode();
        }

        if (null === $exception->getMessage()) {
            $response['meta'][self::MESSAGE] = class_basename($exceptionClass);
        } else {
            $response['meta'][self::MESSAGE] = $exception->getMessage();
        }

        return $response;
    }
}
