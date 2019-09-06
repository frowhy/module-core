<?php

namespace Modules\Core\Traits\Supports;

use Exception;
use Modules\Core\Enums\StatusCodeEnum;
use Modules\Core\ErrorCodes\JWTErrorCode;
use Modules\Core\Supports\Handler;
use Tymon\JWTAuth\Exceptions\InvalidClaimException;
use Tymon\JWTAuth\Exceptions\PayloadException;
use Tymon\JWTAuth\Exceptions\TokenBlacklistedException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Exceptions\UserNotDefinedException;

trait HandleParseTrait
{
    protected static function parseTokenInvalidException(array $response, Exception $exception)
    {
        if ($exception instanceof TokenInvalidException) {
            $response['meta'][Handler::ERROR_CODE] = JWTErrorCode::TOKEN_INVALID;
        }

        return $response;
    }

    protected static function parseInvalidClaimException(array $response, Exception $exception)
    {
        if ($exception instanceof InvalidClaimException) {
            $response['meta'][Handler::ERROR_CODE] = JWTErrorCode::INVALID_CLAIM;
        }

        return $response;
    }

    protected static function parsePayloadException(array $response, Exception $exception)
    {
        if ($exception instanceof PayloadException) {
            $response['meta'][Handler::ERROR_CODE] = JWTErrorCode::PAYLOAD;
        }

        return $response;
    }

    protected static function parseTokenBlacklistedException(array $response, Exception $exception)
    {
        if ($exception instanceof TokenBlacklistedException) {
            $response['meta'][Handler::ERROR_CODE] = JWTErrorCode::TOKEN_BLACKLISTED;
        }

        return $response;
    }

    protected static function parseTokenExpiredException(array $response, Exception $exception)
    {
        if ($exception instanceof TokenExpiredException) {
            if ('Token has expired and can no longer be refreshed' === $exception->getMessage()) {
                $response['meta'][Handler::ERROR_CODE] = JWTErrorCode::CAN_NOT_REFRESHED;
            } else {
                $response['meta'][Handler::ERROR_CODE] = JWTErrorCode::TOKEN_EXPIRED;
            }
        }

        return $response;
    }

    protected static function parseUserNotDefinedException(array $response, Exception $exception)
    {
        if ($exception instanceof UserNotDefinedException) {
            $response['meta'][Handler::ERROR_CODE] = JWTErrorCode::USER_NOT_DEFINED;
        }

        return $response;
    }

    protected static function parseJWTException(array $response, Exception $exception): array
    {
        $response['meta'][Handler::ERROR_CODE] = JWTErrorCode::DEFAULT;
        $response['meta'][Handler::STATUS_CODE] = StatusCodeEnum::HTTP_UNAUTHORIZED;
        $response['meta'][Handler::MESSAGE] = $exception->getMessage();

        $response = self::parseTokenInvalidException($response, $exception);
        $response = self::parseInvalidClaimException($response, $exception);
        $response = self::parsePayloadException($response, $exception);
        $response = self::parseTokenBlacklistedException($response, $exception);
        $response = self::parseTokenExpiredException($response, $exception);
        $response = self::parseUserNotDefinedException($response, $exception);

        return $response;
    }
}
