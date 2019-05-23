<?php
/**
 * Created by PhpStorm.
 * User: frowhy
 * Date: 2017/12/6
 * Time: 上午10:30
 */

namespace Modules\Core\Abstracts;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Modules\Core\Contracts\Repository\Filter;
use Modules\Core\Supports\Response;
use League\Fractal\TransformerAbstract as BaseTransformerAbstract;

abstract class TransformerAbstract extends BaseTransformerAbstract
{
    protected $transform;
    protected $field;
    protected $filter;

    abstract public function fields($attribute);

    public function transform($transform)
    {
        $this->transform = $transform;
        $this->field = $this->fields($transform);
        $this->filter = app(Filter::class);
        $this->parseRequestedFields();
        $this->parseExcludeFields();

        return $this->field;
    }

    protected function parseRequestedFields()
    {
        $class = class_basename($this->transform);

        $param = Response::param('requested_fields') ?? Arr::get($this->filter->requestedFields, $class);

        if (!is_null($param)) {

            if (is_array($param)) {
                $requestedFields = $param;
            } else {
                $requestedFields = explode(',', $param);
            }

            foreach ($requestedFields as $requestedField) {
                if ($this instanceof TransformerAbstract) {
                    $this->field = Arr::only($this->field, $this->getFilterField($requestedField));
                }
            }
        }
    }

    protected function parseExcludeFields()
    {
        $class = class_basename($this->transform);
        $param = Response::param('exclude_fields') ?? Arr::get($this->filter->excludeFields, $class);

        if (!is_null($param)) {

            if (is_array($param)) {
                $excludeFields = $param;
            } else {
                $excludeFields = explode(',', $param);
            }

            foreach ($excludeFields as $excludeField) {
                if ($this instanceof TransformerAbstract) {
                    $this->field = Arr::except($this->field, $this->getFilterField($excludeField));
                }
            }
        }
    }

    protected function getFilterField(string $field): ?string
    {
        $scope = null;

        if (Str::contains($field, '.')) {
            $fieldArray = explode('.', $field);
            $length = count($fieldArray);
            $field = Arr::last($fieldArray);
            Arr::forget($fieldArray, $length - 1);
            $scope = implode('.', $fieldArray);

        }

        if ($scope === $this->getCurrentScope()->getIdentifier()) {
            return $field;
        } else {
            return null;
        }
    }
}
