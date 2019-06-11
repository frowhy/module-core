<?php

namespace Modules\Core\Traits\Supports;

use Illuminate\Support\Arr;
use Modules\Core\Enums\StatusCodeEnum;
use Modules\Core\Supports\Response;

trait ResponseHandleTrait
{
    use ResponseParseTrait;

    /**
     * Response Handle.
     *
     * @param int         $statusCode
     * @param             $data
     * @param bool        $overwrite
     * @param string|null $message
     *
     * @return \Modules\Core\Supports\Response
     */
    public static function handle(
        int $statusCode,
        $data = null,
        bool $overwrite = false,
        string $message = null
    ): Response {
        if (($overwrite && is_array($data))) {
            $_data = $data;
        } else {
            $_data = self::parseData($data);
        }

        $_data = self::parseDataMeta($_data);
        $_meta = self::parseMeta($data);

        $_meta = Arr::prepend($_meta, $statusCode, 'status_code');
        $_meta = Arr::prepend($_meta, $message ?? StatusCodeEnum::__($statusCode), 'message');

        Arr::set($response, 'meta', $_meta);

        if (!is_null($_data)) {
            Arr::set($response, 'data', $_data);
        }

        return self::call($response);
    }

    /**
     * Response Ok.
     *
     * @param             $data
     * @param bool        $overwrite
     * @param string|null $message
     *
     * @return \Modules\Core\Supports\Response
     */
    public static function handleOk($data = null, bool $overwrite = false, string $message = null): Response
    {
        return self::handle(StatusCodeEnum::HTTP_OK, $data, $overwrite, $message);
    }

    /**
     * Response Created.
     *
     * @param             $data
     * @param bool        $overwrite
     * @param string|null $message
     *
     * @return \Modules\Core\Supports\Response
     */
    public static function handleCreated($data = null, bool $overwrite = false, string $message = null): Response
    {
        return self::handle(StatusCodeEnum::HTTP_CREATED, $data, $overwrite, $message);
    }

    /**
     * Response Accepted.
     *
     * @param             $data
     * @param bool        $overwrite
     * @param string|null $message
     *
     * @return \Modules\Core\Supports\Response
     */
    public static function handleAccepted($data = null, bool $overwrite = false, string $message = null): Response
    {
        return self::handle(StatusCodeEnum::HTTP_ACCEPTED, $data, $overwrite, $message);
    }

    /**
     * Response NoContent.
     *
     * @param             $data
     * @param bool        $overwrite
     * @param string|null $message
     *
     * @return \Modules\Core\Supports\Response
     */
    public static function handleNoContent($data = null, bool $overwrite = false, string $message = null): Response
    {
        return self::handle(StatusCodeEnum::HTTP_NO_CONTENT, $data, $overwrite, $message);
    }

    /**
     * Response ResetContent.
     *
     * @param             $data
     * @param bool        $overwrite
     * @param string|null $message
     *
     * @return \Modules\Core\Supports\Response
     */
    public static function handleResetContent($data = null, bool $overwrite = false, string $message = null): Response
    {
        return self::handle(StatusCodeEnum::HTTP_RESET_CONTENT, $data, $overwrite, $message);
    }

    /**
     * Response SeeOther.
     *
     * @param             $data
     * @param bool        $overwrite
     * @param string|null $message
     *
     * @return \Modules\Core\Supports\Response
     */
    public static function handleSeeOther(string $message = null, $data = null, bool $overwrite = false): Response
    {
        return self::handle(StatusCodeEnum::HTTP_SEE_OTHER, $data, $overwrite, $message);
    }

    /**
     * Response BadRequest.
     *
     * @param             $data
     * @param bool        $overwrite
     * @param string|null $message
     *
     * @return \Modules\Core\Supports\Response
     */
    public static function handleBadRequest(string $message = null, $data = null, bool $overwrite = false): Response
    {
        return self::handle(StatusCodeEnum::HTTP_BAD_REQUEST, $data, $overwrite, $message);
    }

    /**
     * Response Unauthorized.
     *
     * @param             $data
     * @param bool        $overwrite
     * @param string|null $message
     *
     * @return \Modules\Core\Supports\Response
     */
    public static function handleUnauthorized(string $message = null, $data = null, bool $overwrite = false): Response
    {
        return self::handle(StatusCodeEnum::HTTP_UNAUTHORIZED, $data, $overwrite, $message);
    }

    /**
     * Response PaymentRequired.
     *
     * @param             $data
     * @param bool        $overwrite
     * @param string|null $message
     *
     * @return \Modules\Core\Supports\Response
     */
    public static function handlePaymentRequired(
        string $message = null,
        $data = null,
        bool $overwrite = false
    ): Response {
        return self::handle(StatusCodeEnum::HTTP_PAYMENT_REQUIRED, $data, $overwrite, $message);
    }

    /**
     * Response Forbidden.
     *
     * @param             $data
     * @param bool        $overwrite
     * @param string|null $message
     *
     * @return \Modules\Core\Supports\Response
     */
    public static function handleForbidden(string $message = null, $data = null, bool $overwrite = false): Response
    {
        return self::handle(StatusCodeEnum::HTTP_PAYMENT_REQUIRED, $data, $overwrite, $message);
    }

    /**
     * Response NotFound.
     *
     * @param             $data
     * @param bool        $overwrite
     * @param string|null $message
     *
     * @return \Modules\Core\Supports\Response
     */
    public static function handleNotFound(string $message = null, $data = null, bool $overwrite = false): Response
    {
        return self::handle(StatusCodeEnum::HTTP_NOT_FOUND, $data, $overwrite, $message);
    }

    /**
     * Response UnprocessableEntity.
     *
     * @param             $data
     * @param bool        $overwrite
     * @param string|null $message
     *
     * @return \Modules\Core\Supports\Response
     */
    public static function handleUnprocessableEntity(
        string $message = null,
        $data = null,
        bool $overwrite = false
    ): Response {
        return self::handle(StatusCodeEnum::HTTP_UNPROCESSABLE_ENTITY, $data, $overwrite, $message);
    }

    /**
     * Response Locked.
     *
     * @param             $data
     * @param bool        $overwrite
     * @param string|null $message
     *
     * @return \Modules\Core\Supports\Response
     */
    public static function handleLocked(string $message = null, $data = null, bool $overwrite = false): Response
    {
        return self::handle(StatusCodeEnum::HTTP_LOCKED, $data, $overwrite, $message);
    }

    /**
     * Response TooManyRequests.
     *
     * @param             $data
     * @param bool        $overwrite
     * @param string|null $message
     *
     * @return \Modules\Core\Supports\Response
     */
    public static function handleTooManyRequests(
        string $message = null,
        $data = null,
        bool $overwrite = false
    ): Response {
        return self::handle(StatusCodeEnum::HTTP_TOO_MANY_REQUESTS, $data, $overwrite, $message);
    }

    /**
     * Response InternalServerError.
     *
     * @param             $data
     * @param bool        $overwrite
     * @param string|null $message
     *
     * @return \Modules\Core\Supports\Response
     */
    public static function handleInternalServerError(
        string $message = null,
        $data = null,
        bool $overwrite = false
    ): Response {
        return self::handle(StatusCodeEnum::HTTP_INTERNAL_SERVER_ERROR, $data, $overwrite, $message);
    }

    /**
     * Response NotImplemented.
     *
     * @param             $data
     * @param bool        $overwrite
     * @param string|null $message
     *
     * @return \Modules\Core\Supports\Response
     */
    public static function handleNotImplemented(string $message = null, $data = null, bool $overwrite = false): Response
    {
        return self::handle(StatusCodeEnum::HTTP_NOT_IMPLEMENTED, $data, $overwrite, $message);
    }
}
