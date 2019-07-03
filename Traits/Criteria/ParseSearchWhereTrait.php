<?php

namespace Modules\Core\Traits\Criteria;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;

trait ParseSearchWhereTrait
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
    protected $searchClosures;

    protected function parseSearchWhere($value, $relation, $field, $condition)
    {
        if ($this->isFirstField || $this->modelForceAndWhere) {
            $this->parseSearchAndWhere($value, $relation, $field, $condition);
        } else {
            $this->parseSearchOrWhere($value, $relation, $field, $condition);
        }
    }

    protected function parseSearchOrWhere($value, $relation, $field, $condition)
    {
        if (!is_null($value)) {
            if (!is_null($relation)) {
                $this->parseSearchOrRelationClosure($value, $relation, $field, $condition);
            } else {
                $this->parseSearchOrClosure($value, $field, $condition);
            }
        }
    }

    protected function parseSearchAndWhere($value, $relation, $field, $condition)
    {
        if (!is_null($value)) {
            if (!is_null($relation)) {
                $this->parseSearchAndRelationClosure($value, $relation, $field, $condition);
            } else {
                $this->parseSearchAndClosure($value, $field, $condition);
            }
            $this->isFirstField = false;
        }
    }

    protected function parseSearchAndRelationClosure($value, $relation, $field, $condition)
    {
        $this->model = $this->model->whereHas($relation, function (Builder $query) use ($condition, $field, $value) {
            if (is_array($this->searchClosures) && Arr::has($this->searchClosures, $condition)) {
                $this->searchClosures[$condition]($query, $condition, $field, $value);
            } else {
                $query->where($field, $condition, $value);
            }
        });
    }

    protected function parseSearchAndClosure($value, $field, $condition)
    {
        if (is_array($this->searchClosures) && Arr::has($this->searchClosures, $condition)) {
            $this->model = $this->searchClosures[$condition]($this->model, $condition, $field, $value);
        } else {
            $this->model = $this->model->where($field, $condition, $value);
        }
    }

    protected function parseSearchOrClosure($value, $field, $condition)
    {
        if (is_array($this->searchClosures) && Arr::has($this->searchClosures, $condition)) {
            $this->model = $this->searchClosures[$condition]($this->model, $condition, $field, $value);
        } else {
            $this->model = $this->model->where($field, $condition, $value);
        }
    }

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
