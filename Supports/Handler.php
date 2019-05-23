<?php

namespace Modules\Core\Supports;

use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Modules\Core\Enums\StatusCodeEnum;
use Modules\Core\ErrorCodes\JWTErrorCode;
use Modules\Core\Traits\Supports\HandleParseTrait;
use Symfony\Component\HttpKernel\Exception\{
    HttpException, UnauthorizedHttpException
};
use Tymon\JWTAuth\Exceptions\{
    InvalidClaimException,
    JWTException,
    PayloadException,
    TokenBlacklistedException,
    TokenExpiredException,
    TokenInvalidException,
    UserNotDefinedException
};

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
                $response = [];
                $exception = $this->parseUnauthorizedHttpException($exception);

                if ($exception instanceof ModelNotFoundException) {
                    $response = $this->parseModelNotFoundException($response, $exception);
                } elseif ($exception instanceof JWTException) {
                    $response = $this->parseJWTException($response, $exception);
                } else {
                    $response = $this->parseException($response, $exception, get_class($exception));
                }

                $response = $this->parseDebug($response, $exception);

                return $this->response(collect($response)->toArray());
            }
        }

        return parent::render($request, $exception);
    }

    protected function response(array $response)
    {
        return (new Response($response))->render();
    }

    protected function parseDebug(array $response, Exception $exception)
    {
        if (true === config('app.debug')) {
            $response['meta'][self::DEBUG]['file'] = $exception->getFile();
            $response['meta'][self::DEBUG]['line'] = $exception->getLine();
            $response['meta'][self::DEBUG]['trace'] = $exception->getTrace();
        }

        return $response;
    }

    protected function parseUnauthorizedHttpException(Exception $exception)
    {
        if ($exception instanceof UnauthorizedHttpException && method_exists($exception, 'getPrevious')) {
            $exception = $exception->getPrevious();
        }

        return $exception;
    }

    protected function parseModelNotFoundException(array $response, Exception $exception): array
    {
        $response['meta'][self::STATUS_CODE] = StatusCodeEnum::HTTP_NOT_FOUND;
        $response['meta'][self::MESSAGE] = $exception->getMessage();

        return $response;
    }

    protected function parseException(array $response, Exception $exception, string $exceptionClass): array
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
