<?php

namespace Modules\Core\Traits\Criteria;

use Illuminate\Database\Eloquent\Builder;

trait ParseSearchOrClosureTrait
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

    protected function parseSearchOrClosure($value, $field, $condition)
    {
        $modelTableName = $this->model->getModel()->getTable();
        switch ($condition) {
            case 'in':
                $this->model = $this->model->orWhereIn($modelTableName.'.'.$field, $value);
                break;
            case 'between':
                $this->model = $this->model->orWhereBetween($modelTableName.'.'.$field, $value);
                break;
            case 'cross':
                $this->model = $this->model->orWhere(function (Builder $query) use ($field, $value) {
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
                $this->model = $this->model->orWhere($modelTableName.'.'.$field, $condition, $value);
        }
    }
}
