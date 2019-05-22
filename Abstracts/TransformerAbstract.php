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
        $this->parseRequestedFields();
        $this->parseExcludeFields();
        $this->filter = app(Filter::class);

        return $this->field;
    }

    protected function parseRequestedFields()
    {
        $class = class_basename($this->transform);
        $param = Response::param('requested_fields') ?? $this->filter->requestedFields[$class];
        if (!is_null($param)) {
            if (is_array($param)) {
                $requestedFields = $param;
            } else {
                $requestedFields = explode(',', $param);
            }

            if ($requestedFields) {

                foreach ($requestedFields as $requestedField) {

                    if ($this instanceof TransformerAbstract) {

                        $scope = null;

                        if (Str::contains($requestedField, '.')) {
                            $requestedFieldArray = explode('.', $requestedField);
                            $length = count($requestedFieldArray);
                            $requestedField = Arr::last($requestedFieldArray);
                            Arr::forget($requestedFieldArray, $length - 1);
                            $scope = implode('.', $requestedFieldArray);
                        }

                        if ($scope === $this->getCurrentScope()->getIdentifier()) {
                            $this->field = Arr::only($this->field, $requestedField);
                        }
                    }
                }
            }
        }
    }

    protected function parseExcludeFields()
    {
        $class = class_basename($this->transform);
        $param = Response::param('exclude_fields') ?? $this->filter->excludeFields[$class];

        if (!is_null($param)) {
            if (is_array($param)) {
                $excludeFields = $param;
            } else {
                $excludeFields = explode(',', $param);
            }

            if ($excludeFields) {

                foreach ($excludeFields as $excludeField) {

                    if ($this instanceof TransformerAbstract) {

                        $scope = null;

                        if (Str::contains($excludeField, '.')) {
                            $excludeFieldArray = explode('.', $excludeField);
                            $length = count($excludeFieldArray);
                            $excludeField = Arr::last($excludeFieldArray);
                            Arr::forget($excludeFieldArray, $length - 1);
                            $scope = implode('.', $excludeFieldArray);
                        }

                        if ($scope === $this->getCurrentScope()->getIdentifier()) {
                            if (Arr::has($this->field, $excludeField)) {
                                $this->field = Arr::except($this->field, $excludeField);
                            }
                        }
                    }
                }
            }
        }
    }
}
