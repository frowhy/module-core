<?php
/**
 * Created by PhpStorm.
 * User: frowhy
 * Date: 2017/8/1
 * Time: 下午3:25.
 */

namespace Modules\Core\Supports;

use Asm89\Stack\CorsService;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\Response as BaseResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Modules\Core\Contracts\Support\Boolable;
use Modules\Core\Enums\StatusCodeEnum;
use Modules\Core\Traits\Supports\ResponseHandleTrait;
use SoapBox\Formatter\Formatter;

class Response implements Responsable, Arrayable, Renderable, Boolable
{
    protected $response;
    protected $statusCode;

    use ResponseHandleTrait;

    public function __construct(array $response)
    {
        $this->response = $response;
        $this->statusCode = $response['meta']['status_code'] ?? StatusCodeEnum::HTTP_OK;

        return $this;
    }

    /**
     * 格式化响应.
     *
     * @return \Illuminate\Http\Response
     */
    private function format(): BaseResponse
    {
        list($response, $statusCode) = [$this->response, $this->statusCode];
        $formatter = Formatter::make($response, Formatter::ARR);
        $format = self::param('output_format') ?? (config('core.api.output_format'));
        $statusCode =
            (self::param('status_sync') ?? config('core.api.status_sync')) ? $statusCode : StatusCodeEnum::HTTP_OK;
        if (in_array($format, ['application/xml', 'xml'])) {
            $response = response($formatter->toXml(), $statusCode, ['Content-Type' => 'application/xml']);
        } elseif (in_array($format, ['application/x-yaml', 'yaml'])) {
            $response = response($formatter->toYaml(), $statusCode, ['Content-Type' => 'application/x-yaml']);
        } elseif (in_array($format, ['text/csv', 'csv'])) {
            $response = response($formatter->toCsv(), $statusCode, ['Content-Type' => 'text/csv']);
        } elseif (in_array($format, ['application/json', 'json'])) {
            $response = response($formatter->toJson(), $statusCode, ['Content-Type' => 'application/json']);
        } else {
            $response = response($response, $statusCode);
        }

        return $response;
    }

    /**
     * s
     * 允许跨域请求
     *
     * @param \Illuminate\Http\Response $response
     *
     * @return \Illuminate\Http\Response
     */
    private function cors(BaseResponse $response): BaseResponse
    {
        if (config('core.api.cors_enabled')) {
            /** @var CorsService $cors */
            $cors = app(CorsService::class);
            $request = request();

            if ($cors->isCorsRequest(request())) {
                if (!$response->headers->has('Access-Control-Allow-Origin')) {
                    $response = $cors->addActualRequestHeaders($response, $request);
                }
            }
        }

        return $response;
    }

    /**
     * Create an HTTP response that represents the object.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function toResponse($request): BaseResponse
    {
        return $this->render();
    }

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return (array) Arr::get($this->response, 'data');
    }

    /**
     * Get the evaluated contents of the object.
     *
     * @return \Illuminate\Http\Response
     */
    public function render(): BaseResponse
    {
        return $this->cors($this->format());
    }

    /**
     * Get the true and false of the instance.
     *
     * @return bool
     */
    public function toBool(): bool
    {
        return Str::startsWith(Arr::get($this->response, 'meta.status_code'), 2);
    }

    /**
     * Return an response.
     *
     * @param array $response
     *
     * @return Response
     */
    private static function call(array $response): self
    {
        return new self($response);
    }

    public static function param(string $param)
    {
        $request = app('Illuminate\Http\Request');

        if ($request->has($param)) {
            return $request->get($param);
        } else {
            $header_param = Str::title(Str::kebab(Str::studly($param)));
            if ($request->hasHeader($header_param)) {
                return $request->header($header_param);
            } else {
                return null;
            }
        }
    }
}
