<?php

namespace Modules\Core\Traits\Criteria;

use Illuminate\Database\Eloquent\Builder;

trait ParseSearchAndClosureTrait
{
    /** @var \Illuminate\Http\Request $request */
    protected $request;
    /** @var \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|\Illuminate\Database\Query\Builder $model */
    protected $model;
    /** @var \Prettus\Repository\Contracts\RepositoryInterface $repository */
    protected $repository;
    protected $search;
    protected $searchData;
    protected $searchFields;
    protected $isFirstField;
    protected $modelForceAndWhere;
    protected $fieldsSearchable;
    protected $fields;
    protected $filter;
    protected $orderBy;
    protected $sortedBy;
    protected $with;
    protected $searchJoin;
    protected $acceptedConditions;
    protected $originalFields;

    protected function parseSearchAndClosure($value, $field, $condition)
    {
        switch ($condition) {
            case 'in':
                $this->model = $this->model->whereIn($field, $value);
                break;
            case 'between':
                $this->model = $this->model->whereBetween($field, $value);
                break;
            case 'cross':
                $this->model = $this->model->where(function (Builder $query) use ($field, $value) {
                    $query->where(function (Builder $query) use ($field, $value) {
                        $query->where("{$field}_min", '<=', $value[0])
                              ->where("{$field}_max", '>=', $value[1]);
                    })->orWhere(function (Builder $query) use ($field, $value) {
                        $query->where("{$field}_min", '<=', $value[0])
                              ->where("{$field}_max", '>=', $value[0]);
                    })->orWhere(function (Builder $query) use ($field, $value) {
                        $query->where("{$field}_min", '>=', $value[0])
                              ->where("{$field}_max", '<=', $value[1]);
                    })->orWhere(function (Builder $query) use ($field, $value) {
                        $query->where("{$field}_min", '>=', $value[0])
                              ->where("{$field}_max", '>=', $value[1])
                              ->where("{$field}_min", '<=', $value[1]);
                    });
                });
                break;
            default:
                $this->model = $this->model->where($field, $condition, $value);
        }
    }
}
