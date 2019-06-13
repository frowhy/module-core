<?php

namespace Modules\Core\Traits\Criteria;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;

trait ParseSearchOrRelationClosureTrait
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
    protected $crossMin;
    protected $crossMax;
    protected $searchClosures;

    protected function parseSearchOrRelationClosure($value, $relation, $field, $condition)
    {
        $this->model = $this->model->orWhereHas($relation, function (Builder $query) use ($field, $condition, $value) {

            if (is_array($this->searchClosures) && Arr::has($this->searchClosures, $condition)) {
                $this->searchClosures[$condition]($query, $condition, $field, $value);
            } else {
                $query->where($field, $condition, $value);
            }
        });
    }
}
