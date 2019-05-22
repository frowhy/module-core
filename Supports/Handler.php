<?php

namespace Modules\Core\Supports;

use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Modules\Core\Enums\StatusCodeEnum;
use Modules\Core\ErrorCodes\JWTErrorCode;
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
                $exceptionClass = get_class($exception);
                $exception = $this->overwrite($exception);

                if ($exception instanceof ModelNotFoundException) {
                    $response['meta'][self::STATUS_CODE] = StatusCodeEnum::HTTP_NOT_FOUND;
                    $response['meta'][self::MESSAGE] = $exception->getMessage();
                } elseif ($exception instanceof JWTException) {
                    switch ($exceptionClass) {
                        case InvalidClaimException::class:
                            $response['meta'][self::ERROR_CODE] = JWTErrorCode::INVALID_CLAIM;
                            break;

                        case PayloadException::class:
                            $response['meta'][self::ERROR_CODE] = JWTErrorCode::PAYLOAD;
                            break;

                        case TokenBlacklistedException::class:
                            $response['meta'][self::ERROR_CODE] = JWTErrorCode::TOKEN_BLACKLISTED;
                            break;

                        case TokenExpiredException::class:
                            if ('Token has expired and can no longer be refreshed' === $exception->getMessage()) {
                                $response['meta'][self::ERROR_CODE] = JWTErrorCode::CAN_NOT_REFRESHED;
                            } else {
                                $response['meta'][self::ERROR_CODE] = JWTErrorCode::TOKEN_EXPIRED;
                            }
                            break;

                        case TokenInvalidException::class:
                            $response['meta'][self::ERROR_CODE] = JWTErrorCode::TOKEN_INVALID;
                            break;

                        case UserNotDefinedException::class:
                            $response['meta'][self::ERROR_CODE] = JWTErrorCode::USER_NOT_DEFINED;
                            break;

                        default:
                            $response['meta'][self::ERROR_CODE] = JWTErrorCode::DEFAULT;;
                    }
                    $response['meta'][self::STATUS_CODE] = StatusCodeEnum::HTTP_UNAUTHORIZED;
                    $response['meta'][self::MESSAGE] = $exception->getMessage();
                } else {
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
                }

                $response = $this->debug($response, $exception);

                return $this->response(collect($response)->toArray());
            }
        }

        return parent::render($request, $exception);
    }

    protected function response(array $response)
    {
        return (new Response($response))->render();
    }

    protected function debug(array $response, Exception $exception)
    {
        if (true === config('app.debug')) {
            $response['meta'][self::DEBUG]['file'] = $exception->getFile();
            $response['meta'][self::DEBUG]['line'] = $exception->getLine();
            $response['meta'][self::DEBUG]['trace'] = $exception->getTrace();
        }

        return $response;
    }

    protected function overwrite(Exception $exception)
    {
        if ($exception instanceof UnauthorizedHttpException && method_exists($exception, 'getPrevious')) {
            $exception = $exception->getPrevious();
        }

        return $exception;
    }
}
